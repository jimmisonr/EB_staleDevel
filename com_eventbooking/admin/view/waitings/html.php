<?php
/**
 * @version        	1.6.8
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2014 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventbookingViewWaitingsHtml extends RADViewList
{

	function display()
	{		
		//Get list of events
		$config = EventbookingHelper::getConfig();
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('id, title, event_date')
			->from('#__eb_events')
			->where('published=1')
			->order('title');		
		$db->setQuery($query);
		$options = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_SELECT_EVENT'), 'id', 'title');
		if ($config->show_event_date)
		{
			$rows = $db->loadObjectList();
			for ($i = 0, $n = count($rows); $i < $n; $i++)
			{
				$row = $rows[$i];
				$options[] = JHtml::_('select.option', $row->id, 
					$row->title . ' (' . JHtml::_('date', $row->event_date, $config->date_format, null) . ')' . '', 'id', 'title');
			}
		}
		else
		{
			$options = array_merge($options, $db->loadObjectList());
		}
		$this->lists['filter_event_id'] = JHtml::_('select.genericlist', $options, 'filter_event_id', ' class="inputbox" onchange="submit();"', 'id', 'title', $this->state->filter_event_id);				
		$this->config = $config;
		
		parent::display();
	}
}