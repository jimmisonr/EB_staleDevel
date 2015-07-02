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
 * Event Booking Component Locations Model
 *
 * @package		Joomla
 * @subpackage	Event Booking
 */
class EventBookingModelLocationlist extends RADModelList
{

	function __construct($config = array())
	{
		$config = array_merge($config, array('table' => '#__eb_locations'));
		parent::__construct($config);
		$request = EventbookingHelper::getRequestData();
		$this->state->setData($request);
		$this->state->set('filter_order_Dir', 'DESC');
		JFactory::getApplication()->setUserState('eventbooking.limit', $this->state->limit);
	}

	/**
	 * Builds a WHERE clause for the query
	 */
	protected function _buildQueryWhere(JDatabaseQuery $query)
	{
		$query->where('tbl.user_id=' . (int) JFactory::getUser()->id);
		
		return $this;
	}
}