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
class EventbookingModelConfiguration extends RADModel
{

	/**
	 * Containing all config data,  store in an object with key, value
	 *
	 * @var object
	 */
	var $data = null;

	/**
	 * Get configuration data
	 *
	 */
	function getData()
	{
		if (empty($this->data))
		{
			$config = new stdClass();
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$query->select('config_key, config_value')->from('#__eb_configs');
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			for ($i = 0, $n = count($rows); $i < $n; $i++)
			{
				$row = $rows[$i];
				$key = $row->config_key;
				$value = $row->config_value;
				$config->{$key} = stripslashes($value);
			}
			
			$this->data = $config;
		}
		
		return $this->data;
	}

	/**
	 * Store the configuration data
	 *
	 * @param array $data
	 */
	function store($data)
	{
		$db = $this->getDbo();
		$db->truncateTable('#__eb_configs');
		$row = new RADTable('#__eb_configs', 'id', $this->db);
		foreach ($data as $key => $value)
		{
			if (is_array($value))
			{
				$value = implode(',', $value);
			}
			$row->id = 0;
			$row->config_key = $key;
			$row->config_value = $value;
			$row->store();
		}        
		return true;
	}
}