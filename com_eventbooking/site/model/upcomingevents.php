<?php
/**
 * @version        	2.0.0
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

/**
 * EventBooking Component Up-coming events Model
 *
 * @package		Joomla
 * @subpackage	EventBooking
 */
class EventbookingModelUpcomingevents extends EventbookingModelList
{

	public function __construct($config = array())
	{
		parent::__construct($config);
		
		$this->state->set('filter_order', 'tbl.event_date');
		$this->state->set('filter_order_Dir', 'ASC');
	}
}