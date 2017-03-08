<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2017 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

class EventbookingModelMassmail extends RADModel
{
	/**
	 * Send email to all registrants of event
	 *
	 * @param $data
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function send($data)
	{
		if ($data['event_id'] >= 1)
		{
			$mailer  = JFactory::getMailer();
			$config  = EventbookingHelper::getConfig();
			$siteUrl = EventbookingHelper::getSiteUrl();
			$db      = JFactory::getDbo();
			$query   = $db->getQuery(true);

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

			$event                         = EventbookingHelperDatabase::getEvent((int) $data['event_id']);
			$replaces                      = array();
			$replaces['event_title']       = $event->title;
			$replaces['event_date']        = JHtml::_('date', $event->event_date, $config->event_date_format, null);
			$replaces['short_description'] = $event->short_description;
			$replaces['description']       = $event->description;

			if ($event->location_id)
			{
				$location                   = EventbookingHelperDatabase::getLocation($event->location_id);
				$replaces['event_location'] = $location->name . ' (' . $location->address . ', ' . $location->city . ', ' . $location->zip . ', ' . $location->country . ')';
			}
			else
			{
				$replaces['event_location'] = '';
			}


			$query->clear()
				->select('*')
				->from('#__eb_registrants')
				->where('event_id = ' . (int) $data['event_id'])
				->where('(published=1 OR (payment_method LIKE "os_offline%" AND published NOT IN (2,3)))');

			$db->setQuery($query);
			$rows    = $db->loadObjectList();
			$emails  = array();
			$subject = $data['subject'];
			$body    = EventbookingHelper::convertImgTags($data['description']);

			foreach ($replaces as $key => $value)
			{
				$key     = strtoupper($key);
				$subject = str_replace("[$key]", $value, $subject);
				$body    = str_replace("[$key]", $value, $body);
			}

			// Attach ICS file
			if ($config->send_ics_file)
			{
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

				$ics = new EventbookingHelperIcs();
				$ics->setName($event->title)
					->setDescription($event->short_description)
					->setOrganizer($fromEmail, $fromName)
					->setStart($event->event_date)
					->setEnd($event->event_end_date);

				if (!empty($location))
				{
					$ics->setLocation($location->name);
				}

				$fileName = JApplicationHelper::stringURLSafe($event->title) . '.ics';
				$mailer->addAttachment($ics->save(JPATH_ROOT . '/media/com_eventbooking/icsfiles/', $fileName));
			}

			foreach ($rows as $row)
			{
				$message = $body;
				$email   = $row->email;
				if (!in_array($email, $emails))
				{
					$downloadCertificateLink = $siteUrl . 'index.php?option=com_eventbooking&task=registrant.download_certificate&download_code=' . $row->registration_code;

					$message = str_replace("[FIRST_NAME]", $row->first_name, $message);
					$message = str_replace("[LAST_NAME]", $row->last_name, $message);
					$message = str_replace("[DOWNLOAD_CERTIFICATE_LINK]", $downloadCertificateLink, $message);

					// Process [REGISTRATION_DETAIL] tag if it is used in the message
					if (strpos($message, '[REGISTRATION_DETAIL]') !== false)
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
						$registrationDetail = EventbookingHelper::getEmailContent($config, $row, true, $form);
						$message            = str_replace("[REGISTRATION_DETAIL]", $registrationDetail, $message);
					}

					if (strpos($message, '[QRCODE]') !== false)
					{
						EventbookingHelper::generateQrcode($row->id);
						$imgTag  = '<img src="' . $siteUrl . 'media/com_eventbooking/qrcodes/' . $row->id . '.png" border="0" />';
						$message = str_ireplace("[QRCODE]", $imgTag, $message);
					}

					if (JMailHelper::isEmailAddress($email))
					{
						$emails[] = $email;
						$mailer->sendMail($fromEmail, $fromName, $email, $subject, $message, 1);
						$mailer->clearAllRecipients();
					}
				}
			}
		}

		return true;
	}
}
