<?php
/**
 * @version        	1.7.0
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventBookingViewAddlocation extends JViewLegacy
{

	function display($tpl = null)
	{
		$user = JFactory::getUser();
		if (!$user->authorise('eventbooking.addlocation', 'com_eventbooking'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_("EB_NO_PERMISSION"));
			return;
		}
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$item = $this->get('Data');
		$query->select('name AS `value`, name AS `text`')
			->from('#__eb_countries')
			->order('name');
		$db->setQuery($query);
		$options = array();
		$options[] = JHtml::_('select.option', '', JText::_('Select Country'));
		$options = array_merge($options, $db->loadObjectList());
		$lists['country'] = JHtml::_('select.genericlist', $options, 'country', ' class="inputbox" ', 'value', 'text', $item->country);
		$lists['published'] = JHtml::_('select.booleanlist', 'published', ' class="inputbox" ', $item->published);
		$this->item = $item;
		$this->lists = $lists;
		$this->config = EventbookingHelper::getConfig();
		$this->Itemid = JRequest::getInt('Itemid', 0);
		
		parent::display($tpl);
	}
}
