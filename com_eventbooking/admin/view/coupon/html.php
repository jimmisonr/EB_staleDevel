<?php
/**
 * @version            2.0.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

class EventbookingViewCouponHtml extends RADViewItem
{

	protected function prepareView()
	{
		parent::prepareView();

		$config                     = EventbookingHelper::getConfig();
		$options                    = array();
		$options[]                  = JHtml::_('select.option', 0, JText::_('%'));
		$options[]                  = JHtml::_('select.option', 1, $config->currency_symbol);
		$this->lists['coupon_type'] = JHtml::_('select.genericlist', $options, 'coupon_type', 'class="input-mini"', 'value', 'text', $this->item->coupon_type);
		$options                    = array();
		$options[]                  = JHtml::_('select.option', 0, JText::_('EB_ALL_EVENTS'), 'id', 'title');
		$rows                       = EventbookingHelperDatabase::getAllEvents('title, ordering');

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
		$this->lists['event_id'] = JHtml::_('select.genericlist', $options, 'event_id', 'class="input-xlarge"', 'id', 'title', $this->item->event_id);
		$this->nullDate          = JFactory::getDbo()->getNullDate();
	}
}