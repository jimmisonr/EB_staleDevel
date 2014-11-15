<?php
/**
 * @version        	1.6.8
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2014 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

/**
 * Event Booking Component Register Model
 *
 * @package		Joomla
 * @subpackage	Event Booking
 */
class EventBookingModelRegister extends JModelLegacy
{

	/**
	 * Process individual registration
	 *
	 * @param array $data
	 */
	function processIndividualRegistration($data)
	{
		jimport('joomla.user.helper');
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();
		$config = EventbookingHelper::getConfig();
		$row = JTable::getInstance('EventBooking', 'Registrant');
		$data['transaction_id'] = strtoupper(JUserHelper::genRandomPassword());
		if (!$user->id && $config->user_registration)
		{
			$userId = EventbookingHelper::saveRegistration($data);
			$data['user_id'] = $userId;
		}
		while (true)
		{
			$registrationCode = JUserHelper::genRandomPassword(10);
			$query->select('COUNT(*)')
				->from('#__eb_registrants')
				->where('registration_code=' . $db->quote($registrationCode));
			$db->setQuery($query);
			$total = $db->loadResult();
			if (!$total)
			{
				break;
			}
		}
		$row->registration_code = $registrationCode;
		//Calculate the payment amount
		$eventId = (int) $data['event_id'];
		$query->clear();
		$query->select('*')
			->from('#__eb_events')
			->where('id=' . $eventId);
		$db->setQuery($query);
		$event = $db->loadObject();
		$rowFields = EventbookingHelper::getFormFields($eventId, 0);
		$form = new RADForm($rowFields);
		$form->bind($data);
		$totalAmount = $event->individual_price + $form->calculateFee();
		$discountAmount = 0;
		if ($user->get('id') && EventbookingHelper::memberGetDiscount($user, $config))
		{
			if ($event->discount > 0)
			{
				if ($event->discount_type == 1)
				{
					$discountAmount = $totalAmount * $event->discount / 100;
				}
				else
				{
					$discountAmount = $event->discount;
				}
			}
		}
		
		$couponCode = isset($data['coupon_code']) ? $data['coupon_code'] : null;
		if ($couponCode)
		{
			$query->clear();
			$query->select('*')
				->from('#__eb_coupons')
				->where('published=1')
				->where('code="' . $couponCode . '"')
				->where('(valid_from="0000-00-00" OR valid_from <= NOW())')
				->where('(valid_to="0000-00-00" OR valid_to >= NOW())')
				->where('(times = 0 OR times > used)')
				->where('(event_id=0 OR event_id=' . $eventId . ')');
			$db->setQuery($query);
			$coupon = $db->loadObject();
			if ($coupon)
			{
				if ($coupon->coupon_type == 0)
				{
					$discountAmount = $discountAmount + $totalAmount * $coupon->discount / 100;
				}
				else
				{
					$discountAmount = $discountAmount + $coupon->discount;
				}
			}
		}
		
		$todayDate = JHtml::_('date', 'now', 'Y-m-d');
		$query->clear();
		$query->select('COUNT(id)')
			->from('#__eb_events')
			->where('id=' . $eventId)
			->where('DATEDIFF(early_bird_discount_date, "' . $todayDate . '") >= 0');
		$db->setQuery($query);
		$total = $db->loadResult();
		if ($total)
		{
			$earlyBirdDiscountAmount = $event->early_bird_discount_amount;
			if ($earlyBirdDiscountAmount > 0)
			{
				if ($event->early_bird_discount_type == 1)
				{
					$discountAmount = $discountAmount + $totalAmount * $event->early_bird_discount_amount / 100;
				}
				else
				{
					$discountAmount = $discountAmount + $event->early_bird_discount_amount;
				}
			}
		}
		if ($discountAmount > $totalAmount)
		{
			$discountAmount = $totalAmount;
		}
		
		if ($config->enable_tax && ($totalAmount - $discountAmount > 0))
		{
			$taxAmount = round(($totalAmount - $discountAmount) * $config->tax_rate / 100, 2);
		}
		else
		{
			$taxAmount = 0;
		}
		$amount = $totalAmount - $discountAmount + $taxAmount;
		$paymentType = JRequest::getInt('payment_type', 0);
		if ($config->activate_deposit_feature && $event->deposit_amount > 0 && $paymentType == 1)
		{
			if ($event->deposit_type == 2)
			{
				$depositAmount = $event->deposit_amount;
			}
			else
			{
				$depositAmount = $event->deposit_amount * $amount / 100;
			}
		}
		else
		{
			$depositAmount = 0;
		}
		
		$data['total_amount'] = round($totalAmount, 2);
		$data['discount_amount'] = round($discountAmount, 2);
		$data['tax_amount'] = $taxAmount;
		$data['amount'] = round($amount, 2);
		$data['deposit_amount'] = $depositAmount;
		
		$row->bind($data);
		$row->group_id = 0;
		$row->published = 0;
		$row->register_date = gmdate('Y-m-d H:i:s');
		$row->number_registrants = 1;
		if (isset($data['user_id']))
		{
			$row->user_id = $data['user_id'];
		}
		else
		{
			$row->user_id = $user->get('id');
		}
		if ($row->deposit_amount > 0)
		{
			$row->payment_status = 0;
		}
		else
		{
			$row->payment_status = 1;
		}
		
		//Save the active language
		if ($app->getLanguageFilter())
		{
			$row->language = JFactory::getLanguage()->getTag();
		}
		else
		{
			$row->language = '*';
		}
		if (isset($coupon) && $coupon)
		{
			$sql = 'UPDATE #__eb_coupons SET used = used + 1 WHERE id=' . (int) $coupon->id;
			$db->setQuery($sql);
			$db->execute();
			$row->coupon_id = $coupon->id;
		}
		$row->store();
		$form->storeData($row->id, $data);
		$data['event_title'] = $event->title;
		JPluginHelper::importPlugin('eventbooking');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAfterStoreRegistrant', array($row));
		if ($row->deposit_amount > 0)
		{
			$data['amount'] = $row->deposit_amount;
		}
		if ($row->amount > 0)
		{
			$paymentMethod = $data['payment_method'];
			require_once JPATH_COMPONENT . '/payments/' . $paymentMethod . '.php';
			$query->clear();
			$query->select('params')
				->from('#__eb_payment_plugins')
				->where('name=' . $db->quote($paymentMethod));
			$db->setQuery($query);
			$params = new JRegistry($db->loadResult());
			$paymentClass = new $paymentMethod($params);
			$paymentClass->processPayment($row, $data);
		}
		else
		{
			$Itemid = JRequest::getInt('Itemid');
			$row->payment_date = gmdate('Y-m-d H:i:s');
			$row->published = 1;
			$row->store();
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
	 * Process Group Registration
	 *
	 * @param array $data
	 */
	function processGroupRegistration($data)
	{
		jimport('joomla.user.helper');
		$app = JFactory::getApplication();
		$session = JFactory::getSession();
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$config = EventbookingHelper::getConfig();
		$row = JTable::getInstance('EventBooking', 'Registrant');
		$numberRegistrants = (int) $session->get('eb_number_registrants', '');
		$membersData = $session->get('eb_group_members_data', null);
		if ($membersData)
		{
			$membersData = unserialize($membersData);
		}
		else
		{
			$membersData = array();
		}
		$data['number_registrants'] = $numberRegistrants;
		$data['transaction_id'] = strtoupper(JUserHelper::genRandomPassword());
		if (!$user->id && $config->user_registration)
		{
			$userId = EventbookingHelper::saveRegistration($data);
			$data['user_id'] = $userId;
		}
		$eventId = (int) $data['event_id'];
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_events')
			->where('id=' . $eventId);
		$db->setQuery($query);
		$event = $db->loadObject();
		$rate = EventbookingHelper::getRegistrationRate($eventId, $numberRegistrants);
		$rowFields = EventbookingHelper::getFormFields($eventId, 1);
		$memberFormFields = EventbookingHelper::getFormFields($eventId, 2);
		$form = new RADForm($rowFields);
		$form->bind($data);
		$extraFee = $form->calculateFee();
		//Calculate members fee
		$membersForm = array();
		$membersTotalAmount = array();
		$membersDiscountAmount = array();
		$membersTaxAmount = array();
		if ($config->collect_member_information)
		{
			for ($i = 0; $i < $numberRegistrants; $i++)
			{
				$membersForm[$i] = new RADForm($memberFormFields);
				$membersForm[$i]->setFieldSuffix($i + 1);
				$membersForm[$i]->bind($membersData);
				$memberExtraFee = $membersForm[$i]->calculateFee();
				$extraFee += $memberExtraFee;
				$membersTotalAmount[$i] = $rate + $memberExtraFee;
				$membersDiscountAmount[$i] = 0;
			}
		}
		if ($event->fixed_group_price > 0)
		{
			$totalAmount = $event->fixed_group_price + $extraFee;
		}
		else
		{
			$totalAmount = $rate * $numberRegistrants + $extraFee;
		}
		$discountAmount = 0;
		#Members discount        
		if ($user->get('id') && EventbookingHelper::memberGetDiscount($user, $config))
		{
			if ($event->discount > 0)
			{
				if ($event->discount_type == 1)
				{
					$discountAmount = $totalAmount * $event->discount / 100;
					if ($config->collect_member_information)
					{
						for ($i = 0; $i < $numberRegistrants; $i++)
						{
							$membersDiscountAmount[$i] += $membersTotalAmount[$i] * $event->discount / 100;
						}
					}
				}
				else
				{
					$discountAmount = $numberRegistrants * $event->discount;
					if ($config->collect_member_information)
					{
						for ($i = 0; $i < $numberRegistrants; $i++)
						{
							$membersDiscountAmount[$i] += $event->discount;
						}
					}
				}
			}
		}
		#Coupon discount
		$couponCode = isset($data['coupon_code']) ? $data['coupon_code'] : '';
		if ($couponCode)
		{
			$query->clear();
			$query->select('*')
				->from('#__eb_coupons')
				->where('published=1')
				->where('code="' . $couponCode . '"')
				->where('(valid_from="0000-00-00" OR valid_from <= NOW())')
				->where('(valid_to="0000-00-00" OR valid_to >= NOW())')
				->where('(times = 0 OR times > used)')
				->where('(event_id=0 OR event_id=' . $eventId . ')');
			$db->setQuery($query);
			$coupon = $db->loadObject();
			if ($coupon)
			{
				if ($coupon->coupon_type == 0)
				{
					$discountAmount = $discountAmount + $totalAmount * $coupon->discount / 100;
					if ($config->collect_member_information)
					{
						for ($i = 0; $i < $numberRegistrants; $i++)
						{
							$membersDiscountAmount[$i] += $membersTotalAmount[$i] * $coupon->discount / 100;
						}
					}
				}
				else
				{
					$discountAmount = $discountAmount + $numberRegistrants * $coupon->discount;
					if ($config->collect_member_information)
					{
						for ($i = 0; $i < $numberRegistrants; $i++)
						{
							$membersDiscountAmount[$i] += $coupon->discount;
						}
					}
				}
			}
		}
		
		//Early bird discount
		$todayDate = JHtml::_('date', 'now', 'Y-m-d');
		$query->clear();
		$query->select('COUNT(id)')
			->from('#__eb_events')
			->where('id=' . $eventId)
			->where('DATEDIFF(early_bird_discount_date, "' . $todayDate . '") >= 0');
		$db->setQuery($query);
		$total = $db->loadResult();
		if ($total)
		{
			$earlyBirdDiscountAmount = $event->early_bird_discount_amount;
			if ($earlyBirdDiscountAmount > 0)
			{
				if ($event->early_bird_discount_type == 1)
				{
					$discountAmount = $discountAmount + $totalAmount * $event->early_bird_discount_amount / 100;
					if ($config->collect_member_information)
					{
						for ($i = 0; $i < $numberRegistrants; $i++)
						{
							$membersDiscountAmount[$i] += $membersTotalAmount[$i] * $event->early_bird_discount_amount / 100;
						}
					}
				}
				else
				{
					$discountAmount = $discountAmount + $numberRegistrants * $event->early_bird_discount_amount;
					
					if ($config->collect_member_information)
					{
						for ($i = 0; $i < $numberRegistrants; $i++)
						{
							$membersDiscountAmount[$i] += $event->early_bird_discount_amount;
						}
					}
				}
			}
		}
		
		if ($discountAmount > $totalAmount)
		{
			$discountAmount = $totalAmount;
		}
		if ($config->collect_member_information)
		{
			for ($i = 0; $i < $numberRegistrants; $i++)
			{
				if ($membersDiscountAmount[$i] > $membersTotalAmount[$i])
				{
					$membersDiscountAmount[$i] = $membersTotalAmount[$i];
				}
			}
		}
		
		if ($config->enable_tax && ($totalAmount - $discountAmount > 0))
		{
			$taxAmount = round(($totalAmount - $discountAmount) * $config->tax_rate / 100, 2);
			if ($config->collect_member_information)
			{
				for ($i = 0; $i < $numberRegistrants; $i++)
				{
					$membersTaxAmount[$i] = round(($membersTotalAmount[$i] - $membersDiscountAmount[$i]) * $config->tax_rate / 100, 2);
				}
			}
		}
		else
		{
			$taxAmount = 0;
			if ($config->collect_member_information)
			{
				for ($i = 0; $i < $numberRegistrants; $i++)
				{
					$membersTaxAmount[$i] = 0;
				}
			}
		}
		$amount = $totalAmount - $discountAmount + $taxAmount;
		$paymentType = (int) @$data['payment_type'];
		if ($config->activate_deposit_feature && $event->deposit_amount > 0 && $paymentType == 1)
		{
			if ($event->deposit_type == 2)
			{
				$depositAmount = $numberRegistrants * $event->deposit_amount;
			}
			else
			{
				$depositAmount = $event->deposit_amount * $amount / 100;
			}
		}
		else
		{
			$depositAmount = 0;
		}
		//The data for group billing record		
		$data['total_amount'] = $totalAmount;
		$data['discount_amount'] = $discountAmount;
		$data['tax_amount'] = $taxAmount;
		$data['deposit_amount'] = $depositAmount;
		$data['amount'] = $amount;
		if (!isset($data['first_name']))
		{
			//Get data from first member
			$firstMemberForm = new RADForm($memberFormFields);
			$firstMemberForm->setFieldSuffix(1);
			$firstMemberForm->bind($membersData);
			$firstMemberForm->removeFieldSuffix();
			$data = array_merge($data, $firstMemberForm->getFormData());
		}
		$row->bind($data);
		$row->group_id = 0;
		$row->published = 0;
		$row->register_date = gmdate('Y-m-d H:i:s');
		$row->is_group_billing = 1;
		if (isset($data['user_id']))
		{
			$row->user_id = $data['user_id'];
		}
		else
		{
			$row->user_id = $user->get('id');
		}
		if ($row->deposit_amount > 0)
		{
			$row->payment_status = 0;
		}
		else
		{
			$row->payment_status = 1;
		}
		//Save the active language
		if ($app->getLanguageFilter())
		{
			$row->language = JFactory::getLanguage()->getTag();
		}
		else
		{
			$row->language = '*';
		}
		if (isset($coupon) && $coupon->id)
		{
			$sql = 'UPDATE #__eb_coupons SET used = used + 1 WHERE id=' . (int) $coupon->id;
			$db->setQuery($sql);
			$db->execute();
			$row->coupon_id = $coupon->id;
		}
		
		while (true)
		{
			$registrationCode = JUserHelper::genRandomPassword(10);
			$query->clear();
			$query->select('COUNT(*)')
				->from('#__eb_registrants')
				->where('registration_code=' . $db->quote($registrationCode));
			$db->setQuery($query);
			$total = $db->loadResult();
			if (!$total)
			{
				break;
			}
		}
		$row->registration_code = $registrationCode;
		//Clear the coupon session    
		$row->store();
		$form->storeData($row->id, $data);
		//Store group members data
		if ($config->collect_member_information)
		{
			for ($i = 0; $i < $numberRegistrants; $i++)
			{
				$rowMember = JTable::getInstance('EventBooking', 'Registrant');
				$rowMember->group_id = $row->id;
				$rowMember->transaction_id = $row->transaction_id;
				$rowMember->event_id = $row->event_id;
				$rowMember->payment_method = $row->payment_method;
				$rowMember->user_id = $row->user_id;
				$rowMember->register_date = $row->register_date;
				$rowMember->total_amount = $membersTotalAmount[$i];
				$rowMember->discount_amount = $membersDiscountAmount[$i];
				$rowMember->tax_amount = $membersTaxAmount[$i];
				$rowMember->amount = $rowMember->total_amount - $rowMember->discount_amount + $rowMember->tax_amount;
				$rowMember->number_registrants = 1;				
				$membersForm[$i]->removeFieldSuffix();
				$memberData = $membersForm[$i]->getFormData();
				$rowMember->bind($memberData);
				$rowMember->store();
				//Store members data custom field
				$membersForm[$i]->storeData($rowMember->id, $memberData);
			}
		}
		$query->clear();
		$query->select('title')
			->from('#__eb_events')
			->where('id=' . $eventId);
		$db->setQuery($query);
		$eventTitlte = $db->loadResult();
		$data['event_title'] = $eventTitlte;
		JPluginHelper::importPlugin('eventbooking');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAfterStoreRegistrant', array($row));
		#Support deposit payment		
		if ($row->deposit_amount > 0)
		{
			$data['amount'] = $row->deposit_amount;
		}
		//Clear session data
		$session->clear('eb_number_registrants');
		$session->clear('eb_group_members_data');
		$session->clear('eb_group_billing_data');
		if ($row->amount > 0)
		{
			$paymentMethod = $data['payment_method'];
			require_once JPATH_COMPONENT . '/payments/' . $paymentMethod . '.php';
			$query->clear();
			$query->select('params')
				->from('#__eb_payment_plugins')
				->where('name=' . $db->quote($paymentMethod));
			$db->setQuery($query);
			$params = new JRegistry($db->loadResult());
			$paymentClass = new $paymentMethod($params);
			$paymentClass->processPayment($row, $data);
		}
		else
		{
			$row->payment_date = gmdate('Y-m-d H:i:s');
			$row->published = 1;
			$row->store();
			if ($row->is_group_billing)
			{
				EventbookingHelper::updateGroupRegistrationRecord($row->id);
			}
			EventbookingHelper::sendEmails($row, $config);
			JPluginHelper::importPlugin('eventbooking');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onAfterPaymentSuccess', array($row));
			$url = JRoute::_(
				'index.php?option=com_eventbooking&view=complete&registration_code=' . $row->registration_code . '&Itemid=' .
					 (int) JRequest::getInt('Itemid'), false);
			$app->redirect($url);
		}
	}

	/**
	 * Process payment confirmation
	 *
	 */
	function paymentConfirm()
	{
		$paymentMethod = JRequest::getVar('payment_method', '');
		$method = os_payments::getPaymentMethod($paymentMethod);
		if ($method)
		{
			$method->verifyPayment();
		}
	}

	/**
	 * Process registration cancellation
	 * 
	 */
	function cancelRegistration($id)
	{
		if (!$id)
		{
			return false;
		}
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$config = EventbookingHelper::getConfig();
		$row = JTable::getInstance('EventBooking', 'Registrant');
		$row->load($id);
		if (!$row->id)
		{
			return false;
		}
		//Trigger the cancellation
		JPluginHelper::importPlugin('eventbooking');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onRegistrationCancel', array($row));
		$row->published = 2;
		$row->store();
		//Update status of group members record to cancelled as well				
		if ($row->is_group_billing)
		{
			//We will need to set group members records to be cancelled
			$query->update('#__eb_registrants')
			->set('published=2')
			->where('group_id='.(int)$row->id);
			$db->setQuery($query);
			$db->execute();
			$query->clear();
		}
		elseif($row->group_id > 0)
		{
			$query->update('#__eb_registrants')
			->set('published=2')
			->where('group_id='.(int)$row->group_id.' OR id='.$row->group_id);
			$db->setQuery($query);
			$db->execute();
			$query->clear();
		}
		//Send notification email to administrator
		$app = JFactory::getApplication();
		if ($config->from_name)
		{
			$fromName = $config->from_name;
		}
		else
		{
			$fromName = $app->getCfg('fromname');
		}
		if ($config->from_email)
		{
			$fromEmail = $config->from_email;
		}
		else
		{
			$fromEmail = $app->getCfg('mailfrom');
		}
		if ($config->multiple_booking)
		{
			$rowFields = EventbookingHelper::getFormFields($row->id, 4);
		}
		elseif ($row->is_group_billing)
		{
			$rowFields = EventbookingHelper::getFormFields($row->event_id, 1);
		}
		else
		{
			$rowFields = EventbookingHelper::getFormFields($row->event_id, 0);
		}
		$form = new RADForm($rowFields);
		$data = EventbookingHelper::getRegistrantData($row, $rowFields);
		$form->bind($data);
		//Need to over-ridde some config options				
		$emailContent = EventbookingHelper::getEmailContent($config, $row, true, $form);
		$query->select('title')
			->from('#__eb_events')
			->where('id=' . $row->event_id);
		$db->setQuery($query);
		$eventTitle = $db->loadResult();
		$replaces = array();
		$replaces['event_title'] = $db->loadResult();
		//Replace the custom fields
		$fields = $form->getFields();
		foreach ($fields as $field)
		{
			if (is_string($field->value) && is_array(json_decode($field->value)))
			{
				$fieldValue = implode(', ', json_decode($field->value));
			}
			else
			{
				$fieldValue = $field->value;
			}
			$replaces[$field->name] = $fieldValue;
		}
		$replaces['amount'] = EventbookingHelper::formatAmount($row->amount, $config);
		//Notification email send to user
		$message = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		if (strlen(trim($message->{'registration_cancel_email_subject' . $fieldSuffix})))
		{
			$subject = $message->{'registration_cancel_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->registration_cancel_email_subject;
		}
		if (strlen(trim(strip_tags($message->{'registration_cancel_email_body' . $fieldSuffix}))))
		{
			$body = $message->{'registration_cancel_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->registration_cancel_email_body;
		}
		$subject = str_replace('[EVENT_TITLE]', $eventTitle, $subject);
		$body = str_replace('[REGISTRATION_DETAIL]', $emailContent, $body);
		foreach ($replaces as $key => $value)
		{
			$key = strtoupper($key);
			$body = str_replace("[$key]", $value, $body);
		}
		//Send emails to notification emails
		if (strlen(trim($event->notification_emails)) > 0)
		{
			$config->notification_emails = $event->notification_emails;
		}
		if ($config->notification_emails == '')
		{
			$notificationEmails = $fromEmail;
		}
		else
		{
			$notificationEmails = $config->notification_emails;
		}
		$notificationEmails = str_replace(' ', '', $notificationEmails);
		$emails = explode(',', $notificationEmails);
		$mailer = JFactory::getMailer();
		for ($i = 0, $n = count($emails); $i < $n; $i++)
		{
			$email = $emails[$i];
			$mailer->sendMail($fromEmail, $fromName, $email, $subject, $body, 1);
			$mailer->ClearAllRecipients();
		}
	}
} 