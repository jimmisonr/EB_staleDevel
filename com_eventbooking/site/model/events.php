<?php
/**
 * @version            2.0.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

class EventBookingModelEvents extends RADModelList
{
	/**
	 * Instantiate the model.
	 *
	 * @param array $config configuration data for the model
	 *
	 */
	public function __construct($config = array())
	{
		$config = array_merge($config, array('table' => '#__eb_events', 'remember_states' => false));

		parent::__construct($config);

		$this->state->insert('filter_category_id', 'int', 0)
			->insert('filter_search', 'string', '')
			->setDefault('filter_order_Dir', 'DESC');
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
			$rows        = parent::getData();
			$db          = $this->getDbo();
			$query       = $db->getQuery(true);
			$fieldSuffix = EventbookingHelper::getFieldSuffix();
			$query->select('a.name' . $fieldSuffix . ' AS name FROM #__eb_categories AS a')->innerJoin('#__eb_event_categories AS b ON a.id = b.category_id');
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
	 *
	 * @param JDatabaseQuery $query
	 *
	 * @return $this
	 */
	protected function buildQueryColumns(JDatabaseQuery $query)
	{
		$query->select('tbl.*')
			->select('c.name AS location_name')
			->select('IFNULL(SUM(b.number_registrants), 0) AS total_registrants');

		return $this;
	}

	/**
	 * Builds JOINS clauses for the query
	 *
	 * @param JDatabaseQuery $query
	 *
	 * @return $this
	 */
	protected function buildQueryJoins(JDatabaseQuery $query)
	{
		$query->leftJoin(
			'#__eb_registrants AS b ON (tbl.id = b.event_id AND b.group_id=0 AND (b.published = 1 OR (b.payment_method LIKE "os_offline%" AND b.published NOT IN (2,3))))')->leftJoin(
			'#__eb_locations AS c ON tbl.location_id = c.id ');

		return $this;
	}

	/**
	 * Builds a WHERE clause for the query
	 *
	 * @param JDatabaseQuery $query
	 *
	 * @return $this
	 */
	protected function buildQueryWhere(JDatabaseQuery $query)
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
	 *
	 * @param JDatabaseQuery $query
	 *
	 * @return $this
	 */
	protected function buildQueryGroup(JDatabaseQuery $query)
	{
		$query->group('tbl.id');

		return $this;
	}
}