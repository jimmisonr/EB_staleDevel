<?php
/**
 * @version            2.0.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

/**
 * Event Booking controller
 * @package        Joomla
 * @subpackage     Event Booking
 */
class EventbookingControllerRegistrant extends EventbookingController
{
	/**
	 * Save the registration record and back to registration record list
	 */
	public function save()
	{
		$Itemid = JRequest::getInt('Itemid', 0);
		$model  = &$this->getModel('registrant');
		$post   = JRequest::get('post');
		$model->store($post);
		$from = JRequest::getVar('from', '');
		if ($from == 'history')
		{
			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('history', $Itemid), false));
		}
		else
		{
			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('registrants', $Itemid), false));
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
		$Itemid           = JRequest::getInt('Itemid', 0);
		$id               = JRequest::getInt('id');
		$registrationCode = JRequest::getVar('cancel_code', '');
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

	public function download_invoice()
	{
		$user = JFactory::getUser();
		if (!$user->id)
		{
			JFactory::getApplication()->redirect('index.php', JText::_('You do not have permission to download the invoice'));
		}
		$id = JRequest::getInt('id');
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_eventbooking/tables');
		$row = JTable::getInstance('eventbooking', 'Registrant');
		$row->load($id);
		if (!$row->id || ($row->user_id) != $user->id)
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
		$db          = JFactory::getDbo();
		$query       = $db->getQuery(true);
		$config      = EventbookingHelper::getConfig();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$eventId     = JRequest::getInt('event_id');
		if (!EventbookingHelper::canExportRegistrants($eventId))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('EB_NOT_ALLOWED_TO_EXPORT'));
		}
		if (!$eventId)
		{
			JFactory::getApplication()->redirect('index.php', JText::_('EB_PLEASE_CHOOSE_AN_EVENT_TO_EXPORT_REGISTRANTS'));
		}
		$where   = array();
		$where[] = '(a.published = 1 OR (a.payment_method LIKE "os_offline%" AND a.published NOT IN (2,3)))';
		if ($eventId)
		{
			$where[] = ' a.event_id=' . $eventId;
		}
		if (isset($config->include_group_billing_in_csv_export) && !$config->include_group_billing_in_csv_export)
		{
			$where[] = ' a.is_group_billing = 0 ';
		}
		if (!$config->include_group_members_in_csv_export)
		{
			$where[] = ' a.group_id = 0 ';
		}
		if ($config->show_coupon_code_in_registrant_list)
		{
			$sql = 'SELECT a.*, b.event_date, b.title' . $fieldSuffix . ' AS event_title, c.code AS coupon_code FROM #__eb_registrants AS a INNER JOIN #__eb_events AS b ON a.event_id = b.id LEFT JOIN #__eb_coupons AS c ON a.coupon_id=c.id WHERE ' .
				implode(' AND ', $where) . ' ORDER BY a.id ';
		}
		else
		{
			$sql = 'SELECT a.*, b.event_date, b.title' . $fieldSuffix . ' AS event_title FROM #__eb_registrants AS a INNER JOIN #__eb_events AS b ON a.event_id = b.id WHERE ' .
				implode(' AND ', $where) . ' ORDER BY a.id ';
		}
		$db->setQuery($sql);
		$rows = $db->loadObjectList();
		if (count($rows) == 0)
		{
			JFactory::getApplication()->redirect('index.php', JText::_('EB_NO_REGISTRANTS_TO_EXPORT'));
		}
		if ($eventId)
		{
			if ($config->custom_field_by_category)
			{
				$query->clear();
				$query->select('category_id')
					->from('#__eb_event_categories')
					->where('event_id=' . $eventId)
					->where('main_category=1');
				$db->setQuery($query);
				$categoryId = (int) $db->loadResult();
				$sql        = 'SELECT id, name, title, is_core FROM #__eb_fields WHERE published=1 AND (category_id=0 OR category_id=' . $categoryId .
					') ORDER BY ordering';
			}
			else
			{
				$sql = 'SELECT id, name, title, is_core FROM #__eb_fields WHERE published=1 AND (event_id = -1 OR id IN (SELECT field_id FROM #__eb_field_events WHERE event_id=' . $eventId . ')) ORDER BY ordering';
			}
		}
		else
		{
			$sql = 'SELECT id, name, title, is_core FROM #__eb_fields WHERE published=1  ORDER BY ordering';
		}
		$db->setQuery($sql);
		$rowFields = $db->loadObjectList();
		//Get the custom fields value and store them into an array
		$sql = 'SELECT id FROM #__eb_registrants AS a WHERE ' . implode(' AND ', $where);
		$db->setQuery($sql);
		$registrantIds = array(0);
		$registrantIds = array_merge($registrantIds, $db->loadColumn());
		$sql           = 'SELECT registrant_id, field_id, field_value FROM #__eb_field_values WHERE registrant_id IN (' . implode(',', $registrantIds) . ')';
		$db->setQuery($sql);
		$rowFieldValues = $db->loadObjectList();
		$fieldValues    = array();
		for ($i = 0, $n = count($rowFieldValues); $i < $n; $i++)
		{
			$rowFieldValue                                                        = $rowFieldValues[$i];
			$fieldValues[$rowFieldValue->registrant_id][$rowFieldValue->field_id] = $rowFieldValue->field_value;
		}
		//Get name of groups
		$groupNames = array();
		$sql        = 'SELECT id, first_name, last_name FROM #__eb_registrants AS a WHERE is_group_billing = 1' .
			(COUNT($where) ? ' AND ' . implode(' AND ', $where) : '');
		$db->setQuery($sql);
		$rowGroups = $db->loadObjectList();
		if (count($rowGroups))
		{
			foreach ($rowGroups as $rowGroup)
			{
				$groupNames[$rowGroup->id] = $rowGroup->first_name . ' ' . $rowGroup->last_name;
			}
		}
		EventbookingHelperData::csvExport($rows, $config, $rowFields, $fieldValues, $groupNames);
	}
}