<?php
/**
 * @version        	1.6.10
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventbookingViewFieldHtml extends RADViewItem
{

	function display()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$config = EventbookingHelper::getConfig();
		$fieldTypes = array('Text', 'Textarea', 'List', 'Checkboxes', 'Radio', 'Date', 'Heading', 'Message', 'File', 'Countries', 'State', 'SQL');
		$options = array();
		$options[] = JHtml::_('select.option', -1, JText::_('EB_FIELD_TYPE'));
		$options = array();
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
			$query->clear();
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
			$this->lists['category_id'] = JHtml::_('select.genericlist', $options, 'category_id', 
				array(
					'option.text.toHtml' => false, 
					'option.text' => 'text', 
					'option.value' => 'value', 
					'list.attr' => '', 
					'list.select' => $this->item->category_id));
		}
		else
		{
			$options = array();
			$options[] = JHtml::_('select.option', -1, JText::_('EB_ALL_EVENTS'), 'id', 'title');
			$query->clear();
			$query->select('id, title, event_date')
				->from('#__eb_events')
				->where('published=1')
				->order('title, ordering');
			$db->setQuery($query);
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
			$selecteds = array();
			if ($this->item->id)
			{
				if ($this->item->event_id == -1)
				{
					$selecteds[] = -1;
				}
				else
				{
					$query->clear();
					$query->select('event_id')
						->from('#__eb_field_events')
						->where('field_id='.$this->item->id);											
					$db->setQuery($query);
					$selecteds = $db->loadColumn();					
				}
			}			
			$this->lists['event_id'] = JHtml::_('select.genericlist', $options, 'event_id[]', 'class="inputbox" multiple="multiple" size="5" ', 'id', 
				'title', $selecteds);
		}
		$this->lists['required'] = JHtml::_('select.booleanlist', 'required', ' class="inputbox" ', $this->item->required);
		$this->lists['fee_field'] = JHtml::_('select.booleanlist', 'fee_field', ' class="inputbox" ', $this->item->fee_field);
		$this->lists['multiple'] = JHtml::_('select.booleanlist', 'multiple', ' class="inputbox" ', $this->item->multiple);
		$integration = $config->cb_integration;
		if ($integration)
		{
			$options = array();
			$options[] = JHtml::_('select.option', '', JText::_('Select Field'));
			if ($integration == 1 || $integration == 2)
			{
				if ($integration == 1)
				{
					$sql = 'SELECT name AS `value`, name AS `text` FROM #__comprofiler_fields WHERE `table`="#__comprofiler"';
				}
				else
				{
					$sql = 'SELECT fieldcode AS `value`, fieldcode AS `text` FROM #__community_fields WHERE published=1 AND fieldcode != ""';
				}
				$db->setQuery($sql);
				$options = array_merge($options, $db->loadObjectList());
				$this->lists['field_mapping'] = JHtml::_('select.genericlist', $options, 'field_mapping', ' class="inputbox" ', 'value', 'text', 
					$this->item->field_mapping);
			}
			elseif ($integration == 3)
			{
				//Get list of profiles file from Membership Pro
				require_once JPATH_ROOT . '/components/com_osmembership/helper/helper.php';
				$fields = OSMembershipHelper::getProfileFields(0);
				foreach ($fields as $field)
				{
					$options[] = JHtml::_('select.option', $field->name, $field->title);
				}
				$this->lists['field_mapping'] = JHtml::_('select.genericlist', $options, 'field_mapping', ' class="inputbox" ', 'value', 'text', 
					$this->item->field_mapping);
			}
			elseif ($integration == 4)
			{
				$fields = array(
					'address1', 
					'address2', 
					'city', 
					'region', 
					'country', 
					'postal_code', 
					'phone', 
					'website', 
					'favoritebook', 
					'aboutme', 
					'dob');
				foreach ($fields as $field)
				{
					$options[] = JHtml::_('select.option', $field, $field);
				}
				$this->lists['field_mapping'] = JHtml::_('select.genericlist', $options, 'field_mapping', ' class="inputbox" ', 'value', 'text', 
					$this->item->field_mapping);
			}
		}
		
		$options = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_ALL'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_INDIVIDUAL_BILLING'));
		$options[] = JHtml::_('select.option', 2, JText::_('EB_GROUP_BILLING'));
		$options[] = JHtml::_('select.option', 3, JText::_('EB_INDIVIDUAL_GROUP_BILLING'));
		$options[] = JHtml::_('select.option', 4, JText::_('EB_GROUP_MEMBER_FORM'));
		$options[] = JHtml::_('select.option', 5, JText::_('EB_GROUP_MEMBER_INDIVIDUAL'));
		$this->lists['display_in'] = JHtml::_('select.genericlist', $options, 'display_in', ' class="inputbox" ', 'value', 'text', 
			$this->item->display_in);
		$options = array();
		$options[] = JHtml::_('select.option', 0, JText::_('None'));
		$options[] = JHtml::_('select.option', 1, JText::_('Integer Number'));
		$options[] = JHtml::_('select.option', 2, JText::_('Number'));
		$options[] = JHtml::_('select.option', 3, JText::_('Email'));
		$options[] = JHtml::_('select.option', 4, JText::_('Url'));
		$options[] = JHtml::_('select.option', 5, JText::_('Phone'));
		$options[] = JHtml::_('select.option', 6, JText::_('Past Date'));
		$options[] = JHtml::_('select.option', 7, JText::_('Ip'));
		$options[] = JHtml::_('select.option', 8, JText::_('Min size'));
		$options[] = JHtml::_('select.option', 9, JText::_('Max size'));
		$options[] = JHtml::_('select.option', 10, JText::_('Min integer'));
		$options[] = JHtml::_('select.option', 11, JText::_('Max integer'));
		$this->lists['datatype_validation'] = JHtml::_('select.genericlist', $options, 'datatype_validation', 'class="inputbox"', 'value', 'text', 
			$this->item->datatype_validation);
		
		$query->clear();
		$query->select('id, title')
			->from('#__eb_fields')
			->where('fieldtype IN ("List", "Radio", "Checkboxes")')
			->where('published=1');
		$db->setQuery($query);
		$options = array();
		$options[] = JHtml::_('select.option', 0, JText::_('Select'), 'id', 'title');
		$options = array_merge($options, $db->loadObjectList());
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
		
		if (JPluginHelper::isEnabled('osmembership', 'userprofile'))
		{
			$options = array();
			$options[] = JHtml::_('select.option', '', JText::_('Select Field'));
			
			$fields = array('address1', 'address2', 'city', 'region', 'country', 'postal_code', 'phone', 'website', 'favoritebook', 'aboutme', 'dob');
			
			foreach ($fields as $field)
			{
				$options[] = JHtml::_('select.option', $field);
			}
			$this->lists['profile_field_mapping'] = JHtml::_('select.genericlist', $options, 'profile_field_mapping', ' class="inputbox" ', 'value', 
				'text', $this->item->profile_field_mapping);
		}
		$this->integration = $integration;
		$this->config = $config;
		
		parent::display();
	}
}