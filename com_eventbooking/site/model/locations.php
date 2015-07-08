<?php
/**
 * @version            2.0.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

class EventbookingModelLocations extends RADModelList
{

	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->state->setDefault('filter_order_Dir', 'DESC');
	}

	/**
	 * Builds a WHERE clause for the query
	 */
	protected function _buildQueryWhere(JDatabaseQuery $query)
	{
		$query->where('tbl.user_id=' . (int) JFactory::getUser()->id);

		return $this;
	}
}