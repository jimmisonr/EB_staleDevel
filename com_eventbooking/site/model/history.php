<?php
/**
 * @version            2.4.3
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

class EventbookingModelHistory extends RADModelList
{
	/**
	 * Instantiate the model.
	 *
	 * @param array $config configuration data for the model
	 *
	 */
	public function __construct($config = array())
	{
		$config['remember_states'] = false;

		parent::__construct($config);
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
		$db     = $this->getDbo();

		$query->where('(tbl.user_id =' . $user->get('id') . ' OR tbl.email=' . $db->quote($user->get('email')) . ')');

		return parent::buildQueryWhere($query);
	}
}