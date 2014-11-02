<?php
/**
 * @version		1.0.0
 * @package		Joomla
 * @subpackage	Event Booking
 * @author  Tuan Pham Ngoc
 * @copyright	Copyright (C) 2010 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die();

class plgEventBookingCB extends JPlugin
{

	public function __construct(& $subject, $config = array())
	{
		
		parent::__construct($subject, $config);
	}

	/**
	 * Run when a membership activated
	 * @param PlanOsMembership $row
	 */
	function onAfterStoreRegistrant($row)
	{
		if (!file_exists(JPATH_ROOT . '/components/com_comprofiler/comprofiler.php'))
		{
			return;
		}
		$config = EventBookingHelper::getConfig();
		$integration = (int) $config->cb_integration;
		if ($row->user_id && $integration == 1)
		{
			$db = JFactory::getDBO();
			$sql = 'SELECT count(*) FROM `#__comprofiler` WHERE `user_id` = ' . $db->Quote($row->user_id);
			$db->setQuery($sql);
			$count = $db->loadResult();
			if ($count)
			{
				return;
			}
			
			$sql = ' SHOW FIELDS FROM #__comprofiler ';
			$db->setQuery($sql);
			$fields = $db->loadObjectList();
			$fieldList = array();
			for ($i = 0, $n = count($fields); $i < $n; $i++)
			{
				$field = $fields[$i];
				$fieldList[] = $field->Field;
			}
			$mappings = array(
				'first_name' => $config->m_firstname, 
				'last_name' => $config->m_lastname, 
				'organization' => $config->m_organization, 
				'address' => $config->m_address, 
				'address2' => $config->m_address2, 
				'city' => $config->m_city, 
				'state' => $config->m_state, 
				'zip' => $config->m_zip, 
				'country' => $config->m_country, 
				'phone' => $config->m_phone, 
				'fax' => $config->m_fax);
			
			foreach ($mappings as $field => $cbField)
			{
				if ($cbField && in_array($cbField, $fieldList))
				{
					$fieldValues[$cbField] = $row->{$field};
				}
			}			
			$sql = 'SELECT a.field_mapping, b.field_value FROM #__eb_fields AS a '
			.' INNER JOIN #__eb_field_values AS b '
			.' ON a.id = b.field_id '
			.' WHERE b.registrant_id='.$row->id
			
			;			
			$db->setQuery($sql);
			$fields = $db->loadObjectList();
			if (count($fields))
			{
				foreach ($fields as $field)
				{
					if ($field->field_mapping && in_array($field->field_mapping, $fieldList))
					{
						$fieldValues[$field->field_mapping] = $field->field_value;
					}
				}
			}
			
			$profile = new stdClass();
			$profile->id = $row->user_id;
			$profile->user_id = $row->user_id;
			$profile->firstname = $row->first_name;
			$profile->lastname = $row->last_name;
			$profile->avatarapproved = 1;
			$profile->confirmed = 1;
			$profile->registeripaddr = htmlspecialchars($_SERVER['REMOTE_ADDR']);
			$profile->banned = 0;
			$profile->acceptedterms = 1;
			foreach ($fieldValues as $fieldName => $value)
			{
				$profile->{$fieldName} = $value;
			}
			$db->insertObject('#__comprofiler', $profile);
			
			return true;
		}
	}
}	