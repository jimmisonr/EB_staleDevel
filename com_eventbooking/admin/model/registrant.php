<?php
/**
 * @version            2.7.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

class EventbookingModelRegistrant extends EventbookingModelCommonRegistrant
{

	/**
	 * Instantiate the model.
	 *
	 * @param array $config configuration data for the model
	 *
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->state->insert('filter_event_id', 'int', 0);
	}

	/**
	 * Initial registrant data
	 *
	 * @see RADModelAdmin::initData()
	 */
	public function initData()
	{
		parent::initData();
		$this->data->event_id = $this->state->filter_event_id;
	}

	/**
	 * Resend confirmation email to registrant
	 *
	 * @param $id
	 *
	 * @return bool True if email is successfully delivered
	 */
	public function resendEmail($id)
	{
		$row = $this->getTable();
		$row->load($id);
		if ($row->group_id > 0)
		{
			// We don't send email to group members, return false
			return false;
		}

		// Load the default frontend language
		$lang = JFactory::getLanguage();
		$tag  = $row->language;
		if (!$tag || $tag == '*')
		{
			$tag = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}
		$lang->load('com_eventbooking', JPATH_ROOT, $tag);

		$config = EventbookingHelper::getConfig();
		EventbookingHelper::sendEmails($row, $config);

		return true;
	}

	/**
	 * Send batch emails to selected registrants
	 *
	 * @param RADInput $input
	 *
	 * @throws Exception
	 */
	public function batchMail($input)
	{
		$cid     = $input->get('cid', array(), 'array');
		$emailSubject = $input->getString('subject');
		$emailMessage    = $input->get('message', '', 'raw');

		if (empty($cid))
		{
			throw new Exception('Please select registrants to send mass mail');
		}

		if (empty($emailSubject))
		{
			throw new Exception('Please enter subject of the email');
		}

		if (empty($emailMessage))
		{
			throw new Exception('Please enter message ofthe email');
		}

		// OK, data is valid, process sending email
		$mailer = JFactory::getMailer();
		$config = EventbookingHelper::getConfig();
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);

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

		// Get list of registration records
		$query->select('a.*, b.title, b.event_date, b.event_end_date, b.short_description, b.description')
				->from('#__eb_registrants AS a')
				->innerJoin('#__eb_events AS b ON a.event_id = b.id')
				->where('a.id IN (' . implode(',', $cid) . ')');
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		// Get list of core fields
		$query->clear();
		$query->select('name')
			->from('#__eb_fields')
			->where('is_core = 1');
		$db->setQuery($query);
		$fields = $db->loadObjectList();

		$emails = array();

		foreach ($rows as $row)
		{
			$subject = $emailSubject;
			$message = $emailMessage;
			$email   = $row->email;
			if (!in_array($email, $emails))
			{
				$replaces                      = array();
				$replaces['event_title']       = $row->title;
				$replaces['event_date']        = JHtml::_('date', $row->event_date, $config->event_date_format, null);
				$replaces['event_end_date']    = JHtml::_('date', $row->event_end_date, $config->event_date_format, null);
				$replaces['short_description'] = $row->short_description;
				$replaces['description']       = $row->description;
				$replaces['first_name']       = $row->first_name;


				foreach($replaces as $key => $value)
				{
					$key     = strtoupper($key);
					$subject = str_ireplace("[$key]", $value, $subject);
					$message    = str_ireplace("[$key]", $value, $message);
				}

				foreach($fields as $field)
				{
					$key = $field->name;
					$value = $row->{$field->name};
					$subject = str_ireplace("[$key]", $value, $subject);
					$message    = str_ireplace("[$key]", $value, $message);
				}

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

				$emails[] = $email;
				$mailer->sendMail($fromEmail, $fromName, $email, $subject, $message, 1);
				$mailer->clearAllRecipients();
			}
		}
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param array $cid   A list of the primary keys to change.
	 * @param int   $state The value of the published state.
	 *
	 * @throws Exception
	 */
	public function publish($cid, $state = 1)
	{
		$db = $this->getDbo();
		if (($state == 1) && count($cid))
		{
			JPluginHelper::importPlugin('eventbooking');
			$config     = EventbookingHelper::getConfig();
			$row        = new RADTable('#__eb_registrants', 'id', $db);
			foreach ($cid as $registrantId)
			{
				$row->load($registrantId);
				if (!$row->published)
				{
					// Re-generate invoice with Paid status
					if ($config->activate_invoice_feature && $row->invoice_number)
					{
						EventbookingHelper::generateInvoicePDF($row);
					}

					EventbookingHelper::sendRegistrationApprovedEmail($row, $config);

					// Trigger event
					JFactory::getApplication()->triggerEvent('onAfterPaymentSuccess', array($row));
				}
			}
		}

		$cids  = implode(',', $cid);
		$query = $db->getQuery(true);
		$query->update('#__eb_registrants')
			->set('published = ' . (int) $state)
			->where("(id IN ($cids) OR group_id IN ($cids))")
			->where("payment_method LIKE 'os_offline%'");
		$db->setQuery($query);
		$db->execute();
	}
}