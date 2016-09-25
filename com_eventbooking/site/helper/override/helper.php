<?php
class EventbookingHelperOverrideHelper extends EventbookingHelper
{
	/**
	 * Calculate registration fee for cart registration
	 *
	 * @param EventbookingHelperCart $cart
	 * @param RADForm                $form
	 * @param                        $data
	 * @param                        $config
	 * @param null                   $paymentMethod
	 *
	 * @return array
	 */
	public static function calculateCartRegistrationFee($cart, $form, $data, $config, $paymentMethod = null)
	{
		$user                 = JFactory::getUser();
		$db                   = JFactory::getDbo();
		$query                = $db->getQuery(true);
		$nullDate             = $db->getNullDate();
		$fees                 = array();
		$recordsData          = array();
		$totalAmount          = 0;
		$discountAmount       = 0;
		$lateFee              = 0;
		$taxAmount            = 0;
		$amount               = 0;
		$depositAmount        = 0;
		$paymentProcessingFee = 0;
		$feeAmount            = $form->calculateFee();
		$items                = $cart->getItems();
		$quantities           = $cart->getQuantities();
		$paymentType          = isset($data['payment_type']) ? $data['payment_type'] : 1;
		$couponCode           = isset($data['coupon_code']) ? $data['coupon_code'] : '';
		$collectRecordsData   = isset($data['collect_records_data']) ? $data['collect_records_data'] : false;
		$paymentFeeAmount     = 0;
		$paymentFeePercent    = 0;

		$couponTimesAvailable = -1;
		$couponUsedCount      = 0;

		if ($paymentMethod)
		{
			$method            = os_payments::loadPaymentMethod($paymentMethod);
			$params            = new JRegistry($method->params);
			$paymentFeeAmount  = (float) $params->get('payment_fee_amount');
			$paymentFeePercent = (float) $params->get('payment_fee_percent');
		}

		$couponDiscountedEventIds = array();
		if ($couponCode)
		{
			$query->clear()
				->select('*')
				->from('#__eb_coupons')
				->where('published = 1')
				->where('`access` IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')')
				->where('code = ' . $db->quote($couponCode))
				->where('(valid_from="0000-00-00" OR valid_from <= NOW())')
				->where('(valid_to="0000-00-00" OR valid_to >= NOW())')
				->where('user_id IN (0, ' . $user->id . ')')
				->where('(times = 0 OR times > used)')
				->where('(event_id = -1 OR id IN (SELECT coupon_id FROM #__eb_coupon_events WHERE event_id IN (' . implode(',', $items) . ')))')
				->order('id DESC');
			$db->setQuery($query);
			$coupon = $db->loadObject();
			if ($coupon)
			{
				$fees['coupon_valid'] = 1;
				if ($coupon->event_id != -1)
				{
					// Get list of events which will receive discount
					$query->clear();
					$query->select('event_id')
						->from('#__eb_coupon_events')
						->where('coupon_id = ' . $coupon->id);
					$db->setQuery($query);
					$couponDiscountedEventIds = $db->loadColumn();
				}

				if ($coupon->times > 0)
				{
					$couponTimesAvailable = $coupon->times - $coupon->used;
				}
			}
			else
			{
				$fees['coupon_valid'] = 0;
			}
		}
		else
		{
			$fees['coupon_valid'] = 1;
		}

		if ($config->collect_member_information_in_cart)
		{
			$membersForm           = array();
			$membersTotalAmount    = array();
			$membersDiscountAmount = array();
			$membersLateFee        = array();
			$membersTaxAmount      = array();
		}

		// Calculate bundle discount if setup
		$fees['bundle_discount_amount'] = 0;
		$fees['bundle_discount_ids']    = array();

		$nullDate    = $db->quote($db->getNullDate());
		$currentDate = $db->quote(JHtml::_('date', 'Now', 'Y-m-d'));
		$query->clear()
			->select('id, event_ids, discount_amount')
			->from('#__eb_discounts')
			->where('(from_date = ' . $nullDate . ' OR DATE(from_date) <=' . $currentDate . ')')
			->where('(to_date = ' . $nullDate . ' OR DATE(to_date) >= ' . $currentDate . ')')
			->where('(times = 0 OR times > used)')
			->where('id IN (SELECT discount_id FROM #__eb_discount_events WHERE event_id IN (' . implode(',', $items) . '))');
		$db->setQuery($query);
		$discountRules = $db->loadObjectList();
		if (!empty($discountRules))
		{
			$registeredEventIds = $items;
			if ($user->id)
			{
				$query->clear()
					->select('DISTINCT event_id')
					->from('#__eb_registrants')
					->where('user_id = ' . $user->id)
					->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published IN (0, 1)))');
				$registeredEventIds = array_merge($registeredEventIds, $db->loadColumn());
			}

