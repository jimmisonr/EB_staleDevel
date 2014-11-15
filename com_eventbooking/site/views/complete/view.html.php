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
class EventBookingViewComplete extends JViewLegacy
{

	function display($tpl = null)
	{
		$this->setLayout('default'); //Hardcoded the layout, it happens with some clients. Maybe it is a bug of Joomla core code, will find out it later
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$config = EventbookingHelper::getConfig();
		$registrationCode = JRequest::getVar('registration_code');
		if ($registrationCode)
		{
			$sql = 'SELECT id FROM #__eb_registrants WHERE registration_code="' . $registrationCode . '" ORDER BY id LIMIT 1 ';
			$db->setQuery($sql);
			$id = (int) $db->loadResult();
		}
		else
		{
			JFactory::getApplication()->redirect('index.php', JText::_('EB_INVALID_REGISTRATION_CODE'));
		}
		if (!$id)
		{
			JFactory::getApplication()->redirect('index.php', JText::_('EB_INVALID_REGISTRATION_CODE'));
		}
		$query->select('a.*, b.payment_method')
			->from('#__eb_events  AS a ')
			->innerJoin('#__eb_registrants AS b ON a.id = b.event_id')
			->where('b.id=' . $id);
		$db->setQuery($query);
		$rowEvent = $db->loadObject();
		$message = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		//Override thanks message
		if (strlen(trim(strip_tags($rowEvent->thanks_message))))
		{
			$config->thanks_message = $rowEvent->thanks_message;
		}
		if (strlen(trim(strip_tags($rowEvent->thanks_message_offline))))
		{
			$config->thanks_message_offline = $rowEvent->thanks_message_offline;
		}
		if (strpos($rowEvent->payment_method, 'os_offline') !== false)
		{
			if (strlen(trim(strip_tags($rowEvent->thanks_message_offline))))
			{
				$thankMessage = $rowEvent->thanks_message_offline;
			}
			elseif (strlen(trim(strip_tags($message->{'thanks_message_offline' . $fieldSuffix}))))
			{
				$thankMessage = $message->{'thanks_message_offline' . $fieldSuffix};
			}
			else
			{
				$thankMessage = $message->thanks_message_offline;
			}
		}
		else
		{
			if (strlen(trim(strip_tags($rowEvent->thanks_message))))
			{
				$thankMessage = $rowEvent->thanks_message;
			}
			elseif (strlen(trim(strip_tags($message->{'thanks_message' . $fieldSuffix}))))
			{
				$thankMessage = $message->{'thanks_message' . $fieldSuffix};
			}
			else
			{
				$thankMessage = $message->thanks_message;
			}
		}
		$query->clear();
		$query->select('*')
			->from('#__eb_registrants')
			->where('id=' . $id);
		$db->setQuery($query);
		$rowRegistrant = $db->loadObject();
		if ($config->multiple_booking)
		{
			$rowFields = EventbookingHelper::getFormFields($rowRegistrant->id, 4);
		}
		elseif (EventbookingHelper::isGroupRegistration($rowRegistrant->id))
		{
			$rowFields = EventbookingHelper::getFormFields($rowEvent->id, 1);
		}
		else
		{
			$rowFields = EventbookingHelper::getFormFields($rowEvent->id, 0);
		}
		$replaces = array();
		$replaces['event_date'] = JHtml::_('date', $rowEvent->event_date, $config->event_date_format, null);
		$form = new RADForm($rowFields);
		$data = EventbookingHelper::getRegistrantData($rowRegistrant, $rowFields);
		$form->bind($data);				
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
		$replaces['transaction_id'] = $rowRegistrant->transaction_id;
		$replaces['date'] = date($config->date_format);
		$replaces['short_description'] = $rowEvent->short_description;
		$replaces['description'] = $rowEvent->description;								
		$replaces['REGISTRATION_DETAIL'] = EventbookingHelper::getEmailContent($config, $rowRegistrant, false, $form);
		if ($config->multiple_booking)
		{
			$sql = 'SELECT event_id FROM #__eb_registrants WHERE id=' . $id . ' OR cart_id=' . $id . ' ORDER BY id';
			$db->setQuery($sql);
			$eventIds = $db->loadColumn();
			$sql = 'SELECT title FROM #__eb_events WHERE id IN (' . implode(',', $eventIds) . ') ORDER BY FIND_IN_SET(id, "' . implode(',', $eventIds) .
				 '")';
			$db->setQuery($sql);
			$eventTitles = $db->loadColumn();
			$eventTitle = implode(', ', $eventTitles);
			$thankMessage = str_replace('[EVENT_TITLE]', $eventTitle, $thankMessage);
			//Amount calculation
			$sql = 'SELECT SUM(total_amount) FROM #__eb_registrants WHERE id=' . $rowRegistrant->id . ' OR cart_id=' . $rowRegistrant->id;
			$db->setQuery($sql);
			$totalAmount = $db->loadResult();
			
			$sql = 'SELECT SUM(tax_amount) FROM #__eb_registrants WHERE id=' . $rowRegistrant->id . ' OR cart_id=' . $rowRegistrant->id;
			$db->setQuery($sql);
			$taxAmount = $db->loadResult();
			
			$sql = 'SELECT SUM(discount_amount) FROM #__eb_registrants WHERE id=' . $rowRegistrant->id . ' OR cart_id=' . $rowRegistrant->id;
			$db->setQuery($sql);
			$discountAmount = $db->loadResult();
			$amount = $totalAmount - $discountAmount;
			
			$replaces['total_amount'] = EventbookingHelper::formatCurrency($totalAmount, $config, $rowEvent->currency_symbol);
			$replaces['tax_amount'] = EventbookingHelper::formatCurrency($taxAmount, $config, $rowEvent->currency_symbol);
			$replaces['amount'] = EventbookingHelper::formatCurrency($amount, $config, $rowEvent->currency_symbol);
		}
		else
		{
			$thankMessage = str_replace('[EVENT_TITLE]', $rowEvent->title, $thankMessage);
			$replaces['amount'] = EventbookingHelper::formatCurrency($rowRegistrant->amount, $config, $rowEvent->currency_symbol);
		}
		foreach ($replaces as $key => $value)
		{
			$key = strtoupper($key);
			$thankMessage = str_replace("[$key]", $value, $thankMessage);
		}
		$this->message = $thankMessage;
		
		parent::display($tpl);
	}
}