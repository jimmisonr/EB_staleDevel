<?php
/**
 * @version            2.2.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

class EventbookingViewConfigurationHtml extends RADViewHtml
{

	public function display()
	{
		$db        = JFactory::getDbo();
		$query     = $db->getQuery(true);
		$config    = EventbookingHelper::getConfig();

		$options                            = array();
		$options[]                          = JHtml::_('select.option', 2, JText::_('EB_VERSION_2'));
		$options[]                          = JHtml::_('select.option', 3, JText::_('EB_VERSION_3'));
		$lists['twitter_bootstrap_version'] = JHtml::_('select.genericlist', $options, 'twitter_bootstrap_version', '', 'value', 'text', $config->get('twitter_bootstrap_version', 2));

		$options                                     = array();
		$options[]                                   = JHtml::_('select.option', 0, JText::_('EB_SUNDAY'));
		$options[]                                   = JHtml::_('select.option', 1, JText::_('EB_MONDAY'));
		$lists['calendar_start_date']                = JHtml::_('select.genericlist', $options, 'calendar_start_date', ' class="inputbox" ', 'value', 'text',
			$config->calendar_start_date);

		$query->select('id, title')
			->from('#__content')
			->where('`state` = 1')
			->order('title');
		$db->setQuery($query);
		$rows                                 = $db->loadObjectList();
		$options                              = array();
		$options[]                            = JHtml::_('select.option', 0, JText::_('EB_SELECT_ARTICLE'), 'id', 'title');
		$options                              = array_merge($options, $rows);
		$lists['article_id']                  = JHtml::_('select.genericlist', $options, 'article_id', ' class="inputbox" ', 'id', 'title', $config->article_id);

		$options                  = array();
		$options[]                = JHtml::_('select.option', 1, JText::_('EB_ORDERING'));
		$options[]                = JHtml::_('select.option', 2, JText::_('EB_EVENT_DATE'));
		$lists['order_events']    = JHtml::_('select.genericlist', $options, 'order_events', '  class="inputbox" ', 'value', 'text',
			$config->order_events);
		$options                  = array();
		$options[]                = JHTML::_('select.option', 'asc', JText::_('EB_ASC'));
		$options[]                = JHTML::_('select.option', 'desc', JText::_('EB_DESC'));
		$lists['order_direction'] = JHTML::_('select.genericlist', $options, 'order_direction', '', 'value', 'text', $config->order_direction);

		//Get list of country
		$query->clear();
		$query->select('name AS value, name AS text')
			->from('#__eb_countries')
			->order('name');
		$db->setQuery($query);
		$rowCountries          = $db->loadObjectList();
		$options               = array();
		$options[]             = JHtml::_('select.option', '', JText::_('EB_SELECT_DEFAULT_COUNTRY'));
		$options               = array_merge($options, $rowCountries);
		$lists['country_list'] = JHtml::_('select.genericlist', $options, 'default_country', '', 'value', 'text', $config->default_country);

		$options                = array();
		$options[]              = JHtml::_('select.option', ',', JText::_('EB_COMMA'));
		$options[]              = JHtml::_('select.option', ';', JText::_('EB_SEMICOLON'));
		$lists['csv_delimiter'] = JHtml::_('select.genericlist', $options, 'csv_delimiter', '', 'value', 'text', $config->csv_delimiter);

		$options                           = array();
		$options[]                         = JHtml::_('select.option', '', JText::_('EB_DEFAULT'));
		$options[]                         = JHtml::_('select.option', 'simple', JText::_('EB_SIMPLE_FORM'));
		$lists['submit_event_form_layout'] = JHtml::_('select.genericlist', $options, 'submit_event_form_layout', '', 'value', 'text',
			$config->submit_event_form_layout);
		//Theme configuration						
		$options                                             = array();
		$options[]                                           = JHtml::_('select.option', 'default', JText::_('EB_DEFAULT'));
		$options[]                                           = JHtml::_('select.option', 'fire', JText::_('EB_FIRE'));
		$options[]                                           = JHtml::_('select.option', 'leaf', JText::_('EB_LEAF'));
		$options[]                                           = JHtml::_('select.option', 'sky', JText::_('EB_SKY'));
		$options[]                                           = JHtml::_('select.option', 'tree', JText::_('EB_TREE'));
		$options[]                                           = JHtml::_('select.option', 'dark', JText::_('EB_DARK'));
		$lists['calendar_theme']                             = JHtml::_('select.genericlist', $options, 'calendar_theme', ' class="inputbox" ', 'value', 'text',
			$config->calendar_theme);

		$options                           = array();
		$options[]                         = JHtml::_('select.option', '', JText::_('EB_SELECT_POSITION'));
		$options[]                         = JHtml::_('select.option', 0, JText::_('EB_BEFORE_AMOUNT'));
		$options[]                         = JHtml::_('select.option', 1, JText::_('EB_AFTER_AMOUNT'));
		$lists['currency_position']        = JHtml::_('select.genericlist', $options, 'currency_position', ' class="inputbox"', 'value', 'text',
			$config->currency_position);

		//Default settings when creating new events
		$options                      = array();
		$options[]                    = JHtml::_('select.option', 0, JText::_('EB_INDIVIDUAL_GROUP'));
		$options[]                    = JHtml::_('select.option', 1, JText::_('EB_INDIVIDUAL_ONLY'));
		$options[]                    = JHtml::_('select.option', 2, JText::_('EB_GROUP_ONLY'));
		$options[]                    = JHtml::_('select.option', 3, JText::_('EB_DISABLE_REGISTRATION'));
		$lists['registration_type']   = JHtml::_('select.genericlist', $options, 'registration_type', ' class="inputbox" ', 'value', 'text', $config->get('registration_type', 0));
		$lists['access']              = JHtml::_('access.level', 'access', $config->get('access', 1), 'class="inputbox"', false);
		$lists['registration_access'] = JHtml::_('access.level', 'registration_access', $config->get('registration_access', 1), 'class="inputbox"', false);

		$options                       = array();
		$options[]                     = JHtml::_('select.option', 0, JText::_('EB_UNPUBLISHED'));
		$options[]                     = JHtml::_('select.option', 1, JText::_('EB_PUBLISHED'));
		$lists['default_event_status'] = JHtml::_('select.genericlist', $options, 'default_event_status', ' class="inputbox"', 'value', 'text', $config->get('default_event_status', 0));

		$options = array();
		$options[] = JHtml::_('select.option', '', JText::_('EB_SELECT_FORMAT'));
		$options[] = JHtml::_('select.option', '%Y-%m-%d', 'Y-m-d');
		$options[] = JHtml::_('select.option', '%Y/%m/%d', 'Y/m/d');
		$options[] = JHtml::_('select.option', '%Y.%m.%d', 'Y.m.d');
		$options[] = JHtml::_('select.option', '%m-%d-%Y', 'm-d-Y');
		$options[] = JHtml::_('select.option', '%m/%d/%Y', 'm/d/Y');
		$options[] = JHtml::_('select.option', '%m.%d.%Y', 'm.d.Y');
		$options[] = JHtml::_('select.option', '%d-%m-%Y', 'd-m-Y');
		$options[] = JHtml::_('select.option', '%d/%m/%Y', 'd/m/Y');
		$options[] = JHtml::_('select.option', '%d.%m.%Y', 'd.m.Y');
		$lists['date_field_format'] = JHtml::_('select.genericlist', $options, 'date_field_format', '', 'value', 'text', isset($config->date_field_format) ? $config->date_field_format : 'Y-m-d');
		
		$options                  = array();
		$options[]                = JHtml::_('select.option', 0, JText::_('EB_ALL_NESTED_CATEGORIES'));
		$options[]                = JHtml::_('select.option', 1, JText::_('EB_ONLY_LAST_ONE'));
		$lists['insert_category'] = JHtml::_('select.genericlist', $options, 'insert_category', ' class="inputbox"', 'value', 'text',
			$config->insert_category);
		$this->lists              = $lists;
		$this->config             = $config;
		$this->addToolbar();
		$this->languages = EventbookingHelper::getLanguages();

		parent::display();
	}

	/**
	 * Override addToolbar method to use custom buttons for this view
	 */
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('EB_CONFIGURATION'), 'generic.png');
		JToolBarHelper::apply('apply', 'JTOOLBAR_APPLY');
		JToolBarHelper::save('save');
		JToolBarHelper::cancel();
		JToolBarHelper::preferences('com_eventbooking');
	}
}