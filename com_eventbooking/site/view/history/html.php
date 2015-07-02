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
class EventBookingViewHistory extends JViewLegacy
{

	function display($tpl = null)
	{
		$user = JFactory::getUser();
		if (!$user->id)
		{
			JFactory::getApplication()->redirect('index.php?option=com_users&view=login&return=' . base64_encode(JUri::getInstance()->toString()));
			return;
		}
		$model = $this->getModel();
		$state = $model->getState();
		$config = EventbookingHelper::getConfig();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$lists['search'] = JString::strtolower($state->search);
		$lists['order_Dir'] = $state->filter_order_Dir;
		$lists['order'] = $state->filter_order;
		//Get list of document		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, title' . $fieldSuffix . ' AS title, event_date')
			->from('#__eb_events')
			->where('published = 1')
			->where('id IN (SELECT event_id FROM #__eb_registrants AS tbl WHERE (tbl.published=1 OR tbl.payment_method LIKE "os_offline%") AND (tbl.user_id =' . $user->get('id') . ' OR tbl.email="' . $user->get('email') . '"))')
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

		$lists['event_id'] = JHtml::_('select.genericlist', $options, 'event_id', ' class="inputbox" onchange="submit();"', 'id', 'title', 
			$state->event_id);
		$this->lists = $lists;
		$this->items = $model->getData();
		$this->pagination = $model->getPagination();
		$this->config = $config;
		$this->Itemid = JRequest::getInt('Itemid', 0);
		
		parent::display($tpl);
	}
}