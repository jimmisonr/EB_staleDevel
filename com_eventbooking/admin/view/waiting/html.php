<?php
/**
 * @version        	1.6.9
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2014 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventbookingViewWaitingHtml extends RADViewItem
{

	function display()
	{		
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
		$this->lists['event_id'] = JHtml::_('select.genericlist', $options, 'event_id', ' class="inputbox"', 'id', 'title', $this->item->event_id);
		//Build list of user name dropdown
		$query->clear();					
		$query->select('id, name')
			->from('#__users')
			->order('name');
		$db->setQuery($query);
		$options = array();
		$options[] = JHtml::_('select.option', 0, JText::_('Select User'), 'id', 'name');
		$options = array_merge($options, $db->loadObjectList());
		$this->lists['user_id'] = JHtml::_('select.genericlist', $options, 'user_id', ' class="inputbox" ', 'id', 'name', $this->item->user_id);
		//Get list of country
		$query->clear();
		$query->select('name AS value, name AS text')
			->from('#__eb_countries')
			->order('name');
		$db->setQuery($query);
		$rowCountries = $db->loadObjectList();
		$options = array();
		$options[] = JHtml::_('select.option', '', JText::_('EB_SELECT_COUNTRY'));
		$options = array_merge($options, $rowCountries);
		$this->lists['country_list'] = JHtml::_('select.genericlist', $options, 'country', '', 'value', 'text', $this->item->country);						
		//get list notified
		$this->lists['notified'] = JHtml::_('select.booleanlist', 'notified', ' class="inputbox" ', $this->item->notified);									
		$this->config = $config;				
				
		parent::display();
	}
}