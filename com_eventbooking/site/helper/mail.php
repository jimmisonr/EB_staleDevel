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

class EventbookingHelperMail
{
	/**
	 * From Name
	 *
	 * @var string
	 */
	public static $fromName;

	/**
	 * From Email
	 *
	 * @var string
	 */
	public static $fromEmail;

	/**
	 * Helper function for sending emails to registrants and administrator
	 *
	 * @param EventbookingTableRegistrant $row
	 * @param object                      $config
	 */
	public static function sendEmails($row, $config)
	{
		if ($config->send_emails == 3)
		{
			return;
		}

		$message     = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_events')
			->where('id = ' . $row->event_id);
		$db->setQuery($query);
		if ($fieldSuffix)
		{
			EventbookingHelperDatabase::getMultilingualFields($query, array('title'), $fieldSuffix);
		}
		$event = $db->loadObject();

		if (strlen(trim($event->notification_emails)) > 0)
		{
			$config->notification_emails = $event->notification_emails;
		}

		$mailer = static::getMailer($config);

		if ($event->created_by)
		{
			$eventCreator = JUser::getInstance($event->created_by);
			if (JMailHelper::isEmailAddress($eventCreator->email) && !$eventCreator->authorise('core.admin'))
			{
				$mailer->addReplyTo($eventCreator->email);
			}
		}

		if ($config->multiple_booking)
		{
			$rowFields = EventbookingHelper::getFormFields($row->id, 4);
		}
		elseif ($row->is_group_billing)
		{
			$rowFields = EventbookingHelper::getFormFields($row->event_id, 1);
		}
		else
		{
			$rowFields = EventbookingHelper::getFormFields($row->event_id, 0);
		}
		$form = new RADForm($rowFields);
		$data = EventbookingHelper::getRegistrantData($row, $rowFields);
		$form->bind($data);
		$form->buildFieldsDependency();
		$replaces = EventbookingHelper::buildTags($row, $form, $event, $config);

		$query->clear()
			->select('a.*')
			->from('#__eb_locations AS a')
			->innerJoin('#__eb_events AS b ON a.id = b.location_id')
			->where('b.id=' . $row->event_id);

		$db->setQuery($query);
		$rowLocation = $db->loadObject();

		// Notification email send to user
		if ($config->send_emails == 0 || $config->send_emails == 2)
		{
			if ($fieldSuffix && strlen($message->{'user_email_subject' . $fieldSuffix}))
			{
				$subject = $message->{'user_email_subject' . $fieldSuffix};
			}
			else
			{
				$subject = $message->user_email_subject;
			}

			if (!$row->published && strpos($row->payment_method, 'os_offline') !== false)
			{
				if ($fieldSuffix && EventbookingHelper::isValidMessage($event->{'user_email_body_offline' . $fieldSuffix}))
				{
					$body = $event->{'user_email_body_offline' . $fieldSuffix};
				}
				elseif ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'user_email_body_offline' . $fieldSuffix}))
				{
					$body = $message->{'user_email_body_offline' . $fieldSuffix};
				}
				elseif (EventbookingHelper::isValidMessage($event->user_email_body_offline))
				{
					$body = $event->user_email_body_offline;
				}
				else
				{
					$body = $message->user_email_body_offline;
				}
			}
			else
			{
				if ($fieldSuffix && EventbookingHelper::isValidMessage($event->{'user_email_body' . $fieldSuffix}))
				{
					$body = $event->{'user_email_body' . $fieldSuffix};
				}
				elseif ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'user_email_body' . $fieldSuffix}))
				{
					$body = $message->{'user_email_body' . $fieldSuffix};
				}
				elseif (EventbookingHelper::isValidMessage($event->user_email_body))
				{
					$body = $event->user_email_body;
				}
				else
				{
					$body = $message->user_email_body;
				}
			}

			foreach ($replaces as $key => $value)
			{
				$key     = strtoupper($key);
				$subject = str_ireplace("[$key]", $value, $subject);
				$body    = str_ireplace("[$key]", $value, $body);
			}
			$body = EventbookingHelper::convertImgTags($body);

			if (strpos($body, '[QRCODE]') !== false)
			{
				EventbookingHelper::generateQrcode($row->id);
				$imgTag = '<img src="' . EventbookingHelper::getSiteUrl() . 'media/com_eventbooking/qrcodes/' . $row->id . '.png" border="0" />';
				$body   = str_ireplace("[QRCODE]", $imgTag, $body);
			}

			$invoiceFilePath = '';
			if ($config->activate_invoice_feature && $config->send_invoice_to_customer && $row->invoice_number)
			{
				EventbookingHelper::generateInvoicePDF($row);
				$invoiceFilePath = JPATH_ROOT . '/media/com_eventbooking/invoices/' . EventbookingHelper::formatInvoiceNumber($row->invoice_number, $config) . '.pdf';
				$mailer->addAttachment($invoiceFilePath);
			}

			static::addEventAttachments($mailer, $row, $event, $config);

			//Generate and send ics file to registrants
			if ($config->send_ics_file)
			{
				$ics = new EventbookingHelperIcs();
				$ics->setName($event->title)
					->setDescription($event->short_description)
					->setOrganizer(static::$fromEmail, static::$fromName)
					->setStart($event->event_date)
					->setEnd($event->event_end_date);

				if ($rowLocation)
				{
					$ics->setLocation($rowLocation->name);
				}
				$fileName = JApplicationHelper::stringURLSafe($event->title) . '.ics';
				$mailer->addAttachment($ics->save(JPATH_ROOT . '/media/com_eventbooking/icsfiles/', $fileName));
			}

			if (JMailHelper::isEmailAddress($row->email))
			{
				static::send($mailer, array($row->email), $subject, $body);
				$mailer->clearAllRecipients();
			}

			if ($config->send_email_to_group_members && $row->is_group_billing)
			{
				// Remove invoice from attachment, group members should not receive invoice
				if ($invoiceFilePath && file_exists($invoiceFilePath))
				{
					$mailer->removeAttachment(0);
				}

				$query->clear()
					->select('*')
					->from('#__eb_registrants')
					->where('group_id = ' . $row->id)
					->order('id');
				$db->setQuery($query);
				$rowMembers = $db->loadObjectList();
				if (count($rowMembers))
				{
					$memberReplaces = array();

					$memberReplaces['registration_detail']      = $replaces['registration_detail'];
					$memberReplaces['group_billing_first_name'] = $row->first_name;
					$memberReplaces['group_billing_last_name']  = $row->last_name;
					$memberReplaces['group_billing_email']      = $row->email;

					$memberReplaces['event_title']       = $replaces['event_title'];
					$memberReplaces['event_date']        = $replaces['event_date'];
					$memberReplaces['transaction_id']    = $replaces['transaction_id'];
					$memberReplaces['date']              = $replaces['date'];
					$memberReplaces['short_description'] = $replaces['short_description'];
					$memberReplaces['description']       = $replaces['short_description'];
					$memberReplaces['location']          = $replaces['location'];
					$memberFormFields                    = EventbookingHelper::getFormFields($row->event_id, 2);
					foreach ($rowMembers as $rowMember)
					{
						if (!JMailHelper::isEmailAddress($rowMember->email))
						{
							continue;
						}

						if (strlen($message->{'group_member_email_subject' . $fieldSuffix}))
						{
							$subject = $message->{'group_member_email_subject' . $fieldSuffix};
						}
						else
						{
							$subject = $message->group_member_email_subject;
						}

						if (EventbookingHelper::isValidMessage($message->{'group_member_email_body' . $fieldSuffix}))
						{
							$body = $message->{'group_member_email_body' . $fieldSuffix};
						}
						else
						{
							$body = $message->group_member_email_body;
						}

						if (!$subject)
						{
							break;
						}

						if (!$body)
						{
							break;
						}

						//Build the member form
						$memberForm = new RADForm($memberFormFields);
						$memberData = EventbookingHelper::getRegistrantData($rowMember, $memberFormFields);
						$memberForm->bind($memberData);
						$memberForm->buildFieldsDependency();
						$fields = $memberForm->getFields();
						foreach ($fields as $field)
						{
							if ($field->hideOnDisplay)
							{
								$fieldValue = '';
							}
							else
							{
								if (is_string($field->value) && is_array(json_decode($field->value)))
								{
									$fieldValue = implode(', ', json_decode($field->value));
								}
								else
								{
									$fieldValue = $field->value;
								}
							}
							$memberReplaces[$field->name] = $fieldValue;
						}
						$memberReplaces['member_detail'] = EventbookingHelper::getMemberDetails($config, $rowMember, $event, $rowLocation, true, $memberForm);
						foreach ($memberReplaces as $key => $value)
						{
							$key     = strtoupper($key);
							$body    = str_ireplace("[$key]", $value, $body);
							$subject = str_ireplace("[$key]", $value, $subject);
						}
						$body = EventbookingHelper::convertImgTags($body);

						static::send($mailer, array($rowMember->email), $subject, $body);
						$mailer->clearAllRecipients();
					}
				}
			}

			// Clear attachments
			$mailer->clearAttachments();
			$mailer->clearReplyTos();
		}

		// Send notification emails to admin if needed
		if ($config->send_emails == 0 || $config->send_emails == 1)
		{
			if ($config->send_invoice_to_admin && !empty($invoiceFilePath) && file_exists($invoiceFilePath))
			{
				$mailer->addAttachment($invoiceFilePath);
			}

			// Send attachment to admin email if needed
			if ($config->send_attachments_to_admin)
			{
				static::addRegistrationFormAttachments($mailer, $rowFields, $replaces);
			}

			$emails = $emails = explode(',', $config->notification_emails);
			if ($fieldSuffix && strlen($message->{'admin_email_subject' . $fieldSuffix}))
			{
				$subject = $message->{'admin_email_subject' . $fieldSuffix};
			}
			else
			{
				$subject = $message->admin_email_subject;
			}

			if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'admin_email_body' . $fieldSuffix}))
			{
				$body = $message->{'admin_email_body' . $fieldSuffix};
			}
			else
			{
				$body = $message->admin_email_body;
			}

			if ($row->payment_method == 'os_offline_creditcard')
			{
				$replaces['registration_detail'] = EventbookingHelper::getEmailContent($config, $row, true, $form, true);
			}

			foreach ($replaces as $key => $value)
			{
				$key     = strtoupper($key);
				$subject = str_ireplace("[$key]", $value, $subject);
				$body    = str_ireplace("[$key]", $value, $body);
			}
			$body = EventbookingHelper::convertImgTags($body);

			if (strpos($body, '[QRCODE]') !== false)
			{
				EventbookingHelper::generateQrcode($row->id);
				$imgTag = '<img src="' . EventbookingHelper::getSiteUrl() . 'media/com_eventbooking/qrcodes/' . $row->id . '.png" border="0" />';
				$body   = str_ireplace("[QRCODE]", $imgTag, $body);
			}

			if (!empty($eventCreator->email) && !$eventCreator->authorise('core.admin') && JMailHelper::isEmailAddress($eventCreator->email) && !in_array($eventCreator->email, $emails))
			{
				$emails[] = $eventCreator->email;
			}

			static::send($mailer, $emails, $subject, $body);
		}
	}

	/**
	 * Send email to registrant when admin approves his registration
	 *
	 * @param EventbookingTableRegistrant $row
	 * @param RADConfig                   $config
	 */
	public static function sendRegistrationApprovedEmail($row, $config)
	{
		if (!JMailHelper::isEmailAddress($row->email))
		{
			return;
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		EventbookingHelper::loadLanguage();

		$message     = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);
		$mailer      = static::getMailer($config);

		if ($config->multiple_booking)
		{
			$rowFields = EventbookingHelper::getFormFields($row->id, 4);
		}
		elseif ($row->is_group_billing)
		{
			$rowFields = EventbookingHelper::getFormFields($row->event_id, 1);
		}
		else
		{
			$rowFields = EventbookingHelper::getFormFields($row->event_id, 0);
		}
		$form = new RADForm($rowFields);
		$data = EventbookingHelper::getRegistrantData($row, $rowFields);
		$form->bind($data);
		$form->buildFieldsDependency();

		$query->select('*')
			->from('#__eb_events')
			->where('id = ' . $row->event_id);
		$db->setQuery($query);
		if ($fieldSuffix)
		{
			EventbookingHelperDatabase::getMultilingualFields($query, array('title'), $fieldSuffix);
		}
		$event = $db->loadObject();

		$replaces = EventbookingHelper::buildTags($row, $form, $event, $config);

		if (strlen(trim($event->registration_approved_email_subject)))
		{
			$subject = $event->registration_approved_email_subject;
		}
		elseif (strlen($message->{'registration_approved_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'registration_approved_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->registration_approved_email_subject;
		}

		if ($fieldSuffix && EventbookingHelper::isValidMessage($event->{'registration_approved_email_body' . $fieldSuffix}))
		{
			$body = $event->{'registration_approved_email_body' . $fieldSuffix};
		}
		elseif ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'registration_approved_email_body' . $fieldSuffix}))
		{
			$body = $message->{'registration_approved_email_body' . $fieldSuffix};
		}
		elseif (EventbookingHelper::isValidMessage($event->registration_approved_email_body))
		{
			$body = $event->registration_approved_email_body;
		}
		else
		{
			$body = $message->registration_approved_email_body;
		}

		foreach ($replaces as $key => $value)
		{
			$key     = strtoupper($key);
			$subject = str_ireplace("[$key]", $value, $subject);
			$body    = str_ireplace("[$key]", $value, $body);
		}
		$body = EventbookingHelper::convertImgTags($body);

		if (strpos($body, '[QRCODE]') !== false)
		{
			EventbookingHelper::generateQrcode($row->id);
			$imgTag = '<img src="' . EventbookingHelper::getSiteUrl() . 'media/com_eventbooking/qrcodes/' . $row->id . '.png" border="0" />';
			$body   = str_ireplace("[QRCODE]", $imgTag, $body);
		}

		if ($config->activate_invoice_feature && $row->invoice_number)
		{
			EventbookingHelper::generateInvoicePDF($row);
			$mailer->addAttachment(JPATH_ROOT . '/media/com_eventbooking/invoices/' . EventbookingHelper::formatInvoiceNumber($row->invoice_number, $config) . '.pdf');
		}

		static::send($mailer, array($row->email), $subject, $body);
	}

	/**
	 * Send email to registrant when admin change the status to cancelled
	 *
	 * @param EventbookingTableRegistrant $row
	 * @param object                      $config
	 */
	public static function sendRegistrationCancelledEmail($row, $config)
	{
		if (!JMailHelper::isEmailAddress($row->email))
		{
			return;
		}

		$app   = JFactory::getApplication();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		if ($app->isSite())
		{
			if ($row->language && $row->language != '*')
			{
				$tag = $row->language;
			}
			else
			{
				$tag = EventbookingHelper::getDefaultLanguage();
			}

			JFactory::getLanguage()->load('com_eventbooking', JPATH_ROOT, $tag);
		}

		$message     = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);

		if ($fieldSuffix && strlen($message->{'user_registration_cancel_subject' . $fieldSuffix}))
		{
			$subject = $message->{'user_registration_cancel_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->user_registration_cancel_subject;
		}

		if (empty($subject))
		{
			return;
		}

		if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'user_registration_cancel_message' . $fieldSuffix}))
		{
			$body = $message->{'user_registration_cancel_message' . $fieldSuffix};
		}
		else
		{
			$body = $message->user_registration_cancel_message;
		}

		if (empty($body))
		{
			return;
		}

		if (!JMailHelper::isEmailAddress($row->email))
		{
			return;
		}

		$mailer = static::getMailer($config);

		if ($config->multiple_booking)
		{
			$rowFields = EventbookingHelper::getFormFields($row->id, 4);
		}
		elseif ($row->is_group_billing)
		{
			$rowFields = EventbookingHelper::getFormFields($row->event_id, 1);
		}
		else
		{
			$rowFields = EventbookingHelper::getFormFields($row->event_id, 0);
		}

		$form = new RADForm($rowFields);
		$data = EventbookingHelper::getRegistrantData($row, $rowFields);
		$form->bind($data);
		$form->buildFieldsDependency();

		$query->select('*')
			->from('#__eb_events')
			->where('id=' . $row->event_id);
		$db->setQuery($query);
		if ($fieldSuffix)
		{
			EventbookingHelperDatabase::getMultilingualFields($query, array('title'), $fieldSuffix);
		}
		$event    = $db->loadObject();
		$replaces = EventbookingHelper::buildTags($row, $form, $event, $config);

		foreach ($replaces as $key => $value)
		{
			$key     = strtoupper($key);
			$subject = str_ireplace("[$key]", $value, $subject);
			$body    = str_ireplace("[$key]", $value, $body);
		}
		$body = EventbookingHelper::convertImgTags($body);

		static::send($mailer, array($row->email), $subject, $body);
	}

	/**
	 * Send email when users fill-in waitinglist
	 *
	 * @param  object $row
	 * @param object  $config
	 */
	public static function sendWaitinglistEmail($row, $config)
	{
		$message     = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);
		$db          = JFactory::getDbo();
		$query       = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_events')
			->where('id=' . $row->event_id);
		$db->setQuery($query);
		if ($fieldSuffix)
		{
			EventbookingHelperDatabase::getMultilingualFields($query, array('title'), $fieldSuffix);
		}
		$event = $db->loadObject();

		if (strlen(trim($event->notification_emails)) > 0)
		{
			$config->notification_emails = $event->notification_emails;
		}

		$mailer = static::getMailer($config);
		if ($config->multiple_booking)
		{
			$rowFields = EventbookingHelper::getFormFields($row->id, 4);
		}
		elseif ($row->is_group_billing)
		{
			$rowFields = EventbookingHelper::getFormFields($row->event_id, 1);
		}
		else
		{
			$rowFields = EventbookingHelper::getFormFields($row->event_id, 0);
		}
		$form = new RADForm($rowFields);
		$data = EventbookingHelper::getRegistrantData($row, $rowFields);
		$form->bind($data);
		$form->buildFieldsDependency();
		$replaces = EventbookingHelper::buildTags($row, $form, $event, $config);

		//Notification email send to user
		if ($fieldSuffix && strlen($message->{'watinglist_confirmation_subject' . $fieldSuffix}))
		{
			$subject = $message->{'watinglist_confirmation_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->watinglist_confirmation_subject;
		}

		if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'watinglist_confirmation_body' . $fieldSuffix}))
		{
			$body = $message->{'watinglist_confirmation_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->watinglist_confirmation_body;
		}
		$subject = str_ireplace('[EVENT_TITLE]', $event->title, $subject);
		foreach ($replaces as $key => $value)
		{
			$key  = strtoupper($key);
			$body = str_ireplace("[$key]", $value, $body);
		}

		if (JMailHelper::isEmailAddress($row->email))
		{
			static::send($mailer, array($row->email), $subject, $body);
			$mailer->clearAllRecipients();
		}

		$emails = explode(',', $config->notification_emails);

		if (strlen($message->{'watinglist_notification_subject' . $fieldSuffix}))
		{
			$subject = $message->{'watinglist_notification_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->watinglist_notification_subject;
		}

		if (EventbookingHelper::isValidMessage($message->{'watinglist_notification_body' . $fieldSuffix}))
		{
			$body = $message->{'watinglist_notification_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->watinglist_notification_body;
		}
		$subject = str_ireplace('[EVENT_TITLE]', $event->title, $subject);
		foreach ($replaces as $key => $value)
		{
			$key  = strtoupper($key);
			$body = str_ireplace("[$key]", $value, $body);
		}
		$body = EventbookingHelper::convertImgTags($body);

		static::send($mailer, $emails, $subject, $body);
	}

	/**
	 * Send email when registrants complete deposit payment
	 *
	 * @param EventbookingTableRegistrant $row
	 * @param RADConfig                   $config
	 */
	public static function sendDepositPaymentEmail($row, $config)
	{
		$message     = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_events')
			->where('id=' . $row->event_id);
		$db->setQuery($query);
		if ($fieldSuffix)
		{
			EventbookingHelperDatabase::getMultilingualFields($query, array('title'), $fieldSuffix);
		}
		$event = $db->loadObject();

		if (strlen(trim($event->notification_emails)) > 0)
		{
			$config->notification_emails = $event->notification_emails;
		}

		$mailer   = static::getMailer($config);
		$replaces = EventbookingHelper::buildDepositPaymentTags($row, $config);

		//Notification email send to user
		if (JMailHelper::isEmailAddress($row->email))
		{
			if ($fieldSuffix && strlen($message->{'deposit_payment_user_email_subject' . $fieldSuffix}))
			{
				$subject = $message->{'deposit_payment_user_email_subject' . $fieldSuffix};
			}
			else
			{
				$subject = $message->deposit_payment_user_email_subject;
			}

			if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'deposit_payment_user_email_body' . $fieldSuffix}))
			{
				$body = $message->{'deposit_payment_user_email_body' . $fieldSuffix};
			}
			else
			{
				$body = $message->deposit_payment_user_email_body;
			}

			foreach ($replaces as $key => $value)
			{
				$key     = strtoupper($key);
				$body    = str_ireplace("[$key]", $value, $body);
				$subject = str_ireplace("[$key]", $value, $subject);
			}

			static::send($mailer, array($row->email), $subject, $body);
			$mailer->clearAllRecipients();
		}

		$emails = explode(',', $config->notification_emails);

		if (strlen($message->{'deposit_payment_admin_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'deposit_payment_admin_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->deposit_payment_admin_email_subject;
		}

		if (EventbookingHelper::isValidMessage($message->{'deposit_payment_admin_email_body' . $fieldSuffix}))
		{
			$body = $message->{'deposit_payment_admin_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->deposit_payment_admin_email_body;
		}

		foreach ($replaces as $key => $value)
		{
			$key     = strtoupper($key);
			$subject = str_ireplace("[$key]", $value, $subject);
			$body    = str_ireplace("[$key]", $value, $body);
		}

		$body = EventbookingHelper::convertImgTags($body);

		static::send($mailer, $emails, $subject, $body);
	}

	/**
	 * Send new event notification email to admin and users when new event is submitted in the frontend
	 *
	 * @param EventbookingTableEvent $row
	 * @param RADConfig              $config
	 */
	public static function sendNewEventNotificationEmail($row, $config)
	{
		$user        = JFactory::getUser();
		$message     = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);

		$mailer = static::getMailer($config);

		$replaces = array(
			'username'    => $user->username,
			'name'        => $user->name,
			'event_id'    => $row->id,
			'event_title' => $row->title,
			'event_date'  => JHtml::_('date', $row->event_date, $config->event_date_format, null),
			'event_link'  => JUri::root() . 'administrator/index.php?option=com_eventbooking&view=event&id=' . $row->id,
		);

		//Notification email send to user

		if ($fieldSuffix && strlen($message->{'submit_event_user_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'submit_event_user_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->submit_event_user_email_subject;
		}

		if ($fieldSuffix && EventbookingHelper::isValidMessage($message->{'submit_event_user_email_body' . $fieldSuffix}))
		{
			$body = $message->{'submit_event_user_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->submit_event_user_email_body;
		}

		if ($subject)
		{
			foreach ($replaces as $key => $value)
			{
				$key     = strtoupper($key);
				$subject = str_ireplace("[$key]", $value, $subject);
				$body    = str_ireplace("[$key]", $value, $body);
			}

			$body = EventbookingHelper::convertImgTags($body);
			if (JMailHelper::isEmailAddress($user->email))
			{
				static::send($mailer, array($user->email), $subject, $body);
				$mailer->clearAllRecipients();
			}
		}

		$emails = explode(',', $config->notification_emails);
		$emails = array_map('trim', $emails);

		if (strlen($message->{'submit_event_admin_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'submit_event_admin_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->submit_event_admin_email_subject;
		}

		if (!$subject)
		{
			return;
		}

		if (EventbookingHelper::isValidMessage($message->{'submit_event_admin_email_body' . $fieldSuffix}))
		{
			$body = $message->{'submit_event_admin_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->submit_event_admin_email_body;
		}

		foreach ($replaces as $key => $value)
		{
			$key     = strtoupper($key);
			$subject = str_ireplace("[$key]", $value, $subject);
			$body    = str_ireplace("[$key]", $value, $body);
		}

		$body = EventbookingHelper::convertImgTags($body);

		static::send($mailer, $emails, $subject, $body);
	}

	/**
	 * Send reminder email to registrants
	 *
	 * @param int  $numberEmailSendEachTime
	 * @param null $bccEmail
	 */
	public static function sendReminder($numberEmailSendEachTime = 0, $bccEmail = null)
	{
		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);
		$config  = EventbookingHelper::getConfig();
		$message = EventbookingHelper::getMessages();
		$mailer  = static::getMailer($config);

		$siteUrl = EventbookingHelper::getSiteUrl();

		EventbookingHelper::loadLanguage();

		if (JMailHelper::isEmailAddress($bccEmail))
		{
			$mailer->addBcc($bccEmail);
		}

		if (!$numberEmailSendEachTime)
		{
			$numberEmailSendEachTime = 15;
		}

		$eventFields = array('b.id as event_id', 'b.event_date', 'b.title');

		if (JLanguageMultilang::isEnabled())
		{
			$languages = EventbookingHelper::getLanguages();
			if (count($languages))
			{
				foreach ($languages as $language)
				{
					$eventFields[] = 'b.title_' . $language->sef;
				}
			}
		}

		$query->select('a.*, c.name AS location_name')
			->select(implode(',', $eventFields))
			->from('#__eb_registrants AS a')
			->innerJoin('#__eb_events AS b ON a.event_id = b.id')
			->leftJoin('#__eb_locations AS c ON b.location_id = c.id')
			->where('(a.published = 1 OR (a.payment_method LIKE "os_offline%" AND a.published = 0))')
			->where('a.is_reminder_sent = 0')
			->where('b.published = 1')
			->where('b.enable_auto_reminder = 1')
			->where('DATEDIFF(b.event_date, NOW()) <= b.remind_before_x_days')
			->where('DATEDIFF(b.event_date, NOW()) >= 0')
			->order('b.event_date, a.register_date');

		$db->setQuery($query, 0, $numberEmailSendEachTime);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (Exception  $e)
		{
			$rows = array();
		}

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row = $rows[$i];

			if (!JMailHelper::isEmailAddress($row->email))
			{
				continue;
			}

			$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);
			if ($fieldSuffix && strlen($message->{'reminder_email_subject' . $fieldSuffix}))
			{
				$emailSubject = $message->{'reminder_email_subject' . $fieldSuffix};
			}
			else
			{
				$emailSubject = $message->reminder_email_subject;
			}

			$eventTitle = $row->{'title' . $fieldSuffix};

			$emailSubject = str_ireplace('[EVENT_TITLE]', $eventTitle, $emailSubject);

			if ($fieldSuffix && strlen($message->{'reminder_email_body' . $fieldSuffix}))
			{
				$emailBody = $message->{'reminder_email_body' . $fieldSuffix};
			}
			else
			{
				$emailBody = $message->reminder_email_body;
			}

			$replaces                = array();
			$replaces['event_date']  = JHtml::_('date', $row->event_date, $config->event_date_format, null);
			$replaces['first_name']  = $row->first_name;
			$replaces['last_name']   = $row->last_name;
			$replaces['event_title'] = $eventTitle;
			$replaces['location']    = $row->location_name;

			// On process [REGISTRATION_DETAIL] tag if it is available in the email message
			if (strpos($emailBody, '[REGISTRATION_DETAIL]') !== false)
			{
				// Build this tag
				if ($config->multiple_booking)
				{
					$rowFields = EventbookingHelper::getFormFields($row->id, 4);
				}
				elseif ($row->is_group_billing)
				{
					$rowFields = EventbookingHelper::getFormFields($row->event_id, 1);
				}
				else
				{
					$rowFields = EventbookingHelper::getFormFields($row->event_id, 0);
				}

				$form = new RADForm($rowFields);
				$data = EventbookingHelper::getRegistrantData($row, $rowFields);
				$form->bind($data);
				$form->buildFieldsDependency();

				$replaces['registration_detail'] = EventbookingHelper::getEmailContent($config, $row, true, $form);
			}

			if (strpos($emailBody, '[QRCODE]') !== false)
			{
				EventbookingHelper::generateQrcode($row->id);
				$imgTag    = '<img src="' . $siteUrl . 'media/com_eventbooking/qrcodes/' . $row->id . '.png" border="0" />';
				$emailBody = str_ireplace("[QRCODE]", $imgTag, $emailBody);
			}

			foreach ($replaces as $key => $value)
			{
				$emailBody = str_ireplace('[' . strtoupper($key) . ']', $value, $emailBody);
			}

			$emailBody = EventbookingHelper::convertImgTags($emailBody);
			static::send($mailer, array($row->email), $emailSubject, $emailBody);
			$mailer->clearAddresses();

			$query->clear()
				->update('#__eb_registrants')
				->set('is_reminder_sent = 1')
				->where('id = ' . (int) $row->id);
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Send deposit payment reminder email to registrants
	 *
	 * @param int  $numberDays
	 * @param int  $numberEmailSendEachTime
	 * @param null $bccEmail
	 */
	public static function sendDepositReminder($numberDays, $numberEmailSendEachTime = 0, $bccEmail = null)
	{
		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);
		$config  = EventbookingHelper::getConfig();
		$message = EventbookingHelper::getMessages();
		$mailer  = static::getMailer($config);
		$Itemid  = EventbookingHelper::getItemid();
		$siteUrl = EventbookingHelper::getSiteUrl();

		if ($bccEmail)
		{
			$mailer->addBcc($bccEmail);
		}

		if (!$numberDays)
		{
			$numberDays = 7;
		}

		if (!$numberEmailSendEachTime)
		{
			$numberEmailSendEachTime = 15;
		}

		$query->select('a.id, a.first_name, a.last_name, a.email, a.amount, a.deposit_amount, b.title, b.event_date, b.currency_symbol')
			->from('#__eb_registrants AS a')
			->innerJoin('#__eb_events AS b ON a.event_id = b.id')
			->where('(a.published = 1 OR (a.payment_method LIKE "os_offline%" AND a.published = 0))')
			->where('a.payment_status = 0')
			->where('a.group_id = 0')
			->where('a.is_deposit_payment_reminder_sent = 0')
			->where('b.published = 1')
			->where('DATEDIFF(b.event_date, NOW()) <= ' . $numberDays)
			->where('DATEDIFF(b.event_date, NOW()) >= 0')
			->order('b.event_date, a.register_date');

		$db->setQuery($query, 0, $numberEmailSendEachTime);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (Exception  $e)
		{
			$rows = array();
		}

		foreach ($rows as $row)
		{
			if (!JMailHelper::isEmailAddress($row->email))
			{
				continue;
			}

			$emailSubject = $message->deposit_payment_reminder_email_subject;
			$emailBody    = $message->deposit_payment_reminder_email_body;

			$replaces                         = array();
			$replaces['event_date']           = JHtml::_('date', $row->event_date, $config->event_date_format, null);
			$replaces['first_name']           = $row->first_name;
			$replaces['last_name']            = $row->last_name;
			$replaces['event_title']          = $row->title;
			$replaces['amount']               = EventbookingHelper::formatCurrency($row->amount - $row->deposit_amount, $config, $row->currency_symbol);
			$replaces['registration_id']      = $row->id;
			$replaces['deposit_payment_link'] = $siteUrl . 'index.php?option=com_eventbooking&view=payment&amp;registrant_id=' . $row->id . '&Itemid=' . $Itemid;

			foreach ($replaces as $key => $value)
			{
				$emailSubject = str_ireplace('[' . strtoupper($key) . ']', $value, $emailSubject);
				$emailBody    = str_ireplace('[' . strtoupper($key) . ']', $value, $emailBody);
			}

			$emailBody = EventbookingHelper::convertImgTags($emailBody);
			static::send($mailer, array($row->email), $emailSubject, $emailBody);
			$mailer->clearAddresses();

			$query->clear()
				->update('#__eb_registrants')
				->set('is_deposit_payment_reminder_sent = 1')
				->where('id = ' . (int) $row->id);
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Create and initialize mailer object from configuration data
	 *
	 * @param $config
	 *
	 * @return JMail
	 */
	private static function getMailer($config)
	{
		$mailer = JFactory::getMailer();

		if ($config->from_name)
		{
			$fromName = $config->from_name;
		}
		else
		{
			$fromName = JFactory::getConfig()->get('fromname');
		}

		if ($config->from_email)
		{
			$fromEmail = $config->from_email;
		}
		else
		{
			$fromEmail = JFactory::getConfig()->get('mailfrom');
		}

		$mailer->setSender(array($fromEmail, $fromName));
		$mailer->isHtml(true);

		if (empty($config->notification_emails))
		{
			$config->notification_emails = $fromEmail;
		}

		static::$fromName  = $fromName;
		static::$fromEmail = $fromEmail;

		return $mailer;
	}

	/**
	 * Add event's attachments to mailer object for sending emails to registrants
	 *
	 * @param JMail                       $mailer
	 * @param EventbookingTableRegistrant $row
	 * @param EventbookingTableEvent      $event
	 * @param RADConfig                   $config
	 */
	private static function addEventAttachments($mailer, $row, $event, $config)
	{
		if ($config->multiple_booking)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('attachment')
				->from('#__eb_events')
				->where('id IN (SELECT event_id FROM #__eb_registrants AS a WHERE a.id=' . $row->id . ' OR a.cart_id=' . $row->id . ' ORDER BY a.id)');
			$db->setQuery($query);
			$attachmentFiles = $db->loadColumn();
		}
		elseif ($event->attachment)
		{
			$attachmentFiles = array($event->attachment);
		}
		else
		{
			$attachmentFiles = array();
		}

		// Remove empty value from array
		$attachmentFiles = array_filter($attachmentFiles);

		// Add all valid attachments to email
		foreach ($attachmentFiles as $attachmentFile)
		{
			$files = explode('|', $attachmentFile);
			foreach ($files as $file)
			{
				$filePath = JPATH_ROOT . '/media/com_eventbooking/' . $file;
				if ($file && file_exists($filePath))
				{
					$mailer->addAttachment($filePath);
				}
			}
		}
	}

	/**
	 * Add file uploads to the mailer object for sending to administrator
	 *
	 * @param JMail $mailer
	 * @param array $rowFields
	 * @param array $replaces
	 */
	private static function addRegistrationFormAttachments($mailer, $rowFields, $replaces)
	{
		$attachmentsPath = JPATH_ROOT . '/media/com_eventbooking/files/';
		for ($i = 0, $n = count($rowFields); $i < $n; $i++)
		{
			$rowField = $rowFields[$i];
			if ($rowField->fieldtype == 'File')
			{
				if (isset($replaces[$rowField->name]))
				{
					$fileName = $replaces[$rowField->name];
					if ($fileName && file_exists($attachmentsPath . '/' . $fileName))
					{
						$pos = strpos($fileName, '_');
						if ($pos !== false)
						{
							$originalFilename = substr($fileName, $pos + 1);
						}
						else
						{
							$originalFilename = $fileName;
						}
						$mailer->addAttachment($attachmentsPath . '/' . $fileName, $originalFilename);
					}
				}
			}
		}
	}

	/**
	 * Process sending after all the data has been initialized
	 *
	 * @param JMail  $mailer
	 * @param array  $emails
	 * @param string $subject
	 * @param string $body
	 */
	private static function send($mailer, $emails, $subject, $body)
	{
		if (empty($subject))
		{
			return;
		}

		$emails = array_map('trim', $emails);

		for ($i = 0, $n = count($emails); $i < $n; $i++)
		{
			if (!JMailHelper::isEmailAddress($emails[$i]))
			{
				unset($emails[$i]);
			}
		}

		if (count($emails) == 0)
		{
			return;
		}

		$mailer->addRecipient($emails[0]);

		if (count($emails) > 1)
		{
			unset($emails[0]);
			$mailer->addBcc($emails);
		}

		$mailer->setSubject($subject)
			->setBody($body)
			->Send();
	}
}
