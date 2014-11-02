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
 * EventBooking Component List  Model, a generic model allows getting list of events in different cases
 *
 * @package		Joomla
 * @subpackage	EventBooking
 */
class EventBookingModelList extends RADModelList
{

	function __construct($config = array())
	{
		$app = JFactory::getApplication();
		$config['table'] = '#__eb_events';
		parent::__construct($config);
		$ebConfig = EventbookingHelper::getConfig();
		$listLength = $ebConfig->number_events;
		if (!$listLength)
		{
			$listLength = $app->getCfg('list_limit');
		}
		$this->state->insert('id', 'int', 0)
			->insert('limit', 'int', $listLength)
			->insert('category_id', 'int', 0)
			->insert('location_id', 'int', '0')
			->insert('search', 'string', '');
		$request = EventbookingHelper::getRequestData();
		$this->state->setData($request);
		if ($ebConfig->order_events == 2)
		{
			$this->state->set('filter_order', 'tbl.event_date');
		}
		else
		{
			$this->state->set('filter_order', 'tbl.ordering');
		}
		
		if ($ebConfig->order_direction == 'desc')
		{
			$this->state->set('filter_order_Dir', 'DESC');
		}
		else
		{
			$this->state->set('filter_order_Dir', 'ASC');
		}
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
			$rows = parent::getData();
			EventbookingHelperData::calculateDiscount($rows);
			$this->data = $rows;
		}
		
		return $this->data;
	}

	/**
	 * Get the category object
	 *
	 */
	public function getCategory()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$categoryId = $this->state->id ? $this->state->id : $this->state->category_id;
		$query->select('*')
			->from('#__eb_categories')
			->where('id=' . $categoryId);
		$db->setQuery($query);
		return $db->loadObject();
	}

	/**
     * Builds SELECT columns list for the query
     */
	protected function _buildQueryColumns(JDatabaseQuery $query)
	{
		$query->select('tbl.*')
			->select('DATEDIFF(tbl.early_bird_discount_date, NOW()) AS date_diff')
			->select('c.name AS location_name, c.address AS location_address')
			->select('IFNULL(SUM(b.number_registrants), 0) AS total_registrants');
		
		return $this;
	}

	/**
     * Builds LEFT JOINS clauses for the query
     */
	protected function _buildQueryJoins(JDatabaseQuery $query)
	{
		$query->leftJoin(
			'#__eb_registrants AS b ON (tbl.id = b.event_id AND b.group_id=0 AND (b.published = 1 OR (b.payment_method LIKE "os_offline%" AND b.published != 2)))')->leftJoin(
			'#__eb_locations AS c ON tbl.location_id = c.id ');
		
		return $this;
	}

	/**
     * Builds a WHERE clause for the query
     */
	protected function _buildQueryWhere(JDatabaseQuery $query)
	{
		$db = $this->getDbo();
		$state = $this->getState();
		$hidePastEvents = EventbookingHelper::getConfigValue('hide_past_events');
		$query->where('tbl.published=1')->where('tbl.access IN (' . implode(',', JFactory::getUser()->getAuthorisedViewLevels()) . ')');
		
		if ($state->id || $state->category_id)
		{
			$categoryId = $state->id ? $state->id : $state->category_id;
			$query->where(' tbl.id IN (SELECT event_id FROM #__eb_event_categories WHERE category_id=' . $categoryId . ')');
		}
		if ($state->location_id)
		{
			$query->where('tbl.location_id=' . $state->location_id);
		}
		
		if ($state->search)
		{
			$search = $db->Quote('%' . $db->escape($state->search, true) . '%', false);
			$query->where("(LOWER(tbl.title) LIKE $search OR LOWER(tbl.short_description) LIKE $search OR LOWER(tbl.description) LIKE $search)");
		}
		$name = $this->getName();
		if ($name == 'Upcomingevents')
		{
			$query->where('DATE(tbl.event_date) >= CURDATE()');
		}
		elseif ($name == 'Archive')
		{
			$query->where('DATE(tbl.event_date) < CURDATE()');
		}
		elseif ($hidePastEvents)
		{
			$query->where('DATE(tbl.event_date) >= CURDATE()');
		}
		if (JFactory::getApplication()->getLanguageFilter())
		{
			$query->where('tbl.language IN (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')');
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