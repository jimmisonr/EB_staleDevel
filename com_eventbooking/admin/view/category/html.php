<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2017 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

class EventbookingViewCategoryHtml extends RADViewItem
{
	protected function prepareView()
	{
		parent::prepareView();

		JFactory::getDocument()->addScript(JUri::base(true) . '/components/com_eventbooking/assets/js/colorpicker/jscolor.js');
		$options   = array();
		$options[] = JHtml::_('select.option', '', JText::_('Default Layout'));
		$options[] = JHtml::_('select.option', 'table', JText::_('Table Layout'));
		$options[] = JHtml::_('select.option', 'calendar', JText::_('Calendar Layout'));
		$options[] = JHtml::_('select.option', 'timeline', JText::_('Timeline Layout'));
		$options[] = JHtml::_('select.option', 'columns', JText::_('Columns Layout'));

		$this->lists['show_on_submit_event_form'] = JHtml::_('select.booleanlist', 'show_on_submit_event_form', ' ', $this->item->show_on_submit_event_form);
		$this->lists['layout']                    = JHtml::_('select.genericlist', $options, 'layout', ' class="inputbox" ', 'value', 'text', $this->item->layout);
		$this->lists['parent']                    = EventbookingHelperHtml::buildCategoryDropdown($this->item->parent, 'parent');
	}
}
