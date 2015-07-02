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

/**
 * HTML View class for EventBooking component
 *
 * @static
 * @package		Joomla
 * @subpackage	EventBooking
 */
class EventbookingViewCategoryHtml extends RADViewItem
{

	function display()
	{
		JFactory::getDocument()->addScript(JURI::base() . 'components/com_eventbooking/assets/js/colorpicker/jscolor.js');
		$options = array();
		$options[] = JHtml::_('select.option', '', JText::_('Default Layout'));
		$options[] = JHtml::_('select.option', 'table', JText::_('Table Layout'));
		$options[] = JHtml::_('select.option', 'calendar', JText::_('Calendar Layout'));
		$options[] = JHtml::_('select.option', 'timeline', JText::_('Timeline Layout'));
		$this->lists['layout'] = JHtml::_('select.genericlist', $options, 'layout', ' class="inputbox" ', 'value', 'text', $this->item->layout);
		$this->lists['parent'] = EventbookingHelperHtml::buildCategoryDropdown($this->item->parent, 'parent');
		
		parent::display();
	}
}