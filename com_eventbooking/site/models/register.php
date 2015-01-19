<?php
/**
 * @version        	1.6.9
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
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
		// Calculate the payment amount
		$eventId = (int) $data['event_id'];
		$query->clear();
		$query->select('*')
			->from('#__eb_events')
			->where('id=' . $eventId);
		$db->setQuery($query);
		$event = $db->loadObject();
		$paymentMethod = isset($data['payment_method']) ? $data['payment_method'] : '';
		$rowFields = EventbookingHelper::getFormFields($eventId, 0);
		$form = new RADForm($rowFields);
		$form->bind($data);
		$fees = EventbookingHelper::calculateIndividualRegistrationFees($event, $form, $data, $config, $paymentMethod);
		$paymentType = JRequest::getInt('payment_type', 0);
		if ($paymentType == 0)
		{
			$fees['deposit_amount'] = 0;
		}
		$data['total_amount'] = round($fees['total_amount'], 2);
		$data['discount_amount'] = round($fees['discount_amount'], 2);
		$data['tax_amount'] = round($fees['tax_amount']);
		$data['amount'] = round($fees['amount'], 2);
		$data['deposit_amount'] = $fees['deposit_amount'];
		$data['payment_processing_fee'] = $fees['payment_processing_fee'];

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

		$couponCode = isset($data['coupon_code']) ? $data['coupon_code'] : null;
		if ($couponCode && $fees['coupon_valid'])
		{
			$coupon = $fees['coupon'];
			$sql = 'UPDATE #__eb_coupons SET used = used + 1 WHERE id=' . $coupon->id;
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
		$rowFields = EventbookingHelper::getFormFields($eventId, 1);
		$memberFormFields = EventbookingHelper::getFormFields($eventId, 2);
		$form = new RADForm($rowFields);
		$form->bind($data);

		$paymentMethod = isset($data['payment_method']) ? $data['payment_method'] : '';
		$fees = EventbookingHelper::calculateGroupRegistrationFees($event, $form, $data, $config, $paymentMethod);
		//Calculate members fee
		$membersForm = $fees['members_form'];
		$membersTotalAmount = $fees['members_total_amount'];
		$membersDiscountAmount = $fees['members_discount_amount'];
		$membersTaxAmount = $fees['members_tax_amount'];
		$paymentType = (int) @$data['payment_type'];
		if ($paymentType == 0)
		{
			$fees['deposit_amount'] = 0;
		}
		//The data for group billing record		
		$data['total_amount'] = $fees['total_amount'];
		$data['discount_amount'] = $fees['discount_amount'];
		$data['tax_amount'] = $fees['tax_amount'];
		$data['deposit_amount'] = $fees['deposit_amount'];
		$data['payment_processing_fee'] = $fees['payment_processing_fee'];
		$data['amount'] = $fees['amount'];
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

		// Save the active language
		if ($app->getLanguageFilter())
		{
			$row->language = JFactory::getLanguage()->getTag();
		}
		else
		{
			$row->language = '*';
		}

		// Unique registration code for the registration
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

		// Coupon code
		$couponCode = isset($data['coupon_code']) ? $data['coupon_code'] : null;
		if ($couponCode && $fees['coupon_valid'])
		{
			$coupon = $fees['coupon'];
			$sql = 'UPDATE #__eb_coupons SET used = used + 1 WHERE id=' . $coupon->id;
			$db->setQuery($sql);
			$db->execute();
			$row->coupon_id = $coupon->id;
		}

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
		$data['event_title'] = $event->title;

		// Trigger onAfterStoreRegistrant event
		JPluginHelper::importPlugin('eventbooking');
		$dispatcher = JDispatcher::getInstance();
		$dispatcher->trigger('onAfterStoreRegistrant', array($row));

		// Support deposit payment
		if ($row->deposit_amount > 0)
		{
			$data['amount'] = $row->deposit_amount;
		}

		// Clear session data
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
		$query->clear();
		$query->select('*')
			->from('#__eb_events')
			->where('id = '. (int) $row->event_id);
		$db->setQuery($query);
		$event = $db->loadObject();
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
		$query->clear();
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