<?php
/**
 * @version            1.7.4
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

class EventBookingModelCountry extends RADModelItem
{

	/**
	 * Method to store a country
	 *
	 * @param    RADInput $input
	 *
	 * @return    boolean    True on success
	 */
	function store($input, $ignore = array())
	{
		if ($input->getInt('id'))
		{
			$isNew = false;
		}
		else
		{
			$isNew = true;
		}

		parent::store($input, $ignore);

		if ($isNew)
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$query->update('#__eb_countries')
				->set('country_id=id')
				->where('id=' . $input->getInt('id', 0));
			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}
}