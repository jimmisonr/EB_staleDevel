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
 * EventBooking Component Registrantration History Model
 *
 * @package		Joomla
 * @subpackage	Event Booking
 */
class EventBookingModelHistory extends RADModelList
{

	function __construct($config = array())
	{
		$config = array_merge($config, array('table' => '#__eb_registrants'));
		parent::__construct($config);
		$request = EventbookingHelper::getRequestData();
		$this->state->insert('event_id', 'int', 0)
			->insert('search', 'string', '')
			->insert('filter_order', 'cmd', 'tbl.register_date')
			->insert('filter_order_Dir', 'word', 'DESC');
		$this->state->setData($request);
		JFactory::getApplication()->setUserState('eventbooking.limit', $this->state->limit);
	}

	public function getTotal()
	{
		if (empty($this->total))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('COUNT(*)');
			$this->_buildQueryFrom($query)
				->_buildQueryJoins($query)
				->_buildQueryWhere($query);
			$db->setQuery($query);
			$this->total = (int) $db->loadResult();
		}
		
		return $this->total;
	}

	/**
	 * Builds SELECT columns list for the query
	 */
	protected function _buildQueryColumns(JDatabaseQuery $query)
	{
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$query->select('tbl.*')->select('b.title' . $fieldSuffix . ' AS title, b.event_date');
		return $this;
	}

	/**
	 * Builds LEFT JOINS clauses for the query
	 */
	protected function _buildQueryJoins(JDatabaseQuery $query)
	{
		$query->innerJoin('#__eb_events AS b ON tbl.event_id=b.id');
		
		return $this;
	}

	/**
	 * Builds a WHERE clause for the query
	 */
	protected function _buildQueryWhere(JDatabaseQuery $query)
	{
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$state = $this->getState();
		$config = EventbookingHelper::getConfig();
		$query->where('(tbl.published=1 OR tbl.payment_method LIKE "os_offline%")')->where(
			'(tbl.user_id =' . $user->get('id') . ' OR tbl.email="' . $user->get('email') . '")');
		if ($state->event_id)
		{
			$query->where('tbl.event_id=' . $state->event_id);
		}
		if ($state->search)
		{
			$search = $db->Quote('%' . $db->escape($state->search, true) . '%', false);
			$query->where('LOWER(b.title) LIKE ' . $search);
		}		
		if (isset($config->include_group_billing_in_registrants) && !$config->include_group_billing_in_registrants)
		{
			$query->where('tbl.is_group_billing = 0 ');
		}
		if (!$config->include_group_members_in_registrants)
		{
			$query->where('tbl.group_id = 0');
		}
		
		return $this;
	}
}