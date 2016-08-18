<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die;

class EventbookingViewDiscountHtml extends RADViewItem
{
	protected function prepareView()
	{
		parent::prepareView();

		$db     = JFactory::getDbo();
		$config = EventbookingHelper::getConfig();

		$options = array();
		$rows    = EventbookingHelperDatabase::getAllEvents($config->sort_events_dropdown, $config->hide_past_events_from_events_dropdown);

		if ($config->show_event_date)
		{
			for ($i = 0, $n = count($rows); $i < $n; $i++)
			{
				$row       = $rows[$i];
				$options[] = JHtml::_('select.option', $row->id,
					$row->title . ' (' . JHtml::_('date', $row->event_date, $config->date_format) . ')' . '', 'id', 'title');
			}
		}
		else
		{
			$options = array_merge($options, $rows);
		}

		$selectedEventIds = array();
		if ($this->item->id)
		{
			$query = $db->getQuery(true);
			$query->select('event_id')
				->from('#__eb_discount_events')
				->where('discount_id=' . $this->item->id);
			$db->setQuery($query);
			$selectedEventIds = $db->loadColumn();
		}

		$this->lists['event_id'] = JHtml::_('select.genericlist', $options, 'event_id[]', 'class="input-xlarge" multiple="multiple" ', 'id', 'title', $selectedEventIds);
		$this->nullDate          = $db->getNullDate();
		$this->config            = $config;
	}
}
