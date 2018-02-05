<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2018 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

$rootUri = JUri::root(true);
echo JHtml::_('bootstrap.startTabSet', 'message-translation', array('active' => 'translation-page-'.$this->languages[0]->sef));
foreach ($this->languages as $language)
{
	$sef = $language->sef;
	echo JHtml::_('bootstrap.addTab', 'message-translation', 'translation-page-' . $sef, $language->title . ' <img src="' . $rootUri . '/media/com_eventbooking/flags/' . $sef . '.png" />');
	?>
	<table class="admintable adminform" style="width:100%;">
		<tr>
			<td class="key">
				<?php echo JText::_('EB_INTRO_TEXT'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'intro_text_'.$sef,  $this->message->{'intro_text_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				&nbsp;
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_ADMIN_EMAIL_SUBJECT'); ?>
			</td>
			<td>
				<input type="text" name="admin_email_subject_<?php echo $sef; ?>" class="input-xlarge" value="<?php echo $this->message->{'admin_email_subject_'.$sef}; ?>" size="80" />
			</td>
			<td width="35%">
				<strong><?php echo JText::_('EB_AVAILABLE_TAGS'); ?> : [EVENT_TITLE]</strong>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_ADMIN_EMAIL_BODY'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'admin_email_body_'.$sef,  $this->message->{'admin_email_body_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_AVAILABLE_TAGS'); ?> :[REGISTRATION_DETAIL], [EVENT_TITLE], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]</strong>
			</td>
		</tr>
		<tr>
			<td width="30%" class="key">
				<?php echo JText::_('EB_USER_EMAIL_SUBJECT'); ?>
			</td>
			<td>
				<input type="text" name="user_email_subject_<?php echo $sef; ?>" class="input-xlarge" value="<?php echo $this->message->{'user_email_subject_'.$sef}; ?>" size="50" />
			</td>
			<td>
				<strong><?php echo JText::_('EB_AVAILABLE_TAGS'); ?> : [EVENT_TITLE]</strong>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_USER_EMAIL_BODY'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'user_email_body_'.$sef,  $this->message->{'user_email_body_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_AVAILABLE_TAGS'); ?> :[REGISTRATION_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]</strong>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_USER_EMAIL_BODY_OFFLINE'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'user_email_body_offline_'.$sef,  $this->message->{'user_email_body_offline_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_AVAILABLE_TAGS'); ?> :[REGISTRATION_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]</strong>
			</td>
		</tr>
		<tr>
			<td width="30%" class="key">
				<?php echo JText::_('EB_GROUP_MEMBER_EMAIL_SUBJECT'); ?>
			</td>
			<td>
				<input type="text" name="group_member_email_subject_<?php echo $sef; ?>" class="input-xlarge" value="<?php echo $this->message->{'group_member_email_subject_'.$sef}; ?>" size="50" />
			</td>
			<td>
				<strong><?php echo JText::_('EB_AVAILABLE_TAGS'); ?> : [EVENT_TITLE]</strong>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_GROUP_MEMBER_EMAIL_BODY'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'group_member_email_body_'.$sef,  $this->message->{'group_member_email_body_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_AVAILABLE_TAGS'); ?> :[MEMBER_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]</strong>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_REGISTRATION_FORM_MESSAGE'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'registration_form_message_'.$sef,  $this->message->{'registration_form_message_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_REGISTRATION_FORM_MESSAGE_EXPLAIN'); ?> <?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: [EVENT_TITLE]</strong>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_REGISTRATION_FORM_MESSAGE_GROUP'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'registration_form_message_group_'.$sef,  $this->message->{'registration_form_message_group_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_REGISTRATION_FORM_MESSAGE_GROUP_EXPLAIN'); ?> <?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: [EVENT_TITLE]</strong>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_NUMBER_OF_MEMBERS_FORM_MESSAGE'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'number_members_form_message_'.$sef,  $this->message->{'number_members_form_message_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_NUMBER_OF_MEMBERS_FORM_MESSAGE_EXPLAIN'); ?></strong>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_MEMBER_INFORMATION_FORM_MESSAGE'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'member_information_form_message_'.$sef,  $this->message->{'member_information_form_message_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_MEMBER_INFORMATION_FORM_MESSAGE_EXPLAIN'); ?></strong>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_CONFIRMATION_MESSAGE'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'confirmation_message_'.$sef,  $this->message->{'confirmation_message_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_CONFIRMATION_MESSAGE_EXPLAIN'); ?>. <?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: [EVENT_TITLE], [AMOUNT]</strong>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_THANK_YOU_MESSAGE'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'thanks_message_'.$sef,  $this->message->{'thanks_message_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_THANK_YOU_MESSAGE_EXPLAIN'); ?></strong>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_THANK_YOU_MESSAGE_OFFLINE'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'thanks_message_offline_'.$sef,  $this->message->{'thanks_message_offline_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_THANK_YOU_MESSAGE_OFFLINE_EXPLAIN'); ?></strong>
			</td>
		</tr>

		<?php
		if (count($this->extraOfflinePlugins))
		{
			foreach ($this->extraOfflinePlugins as $offlinePaymentPlugin)
			{
				$name   = $offlinePaymentPlugin->name;
				$title  = $offlinePaymentPlugin->title;
				$prefix = str_replace('os_offline', '', $name);
				?>
                <tr>
                    <td class="key">
						<?php echo JText::_('User email body (' . $title . ')'); ?>
                    </td>
                    <td>
						<?php echo $editor->display('user_email_body_offline' . $prefix.'_'.$sef, $this->message->{'user_email_body_offline' . $prefix.'_'.$sef}, '100%', '250', '75', '8'); ?>
                    </td>
                    <td>
                        <strong><?php echo JText::_('EB_AVAILABLE_TAGS'); ?> :[REGISTRATION_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]</strong>
                    </td>
                </tr>

                <tr>
                    <td class="key">
						<?php echo JText::_('Thank you message (' . $title . ')'); ?>
                    </td>
                    <td>
						<?php echo $editor->display('thanks_message_offline' . $prefix.'_'.$sef, $this->message->{'thanks_message_offline' . $prefix.'_'.$sef}, '100%', '250', '75', '8'); ?>
                    </td>
                    <td>
                        <strong><?php echo JText::_('EB_THANK_YOU_MESSAGE_OFFLINE_EXPLAIN'); ?></strong>
                    </td>
                </tr>
				<?php
			}
		}
		?>


		<tr>
			<td class="key">
				<?php echo JText::_('EB_CANCEL_MESSAGE'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'cancel_message_'.$sef,  $this->message->{'cancel_message_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_CANCEL_MESSAGE_EXPLAIN') ; ?></strong>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_REGISTRATION_CANCEL_MESSAGE_FREE'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'registration_cancel_message_free_'.$sef,  $this->message->{'registration_cancel_message_free_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_REGISTRATION_CANCEL_MESSAGE_FREE_EXPLAIN'); ?></strong>
			</td>
		</tr>

		<tr>
			<td class="key">
				<?php echo JText::_('EB_REGISTRATION_CANCEL_MESSAGE_PAID'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'registration_cancel_message_paid_'.$sef,  $this->message->{'registration_cancel_message_paid_'.$sef}, '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_REGISTRATION_CANCEL_MESSAGE_PAID_EXPLAIN'); ?></strong>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_USER_REGISTRATION_CANCEL_SUBJECT'); ?>
			</td>
			<td class="controls">
				<input type="text" name="user_registration_cancel_subject_<?php echo $sef; ?>" class="input-xlarge" value="<?php echo $this->message->{'user_registration_cancel_subject_'.$sef}; ?>" size="50" />
			</td>
			<td>
				&nbsp;
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_USER_REGISTRATION_CANCEL_MESSAGE'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'user_registration_cancel_message_'.$sef,  $this->message->{'user_registration_cancel_message_'.$sef}, '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				&nbsp;
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_INVITATION_FORM_MESSAGE'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'invitation_form_message_'.$sef,  $this->message->{'invitation_form_message_'.$sef}, '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_INVITATION_FORM_MESSAGE_EXPLAIN'); ?></strong>
			</td>
		</tr>
		<tr>
			<td width="30%" class="key">
				<?php echo JText::_('EB_INVITATION_EMAIL_SUBJECT'); ?>
			</td>
			<td>
				<input type="text" name="invitation_email_subject_<?php echo $sef ?>" class="input-xlarge" value="<?php echo $this->message->{'invitation_email_subject_'.$sef}; ?>" size="50" />
			</td>
			<td>
				<strong><?php echo JText::_('EB_AVAILABLE_TAGS'); ?> : [EVENT_TITLE]</strong>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_INVITATION_EMAIL_BODY'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'invitation_email_body_'.$sef,  $this->message->{'invitation_email_body_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong>[SENDER_NAME],[NAME], [EVENT_TITLE], [INVITATION_NAME], [EVENT_DETAIL_LINK], [PERSONAL_MESSAGE]</strong>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_INVITATION_COMPLETE_MESSAGE'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'invitation_complete_'.$sef,  $this->message->{'invitation_complete_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<?php echo JText::_('EB_INVITATION_COMPLETE_MESSAGE_EXPLAIN'); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_REMINDER_EMAIL_SUBJECT'); ?>
			</td>
			<td>
				<input type="text" name="reminder_email_subject_<?php echo $sef; ?>" class="input-xlarge" value="<?php echo $this->message->{'reminder_email_subject_'.$sef}; ?>" size="50" />
			</td>
			<td>
				<strong><?php echo JText::_('EB_AVAILABLE_TAGS'); ?> : [EVENT_TITLE]</strong>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_REMINDER_EMAIL_BODY'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'reminder_email_body_'.$sef,  $this->message->{'reminder_email_body_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_AVAILABLE_TAG'); ?> :[REGISTRATION_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]</strong>
			</td>
		</tr>
		<tr>
			<td  class="key">
				<?php echo JText::_('EB_CANCEL_NOTIFICATION_EMAIL_SUBJECT'); ?>
			</td>
			<td>
				<input type="text" name="registration_cancel_email_subject_<?php echo $sef; ?>" class="input-xlarge" value="<?php echo $this->message->{'registration_cancel_email_subject_'.$sef}; ?>" size="50" />
			</td>
			<td>
				<strong><?php echo JText::_('EB_AVAILABLE_TAGS'); ?> : [EVENT_TITLE]</strong>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_CANCEL_NOTIFICATION_EMAIL_BODY'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'registration_cancel_email_body_'.$sef,  $this->message->{'registration_cancel_email_body_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_AVAILABLE_TAGS'); ?> :[REGISTRATION_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]</strong>
			</td>
		</tr>

		<tr>
			<td  class="key">
				<?php echo JText::_('EB_REGISTRATION_APPROVED_EMAIL_SUBJECT'); ?>
			</td>
			<td>
				<input type="text" name="registration_approved_email_subject_<?php echo $sef; ?>" class="input-xlarge" value="<?php echo $this->message->{'registration_approved_email_subject_'.$sef}; ?>" size="50" />
			</td>
			<td>
				<strong><?php echo JText::_('EB_AVAILABLE_TAGS'); ?> : [EVENT_TITLE]</strong>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_REGISTRATION_APPROVED_EMAIL_BODY'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'registration_approved_email_body_'.$sef,  $this->message->{'registration_approved_email_body_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_AVAILABLE_TAGS'); ?> :[REGISTRATION_DETAIL], [FIRST_NAME], [LAST_NAME], [ORGANIZATION], [ADDRESS], [ADDRESS2], [CITY], [STATE], [CITY], [ZIP], [COUNTRY], [PHONE], [FAX], [EMAIL], [COMMENT], [AMOUNT]</strong>
			</td>
		</tr>

		<tr>
			<td class="key">
				<?php echo JText::_('EB_WAITINGLIST_FORM_MESSAGE'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'waitinglist_form_message_'.$sef,  $this->message->{'waitinglist_form_message_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_WAITINGLIST_FORM_MESSAGE_EXPLAIN'); ?> <?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: [EVENT_TITLE]</strong>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_WAITINGLIST_COMPLETE_MESSAGE'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'waitinglist_complete_message_'.$sef,  $this->message->{'waitinglist_complete_message_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_WAITINGLIST_COMPLETE_MESSAGE_EXPLAIN'); ?> <?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: [EVENT_TITLE], [FIRST_NAME], [LAST_NAME]</strong>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_WAITINGLIST_CONFIRMATION_SUBJECT');  ?>
			</td>
			<td>
				<input type="text" name="watinglist_confirmation_subject_<?php echo $sef; ?>" class="input-xlarge" size="70" value="<?php echo $this->message->{'watinglist_confirmation_subject_'.$sef} ; ?>" />
			</td>
			<td>
				<?php echo JText::_('EB_WAITINGLIST_CONFIRMATION_SUBJECT_EXPLAIN');  ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_WAITINGLIST_CONFIRMATION_BODY'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'watinglist_confirmation_body_'.$sef,  $this->message->{'watinglist_confirmation_body_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_WAITINGLIST_COMPLETE_MESSAGE_EXPLAIN'); ?> <?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: [EVENT_TITLE], [FIRST_NAME], [LAST_NAME]</strong>
			</td>
		</tr>

		<tr>
			<td class="key">
				<?php echo JText::_('EB_WAITINGLIST_NOTIFICATION_SUBJECT');  ?>
			</td>
			<td>
				<input type="text" name="watinglist_notification_subject_<?php echo $sef; ?>" class="input-xlarge" size="70" value="<?php echo $this->message->{'watinglist_notification_subject_'.$sef} ; ?>" />
			</td>
			<td>
				<?php echo JText::_('EB_WAITINGLIST_NOTIFICATION_SUBJECT_EXPLAIN');  ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_WAITINGLIST_NOTIFICATION_BODY'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'watinglist_notification_body_'.$sef,  $this->message->{'watinglist_notification_body_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_WAITINGLIST_NOTIFICATION_BODY_EXPLAIN'); ?> <?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: [EVENT_TITLE], [FIRST_NAME], [LAST_NAME]</strong>
			</td>
		</tr>

		<tr>
			<td class="key">
				<?php echo JText::_('EB_REGISTRANT_WAITINGLIST_NOTIFICATION_SUBJECT');  ?>
			</td>
			<td>
				<input type="text" name="registrant_waitinglist_notification_subject_<?php echo $sef; ?>" class="input-xlarge" size="70" value="<?php echo $this->message->{'registrant_waitinglist_notification_subject_'.$sef} ; ?>" />
			</td>
			<td>
				<?php echo JText::_('EB_REGISTRANT_WAITINGLIST_NOTIFICATION_SUBJECT_EXPLAIN');  ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_REGISTRANT_WAITINGLIST_NOTIFICATION_BODY'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'registrant_waitinglist_notification_body_'.$sef,  $this->message->{'registrant_waitinglist_notification_body_'.$sef} , '100%', '250', '75', '8' ) ;?>
			</td>
			<td>
				<strong><?php echo JText::_('EB_REGISTRANT_WAITINGLIST_NOTIFICATION_BODY_EXPLAIN'); ?> <?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: [EVENT_TITLE], [FIRST_NAME], [LAST_NAME]</strong>
			</td>
		</tr>
	</table>
	<?php
	echo JHtml::_('bootstrap.endTab');
}
echo JHtml::_('bootstrap.endTabSet');
