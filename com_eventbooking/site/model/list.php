<?php
/**
 * @version            2.8.1
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

class EventbookingModelList extends RADModelList
{
	/**
	 * Fields which will be returned from SQL query
	 *
	 * @var array
	 */
	public static $fields = array(
		'tbl.id',
		'tbl.location_id',
		'tbl.title',
		'tbl.event_type',
		'tbl.event_date',
		'tbl.event_end_date',
		'tbl.short_description',
		'tbl.description',
		'tbl.access',
		'tbl.registration_access',
		'tbl.individual_price',
		'tbl.event_capacity',
		'tbl.created_by',
		'tbl.cut_off_date',
		'tbl.registration_type',
		'tbl.discount_type',
		'tbl.discount',
		'tbl.early_bird_discount_type',
		'tbl.early_bird_discount_date',
		'tbl.early_bird_discount_amount',
		'tbl.enable_cancel_registration',
		'tbl.cancel_before_date',
		'tbl.params',
		'tbl.published',
		'tbl.custom_fields',
		'tbl.discount_groups',
		'tbl.discount_amounts',
		'tbl.registration_start_date',
		'tbl.registration_handle_url',
		'tbl.fixed_group_price',
		'tbl.attachment',
		'tbl.late_fee_type',
		'tbl.late_fee_date',
		'tbl.late_fee_amount',
		'tbl.event_password',
		'tbl.currency_code',
		'tbl.currency_symbol',
		'tbl.thumb',
		'tbl.language',
		'tbl.alias',
		'tbl.tax_rate',
		'tbl.featured',
		'tbl.has_multiple_ticket_types',
	);

	/**
	 * Fields which could be translated
	 *
	 * @var array
	 */
	public static $translatableFields = array(
		'tbl.title',
		'tbl.short_description',
		'tbl.description',
	);

	/**
	 * Instantiate the model.
	 *
	 * @param array $config configuration data for the model
	 */
	public function __construct($config = array())
	{
		$config['table'] = '#__eb_events';

		parent::__construct($config);

		$this->state->insert('id', 'int', 0);

		$ebConfig   = EventbookingHelper::getConfig();
		$listLength = (int) $ebConfig->number_events;

		if ($listLength)
		{
			$this->state->setDefault('limit', $listLength);
		}

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
			if ($config->show_price_including_tax)
			{
				for ($i = 0, $n = count($rows); $i < $n; $i++)
				{
					$row                    = $rows[$i];
					$taxRate                = $row->tax_rate;
					$row->individual_price  = round($row->individual_price * (1 + $taxRate / 100), 2);
					$row->fixed_group_price = round($row->fixed_group_price * (1 + $taxRate / 100), 2);
					if ($config->show_discounted_price)
					{
						$row->discounted_price = round($row->discounted_price * (1 + $taxRate / 100), 2);
					}
				}
			}

			if ($config->display_ticket_types)
			{
				foreach ($rows as $row)
				{
					if ($row->has_multiple_ticket_types)
					{
						$row->ticketTypes = EventbookingHelperData::getTicketTypes($row->id);
					}
				}
			}

			$db          = $this->getDbo();
			$query       = $db->getQuery(true);
			$fieldSuffix = EventbookingHelper::getFieldSuffix();

			$query->select('a.id, a.name, a.alias FROM #__eb_categories AS a')
				->innerJoin('#__eb_event_categories AS b ON a.id = b.category_id')
				->order('b.main_category DESC');

			if ($fieldSuffix)
			{
				EventbookingHelperDatabase::getMultilingualFields($query, array('a.name', 'a.alias'), $fieldSuffix);
			}

			foreach ($rows as $row)
			{
				$query->where('event_id=' . $row->id);
				$db->setQuery($query);
				$row->categories = $db->loadObjectList();
				$row->category_id = $row->categories[0]->id;
				$row->category_name = $row->categories[0]->name;
				$row->category_alias = $row->categories[0]->alias;

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
		$config      = EventbookingHelper::getConfig();
		$currentDate = JHtml::_('date', 'Now', 'Y-m-d H:i:s');
		$fieldSuffix = EventbookingHelper::getFieldSuffix();

		$fieldsToSelect = static::$fields;
		if ($fieldSuffix)
		{
			$fieldsToSelect = array_diff($fieldsToSelect, static::$translatableFields);
		}

		$query->select($fieldsToSelect)
			->select("DATEDIFF(tbl.early_bird_discount_date, '$currentDate') AS date_diff")
			->select("DATEDIFF('$currentDate', tbl.late_fee_date) AS late_fee_date_diff")
			->select("DATEDIFF(tbl.event_date, '$currentDate') AS number_event_dates")
			->select("TIMESTAMPDIFF(MINUTE, tbl.registration_start_date, '$currentDate') AS registration_start_minutes")
			->select("TIMESTAMPDIFF(MINUTE, tbl.cut_off_date, '$currentDate') AS cut_off_minutes")
			->select('c.name AS location_name, c.address AS location_address')
			->select('IFNULL(SUM(b.number_registrants), 0) AS total_registrants');

		if ($config->show_event_creator)
		{
			$query->select('u.name as creator_name');
		}

		if ($fieldSuffix)
		{
			EventbookingHelperDatabase::getMultilingualFields($query, array('tbl.title', 'tbl.short_description', 'tbl.description'), $fieldSuffix);
		}

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
		$config = EventbookingHelper::getConfig();

		$query->leftJoin(
			'#__eb_registrants AS b ON (tbl.id = b.event_id AND b.group_id=0 AND (b.published = 1 OR (b.payment_method LIKE "os_offline%" AND b.published NOT IN (2,3))))')->leftJoin(
			'#__eb_locations AS c ON tbl.location_id = c.id ');

		if ($config->show_event_creator)
		{
			$query->leftJoin('#__users as u ON tbl.created_by = u.id');
		}

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
		$db     = $this->getDbo();
		$user   = JFactory::getUser();
		$state  = $this->getState();
		$config = EventbookingHelper::getConfig();

		if ($config->show_children_events_under_parent_event)
		{
			$query->where('tbl.parent_id = 0');
		}

		if (!$user->authorise('core.admin', 'com_eventbooking'))
		{
			$query->where('tbl.published=1')->where('tbl.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
		}

		$categoryId = $this->state->id ? $this->state->id : $this->state->category_id;

		if ($categoryId)
		{
			if ($config->show_events_from_all_children_categories)
			{
				$childrenCategories = EventbookingHelperData::getAllChildrenCategories($categoryId);
				$query->where(' tbl.id IN (SELECT event_id FROM #__eb_event_categories WHERE category_id IN (' . implode(',', $childrenCategories) . '))');
			}
			else
			{
				$query->where(' tbl.id IN (SELECT event_id FROM #__eb_event_categories WHERE category_id = ' . $categoryId . ')');
			}
		}

		if ($state->location_id)
		{
			$query->where('tbl.location_id=' . $state->location_id);
		}

		if ($state->created_by)
		{
			$query->where('tbl.created_by =' . $state->created_by);
		}

		if ($state->search)
		{
			$search = $db->Quote('%' . $db->escape($state->search, true) . '%', false);
			$query->where("(LOWER(tbl.title) LIKE $search OR LOWER(tbl.short_description) LIKE $search OR LOWER(tbl.description) LIKE $search)");
		}
		$name = strtolower($this->getName());
		if ($name == 'archive')
		{
			$query->where('DATE(tbl.event_date) < CURDATE()');
		}
		elseif ($config->hide_past_events || ($name == 'upcomingevents'))
		{
			$currentDate = $db->quote(JHtml::_('date', 'Now', 'Y-m-d'));

			if ($config->show_children_events_under_parent_event)
			{
				$query->where('(DATE(tbl.event_date) >= ' . $currentDate . ' OR DATE(tbl.cut_off_date) >= ' . $currentDate . ' OR DATE(tbl.max_end_date) >= ' . $currentDate . ')');
			}
			else
			{
				$query->where('(DATE(tbl.event_date) >= ' . $currentDate . ' OR DATE(tbl.cut_off_date) >= ' . $currentDate . ')');
			}
		}

		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		if ($fieldSuffix)
		{
			$query->where($db->quoteName('tbl.title' . $fieldSuffix) . ' != ""')
				->where($db->quoteName('tbl.title' . $fieldSuffix) . ' IS NOT NULL');
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

	/**
	 * Builds a generic ORDER BY clause based on the model's state
	 *
	 * @param JDatabaseQuery $query
	 *
	 * @return $this
	 */
	protected function buildQueryOrder(JDatabaseQuery $query)
	{
		$sort      = $this->state->filter_order;
		$direction = strtoupper($this->state->filter_order_Dir);

		// Featured events has highest ordering
		$query->order('tbl.featured DESC');

		if ($sort)
		{
			$query->order($sort . ' ' . $direction);
		}

		return $this;
	}
}
