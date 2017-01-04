<?php
/**
 * @package        Joomla
 * @subpackage     Events Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2015 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

class plgEventbookingUserprofile extends JPlugin
{
	public function __construct(& $subject, $config = array())
	{
		parent::__construct($subject, $config);

		$this->canRun = JPluginHelper::isEnabled('user', 'profile');
	}

	/**
	 * Get list of profile fields used for mapping with fields in Events Booking
	 *
	 * @return array
	 */
	public function onGetFields()
	{
		if ($this->canRun)
		{
			$fields  = array('address1', 'address2', 'city', 'region', 'country', 'postal_code', 'phone', 'website', 'favoritebook', 'aboutme', 'dob');
			$options = array();

			foreach ($fields as $field)
			{
				$options[] = JHtml::_('select.option', $field, $field);
			}

			return $options;
		}
	}

	/**
	 * Method to get data stored in CB profile of the given user
	 *
	 * @param int   $userId
	 * @param array $mappings
	 *
	 * @return array
	 */
	public function onGetProfileData($userId, $mappings)
	{
		if ($this->canRun)
		{

			$synchronizer = new RADSynchronizerJoomla();

			return $synchronizer->getData($userId, $mappings);
		}
	}

	/**
	 * Run when a membership activated
	 *
	 * @param EventbookingTableRegistrant $row
	 */
	public function onAfterStoreRegistrant($row)
	{
		if ($row->user_id)
		{
			$db     = JFactory::getDbo();
			$config = EventbookingHelper::getConfig();
			$userId = $row->user_id;
			// Update Name of users based on first name and last name from profile
			$user = JFactory::getUser($userId);
			$user->set('name', $row->first_name . ' ' . $row->last_name);
			$user->save(true);

			$deleteFields = array(
				'profile.address1',
				'profile.address2',
				'profile.city',
				'profile.region',
				'profile.country',
				'profile.postal_code',
				'profile.phone',
				'profile.website',
				'profile.favoritebook',
				'profile.aboutme',
				'profile.dob',
			);

			//Delete old profile data
			$db->setQuery(
				'DELETE FROM #__user_profiles WHERE user_id = ' . $userId .
				' AND profile_key IN ("' . implode('","', $deleteFields) . '")'
			);
			$db->execute();

			if ($config->multiple_booking)
			{
				$rowFields = EventbookingHelper::getFormFields($row->id, 4);
			}
			elseif ($row->is_group_billing)
			{
				$rowFields = EventbookingHelper::getFormFields($row->event_id, 1);
			}
			else
			{
				$rowFields = EventbookingHelper::getFormFields($row->event_id, 0);
			}

			$data = EventbookingHelper::getRegistrantData($row, $rowFields);

			$fieldMappings = array();
			foreach ($rowFields as $rowField)
			{
				if ($rowField->field_mapping)
				{
					$fieldMappings[$rowField->field_mapping] = $rowField->name;
				}
			}

			$fields = array(
				'address1',
				'address2',
				'city',
				'region',
				'country',
				'postal_code',
				'phone',
				'website',
				'favoritebook',
				'aboutme',
				'dob',
			);

			$tuples = array();
			$order  = 1;

			foreach ($fields as $field)
			{
				$value = '';

				if (isset($fieldMappings[$field]))
				{
					$fieldMapping = $fieldMappings[$field];

					if (isset($data[$fieldMapping]))
					{
						$value = $data[$fieldMapping];
					}
				}

				$tuples[] = '(' . $userId . ', ' . $db->quote('profile.' . $field) . ', ' . $db->quote(json_encode($value)) . ', ' . $order++ . ')';
			}

			$db->setQuery('INSERT INTO #__user_profiles VALUES ' . implode(', ', $tuples));
			$db->execute();
		}
	}
}
