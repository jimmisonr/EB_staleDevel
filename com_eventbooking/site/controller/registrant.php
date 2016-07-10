<?php
/**
 * @version            2.8.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

class EventbookingControllerRegistrant extends EventbookingController
{
	/**
	 * Save the registration record and back to registration record list
	 */
	public function save()
	{
		$this->csrfProtection();
		$model = $this->getModel('registrant');
		$model->store($this->input);
		$return = base64_decode($this->input->getString('return', ''));
		if ($return)
		{
			$this->setRedirect($return);
		}
		else
		{
			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('registrants', $this->input->getInt('Itemid')), false));
		}
	}

	/**
	 * Delete the selected registration record
	 *
	 */
	public function delete()
	{
		$this->csrfProtection();

		$user   = JFactory::getUser();
		$config = EventbookingHelper::getConfig();
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);

		$registrantId = $this->input->getInt('registrant_id', 0);
		$canDelete    = false;

		$query->select('a.*, b.created_by')
			->from('#__eb_registrants AS a')
			->innerJoin('#__eb_events AS b ON a.event_id = b.id')
			->where('a.id = ' . $registrantId);
		$db->setQuery($query);
		$rowRegistrant = $db->loadObject();

		if (!$config->get('enable_delete_registrants', 1))
		{
			throw new RuntimeException('Delete registrants option is disabled. Please contact administrator', 403);
		}

		if (!$rowRegistrant)
		{
			throw new RuntimeException('Invalid registration record');
		}

		if ($user->authorise('core.admin', 'com_eventbooking'))
		{
			$canDelete = true;
		}
		elseif ($user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			if (!$config->only_show_registrants_of_event_owner || ($rowRegistrant->created_by == $user->id))
			{
				$canDelete = true;
			}
		}

		if ($canDelete)
		{
			$model = $this->getModel('Registrant');
			$model->delete(array($registrantId));

			$this->setRedirect(JRoute::_('index.php?option=com_eventbooking&view=registrants&Itemid=' . $this->input->getInt('Itemid')), JText::_('EB_REGISTRANT_DELETED'));
		}
		else
		{
			throw new RuntimeException('You don\'t have permission to delete registrant', 403);
		}
	}

	/**
	 * Cancel registration for the event
	 */
	public function cancel()
	{
		$app              = JFactory::getApplication();
		$db               = JFactory::getDbo();
		$query            = $db->getQuery(true);
		$user             = JFactory::getUser();
		$Itemid           = $this->input->getInt('Itemid', 0);
		$id               = $this->input->getInt('id', 0);
		$registrationCode = $this->input->getString('cancel_code', '');
		$fieldSuffix      = EventbookingHelper::getFieldSuffix();
		if ($id)
		{
			$query->select('a.id, a.title' . $fieldSuffix . ' AS title, b.user_id, cancel_before_date, DATEDIFF(cancel_before_date, NOW()) AS number_days')
				->from('#__eb_events AS a')
				->innerJoin('#__eb_registrants AS b ON a.id = b.event_id')
				->where('b.id = ' . $id);
		}
		else
		{
			$query->select('a.id, a.title' . $fieldSuffix . ' AS title, b.id AS registrant_id, b.user_id, cancel_before_date, DATEDIFF(cancel_before_date, NOW()) AS number_days')
				->from('#__eb_events AS a')
				->innerJoin('#__eb_registrants AS b ON a.id = b.event_id')
				->where('b.registration_code = ' . $db->quote($registrationCode));
		}
		$db->setQuery($query);
		$rowEvent = $db->loadObject();

		if (!$rowEvent)
		{
			$app->redirect(JRoute::_('index.php?option=com_eventbooking&Itemid=' . $Itemid), JText::_('EB_INVALID_ACTION'));
		}

		if (($user->get('id') == 0 && !$registrationCode) || ($user->get('id') != $rowEvent->user_id))
		{
			$app->redirect(JRoute::_('index.php?option=com_eventbooking&Itemid=' . $Itemid), JText::_('EB_INVALID_ACTION'));
		}

		if ($rowEvent->number_days < 0)
		{
			$msg = JText::sprintf('EB_CANCEL_DATE_PASSED', JHtml::_('date', $rowEvent->cancel_before_date, EventbookingHelper::getConfigValue('date_format'), null));
			$app->redirect(JRoute::_('index.php?option=com_eventbooking&Itemid=' . $Itemid), $msg);
		}

		if ($registrationCode)
		{
			$id = $rowEvent->registrant_id;
		}

		$model = $this->getModel('register');
		$model->cancelRegistration($id);
		$this->setRedirect(JRoute::_('index.php?option=com_eventbooking&view=registrationcancel&id=' . $id . '&Itemid=' . $Itemid, false));
	}

	/**
	 * Cancel editing a registration record
	 */
	public function cancel_edit()
	{
		$return = base64_decode($this->input->getString('return', ''));
		if ($return)
		{
			$this->setRedirect($return);
		}
		else
		{
			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('registrants', $this->input->getInt('Itemid')), false));
		}
	}

	/**
	 * Download invoice associated to the registration record
	 *
	 * @throws Exception
	 */
	public function download_invoice()
	{
		$user = JFactory::getUser();
		if (!$user->id)
		{
			JFactory::getApplication()->redirect('index.php', JText::_('You do not have permission to download the invoice'));
		}

		$id = $this->input->getInt('id', 0);
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_eventbooking/table');
		$row = JTable::getInstance('eventbooking', 'Registrant');
		$row->load($id);
		$canDownload = false;

		if ($row->user_id == $user->id)
		{
			$canDownload = true;
		}

		if (!$canDownload)
		{
			if ($user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
			{
				$config = EventbookingHelper::getConfig();
				if ($config->only_show_registrants_of_event_owner)
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);
					$query->select('created_by')
						->from('#__eb_events')
						->where('id = ' . $row->event_id);
					$db->setQuery($query);
					$createdBy = $db->loadResult();
					if ($createdBy == $user->id)
					{
						$canDownload = true;
					}
				}
				else
				{
					$canDownload = true;
				}
			}
		}


		if (!$canDownload)
		{
			JFactory::getApplication()->redirect('index.php', JText::_('You do not have permission to download the invoice'));
		}

		EventbookingHelper::downloadInvoice($id);
	}

	/**
	 * Export registrants data into a csv file
	 */
	public function export()
	{
		$eventId = $this->input->getInt('event_id', 0);
		if (!EventbookingHelper::canExportRegistrants($eventId))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('EB_NOT_ALLOWED_TO_EXPORT'));
		}

		set_time_limit(0);
		$config = EventbookingHelper::getConfig();
		$model  = $this->getModel('registrants');

		/* @var EventbookingModelRegistrants $model */
		$model->setState('filter_event_id', $eventId)
			->setState('limitstart', 0)
			->setState('limit', 0)
			->setState('filter_order', 'tbl.id')
			->setState('filter_order_Dir', 'ASC');

		$rows = $model->getData();

		if (count($rows) == 0)
		{
			echo JText::_('There are no registrants to export');

			return;
		}

		$rowFields = EventbookingHelper::getAllEventFields($eventId);
		$fieldIds  = array();
		foreach ($rowFields as $rowField)
		{
			$fieldIds[] = $rowField->id;
		}

		$fieldValues = $model->getFieldsData($fieldIds);

		EventbookingHelperData::csvExport($rows, $config, $rowFields, $fieldValues, $eventId);
	}

	/**
	 * Checkin registrant from given ID
	 */
	public function checkin()
	{
		$user = JFactory::getUser();
		if ($user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			$model  = $this->getModel();
			$id     = $this->input->getInt('id');
			$result = $model->checkin($id);
			switch ($result)
			{
				case 0:
					$message = JText::_('EB_INVALID_REGISTRATION_RECORD');
					break;
				case 1:
					$message = JText::_('EB_REGISTRANT_ALREADY_CHECKED_IN');
					break;
				case 2:
					$message = JText::_('EB_CHECKED_IN_SUCCESSFULLY');
					break;
			}

			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('registrants', null)), $message);
		}
		else
		{
			throw new Exception('You do not have permission to checkin registrant', 403);
		}
	}

	/*
	 * Check in a registrant
	 */
	public function check_in_webapp()
	{
		JSession::checkToken('get');
		
		$user = JFactory::getUser();
		if ($user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			$id = $this->input->getInt('id');

			$model = $this->getModel();

			try
			{
				$model->checkin($id, true);
				$this->setMessage(JText::_('EB_CHECKIN_SUCCESSFULLY'));
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}

			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('registrants', null)));
		}
		else
		{
			throw new Exception('You do not have permission to checkin registrant', 403);
		}
	}

	/**
	 * Reset check in for a registrant
	 */
	public function reset_check_in()
	{
		JSession::checkToken('get');

		$user = JFactory::getUser();
		if ($user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			$id    = $this->input->getInt('id');
			$model = $this->getModel();
			try
			{
				$model->resetCheckin($id);
				$this->setMessage(JText::_('EB_RESET_CHECKIN_SUCCESSFULLY'));
			}
			catch (Exception $e)
			{
				$this->setMessage($e->getMessage(), 'error');
			}

			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('registrants', null)), $message);
		}
		else
		{
			throw new Exception('You do not have permission to checkin registrant', 403);
		}
	}
}