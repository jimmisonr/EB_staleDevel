<?php
/**
 * @version            2.0.5
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');
$document = JFactory::getDocument();
$document->addStyleDeclaration(".hasTip{display:block !important}");

$translatable = JLanguageMultilang::isEnabled() && count($this->languages);
$editor = JFactory::getEditor();
$fields = EventbookingHelperHtml::getAvailableMessagesTags();
?>
<form action="index.php?option=com_eventbooking&view=message" method="post" name="adminForm" id="adminForm" class="form-horizontal eb-configuration">
		<ul class="nav nav-tabs">
			<li class="active"><a href="#registration-form-messages-page" data-toggle="tab"><?php echo JText::_('EB_REGISTRATION_FORM_MESSAGES'); ?></a></li>
			<li><a href="#registration-email-messages-page" data-toggle="tab"><?php echo JText::_('EB_REGISTRATION_EMAIL_MESSAGES'); ?></a></li>
			<li><a href="#invitation-messages-page" data-toggle="tab"><?php echo JText::_('EB_INVITATION_MESSAGES'); ?></a></li>
			<li><a href="#waitinglist-messages-page" data-toggle="tab"><?php echo JText::_('EB_WAITINGLIST_MESSAGES'); ?></a></li>
			<?php
				if ($translatable)
				{
				?>
					<li><a href="#translation-page" data-toggle="tab"><?php echo JText::_('EB_TRANSLATION'); ?></a></li>
				<?php
				}
			?>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="registration-form-messages-page">
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
			</div>
			<div class="tab-pane" id="registration-email-messages-page">
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
			</div>
			<div class="tab-pane" id="invitation-messages-page">
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('invitation_form_message', JText::_('EB_INVITATION_FORM_MESSAGE'), JText::_('EB_INVITATION_FORM_MESSAGE_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo $editor->display( 'invitation_form_message',  $this->message->invitation_form_message, '100%', '250', '75', '8' ) ;?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('invitation_complete', JText::_('EB_INVITATION_COMPLETE_MESSAGE'), JText::_('EB_INVITATION_COMPLETE_MESSAGE_EXPLAIN')); ?>
					</div>
					<div class="controls">
						<?php echo $editor->display( 'invitation_complete',  $this->message->invitation_complete , '100%', '250', '75', '8' ) ;?>
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('invitation_email_subject', JText::_('EB_INVITATION_EMAIL_SUBJECT')); ?>
						<p class="eb-available-tags">
							<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong>[EVENT_TITLE]</strong>
						</p>
					</div>
					<div class="controls">
						<input type="text" name="invitation_email_subject" class="input-xlarge" value="<?php echo $this->message->invitation_email_subject; ?>" size="50" />
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<?php echo EventbookingHelperHtml::getFieldLabel('invitation_email_body', JText::_('EB_INVITATION_EMAIL_BODY')); ?>
						<p class="eb-available-tags">
							<?php echo JText::_('EB_AVAILABLE_TAGS'); ?>: <strong>[SENDER_NAME],[NAME], [EVENT_TITLE], [INVITATION_NAME], [EVENT_DETAIL_LINK], [PERSONAL_MESSAGE]</strong>
						</p>
					</div>
					<div class="controls">
						<?php echo $editor->display( 'invitation_email_body',  $this->message->invitation_email_body , '100%', '250', '75', '8' ) ;?>
					</div>
				</div>
			</div>
			<div class="tab-pane" id="waitinglist-messages-page">
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
			</div>
	<?php 
	if ($translatable)
	{
	?>
		<div class="tab-pane" id="translation-page">
			<ul class="nav nav-tabs">
				<?php
					$i = 0;
					foreach ($this->languages as $language) {						
						$sef = $language->sef;
						?>
						<li <?php echo $i == 0 ? 'class="active"' : ''; ?>><a href="#translation-page-<?php echo $sef; ?>" data-toggle="tab"><?php echo $language->title; ?>
							<img src="<?php echo JURI::root(); ?>media/com_eventbooking/flags/<?php echo $sef.'.png'; ?>" /></a></li>
						<?php
						$i++;	
					}
				?>			
			</ul>		
			<div class="tab-content">			
				<?php	
					$i = 0;
					foreach ($this->languages as $language)
					{												
						$sef = $language->sef;
					?>
						<div class="tab-pane<?php echo $i == 0 ? ' active' : ''; ?>" id="translation-page-<?php echo $sef; ?>">													
							<table class="admintable adminform" style="width:100%;">																											
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
						</div>										
					<?php				
						$i++;		
					}
				?>
			</div>	
		</div>
	<?php				
	}
	?>
	</div>		
	<div class="clearfix"></div>	
	<input type="hidden" name="task" value="" />	
</form>