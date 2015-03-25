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
 * EventBooking Message controller
 *
 * @package		Joomla
 * @subpackage	Event Booking
 */
class EventbookingControllerMessage extends EventbookingController
{

	public function save()
	{
		$data = $this->input->getData();
		$model = $this->getModel();
		$model->store($data);
		$this->setRedirect('index.php?option=com_eventbooking&view=message', JText::_('EB_MESSAGES_SAVED'));
	}
	
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_eventbooking');
	}
}