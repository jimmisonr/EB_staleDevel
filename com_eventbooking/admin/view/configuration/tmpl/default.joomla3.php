<?php
/**
 * @version        	2.0.0
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die ;
JHtml::_('bootstrap.tooltip');
$document = JFactory::getDocument();
$document->addStyleDeclaration(".hasTip{display:block !important}");

$editor = JFactory::getEditor();
$config = $this->config;
?>
<form action="index.php?option=com_eventbooking&view=configuration" method="post" name="adminForm" id="adminForm" class="form-horizontal eb-configuration">
	<div class="row-fluid">
		<ul class="nav nav-tabs">
			<li class="active"><a href="#general-page" data-toggle="tab"><?php echo JText::_('EB_GENERAL');?></a></li>					
			<li><a href="#theme-page" data-toggle="tab"><?php echo JText::_('EB_THEMES');?></a></li>
			<li><a href="#sef-setting-page" data-toggle="tab"><?php echo JText::_('EB_SEF_SETTING');?></a></li>						
			<li><a href="#invoice-page" data-toggle="tab"><?php echo JText::_('EB_INVOICE_SETTINGS');?></a></li>
		</ul>
		<div class="tab-content">			
			<div class="tab-pane active" id="general-page">
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('custom_field_by_category', JText::_('EB_CUSTOM_FIELD_BY_CATEGORY'), JText::_('EB_CUSTOM_FIELD_BY_CATEGORY_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('custom_field_by_category', $config->custom_field_by_category); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('load_jquery', JText::_('EB_LOAD_JQUERY'), JText::_('EB_LOAD_JQUERY_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('load_jquery', $config->get('load_jquery', 1)); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('load_bootstrap_css_in_frontend', JText::_('EB_LOAD_BOOTSTRAP_CSS_IN_FRONTEND'), JText::_('EB_LOAD_BOOTSTRAP_CSS_IN_FRONTEND_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('load_bootstrap_css_in_frontend', $config->get('load_bootstrap_css_in_frontend', 1)); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('twitter_bootstrap_version', JText::_('EB_TWITTER_BOOTSTRAP_VERSION'), JText::_('EB_TWITTER_BOOTSTRAP_VERSION_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo $this->lists['twitter_bootstrap_version'];?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('from_name', JText::_('EB_FROM_NAME'), JText::_('EB_FROM_NAME_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<input type="text" name="from_name" class="inputbox" value="<?php echo $this->config->from_name; ?>" size="50" />
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('from_email', JText::_('EB_FROM_EMAIL'), JText::_('EB_FROM_EMAIL_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<input type="text" name="from_email" class="inputbox" value="<?php echo $this->config->from_email; ?>" size="50" />
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('notification_emails', JText::_('EB_NOTIFICATION_EMAILS'), JText::_('EB_NOTIFICATION_EMAILS_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<input type="text" name="notification_emails" class="inputbox" value="<?php echo $this->config->notification_emails; ?>" size="50" />
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('user_registration', JText::_('EB_USER_REGISTRATION_INTEGRATION'), JText::_('EB_REGISTRATION_INTEGRATION_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('user_registration', $config->user_registration); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('cb_integration', JText::_('EB_INTEGRATION')); ?>
					</div>
					<div class="controls">
						<?php echo $this->lists['cb_integration']; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('calendar_start_date', JText::_('EB_CALENDAR_START_DATE')); ?>
					</div>
					<div class="controls">
						<?php echo $this->lists['calendar_start_date']; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('send_ics_file', JText::_('EB_SEND_ICS_FILE'), JText::_('EB_SEND_ICS_FILE_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('send_ics_file', $config->send_ics_file); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('simply_registration_process', JText::_('EB_SIMPLY_REGISTRATION_PROCESS'), JText::_('EB_SIMPLY_REGISTRATION_PROCESS_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('simply_registration_process', $config->simply_registration_process); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('activate_deposit_feature', JText::_('EB_ACTIVATE_DEPOSIT_FEATURE'), JText::_('EB_ACTIVATE_DEPOSIT_FEATURE_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('activate_deposit_feature', $config->activate_deposit_feature); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('activate_waitinglist_feature', JText::_('EB_ACTIVATE_WAITINGLIST_FEATURE'), JText::_('EB_ACTIVATE_WAITINGLIST_FEATURE_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('activate_waitinglist_feature', $config->activate_waitinglist_feature); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('event_custom_field', JText::_('EB_EVENT_CUSTOM_FIELD'), JText::_('EB_EVENT_CUSTOM_FIELD_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('event_custom_field', $config->event_custom_field); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('registrant_list_custom_field_ids', JText::_('EB_REGISTRANT_LIST_CUSTOM_FIELD_IDS'), JText::_('EB_REGISTRANT_LIST_CUSTOM_FIELD_IDS_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<input type="text" name="registrant_list_custom_field_ids" value="<?php echo $this->config->registrant_list_custom_field_ids ; ?>" />
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('multiple_booking', JText::_('EB_MULTIPLE_BOOKING'), JText::_('EB_MULTIPLE_BOOKING_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('multiple_booking', $config->multiple_booking); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('prevent_duplicate_registration', JText::_('EB_PREVENT_DUPLICATE'), JText::_('EB_PREVENT_DUPLICATE_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('prevent_duplicate_registration', $config->prevent_duplicate_registration); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('enable_captcha', JText::_('EB_ENABLE_CAPTCHA'), JText::_('EB_CAPTCHA_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('enable_captcha', $config->enable_captcha); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('bypass_captcha_for_registered_user', JText::_('EB_BYPASS_CAPTCHA_FOR_REGISTERED_USER'), JText::_('EB_BYPASS_CAPTCHA_FOR_REGISTERED_USER_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('bypass_captcha_for_registered_user', $config->bypass_captcha_for_registered_user); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('enable_coupon', JText::_('EB_ENABLE_COUPON'), JText::_('EB_COUNPON_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('enable_coupon', $config->enable_coupon); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('show_pending_registrants', JText::_('EB_SHOW_PENDING_REGISTRANTS'), JText::_('EB_SHOW_PENDING_REGISTRANTS_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('show_pending_registrants', $config->show_pending_registrants); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('show_price_including_tax', JText::_('EB_SHOW_PRICE_INCLUDING_TAX'), JText::_('EB_SHOW_PRICE_INCLUDING_TAX_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('show_price_including_tax', $config->show_price_including_tax); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('collect_member_information', JText::_('EB_COLLECT_MEMBER_INFORMATION'), JText::_('EB_COLLECT_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('collect_member_information', $config->collect_member_information); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('send_email_to_group_members', JText::_('EB_SEND_CONFIRMATION_EMAIL_TO_GROUP_MEMBERS'), JText::_('EB_SEND_CONFIRMATION_EMAIL_TO_GROUP_MEMBERS_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('send_email_to_group_members', $config->send_email_to_group_members); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('include_group_billing_in_csv_export', JText::_('EB_INCLUDE_GROUP_BILLING_IN_CSV_EXPORT'), JText::_('EB_INCLUDE_GROUP_BILLING_IN_CSV_EXPORT_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('include_group_billing_in_csv_export', $config->get('include_group_billing_in_csv_export', 1)); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('include_group_billing_in_registrants', JText::_('EB_INCLUDE_GROUP_BILLING_IN_REGISTRANTS_MANAGEMENT'), JText::_('EB_INCLUDE_GROUP_BILLING_IN_REGISTRANTS_MANAGEMENT_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('include_group_billing_in_registrants', $config->get('include_group_billing_in_registrants', 1)); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('include_group_members_in_csv_export', JText::_('EB_INCLUDE_GROUP_MEMBERS_IN_CSV_EXPORT'), JText::_('EB_INCLUDE_GROUP_MEMBERS_IN_CSV_EXPORT_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('include_group_members_in_csv_export', $config->get('include_group_members_in_csv_export', 0)); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('include_group_members_in_registrants', JText::_('EB_INCLUDE_GROUP_MEMBERS_IN_REGISTRANTS_MANAGEMENT'), JText::_('EB_INCLUDE_GROUP_MEMBERS_IN_REGISTRANTS_MANAGEMENT_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('include_group_members_in_registrants', $config->get('include_group_members_in_registrants', 0)); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('only_show_registrants_of_event_owner', JText::_('EB_ONLY_SHOW_REGISTRANTS_OF_EVENT_OWNER'), JText::_('EB_ONLY_SHOW_REGISTRANTS_OF_EVENT_OWNER_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('only_show_registrants_of_event_owner', $config->only_show_registrants_of_event_owner); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('show_all_locations_in_event_submission_form', JText::_('EB_SHOW_ALL_LOCATIONS_IN_EVENT_SUBMISSION_FORM'), JText::_('EB_SHOW_ALL_LOCATIONS_IN_EVENT_SUBMISSION_FORM_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('show_all_locations_in_event_submission_form', $config->show_all_locations_in_event_submission_form); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('show_billing_step_for_free_events', JText::_('EB_SHOW_BILLING_STEP_FOR_FREE_EVENTS'), JText::_('EB_SHOW_BILLING_STEP_FOR_FREE_EVENTS_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('show_billing_step_for_free_events', $config->show_billing_step_for_free_events); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('zoom_level', JText::_('EB_ZOOM_LEVEL'), JText::_('EB_ZOOM_LEVEL_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo JHtml::_('select.integerlist', 1, 14, 1, 'zoom_level', 'class="inputbox"', $this->config->zoom_level); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('map_width', JText::_('EB_MAP_WIDTH'), JText::_('EB_MAP_WIDTH_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<input type="text" name="map_width" class="inputbox" value="<?php echo $this->config->map_width ; ?>" />
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('map_height', JText::_('EB_MAP_HEIGHT'), JText::_('EB_MAP_HEIGHT_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<input type="text" name="map_height" class="inputbox" value="<?php echo $this->config->map_height ; ?>" />
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('thumb_width', JText::_('EB_THUMB_WIDTH'), JText::_('EB_THUMB_WIDTH_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<input type="text" name="thumb_width" class="inputbox" value="<?php echo $this->config->thumb_width ; ?>" />
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('thumb_height', JText::_('EB_THUMB_HEIGHT'), JText::_('EB_THUMB_HEIGHT_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<input type="text" name="thumb_height" class="inputbox" value="<?php echo $this->config->thumb_height ; ?>" />
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('activate_recurring_event', JText::_('EB_ACTIVATE_RECURRING_EVENT'), JText::_('EB_ACTIVATE_RECURRING_EVENT_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('activate_recurring_event', $config->activate_recurring_event); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('use_https', JText::_('EB_ACTIVATE_HTTPS'), JText::_('EB_ACTIVATE_HTTPS_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('use_https', $config->use_https); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('hide_past_events', JText::_('EB_HIDE_PAST_EVENTS'), JText::_('EB_HIDE_PAST_EVENTS_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('hide_past_events', $config->hide_past_events); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('active_term', JText::_('EB_SHOW_TERM_AND_CONDITION'), JText::_('EB_SHOW_TERM_AND_CONDITION_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('active_term', $config->active_term); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('term_condition_by_event', JText::_('EB_TERM_AND_CONDITION_BY_EVENT'), JText::_('EB_TERM_AND_CONDITION_BY_EVENT_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo EventbookingHelperHtml::getBooleanInput('term_condition_by_event', $config->term_condition_by_event); ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('article_id', JText::_('EB_DEFAULT_TERM_AND_CONDITION')); ?>
					</div>
					<div class="controls">
						<?php echo $this->lists['article_id']; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('registration_type', JText::_('EB_DEFAULT_REGISTRATION_TYPE')); ?>
					</div>
					<div class="controls">
						<?php echo $this->lists['registration_type']; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('access', JText::_('EB_DEFAULT_ACCESS')); ?>
					</div>
					<div class="controls">
						<?php echo $this->lists['access']; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('registration_access', JText::_('EB_DEFAULT_REGISTRATION_ACCESS')); ?>
					</div>
					<div class="controls">
						<?php echo $this->lists['registration_access']; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('default_event_status', JText::_('EB_DEFAULT_EVENT_STATUS')); ?>
					</div>
					<div class="controls">
						<?php echo $this->lists['default_event_status']; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('default_event_status', JText::_('EB_ATTACHMENT_FILE_TYPES'), JText::_('EB_ATTACHMENT_FILE_TYPES_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<input type="text" name="attachment_file_types" class="inputbox" value="<?php echo strlen($this->config->attachment_file_types) ? $this->config->attachment_file_types : 'bmp|gif|jpg|png|swf|zip|doc|pdf|xls'; ?>" size="60" />
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('date_format', JText::_('EB_DATE_FORMAT'), JText::_('EB_DATE_FORMAT_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<input type="text" name="date_format" class="inputbox" value="<?php echo $this->config->date_format; ?>" size="20" />
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('event_date_format', JText::_('EB_EVENT_DATE_FORMAT'), JText::_('EB_EVENT_DATE_FORMAT_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<input type="text" name="event_date_format" class="inputbox" value="<?php echo $this->config->event_date_format; ?>" size="40" />
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('event_time_format', JText::_('EB_TIME_FORMAT'), JText::_('EB_TIME_FORMAT_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<input type="text" name="event_time_format" class="inputbox" value="<?php echo $this->config->event_time_format ? $this->config->event_time_format : '%I%P'; ?>" size="40" />
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('date_field_format', JText::_('EB_DATE_FIELD_FORMAT'), JText::_('EB_DATE_FIELD_FORMAT_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo $this->lists['date_field_format']; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('currency_symbol', JText::_('EB_CURRENCY_SYMBOL')); ?>
					</div>
					<div class="controls">
						<input type="text" name="currency_symbol" class="inputbox" value="<?php echo $this->config->currency_symbol; ?>" size="10" />
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('decimals', JText::_('EB_DECIMALS'), JText::_('EB_DECIMALS_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<input type="text" name="decimals" class="inputbox" value="<?php echo $this->config->get('decimals', 2); ?>" size="10" />
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('thousands_sep', JText::_('EB_THOUNSANDS_SEP'), JText::_('EB_THOUNSANDS_SEP_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<input type="text" name="thousands_sep" class="inputbox" value="<?php echo $this->config->get('thousands_sep', ','); ?>" size="10" />
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('currency_position', JText::_('EB_CURRENCY_POSITION')); ?>
					</div>
					<div class="controls">
						<?php echo $this->lists['currency_position']; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('default_country', JText::_('EB_DEFAULT_COUNTRY')); ?>
					</div>
					<div class="controls">
						<?php echo $this->lists['country_list']; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('csv_delimiter', JText::_('EB_CSV_DELIMITTER')); ?>
					</div>
					<div class="controls">
						<?php echo $this->lists['csv_delimiter']; ?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('conversion_tracking_code', JText::_('EB_CONVERSION_TRACKING_CODE'), JText::_('EB_CONVERSION_TRACKING_CODE_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<textarea name="conversion_tracking_code" class="input-xlarge" rows="10"><?php echo $this->config->conversion_tracking_code;?></textarea>
					</div>
				</div>
			</div>			
			<div class="tab-pane" id="theme-page">
				<table class="admintable" width="100%">
					<tr>
						<td class="key" style="width:18%;">
							<?php echo JText::_('EB_CALENDAR_THEME'); ?>
						</td>
						<td width="30%">
							<?php echo $this->lists['calendar_theme']; ?>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
					<tr>
						<td class="key" style="width:18%;">
							<?php echo JText::_('EB_SHOW_CALENDAR_LEGEND'); ?>
						</td>
						<td width="30%">
							<?php echo $this->lists['show_calendar_legend']; ?>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
					<tr>
						<td class="key" style="width:18%;">
							<?php echo JText::_('EB_FRONTEND_SUBMIT_EVENT_FORM_LAYOUT'); ?>
						</td>
						<td width="30%">
							<?php echo $this->lists['submit_event_form_layout']; ?>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>						
					<tr>
						<td class="key">
							<?php echo JText::_('EB_SHOW_MULTIPLE_DAYS_EVENT_IN_CALENDAR'); ?>
						</td>
						<td>
							<?php echo $this->lists['show_multiple_days_event_in_calendar']; ?>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_('EB_SHOW_EVENT_TIME'); ?>
						</td>
						<td>
							<?php echo $this->lists['show_event_time']; ?>
						</td>
						<td>
							<?php echo JText::_('EB_SHOW_EVENT_TIME_EXPLAIN'); ?>
						</td>
					</tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_EMPTY_CATEGORIES'); ?>
						</td>
						<td>
							<?php echo $this->lists['show_empty_cat']; ?>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>				
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_NUMBER_EVENTS'); ?>
						</td>
						<td>
							<?php echo $this->lists['show_number_events']; ?>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_CATEGORIES_PER_PAGE'); ?>
						</td>
						<td>
							<input type="text" name="number_categories" class="inputbox" value="<?php echo $this->config->number_categories; ?>" size="10" />
						</td>
						<td>
							&nbsp;
						</td>
					</tr>													
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_EVENTS_PER_PAGE'); ?>
						</td>
						<td>
							<input type="text" name="number_events" class="inputbox" value="<?php echo $this->config->number_events; ?>" size="10" />
						</td>
						<td>
							&nbsp;
						</td>
					</tr>		
					<tr>
						<td class="key">
							<?php echo JText::_('EB_EVENT_ORDER_BY'); ?>
						</td>
						<td>
							<?php echo $this->lists['order_events'] ; ?>
						</td>
					</tr>
                    <tr>
                        <td class="key">
                            <?php echo JText::_('EB_ORDER_DIRECTION'); ?>
                        </td>
                        <td>
                            <?php echo $this->lists['order_direction'] ; ?>
                        </td>
                    </tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_EVENT_CAPACITY'); ?>
						</td>
						<td>
							<?php echo $this->lists['show_capacity']; ?>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>				
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_NUMBER_REGISTERED_USERS'); ?>
						</td>
						<td>
							<?php echo $this->lists['show_registered']; ?>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_AVAILABLE_PLACES'); ?>
						</td>
						<td>
							<?php echo $this->lists['show_available_place']; ?>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_LIST_OF_REGISTRANTS'); ?>
						</td>
						<td>
							<?php echo $this->lists['show_list_of_registrants']; ?>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_LOCATION_IN_CATEGORY_VIEW'); ?>
						</td>
						<td>
							<?php echo $this->lists['show_location_in_category_view']; ?>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>			
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_LOCATION_IN_EMAIL'); ?>
						</td>
						<td>
							<?php echo $this->lists['show_event_location_in_email']; ?>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_EVENT_CUSTOM_FIELDS_IN_CATEGORY_VIEW'); ?>
						</td>
						<td>
							<?php echo $this->lists['show_event_custom_field_in_category_layout']; ?>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_HIDE_DETAIL_BUTTON'); ?>
						</td>
						<td>
							<?php echo $this->lists['hide_detail_button']; ?>
						</td>
						<td>
							<?php echo JText::_('EB_HIDE_DETAIL_BUTTON_EXPLAIN'); ?>
						</td>
					</tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_PROCESS_CONTENT_PLUGIN'); ?>
						</td>
						<td>
							<?php echo $this->lists['process_plugin']; ?>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>						
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_CATEGORY_DESCRIPTION_IN_CALENDAR_LAYOUT'); ?>
						</td>
						<td>
							<?php echo $this->lists['show_cat_decription_in_calendar_layout']; ?>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_CATEGORY_DESCRIPTION_IN_TABLE_LAYOUT'); ?>
						</td>
						<td>
							<?php echo $this->lists['show_cat_decription_in_table_layout']; ?>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>

					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_EVENT_IMAGE_IN_CALENDAR'); ?>
						</td>
						<td>
							<?php echo $this->lists['show_thumb_in_calendar']; ?>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>

					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_EVENT_IMAGE_IN_TABLE_LAYOUT'); ?>
						</td>
						<td>
							<?php echo $this->lists['show_image_in_table_layout']; ?>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_EVENT_END_DATE_IN_TABLE_LAYOUT'); ?>
						</td>
						<td>
							<?php echo $this->lists['show_event_end_date_in_table_layout']; ?>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_PRICE_IN_TABLE_LAYOUT'); ?>
						</td>
						<td>
							<?php echo $this->lists['show_price_in_table_layout']; ?>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_DISPLAY_MESSAGE_FOR_FULL_EVENT'); ?>														
						</td>
						<td>
							<?php echo $this->lists['display_message_for_full_event']; ?>
						</td>
						<td>
							<?php echo JText::_('EB_DISPLAY_MESSAGE_FOR_FULL_EVENT_EXPLAIN'); ?>
						</td>
					</tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_PRICE_FOR_FREE_EVENT'); ?>														
						</td>
						<td>
							<?php echo $this->lists['show_price_for_free_event']; ?>
						</td>
						<td>
							<?php echo JText::_('EB_SHOW_PRICE_FOR_FREE_EVENT_EXPLAIN'); ?>
						</td>
					</tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_DISCOUNTED_PRICE'); ?>														
						</td>
						<td>
							<?php echo $this->lists['show_discounted_price']; ?>
						</td>
						<td>
							<?php echo JText::_('EB_SHOW_DISCOUNTED_PRICE_EXPLAIN'); ?>
						</td>
					</tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_EVENT_DATE'); ?>					
						</td>
						<td>
							<?php echo $this->lists['show_event_date']; ?>
						</td>
						<td>
							<?php echo JText::_('EB_SHOW_EVENT_DATE_EXPLAIN'); ?>
						</td>
					</tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_FACEBOOK_LIKE_BUTTON'); ?>					
						</td>
						<td>
							<?php echo $this->lists['show_fb_like_button']; ?>
						</td>
						<td>
							<?php echo JText::_('EB_SHOW_FACEBOOKING_LIKE_BUTTON_EXPLAIN'); ?>
						</td>
					</tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_SAVE_TO_PERSONAL_CALENDAR'); ?>
						</td>
						<td>
							<?php echo $this->lists['show_save_to_personal_calendar']; ?>
						</td>
						<td>
							<?php echo JText::_('EB_SHOW_SAVE_TO_PERSONAL_CALENDAR_EXPLAIN'); ?>
						</td>
					</tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_SOCIAL_BOOKMARK'); ?>					
						</td>
						<td>
							<?php echo $this->lists['show_social_bookmark']; ?>
						</td>
						<td>
							<?php echo JText::_('EB_SHOW_SOCIAL_BOOKMARK_EXPLAIN'); ?>
						</td>
					</tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_INVITE_FRIEND'); ?>
						</td>
						<td>
							<?php echo $this->lists['show_invite_friend'] ; ?>
						</td>
						<td>
							<?php echo JText::_('EB_SHOW_INVITE_FRIEND_EXPLAIN') ; ?>
						</td>
					</tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_ATTACHMENT'); ?>
						</td>
						<td>
							<?php echo $this->lists['show_attachment_in_frontend'] ; ?>
						</td>
						<td>
							<?php echo JText::_('EB_SHOW_ATTACHMENT_EXPLAIN') ; ?>
						</td>
					</tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_ACTIVATE_WEEKLY_CALENDAR_VIEW'); ?>
						</td>
						<td>
							<?php echo $this->lists['activate_weekly_calendar_view'] ; ?>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>					
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_ACTIVATE_DAILY_CALENDAR_VIEW'); ?>
						</td>
						<td>
							<?php echo $this->lists['activate_daily_calendar_view'] ; ?>
						</td>
						<td>
							&nbsp;
						</td>
					</tr>
					<tr>
						<td width="30%" class="key">
							<?php echo JText::_('EB_SHOW_COUPON_CODE'); ?>
						</td>
						<td>
							<?php echo $this->lists['show_coupon_code_in_registrant_list'] ; ?>
						</td>
						<td>
							<?php echo JText::_('EB_SHOW_COUPON_CODE_EXPLAIN'); ?>
						</td>
					</tr>						
				</table>
			</div>
			<div class="tab-pane" id="sef-setting-page">
				<table class="admintable">
	    			<tr>
	    				<td colspan="3">
	    					<p class="message"><strong><?php echo JText::_('EB_SEF_SETTING_EXPLAIN'); ?></strong></p>
	    				</td>
	    			</tr>
	    			<tr>
	    				<td width="30%" class="key">
	    					<?php echo JText::_('EB_INSERT_EVENT_ID'); ?>
	    				</td>
	    				<td>
	    					<?php
	    					    echo $this->lists['insert_event_id'] ;
	    					?>					
	    				</td>
	    				<td>
	    					<?php echo JText::_('EB_INSERT_EVENT_ID_EXPLAIN'); ?>
	    				</td>
	    			</tr>
	    			<tr>
	    				<td width="30%" class="key">
	    					<?php echo JText::_('EB_INSERT_CATEGORY'); ?>
	    				</td>
	    				<td>
	    					<?php
	    					    echo $this->lists['insert_category'] ;
	    					?>					
	    				</td>
	    				<td>
	    					<?php echo JText::_('EB_INSERT_CATEGORY_EXPLAIN'); ?>
	    				</td>
	    			</tr>
	    		</table>
			</div>
			<div class="tab-pane" id="invoice-page">
				<table class="admintable adminform" style="width:100%;">
					<tr>
						<td  class="key" width="10%">
							<?php echo JText::_('EB_ACTIVATE_INVOICE_FEATURE'); ?>
						</td>
						<td width="60%">
							<?php echo $this->lists['activate_invoice_feature']; ?>
						</td>
						<td>
							<?php echo JText::_('EB_ACTIVATE_INVOICE_FEATURE_EXPLAIN'); ?>
						</td>
					</tr>
					<tr>
						<td  class="key" width="10%">
							<?php echo JText::_('EB_SEND_INVOICE_TO_SUBSCRIBERS'); ?>
						</td>
						<td width="60%">
							<?php echo $this->lists['send_invoice_to_customer']; ?>
						</td>
						<td>
							<?php echo JText::_('EB_SEND_INVOICE_TO_SUBSCRIBERS_EXPLAIN'); ?>
						</td>
					</tr>
					<tr>
						<td  class="key" width="10%">
							<?php echo JText::_('EB_SEND_INVOICE_TO_ADMIN'); ?>
						</td>
						<td width="60%">
							<?php echo $this->lists['send_invoice_to_admin']; ?>
						</td>
						<td>
							<?php echo JText::_('EB_SEND_INVOICE_TO_ADMIN_EXPLAIN'); ?>
						</td>
					</tr>
					<tr>
						<td  class="key">
							<?php echo JText::_('EB_INVOICE_START_NUMBER'); ?>
						</td>
						<td>
							<input type="text" name="invoice_start_number" class="inputbox" value="<?php echo $this->config->invoice_start_number ? $this->config->invoice_start_number : 1; ?>" size="10" />
						</td>
						<td>
							<?php echo JText::_('EB_INVOICE_START_NUMBER_EXPLAIN'); ?>
						</td>
					</tr>
					<tr>
						<td  class="key" style="width:25%">
							<?php echo JText::_('EB_INVOICE_PREFIX'); ?>
						</td>
						<td>
							<input type="text" name="invoice_prefix" class="inputbox" value="<?php echo $this->config->get('invoice_prefix', 'IV'); ?>" size="10" />
						</td>
						<td>
							<?php echo JText::_('EB_INVOICE_PREFIX_EXPLAIN'); ?>
						</td>
					</tr>
					<tr>
						<td  class="key" style="width:25%">
							<?php echo JText::_('EB_INVOICE_NUMBER_LENGTH'); ?>
						</td>
						<td>
							<input type="text" name="invoice_number_length" class="inputbox" value="<?php echo $this->config->get('invoice_number_length', 5); ?>" size="10" />
						</td>
						<td>
							<?php echo JText::_('EB_INVOICE_NUMBER_LENGTH_EXPLAIN'); ?>
						</td>
					</tr>																						
					<tr>
						<td class="key">
							<?php echo JText::_('EB_INVOICE_FORMAT'); ?>
						</td>
						<td>
							<?php echo $editor->display( 'invoice_format',  $this->config->invoice_format , '100%', '550', '75', '8' ) ;?>					
						</td>
						<td>
							<?php echo JText::_('EB_INVOICE_FORMAT_EXPLAIN'); ?>
						</td>				
					</tr>
					<tr>
						<td class="key">
							<?php echo JText::_('EB_INVOICE_FORMAT_CART'); ?>
						</td>
						<td>
							<?php echo $editor->display( 'invoice_format_cart',  $this->config->invoice_format_cart , '100%', '550', '75', '8' ) ;?>					
						</td>
						<td>
							<?php echo JText::_('EB_INVOICE_FORMAT_CART_EXPLAIN'); ?>
						</td>				
					</tr>
				</table>	
			</div>		
		</div>		
	</div>													
	<div class="clearfix"></div>		
	<input type="hidden" name="task" value="" />				
</form>