<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2017 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

class EventbookingViewCompleteHtml extends RADViewHtml
{
	public $hasModel = false;

	/**
	 * Display the view
	 *
	 * @throws Exception
	 */
	public function display()
	{
		//Hardcoded the layout, it happens with some clients. Maybe it is a bug of Joomla core code, will find out it later
		$this->setLayout('default');
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$config = EventbookingHelper::getConfig();

		$session          = JFactory::getSession();
		$registrationCode = $session->get('eb_registration_code', '');

		$id = 0;
		if ($registrationCode)
		{
			$query->select('id')
				->from('#__eb_registrants')
				->where('registration_code = ' . $db->quote($registrationCode));
			$db->setQuery($query);
			$id = (int) $db->loadResult();
		}

		if (!$id)
		{
			JFactory::getApplication()->redirect('index.php', JText::_('EB_INVALID_REGISTRATION_CODE'));
		}

		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$query->clear()
			->select('a.*, b.payment_method')
			->from('#__eb_events  AS a ')
			->innerJoin('#__eb_registrants AS b ON a.id = b.event_id')
			->where('b.id=' . $id);
		if ($fieldSuffix)
		{
			EventbookingHelperDatabase::getMultilingualFields($query, array('a.title'), $fieldSuffix);
		}
		$db->setQuery($query);
		$rowEvent = $db->loadObject();

		$message = EventbookingHelper::getMessages();
		if (strpos($rowEvent->payment_method, 'os_offline') !== false)
		{
			$offlineSuffix = str_replace('os_offline', '', $rowEvent->payment_method);

			if ($fieldSuffix && EventbookingHelper::isValidMessage($rowEvent->{'thanks_message_offline' . $fieldSuffix}))
			{
				$thankMessage = $rowEvent->{'thanks_message_offline' . $fieldSuffix};
			}
			elseif ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'thanks_message_offline' . $fieldSuffix}))
			{
				$thankMessage = $message->{'thanks_message_offline' . $fieldSuffix};
			}
			elseif (EventbookingHelper::isValidMessage($rowEvent->thanks_message_offline))
			{
				$thankMessage = $rowEvent->thanks_message_offline;
			}
			elseif (EventbookingHelper::isValidMessage($rowEvent->thanks_message_offline))
			{

			}
			else
			{
				$thankMessage = $message->thanks_message_offline;
			}
		}
		else
		{
			if ($fieldSuffix && EventbookingHelper::isValidMessage($rowEvent->{'thanks_message' . $fieldSuffix}))
			{
				$thankMessage = $rowEvent->{'thanks_message' . $fieldSuffix};
			}
			elseif ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'thanks_message' . $fieldSuffix}))
			{
				$thankMessage = $message->{'thanks_message' . $fieldSuffix};
			}
			elseif (EventbookingHelper::isValidMessage($rowEvent->thanks_message))
			{
				$thankMessage = $rowEvent->thanks_message;
			}
			else
			{
				$thankMessage = $message->thanks_message;
			}
		}

		$query->clear()
			->select('*')
			->from('#__eb_registrants')
			->where('id=' . $id);
		$db->setQuery($query);
		$rowRegistrant = $db->loadObject();

		if ($rowRegistrant->published == 0 && ($rowRegistrant->payment_method == 'os_ideal'))
		{
			// Use online payment method and the payment is not success for some reason, we need to redirec to failure page
			$Itemid     = JFactory::getApplication()->input->getInt('Itemid', 0);
			$failureUrl = JRoute::_('index.php?option=com_eventbooking&view=failure&id=' . $rowRegistrant->id . '&Itemid=' . $Itemid, false, false);
			JFactory::getApplication()->redirect($failureUrl, 'Something went wrong, you are NOT successfully registered');
		}

		if ($config->multiple_booking)
		{
			$rowFields = EventbookingHelper::getFormFields($rowRegistrant->id, 4);
		}
		elseif ($rowRegistrant->is_group_billing)
		{
			$rowFields = EventbookingHelper::getFormFields($rowEvent->id, 1);
		}
		else
		{
			$rowFields = EventbookingHelper::getFormFields($rowEvent->id, 0);
		}
		$form = new RADForm($rowFields);
		$data = EventbookingHelper::getRegistrantData($rowRegistrant, $rowFields);
		$form->bind($data);
		$form->buildFieldsDependency();

		if (is_callable('EventbookingHelperOverrideHelper::buildTags'))
		{
			$replaces = EventbookingHelperOverrideHelper::buildTags($rowRegistrant, $form, $rowEvent, $config, false);
		}
		else
		{
			$replaces = EventbookingHelper::buildTags($rowRegistrant, $form, $rowEvent, $config, false);
		}

		foreach ($replaces as $key => $value)
		{
			$key          = strtoupper($key);
			$thankMessage = str_ireplace("[$key]", $value, $thankMessage);
		}

		if (strpos($thankMessage, '[QRCODE]') !== false)
		{
			EventbookingHelper::generateQrcode($rowRegistrant->id);
			$imgTag       = '<img src="media/com_eventbooking/qrcodes/' . $rowRegistrant->id . '.png" border="0" />';
			$thankMessage = str_ireplace("[QRCODE]", $imgTag, $thankMessage);
		}

		$trackingCode = $config->conversion_tracking_code;
		if (!empty($trackingCode))
		{
			foreach ($replaces as $key => $value)
			{
				$key          = strtoupper($key);
				$trackingCode = str_ireplace("[$key]", $value, $trackingCode);
			}
		}

		$this->message                = $thankMessage;
		$this->registrationCode       = $registrationCode;
		$this->print                  = $this->input->getInt('print', 0);
		$this->conversionTrackingCode = $trackingCode;
		$this->showPrintButton        = $config->get('show_print_button', '1');

		// Reset cart
		$cart = new EventbookingHelperCart();
		$cart->reset();

		parent::display();
	}
}
