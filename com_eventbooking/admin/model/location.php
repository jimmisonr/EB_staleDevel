<?php
/**
 * * @version        	2.0.0
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventBookingModelLocation extends RADModelItem
{

	/**
	 * Method to store a location
	 *	 
	 * @param	RADInput $input
	 * @return	boolean	True on success	 
	 */
	function store($input, $ignore = array())
	{
		$data = $input->getData();
		$db = $this->getDbo();
		$row = $this->getTable();
		if ($data['id'])
			$row->load($data['id']);
		else
			$row->user_id = JFactory::getUser()->id;
		if (!$row->bind($data))
		{
			throw new Exception($db->getErrorMsg());
			return false;
		}			
		$coordinates = $data['coordinates'];
		$coordinates = explode(',',$coordinates);
		$row->lat  	 = $coordinates[0];
		$row->long   = $coordinates[1];
				
		if (!$row->store())
		{
			throw new Exception($db->getErrorMsg());
			return false;
		}
		$input->set('id', $row->id);
		
		return true;
	}
}