<?php
/**
 * @package     Joomla.RAD
 * @subpackage  ModelAdmin
 * @author		Ossolution Team
 */
defined('_JEXEC') or die();

class RADModelItem extends RADModel
{

	/**
	 * The record data
	 * 
	 * @var object
	 */
	protected $data = null;

	/**
	 * Constructor function
	 * 
	 * @param array $config
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		
		$this->state->insert('id', 'int', 0)->insert('cid', 'array', array());	
	}

	/**
	 * Method to get the record data
	 *
	 * @return object
	 */
	public function getData()
	{		
		if (empty($this->data))
		{
			if (count($this->state->cid))
			{
				$this->state->id = (int) $this->state->cid[0];
			}
			if ($this->state->id)
			{
				$this->loadData();
			}
			else
			{
				$this->initData();
			}
		}
		
		return $this->data;
	}

	/**
	 * Load the record from database
	 *
	 */
	public function loadData()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from($this->table)
			->where('id = ' . (int) $this->state->id);
		$db->setQuery($query);
								
		$this->data = $db->loadObject();
	}

	/**
	 * Init the record dara object
	 */
	public function initData()
	{
		$this->data = $this->getTable();		
	}

	/**
	 * Method to store a record
	 * 
	 * @param RADInput $input the input data which users entered
	 * 
	 * @return boolean
	 */
	public function store($input, $ignore = array())
	{
		$db = $this->getDbo();
		$user = JFactory::getUser();
		$row = $this->getTable();
		$data = $input->getData();
		if ($data['id'])
		{
			$row->load($data['id']);
		}
		if (isset($data['params']) && is_array($data['params']))
		{
			$data['params'] = json_encode($data['params']);
		}
		if (!$data['alias'])
		{
			if (isset($data['title']))
			{
				$data['alias'] = JApplication::stringURLSafe($data['title']);
			}
			elseif (isset($data['name']))
			{
				$data['alias'] = JApplication::stringURLSafe($data['name']);
			}
		}
		else 
		{			
			$data['alias'] = JApplication::stringURLSafe($data['alias']);
		}
		if (!$row->bind($data, $ignore))
		{
			throw new Exception($db->getErrorMsg());
			return false;
		}
		if (!$row->id)
		{
			if (property_exists($row, 'ordering'))
			{
				$row->ordering = $row->getNextOrder($this->getReorderConditions($row));
			}
			
			if (property_exists($row, 'created_date') && !$row->created_date)
			{
				$row->created_date = JFactory::getDate()->toSql();
			}
			
			if (property_exists($row, 'created_by') && !$row->created_by)
			{
				$row->created_by = $user->get('id');
			}
		}
		
		if (property_exists($row, 'modified_date') && !$row->modified_date)
		{
			$row->modified_date = JFactory::getDate()->toSql();
		}
		if (property_exists($row, 'modified_by') && !$row->modified_by)
		{
			$row->modified_by = $user->get('id');
		}
		
		if (!$row->check())
		{
			throw new Exception($db->getErrorMsg());
			return false;
		}
		
		if (!$row->store())
		{
			throw new Exception($db->getErrorMsg());
			return false;
		}		
		$input->set('id', $row->id);
				
		return true;
	}

	/**
	 * Method to remove selected records
	 *
	 * @access	public
	 * @return boolean True on success
	 */
	public function delete($cid = array())
	{
		if (count($cid))
		{
			$db = $this->getDbo();
			$cids = implode(',', $cid);
			$query = $db->getQuery(true);
			$query->delete($this->table)->where('id IN (' . $cids . ')');
			$db->setQuery($query);
			if (!$db->execute())
			{
				throw new Exception($db->getErrorMsg());
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Publish the selected records
	 *
	 * @param  array   $cid
	 * @return boolean
	 */
	public function publish($cid, $state)
	{
		if (count($cid))
		{
			$db = $this->getDbo();
			$cids = implode(',', $cid);
			$query = $db->getQuery(true);
			$query->update($this->table)
				->set('published = ' . $state)
				->where('id IN (' . $cids . ')');
			$db->setQuery($query);
			if (!$db->execute())
			{
				throw new Exception($db->getErrorMsg());
				return false;
			}
		}
		
		return true;
	}

	/**
	 * Save the order of entities
	 *
	 * @param array $cid
	 * @param array $order
	 */
	public function saveOrder($cid, $order)
	{
		$row = $this->getTable();
		for ($i = 0; $i < count($cid); $i++)
		{
			$row->load($cid[$i]);
			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];
				if (!$row->store())
				{
					return false;
				}
			}
		}
		
		return true;
	}

	/**
	 * Change order of the record
	 * @param int $direction
	 * 
	 * @return boolean
	 */
	public function move($direction)
	{
        if (count($this->state->cid))
        {
            $this->state->id = (int) $this->state->cid[0];
        }
		$row = $this->getTable();
		$row->load($this->state->id);
		if (!$row->move($direction))
		{
			return false;
		}
        $row->reorder($this->getReorderConditions($row));

		return true;
	}

	/**
	 * Method to copy a record
	 * 
	 * @param int $id ID of the record which will be copied
	 * @return int ID of the new record
	 */
	public function copy($id)
	{
		$rowOld = $this->getTable();
		$row = $this->getTable();
		$rowOld->load($id);
		$data = JArrayHelper::fromObject($rowOld);
		$data['id'] = 0;
		if (isset($data['title']))
		{
			$data['title'] = $data['title'] . ' Copy';
			$data['alias'] = JApplication::stringURLSafe($data['title']);
		}
		elseif (isset($data['name']))
		{
			$data['name'] = $data['name'] . ' Copy';
			$data['alias'] = JApplication::stringURLSafe($data['name']);
		}		
		$row->bind($data);
		$row->check();
		if (property_exists($row, 'ordering'))
		{
			$row->ordering = $row->getNextOrder($this->getReorderConditions($row));
		}
		$row->store();		
		return $row->id;
	}

	/**
	 * Build additional conditions used to calculate the ordering for new record
	 * 
	 * @return string
	 */
	public function getReorderConditions($row)
	{
		return '';
	}
}
