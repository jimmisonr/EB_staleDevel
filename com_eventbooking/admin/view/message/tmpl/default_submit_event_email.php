<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2018 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
?>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('submit_event_user_email_subject', JText::_('EB_SUBMIT_EVENT_USER_EMAIL_SUBJECT')); ?>
	</div>
	<div class="controls">
		<input type="text" name="submit_event_user_email_subject" class="input-xlarge" value="<?php echo $this->message->submit_event_user_email_subject; ?>" size="80" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('submit_event_user_email_body', JText::_('EB_SUBMIT_EVENT_USER_EMAIL_BODY')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong>[NAME], [USERNAME], [EVENT_TITLE], [EVENT_DATE], [EVENT_ID], [EVENT_LINK]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'submit_event_user_email_body',  $this->message->submit_event_user_email_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('submit_event_admin_email_subject', JText::_('EB_SUBMIT_EVENT_ADMIN_EMAIL_SUBJECT')); ?>
	</div>
	<div class="controls">
		<input type="text" name="submit_event_admin_email_subject" class="input-xlarge" value="<?php echo $this->message->submit_event_admin_email_subject; ?>" size="50" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('submit_event_admin_email_body', JText::_('EB_SUBMIT_EVENT_ADMIN_EMAIL_BODY')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong>[NAME], [USERNAME], [EVENT_TITLE], [EVENT_DATE], [EVENT_ID], [EVENT_LINK]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'submit_event_admin_email_body',  $this->message->submit_event_admin_email_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
