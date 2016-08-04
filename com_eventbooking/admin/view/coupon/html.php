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

class EventbookingViewCouponHtml extends RADViewItem
{
	protected function prepareView()
	{
		parent::prepareView();

		$db                         = JFactory::getDbo();
		$config                     = EventbookingHelper::getConfig();
		$options                    = array();
		$options[]                  = JHtml::_('select.option', 0, JText::_('%'));
		$options[]                  = JHtml::_('select.option', 1, $config->currency_symbol);
		$this->lists['coupon_type'] = JHtml::_('select.genericlist', $options, 'coupon_type', 'class="input-mini"', 'value', 'text', $this->item->coupon_type);

		$options                 = array();
		$options[]               = JHtml::_('select.option', 0, JText::_('EB_EACH_MEMBER'));
		$options[]               = JHtml::_('select.option', 1, JText::_('EB_EACH_REGISTRATION'));
		$this->lists['apply_to'] = JHtml::_('select.genericlist', $options, 'apply_to', '', 'value', 'text', $this->item->apply_to);

		$options                   = array();
		$options[]                 = JHtml::_('select.option', 0, JText::_('EB_BOTH'));
		$options[]                 = JHtml::_('select.option', 1, JText::_('EB_INDIVIDUAL_REGISTRATION'));
		$options[]                 = JHtml::_('select.option', 2, JText::_('EB_GROUP_REGISTRATION'));
		$this->lists['enable_for'] = JHtml::_('select.genericlist', $options, 'enable_for', '', 'value', 'text', $this->item->enable_for);

		$options   = array();
		$options[] = JHtml::_('select.option', -1, JText::_('EB_ALL_EVENTS'), 'id', 'title');
		$rows      = EventbookingHelperDatabase::getAllEvents($config->sort_events_dropdown, $config->hide_past_events_from_events_dropdown);

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

		if (empty($this->item->id) || $this->item->event_id == -1)
		{
			$selectedEventIds[] = -1;
		}
		else
		{
			$query = $db->getQuery(true);
			$query->select('event_id')
				->from('#__eb_coupon_events')
				->where('coupon_id=' . $this->item->id);
			$db->setQuery($query);
			$selectedEventIds = $db->loadColumn();
		}

		$this->lists['event_id'] = JHtml::_('select.genericlist', $options, 'event_id[]', 'class="input-xlarge" multiple="multiple" ', 'id', 'title', $selectedEventIds);
		$this->nullDate          = $db->getNullDate();
		$this->config            = $config;
	}

	/**
	 * Override addToolbar function to allow generating custom buttons for import & batch coupon feature
	 */
	protected function addToolbar()
	{
		$layout = $this->getLayout();
		if ($layout == 'default')
		{
			parent::addToolbar();
		}
	}
}
