<?php
/**
 * @version        	1.6.9
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

/**
 * EventBooking Component Categories Model
 *
 * @package		Joomla
 * @subpackage	EventBooking
 */
class EventbookingModelCategories extends RADModelList
{

	function __construct($config = array())
	{
		$config['table'] = '#__eb_categories';
		parent::__construct($config);
		$app = JFactory::getApplication();
		$listLength = EventbookingHelper::getConfigValue('number_categories');
		if (!$listLength)
		{
			$listLength = $app->getCfg('list_limit');
		}
		$this->state->insert('id', 'int', 0)->insert('limit', 'int', $listLength);
		$request = EventbookingHelper::getRequestData();
		$this->state->setData($request);
		$this->state->set('filter_order', 'tbl.ordering');
		if (JRequest::getVar('view') == 'categories')
		{
			$app->setUserState('eventbooking.limit', $this->state->limit);
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
				$row = $rows[$i];
				$row->total_events = EventbookingHelper::getTotalEvent($row->id);
			}
			$this->data = $rows;
		}
		return $this->data;
	}

	protected function _buildQueryWhere(JDatabaseQuery $query)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$query->where('tbl.published=1')
			->where('tbl.parent=' . $this->state->id)
			->where('tbl.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
		if ($app->getLanguageFilter())
		{
			$query->where('tbl.language IN (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')');
		}
		
		return $this;
	}
}