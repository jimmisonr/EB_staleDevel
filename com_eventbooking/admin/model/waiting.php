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
class EventbookingModelWaiting extends RADModelItem
{		
	
	public function __construct($config)
	{
		$config['table'] = '#__eb_waiting_lists';
		
		parent::__construct($config);
	}
	
	public function getTable($name = '')
	{			
		return new RADTable('#__eb_waiting_lists', 'id', $this->db);
	}	
}
?> 