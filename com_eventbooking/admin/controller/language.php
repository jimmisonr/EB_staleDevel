<?php
/**
 * @version        	1.7.0
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

/**
 * EventBooking Language controller
 *
 * @package		Joomla
 * @subpackage	Event Booking
 */
class EventbookingControllerLanguage extends EventbookingController
{
	public function save() {
		$data = $this->input->getData();		
		$model = $this->getModel() ;
		$model->save($data);
		$this->setRedirect('index.php?option=com_eventbooking&view=language');
	}

	/**
	 * Cancel registration, redirect to dashboard page
	 * 	 
	 */
	public function cancel()
	{				
		$this->setRedirect('index.php?option=com_eventbooking');		
	}
}