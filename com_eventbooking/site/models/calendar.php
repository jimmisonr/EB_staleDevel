<?php
/**
 * @version            1.7.1
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

/**
 * EventBooking Component Categories Model
 *
 * @package        Joomla
 * @subpackage     EventBooking
 */
class EventBookingModelCalendar extends JModelLegacy
{

	/**
	 * Categories data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Constructor
	 *
	 */
	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get array Year, Month, Day from current Request, fallback to current date
	 *
	 * @return array
	 */
	function getYMD()
	{
		static $data;

		if (!isset($data))
		{
			list($year, $month, $day) = explode('-', JHtml::_('date', 'Now', 'Y-m-d'));
			$year  = min(2100, abs(intval(JRequest::getVar('year', $year))));
			$month = min(99, abs(intval(JRequest::getVar('month', $month))));
			$day   = min(3650, abs(intval(JRequest::getVar('day', $day))));
			if ($day <= '9')
			{
				$day = '0' . $day;
			}
			if ($month <= '9')
			{
				$month = '0' . $month;
			}
			$data   = array();
			$data[] = $year;
			$data[] = $month;
			$data[] = $day;
		}

		return $data;
	}

	/**
	 * Get list of events by current month
	 *
	 * @param int $year
	 * @param int $month
	 *
	 * @return string
	 */
	function getEventsByMonth($year, $month)
	{
		$config      = EventbookingHelper::getConfig();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$db          = JFactory::getDbo();
		$query       = $db->getQuery(true);
		$startDate   = mktime(0, 0, 0, $month, 1, $year);
		$endDate     = mktime(23, 59, 59, $month, date('t', $startDate), $year);
		$startDate   = date('Y-m-d', $startDate) . " 00:00:00";
		$endDate     = date('Y-m-d', $endDate) . " 23:59:59";
		$query->select('a.*,title' . $fieldSuffix . ' AS title, SUM(b.number_registrants) AS total_registrants')
			->from('#__eb_events AS a')
			->leftJoin('#__eb_registrants AS b ON (a.id = b.event_id ) AND b.group_id = 0 AND (b.published=1 OR (b.payment_method LIKE "os_offline%" AND b.published NOT IN (2,3)))')
			->where('a.published = 1')
			->where('a.access in (' . implode(',' ,JFactory::getUser()->getAuthorisedViewLevels()) . ')')
			->group('a.id')
			->order('a.event_date ASC, a.ordering ASC');
		if ($config->show_multiple_days_event_in_calendar)
		{
			$query->where("((`event_date` BETWEEN '$startDate' AND '$endDate') OR (MONTH(event_end_date) = $month AND YEAR(event_end_date) = $year ))");
		}
		else
		{
			$query->where("`event_date` BETWEEN '$startDate' AND '$endDate'");
		}
		if ($config->hide_past_events)
		{
			$currentDate = JHtml::_('date', 'Now', 'Y-m-d');
			$query->where('DATE(event_date) >= ' . $db->quote($currentDate));
		}
		$db->setQuery($query);
		if ($config->show_multiple_days_event_in_calendar)
		{
			$rows      = $db->loadObjectList();
			$rowEvents = array();
			for ($i = 0, $n = count($rows); $i < $n; $i++)
			{
				$row      = $rows[$i];
				$arrDates = explode('-', $row->event_date);
				if ($arrDates[0] == $year && $arrDates[1] == $month)
				{
					$rowEvents[] = $row;
				}
				$startDateParts = explode(' ', $row->event_date);
				$startTime      = strtotime($startDateParts[0]);
				$startDateTime  = strtotime($row->event_date);
				$endDateParts   = explode(' ', $row->event_end_date);
				$endTime        = strtotime($endDateParts[0]);
				$count          = 0;
				while ($startTime < $endTime)
				{
					$count++;
					$rowNew             = clone ($row);
					$rowNew->event_date = date('Y-m-d H:i:s', $startDateTime + $count * 24 * 3600);
					$arrDates           = explode('-', $rowNew->event_date);
					if ($arrDates[0] == $year && $arrDates[1] == $month)
					{
						$rowEvents[] = $rowNew;
					}
					$startTime += 24 * 3600;
				}
			}

			return $rowEvents;
		}
		else
		{
			return $db->loadObjectList();
		}
	}

	function getEventsByWeek()
	{
		$config      = EventbookingHelper::getConfig();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$db          = JFactory::getDbo();
		$query       = $db->getQuery(true);

		// get first day of week of today
		$day               = 0;
		$week_number       = date('W', time());
		$year              = date('Y', time());
		$date              = date('Y-m-d', strtotime($year . "W" . $week_number . $day));
		$first_day_of_week = JRequest::getVar('date', $date);
		$last_day_of_week  = date('Y-m-d', strtotime("+6 day", strtotime($first_day_of_week)));
		$startDate         = $first_day_of_week . " 00:00:00";
		$endDate           = $last_day_of_week . " 23:59:59";


		$query->select('a.*')
			->select('a.title' . $fieldSuffix . ' AS title')
			->select('b.name AS location_name')
			->from('#__eb_events AS a')
			->leftJoin('#__eb_locations AS b ON b.id = a.location_id')
			->where('a.published = 1')
			->where("(a.event_date BETWEEN '$startDate' AND '$endDate')")
			->where('a.access IN (' . implode(',', JFactory::getUser()->getAuthorisedViewLevels()) . ')');
		if ($config->hide_past_events)
		{
			$query->where('DATE(event_date) >=' . JHtml::_('date', 'Now', 'Y-m-d'));
		}
		$query->order('a.event_date ASC, a.ordering ASC');

		$db->setQuery($query);
		$events   = $db->loadObjectList();
		$eventArr = array();
		foreach ($events as $event)
		{
			$eventArr[date('w', strtotime($event->event_date))][] = $event;
		}

		return $eventArr;
	}

	/**
	 * list events for day
	 *
	 */
	function getEventsByDaily()
	{
		$config      = EventbookingHelper::getConfig();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$db          = JFactory::getDbo();
		$query       = $db->getQuery(true);
		$day         = JRequest::getVar('day', JHtml::_('date', 'Now', 'Y-m-d'));
		$startDate   = $day . " 00:00:00";
		$endDate     = $day . " 23:59:59";
		$query->select('a.*')
			->select('a.title' . $fieldSuffix . ' AS title')
			->select('b.name AS location_name')
			->from('#__eb_events AS a')
			->leftJoin('#__eb_locations AS b ON b.id = a.location_id')
			->where('a.published = 1')
			->where("(a.event_date BETWEEN '$startDate' AND '$endDate')")
			->where('a.access IN (' . implode(',', JFactory::getUser()->getAuthorisedViewLevels()) . ')');
		if ($config->hide_past_events)
		{
			$query->where('DATE(event_date) >=' . JHtml::_('date', 'Now', 'Y-m-d'));
		}
		$query->order('a.event_date ASC, a.ordering ASC');

		$db->setQuery($query);

		return $db->loadObjectList();
	}
}
