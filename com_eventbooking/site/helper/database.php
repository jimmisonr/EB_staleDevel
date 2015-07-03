<?php
/**
 * @version            2.0.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
class EventbookingHelperDatabase
{
	/**
	 * Get category data from database
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public static function getCategory($id)
	{
		$db          = JFactory::getDbo();
		$query       = $db->getQuery(true);
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$query->select('*')
			->from('#__eb_categories')
			->where('id=' . (int) $id);

		if ($fieldSuffix)
		{
			self::getMultilingualFields($query, array('name'), $fieldSuffix);
		}

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Method to load location object from database
	 *
	 * @param $id
	 *
	 * @return mixed
	 */
	public static function getLocation($id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_locations')
			->where('id=' . (int) $id);
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Method to get group registration rates for an event
	 *
	 * @param $eventId
	 *
	 * @return array
	 */
	public static function getGroupRegistrationRates($eventId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_event_group_prices')
			->where('event_id=' . (int) $eventId)
			->order('id');
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Helper method to get fields from database table in case the site is multilingual
	 *
	 * @param JDatabaseQuery $query
	 * @param array          $fields
	 * @param                $fieldSuffix
	 */
	public static function getMultilingualFields(JDatabaseQuery $query, $fields = array(), $fieldSuffix)
	{
		foreach ($fields as $field)
		{
			$query->select($query->quoteName($field, $field . $fieldSuffix));
		}
	}
}