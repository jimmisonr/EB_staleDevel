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
class EventBookingViewRegistrationCancel extends JViewLegacy
{

	function display($tpl = null)
	{
		$this->setLayout('default');
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$id = JRequest::getInt('id', 0);
		if (!$id)
		{
			return;
		}
		$message = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$query->select('*')
			->from('#__eb_registrants')
			->where('id=' . $id);
		$db->setQuery($query);
		$row = $db->loadObject();
		if ($row->amount > 0)
		{
			if (strlen(trim(strip_tags($message->{'registration_cancel_message_paid' . $fieldSuffix}))))
			{
				$cancelMessage = $message->{'registration_cancel_message_paid' . $fieldSuffix};
			}
			else
			{
				$cancelMessage = $message->registration_cancel_message_paid;
			}
		}
		else
		{
			if (strlen(trim(strip_tags($message->{'registration_cancel_message_free' . $fieldSuffix}))))
			{
				$cancelMessage = $message->{'registration_cancel_message_free' . $fieldSuffix};
			}
			else
			{
				$cancelMessage = $message->registration_cancel_message_free;
			}
		}
		$query->clear();
		$query->select('a.title')
			->from('#__eb_events AS a')
			->innerJoin('#__eb_registrants AS b ON a.id = b.event_id')
			->where('b.id=' . $id);
		$db->setQuery($query);
		$title = $db->loadResult();
		$cancelMessage = str_replace('[EVENT_TITLE]', $title, $cancelMessage);
		$this->message = $cancelMessage;
		parent::display($tpl);
	}
}