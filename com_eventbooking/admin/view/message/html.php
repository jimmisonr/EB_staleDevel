<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

class EventbookingViewMessageHtml extends RADViewHtml
{
	public function display()
	{
		$languages = EventbookingHelper::getLanguages();
		$message   = EventbookingHelper::getMessages();

		if (count($languages))
		{
			$translatableKeys = array(
				'intro_text',
				'admin_email_subject',
				'admin_email_body',
				'user_email_subject',
				'user_email_body',
				'user_email_body_offline',
				'group_member_email_subject',
				'group_member_email_body',
				'registration_form_message',
				'registration_form_message_group',
				'number_members_form_message',
				'member_information_form_message',
				'confirmation_message',
				'thanks_message',
				'thanks_message_offline',
				'cancel_message',
				'registration_cancel_message_free',
				'registration_cancel_message_paid',
				'invitation_form_message',
				'invitation_email_subject',
				'invitation_email_body',
				'invitation_complete',
				'reminder_email_subject',
				'reminder_email_body',
				'registration_cancel_email_subject',
				'registration_cancel_email_body',
				'registration_approved_email_subject',
				'registration_approved_email_body',
				'waitinglist_form_message',
				'waitinglist_complete_message',
				'watinglist_confirmation_subject',
				'watinglist_confirmation_body',
				'watinglist_notification_subject',
				'watinglist_notification_body',
				'registrant_waitinglist_notification_subject',
				'registrant_waitinglist_notification_body',
			);

			foreach ($languages as $language)
			{
				$sef = $language->sef;
				foreach ($translatableKeys as $key)
				{
					if (empty($message->{$key . '_' . $sef}))
					{
						$message->{$key . '_' . $sef} = $message->{$key};
					}
				}
			}
		}

		$this->languages = $languages;
		$this->message   = $message;

		$this->addToolbar();

		parent::display();
	}

	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('Emails & Messages'), 'generic.png');
		JToolBarHelper::apply('apply', 'JTOOLBAR_APPLY');
		JToolBarHelper::save('save');
		JToolBarHelper::cancel('cancel');
	}
}
