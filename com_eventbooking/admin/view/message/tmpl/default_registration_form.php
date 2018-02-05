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
		<?php echo EventbookingHelperHtml::getFieldLabel('intro_text', JText::_('EB_INTRO_TEXT'), JText::_('EB_INTRO_TEXT_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'intro_text',  $this->message->intro_text , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_form_message', JText::_('EB_REGISTRATION_FORM_MESSAGE'), JText::_('EB_REGISTRATION_FORM_MESSAGE_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'registration_form_message',  $this->message->registration_form_message , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_form_message_group', JText::_('EB_REGISTRATION_FORM_MESSAGE_GROUP'), JText::_('EB_REGISTRATION_FORM_MESSAGE_GROUP_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE]</strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'registration_form_message_group',  $this->message->registration_form_message_group , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('number_members_form_message', JText::_('EB_NUMBER_OF_MEMBERS_FORM_MESSAGE'), JText::_('EB_NUMBER_OF_MEMBERS_FORM_MESSAGE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'number_members_form_message',  $this->message->number_members_form_message , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('member_information_form_message', JText::_('EB_MEMBER_INFORMATION_FORM_MESSAGE'), JText::_('EB_MEMBER_INFORMATION_FORM_MESSAGE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'member_information_form_message',  $this->message->member_information_form_message , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('thanks_message', JText::_('EB_THANK_YOU_MESSAGE'), JText::_('EB_THANK_YOU_MESSAGE_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong><?php echo $fields; ?></strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'thanks_message',  $this->message->thanks_message , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('thanks_message_offline', JText::_('EB_THANK_YOU_MESSAGE_OFFLINE'), JText::_('EB_THANK_YOU_MESSAGE_OFFLINE_EXPLAIN')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong><?php echo $fields; ?></strong>
		</p>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'thanks_message_offline',  $this->message->thanks_message_offline , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<?php
if (count($this->extraOfflinePlugins))
{
	foreach ($this->extraOfflinePlugins as $offlinePaymentPlugin)
	{
		$name   = $offlinePaymentPlugin->name;
		$title  = $offlinePaymentPlugin->title;
		$prefix = str_replace('os_offline', '', $name);
		?>
		<div class="control-group">
			<div class="control-label">
				<?php echo JText::_('User email body (' . $title . ')'); ?>
				<p class="eb-available-tags">
					<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong><?php echo $fields; ?></strong>
				</p>
			</div>
			<div class="controls">
				<?php echo $editor->display('user_email_body_offline' . $prefix, $this->message->{'user_email_body_offline' . $prefix}, '100%', '250', '75', '8'); ?>
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<?php echo JText::_('Thank you message (' . $title . ')'); ?>
				<p>
					<strong>This message will be displayed on the thank you page after users complete an offline
						payment</strong>
				</p>
			</div>
			<div class="controls">
				<?php echo $editor->display('thanks_message_offline' . $prefix, $this->message->{'thanks_message_offline' . $prefix}, '100%', '250', '75', '8'); ?>
			</div>
		</div>
		<?php
	}
}
?>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('cancel_message', JText::_('EB_CANCEL_MESSAGE'), JText::_('EB_CANCEL_MESSAGE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'cancel_message',  $this->message->cancel_message , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_cancel_message_free', JText::_('EB_REGISTRATION_CANCEL_MESSAGE_FREE'), JText::_('EB_REGISTRATION_CANCEL_MESSAGE_FREE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'registration_cancel_message_free',  $this->message->registration_cancel_message_free , '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('registration_cancel_message_paid', JText::_('EB_REGISTRATION_CANCEL_MESSAGE_PAID'), JText::_('EB_REGISTRATION_CANCEL_MESSAGE_PAID_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'registration_cancel_message_paid',  $this->message->registration_cancel_message_paid, '100%', '250', '75', '8' ) ;?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('user_registration_cancel_subject', JText::_('EB_USER_REGISTRATION_CANCEL_SUBJECT')); ?>
		<p class="eb-available-tags">
			<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE]</strong>
		</p>
	</div>
	<div class="controls">
		<input type="text" name="user_registration_cancel_subject" class="input-xlarge" value="<?php echo $this->message->user_registration_cancel_subject; ?>" size="50" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('user_registration_cancel_message', JText::_('EB_USER_REGISTRATION_CANCEL_MESSAGE'), JText::_('EB_USER_REGISTRATION_CANCEL_MESSAGE_EXPLAIN'));?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'user_registration_cancel_message',  $this->message->user_registration_cancel_message, '100%', '250', '75', '8' ) ;?>
	</div>
</div>
