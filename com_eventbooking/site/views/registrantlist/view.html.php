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
class EventBookingViewRegistrantList extends JViewLegacy
{

	function display($tpl = null)
	{
		if (!EventbookingHelper::canViewRegistrantList())
		{
			return;
		}
		$config = EventbookingHelper::getConfig();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$eventId = JRequest::getInt('id');
		if ($eventId)
		{
			$query->select('*')
				->from('#__eb_registrants AS tbl')
				->where('tbl.event_id=' . $eventId)
				->where('(tbl.published =1 OR tbl.payment_method LIKE "os_offline%")')
				->where('tbl.published != 2');
			if (isset($config->include_group_billing_in_registrants) && !$config->include_group_billing_in_registrants)
			{
				$query->where('tbl.is_group_billing = 0 ');
			}
			if (!$config->include_group_members_in_registrants)
			{
				$query->where('tbl.group_id = 0');
			}
			$query->order('register_date DESC');
			$db->setQuery($query);			
			$rows = $db->loadObjectList();
		}
		else
		{
			$rows = array();
		}
		$sql = 'SELECT * FROM #__eb_events WHERE id=' . $eventId;
		$db->setQuery($sql);
		$event = $db->loadObject();
		if (strlen(trim($event->custom_field_ids)))
		{
			$fields = explode(',', $event->custom_field_ids);
			$fieldTitles = array();
			$fieldValues = array();
			$sql = 'SELECT id, title FROM #__eb_fields WHERE id IN (' . $event->custom_field_ids . ')';
			$db->setQuery($sql);
			$rowFields = $db->loadObjectList();
			foreach ($rowFields as $rowField)
			{
				$fieldTitles[$rowField->id] = $rowField->title;
			}
			$registrantIds = array();
			foreach ($rows as $row)
			{
				$registrantIds[] = $row->id;
			}
			$sql = 'SELECT registrant_id, field_id, field_value FROM #__eb_field_values WHERE registrant_id IN (' . implode(',', $registrantIds) . ')';
			$db->setQuery($sql);
			$rowFields = $db->loadObjectList();
			foreach ($rowFields as $rowField)
			{
				$fieldValues[$rowField->registrant_id][$rowField->field_id] = $rowField->field_value;
			}
			$this->fieldTitles = $fieldTitles;
			$this->fieldValues = $fieldValues;
			$this->fields = $fields;
			$displayCustomField = true;
		}
		else
		{
			$displayCustomField = false;
		}
		$this->items = $rows;
		$this->config = $config;
		$this->displayCustomField = $displayCustomField;
		
		parent::display($tpl);
	}
}