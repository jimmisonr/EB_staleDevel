<?php
/**
 * @version        	1.6.10
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

/**
 * Event Booking Component Waiting List Model
 *
 * @package		Joomla
 * @subpackage	Event Booking
 */
class EventBookingModelWaitingList extends JModelLegacy
{

	/**
	 * Store waiting list into database
	 *
	 * @param array $data
	 */
	function store($data)
	{
		$Itemid = JRequest::getInt('Itemid');
		$config = EventbookingHelper::getConfig();
		$row = JTable::getInstance('EventBooking', 'WaitingList');
		$row->bind($data);
		$row->notified = 0;
		$row->register_date = gmdate('Y-m-d H:i:s');
		$row->user_id = JFactory::getUser()->get('id');
		$row->store();
		#Send notificaiton email here
		EventbookingHelper::sendWaitinglistEmail($row, $config);
		#Rediect to complete page		
		JFactory::getApplication()->redirect(
			JRoute::_('index.php?option=com_eventbooking&task=waitinglist_complete&id=' . $row->id . '&Itemid=' . $Itemid));
	}
} 