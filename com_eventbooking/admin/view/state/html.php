<?php
/**
 * @version        	1.7.2
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

/**
 * HTML View class for EventBooking component
 *
 * @static
 * @package		Joomla
 * @subpackage	EventBooking
 */
class EventbookingViewStateHtml extends RADViewItem
{

	function display()
	{
		$db = JFactory::getDbo();
		$db->setQuery("SELECT `id` AS value, `name` AS text FROM `#__eb_countries` WHERE `published`=1");
		$options = $db->loadObjectList();
		array_unshift($options,JHtml::_('select.option',0,' - '.JText::_('EB_SELECT_COUNTRY').' - '));
		$this->item->country_id = JHtml::_('select.genericlist', $options, 'country_id', ' class="inputbox"','value', 'text', $this->item->country_id);
		parent::display();
	}
}