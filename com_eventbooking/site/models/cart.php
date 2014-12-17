<?php
/**
 * @version        	1.6.9
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2014 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

/**
 * Event Booking Component Cart Model
 *
 * @package		Joomla
 * @subpackage	Event Booking
 */
class EventBookingModelCart extends JModelLegacy
{	
	/**
	 * Add one or multiple events to cart	 
	 * @param string
	 */
	function processAddToCart($data)
	{
		if (is_array($data['id']))
		{
			$eventIds = $data['id'];
		}
		else
		{
			$eventIds = array($data['id']);
		}
		$cart = new EventbookingHelperCart();
		$cart->addEvents($eventIds);
		return true;
	}

	/**
	 * Update cart with new quantities	 
	 * @param array $eventIds
	 * @param array $quantities
	 */
	function processUpdateCart($eventIds, $quantities)
	{
		$cart = new EventbookingHelperCart();
		$cart->updateCart($eventIds, $quantities);
		return true;
	}

	/**
	 * Remove an event from cart
	 * Enter description here ...
	 * @param int $id
	 */
	function removeEvent($id)
	{
		$cart = new EventbookingHelperCart();
		$cart->remove($id);
		return true;
	}

	/**
	 * Process checkout in case customer using shopping cart feature	 
	 * @param array $data
	 */
	function processCheckout(&$data)
	{
		jimport('joomla.user.helper');
		$app = JFactory::getApplication();
		$Itemid = JRequest::getInt('Itemid');		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$nullDate = $db->getNullDate();
		$user = JFactory::getUser();
		$config = EventbookingHelper::getConfig();
		$row = JTable::getInstance('EventBooking', 'Registrant');		
		$data['transaction_id'] = strtoupper(JUserHelper::genRandomPassword());
		$cart = new EventbookingHelperCart();
		$items = $cart->getItems();
		$quantities = $cart->getQuantities();
		$paymentType = JRequest::getInt('payment_type', 0);
		if (!$user->id && $config->user_registration)
		{
			$userId = EventbookingHelper::saveRegistration($data);
			$data['user_id'] = $userId;
		}
		$rowFields = EventbookingHelper::getFormFields(0, 4);
		$form = new RADForm($rowFields);
		$form->bind($data);	
		$feeAmount = $form->calculateFee();
		$totalAmount = 0;
		$totalDiscount = 0;
		$registrantIds = array();
		$depositAmount = 0;
		//Save the active language
		if ($app->getLanguageFilter())
		{
			$language = JFactory::getLanguage()->getTag();
		}
		else
		{
			$language = '*';
		}

		$paymentMethod = isset($data['payment_method']) ? $data['payment_method'] : '';
		$paymentFeeAmount  = 0;
		$paymentFeePercent = 0;
		if ($paymentMethod)
		{
			$method            = os_payments::loadPaymentMethod($paymentMethod);
			$params            = new JRegistry($method->params);
			$paymentFeeAmount  = (float) $params->get('payment_fee_amount');
			$paymentFeePercent = (float) $params->get('payment_fee_percent');
		}

		$paymentProcessingFee = 0;
		//Store list of registrants
		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			
			$eventId = $items[$i];
			$query->clear();
			$query->select('*')
				->from('#__eb_events')
				->where('id='.$eventId);			
			$db->setQuery($query);
			$event = $db->loadObject();
			$quantity = $quantities[$i];
			$rate = EventbookingHelper::getRegistrationRate($eventId, $quantity);
			$registrantTotalAmount = $rate * $quantity;

			// Calculate discount
			$registrantDiscount = 0;

			if ($user->id)
			{
				$discountRate = EventbookingHelper::calculateMemberDiscount($event->discount_amounts, $event->discount_groups);
				if ($discountRate > 0)
				{
					if ($event->discount_type == 1)
					{
						$registrantDiscount = $registrantTotalAmount * $discountRate / 100;
					}
					else
					{
						$registrantDiscount = $quantity * $discountRate;
					}
				}
			}

			if ($config->activate_deposit_feature && $event->deposit_amount > 0 && $paymentType == 1)
			{
				if ($event->deposit_type == 2)
				{
					$registrantDepositAmount = $event->deposit_amount * $quantity;
				}
				else
				{
					$registrantDepositAmount = round($registrantTotalAmount * $event->deposit_amount / 100, 2);
				}
			}
			else
			{
				$registrantDepositAmount = 0;
			}
			$depositAmount += $registrantDepositAmount;
			//Calculate the coupon discount
			if (isset($_SESSION['coupon_id']))
			{
				$query->clear();
				$query->select('*')
					->from('#__eb_coupons')
					->where('id='.(int) $_SESSION['coupon_id']);								
				$db->setQuery($query);
				$coupon = $db->loadObject();
				if ($coupon && ($coupon->event_id == 0 || $coupon->event_id == $eventId))
				{
					if ($coupon->coupon_type == 0)
					{
						$registrantDiscount = $registrantDiscount + $registrantTotalAmount * $coupon->discount / 100;
					}
					else
					{
						$registrantDiscount = $registrantDiscount + $coupon->discount;
					}
				}
				$row->coupon_id = (int) $_SESSION['coupon_id'];
			}
			#Early bird discount
			if (($event->early_bird_discount_amount > 0) && ($event->early_bird_discount_date != $nullDate) &&
				 (strtotime($event->early_bird_discount_date) >= mktime()))
			{
				if ($event->early_bird_discount_type == 1)
				{
					$registrantDiscount += $registrantTotalAmount * $event->early_bird_discount_amount / 100;
				}
				else
				{
					$registrantDiscount += $event->early_bird_discount_amount;
				}
			}
			if ($registrantDiscount > $registrantTotalAmount)
			{
				$registrantDiscount = $registrantTotalAmount;
			}				
			$totalAmount += $registrantTotalAmount;
			$totalDiscount += $registrantDiscount;
			if ($i == 0)
			{
				$data['total_amount'] = $registrantTotalAmount + $feeAmount;
			}
			else
			{
				$data['total_amount'] = $registrantTotalAmount;
			}
			$data['discount_amount'] = $registrantDiscount;
			$data['deposit_amount'] = $registrantDepositAmount;					
			if ($config->enable_tax && $config->tax_rate > 0)
			{
				$data['tax_amount'] = round($config->tax_rate * ($data['total_amount'] - $data['discount_amount']) / 100, 2);
			}
			else
			{
				$data['tax_amount'] = 0;
			}			
			$data['amount'] = $data['total_amount'] - $registrantDiscount + $data['tax_amount'];
			if (($paymentFeeAmount > 0 || $paymentFeePercent > 0) && $data['amount'] > 0)
			{
				$registrantPaymentProcessingFee         = round($paymentFeeAmount + $data['amount']*$paymentFeePercent / 100, 2);
				$data['payment_processing_fee'] = $registrantPaymentProcessingFee;
				$data['amount'] += $registrantPaymentProcessingFee;
				$paymentProcessingFee += $registrantPaymentProcessingFee;
			}
			else
			{

				$data['payment_processing_fee'] = 0;
			}
			if ($registrantDepositAmount > 0)
			{
				$data['payment_status'] = 0;
			}
			else
			{
				$data['payment_status'] = 1;
			}
			$data['event_id'] = $eventId;
			$row->bind($data);
			$row->group_id = 0;
			$row->published = 0;
			$row->register_date = gmdate('Y-m-d H:i:s');
			if (isset($data['user_id']))
			{
				$row->user_id = $data['user_id'];
			}				
			else
			{
				$row->user_id = $user->get('id');
			}				
			$row->number_registrants = $quantity;
			$row->event_id = $eventId;
			if ($i == 0)
			{
				$row->cart_id = 0;
				//Store registration code
				while (true)
				{
					$registrationCode = JUserHelper::genRandomPassword(10);
					$query->clear();
					$query->select('COUNT(*)')
					->from('#__eb_registrants')
					->where('registration_code='.$db->quote($registrationCode));
					$db->setQuery($query);
					$total = $db->loadResult();
					if (!$total)
					{
						break;
					}										
				}
				$row->registration_code = $registrationCode;				
			}
			else
			{
				$row->cart_id = $registrantIds[0];
			}
			$row->id = 0;
			$row->language = $language;
			$row->store();
			$form->storeData($row->id, $data);						
			$registrantIds[] = $row->id;
			JPluginHelper::importPlugin('eventbooking');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAfterStoreRegistrant', array($row));
		}
		$sql = 'SELECT title FROM #__eb_events WHERE id IN (' . implode(',', $items) . ') ORDER BY FIND_IN_SET(id, "' . implode(',', $items) . '")';
		$db->setQuery($sql);
		$eventTitltes = $db->loadColumn();
		$data['event_title'] = implode(', ', $eventTitltes);
		//Now, we will need to creat registrants for each events										
		//Clear the coupon session
		if (isset($_SESSION['coupon_id']))
		{
			$sql = 'UPDATE #__eb_coupons SET used = used + 1 WHERE id=' . (int) $_SESSION['coupon_id'];
			$db->setQuery($sql);
			$db->execute();
			unset($_SESSION['coupon_id']);
		}
		$cart->reset();
		$totalAmount += $feeAmount;
		$amount = $totalAmount - $totalDiscount;
		if ($config->enable_tax && $config->tax_rate > 0)
		{
			$taxAmount = round($amount * $config->tax_rate / 100, 2);
		}
		else
		{
			$taxAmount = 0;
		}
		$amount = $totalAmount - $totalDiscount + $taxAmount + $paymentProcessingFee;
		// Payment processing fee
		if ($amount > 0)
		{
			if ($depositAmount > 0)
			{
				$data['amount'] = $depositAmount;
			}				
			else
			{
				$data['amount'] = $amount;
			}				
			$row->load($registrantIds[0]);
			$paymentMethod = $data['payment_method'];
			require_once JPATH_COMPONENT . '/payments/' . $paymentMethod . '.php';
			$query->clear();
			$query->select('params')
			->from('#__eb_payment_plugins')
			->where('name='.$db->quote($paymentMethod));
			$db->setQuery($query);			
			$params = new JRegistry($db->loadResult());
			$paymentClass = new $paymentMethod($params);
			$paymentClass->processPayment($row, $data);
		}
		else
		{
			$row->load($registrantIds[0]);
			$row->payment_date = gmdate('Y-m-d H:i:s');
			$row->published = 1;
			$row->store();
			//Update status of all registrants
			$sql = 'UPDATE #__eb_registrants SET published=1, payment_date=NOW() WHERE cart_id=' . $row->id;
			$db->setQuery($sql);
			$db->execute();
			EventbookingHelper::sendEmails($row, $config);
			JPluginHelper::importPlugin('eventbooking');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAfterPaymentSuccess', array($row));
			$url = JRoute::_('index.php?option=com_eventbooking&view=complete&registration_code=' . $row->registration_code . '&Itemid=' . $Itemid, 
				false);
			$app->redirect($url);
		}
	}

	/**
	 * 
	 * Enter description here ...
	 */
	function getData()
	{
		$cart = new EventbookingHelperCart();
		return $cart->getEvents();
	}
} 