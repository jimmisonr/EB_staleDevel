<?php
/**
 * @version        	1.6.9
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventbookingViewConfigurationHtml extends RADViewHtml
{

	public function display()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$config = $this->model->getData();
		$options = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_NO_INTEGRATION'));
		if (file_exists(JPATH_ROOT . '/components/com_comprofiler/comprofiler.php'))
		{
			$options[] = JHtml::_('select.option', 1, JText::_('EB_CB'));
		}
		if (file_exists(JPATH_ROOT . '/components/com_community/community.php'))
		{
			$options[] = JHtml::_('select.option', 2, JText::_('EB_JS'));
		}
		if (file_exists(JPATH_ROOT . '/components/com_osmembership/osmembership.php'))
		{
			$options[] = JHtml::_('select.option', 3, JText::_('EB_MEMBERSHIP_PRO'));
		}
		if (JPluginHelper::isEnabled('user', 'profile'))
		{
			$options[] = JHtml::_('select.option', 4, JText::_('EB_JOOMLA_PROFILE'));
		}
		$lists['custom_field_by_category'] = JHtml::_('select.booleanlist', 'custom_field_by_category', '', $config->custom_field_by_category);
		$lists['cb_integration'] = JHtml::_('select.genericlist', $options, 'cb_integration', ' class="inputbox" ', 'value', 'text', 
			$config->cb_integration);
		$lists['user_registration'] = JHtml::_('select.booleanlist', 'user_registration', '', $config->user_registration);
		$lists['simply_registration_process'] = JHtml::_('select.booleanlist', 'simply_registration_process', '', 
			$config->simply_registration_process);
		$lists['use_https'] = JHtml::_('select.booleanlist', 'use_https', '', $config->use_https);
		$lists['collect_member_information'] = JHtml::_('select.booleanlist', 'collect_member_information', '', $config->collect_member_information);
		$lists['show_pending_registrants'] = JHtml::_('select.booleanlist', 'show_pending_registrants', '', $config->show_pending_registrants);
		$lists['event_custom_field'] = JHtml::_('select.booleanlist', 'event_custom_field', '', $config->event_custom_field);
		$lists['load_bootstrap_css_in_frontend'] = JHtml::_('select.booleanlist', 'load_bootstrap_css_in_frontend', '', 
			isset($config->load_bootstrap_css_in_frontend) ? $config->load_bootstrap_css_in_frontend : 1);
		$lists['load_jquery'] = JHtml::_('select.booleanlist', 'load_jquery', '', isset($config->load_jquery) ? $config->load_jquery : 1);
		$lists['multiple_booking'] = JHtml::_('select.booleanlist', 'multiple_booking', '', $config->multiple_booking);
		$lists['prevent_duplicate_registration'] = JHtml::_('select.booleanlist', 'prevent_duplicate_registration', '', 
			$config->prevent_duplicate_registration);
		$options = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_SUNDAY'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_MONDAY'));
		$lists['calendar_start_date'] = JHtml::_('select.genericlist', $options, 'calendar_start_date', ' class="inputbox" ', 'value', 'text', 
			$config->calendar_start_date);
		$options = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_NO_NO'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_FREE_EVENT_ONLY'));
		$options[] = JHtml::_('select.option', 2, JText::_('EB_PAID_EVENT_ONLY'));
		$options[] = JHtml::_('select.option', 3, JText::_('EB_BOTH_FREE_AND_PAID'));
		$lists['enable_captcha'] = JHtml::_('select.booleanlist', 'enable_captcha', '', $config->enable_captcha > 0 ? "1" : "0");
		$lists['bypass_captcha_for_registered_user'] = JHtml::_('select.booleanlist', 'bypass_captcha_for_registered_user', '', 
			$config->bypass_captcha_for_registered_user);
		$lists['fix_next_button'] = JHtml::_('select.booleanlist', 'fix_next_button', '', $config->fix_next_button);
		$lists['fix_term_and_condition_popup'] = JHtml::_('select.booleanlist', 'fix_term_and_condition_popup', '', 
			$config->fix_term_and_condition_popup);
		$lists['activate_recurring_event'] = JHtml::_('select.booleanlist', 'activate_recurring_event', '', $config->activate_recurring_event);
		$lists['fix_breadcrumbs'] = JHtml::_('select.booleanlist', 'fix_breadcrumbs', '', $config->fix_breadcrumbs);
		$lists['send_ics_file'] = JHtml::_('select.booleanlist', 'send_ics_file', '', $config->send_ics_file);
		
		$query->select('id, title')
			->from('#__content')
			->where('`state` = 1')
			->order('title');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$options = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_SELECT_ARTICLE'), 'id', 'title');
		$options = array_merge($options, $rows);
		$lists['article_id'] = JHtml::_('select.genericlist', $options, 'article_id', ' class="inputbox" ', 'id', 'title', $config->article_id);
		$lists['active_term'] = JHtml::_('select.booleanlist', 'accept_term', '', $config->accept_term);
		$lists['term_condition_by_event'] = JHtml::_('select.booleanlist', 'term_condition_by_event', '', $config->term_condition_by_event);
		$lists['hide_past_events'] = JHtml::_('select.booleanlist', 'hide_past_events', '', $config->hide_past_events);
		$lists['send_email_to_group_members'] = JHtml::_('select.booleanlist', 'send_email_to_group_members', '', 
			$config->send_email_to_group_members);
		$lists['enable_coupon'] = JHtml::_('select.booleanlist', 'enable_coupon', '', $config->enable_coupon);
		$lists['enable_tax'] = JHtml::_('select.booleanlist', 'enable_tax', '', $config->enable_tax);
		
		$options = array();
		$options[] = JHtml::_('select.option', 1, JText::_('EB_ORDERING'));
		$options[] = JHtml::_('select.option', 2, JText::_('EB_EVENT_DATE'));
		$lists['order_events'] = JHtml::_('select.genericlist', $options, 'order_events', '  class="inputbox" ', 'value', 'text', 
			$config->order_events);
		$options = array();
		$options[] = JHTML::_('select.option', 'asc', JText::_('EB_ASC'));
		$options[] = JHTML::_('select.option', 'desc', JText::_('EB_DESC'));
		$lists['order_direction'] = JHTML::_('select.genericlist', $options, 'order_direction', '', 'value', 'text', $config->order_direction);
		
		//Get list of country
		$query->clear();
		$query->select('name AS value, name AS text')
			->from('#__eb_countries')
			->order('name');
		$db->setQuery($query);
		$rowCountries = $db->loadObjectList();
		$options = array();
		$options[] = JHtml::_('select.option', '', JText::_('EB_SELECT_DEFAULT_COUNTRY'));
		$options = array_merge($options, $rowCountries);
		$lists['country_list'] = JHtml::_('select.genericlist', $options, 'default_country', '', 'value', 'text', $config->default_country);
		
		$options = array();
		$options[] = JHtml::_('select.option', ',', JText::_('EB_COMMA'));
		$options[] = JHtml::_('select.option', ';', JText::_('EB_SEMICOLON'));
		$lists['csv_delimiter'] = JHtml::_('select.genericlist', $options, 'csv_delimiter', '', 'value', 'text', $config->csv_delimiter);
		
		$options = array();
		$options[] = JHtml::_('select.option', '', JText::_('EB_DEFAULT'));
		$options[] = JHtml::_('select.option', 'simple', JText::_('EB_SIMPLE_FORM'));
		$lists['submit_event_form_layout'] = JHtml::_('select.genericlist', $options, 'submit_event_form_layout', '', 'value', 'text', 
			$config->submit_event_form_layout);
		//Theme configuration						
		$options = array();
		$options[] = JHtml::_('select.option', 'default', JText::_('EB_DEFAULT'));
		$options[] = JHtml::_('select.option', 'fire', JText::_('EB_FIRE'));
		$options[] = JHtml::_('select.option', 'leaf', JText::_('EB_LEAF'));
		$options[] = JHtml::_('select.option', 'sky', JText::_('EB_SKY'));
		$options[] = JHtml::_('select.option', 'tree', JText::_('EB_TREE'));
		$options[] = JHtml::_('select.option', 'dark', JText::_('EB_DARK'));
		$lists['calendar_theme'] = JHtml::_('select.genericlist', $options, 'calendar_theme', ' class="inputbox" ', 'value', 'text', 
			$config->calendar_theme);
		$lists['show_event_time'] = JHtml::_('select.booleanlist', 'show_event_time', '', $config->show_event_time);
		$lists['activate_deposit_feature'] = JHtml::_('select.booleanlist', 'activate_deposit_feature', '', $config->activate_deposit_feature);
		$lists['activate_waitinglist_feature'] = JHtml::_('select.booleanlist', 'activate_waitinglist_feature', '', 
			$config->activate_waitinglist_feature);
		$lists['show_empty_cat'] = JHtml::_('select.booleanlist', 'show_empty_cat', '', $config->show_empty_cat);
		$lists['show_number_events'] = JHtml::_('select.booleanlist', 'show_number_events', '', $config->show_number_events);
		$lists['show_capacity'] = JHtml::_('select.booleanlist', 'show_capacity', '', $config->show_capacity);
		$lists['show_registered'] = JHtml::_('select.booleanlist', 'show_registered', '', $config->show_registered);
		$lists['show_available_place'] = JHtml::_('select.booleanlist', 'show_available_place', '', $config->show_available_place);
		$lists['show_list_of_registrants'] = JHtml::_('select.booleanlist', 'show_list_of_registrants', '', $config->show_list_of_registrants);
		$lists['show_event_custom_field_in_category_layout'] = JHtml::_('select.booleanlist', 'show_event_custom_field_in_category_layout', '', 
			$config->show_event_custom_field_in_category_layout);
		$lists['process_plugin'] = JHtml::_('select.booleanlist', 'process_plugin', '', $config->process_plugin);
		$lists['show_cat_decription_in_table_layout'] = JHtml::_('select.booleanlist', 'show_cat_decription_in_table_layout', '', 
			$config->show_cat_decription_in_table_layout);
		$lists['show_price_in_table_layout'] = JHtml::_('select.booleanlist', 'show_price_in_table_layout', '', $config->show_price_in_table_layout);
		$lists['show_image_in_table_layout'] = JHtml::_('select.booleanlist', 'show_image_in_table_layout', '', $config->show_image_in_table_layout);
		$lists['show_cat_decription_in_calendar_layout'] = JHtml::_('select.booleanlist', 'show_cat_decription_in_calendar_layout', '', 
			$config->show_cat_decription_in_calendar_layout);
		$lists['display_message_for_full_event'] = JHtml::_('select.booleanlist', 'display_message_for_full_event', '', 
			$config->display_message_for_full_event);
		$lists['show_event_date'] = JHtml::_('select.booleanlist', 'show_event_date', '', $config->show_event_date);
		$lists['show_location_in_category_view'] = JHtml::_('select.booleanlist', 'show_location_in_category_view', '', 
			$config->show_location_in_category_view);
		$lists['show_fb_like_button'] = JHtml::_('select.booleanlist', 'show_fb_like_button', '', $config->show_fb_like_button);
		$lists['show_social_bookmark'] = JHtml::_('select.booleanlist', 'show_social_bookmark', '', $config->show_social_bookmark);
		$lists['show_invite_friend'] = JHtml::_('select.booleanlist', 'show_invite_friend', '', $config->show_invite_friend);
		$lists['show_price_for_free_event'] = JHtml::_('select.booleanlist', 'show_price_for_free_event', '', $config->show_price_for_free_event);
		$lists['include_group_billing_in_csv_export'] = JHtml::_('select.booleanlist', 'include_group_billing_in_csv_export', '', 
			isset($config->include_group_billing_in_csv_export) ? $config->include_group_billing_in_csv_export : 1);
		$lists['include_group_billing_in_registrants'] = JHtml::_('select.booleanlist', 'include_group_billing_in_registrants', '', 
			isset($config->include_group_billing_in_registrants) ? $config->include_group_billing_in_registrants : 1);
		$lists['include_group_members_in_csv_export'] = JHtml::_('select.booleanlist', 'include_group_members_in_csv_export', '', 
			isset($config->include_group_members_in_csv_export) ? $config->include_group_members_in_csv_export : 0);
		$lists['include_group_members_in_registrants'] = JHtml::_('select.booleanlist', 'include_group_members_in_registrants', '', 
			isset($config->include_group_members_in_registrants) ? $config->include_group_members_in_registrants : 0);
		$lists['show_event_location_in_email'] = JHtml::_('select.booleanlist', 'show_event_location_in_email', '', 
			$config->show_event_location_in_email);
		$lists['show_discounted_price'] = JHtml::_('select.booleanlist', 'show_discounted_price', '', $config->show_discounted_price);
		$lists['activate_weekly_calendar_view'] = JHtml::_('select.booleanlist', 'activate_weekly_calendar_view', '', 
			$config->activate_weekly_calendar_view);
		$lists['activate_daily_calendar_view'] = JHtml::_('select.booleanlist', 'activate_daily_calendar_view', '', 
			$config->activate_daily_calendar_view);
		$lists['show_coupon_code_in_registrant_list'] = JHtml::_('select.booleanlist', 'show_coupon_code_in_registrant_list', '', 
			$config->show_coupon_code_in_registrant_list);
		$lists['show_multiple_days_event_in_calendar'] = JHtml::_('select.booleanlist', 'show_multiple_days_event_in_calendar', '', 
			$config->show_multiple_days_event_in_calendar);
		#Waitinglist fields configuration		
		$lists['swt_lastname'] = JHtml::_('select.booleanlist', 'swt_lastname', '', $config->swt_lastname);
		$lists['rwt_lastname'] = JHtml::_('select.booleanlist', 'rwt_lastname', '', $config->rwt_lastname);
		$lists['swt_organization'] = JHtml::_('select.booleanlist', 'swt_organization', '', $config->swt_organization);
		$lists['rwt_organization'] = JHtml::_('select.booleanlist', 'rwt_organization', '', $config->rwt_organization);
		$lists['swt_address'] = JHtml::_('select.booleanlist', 'swt_address', '', $config->swt_address);
		$lists['rwt_address'] = JHtml::_('select.booleanlist', 'rwt_address', '', $config->rwt_address);
		$lists['swt_address2'] = JHtml::_('select.booleanlist', 'swt_address2', '', $config->swt_address2);
		$lists['rwt_address2'] = JHtml::_('select.booleanlist', 'rwt_address2', '', $config->rwt_address2);
		$lists['swt_city'] = JHtml::_('select.booleanlist', 'swt_city', '', $config->swt_city);
		$lists['rwt_city'] = JHtml::_('select.booleanlist', 'rwt_city', '', $config->rwt_city);
		$lists['swt_state'] = JHtml::_('select.booleanlist', 'swt_state', '', $config->swt_state);
		$lists['rwt_state'] = JHtml::_('select.booleanlist', 'rwt_state', '', $config->rwt_state);
		$lists['swt_zip'] = JHtml::_('select.booleanlist', 'swt_zip', '', $config->swt_zip);
		$lists['rwt_zip'] = JHtml::_('select.booleanlist', 'rwt_zip', '', $config->rwt_zip);
		$lists['swt_country'] = JHtml::_('select.booleanlist', 'swt_country', '', $config->swt_country);
		$lists['rwt_country'] = JHtml::_('select.booleanlist', 'rwt_country', '', $config->rwt_country);
		$lists['swt_phone'] = JHtml::_('select.booleanlist', 'swt_phone', '', $config->swt_phone);
		$lists['rwt_phone'] = JHtml::_('select.booleanlist', 'rwt_phone', '', $config->rwt_phone);
		$lists['swt_fax'] = JHtml::_('select.booleanlist', 'swt_fax', '', $config->swt_fax);
		$lists['rwt_fax'] = JHtml::_('select.booleanlist', 'rwt_fax', '', $config->rwt_fax);
		$lists['swt_comment'] = JHtml::_('select.booleanlist', 'swt_comment', '', $config->swt_comment);
		$lists['rwt_comment'] = JHtml::_('select.booleanlist', 'rwt_comment', '', $config->rwt_comment);
		$options = array();
		$options[] = JHtml::_('select.option', '', JText::_('EB_SELECT_POSITION'));
		$options[] = JHtml::_('select.option', 0, JText::_('EB_BEFORE_AMOUNT'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_AFTER_AMOUNT'));
		$lists['currency_position'] = JHtml::_('select.genericlist', $options, 'currency_position', ' class="inputbox"', 'value', 'text', 
			$config->currency_position);
		//Default settings when creating new events
		$options = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_INDIVIDUAL_GROUP'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_INDIVIDUAL_ONLY'));
		$options[] = JHtml::_('select.option', 2, JText::_('EB_GROUP_ONLY'));
		$options[] = JHtml::_('select.option', 3, JText::_('EB_DISABLE_REGISTRATION'));
		$lists['registration_type'] = JHtml::_('select.genericlist', $options, 'registration_type', ' class="inputbox" ', 'value', 'text', 
			isset($config->registration_type) ? $config->registration_type : 0);
		$lists['access'] = JHtml::_('access.level', 'access', isset($config->access) ? $config->access : 1, 'class="inputbox"', false);
		$lists['registration_access'] = JHtml::_('access.level', 'registration_access', 
			isset($config->registration_access) ? $config->registration_access : 1, 'class="inputbox"', false);
		
		$options = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_UNPUBLISHED'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_PUBLISHED'));
		$lists['default_event_status'] = JHtml::_('select.genericlist', $options, 'default_event_status', ' class="inputbox"', 'value', 'text', 
			isset($config->default_event_status) ? $config->default_event_status : 0);
		
		#Invoice settings
		$lists['activate_invoice_feature'] = JHtml::_('select.booleanlist', 'activate_invoice_feature', '', $config->activate_invoice_feature);
		$lists['send_invoice_to_customer'] = JHtml::_('select.booleanlist', 'send_invoice_to_customer', '', $config->send_invoice_to_customer);
		
		#SEF setting
		$lists['insert_event_id'] = JHtml::_('select.booleanlist', 'insert_event_id', '', $config->insert_event_id);
		$options = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_ALL_NESTED_CATEGORIES'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_ONLY_LAST_ONE'));
		$lists['insert_category'] = JHtml::_('select.genericlist', $options, 'insert_category', ' class="inputbox"', 'value', 'text', 
			$config->insert_category);
		$this->lists = $lists;
		$this->config = $config;
		$this->addToolbar();
		
		parent::display();
	}

	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('EB_CONFIGURATION'), 'generic.png');
		JToolBarHelper::apply('apply', 'JTOOLBAR_APPLY');
		JToolBarHelper::save('save');
		JToolBarHelper::cancel();
		JToolBarHelper::preferences($this->option);
	}
}