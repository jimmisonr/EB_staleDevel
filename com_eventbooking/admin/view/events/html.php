<?php
/**
 * * @version        	2.0.0
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventbookingViewEventsHtml extends RADViewList
{

	function display()
	{
		$config = EventbookingHelper::getConfig();		
		$db = JFactory::getDBO();
		$this->lists['filter_category_id'] = EventbookingHelperHtml::buildCategoryDropdown($this->state->filter_category_id, 'filter_category_id', 
			'onchange="submit();"');
		$query = $db->getQuery(true);
		$query->select('id, name')
			->from('#__eb_locations')
			->where('published=1');
		$db->setQuery($query);
		$options = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_SELECT_LOCATION'), 'id', 'name');
		$options = array_merge($options, $db->loadObjectList());
		$this->lists['filter_location_id'] = JHtml::_('select.genericlist', $options, 'filter_location_id', ' class="inputbox" onchange="submit();" ', 
			'id', 'name', $this->state->filter_location_id);
		
		$options = array();
		$options[] = JHtml::_('select.option', -1, JText::_('EB_PAST_EVENTS'));
		$options[] = JHtml::_('select.option', 0, JText::_('EB_HIDE'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_SHOW'));
		$this->lists['filter_past_events'] = JHtml::_('select.genericlist', $options, 'filter_past_events', ' class="input-medium" onchange="submit();" ',
			'value', 'text', $this->state->filter_past_events);
							
		$this->config = $config;
		
		parent::display();
	}
}