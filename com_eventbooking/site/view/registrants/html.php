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
class EventBookingViewRegistrants extends JViewLegacy
{

	function display($tpl = null)
	{
		EventbookingHelper::checkRegistrantsAccess();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$config = EventbookingHelper::getConfig();
		$model = $this->getModel();
		$state = $model->getState();
		//Get list of events				
		if ($config->only_show_registrants_of_event_owner)
		{
			$sql = 'SELECT id, title' . $fieldSuffix . ' AS title, event_date FROM #__eb_events WHERE published = 1 AND created_by=' . $user->id . ' ORDER BY title' . $fieldSuffix;
		}
		else
		{
			$sql = 'SELECT id, title' . $fieldSuffix . ' AS title, event_date FROM #__eb_events WHERE published = 1 ORDER BY title' . $fieldSuffix;
		}
		$db->setQuery($sql);
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
		$lists['event_id'] = JHtml::_('select.genericlist', $options, 'event_id', ' class="inputbox" onchange="submit();"', 'id', 'title', 
			$state->event_id);
		$options = array();
		$options[] = JHtml::_('select.option', -1, JText::_('EB_REGISTRATION_STATUS'));
		$options[] = JHtml::_('select.option', 0, JText::_('EB_PENDING'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_PAID'));
		$options[] = JHtml::_('select.option', 2, JText::_('EB_CANCELLED'));
		$lists['published'] = JHtml::_('select.genericlist', $options, 'published', ' class="input-medium" onchange="submit()" ', 'value', 'text',
			$state->published);
		$lists['search'] = $state->search;
		$lists['order_Dir'] = $state->filter_order_Dir;
		$lists['order'] = $state->filter_order;
		$this->lists = $lists;
		$this->items = $model->getData();
		$this->pagination = $model->getPagination();
		$this->config = $config;
		$this->Itemid = JRequest::getInt('Itemid', 0);
		
		parent::display($tpl);
	}
}