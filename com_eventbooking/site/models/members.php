<?php
/**
 * @version        	1.7.0
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

/**
 * Event Booking Members Model
 *
 * @package		Joomla
 * @subpackage	Members
 */
class EventBookingModelMembers extends JModelLegacy
{

	/**
	 * Event id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * Event data
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();
		$array = JRequest::getVar('cid', array(0), '', 'array');
		$edit = JRequest::getVar('edit', true);
		if ($edit)
		{
			$this->setId((int) $array[0]);
		}
	}

	/**
	 * Method to set the registrant identifier
	 *
	 * @access	public
	 * @param	int registrant identifier
	 */
	function setId($id)
	{
		// Set event id and wipe data
		$this->_id = $id;
		$this->_data = null;
	}

	/**
	 * Method to get a Registrant
	 *	
	 */
	function &getData()
	{
		if (empty($this->_data))
		{
			$db = $this->getDbo();
			$sql = 'SElECT * FROM #__eb_registrants WHERE group_id=' . $this->_id;
			$db->setQuery($sql);
			$rowMembers = $db->loadObjectList();
			if (!$rowMembers)
			{
				//Add new registration records from back-end
				$sql = 'SELECT number_registrants FROM #__eb_registrants WHERE id=' . $this->_id;
				$db->setQuery($sql);
				$numberRegistrants = $db->loadResult();
				$rowMembers = array();
				for ($i = 0; $i < $numberRegistrants; $i++)
				{
					$rowMembers[] = $this->getTable('EventBooking', 'Registrant');
				}
			}
			$this->_data = $rowMembers;
		}
		return $this->_data;
	}

	/**
	 * Method to store a registrant
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	function store(&$data)
	{
		$groupId = JRequest::getInt('group_id', 0);
		$ids = JRequest::getVar('ids', array(), 'post', 'array');
		$firstNames = JRequest::getVar('first_name', array(), 'post', 'array');
		if (count($firstNames))
		{
			$lastNames = JRequest::getVar('last_name', array(), 'post', 'array');
			$organizations = JRequest::getVar('organization', array(), 'post', 'array');
			$addresses = JRequest::getVar('address', array(), 'post', 'array');
			$address2s = JRequest::getVar('address2', array(), 'post', 'array');
			$cities = JRequest::getVar('city', array(), 'post', 'array');
			$states = JRequest::getVar('state', array(), 'post', 'array');
			$zips = JRequest::getVar('zip', array(), 'post', 'array');
			$phones = JRequest::getVar('phone', array(), 'post', 'array');
			$faxs = JRequest::getVar('fax', array(), 'post', 'array');
			$countries = JRequest::getVar('country', array(), 'post', 'array');
			$emails = JRequest::getVar('email', array(), 'post', 'array');
			$comments = JRequest::getVar('comment', array(), 'comment', 'array');
			for ($i = 0, $n = count($firstNames); $i < $n; $i++)
			{
				$row = $this->getTable('EventBooking', 'Registrant');
				if (isset($ids[$i]))
				{
					$row->load($ids[$i]);
				}
				else
				{
					$row->id = 0;
				}
				$row->group_id = $groupId;
				$row->first_name = $firstNames[$i];
				$row->last_name = $lastNames[$i];
				$row->organization = $organizations[$i];
				$row->address = $addresses[$i];
				$row->address2 = $address2s[$i];
				$row->city = $cities[$i];
				$row->state = $states[$i];
				$row->zip = $zips[$i];
				$row->phone = $phones[$i];
				$row->fax = $faxs[$i];
				$row->email = $emails[$i];
				$row->comment = $comments[$i];
				$row->country = $countries[$i];
				$row->store();
			}
		}
		return true;
	}
}