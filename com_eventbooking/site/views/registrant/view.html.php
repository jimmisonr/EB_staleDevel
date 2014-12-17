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
class EventBookingViewRegistrant extends JViewLegacy
{

	function display($tpl = null)
	{
		JFactory::getDocument()->addScript(JUri::base(true) . '/components/com_eventbooking/assets/js/paymentmethods.js');
		$this->setLayout('default');
		EventbookingHelper::checkEditRegistrant();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();
		$item = $this->get('Data');
		$config = EventbookingHelper::getConfig();
		$userId = $user->get('id');
		
		$query->select('*')
			->from('#__eb_events')
			->where('id=' . $item->event_id);
		$db->setQuery($query);
		$event = $db->loadObject();
		if (EventbookingHelper::isGroupRegistration($item->id))
		{
			$rowFields = EventbookingHelper::getFormFields($item->event_id, 1);
			$query->clear();
			$query->select('*')
				->from('#__eb_registrants')
				->where('group_id=' . $item->id);
			$db->setQuery($query);
			$rowMembers = $db->loadObjectList();
		}
		else
		{
			$rowFields = EventbookingHelper::getFormFields($item->event_id, 0);
			$rowMembers = array();
		}
		$form = new RADForm($rowFields);
		$data = EventBookinghelper::getRegistrantData($item, $rowFields);
		$form->bind($data);
		$from = JRequest::getVar('from', '');
		if ($userId && $user->authorise('eventbooking.registrants_management', 'com_eventbooking'))
		{
			$canChangeStatus = true;
			$options = array();
			$options[] = JHtml::_('select.option', 0, JText::_('EB_PENDING'));
			$options[] = JHtml::_('select.option', 1, JText::_('EB_PAID'));
			$options[] = JHtml::_('select.option', 2, JText::_('EB_CANCELLED'));
			$lists['published'] = JHtml::_('select.genericlist', $options, 'published', ' class="inputbox" ', 'value', 'text', $item->published);
		}
		else
		{
			$canChangeStatus = false;
		}
		if (count($rowMembers))
		{
			$this->memberFormFields = EventbookingHelper::getFormFields($item->event_id, 2);
		}
		$this->item = $item;
		$this->event = $event;
		$this->config = $config;
		$this->lists = $lists;
		$this->from = $from;
		$this->canChangeStatus = $canChangeStatus;
		$this->form = $form;
		$this->rowMembers = $rowMembers;
		
		parent::display($tpl);
	}
}