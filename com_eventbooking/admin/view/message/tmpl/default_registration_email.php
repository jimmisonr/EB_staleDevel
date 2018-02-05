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
		<?php echo EventbookingHelperHtml::getFieldLabel('admin_email_subject', JText::_('EB_ADMIN_EMAIL_SUBJECT')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE]</strong>
		</p>
	</div>
	<div class="controls">
		<input type="text" name="admin_email_subject" class="input-xlarge" value="<?php echo $this->message->admin_email_subject; ?>" size="80" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('admin_email_body', JText::_('EB_ADMIN_EMAIL_BODY')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong><?php echo $fields; ?></strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'admin_email_body',  $this->message->admin_email_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('user_email_subject', JText::_('EB_USER_EMAIL_SUBJECT')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE]</strong>
		</p>
	</div>
	<div class="controls">
		<input type="text" name="user_email_subject" class="input-xlarge" value="<?php echo $this->message->user_email_subject; ?>" size="50" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('user_email_body', JText::_('EB_USER_EMAIL_BODY')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong><?php echo $fields; ?></strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'user_email_body',  $this->message->user_email_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('user_email_body_offline', JText::_('EB_USER_EMAIL_BODY_OFFLINE')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong><?php echo $fields; ?></strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'user_email_body_offline',  $this->message->user_email_body_offline , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('group_member_email_subject', JText::_('EB_GROUP_MEMBER_EMAIL_SUBJECT')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE]</strong>
		</p>
	</div>
	<div class="controls">
		<input type="text" name="group_member_email_subject" class="input-xlarge" value="<?php echo $this->message->group_member_email_subject; ?>" size="50" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('group_member_email_body', JText::_('EB_GROUP_MEMBER_EMAIL_BODY')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong>[MEMBER_DETAIL], <?php echo EventbookingHelperHtml::getAvailableMessagesTags(false); ?></strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'group_member_email_body',  $this->message->group_member_email_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('reminder_email_subject', JText::_('EB_REMINDER_EMAIL_SUBJECT')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: [EVENT_TITLE]
		</p>
	</div>
	<div class="controls">
		<input type="text" name="reminder_email_subject" class="input-xlarge" value="<?php echo $this->message->reminder_email_subject; ?>" size="50" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('reminder_email_body', JText::_('EB_REMINDER_EMAIL_BODY')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong>[REGISTRATION_DETAIL], [EVENT_DATE], [FIRST_NAME], [LAST_NAME], [EVENT_TITLE]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'reminder_email_body',  $this->message->reminder_email_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_cancel_email_subject', JText::_('EB_CANCEL_NOTIFICATION_EMAIL_SUBJECT')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE]</strong>
		</p>
	</div>
	<div class="controls">
		<input type="text" name="registration_cancel_email_subject" class="input-xlarge" value="<?php echo $this->message->registration_cancel_email_subject; ?>" size="50" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_cancel_email_body', JText::_('EB_CANCEL_NOTIFICATION_EMAIL_BODY')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong>[REGISTRATION_DETAIL], <?php echo $fields; ?></strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'registration_cancel_email_body',  $this->message->registration_cancel_email_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_approved_email_subject', JText::_('EB_REGISTRATION_APPROVED_EMAIL_SUBJECT')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE]</strong>
		</p>
	</div>
	<div class="controls">
		<input type="text" name="registration_approved_email_subject" class="input-xlarge" value="<?php echo $this->message->registration_approved_email_subject; ?>" size="50" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_approved_email_body', JText::_('EB_REGISTRATION_APPROVED_EMAIL_BODY')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong><?php echo $fields; ?></strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'registration_approved_email_body',  $this->message->registration_approved_email_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
