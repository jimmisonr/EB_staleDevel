<?php
/**
 * @version        	1.7.4
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
require_once dirname(__FILE__) . '/list.php';

/**
 * EventBooking Component Category Model
 *
 * @package		Joomla
 * @subpackage	EventBooking
 */
class EventBookingModelLocation extends EventBookingModelList
{

	function __construct($config = array())
	{
		parent::__construct($config);
	}

	/**
	 * Get list of category
	 *
	 */
	function getLocation()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_locations')
			->where('id=' . $this->state->location_id);
		$db->setQuery($query);
		return $db->loadObject();
	}
} 