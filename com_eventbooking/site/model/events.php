<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2018 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

JLoader::register('EventbookingModelCommonEvents', JPATH_ADMINISTRATOR . '/components/com_eventbooking/model/common/events.php');

class EventbookingModelEvents extends EventbookingModelCommonEvents
{
	/**
	 * Instantiate the model.
	 *
	 * @param array $config configuration data for the model
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->state->setDefault('filter_order_Dir', 'DESC');
		
		// Remember filter states
		$this->rememberStates = true;
	}

	protected function buildQueryWhere(JDatabaseQuery $query)
	{
		$user = JFactory::getUser();

		if (!$user->authorise('core.admin', 'com_eventbooking'))
		{
			$query->where('tbl.created_by=' . (int) $user->id);
		}

		return parent::buildQueryWhere($query);
	}
}
