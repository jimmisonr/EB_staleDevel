<?php
/**
 * @version            2.4.1
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die ;
JHtml::_('bootstrap.tooltip');
$document = JFactory::getDocument();
$document->addStyleDeclaration(".hasTip{display:block !important}");
$translatable = JLanguageMultilang::isEnabled() && count($this->languages);
$editor = JEditor::getInstance(JFactory::getConfig()->get('editor'));
$config = $this->config;
JHtml::_('formbehavior.chosen', 'select');
?>
<div class="row-fluid">
<form action="index.php?option=com_eventbooking&view=configuration" method="post" name="adminForm" id="adminForm" class="form-horizontal eb-configuration">
	<?php echo JHtml::_('bootstrap.startTabSet', 'configuration', array('active' => 'general-page')); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'configuration', 'general-page', JText::_('EB_GENERAL', true)); ?>
			<div class="span6">
				<fieldset class="form-horizontal">
					<legend><?php echo JText::_('EB_GENERAL_SETTINGS'); ?></legend>
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
							<?php echo EventbookingHelperHtml::getFieldLabel('activate_recurring_event', JText::_('EB_ACTIVATE_RECURRING_EVENT'), JText::_('EB_ACTIVATE_RECURRING_EVENT_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<?php echo EventbookingHelperHtml::getBooleanInput('activate_recurring_event', $config->activate_recurring_event); ?>
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
							<?php echo EventbookingHelperHtml::getFieldLabel('hide_past_events_from_events_dropdown', JText::_('EB_HIDE_PAST_EVENTS_FROM_DROPDOWN'), JText::_('EB_HIDE_PAST_EVENTS_FROM_DROPDOWN_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<?php echo EventbookingHelperHtml::getBooleanInput('hide_past_events_from_events_dropdown', $config->hide_past_events_from_events_dropdown); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo EventbookingHelperHtml::getFieldLabel('date_format', JText::_('EB_DATE_FORMAT'), JText::_('EB_DATE_FORMAT_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<input type="text" name="date_format" class="inputbox" value="<?php echo $config->date_format; ?>" size="20" />
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo EventbookingHelperHtml::getFieldLabel('event_date_format', JText::_('EB_EVENT_DATE_FORMAT'), JText::_('EB_EVENT_DATE_FORMAT_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<input type="text" name="event_date_format" class="inputbox" value="<?php echo $config->event_date_format; ?>" size="40" />
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo EventbookingHelperHtml::getFieldLabel('event_time_format', JText::_('EB_TIME_FORMAT'), JText::_('EB_TIME_FORMAT_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<input type="text" name="event_time_format" class="inputbox" value="<?php echo $config->event_time_format ? $config->event_time_format : '%I%P'; ?>" size="40" />
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
							<?php echo EventbookingHelperHtml::getFieldLabel('currency_code', JText::_('EB_CURRENCY_CODE')); ?>
						</div>
						<div class="controls">
							<?php echo $this->lists['currency_code']; ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo EventbookingHelperHtml::getFieldLabel('currency_symbol', JText::_('EB_CURRENCY_SYMBOL')); ?>
						</div>
						<div class="controls">
							<input type="text" name="currency_symbol" class="inputbox" value="<?php echo $config->currency_symbol; ?>" size="10" />
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo EventbookingHelperHtml::getFieldLabel('decimals', JText::_('EB_DECIMALS'), JText::_('EB_DECIMALS_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<input type="text" name="decimals" class="inputbox" value="<?php echo $config->get('decimals', 2); ?>" size="10" />
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo EventbookingHelperHtml::getFieldLabel('dec_point', JText::_('EB_DECIMAL_POINT'), JText::_('EB_DECIMAL_POINT_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<input type="text" name="dec_point" class="inputbox" value="<?php echo $this->config->get('dec_point', '.');?>" size="10" />
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo EventbookingHelperHtml::getFieldLabel('thousands_sep', JText::_('EB_THOUNSANDS_SEP'), JText::_('EB_THOUNSANDS_SEP_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<input type="text" name="thousands_sep" class="inputbox" value="<?php echo $config->get('thousands_sep', ','); ?>" size="10" />
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
							<input type="text" name="registrant_list_custom_field_ids" value="<?php echo $config->registrant_list_custom_field_ids ; ?>" />
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
							<?php echo EventbookingHelperHtml::getFieldLabel('default_country', JText::_('EB_DEFAULT_COUNTRY')); ?>
						</div>
						<div class="controls">
							<?php echo $this->lists['country_list']; ?>
						</div>
					</div>
				</fieldset>
				<fieldset class="form-horizontal" style="margin-top:103px;">
					<legend><?php echo JText::_('EB_MAIL_SETTINGS'); ?></legend>
					<div class="control-group">
						<div class="control-label">
							<?php echo EventbookingHelperHtml::getFieldLabel('send_emails', JText::_('EB_SEND_NOTIFICATION_EMAILS')); ?>
						</div>
						<div class="controls">
							<?php echo $this->lists['send_emails']; ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo EventbookingHelperHtml::getFieldLabel('from_name', JText::_('EB_FROM_NAME'), JText::_('EB_FROM_NAME_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<input type="text" name="from_name" class="inputbox" value="<?php echo $config->from_name; ?>" size="50" />
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo EventbookingHelperHtml::getFieldLabel('from_email', JText::_('EB_FROM_EMAIL'), JText::_('EB_FROM_EMAIL_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<input type="text" name="from_email" class="inputbox" value="<?php echo $config->from_email; ?>" size="50" />
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo EventbookingHelperHtml::getFieldLabel('notification_emails', JText::_('EB_NOTIFICATION_EMAILS'), JText::_('EB_NOTIFICATION_EMAILS_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<input type="text" name="notification_emails" class="inputbox" value="<?php echo $config->notification_emails; ?>" size="50" />
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
							<?php echo EventbookingHelperHtml::getFieldLabel('send_attachments_to_admin', JText::_('EB_SEND_ATTACHMENTS_TO_ADMIN'), JText::_('EB_SEND_ATTACHMENTS_TO_ADMIN_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<?php echo EventbookingHelperHtml::getBooleanInput('send_attachments_to_admin', $config->send_attachments_to_admin); ?>
						</div>
					</div>
				</fieldset>
			</div>
			<div class="span6">
				<fieldset class="form-horizontal">
					<legend><?php echo JText::_('EB_REGISTRATION_SETTINGS'); ?></legend>
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
							<?php echo EventbookingHelperHtml::getFieldLabel('multiple_booking', JText::_('EB_MULTIPLE_BOOKING'), JText::_('EB_MULTIPLE_BOOKING_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<?php echo EventbookingHelperHtml::getBooleanInput('multiple_booking', $config->multiple_booking); ?>
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
							<?php echo EventbookingHelperHtml::getFieldLabel('prevent_duplicate_registration', JText::_('EB_PREVENT_DUPLICATE'), JText::_('EB_PREVENT_DUPLICATE_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<?php echo EventbookingHelperHtml::getBooleanInput('prevent_duplicate_registration', $config->prevent_duplicate_registration); ?>
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
							<?php echo EventbookingHelperHtml::getFieldLabel('send_ics_file', JText::_('EB_SEND_ICS_FILE'), JText::_('EB_SEND_ICS_FILE_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<?php echo EventbookingHelperHtml::getBooleanInput('send_ics_file', $config->send_ics_file); ?>
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
							<?php echo EventbookingHelperHtml::getFieldLabel('show_billing_step_for_free_events', JText::_('EB_SHOW_BILLING_STEP_FOR_FREE_EVENTS'), JText::_('EB_SHOW_BILLING_STEP_FOR_FREE_EVENTS_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<?php echo EventbookingHelperHtml::getBooleanInput('show_billing_step_for_free_events', $config->show_billing_step_for_free_events); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo EventbookingHelperHtml::getFieldLabel('activate_checkin_registrants', JText::_('EB_ACTIVATE_CHECKIN_REGISTRANTS'), JText::_('EB_ACTIVATE_CHECKIN_REGISTRANTS_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<?php echo EventbookingHelperHtml::getBooleanInput('activate_checkin_registrants', $config->activate_checkin_registrants); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo EventbookingHelperHtml::getFieldLabel('accept_term', JText::_('EB_SHOW_TERM_AND_CONDITION'), JText::_('EB_SHOW_TERM_AND_CONDITION_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<?php echo EventbookingHelperHtml::getBooleanInput('accept_term', $config->accept_term); ?>
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
							<?php echo EventbookingHelper::getArticleInput($config->article_id); ?>
						</div>
					</div>
				</fieldset>
				<fieldset class="form-horizontal">
					<legend><?php echo JText::_('EB_OTHER_SETTINGS'); ?></legend>
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
							<?php echo EventbookingHelperHtml::getFieldLabel('attachment_file_types', JText::_('EB_ATTACHMENT_FILE_TYPES'), JText::_('EB_ATTACHMENT_FILE_TYPES_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<input type="text" name="attachment_file_types" class="inputbox" value="<?php echo strlen($config->attachment_file_types) ? $config->attachment_file_types : 'bmp|gif|jpg|png|swf|zip|doc|pdf|xls'; ?>" size="60" />
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo EventbookingHelperHtml::getFieldLabel('zoom_level', JText::_('EB_ZOOM_LEVEL'), JText::_('EB_ZOOM_LEVEL_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<?php echo JHtml::_('select.integerlist', 1, 14, 1, 'zoom_level', 'class="inputbox"', $config->zoom_level); ?>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo EventbookingHelperHtml::getFieldLabel('map_width', JText::_('EB_MAP_WIDTH'), JText::_('EB_MAP_WIDTH_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<input type="text" name="map_width" class="inputbox" value="<?php echo $config->map_width ; ?>" />
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo EventbookingHelperHtml::getFieldLabel('map_height', JText::_('EB_MAP_HEIGHT'), JText::_('EB_MAP_HEIGHT_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<input type="text" name="map_height" class="inputbox" value="<?php echo $config->map_height ; ?>" />
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo EventbookingHelperHtml::getFieldLabel('thumb_width', JText::_('EB_THUMB_WIDTH'), JText::_('EB_THUMB_WIDTH_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<input type="text" name="thumb_width" class="inputbox" value="<?php echo $config->thumb_width ; ?>" />
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo EventbookingHelperHtml::getFieldLabel('thumb_height', JText::_('EB_THUMB_HEIGHT'), JText::_('EB_THUMB_HEIGHT_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<input type="text" name="thumb_height" class="inputbox" value="<?php echo $config->thumb_height ; ?>" />
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
							<textarea name="conversion_tracking_code" class="input-xlarge" rows="10"><?php echo $config->conversion_tracking_code;?></textarea>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo EventbookingHelperHtml::getFieldLabel('debug', JText::_('EB_DEBUG'), JText::_('EB_DEBUG_EXPLAIN')); ?>
						</div>
						<div class="controls">
							<?php echo EventbookingHelperHtml::getBooleanInput('debug', $config->debug); ?>
						</div>
					</div>
				</fieldset>
			</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'configuration', 'theme-page', JText::_('EB_THEMES', true)); ?>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('calendar_theme', JText::_('EB_CALENDAR_THEME')); ?>
				</div>
				<div class="controls">
					<?php echo $this->lists['calendar_theme']; ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_calendar_legend', JText::_('EB_SHOW_CALENDAR_LEGEND')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_calendar_legend', $config->show_calendar_legend); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('submit_event_form_layout', JText::_('EB_FRONTEND_SUBMIT_EVENT_FORM_LAYOUT')); ?>
				</div>
				<div class="controls">
					<?php echo $this->lists['submit_event_form_layout']; ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_multiple_days_event_in_calendar', JText::_('EB_SHOW_MULTIPLE_DAYS_EVENT_IN_CALENDAR')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_multiple_days_event_in_calendar', $config->show_multiple_days_event_in_calendar); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_event_time', JText::_('EB_SHOW_EVENT_TIME'), JText::_('EB_SHOW_EVENT_TIME_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_event_time', $config->show_event_time); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_empty_cat', JText::_('EB_SHOW_EMPTY_CATEGORIES')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_empty_cat', $config->show_empty_cat); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_number_events', JText::_('EB_SHOW_NUMBER_EVENTS')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_number_events', $config->show_number_events); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('number_categories', JText::_('EB_CATEGORIES_PER_PAGE')); ?>
				</div>
				<div class="controls">
					<input type="text" name="number_categories" class="inputbox" value="<?php echo $config->number_categories; ?>" size="10" />
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('number_events', JText::_('EB_EVENTS_PER_PAGE')); ?>
				</div>
				<div class="controls">
					<input type="text" name="number_events" class="inputbox" value="<?php echo $config->number_events; ?>" size="10" />
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('order_events', JText::_('EB_EVENT_ORDER_BY')); ?>
				</div>
				<div class="controls">
					<?php echo $this->lists['order_events'] ; ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('order_direction', JText::_('EB_ORDER_DIRECTION')); ?>
				</div>
				<div class="controls">
					<?php echo $this->lists['order_direction'] ; ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_capacity', JText::_('EB_SHOW_EVENT_CAPACITY')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_capacity', $config->show_capacity); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_registered', JText::_('EB_SHOW_NUMBER_REGISTERED_USERS')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_registered', $config->show_registered); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_available_place', JText::_('EB_SHOW_AVAILABLE_PLACES')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_available_place', $config->show_available_place); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_list_of_registrants', JText::_('EB_SHOW_LIST_OF_REGISTRANTS')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_list_of_registrants', $config->show_list_of_registrants); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_location_in_category_view', JText::_('EB_SHOW_LOCATION_IN_CATEGORY_VIEW')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_location_in_category_view', $config->show_location_in_category_view); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_event_location_in_email', JText::_('EB_SHOW_LOCATION_IN_EMAIL')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_event_location_in_email', $config->show_event_location_in_email); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_event_custom_field_in_category_layout', JText::_('EB_SHOW_EVENT_CUSTOM_FIELDS_IN_CATEGORY_VIEW')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_event_custom_field_in_category_layout', $config->show_event_custom_field_in_category_layout); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('hide_detail_button', JText::_('EB_HIDE_DETAIL_BUTTON'), JText::_('EB_HIDE_DETAIL_BUTTON_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('hide_detail_button', $config->hide_detail_button); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('process_plugin', JText::_('EB_PROCESS_CONTENT_PLUGIN')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('process_plugin', $config->get('process_plugin', 1)); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_cat_decription_in_calendar_layout', JText::_('EB_SHOW_CATEGORY_DESCRIPTION_IN_CALENDAR_LAYOUT')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_cat_decription_in_calendar_layout', $config->show_cat_decription_in_calendar_layout); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_cat_decription_in_table_layout', JText::_('EB_SHOW_CATEGORY_DESCRIPTION_IN_TABLE_LAYOUT')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_cat_decription_in_table_layout', $config->show_cat_decription_in_table_layout); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_thumb_in_calendar', JText::_('EB_SHOW_EVENT_IMAGE_IN_CALENDAR')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_thumb_in_calendar', $config->show_thumb_in_calendar); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_image_in_table_layout', JText::_('EB_SHOW_EVENT_IMAGE_IN_TABLE_LAYOUT')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_image_in_table_layout', $config->show_image_in_table_layout); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_event_end_date_in_table_layout', JText::_('EB_SHOW_EVENT_END_DATE_IN_TABLE_LAYOUT')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_event_end_date_in_table_layout', $config->show_event_end_date_in_table_layout); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_price_in_table_layout', JText::_('EB_SHOW_PRICE_IN_TABLE_LAYOUT')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_price_in_table_layout', $config->show_price_in_table_layout); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('display_message_for_full_event', JText::_('EB_DISPLAY_MESSAGE_FOR_FULL_EVENT'), JText::_('EB_DISPLAY_MESSAGE_FOR_FULL_EVENT_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('display_message_for_full_event', $config->display_message_for_full_event); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_price_for_free_event', JText::_('EB_SHOW_PRICE_FOR_FREE_EVENT'), JText::_('EB_SHOW_PRICE_FOR_FREE_EVENT_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_price_for_free_event', $config->show_price_for_free_event); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_discounted_price', JText::_('EB_SHOW_DISCOUNTED_PRICE'), JText::_('EB_SHOW_DISCOUNTED_PRICE_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_discounted_price', $config->show_discounted_price); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_event_date', JText::_('EB_SHOW_EVENT_DATE'), JText::_('EB_SHOW_EVENT_DATE_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_event_date', $config->show_event_date); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_fb_like_button', JText::_('EB_SHOW_FACEBOOK_LIKE_BUTTON'), JText::_('EB_SHOW_FACEBOOKING_LIKE_BUTTON_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_fb_like_button', $config->show_fb_like_button); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_save_to_personal_calendar', JText::_('EB_SHOW_SAVE_TO_PERSONAL_CALENDAR'), JText::_('EB_SHOW_SAVE_TO_PERSONAL_CALENDAR_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_save_to_personal_calendar', $config->show_save_to_personal_calendar); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_social_bookmark', JText::_('EB_SHOW_SOCIAL_BOOKMARK'), JText::_('EB_SHOW_SOCIAL_BOOKMARK_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_social_bookmark', $config->show_social_bookmark); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_invite_friend', JText::_('EB_SHOW_INVITE_FRIEND'), JText::_('EB_SHOW_INVITE_FRIEND_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_invite_friend', $config->show_invite_friend); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_attachment_in_frontend', JText::_('EB_SHOW_ATTACHMENT'), JText::_('EB_SHOW_ATTACHMENT_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_attachment_in_frontend', $config->show_attachment_in_frontend); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('activate_weekly_calendar_view', JText::_('EB_ACTIVATE_WEEKLY_CALENDAR_VIEW')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('activate_weekly_calendar_view', $config->activate_weekly_calendar_view); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('activate_daily_calendar_view', JText::_('EB_ACTIVATE_DAILY_CALENDAR_VIEW')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('activate_daily_calendar_view', $config->activate_daily_calendar_view); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('show_coupon_code_in_registrant_list', JText::_('EB_SHOW_COUPON_CODE'), JText::_('EB_SHOW_COUPON_CODE_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('show_coupon_code_in_registrant_list', $config->show_coupon_code_in_registrant_list); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('social_sharing_buttons', JText::_('EB_SOCIAL_SHARING_BUTTONS'), JText::_('EB_SOCIAL_SHARING_BUTTONS_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo $this->lists['social_sharing_buttons']; ?>
				</div>
			</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'configuration', 'sef-setting-page', JText::_('EB_SEF_SETTING', true)); ?>
			<p class="message"><strong><?php echo JText::_('EB_SEF_SETTING_EXPLAIN'); ?></strong></p>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('insert_event_id', JText::_('EB_INSERT_EVENT_ID'), JText::_('EB_INSERT_EVENT_ID_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('insert_event_id', $config->insert_event_id); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('insert_category', JText::_('EB_INSERT_CATEGORY'), JText::_('EB_INSERT_CATEGORY_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo $this->lists['insert_category']; ?>
				</div>
			</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'configuration', 'invoice-page', JText::_('EB_INVOICE_SETTINGS', true)); ?>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('activate_invoice_feature', JText::_('EB_ACTIVATE_INVOICE_FEATURE'), JText::_('EB_ACTIVATE_INVOICE_FEATURE_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('activate_invoice_feature', $config->activate_invoice_feature); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('send_invoice_to_customer', JText::_('EB_SEND_INVOICE_TO_SUBSCRIBERS'), JText::_('EB_SEND_INVOICE_TO_SUBSCRIBERS_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('send_invoice_to_customer', $config->send_invoice_to_customer); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('send_invoice_to_admin', JText::_('EB_SEND_INVOICE_TO_ADMIN'), JText::_('EB_SEND_INVOICE_TO_ADMIN_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('send_invoice_to_admin', $config->send_invoice_to_admin); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('invoice_start_number', JText::_('EB_INVOICE_START_NUMBER'), JText::_('EB_INVOICE_START_NUMBER_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<input type="text" name="invoice_start_number" class="inputbox" value="<?php echo $config->invoice_start_number ? $config->invoice_start_number : 1; ?>" size="10" />
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('invoice_prefix', JText::_('EB_INVOICE_PREFIX'), JText::_('EB_INVOICE_PREFIX_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<input type="text" name="invoice_prefix" class="inputbox" value="<?php echo $config->get('invoice_prefix', 'IV'); ?>" size="10" />
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('invoice_number_length', JText::_('EB_INVOICE_NUMBER_LENGTH'), JText::_('EB_INVOICE_NUMBER_LENGTH_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<input type="text" name="invoice_number_length" class="inputbox" value="<?php echo $config->get('invoice_number_length', 5); ?>" size="10" />
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('invoice_format', JText::_('EB_INVOICE_FORMAT'), JText::_('EB_INVOICE_FORMAT_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo $editor->display( 'invoice_format',  $config->invoice_format , '100%', '550', '75', '8' ) ;?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo EventbookingHelperHtml::getFieldLabel('invoice_format_cart', JText::_('EB_INVOICE_FORMAT_CART'), JText::_('EB_INVOICE_FORMAT_CART_EXPLAIN')); ?>
				</div>
				<div class="controls">
					<?php echo $editor->display( 'invoice_format_cart',  $config->invoice_format_cart , '100%', '550', '75', '8' ) ;?>
				</div>
			</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php
	if ($translatable)
	{
		echo JHtml::_('bootstrap.addTab', 'configuration', 'invoice-translation', JText::_('EB_INVOICE_TRANSLATION', true));
		echo JHtml::_('bootstrap.startTabSet', 'invoice-translation', array('active' => 'invoice-translation-'.$this->languages[0]->sef));
		foreach ($this->languages as $language)
		{
			$sef = $language->sef;
			echo JHtml::_('bootstrap.addTab', 'invoice-translation', 'invoice-translation-' . $sef, $language->title . ' <img src="' . JUri::root() . 'media/com_eventbooking/flags/' . $sef . '.png" />');
			?>
			<table class="admintable adminform" style="width: 100%;">
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('invoice_format', JText::_('EB_INVOICE_FORMAT'), JText::_('EB_INVOICE_FORMAT_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo $editor->display('invoice_format_' . $sef, $config->{'invoice_format_' . $sef}, '100%', '550', '75', '8');?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('invoice_format_cart', JText::_('EB_INVOICE_FORMAT_CART'), JText::_('EB_INVOICE_FORMAT_CART_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo $editor->display('invoice_format_cart_' . $sef, $config->{'invoice_format_cart_' . $sef}, '100%', '550', '75', '8');?>
					</div>
				</div>
			</table>
			<?php
			echo JHtml::_('bootstrap.endTab');
		}
		echo JHtml::_('bootstrap.endTabSet');
		echo JHtml::_('bootstrap.endTab');
	}
	?>
	<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	<div class="clearfix"></div>
	<input type="hidden" name="task" value="" />
</form>
</div>