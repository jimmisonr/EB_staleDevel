<?php
/**
 * @version            2.2.1
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

class EventbookingModelRegistrants extends RADModelList
{

	/**
	 * Instantiate the model.
	 *
	 * @param array $config configuration data for the model
	 *
	 */
	public function __construct($config = array())
	{
		$config['search_fields'] = array('tbl.first_name', 'tbl.last_name', 'tbl.email', 'tbl.transaction_id');
		$config['remember_states'] = true;
		
		parent::__construct($config);

		$this->state->insert('filter_event_id', 'int', 0)
			->insert('filter_published', 'int', -1)
			->insert('filter_checked_in', 'int', -1)
			->insert('filter_order_Dir', 'word', 'DESC');
	}

	/**
	 * Method to get registrants data
	 *
	 * @access public
	 * @return array
	 */
	public function getData()
	{
		$rows = parent::getData();
		if (count($rows))
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);

			// Get group billing records
			$billingIds = array();
			foreach ($rows as $row)
			{
				if ($row->group_id)
				{
					$billingIds[] = $row->group_id;
				}
			}

			if (count($billingIds))
			{
				$query->select('id, first_name, last_name')
					->from('#__eb_registrants')
					->where('id IN (' . implode(',', $billingIds) . ')');
				$db->setQuery($query);
				$billingRecords = $db->loadObjectList('id');
				foreach ($rows as $row)
				{
					if ($row->group_id > 0)
					{
						$billingRecord   = $billingRecords[$row->group_id];
						$row->group_name = $billingRecord->first_name . ' ' . $billingRecord->last_name;
					}
				}
			}
		}

		return $rows;
	}

	/**
	 * Builds SELECT columns list for the query
	 */
	protected function buildQueryColumns(JDatabaseQuery $query)
	{
		$query->select('tbl.*, ev.title, ev.event_date, cp.code AS coupon_code');

		return $this;
	}

	/**
	 * Builds LEFT JOINS clauses for the query
	 */
	protected function buildQueryJoins(JDatabaseQuery $query)
	{
		$query->leftJoin('#__eb_events AS ev ON tbl.event_id = ev.id')->leftJoin('#__eb_coupons AS cp ON tbl.coupon_id = cp.id');

		return $this;
	}

	/**
	 * Build where clase of the query
	 *
	 * @see RADModelList::buildQueryWhere()
	 */
	protected function buildQueryWhere(JDatabaseQuery $query)
	{
		$config = EventbookingHelper::getConfig();

		// Prevent empty registration records (spams) from being showed
		$query->where(' tbl.first_name != ""');

		if ($this->state->filter_published != -1)
		{
			$query->where(' tbl.published = ' . $this->state->filter_published);
		}

		if ($this->state->filter_checked_in != -1)
		{
			$query->where(' tbl.checked_in = ' . $this->state->filter_checked_in);
		}

		if ($this->state->filter_event_id)
		{
			$query->where(' tbl.event_id = ' . $this->state->filter_event_id);
		}

		if (!$config->show_pending_registrants)
		{
			$query->where('(tbl.published >= 1 OR tbl.payment_method LIKE "os_offline%")');
		}

		if (isset($config->include_group_billing_in_registrants) && !$config->include_group_billing_in_registrants)
		{
			$query->where(' tbl.is_group_billing = 0 ');
		}

		if (!$config->include_group_members_in_registrants)
		{
			$query->where(' tbl.group_id = 0 ');
		}

		if (JFactory::getApplication()->isSite() && $config->only_show_registrants_of_event_owner)
		{
			$query->where('tbl.event_id IN (SELECT id FROM #__eb_events WHERE created_by =' . JFactory::getUser()->id . ')');
		}

		return parent::buildQueryWhere($query);
	}

	/**
	 * Get statistic data
	 *
	 * @return array
	 */
	public static function getStatisticsData()
	{
		$data   = array();
		$config = JFactory::getConfig();
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);

		$query->select('SUM(number_registrants) AS total_registrants, SUM(amount) AS total_amount')
			->from('#__eb_registrants');

		// Today
		$date = JFactory::getDate('now', $config->get('offset'));
		$date->setTime(0, 0, 0);
		$date->setTimezone(new DateTimeZone('UCT'));
		$fromDate = $date->toSql(true);
		$date     = JFactory::getDate('now', $config->get('offset'));
		$date->setTime(23, 59, 59);
		$date->setTimezone(new DateTimeZone('UCT'));
		$toDate = $date->toSql(true);

		$query->where('group_id = 0')
			->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
			->where('register_date >= ' . $db->quote($fromDate))
			->where('register_date <=' . $db->quote($toDate));
		$db->setQuery($query);
		$row = $db->loadObject();

		$data['today'] = array(
			'total_registrants' => (int) $row->total_registrants,
			'total_amount'      => floatval($row->total_amount)
		);

		// Yesterday
		$date = JFactory::getDate('now', $config->get('offset'));
		$date->modify('-1 day');
		$date->setTime(0, 0, 0);
		$date->setTimezone(new DateTimeZone('UCT'));
		$fromDate = $date->toSql(true);
		$date     = JFactory::getDate('now', $config->get('offset'));
		$date->modify('-1 day');
		$date->setTime(23, 59, 59);
		$date->setTimezone(new DateTimeZone('UCT'));
		$toDate = $date->toSql(true);

		$query->clear('where');
		$query->where('group_id = 0')
			->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
			->where('register_date >= ' . $db->quote($fromDate))
			->where('register_date <=' . $db->quote($toDate));
		$db->setQuery($query);
		$row = $db->loadObject();

		$data['yesterday'] = array(
			'total_registrants' => (int) $row->total_registrants,
			'total_amount'      => floatval($row->total_amount)
		);

		// This week
		$date   = JFactory::getDate('now', $config->get('offset'));
		$monday = clone $date->modify(('Sunday' == $date->format('l')) ? 'Monday last week' : 'Monday this week');
		$monday->setTime(0, 0, 0);
		$monday->setTimezone(new DateTimeZone('UCT'));
		$fromDate = $monday->toSql(true);
		$sunday   = clone $date->modify('Sunday this week');
		$sunday->setTime(23, 59, 59);
		$sunday->setTimezone(new DateTimeZone('UCT'));
		$toDate = $sunday->toSql(true);

		$query->clear('where');
		$query->where('group_id = 0')
			->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
			->where('register_date >= ' . $db->quote($fromDate))
			->where('register_date <=' . $db->quote($toDate));
		$db->setQuery($query);
		$row = $db->loadObject();

		$data['this_week'] = array(
			'total_registrants' => (int) $row->total_registrants,
			'total_amount'      => floatval($row->total_amount)
		);

		// Last week, re-use data from this week
		$monday->modify('-7 day');
		$sunday->modify('-7 day');
		$fromDate = $monday->toSql(true);
		$toDate   = $sunday->toSql(true);

		$query->clear('where');
		$query->where('group_id = 0')
			->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
			->where('register_date >= ' . $db->quote($fromDate))
			->where('register_date <=' . $db->quote($toDate));
		$db->setQuery($query);
		$row = $db->loadObject();

		$data['last_week'] = array(
			'total_registrants' => (int) $row->total_registrants,
			'total_amount'      => floatval($row->total_amount)
		);

		// This month
		$date = JFactory::getDate('now', $config->get('offset'));
		$date->setDate($date->year, $date->month, 1);
		$date->setTime(0, 0, 0);
		$date->setTimezone(new DateTimeZone('UCT'));
		$fromDate = $date->toSql(true);
		$date     = JFactory::getDate('now', $config->get('offset'));
		$date->setDate($date->year, $date->month, $date->daysinmonth);
		$date->setTime(23, 59, 59);
		$date->setTimezone(new DateTimeZone('UCT'));
		$toDate = $date->toSql(true);

		$query->clear('where');
		$query->where('group_id = 0')
			->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
			->where('register_date >= ' . $db->quote($fromDate))
			->where('register_date <=' . $db->quote($toDate));
		$db->setQuery($query);
		$row = $db->loadObject();

		$data['this_month'] = array(
			'total_registrants' => (int) $row->total_registrants,
			'total_amount'      => floatval($row->total_amount)
		);

		// Last month
		$date = JFactory::getDate('first day of last month', $config->get('offset'));
		$date->setTime(0, 0, 0);
		$date->setTimezone(new DateTimeZone('UCT'));
		$fromDate = $date->toSql(true);
		$date     = JFactory::getDate('last day of last month', $config->get('offset'));
		$date->setTime(23, 59, 59);
		$date->setTimezone(new DateTimeZone('UCT'));
		$toDate = $date->toSql(true);

		$query->clear('where');
		$query->where('group_id = 0')
			->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
			->where('register_date >= ' . $db->quote($fromDate))
			->where('register_date <=' . $db->quote($toDate));
		$db->setQuery($query);
		$row = $db->loadObject();

		$data['last_month'] = array(
			'total_registrants' => (int) $row->total_registrants,
			'total_amount'      => floatval($row->total_amount)
		);

		// This year
		$date = JFactory::getDate('now', $config->get('offset'));
		$date->setDate($date->year, 1, 1);
		$date->setTime(0, 0, 0);
		$date->setTimezone(new DateTimeZone('UCT'));
		$fromDate = $date->toSql(true);
		$date     = JFactory::getDate('now', $config->get('offset'));
		$date->setDate($date->year, 12, 31);
		$date->setTime(23, 59, 59);
		$date->setTimezone(new DateTimeZone('UCT'));
		$toDate = $date->toSql(true);

		$query->clear('where');
		$query->where('group_id = 0')
			->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
			->where('register_date >= ' . $db->quote($fromDate))
			->where('register_date <=' . $db->quote($toDate));
		$db->setQuery($query);
		$row = $db->loadObject();

		$data['this_year'] = array(
			'total_registrants' => (int) $row->total_registrants,
			'total_amount'      => floatval($row->total_amount)
		);

		// Last year
		$date = JFactory::getDate('now', $config->get('offset'));
		$date->setDate($date->year - 1, 1, 1);
		$date->setTime(0, 0, 0);
		$date->setTimezone(new DateTimeZone('UCT'));
		$fromDate = $date->toSql(true);
		$date     = JFactory::getDate('now', $config->get('offset'));
		$date->setDate($date->year - 1, 12, 31);
		$date->setTime(23, 59, 59);
		$date->setTimezone(new DateTimeZone('UCT'));
		$toDate = $date->toSql(true);

		$query->clear('where');
		$query->where('group_id = 0')
			->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))')
			->where('register_date >= ' . $db->quote($fromDate))
			->where('register_date <=' . $db->quote($toDate));
		$db->setQuery($query);
		$row = $db->loadObject();

		$data['last_year'] = array(
			'total_registrants' => (int) $row->total_registrants,
			'total_amount'      => floatval($row->total_amount)
		);

		// Total registration
		$query->clear();
		$query->select('SUM(number_registrants) AS total_registrants, SUM(amount) AS total_amount')
			->from('#__eb_registrants')
			->where('group_id = 0')
			->where('(published = 1 OR (payment_method LIKE "os_offline%" AND published = 0))');
		$db->setQuery($query);
		$row = $db->loadObject();

		$data['total_registration'] = array(
			'total_registrants' => (int) $row->total_registrants,
			'total_amount'      => floatval($row->total_amount)
		);

		return $data;
	}
}