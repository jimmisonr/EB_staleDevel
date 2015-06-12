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
 * EventBooking Component List  Model, a generic model allows getting list of events in different cases
 *
 * @package        Joomla
 * @subpackage     EventBooking
 */
class EventBookingModelList extends RADModelList
{

	function __construct($config = array())
	{
		$app             = JFactory::getApplication();
		$config['table'] = '#__eb_events';
		parent::__construct($config);
		$ebConfig   = EventbookingHelper::getConfig();
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
			$config = EventbookingHelper::getConfig();
			if ($config->enable_tax && $config->show_price_including_tax)
			{
				$taxRate = $config->tax_rate;
				for ($i = 0, $n = count($rows); $i < $n; $i++)
				{
					$row                    = $rows[$i];
					$row->individual_price  = round($row->individual_price * (1 + $taxRate / 100), 2);
					$row->fixed_group_price = round($row->fixed_group_price * (1 + $taxRate / 100), 2);
					if ($config->show_discounted_price)
					{
						$row->discounted_price = round($row->discounted_price * (1 + $taxRate / 100), 2);
					}
				}
			}
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
		$db          = $this->getDbo();
		$query       = $db->getQuery(true);
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$categoryId  = $this->state->id ? $this->state->id : $this->state->category_id;
		$query->select('*, name' . $fieldSuffix . ' AS name, description' . $fieldSuffix . ' AS description')
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
		$currentDate = JHtml::_('date', 'Now', 'Y-m-d H:i:s');
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$query->select('tbl.*')
			->select('title' . $fieldSuffix . ' AS title, short_description' . $fieldSuffix . ' AS short_description, description' . $fieldSuffix . ' AS description')
			->select("DATEDIFF(tbl.early_bird_discount_date, '$currentDate') AS date_diff")
			->select("DATEDIFF(tbl.event_date, '$currentDate') AS number_event_dates")
			->select("TIMESTAMPDIFF(MINUTE, tbl.registration_start_date, '$currentDate') AS registration_start_minutes")
			->select("TIMESTAMPDIFF(MINUTE, tbl.cut_off_date, '$currentDate') AS cut_off_minutes")
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
			'#__eb_registrants AS b ON (tbl.id = b.event_id AND b.group_id=0 AND (b.published = 1 OR (b.payment_method LIKE "os_offline%" AND b.published NOT IN (2,3))))')->leftJoin(
			'#__eb_locations AS c ON tbl.location_id = c.id ');

		return $this;
	}

	/**
	 * Builds a WHERE clause for the query
	 */
	protected function _buildQueryWhere(JDatabaseQuery $query)
	{
		$db             = $this->getDbo();
		$user           = JFactory::getUser();
		$state          = $this->getState();
		$hidePastEvents = EventbookingHelper::getConfigValue('hide_past_events');
		if (!$user->authorise('core.admin', 'com_eventbooking'))
		{
			$query->where('tbl.published=1')->where('tbl.access IN (' . implode(',', JFactory::getUser()->getAuthorisedViewLevels()) . ')');
		}
		
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
		if ($name == 'Archive')
		{
			$query->where('DATE(tbl.event_date) < CURDATE()');
		}
		elseif ($hidePastEvents || ($name == 'Upcomingevents'))
		{
			$currentDate = JHtml::_('date', 'Now', 'Y-m-d');
			$query->where('DATE(tbl.event_date) >= "' . $currentDate . '"');
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