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

class EventBookingModelRegistrant extends JModelLegacy
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
	 * Method to get a package
	 */
	function &getData()
	{
		if (empty($this->_data))
		{
			if ($this->_id)
			{
				$this->_loadData();
			}
			else
			{
				$this->_initData();
			}
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
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$config = EventbookingHelper::getConfig();
		$row = $this->getTable('EventBooking', 'Registrant');
		$row->load($data['id']);
		$published = $row->published;
		if ($row->is_group_billing)
		{
			$rowFields = EventbookingHelper::getFormFields($data['event_id'], 1);
		}
		else
		{
			$rowFields = EventbookingHelper::getFormFields($data['event_id'], 0);
		}
		$row->bind($data);
		$row->store();
		$form = new RADForm($rowFields);
		$form->storeData($row->id, $data);
		if ($row->is_group_billing && (strpos($row->payment_method, 'os_offline') !== false))
		{
			$query->update('#__eb_registrants')
				->set('published=' . (int) $row->published)
				->where('group_id=' . $row->id);
			$db->setQuery($query);
			$db->execute();
			$query->clear();
		}
		//Store group members data
		if ($row->is_group_billing && $config->collect_member_information)
		{
			$ids = (array) $data['ids'];
			$memberFormFields = EventbookingHelper::getFormFields($row->event_id, 2);
			for ($i = 0, $n = count($ids); $i < $n; $i++)
			{
				$memberId = $ids[$i];
				$rowMember = $this->getTable();
				$rowMember->load($memberId);
				$memberForm = new RADForm($memberFormFields);
				$memberForm->setFieldSuffix($i + 1);
				$memberForm->bind($data);
				$memberForm->removeFieldSuffix();
				$memberData = $memberForm->getFormData();
				$rowMember->bind($memberData);
				$rowMember->store();
				$memberForm->storeData($rowMember->id, $memberData);
			}
		}

		//Sending reminders
		if ($row->published == 1 && $published == 0)
		{
			//Change from pending to paid, send emails
			EventbookingHelper::sendRegistrationApprovedEmail($row, $config);
		}
		elseif($row->published == 2 && $published != 2 && $config->activate)
		{
			//Registration is cancelled, send notification emails to waiting list
			EventbookingHelper::notifyWaitingList($row, $config);
		}

		return true;
	}

	/**
	 * Init event data
	 *
	 */
	function _initData()
	{
		$row = $this->getTable('EventBooking', 'Registrant');
		$row->event_id = JRequest::getInt('event_id', 0);
		$this->_data = $row;
	}

	/**
	 * Load event data
	 *
	 */
	function _loadData()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_registrants')
			->where('id=' . $this->_id);
		$db->setQuery($query);
		$this->_data = $db->loadObject();
	}

	/**
	 * Method to remove registrants 
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	function delete($cid = array())
	{
		$db = JFactory::getDbo();
		if (count($cid))
		{
			$cids = implode(',', $cid);
			$sql = 'SELECT id FROM #__eb_registrants WHERE group_id IN (' . $cids . ')';
			$db->setQuery($sql);
			$cid = array_merge($cid, $db->loadColumn());
			$registrantIds = implode(',', $cid);
			$sql = 'DELETE FROM #__eb_field_values WHERE registrant_id IN (' . $registrantIds . ')';
			$db->setQuery($sql);
			$db->execute();
			$sql = 'DELETE FROM #__eb_registrants WHERE id IN (' . $registrantIds . ')';
			$db->setQuery($sql);
			$db->execute();
		}
		return true;
	}

	/**
	 * Publish / unpublish a registrant 
	 *
	 * @param array $cid
	 * @param int $state
	 */
	function publish($cid, $state)
	{
		$db = $this->getDbo();
		if (($state == 1) && count($cid))
		{
			$config = EventbookingHelper::getConfig();
			$row = new RADTable('#__eb_registrants', 'id', $db);
			foreach ($cid as $registrantId)
			{
				$row->load($registrantId);
				if (!$row->published)
				{
					EventbookingHelper::sendRegistrationApprovedEmail($row, $config);
				}
			}
		}
		$cids = implode(',', $cid);
		$sql = " UPDATE #__eb_registrants SET published=$state WHERE id IN ($cids) ";
		$db->setQuery($sql);
		$db->execute();
		return true;
	}
}