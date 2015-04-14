<?php
/**
 * @version        	1.7.2
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

/**
 * EventBooking Field controller
 *
 * @package		Joomla
 * @subpackage	Event Booking
 */
class EventbookingControllerMassmail extends RADController
{		
	/**
	 * Send massmail to registrants of an event
	 */
	public function send()
	{
		$data = $this->input->getData();
		$model = $this->getModel();
		$model->send($data);
		$this->setRedirect('index.php?option=com_eventbooking&view=massmail', JText::_('EB_EMAIL_SENT')) ;
	}
	
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_eventbooking');
	}
}