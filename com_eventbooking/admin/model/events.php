<?php
/**
 * @version        	1.6.9
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2014 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventbookingModelEvents extends RADModelList
{	
	/**
	 * Constructor
	 *	 
	 */
	function __construct($config)
	{
		parent::__construct($config);
		
		$app = JFactory::getApplication();
		$context = $this->option . '.' . $this->name . '.';
		$this->state->insert('filter_category_id', 'int', $app->getUserStateFromRequest($context . 'filter_category_id', 'filter_category_id', 0))
			->insert('filter_location_id', 'int', $app->getUserStateFromRequest($context . 'filter_location_id', 'filter_location_id', 0))
			->insert('filter_past_events', 'int', $app->getUserStateFromRequest($context . 'filter_past_events', 'filter_past_events', 0))
            ->insert('filter_order_Dir', 'word', $app->getUserStateFromRequest($context . 'filter_order_Dir', 'filter_order_Dir', 'DESC'));
	}

	/**
	 * Method to get events data
	 *
	 * @access public
	 * @return array
	 */
	function getData()
	{		
		$rows = parent::getData();			
		$db = $this->getDbo();					
		$query = $db->getQuery(true);
		$query->select('a.name FROM #__eb_categories AS a')
			->innerJoin('#__eb_event_categories AS b ON a.id = b.category_id')
            ->order('a.ordering');
		for ($i = 0 , $n = count($rows) ; $i < $n; $i++)
        {
			$row = $rows[$i] ;					
			$query->where('event_id='.$row->id);	
			$db->setQuery($query);						
			$row->category_name = implode(' | ', $db->loadColumn()) ;
			$query->clear('where');
		}		
		
		return $rows;
	}

	/**
	 * Builds SELECT columns list for the query
	 */
	protected function _buildQueryColumns(JDatabaseQuery $query)
	{
		$query->select('tbl.*,  SUM(rgt.number_registrants) AS total_registrants');
	
		return $this;
	}
	
	/**
	 * Builds LEFT JOINS clauses for the query
	 */
	protected function _buildQueryJoins(JDatabaseQuery $query)
	{
		$query->leftJoin('#__eb_registrants AS rgt ON (tbl.id = rgt.event_id AND rgt.group_id = 0 AND (rgt.published=1 OR (rgt.payment_method LIKE "os_offline%" AND rgt.published != 2)))');
						
		return $this;
	}
	
	/**
	 * Build where clase of the query
	 *
	 * @see RADModelList::_buildQueryWhere()
	 */
	protected function _buildQueryWhere(JDatabaseQuery $query)
	{
		if ($this->state->filter_category_id)
		{
			$query->where('tbl.id IN (SELECT event_id FROM #__eb_event_categories WHERE category_id=' . $this->state->filter_category_id . ')');
		}							
		if ($this->state->filter_location_id)
		{
			$query->where('tbl.location_id='.$this->state->filter_location_id);
		}		
		if ($this->state->filter_past_events == 0)
		{
			$query->where('DATE(tbl.event_date) >= CURDATE()');
		}
		
		return parent::_buildQueryWhere($query);
	}	
	
	protected function _buildQueryGroup(JDatabaseQuery $query)
	{
		$query->group('tbl.id');
		
		return $this;	
	}
}