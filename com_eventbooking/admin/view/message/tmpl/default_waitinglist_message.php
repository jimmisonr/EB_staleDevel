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
		<?php echo EventbookingHelperHtml::getFieldLabel('waitinglist_form_message', JText::_('EB_WAITINGLIST_FORM_MESSAGE'), JText::_('EB_WAITINGLIST_FORM_MESSAGE_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'waitinglist_form_message',  $this->message->waitinglist_form_message , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('waitinglist_complete_message', JText::_('EB_WAITINGLIST_COMPLETE_MESSAGE'), JText::_('EB_WAITINGLIST_COMPLETE_MESSAGE_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE], [FIRST_NAME], [LAST_NAME]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'waitinglist_complete_message',  $this->message->waitinglist_complete_message , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('watinglist_confirmation_subject', JText::_('EB_WAITINGLIST_CONFIRMATION_SUBJECT'), JText::_('EB_WAITINGLIST_CONFIRMATION_SUBJECT_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="watinglist_confirmation_subject" class="input-xlarge" size="70" value="<?php echo $this->message->watinglist_confirmation_subject ; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('watinglist_confirmation_body', JText::_('EB_WAITINGLIST_CONFIRMATION_BODY'), JText::_('EB_WAITINGLIST_COMPLETE_MESSAGE_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE], [FIRST_NAME], [LAST_NAME]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'watinglist_confirmation_body',  $this->message->watinglist_confirmation_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('watinglist_notification_subject', JText::_('EB_WAITINGLIST_NOTIFICATION_SUBJECT'), JText::_('EB_WAITINGLIST_NOTIFICATION_SUBJECT_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="watinglist_notification_subject" class="input-xlarge" size="70" value="<?php echo $this->message->watinglist_notification_subject ; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('watinglist_notification_body', JText::_('EB_WAITINGLIST_NOTIFICATION_BODY'), JText::_('EB_WAITINGLIST_NOTIFICATION_BODY_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE], [FIRST_NAME], [LAST_NAME]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'watinglist_notification_body',  $this->message->watinglist_notification_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registrant_waitinglist_notification_subject', JText::_('EB_REGISTRANT_WAITINGLIST_NOTIFICATION_SUBJECT'), JText::_('EB_REGISTRANT_WAITINGLIST_NOTIFICATION_SUBJECT_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="registrant_waitinglist_notification_subject" class="input-xlarge" size="70" value="<?php echo $this->message->registrant_waitinglist_notification_subject ; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registrant_waitinglist_notification_body', JText::_('EB_REGISTRANT_WAITINGLIST_NOTIFICATION_BODY'), JText::_('EB_REGISTRANT_WAITINGLIST_NOTIFICATION_BODY_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong>[REGISTRANT_FIRST_NAME], [REGISTRANT_LAST_NAME],[EVENT_TITLE], [FIRST_NAME], [LAST_NAME], [EVENT_LINK]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'registrant_waitinglist_notification_body',  $this->message->registrant_waitinglist_notification_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('request_payment_email_subject', JText::_('EB_REQUEST_PAYMENT_EMAIL_SUBJECT'), JText::_('EB_REQUEST_PAYMENT_EMAIL_SUBJECT_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="request_payment_email_subject" class="input-xlarge" size="70" value="<?php echo $this->message->request_payment_email_subject ; ?>" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('request_payment_email_body', JText::_('EB_REQUEST_PAYMENT_EMAIL_BODY'), JText::_('EB_REQUEST_PAYMENT_EMAIL_BODY_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong>[FIRST_NAME], [LAST_NAME],[EVENT_TITLE] , [EVENT_DATE], [PAYMENT_LINK]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'request_payment_email_body',  $this->message->request_payment_email_body , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_payment_form_message', JText::_('EB_REGISTRATION_PAYMENT_FORM_MESSAGE'), JText::_('EB_REGISTRATION_PAYMENT_FORM_MESSAGE_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong>[REGISTRATION_ID],[EVENT_TITLE], [EVENT_DATE], [AMOUNT], [REGISTRATION_ID]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'registration_payment_form_message',  $this->message->registration_payment_form_message , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
