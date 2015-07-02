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
class EventbookingViewRegistrantsHtml extends RADViewList
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
		$this->lists['filter_event_id'] = JHtml::_('select.genericlist', $options, 'filter_event_id', ' class="inputbox" onchange="submit();"', 'id', 'title', $this->state->filter_event_id);
		$options = array();
		$options[] = JHtml::_('select.option', -1, JText::_('EB_REGISTRATION_STATUS'));
		$options[] = JHtml::_('select.option', 0, JText::_('EB_PENDING'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_PAID'));
		if ($config->activate_waitinglist_feature)
		{
			$options[] = JHtml::_('select.option', 3, JText::_('EB_WAITING_LIST'));
		}
		$options[] = JHtml::_('select.option', 2, JText::_('EB_CANCELLED'));

		$this->lists['filter_published'] = JHtml::_('select.genericlist', $options, 'filter_published', ' class="inputbox" onchange="submit()" ', 'value', 'text', 
			$this->state->filter_published);
		$query->clear();
		$query->select('COUNT(*)')
			->from('#__eb_payment_plugins')
			->where('published=1');				
		$db->setQuery($query);
		$totalPlugins = (int) $db->loadResult();
								
		$this->config = $config;
		$this->totalPlugins = $totalPlugins;
		
		parent::display();
	}
	
	/**
	 * Override addToolbar method to add custom csv export function
	 * @see RADViewList::addToolbar()
	 */
	public function addToolbar()
	{
		parent::addToolbar();
		JToolBarHelper::custom('resend_email', 'envelope', 'envelope', 'Resend Email', true);
		JToolBarHelper::custom('csv_export', 'download', 'download', 'Export Registration', false);
	}
}