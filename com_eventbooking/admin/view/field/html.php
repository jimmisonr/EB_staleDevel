<?php
/**
 * @version            2.3.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

class EventbookingViewFieldHtml extends RADViewItem
{

	protected function prepareView()
	{
		parent::prepareView();
		$db         = JFactory::getDbo();
		$query      = $db->getQuery(true);
		$config     = EventbookingHelper::getConfig();
		$fieldTypes = array('Text', 'Textarea', 'List', 'Checkboxes', 'Radio', 'Date', 'Heading', 'Message', 'File', 'Countries', 'State', 'SQL');
		$options    = array();
		$options[]  = JHtml::_('select.option', -1, JText::_('EB_FIELD_TYPE'));
		$options    = array();
		foreach ($fieldTypes as $fieldType)
		{
			$options[] = JHtml::_('select.option', $fieldType, $fieldType);
		}
		if ($this->item->is_core)
		{
			$readOnly = ' readonly="true" ';
		}
		else
		{
			$readOnly = '';
		}
		$this->lists['fieldtype'] = JHtml::_('select.genericlist', $options, 'fieldtype', ' class="inputbox" ' . $readOnly, 'value', 'text',
			$this->item->fieldtype);
		if ($config->custom_field_by_category)
		{
			$rows     = EventbookingHelperDatabase::getAllCategories();
			$children = array();
			if ($rows)
			{
				// first pass - collect children
				foreach ($rows as $v)
				{
					$pt   = $v->parent;
					$list = @$children[$pt] ? $children[$pt] : array();
					array_push($list, $v);
					$children[$pt] = $list;
				}
			}
			$list      = JHtml::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
			$options   = array();
			$options[] = JHtml::_('select.option', -1, JText::_('EB_ALL_CATEGORIES'));
			foreach ($list as $listItem)
			{
				$options[] = JHtml::_('select.option', $listItem->id, '&nbsp;&nbsp;&nbsp;' . $listItem->treename);
			}

			if (empty($this->item->id) || $this->item->category_id == -1)
			{
				$selectedCategoryIds[] = -1;
			}
			else
			{
				$query->clear();
				$query->select('category_id')
					->from('#__eb_field_categories')
					->where('field_id=' . $this->item->id);
				$db->setQuery($query);
				$selectedCategoryIds = $db->loadColumn();
			}

			$this->lists['category_id'] = JHtml::_('select.genericlist', $options, 'category_id[]',
				array(
					'option.text.toHtml' => false,
					'option.text'        => 'text',
					'option.value'       => 'value',
					'list.attr'          => ' class="input-xlarge" multiple="multiple" ',
					'list.select'        => $selectedCategoryIds));
		}
		else
		{
			$options   = array();
			$options[] = JHtml::_('select.option', -1, JText::_('EB_ALL_EVENTS'), 'id', 'title');
			$rows      = EventbookingHelperDatabase::getAllEvents($config->sort_events_dropdown, $config->hide_past_events_from_events_dropdown);
			if ($config->show_event_date)
			{
				for ($i = 0, $n = count($rows); $i < $n; $i++)
				{
					$row       = $rows[$i];
					$options[] = JHtml::_('select.option', $row->id,
						$row->title . ' (' . JHtml::_('date', $row->event_date, $config->date_format, null) . ')' . '', 'id', 'title');
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
				$query->clear();
				$query->select('event_id')
					->from('#__eb_field_events')
					->where('field_id=' . $this->item->id);
				$db->setQuery($query);
				$selectedEventIds = $db->loadColumn();
			}

			$this->lists['event_id'] = JHtml::_('select.genericlist', $options, 'event_id[]', 'class="input-xlarge" multiple="multiple" size="5" ', 'id',
				'title', $selectedEventIds);
		}
		$this->lists['required']       = JHtml::_('select.booleanlist', 'required', '', $this->item->required);
		$this->lists['fee_field']      = JHtml::_('select.booleanlist', 'fee_field', '', $this->item->fee_field);
		$this->lists['quantity_field'] = JHtml::_('select.booleanlist', 'quantity_field', '', $this->item->quantity_field);
		$this->lists['only_show_for_first_member'] = JHtml::_('select.booleanlist', 'only_show_for_first_member', '', $this->item->only_show_for_first_member);
		$this->lists['only_require_for_first_member'] = JHtml::_('select.booleanlist', 'only_require_for_first_member', '', $this->item->only_require_for_first_member);
		$this->lists['multiple']       = JHtml::_('select.booleanlist', 'multiple', ' class="inputbox" ', $this->item->multiple);

		// Trigger plugins to get list of fields for mapping
		JPluginHelper::importPlugin( 'eventbooking');

		$results = JFactory::getApplication()->triggerEvent( 'onGetFields', array());
		$fields = array();
		if (count($results))
		{
			foreach($results as $res)
			{
				if (is_array($res) && count($res))
				{
					$fields = $res;
					break;
				}
			}
		}

		if (count($fields))
		{
			$options = array();
			$options[] = JHtml::_('select.option', '', JText::_('Select Field'));
			$options = array_merge($options, $fields);
			$this->lists['field_mapping'] = JHtml::_('select.genericlist', $options, 'field_mapping', ' class="inputbox" ', 'value', 'text',
				$this->item->field_mapping);
		}

		$options                            = array();
		$options[]                          = JHtml::_('select.option', 0, JText::_('EB_ALL'));
		$options[]                          = JHtml::_('select.option', 1, JText::_('EB_INDIVIDUAL_BILLING'));
		$options[]                          = JHtml::_('select.option', 2, JText::_('EB_GROUP_BILLING'));
		$options[]                          = JHtml::_('select.option', 3, JText::_('EB_INDIVIDUAL_GROUP_BILLING'));
		$options[]                          = JHtml::_('select.option', 4, JText::_('EB_GROUP_MEMBER_FORM'));
		$options[]                          = JHtml::_('select.option', 5, JText::_('EB_GROUP_MEMBER_INDIVIDUAL'));
		$this->lists['display_in']          = JHtml::_('select.genericlist', $options, 'display_in', ' class="inputbox" ', 'value', 'text',
			$this->item->display_in);
		$options                            = array();
		$options[]                          = JHtml::_('select.option', 0, JText::_('None'));
		$options[]                          = JHtml::_('select.option', 1, JText::_('Integer Number'));
		$options[]                          = JHtml::_('select.option', 2, JText::_('Number'));
		$options[]                          = JHtml::_('select.option', 3, JText::_('Email'));
		$options[]                          = JHtml::_('select.option', 4, JText::_('Url'));
		$options[]                          = JHtml::_('select.option', 5, JText::_('Phone'));
		$options[]                          = JHtml::_('select.option', 6, JText::_('Past Date'));
		$options[]                          = JHtml::_('select.option', 7, JText::_('Ip'));
		$options[]                          = JHtml::_('select.option', 8, JText::_('Min size'));
		$options[]                          = JHtml::_('select.option', 9, JText::_('Max size'));
		$options[]                          = JHtml::_('select.option', 10, JText::_('Min integer'));
		$options[]                          = JHtml::_('select.option', 11, JText::_('Max integer'));
		$this->lists['datatype_validation'] = JHtml::_('select.genericlist', $options, 'datatype_validation', 'class="inputbox"', 'value', 'text',
			$this->item->datatype_validation);

		$query->clear();
		$query->select('id, title')
			->from('#__eb_fields')
			->where('fieldtype IN ("List", "Radio", "Checkboxes")')
			->where('published=1');
		$db->setQuery($query);
		$options                           = array();
		$options[]                         = JHtml::_('select.option', 0, JText::_('Select'), 'id', 'title');
		$options                           = array_merge($options, $db->loadObjectList());
		$this->lists['depend_on_field_id'] = JHtml::_('select.genericlist', $options, 'depend_on_field_id',
			'class="inputbox" onchange="updateDependOnOptions();"', 'id', 'title', $this->item->depend_on_field_id);
		if ($this->item->depend_on_field_id)
		{
			//Get the selected options
			$this->dependOnOptions = explode(",", $this->item->depend_on_options);
			$query->clear();
			$query->select('`values`')
				->from('#__eb_fields')
				->where('id=' . $this->item->depend_on_field_id);
			$db->setQuery($query);
			$this->dependOptions = explode("\r\n", $db->loadResult());
		}

		//$this->integration = $integration;
		$this->config      = $config;
	}
}