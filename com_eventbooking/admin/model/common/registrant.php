<?php
/**
 * @version            2.7.1
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

/**
 * Event Booking Registrant Model
 *
 * @package        Joomla
 * @subpackage     Event Booking
 */
class EventbookingModelCommonRegistrant extends RADModelAdmin
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
	 * Method to store a registrant
	 *
	 * @access    public
	 *
	 * @param    RADInput $input
	 *
	 * @return    boolean    True on success
	 */
	public function store($input, $ignore = array())
	{
		$config = EventbookingHelper::getConfig();
		$db     = $this->getDbo();
		$query  = $db->getQuery(true);
		$row    = $this->getTable();
		$data   = $input->getData();
		if ($data['id'])
		{
			//We will need to calculate total amount here now
			$row->load($data['id']);
			$published = $row->published;
			if ($row->is_group_billing)
			{
				$rowFields = EventbookingHelper::getFormFields($data['event_id'], 1);
			}
			else
			{
				$rowFields = EventbookingHelper::getFormFields($data['event_id'], 0);
			}
			$user = JFactory::getUser();
			if ($user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking') || empty($row->published))
			{
				$excludeFeeFields = false;
			}
			else
			{
				$excludeFeeFields = true;
			}

			// Reset number checked in counter if admin change checked in status
			if ($row->checked_in && isset($data['checked_in']) && $data['checked_in'] == 0)
			{
				$row->checked_in_count = 0;
			}

			$row->bind($data);

			$row->store();
			$form = new RADForm($rowFields);
			$form->storeData($row->id, $data, $excludeFeeFields);

			//Update group members records according to grop billing record
			if ($row->is_group_billing)
			{
				if (strpos($row->payment_method, 'os_offline') !== false)
				{
					$query->update('#__eb_registrants')
						->set('published=' . (int) $row->published)
						->where('group_id=' . $row->id);
					$db->setQuery($query);
					$db->execute();
					$query->clear();
				}

				// Update checked_in status
				$query->update('#__eb_registrants')
					->set('checked_in=' . (int) $row->checked_in)
					->set('event_id=' . (int) $row->event_id)
					->where('group_id=' . $row->id);
				$db->setQuery($query);
				$db->execute();
				$query->clear();
			}

			//Store group members data
			if ($row->number_registrants > 1 && $config->collect_member_information)
			{
				$ids              = (array) $data['ids'];
				$memberFormFields = EventbookingHelper::getFormFields($row->event_id, 2);
				for ($i = 0, $n = count($ids); $i < $n; $i++)
				{
					$memberId  = $ids[$i];
					$rowMember = $this->getTable();
					$rowMember->load($memberId);
					$rowMember->event_id       = $row->event_id;
					$rowMember->published      = $row->published;
					$rowMember->payment_method = $row->payment_method;
					$rowMember->transaction_id = $row->transaction_id;
					$memberForm                = new RADForm($memberFormFields);
					$memberForm->setFieldSuffix($i + 1);
					$memberForm->bind($data);
					$memberForm->removeFieldSuffix();
					$memberData = $memberForm->getFormData();
					$rowMember->bind($memberData);
					$rowMember->store();
					$memberForm->storeData($rowMember->id, $memberData);
				}
			}

			$this->storeRegistrantTickets($row, $data);

			if ($row->published == 1 && $published == 0)
			{
				//Change from pending to paid, trigger event, send emails
				JPluginHelper::importPlugin('eventbooking');
				JFactory::getApplication()->triggerEvent('onAfterPaymentSuccess', array($row));
				EventbookingHelper::sendRegistrationApprovedEmail($row, $config);
			}
			elseif ($row->published == 2 && $published != 2 && $config->activate_waitinglist_feature)
			{
				//Registration is cancelled, send notification emails to waiting list
				EventbookingHelper::notifyWaitingList($row, $config);
			}
			$input->set('id', $row->id);
		}
		else
		{
			// In case number registrants is empty, we set it default to 1
			$data['number_registrants'] = (int) $data['number_registrants'];
			if (empty($data['number_registrants']))
			{
				$data['number_registrants'] = 1;
			}
			$row->bind($data);
			$rowFields = EventbookingHelper::getFormFields($data['event_id'], 0);
			$form      = new RADForm($rowFields);
			$form->bind($data);

			if (!$row->payment_method || $row->published == 0)
			{
				$row->payment_method = 'os_offline';
			}

			$row->register_date = JFactory::getDate()->toSql();

			// In case total amount is not entered, calculate it automatically
			if (empty($row->total_amount))
			{
				$rate              = EventbookingHelper::getRegistrationRate($data['event_id'], $data['number_registrants']);
				$row->total_amount = $row->amount = $rate * $data['number_registrants'] + $form->calculateFee();
			}

			if (empty($row->amount))
			{
				$row->amount = $row->total_amount - $row->discount_amount + $row->tax_amount + $row->late_fee + $row->payment_processing_fee;
			}

			if ($row->number_registrants > 1)
			{
				$row->is_group_billing = 1;
			}
			else
			{
				$row->is_group_billing = 0;
			}
			$row->store();
			$form->storeData($row->id, $data);

			$this->storeRegistrantTickets($row, $data);

			if ($row->published == 1)
			{
				// Trigger event and send emails
				JPluginHelper::importPlugin('eventbooking');
				JFactory::getApplication()->triggerEvent('onAfterPaymentSuccess', array($row));
			}

			// In case individual registration, we will send notification email to registrant
			if ($row->number_registrants == 1)
			{
				EventbookingHelper::sendEmails($row, $config);
			}

			$input->set('id', $row->id);
		}

		return true;
	}

	/**
	 * Method to remove registrants
	 *
	 * @access    public
	 * @return    boolean    True on success
	 */
	public function delete($cid = array())
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$row   = $this->getTable();
		if (count($cid))
		{
			foreach ($cid as $registrantId)
			{
				$row->load($registrantId);
				if ($row->group_id > 0)
				{
					$query->update('#__eb_registrants')
						->set('number_registrants = number_registrants -1')
						->where('id=' . $row->group_id);
					$db->setQuery($query);
					$db->execute();
					$query->clear();

					$query->select('number_registrants')
						->from('#__eb_registrants')
						->where('id=' . $row->group_id);
					$db->setQuery($query);
					$numberRegistrants = (int) $db->loadResult();
					$query->clear();
					if ($numberRegistrants == 0)
					{
						$query->delete('#__eb_field_values')->where('registrant_id=' . $row->group_id);
						$db->setQuery($query);
						$db->execute();
						$query->clear();

						$sql = 'DELETE FROM #__eb_registrants WHERE id = ' . $row->group_id;
						$db->setQuery($sql);
						$db->execute();
						$query->clear();
					}
				}
			}

			$cids = implode(',', $cid);
			$query->select('id')
				->from('#__eb_registrants')
				->where('group_id IN (' . $cids . ')');
			$db->setQuery($query);

			$cid           = array_merge($cid, $db->loadColumn());
			$registrantIds = implode(',', $cid);

			$query->clear()
				->delete('#__eb_field_values')
				->where('registrant_id IN (' . $registrantIds . ')');
			$db->setQuery($query)
				->execute();

			$query->clear()
				->delete('#__eb_registrants')
				->where('id IN (' . $registrantIds . ')');
			$db->setQuery($query)
				->execute();

			$query->clear()
				->delete('#__eb_registrant_tickets')
				->where('registrant_id IN (' . $registrantIds . ')');
			$db->setQuery($query)
				->execute();
		}

		return true;
	}

	/**
	 * Check-in a registration record
	 *
	 * @param $id
	 * @pram  $group
	 *
	 * @return int
	 */
	public function checkin($id, $group = false)
	{
		$row = $this->getTable();
		$row->load($id);

		if (empty($row))
		{
			return 0;
		}

		if ($row->checked_in)
		{
			return 1;
		}

		if ($group)
		{
			$row->checked_in_count = $row->number_registrants;
		}
		else
		{
			$row->checked_in_count = $row->checked_in_count + 1;
		}

		if ($row->checked_in_count == $row->number_registrants)
		{
			$row->checked_in = 1;
		}
		$row->store();

		return 2;
	}

	/**
	 * Reset check-in status for the registration record
	 *
	 * @param $id
	 *
	 * @throws Exception
	 */
	public function resetCheckin($id)
	{
		$row = $this->getTable();
		$row->load($id);

		if (empty($row))
		{
			throw new Exception(JText::sprintf('Error checkin registration record %s', $id));
		}

		$row->checked_in_count = 0;
		$row->checked_in       = 0;

		$row->store();
	}

	/**
	 * Store registrant tickets data when the record is created/updated in the backend
	 *
	 * @param JTable $row
	 * @param array  $data
	 */
	private function storeRegistrantTickets($row, $data)
	{
		$event = EventbookingHelperDatabase::getEvent($row->event_id);
		if ($event->has_multiple_ticket_types)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->delete('#__eb_registrant_tickets')
				->where('registrant_id = ' . $row->id);
			$db->setQuery($query)
				->execute();

			$ticketTypes       = EventbookingHelperData::getTicketTypes($row->event_id);
			$numberRegistrants = 0;
			foreach ($ticketTypes as $ticketType)
			{
				if (!empty($data['ticket_type_' . $ticketType->id]))
				{
					$quantity = (int) $data['ticket_type_' . $ticketType->id];
					$query->clear()
						->insert('#__eb_registrant_tickets')
						->columns('registrant_id, ticket_type_id, quantity')
						->values("$row->id, $ticketType->id, $quantity");
					$db->setQuery($query)
						->execute();

					$numberRegistrants += $quantity;
				}
			}

			$query->clear('')
				->update('#__eb_registrants')
				->set('number_registrants = ' . $numberRegistrants)
				->where('id = ' . $row->id);
			$db->setQuery($query)
				->execute();
		}
	}
}