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
 * EventBooking Field controller
 *
 * @package		Joomla
 * @subpackage	Event Booking
 */
class EventbookingControllerField extends EventbookingController
{
	public function __construct($config)
	{
		parent::__construct($config);
		
		$this->registerTask('un_required', 'required');
	}
	/**
	 * Require the selected fields
	 *
	 */
	function required() {
		$cid = $this->input->get('cid', array(), 'array');
		JArrayHelper::toInteger($cid);
		$task = $this->getTask();
		if ($task == 'required')
			$state = 1;
		else 
			$state = 0;
		$model = $this->getModel();			
		$model->required($cid , $state);
		$msg = JText::_('EB_FIELD_REQUIRED_STATE_UPDATED');		
		$this->setRedirect(JRoute::_('index.php?option=com_eventbooking&view=fields', false), $msg);
	}		
}