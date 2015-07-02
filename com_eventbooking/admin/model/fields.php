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
class EventbookingModelFields extends RADModelList
{

	/**
	 * Constructor function	 
	 */
	function __construct($config)
	{
		parent::__construct($config);
		
		$app = JFactory::getApplication();
		$context = $this->option . '.' . $this->name . '.';
		$this->state->insert('filter_category_id', 'int', $app->getUserStateFromRequest($context . 'filter_category_id', 'filter_category_id', 0))
			->insert('filter_event_id', 'int', $app->getUserStateFromRequest($context . 'filter_event_id', 'filter_event_id', 0))
			->insert('filter_show_core_fields', 'int', $app->getUserStateFromRequest($context . 'filter_show_core_fields', 'filter_show_core_fields', 0));
	}

	/**
	 * Builds a WHERE clause for the query
	 */
	protected function _buildQueryWhere(JDatabaseQuery $query)
	{
		$state = $this->state;
		if ($state->filter_category_id)
		{
			$query->where('tbl.category_id=' . $state->filter_category_id);
		}
		if ($state->filter_event_id)
		{
			$query->where(
				' (tbl.event_id = -1 OR tbl.id IN (SELECT field_id FROM #__eb_field_events WHERE event_id=' . $state->filter_event_id . '))');
		}
		if ($state->filter_show_core_fields == 2)
		{
			$query->where('tbl.is_core = 0');
		}
		return parent::_buildQueryWhere($query);
	}
}