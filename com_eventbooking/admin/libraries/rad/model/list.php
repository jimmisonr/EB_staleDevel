<?php
/**
 * @package     Joomla.RAD
 * @subpackage  ModelList
 *
 * @author	Ossolution Team
 */
defined('_JEXEC') or die();

class RADModelList extends RADModel
{

	/**
	 * List of fields which will be used for searching data from database table
	 * 
	 * @var array
	 */
	protected $searchFields = array();

	/**
	 * List total
	 *
	 * @var integer
	 */
	protected $total;

	/**
	 * Model list data
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Pagination object
	 * 
	 * @var JPagination
	 */
	protected $pagination;

	/**
	 * Instantiate the model.
	 *
	 * @param   array	$config	The configuration data for the model	 
	 *	 
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		
		$app = JFactory::getApplication();
		$context = $this->option . '.' . $this->name . '.';
        if (isset($config['ignore_session']))
        {
            $this->state->insert('limit', 'int', $app->getCfg('list_limit'))
                ->insert('limitstart', 'int', 0)
                ->insert('filter_order', 'cmd', 'tbl.id')
                ->insert('filter_order_Dir', 'word', 'asc')
                ->insert('filter_search', 'string')
                ->insert('filter_state', 'string')
                ->insert('filter_access', 'int', 0)
                ->insert('filter_language', 'string');
        }
        else
        {
            $this->state->insert('limit', 'int', $app->getUserStateFromRequest($context . 'limit', 'limit', $app->getCfg('list_limit')))
                ->insert('limitstart', 'int', $app->getUserStateFromRequest($context . 'limitstart', 'limitstart', 0))
                ->insert('filter_order', 'cmd', $app->getUserStateFromRequest($context . 'filter_order', 'filter_order', 'tbl.id'))
                ->insert('filter_order_Dir', 'word', $app->getUserStateFromRequest($context . 'filter_order_Dir', 'filter_order_Dir', 'asc'))
                ->insert('filter_search', 'string', $app->getUserStateFromRequest($context . 'filter_search', 'filter_search'))
                ->insert('filter_state', 'string', $app->getUserStateFromRequest($context . 'filter_state', 'filter_state'))
                ->insert('filter_access', 'int', $app->getUserStateFromRequest($context . 'filter_access', 'filter_access'))
                ->insert('filter_language', 'string', $app->getUserStateFromRequest($context . 'filter_language', 'filter_language'));
        }
		
		if (isset($config['search_fields']))
		{
			$this->searchFields = (array) $config['search_fields'];
		}
		else
		{
			//Build the search field array automatically, basically, we should search based on name, title, description if these fields are available
            $table = new RADTable($this->table, 'id', $this->db);
            $fields = array_keys($table->getFields());
			if (in_array('name', $fields))
			{
				$this->searchFields[] = 'tbl.name';
			}
			if (in_array('title', $fields))
			{
				$this->searchFields[] = 'tbl.title';
			}
			if (in_array('description', $fields))
			{
				$this->searchFields[] = 'tbl.description';
			}
		}
	}

	/**
	 * Get a list of items
	 *
	 * @return  array
	 */
	public function getData()
	{
		if (empty($this->data))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$this->_buildQueryColumns($query)
				->_buildQueryFrom($query)
				->_buildQueryJoins($query)
				->_buildQueryWhere($query)
				->_buildQueryGroup($query)
				->_buildQueryHaving($query)
				->_buildQueryOrder($query);				
			$db->setQuery($query, $this->state->limitstart, $this->state->limit);
			$this->data = $db->loadObjectList();
		}
		
		return $this->data;
	}

	/**
	 * Get total record
	 * 
	 * @return integer Number of records
	 * 
	 */
	public function getTotal()
	{
		if (empty($this->total))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('COUNT(*)');
			$this->_buildQueryFrom($query)
                ->_buildQueryWhere($query);
			$db->setQuery($query);
			$this->total = (int) $db->loadResult();
		}
		
		return $this->total;
	}

	/**
	 * Get pagination object
	 * 
	 * @return JPagination
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->pagination))
		{
			jimport('joomla.html.pagination');
			$this->pagination = new JPagination($this->getTotal(), $this->state->limitstart, $this->state->limit);
		}
		
		return $this->pagination;
	}

	/**
	 * Builds SELECT columns list for the query
	 */
	protected function _buildQueryColumns(JDatabaseQuery $query)
	{
		$query->select(array('tbl.*'));
		
		return $this;
	}

	/**
	 * Builds FROM tables list for the query
	 */
	protected function _buildQueryFrom(JDatabaseQuery $query)
	{
		$query->from($this->table . ' AS tbl');
		
		return $this;
	}

	/**
	 * Builds LEFT JOINS clauses for the query
	 */
	protected function _buildQueryJoins(JDatabaseQuery $query)
	{
		return $this;
	}

	/**
	 * Builds a WHERE clause for the query
	 */
	protected function _buildQueryWhere(JDatabaseQuery $query)
	{
		$user = JFactory::getUser();
		$db = $this->getDbo();
		$state = $this->state;
		if ($state->filter_state == 'P')
		{
			$query->where('tbl.published = 1');
		}
		elseif ($state->filter_state == 'U')
		{
			$query->where('tbl.published = 0');
		}
		
		if ($state->filter_access)
		{
			$query->where('tbl.access = ' . (int) $state->filter_access);
			if (!$user->authorise('core.admin'))
			{
				$query->where('tbl.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
			}
		}
		
		if ($state->filter_search)
		{
			if (stripos($state->search, 'id:') === 0)
			{
				$query->where('tbl.id = ' . (int) substr($state->filter_search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($state->filter_search, true) . '%', false);
				if (is_array($this->searchFields))
				{
					$whereOr = array();
					foreach ($this->searchFields as $searchField)
					{
						$whereOr[] = " LOWER($searchField) LIKE " . $search;
					}
					$query->where('(' . implode(' OR ', $whereOr) . ') ');
				}
			}
		}
		
		if ($state->filter_language && $state->filter_language != '*')
		{
            $query->where('tbl.language IN (' . $db->Quote($state->filter_language) . ',' . $db->Quote('*') . ', "")');
		}
		
		return $this;
	}

	/**
	 * Builds a GROUP BY clause for the query
	 */
	protected function _buildQueryGroup(JDatabaseQuery $query)
	{
		return $this;
	}

	/**
	 * Builds a HAVING clause for the query
	 */
	protected function _buildQueryHaving(JDatabaseQuery $query)
	{
		return $this;
	}

	/**
	 * Builds a generic ORDER BY clasue based on the model's state
	 */
	protected function _buildQueryOrder(JDatabaseQuery $query)
	{
		$sort = $this->state->filter_order;
		$direction = strtoupper($this->state->filter_order_Dir);
		if ($sort)
		{
			$query->order($sort . ' ' . $direction);
		}
	}	
}
