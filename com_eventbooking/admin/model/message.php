<?php
/**
 * @version        	1.7.2
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventbookingModelMessage extends RADModel
{

	protected $data = null;

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
			$query->select('*')->from('#__eb_messages');
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			for ($i = 0, $n = count($rows); $i < $n; $i++)
			{
				$row = $rows[$i];
				$key = $row->message_key;
				$value = $row->message;
				$config->$key = stripslashes($value);
			}
			$this->data = $config;
		}
		
		return $this->data;
	}

	/**
	 * Store the message data
	 *
	 * @param array $data
	 */
	function store($data)
	{
		$db = $this->getDbo();
		$row = new RADTable('#__eb_messages', 'id', $this->db);
		$db->truncateTable('#__eb_messages');
		foreach ($data as $key => $value)
		{
			$row->id = 0;
			$row->message_key = $key;
			$row->message = $value;
			$row->store();
		}
		
		return true;
	}
}