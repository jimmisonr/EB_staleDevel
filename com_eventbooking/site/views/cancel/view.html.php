<?php
/**
 * @version        	1.7.1
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

/**
 * HTML View class for the Booking component
 *
 * @static
 * @package		Joomla
 * @subpackage	Events Booking
 */
class EventBookingViewCancel extends JViewLegacy
{

	function display($tpl = null)
	{
		$this->setLayout('default');
		$id = JRequest::getInt('id', 0);
		$message = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		if (strlen(trim(strip_tags($message->{'cancel_message' . $fieldSuffix}))))
		{
			$cancelMessage = $message->{'cancel_message' . $fieldSuffix};
		}
		else
		{
			$cancelMessage = $message->cancel_message;
		}
		if ($id > 0)
		{
			$db = JFactory::getDbo();
			$sql = 'SELECT b.title' . $fieldSuffix . ' AS title FROM #__eb_registrants AS a INNER JOIN #__eb_events AS b ' . ' ON a.event_id = b.id ' . ' WHERE a.id = ' . $id;
			$db->setQuery($sql);
			$title = $db->loadResult();
			$cancelMessage = str_replace('[EVENT_TITLE]', $title, $cancelMessage);
		}
		$this->assignRef('message', $cancelMessage);
		
		parent::display($tpl);
	}
}