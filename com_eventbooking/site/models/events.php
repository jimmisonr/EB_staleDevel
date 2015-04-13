<?php
/**
 * @version            1.6.6
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

/**
 * Events Booking Events Model
 *
 * @package        Joomla
 * @subpackage     Events Booking
 */
class EventBookingModelEvents extends RADModelList
{

	function __construct($config = array())
	{
		$config = array_merge($config, array('table' => '#__eb_events', 'ignore_session' => true));
		parent::__construct($config);

		$app     = JFactory::getApplication();
		$context = $this->option . '.' . $this->name . '.';
		$this->state->insert('filter_category_id', 'int', $app->getUserStateFromRequest($context . 'filter_category_id', 'filter_category_id', 0))
			->insert('filter_search', 'string', $app->getUserStateFromRequest($context . 'filter_search', 'filter_search'));
		$request = EventbookingHelper::getRequestData();
		$this->state->setData($request);
		$this->state->set('filter_order_Dir', 'DESC');

		$app->setUserState('eventbooking.limit', $this->state->limit);
	}

	/**
	 * Method to get Events data
	 *
	 * @access public
	 * @return array
	 */
	public function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->data))
		{
			$rows  = parent::getData();
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$fieldSuffix = EventbookingHelper::getFieldSuffix();
			$query->select('a.name'.$fieldSuffix.' AS name FROM #__eb_categories AS a')->innerJoin('#__eb_event_categories AS b ON a.id = b.category_id');
			for ($i = 0, $n = count($rows); $i < $n; $i++)
			{
				$row = $rows[$i];
				$query->where('event_id=' . $row->id);
				$db->setQuery($query);
				$row->category_name = implode(' | ', $db->loadColumn());
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
		$query->select('tbl.*')
			->select('c.name AS location_name')
			->select('IFNULL(SUM(b.number_registrants), 0) AS total_registrants');

		return $this;
	}

	/**
	 * Builds LEFT JOINS clauses for the query
	 */
	protected function _buildQueryJoins(JDatabaseQuery $query)
	{
		$query->leftJoin(
			'#__eb_registrants AS b ON (tbl.id = b.event_id AND b.group_id=0 AND (b.published = 1 OR (b.payment_method LIKE "os_offline%" AND b.published NOT IN (2,3))))')->leftJoin(
				'#__eb_locations AS c ON tbl.location_id = c.id ');

		return $this;
	}

	/**
	 * Builds a WHERE clause for the query
	 */
	protected function _buildQueryWhere(JDatabaseQuery $query)
	{
		$query->where('tbl.created_by=' . (int) JFactory::getUser()->id);
		if ($this->state->filter_category_id)
		{
			$query->where('tbl.id IN (SELECT event_id FROM #__eb_event_categories WHERE category_id=' . $this->state->filter_category_id . ')');
		}
		if ($this->state->filter_search)
		{
			$db     = $this->getDbo();
			$search = $db->Quote('%' . $db->escape($this->state->filter_search, true) . '%', false);
			$query->where('LOWER(tbl.title) LIKE ' . $search);
		}


		return $this;
	}

	/**
	 * Builds a GROUP BY clause for the query
	 */
	protected function _buildQueryGroup(JDatabaseQuery $query)
	{
		$query->group('tbl.id');

		return $this;
	}
}