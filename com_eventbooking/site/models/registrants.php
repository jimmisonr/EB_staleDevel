<?php
/**
 * @version        	1.6.6
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2014 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

/**
 * EventBooking Component Registrants Model
 *
 * @package		Joomla
 * @subpackage	Event Booking
 */
class EventBookingModelRegistrants extends RADModelList
{

	function __construct($config = array())
	{
		parent::__construct($config);
		$request = EventbookingHelper::getRequestData();
		$this->state->insert('event_id', 'int', 0)
			->insert('search', 'string', '')
			->insert('published', 'int', -1)
			->insert('filter_order', 'cmd', 'tbl.register_date')
			->insert('filter_order_Dir', 'word', 'DESC');
		$this->state->setData($request);
		JFactory::getApplication()->setUserState('eventbooking.limit', $this->state->limit);
	}

	/**
	 * Method to get registrants data
	 *
	 * @access public
	 * @return array
	 */
	function getData()
	{
		if (empty($this->data))
		{
			$rows = parent::getData();
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('first_name, last_name FROM #__eb_registrants');
			for ($i = 0, $n = count($rows); $i < $n; $i++)
			{
				$row = $rows[$i];
				$query->where('id=' . $row->group_id);
				$db->setQuery($query);
				$rowGroup = $db->loadObject();
				if ($rowGroup)
				{
					$row->group_name = $rowGroup->first_name . ' ' . $rowGroup->last_name;
				}
				$query->clear('where');
			}
			$this->data = $rows;
		}
		return $this->data;
	}

	/**
	 * Builds SELECT columns list for the query
	 */
	protected function _buildQueryColumns(JDatabaseQuery $query)
	{
		$query->select('tbl.*')->select('b.title, b.event_date, c.code AS coupon_code');
		
		return $this;
	}

	/**
	 * Builds LEFT JOINS clauses for the query
	 */
	protected function _buildQueryJoins(JDatabaseQuery $query)
	{
		$query->innerJoin('#__eb_events AS b ON tbl.event_id=b.id')->leftJoin('#__eb_coupons AS c ON tbl.coupon_id = c.id');
		
		return $this;
	}

	/**
	 * Builds a WHERE clause for the query
	 */
	protected function _buildQueryWhere(JDatabaseQuery $query)
	{
		$db = $this->getDbo();
		$state = $this->getState();
		$config = EventbookingHelper::getConfig();
		if (!$config->show_pending_registrants)
		{
			$query->where('(tbl.published >=1 OR tbl.payment_method LIKE "os_offline%")');
		}
		if ($state->published != '-1')
		{
			$query->where('tbl.published = ' . $state->published);
		}
		if ($state->event_id)
		{
			$query->where('tbl.event_id=' . $state->event_id);
		}
		if ($state->search)
		{
			$search = $db->Quote('%' . $db->escape(JString::strtolower($state->search), true) . '%', false);
			$query->where(
				'(LOWER(tbl.first_name) LIKE ' . $search . ' OR LOWER(tbl.last_name) LIKE ' . $search . ' OR LOWER(tbl.transaction_id) LIKE ' . $search .
					 ') ');
		}
		if (isset($config->include_group_billing_in_registrants) && !$config->include_group_billing_in_registrants)
		{
			$query->where('tbl.is_group_billing = 0 ');
		}
		if (!$config->include_group_members_in_registrants)
		{
			$query->where('tbl.group_id = 0');
		}
		if (EB_ONLY_SHOW_REGISTRANTS_OF_EVENT_OWNER)
		{
			$query->where('tbl.event_id IN (SELECT id FROM #__eb_events WHERE created_by =' . JFactory::getUser()->id . ')');
		}
		
		return $this;
	}
}