			foreach ($discountRules as $rule)
			{
				$eventIds = explode(',', $rule->event_ids);
				if (!array_diff($eventIds, $registeredEventIds))
				{
					$fees['bundle_discount_amount'] += $rule->discount_amount;
					$fees['bundle_discount_ids'][] = $rule->id;
				}
			}
		}

		$count = 0;
		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			$eventId               = (int) $items[$i];
			$quantity              = (int) $quantities[$i];
			$recordsData[$eventId] = array();
			$event                 = EventbookingHelperDatabase::getEvent($eventId);
			$rate                  = self::getRegistrationRate($eventId, $quantity);
			if ($i == 0)
			{
				$registrantTotalAmount = $rate * $quantity + $feeAmount;
			}
			else
			{
				$registrantTotalAmount = $rate * $quantity;
			}

			// Members data
			if ($config->collect_member_information_in_cart)
			{
				$memberFormFields = EventbookingHelper::getFormFields($eventId, 2);
				for ($j = 0; $j < $quantity; $j++)
				{
					$count++;
					$memberForm = new RADForm($memberFormFields);
					$memberForm->setFieldSuffix($count);
					$memberForm->bind($data);
					$memberExtraFee = $memberForm->calculateFee();
					$registrantTotalAmount += $memberExtraFee;
					$membersTotalAmount[$eventId][$j]    = $rate + $memberExtraFee;
					$membersDiscountAmount[$eventId][$j] = 0;
					$membersLateFee[$eventId][$j]        = 0;
					$membersForm[$eventId][$j]           = $memberForm;
				}
			}

			if ($i == 0)
			{
				$registrantDiscount = $fees['bundle_discount_amount'];
			}
			else
			{
				$registrantDiscount = 0;
			}

			// Member discount
			if ($user->id)
			{
				$discountRate = EventbookingHelper::calculateMemberDiscount($event->discount_amounts, $event->discount_groups);
				if ($discountRate > 0)
				{
					if ($event->discount_type == 1)
					{
						$registrantDiscount = $registrantTotalAmount * $discountRate / 100;
						if ($config->collect_member_information_in_cart)
						{
							for ($j = 0; $j < $quantity; $j++)
							{
								$membersDiscountAmount[$eventId][$j] += $membersTotalAmount[$eventId][$j] * $discountRate / 100;
							}
						}
					}
					else
					{
						$registrantDiscount = $quantity * $discountRate;
						if ($config->collect_member_information_in_cart)
						{
							for ($j = 0; $j < $quantity; $j++)
							{
								$membersDiscountAmount[$eventId][$j] += $discountRate;
							}
						}
					}
				}
			}

			if (($event->early_bird_discount_date != $nullDate) && $event->date_diff >= 0 && $event->early_bird_discount_amount > 0)
			{
				if ($event->early_bird_discount_type == 1)
				{
					$registrantDiscount += $registrantTotalAmount * $event->early_bird_discount_amount / 100;

					if ($config->collect_member_information_in_cart)
					{
						for ($j = 0; $j < $quantity; $j++)
						{
							$membersDiscountAmount[$eventId][$j] += $membersTotalAmount[$eventId][$j] * $event->early_bird_discount_amount / 100;
						}
					}
				}
				else
				{
					$registrantDiscount += $quantity * $event->early_bird_discount_amount;
					if ($config->collect_member_information_in_cart)
					{
						for ($j = 0; $j < $quantity; $j++)
						{
							$membersDiscountAmount[$eventId][$j] += $event->early_bird_discount_amount;
						}
					}
				}
			}

			// Coupon discount
			if (!empty($coupon) && ($coupon->times == 0 || $couponTimesAvailable > 0) && ($coupon->event_id == -1 || in_array($eventId, $couponDiscountedEventIds)))
			{
				$couponUsedCount++;
				if ($couponTimesAvailable > 0)
				{
					$couponTimesAvailable--;
				}

				if ($coupon->coupon_type == 0)
				{
					$registrantDiscount = $registrantDiscount + $registrantTotalAmount * $coupon->discount / 100;
					if ($config->collect_member_information_in_cart)
					{
						for ($j = 0; $j < $quantity; $j++)
						{
							$membersDiscountAmount[$eventId][$j] += $membersTotalAmount[$eventId][$j] * $coupon->discount / 100;
						}
					}
				}
				else
				{
					$registrantDiscount = $registrantDiscount + $coupon->discount;
					if ($config->collect_member_information_in_cart)
					{
						$membersDiscountAmount[$eventId][0] += $coupon->discount;
					}
				}

				if ($collectRecordsData)
				{
					$recordsData[$eventId]['coupon_id'] = $coupon->id;
				}
			}

			if ($registrantDiscount > $registrantTotalAmount)
			{
				$registrantDiscount = $registrantTotalAmount;
			}

			// Late Fee
			$registrantLateFee = 0;
			if (($event->late_fee_date != $nullDate) && $event->late_fee_date_diff >= 0 && $event->late_fee_amount > 0)
			{
				if ($event->late_fee_type == 1)
				{
					$registrantLateFee = $registrantTotalAmount * $event->late_fee_amount / 100;

					if ($config->collect_member_information_in_cart)
					{
						for ($j = 0; $j < $quantity; $j++)
						{
							$membersLateFee[$eventId][$j] = $membersTotalAmount[$eventId][$j] * $event->late_fee_amount / 100;
						}
					}
				}
				else
				{

					$registrantLateFee = $quantity * $event->late_fee_amount;

					if ($config->collect_member_information_in_cart)
					{
						for ($j = 0; $j < $quantity; $j++)
						{
							$membersLateFee[$eventId][$j] = $event->late_fee_amount / 100;
						}
					}
				}
			}

			if ($event->tax_rate > 0)
			{
				$registrantTaxAmount = round($event->tax_rate * ($registrantTotalAmount - $registrantDiscount + $registrantLateFee) / 100, 2);

				if ($config->collect_member_information_in_cart)
				{
					for ($j = 0; $j < $quantity; $j++)
					{
						$membersTaxAmount[$eventId][$j] = round($event->tax_rate * ($membersTotalAmount[$eventId][$j] - $membersDiscountAmount[$eventId][$j] + $membersLateFee[$eventId][$j]) / 100, 2);
					}
				}
			}
			else
			{
				$registrantTaxAmount = 0;

				for ($j = 0; $j < $quantity; $j++)
				{
					$membersTaxAmount[$eventId][$j] = 0;
				}
			}

			$registrantAmount = $registrantTotalAmount - $registrantDiscount + $registrantTaxAmount + $registrantLateFee;

			if (($paymentFeeAmount > 0 || $paymentFeePercent > 0) && $registrantAmount > 0)
			{
				$registrantPaymentProcessingFee = round($paymentFeeAmount + $registrantAmount * $paymentFeePercent / 100, 2);
				$registrantAmount += $registrantPaymentProcessingFee;
			}
			else
			{

				$registrantPaymentProcessingFee = 0;
			}

			if ($config->activate_deposit_feature && $event->deposit_amount > 0 && $paymentType == 1)
			{
				if ($event->deposit_type == 2)
				{
					$registrantDepositAmount = $event->deposit_amount * $quantity;
				}
				else
				{
					$registrantDepositAmount = round($registrantAmount * $event->deposit_amount / 100, 2);
				}
			}
			else
			{
				$registrantDepositAmount = 0;
			}
			$totalAmount += $registrantTotalAmount;
			$discountAmount += $registrantDiscount;
			$lateFee += $registrantLateFee;
			$depositAmount += $registrantDepositAmount;
			$taxAmount += $registrantTaxAmount;
			$amount += $registrantAmount;
			$paymentProcessingFee += $registrantPaymentProcessingFee;

			if ($collectRecordsData)
			{
				$recordsData[$eventId]['total_amount']           = $registrantTotalAmount;
				$recordsData[$eventId]['discount_amount']        = $registrantDiscount;
				$recordsData[$eventId]['late_fee']               = $registrantLateFee;
				$recordsData[$eventId]['tax_amount']             = $registrantTaxAmount;
				$recordsData[$eventId]['payment_processing_fee'] = $registrantPaymentProcessingFee;
				$recordsData[$eventId]['amount']                 = $registrantAmount;
				$recordsData[$eventId]['deposit_amount']         = $registrantDepositAmount;
			}
		}

		$fees['total_amount']           = $totalAmount;
		$fees['discount_amount']        = $discountAmount;
		$fees['late_fee']               = $lateFee;
		$fees['tax_amount']             = $taxAmount;
		$fees['amount']                 = $amount;
		$fees['deposit_amount']         = $depositAmount;
		$fees['payment_processing_fee'] = $paymentProcessingFee;
		if ($collectRecordsData)
		{
			$fees['records_data'] = $recordsData;
		}

		if ($config->collect_member_information_in_cart)
		{
			$fees['members_form']            = $membersForm;
			$fees['members_total_amount']    = $membersTotalAmount;
			$fees['members_discount_amount'] = $membersDiscountAmount;
			$fees['members_tax_amount']      = $membersTaxAmount;
			$fees['members_late_fee']        = $membersLateFee;
		}

		$fees['coupon_used_count'] = $couponUsedCount;

		return $fees;
	}
}