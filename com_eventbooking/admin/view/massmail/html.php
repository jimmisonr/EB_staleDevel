<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2018 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

class EventbookingViewMassmailHtml extends RADViewHtml
{
	public function display()
	{
		$config = EventbookingHelper::getConfig();

		$options   = array();
		$options[] = JHtml::_('select.option', -1, JText::_('EB_DEFAULT_STATUS'));
		$options[] = JHtml::_('select.option', 0, JText::_('EB_PENDING'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_PAID'));

		if ($config->activate_waitinglist_feature)
		{
			$options[] = JHtml::_('select.option', 3, JText::_('EB_WAITING_LIST'));
		}

		$options[] = JHtml::_('select.option', 2, JText::_('EB_CANCELLED'));

		$lists['published'] = JHtml::_('select.genericlist', $options, 'published', 'class="input-xlarge"', 'value', 'text', $this->input->getInt('published', -1));
		$lists['event_id']  = EventbookingHelperHtml::getEventsDropdown(EventbookingHelperDatabase::getAllEvents(), 'event_id', 'class="input-xlarge"');

		$this->lists = $lists;

		parent::display();
	}
}
