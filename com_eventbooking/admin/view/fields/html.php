<?php
/**
 * @version        	1.6.8
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2014 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventbookingViewFieldsHtml extends RADViewList
{

	function display()
	{
		$config = EventbookingHelper::getConfig();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		if ($config->custom_field_by_category)
		{
			$query->select('id, parent, parent AS parent_id, name, name AS title')
				->from('#__eb_categories')
				->where('published=1');
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			$children = array();
			if ($rows)
			{
				// first pass - collect children
				foreach ($rows as $v)
				{
					$pt = $v->parent;
					$list = @$children[$pt] ? $children[$pt] : array();
					array_push($list, $v);
					$children[$pt] = $list;
				}
			}
			$list = JHtml::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
			$options = array();
			$options[] = JHtml::_('select.option', 0, JText::_('EB_ALL_CATEGORIES'));
			foreach ($list as $listItem)
			{
				$options[] = JHtml::_('select.option', $listItem->id, '&nbsp;&nbsp;&nbsp;' . $listItem->treename);
			}
			$this->lists['filter_category_id'] = JHtml::_('select.genericlist', $options, 'filter_category_id', 
				array(
					'option.text.toHtml' => false, 
					'option.text' => 'text', 
					'option.value' => 'value', 
					'list.attr' => ' onchange="submit();" ', 
					'list.select' => $this->state->filter_category_id));
		}
		else
		{
			$query->select('id, title, event_date')
				->from('#__eb_events')
				->where('published=1')
				->order('title');
			$db->setQuery($query);
			$options = array();
			$options[] = JHtml::_('select.option', 0, JText::_('EB_ALL_EVENTS'), 'id', 'title');
			if ($config->show_event_date)
			{
				$rows = $db->loadObjectList();
				for ($i = 0, $n = count($rows); $i < $n; $i++)
				{
					$row = $rows[$i];
					$options[] = JHtml::_('select.option', $row->id, 
						$row->title . ' (' . JHtml::_('date', $row->event_date, $config->date_format) . ')' . '', 'id', 'title');
				}
			}
			else
			{
				$options = array_merge($options, $db->loadObjectList());
			}
			$this->lists['filter_event_id'] = JHtml::_('select.genericlist', $options, 'filter_event_id', 'class="inputbox" onchange="submit();" ', 
				'id', 'title', $this->state->filter_event_id);
		}
		$options = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_CORE_FIELDS'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_SHOW'));
		$options[] = JHtml::_('select.option', 2, JText::_('EB_HIDE'));
		$this->lists['filter_show_core_fields'] = JHtml::_('select.genericlist', $options, 'filter_show_core_fields', ' onchange="submit();" ', 
			'value', 'text', $this->state->filter_show_core_fields);
		$this->config = $config;
		parent::display();
	}
}