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

class EventbookingViewRegistrantHtml extends RADViewHtml
{
	public function display()
	{
		$rootUri  = JUri::root(true);
		$document = JFactory::getDocument();
		$user     = JFactory::getUser();
		$config   = EventbookingHelper::getConfig();
		$db       = JFactory::getDbo();
		$query    = $db->getQuery(true);

		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$userId      = $user->get('id');
		$lists       = array();

		EventbookingHelper::addLangLinkForAjax();
		$document->addScriptDeclaration('var siteUrl="' . EventbookingHelper::getSiteUrl() . '";');
		$document->addScript($rootUri . '/media/com_eventbooking/assets/js/paymentmethods.js');
		$document->addScript($rootUri . '/media/com_eventbooking/assets/js/ajaxupload.js');

		$item = $this->model->getData();

		EventbookingHelper::checkEditRegistrant($item);

		if ($item->id)
		{
			$query->select('*, title' . $fieldSuffix . ' AS title')
				->from('#__eb_events')
				->where('id=' . $item->event_id);
			$db->setQuery($query);
			$event       = $db->loadObject();
			$this->event = $event;

			if ($item->is_group_billing)
			{
				$rowFields = EventbookingHelper::getFormFields($item->event_id, 1, $item->language);
			}
			else
			{
				$rowFields = EventbookingHelper::getFormFields($item->event_id, 0, $item->language);
			}

			$data = EventbookingHelper::getRegistrantData($item, $rowFields);

			$query->clear()
				->select('*')
				->from('#__eb_registrants')
				->where('group_id=' . $item->id)
				->order('id');
			$db->setQuery($query, 0, $item->number_registrants);
			$rowMembers = $db->loadObjectList();

			$useDefault = false;
		}
		else
		{
			$rowFields = EventbookingHelper::getFormFields($item->event_id, 0);

			$useDefault = true;
			$data       = array();
			$rowMembers = array();
		}

		if ($userId && $user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			$canChangeStatus = true;

			$options   = array();
			$options[] = JHtml::_('select.option', 0, JText::_('EB_PENDING'));
			$options[] = JHtml::_('select.option', 1, JText::_('EB_PAID'));
			$options[] = JHtml::_('select.option', 3, JText::_('EB_WAITING_LIST'));
			$options[] = JHtml::_('select.option', 2, JText::_('EB_CANCELLED'));

			$lists['published'] = JHtml::_('select.genericlist', $options, 'published', ' class="inputbox" ', 'value', 'text', $item->published);
		}
		else
		{
			$canChangeStatus = false;
		}

		$form = new RADForm($rowFields);
		$form->bind($data, $useDefault);

		$form->setEventId($item->event_id);

		if ($canChangeStatus)
		{
			$form->prepareFormFields('setRecalculateFee();');
		}

		$form->buildFieldsDependency();

		if (empty($item->id))
		{
			//Build list of event dropdown
			$options = array();
			$query->clear()
				->select('id, title' . $fieldSuffix . ' AS title, event_date')
				->from('#__eb_events')
				->where('published=1')
				->order('title');
			$db->setQuery($query);
			$options[] = JHtml::_('select.option', '', JText::_('Select Event'), 'id', 'title');

			$rows = $db->loadObjectList();

			if ($config->show_event_date)
			{
				foreach ($rows as $row)
				{
					$options[] = JHtml::_('select.option', $row->id, $row->title . ' (' . JHtml::_('date', $row->event_date, $config->date_format) . ')' .
						'', 'id', 'title');
				}
			}
			else
			{
				$options = array_merge($options, $rows);
			}

			$lists['event_id'] = JHtml::_('select.genericlist', $options, 'event_id', ' class="inputbox validate[required]" ', 'id', 'title', $item->event_id);
		}

		if ($config->collect_member_information && !$rowMembers && $item->number_registrants > 1)
		{
			$rowMembers = array();

			for ($i = 0; $i < $item->number_registrants; $i++)
			{
				$rowMember           = new RADTable('#__eb_registrants', 'id', $db);
				$rowMember->event_id = $item->event_id;
				$rowMember->group_id = $item->id;
				$rowMember->store();
				$rowMembers[] = $rowMember;
			}
		}

		if (count($rowMembers))
		{
			$this->memberFormFields = EventbookingHelper::getFormFields($item->event_id, 2, $item->language);
		}

		if ($config->activate_checkin_registrants)
		{
			$lists['checked_in'] = JHtml::_('select.booleanlist', 'checked_in', ' class="inputbox" ', $item->checked_in);
		}

		if ($user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking') || empty($item->published))
		{
			$canChangeFeeFields = true;
		}
		else
		{
			$canChangeFeeFields = false;
		}

		$event = EventbookingHelperDatabase::getEvent($item->event_id);

		if ($event->has_multiple_ticket_types)
		{
			$this->ticketTypes = EventbookingHelperData::getTicketTypes($event->id);

			if ($item->id)
			{
				$query->clear()
					->select('*')
					->from('#__eb_registrant_tickets')
					->where('registrant_id = ' . (int) $item->id);
				$db->setQuery($query);
				$registrantTickets = $db->loadObjectList('ticket_type_id');
			}
			else
			{
				$registrantTickets = array();
			}

			$this->registrantTickets = $registrantTickets;

			if ($user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
			{
				$canChangeTicketsQuantity = true;
			}
			else
			{
				$canChangeTicketsQuantity = false;
			}

			$this->canChangeTicketsQuantity = $canChangeTicketsQuantity;
		}

		$this->item               = $item;
		$this->config             = $config;
		$this->lists              = $lists;
		$this->canChangeStatus    = $canChangeStatus;
		$this->form               = $form;
		$this->rowMembers         = $rowMembers;
		$this->return             = $this->input->get('return', '', 'string');
		$this->canChangeFeeFields = $canChangeFeeFields;

		$this->addToolbar();

		$this->setLayout('default');

		parent::display();
	}

	/**
	 * Build Form Toolbar
	 */
	protected function addToolbar()
	{
		require_once JPATH_ADMINISTRATOR . '/includes/toolbar.php';

		JToolbarHelper::save('registrant.save', 'JTOOLBAR_SAVE');

		if ($this->item->id &&
			$this->item->published != 2 &&
			EventbookingHelper::canCancelRegistration($this->item->event_id)
		)
		{
			JToolbarHelper::custom('registrant.cancel', 'delete', 'delete', JText::_('EB_CANCEL_REGISTRATION'), false);
		}

		JToolbarHelper::cancel('registrant.cancel_edit', 'JTOOLBAR_CLOSE');
	}
}
