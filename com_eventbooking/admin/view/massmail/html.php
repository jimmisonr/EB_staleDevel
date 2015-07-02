<?php
/**
 * @version        	2.0.0
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventbookingViewMassmailHtml extends RADViewHtml
{

	function display()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$config = EventbookingHelper::getConfig();
		$options = array();
		$query->select('id, title, event_date')
			->from('#__eb_events')
			->where('published=1')
			->order('title');
		$db->setQuery($query);
		$options[] = JHtml::_('select.option', 0, JText::_('Select Event'), 'id', 'title');
		if ($config->show_event_date)
		{
			$rows = $db->loadObjectList();
			for ($i = 0, $n = count($rows); $i < $n; $i++)
			{
				$row = $rows[$i];
				$options[] = JHtml::_('select.option', $row->id, $row->title . ' (' . JHtml::_('date', $row->event_date, $config->date_format, null) . ')' .
					 '', 'id', 'title');
			}
		}
		else
		{
			$options = array_merge($options, $db->loadObjectList());
		}
		$lists = array();
		$lists['event_id'] = JHtml::_('select.genericlist', $options, 'event_id', ' class="inputbox" ', 'id', 'title');
		$this->lists = $lists;
								
		parent::display();
	}
}