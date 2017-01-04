<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2017 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

class EventbookingModelHistory extends EventbookingModelRegistrants
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
		$currentDate = JHtml::_('date', 'Now', 'Y-m-d H:i:s');

		$query->select('ev.activate_certificate_feature')
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
		$user = JFactory::getUser();
		$db   = $this->getDbo();

		$query->where('(tbl.user_id =' . $user->get('id') . ' OR tbl.email=' . $db->quote($user->get('email')) . ')');

		return parent::buildQueryWhere($query);
	}
}
