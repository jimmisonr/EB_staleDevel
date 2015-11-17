<?php
/**
 * @version            2.1.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

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
			$row->bind($data);
			$row->store();
			$form = new RADForm($rowFields);
			$form->storeData($row->id, $data);

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
			if ($row->published == 1 && $published == 0)
			{
				//Change from pending to paid, trigger event, send emails
				JPluginHelper::importPlugin('eventbooking');
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('onAfterPaymentSuccess', array($row));
				EventbookingHelper::sendRegistrationApprovedEmail($row, $config);
			}
			elseif ($row->published == 2 && $published != 2 && $config->activate_waitinglist_feature)
			{
				//Registration is cancelled, send notification emails to waiting list
				EventbookingHelper::notifyWaitingList($row, $config);
			}
			$input->set('id', $row->id);

			return true;
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
			$row->payment_method = 'os_offline';
			$row->register_date  = JFactory::getDate()->toSql();
			$rate                = EventbookingHelper::getRegistrationRate($data['event_id'], $data['number_registrants']);
			$row->total_amount   = $row->amount = $rate * $data['number_registrants'] + $form->calculateFee();
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

			if ($row->published == 1)
			{
				// Trigger event and send emails
				JPluginHelper::importPlugin('eventbooking');
				$dispatcher = JDispatcher::getInstance();
				$dispatcher->trigger('onAfterPaymentSuccess', array($row));
			}
			$input->set('id', $row->id);

			return true;
		}

		return true;
	}

	/**
	 * Checkin a registration record
	 *
	 * @param $id
	 *
	 * @return int
	 */
	public function checkin($id)
	{
		$config = EventbookingHelper::getConfig();
		$db     = $this->getDbo();
		$query  = $db->getQuery(true);

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

		$row->checked_in = 1;
		$row->store();

		if ($row->is_group_billing && $config->collect_member_information)
		{
			$query->update('#__eb_registrants')
				->set('checked_in = 1')
				->where('group_id = ' . $row->id);
			$db->setQuery($query);
			$db->execute();

			$query->clear();
		}

		if ($config->multiple_booking)
		{
			$query->update('#__eb_registrants')
				->set('checked_in = 1')
				->where('cart_id = ' . (int) $row->id);
			$db->setQuery($query);
			$db->execute();
		}

		return 2;
	}
}