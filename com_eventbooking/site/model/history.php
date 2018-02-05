<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2018 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

JLoader::register('EventbookingModelCommonRegistrants', JPATH_ADMINISTRATOR . '/components/com_eventbooking/model/common/registrants.php');

class EventbookingModelHistory extends EventbookingModelCommonRegistrants
{
	/**
	 * Instantiate the model.
	 *
	 * @param array $config configuration data for the model
	 */
	public function __construct($config = array())
	{
		$config['remember_states'] = false;

		parent::__construct($config);
	}

	/**
	 * Builds SELECT columns list for the query
	 */
	protected function buildQueryColumns(JDatabaseQuery $query)
	{
		$currentDate = EventbookingHelper::getServerTimeFromGMTTime();

		$query->select('ev.activate_certificate_feature, ev.payment_methods')
			->select("TIMESTAMPDIFF(MINUTE, ev.event_end_date, '$currentDate') AS event_end_date_minutes");

		return parent::buildQueryColumns($query);
	}

	/**
	 * Builds a WHERE clause for the query
	 *
	 * @param JDatabaseQuery $query
	 *
	 * @return $this
	 */
	protected function buildQueryWhere(JDatabaseQuery $query)
	{
		$user   = JFactory::getUser();
		$config = EventbookingHelper::getConfig();

		$query->where('(tbl.published >= 1 OR tbl.payment_method LIKE "os_offline%")')
			->where('(tbl.user_id =' . $user->get('id') . ' OR tbl.email = ' . $this->getDbo()->quote($user->get('email')) . ')');

		if (!$config->get('include_group_billing_in_registrants', 1))
		{
			$query->where(' tbl.is_group_billing = 0 ');
		}

		if (!$config->include_group_members_in_registrants)
		{
			$query->where(' tbl.group_id = 0 ');
		}

		return parent::buildQueryWhere($query);
	}
}
