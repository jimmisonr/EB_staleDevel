<?php
/**
 * @version        	1.6.8
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2014 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventbookingModelWaitings extends RADModelList
{

	/**
	 * Constructor function		
	 */
	function __construct($config)
	{
		$config['table'] = '#__eb_waiting_lists';
		$config['search_fields'] = array('tbl.first_name', 'tbl.last_name');
		parent::__construct($config);
		
		$app = JFactory::getApplication();
		$context = $this->option . '.' . $this->name . '.';
		$this->state->insert('filter_event_id', 'int', $app->getUserStateFromRequest($context . 'filter_event_id', 'filter_event_id', 0));
	}

	/**
	 * Builds SELECT columns list for the query
	 */
	protected function _buildQueryColumns(JDatabaseQuery $query)
	{
		$query->select('tbl.*, ev.title, ev.event_date');
		
		return $this;
	}

	/**
	 * Builds LEFT JOINS clauses for the query
	 */
	protected function _buildQueryJoins(JDatabaseQuery $query)
	{
		$query->leftJoin('#__eb_events AS ev ON tbl.event_id = ev.id');
		
		return $this;
	}

	/**
	 * Build where clase of the query
	 *
	 * @see RADModelList::_buildQueryWhere()
	 */
	protected function _buildQueryWhere(JDatabaseQuery $query)
	{
		if ($this->state->filter_event_id)
		{
			$query->where('tbl.event_id ='.$this->state->filter_event_id);
		}
		
		return parent::_buildQueryWhere($query);
	}
}