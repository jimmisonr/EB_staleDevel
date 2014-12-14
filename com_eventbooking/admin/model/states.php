<?php
/**
 * @version            1.6.6
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2014 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

class EventbookingModelStates extends RADModelList
{

	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->state->insert('filter_country_id', 'int', 0);
	}

	/**
	 * Builds SELECT columns list for the query
	 */
	protected function _buildQueryColumns(JDatabaseQuery $query)
	{
		$query->select('tbl.*,  b.name AS country_name');

		return $this;
	}

	/**
	 * Builds LEFT JOINS clauses for the query
	 */
	protected function _buildQueryJoins(JDatabaseQuery $query)
	{
		$query->leftJoin('#__eb_countries AS b ON tbl.country_id = b.id');

		return $this;
	}

	/**
	 * Builds a WHERE clause for the query
	 */
	protected function _buildQueryWhere(JDatabaseQuery $query)
	{

		if ($this->state->filter_country_id)
		{
			$query->where('tbl.country_id=' . $this->state->filter_country_id);
		}

		return parent::_buildQueryWhere($query);
	}
}