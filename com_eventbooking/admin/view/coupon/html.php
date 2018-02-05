<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2018 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die;

/**
 * Class EventbookingViewCouponHtml
 *
 * @property EventbookingModelCoupon $model
 */
class EventbookingViewCouponHtml extends RADViewItem
{
	protected function prepareView()
	{
		parent::prepareView();

		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$config = EventbookingHelper::getConfig();

		$options                    = array();
		$options[]                  = JHtml::_('select.option', 0, JText::_('%'));
		$options[]                  = JHtml::_('select.option', 1, $config->currency_symbol);
		$options[]                  = JHtml::_('select.option', 2, JText::_('EB_VOUCHER'));
		$this->lists['coupon_type'] = JHtml::_('select.genericlist', $options, 'coupon_type', 'class="input-medium"', 'value', 'text', $this->item->coupon_type);

		$options                 = array();
		$options[]               = JHtml::_('select.option', 0, JText::_('EB_EACH_MEMBER'));
		$options[]               = JHtml::_('select.option', 1, JText::_('EB_EACH_REGISTRATION'));
		$this->lists['apply_to'] = JHtml::_('select.genericlist', $options, 'apply_to', '', 'value', 'text', $this->item->apply_to);

		$options                   = array();
		$options[]                 = JHtml::_('select.option', 0, JText::_('EB_BOTH'));
		$options[]                 = JHtml::_('select.option', 1, JText::_('EB_INDIVIDUAL_REGISTRATION'));
		$options[]                 = JHtml::_('select.option', 2, JText::_('EB_GROUP_REGISTRATION'));
		$this->lists['enable_for'] = JHtml::_('select.genericlist', $options, 'enable_for', '', 'value', 'text', $this->item->enable_for);

		// Categories dropdown
		$rows     = EventbookingHelperDatabase::getAllCategories();
		$children = array();

		if ($rows)
		{
			// first pass - collect children
			foreach ($rows as $v)
			{
				$pt   = $v->parent_id;
				$list = @$children[$pt] ? $children[$pt] : array();
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}

		$list      = JHtml::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
		$options   = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_SELECT_CATEGORY'));

		foreach ($list as $listItem)
		{
			$options[] = JHtml::_('select.option', $listItem->id, '&nbsp;&nbsp;&nbsp;' . $listItem->treename);
		}

		if ($this->item->id)
		{
			$query->clear()
				->select('category_id')
				->from('#__eb_coupon_categories')
				->where('coupon_id=' . $this->item->id);
			$db->setQuery($query);
			$categoryIds = $db->loadResult();
		}
		else
		{
			$categoryIds = [];
		}

		$this->lists['category_id'] = JHtml::_('select.genericlist', $options, 'category_id[]', array(
			'option.text.toHtml' => false,
			'option.text'        => 'text',
			'option.value'       => 'value',
			'list.attr'          => 'class="advancedSelect input-xlarge" multiple="multiple"',
			'list.select'        => $categoryIds,
		));

		// Events
		$rows = EventbookingHelperDatabase::getAllEvents($config->sort_events_dropdown, $config->hide_past_events_from_events_dropdown);

		if (empty($this->item->id) || $this->item->event_id == -1)
		{
			$selectedEventIds[] = -1;
			$assignment         = 0;
		}
		else
		{
			$query->clear()
				->select('event_id')
				->from('#__eb_coupon_events')
				->where('coupon_id=' . $this->item->id);
			$db->setQuery($query);
			$selectedEventIds = $db->loadColumn();

			if (count($selectedEventIds) && $selectedEventIds[0] < 0)
			{
				$assignment = -1;
			}
			else
			{
				$assignment = 1;
			}

			$selectedEventIds = array_map('abs', $selectedEventIds);
		}

		$this->lists['event_id'] = EventbookingHelperHtml::getEventsDropdown($rows, 'event_id[]', 'class="input-xlarge" multiple="multiple" ', $selectedEventIds);

		$options   = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_ALL_EVENTS'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_ALL_SELECTED_EVENTS'));

		if (!$config->multiple_booking)
		{
			$options[] = JHtml::_('select.option', -1, JText::_('EB_ALL_EXCEPT_SELECTED_EVENTS'));
		}

		$this->lists['assignment'] = JHtml::_('select.genericlist', $options, 'assignment', ' onchange="showHideEventsSelection(this);"', 'value', 'text', $assignment);

		$this->nullDate    = $db->getNullDate();
		$this->config      = $config;
		$this->registrants = $this->model->getRegistrants();
		$this->assignment  = $assignment;
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
