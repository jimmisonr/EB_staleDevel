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
?>
<div class="control-group">
	<label class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('payment_methods', JText::_('EB_PAYMENT_METHODS'), JText::_('EB_PAYMENT_METHODS_EXPLAIN')); ?>
	</label>
	<div class="controls">
		<?php echo $this->lists['payment_methods'] ?>
	</div>
</div>
<div class="control-group">
	<label class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('fixed_group_price', JText::_('EB_FIXED_GROUP_PRICE'), JText::_('EB_FIXED_GROUP_PRICE_EXPLAIN')); ?>
	</label>
	<div class="controls">
		<input type="text" name="fixed_group_price" id="fixed_group_price" class="inputbox" size="10" value="<?php echo $this->item->fixed_group_price; ?>" />
	</div>
</div>
<div class="control-group">
	<label class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('currency_code', JText::_('EB_CURRENCY'), JText::_('EB_CURRENCY_CODE_EXPLAIN')); ?>
	</label>
	<div class="controls">
		<?php echo $this->lists['currency_code'] ?>
	</div>
</div>
<div class="control-group">
	<label class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('currency_symbol', JText::_('EB_CURRENCY_SYMBOL'), JText::_('EB_CURRENCY_SYMBOL_EXPLAIN')); ?>
	</label>
	<div class="controls">
		<input type="text" name="currency_symbol" size="5" class="inputbox" value="<?php echo $this->item->currency_symbol; ?>" />
	</div>
</div>
<div class="control-group">
	<label class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('paypal_email', JText::_('EB_PAYPAL_EMAIL'), JText::_('EB_PAYPAL_EMAIL_EXPLAIN')); ?>
	</label>
	<div class="controls">
		<input type="text" name="paypal_email" class="inputbox" size="50" value="<?php echo $this->item->paypal_email ; ?>" />
	</div>
</div>
<div class="control-group">
	<label class="control-label"><?php echo JText::_('EB_API_LOGIN') ; ?></label>
	<div class="controls">
		<input type="text" name="api_login" value="<?php echo $this->item->api_login; ?>" class="inputbox" size="30" />
	</div>
</div>
<div class="control-group">
	<label class="control-label"><?php echo JText::_('EB_TRANSACTION_KEY') ; ?></label>
	<div class="controls">
		<input type="text" name="transaction_key" value="<?php echo $this->item->transaction_key; ?>" class="inputbox" size="30" />
	</div>
</div>
<div class="control-group">
	<label class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('custom_field_ids', JText::_('EB_CUSTOM_FIELD_IDS'), JText::_('EB_CUSTOM_FIELD_IDS_EXPLAIN')); ?>
	</label>
	<div class="controls">
		<input type="text" name="custom_field_ids" class="inputbox" size="70" value="<?php echo $this->item->custom_field_ids ; ?>" />
	</div>
</div>
<div class="control-group">
	<label class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('notification_emails', JText::_('EB_NOTIFICATION_EMAILS'), JText::_('EB_NOTIFICATION_EMAIL_EXPLAIN')); ?>
	</label>
	<div class="controls">
		<input type="text" name="notification_emails" class="inputbox" size="70" value="<?php echo $this->item->notification_emails ; ?>" />
	</div>
</div>
<div class="control-group">
	<label class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_form_message', JText::_('EB_REGISTRATION_FORM_MESSAGE'), JText::_('EB_AVAILABLE_TAGS').': [EVENT_TITLE]'); ?>
	</label>
	<div class="controls">
		<?php echo $editor->display( 'registration_form_message',  $this->item->registration_form_message , '100%', '250', '90', '10' ) ; ?>
	</div>
</div>
<div class="control-group">
	<label class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_form_message_group', JText::_('EB_REGISTRATION_FORM_MESSAGE_GROUP'), JText::_('EB_AVAILABLE_TAGS').': [EVENT_TITLE]'); ?>
	</label>
	<div class="controls">
		<?php echo $editor->display( 'registration_form_message_group',  $this->item->registration_form_message_group , '100%', '250', '90', '10' ) ; ?>
	</div>
</div>
<div class="control-group">
	<label class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('user_email_body', JText::_('EB_USER_EMAIL_BODY'), JText::_('EB_AVAILABLE_TAGS').': [REGISTRATION_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]'); ?>
	</label>
	<div class="controls">
		<?php echo $editor->display( 'user_email_body',  $this->item->user_email_body , '100%', '250', '90', '10' ) ; ?>
	</div>
</div>
<div class="control-group">
	<label class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('user_email_body_offline', JText::_('EB_USER_EMAIL_BODY_OFFLINE'), JText::_('EB_AVAILABLE_TAGS').': [REGISTRATION_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]'); ?>
	</label>
	<div class="controls">
		<?php echo $editor->display( 'user_email_body_offline',  $this->item->user_email_body_offline , '100%', '250', '90', '10' ) ; ?>
	</div>
</div>
<div class="control-group">
	<label class="control-label">
		<?php echo  JText::_('EB_THANKYOU_MESSAGE'); ?>
	</label>
	<div class="controls">
		<?php echo $editor->display( 'thanks_message',  $this->item->thanks_message , '100%', '180', '90', '6' ) ; ?>
	</div>
</div>
<div class="control-group">
	<label class="control-label">
		<?php echo  JText::_('EB_THANKYOU_MESSAGE_OFFLINE'); ?>
	</label>
	<div class="controls">
		<?php echo $editor->display( 'thanks_message_offline',  $this->item->thanks_message_offline , '100%', '180', '90', '6' ) ; ?>
	</div>
</div>
<div class="control-group">
	<label class="control-label">
		<?php echo  JText::_('EB_REGISTRATION_APPROVED_EMAIL_BODY'); ?>
	</label>
	<div class="controls">
		<?php echo $editor->display( 'registration_approved_email_body',  $this->item->registration_approved_email_body , '100%', '180', '90', '6' ) ; ?>
	</div>
</div>
