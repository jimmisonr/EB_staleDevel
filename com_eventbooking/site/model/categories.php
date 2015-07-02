<?php
/**
 * @version        	2.0.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

/**
 * EventBooking Component Categories Model
 *
 * @package        Joomla
 * @subpackage     EventBooking
 */
class EventbookingModelCategories extends RADModelList
{

	public function __construct($config = array())
	{
		$config['table'] = '#__eb_categories';

		parent::__construct($config);

		$this->state->insert('id', 'int', 0)
			->set('filter_order', 'tbl.ordering');

		$listLength = (int) EventbookingHelper::getConfigValue('number_categories');
		if ($listLength)
		{
			$this->state->setDefault('limit', $listLength);
		}
	}

	/**
	 * Method to get categories data
	 *
	 * @access public
	 * @return array
	 */
	function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->data))
		{
			$rows = parent::getData();
			for ($i = 0, $n = count($rows); $i < $n; $i++)
			{
				$row               = $rows[$i];
				$row->total_events = EventbookingHelper::getTotalEvent($row->id);
			}
			$this->data = $rows;
		}

		return $this->data;
	}

	protected function _buildQueryWhere(JDatabaseQuery $query)
	{
		$query->where('tbl.published=1')
			->where('tbl.parent=' . $this->state->id)
			->where('tbl.access IN (' . implode(',', JFactory::getUser()->getAuthorisedViewLevels()) . ')');

		return $this;
	}

	/**
	 * Builds SELECT columns list for the query
	 */
	protected function _buildQueryColumns(JDatabaseQuery $query)
	{
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$query->select('tbl.id, tbl.name' . $fieldSuffix . ' AS name, tbl.description' . $fieldSuffix . ' AS description');

		return $this;
	}
}