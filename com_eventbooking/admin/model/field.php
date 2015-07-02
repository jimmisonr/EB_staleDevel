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
class EventbookingModelField extends RADModelItem
{

	/**
	 * Method to store a field
	 *
	 * @param RADInput $input the input data 
	 *	 
	 * @return	boolean	True on success	
	 */
	function store($input, $ignore = array())
	{
		$config = EventbookingHelper::getConfig();
		$row = $this->getTable();
		$fieldId = $input->getInt('id', 0);
		if ($fieldId)
		{
			$row->load($fieldId);
		}
		$input->set('depend_on_options', implode(',', $input->get('depend_on_options', array(), 'array')));
		if ($row->name == 'first_name' || $row->name == 'email')
		{
			$ignore = array('field_type', 'published', 'validation_rules');
		}
		else
		{
			$ignore = array();
		}
		if (!$config->custom_field_by_category)
		{
			$eventIds = $input->get('event_id', array(), 'array');
			if (count($eventIds) == 0 || $eventIds[0] == -1 || $row->name == 'first_name' || $row->name == 'email')
			{
				$input->set('event_id', -1);
				$eventIds = array();
			}
			else
			{
				$input->set('event_id', 1);
			}
		}

		parent::store($input, $ignore);
		if (!$config->custom_field_by_category)
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$fieldId = $input->getInt('id', 0);
			$query->clear();
			$query->delete('#__eb_field_events')->where('field_id = ' . $fieldId);
			$db->setQuery($query);
			$db->query();
			if (count($eventIds))
			{
				$query->clear();
				$query->insert('#__eb_field_events')->columns('field_id, event_id');
				for ($i = 0, $n = count($eventIds); $i < $n; $i++)
				{
					$eventId = (int) $eventIds[$i];
					$query->values("$fieldId, $eventId");
				}
				$db->setQuery($query);
				$db->query();
			}
		}

		// Calculate depend on options in different languages		
		if (JLanguageMultilang::isEnabled())
		{
			$languages = EventbookingHelper::getLanguages();
			if (count($languages))
			{
				$fieldId = $input->getInt('id', 0);
				$row = $this->getTable();
				$row->load($fieldId);
				if ($row->depend_on_field_id > 0)
				{
					$masterField = $this->getTable();
					$masterField->load($row->depend_on_field_id);
					$masterFieldValues = explode("\r\n", $masterField->values);
					$dependOnOptions = explode(',', $row->depend_on_options);
					$dependOnIndexes = array();
					foreach($dependOnOptions as $option)
					{
						$index = array_search($option, $masterFieldValues);
						if ($index !== FALSE)
						{
							$dependOnIndexes[] = $index;
						}
					}
					foreach($languages as $language)
					{
						$sef = $language->sef;
						$dependOnOptionsWithThisLanguage = array();
						$values = explode("\r\n", $masterField->{'values_'.$sef});
						foreach($dependOnIndexes as $index)
						{
							if (isset($values[$index]))
							{
								$dependOnOptionsWithThisLanguage[] = $values[$index];
							}
						}
						$row->{'depend_on_options_'.$sef} = implode(',', $dependOnOptionsWithThisLanguage);
					}
					$row->store();
				}
			}
		}

		return true;
	}

	/**
	 * Method to remove  fields
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	function delete($cid = array())
	{
		if (count($cid))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$config = EventbookingHelper::getConfig();
			$cids = implode(',', $cid);
			//Delete data from field values table
			$query->delete('#__eb_field_values')->where('field_id IN (' . $cids . ')');
			$db->setQuery($query);
			$db->execute();
			if (!$config->custom_field_by_category)
			{
				$query->clear();
				$query->delete('#__eb_field_events')->where('field_id IN (' . $cids . ')');
				$db->setQuery($query);
				$db->query();
			}
			//Do not allow deleting core fields
			$query->clear();
			$query->delete('#__eb_fields')->where('id IN (' . $cids . ') AND is_core=0');
			$db->setQuery($query);
			$db->execute();
		}
		
		return true;
	}

	/**
	 * Change require status
	 *
	 * @param array $cid
	 * @param int $state
	 * @return boolean
	 */
	function required($cid, $state)
	{
		$cids = implode(',', $cid);
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$query->update('#__eb_fields')
			->set('required=' . $state)
			->where('id IN (' . $cids . ' )');
		$db->setQuery($query);
		$db->execute();
		
		return true;
	}

	/**
	 * Publish custom fields. Two fields First Name and Email could not be unpublished
	 * @see RADModelItem::publish()
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
				->where('id IN (' . $cids . ')')
				->where('name != "first_name" AND name !="email"');
			$db->setQuery($query);
			$db->execute();
		}
		return true;
	}
}