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
		$fieldSuffix                    = EventbookingHelper::getFieldSuffix();
		$hidePastEvents                 = EventbookingHelper::getConfigValue('hide_past_events');
		$showMultipleDayEventInCalendar = EventbookingHelper::getConfigValue('show_multiple_days_event_in_calendar');
		$db                             = JFactory::getDbo();
		$user                           = JFactory::getUser();
		$startDate                      = mktime(0, 0, 0, $month, 1, $year);
		$endDate                        = mktime(23, 59, 59, $month, date('t', $startDate), $year);
		$startDate                      = date('Y-m-d', $startDate) . " 00:00:00";
		$endDate                        = date('Y-m-d', $endDate) . " 23:59:59";
		$where                          = array();
		$where[]                        = 'a.`published` = 1';
		if ($showMultipleDayEventInCalendar)
		{
			$where[] = "((`event_date` BETWEEN '$startDate' AND '$endDate') OR (MONTH(event_end_date) = $month AND YEAR(event_end_date) = $year ))";
		}
		else
		{
			$where[] = "`event_date` BETWEEN '$startDate' AND '$endDate'";
		}
		if ($hidePastEvents)
		{
			$currentDate = JHtml::_('date', 'Now', 'Y-m-d');
			$where[]     = 'DATE(event_date) >= "' . $currentDate . '"';
		}
		$where[] = "a.access IN (" . implode(',', $user->getAuthorisedViewLevels()) . ")";
		$query   = 'SELECT a.*,title' . $fieldSuffix . ' AS title, SUM(b.number_registrants) AS total_registrants FROM #__eb_events AS a ' . 'LEFT JOIN #__eb_registrants AS b ' .
			'ON (a.id = b.event_id ) AND b.group_id = 0 AND (b.published=1 OR (b.payment_method LIKE "os_offline%" AND b.published NOT IN (2,3))) ' . 'WHERE ' .
			implode(' AND ', $where) . ' GROUP BY a.id ' . ' ORDER BY a.event_date ASC, a.ordering ASC';
		$db->setQuery($query);

		if ($showMultipleDayEventInCalendar)
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
		$hidePastEvents = EventbookingHelper::getConfigValue('hide_past_events');
		$fieldSuffix    = EventbookingHelper::getFieldSuffix();
		$db             = JFactory::getDbo();
		$user           = JFactory::getUser();
		// get first day of week of today
		$day               = 0;
		$week_number       = date('W', time());
		$year              = date('Y', time());
		$date              = date('Y-m-d', strtotime($year . "W" . $week_number . $day));
		$first_day_of_week = JRequest::getVar('date', $date);
		$last_day_of_week  = date('Y-m-d', strtotime("+6 day", strtotime($first_day_of_week)));
		$startDate         = $first_day_of_week . " 00:00:00";
		$endDate           = $last_day_of_week . " 23:59:59";

		if ($hidePastEvents)
		{
			$query = " SELECT a.*, a.title" . $fieldSuffix . " AS title,b.name AS location_name FROM #__eb_events AS a " . " LEFT JOIN #__eb_locations AS b ON a.location_id = b.id " .
				" WHERE (a.published = 1) AND (a.event_date BETWEEN '$startDate' AND '$endDate') AND DATE(a.event_date) >= '" . JHtml::_('date', 'Now', 'Y-m-d') . "' AND a.access IN(" .
				implode(',', $user->getAuthorisedViewLevels()) . ")  ORDER BY a.event_date ASC, a.ordering ASC";
		}
		else
		{
			$query = " SELECT a.*,a.title" . $fieldSuffix . " AS title, b.name AS location_name FROM #__eb_events AS a " . " LEFT JOIN #__eb_locations AS b ON a.location_id = b.id " .
				" WHERE (a.published = 1) AND (a.event_date BETWEEN '$startDate' AND '$endDate') AND a.access IN (" .
				implode(',', $user->getAuthorisedViewLevels()) . ") ORDER BY a.event_date ASC, a.ordering ASC";
		}

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
		$hidePastEvents = EventbookingHelper::getConfigValue('hide_past_events');
		$fieldSuffix    = EventbookingHelper::getFieldSuffix();
		$db             = JFactory::getDbo();
		$user           = JFactory::getUser();
		$day            = JRequest::getVar('day', date('Y-m-d', time()));
		$startDate      = $day . " 00:00:00";
		$endDate        = $day . " 23:59:59";
		if ($hidePastEvents)
		{
			$query = " SELECT a.*, a.title " . $fieldSuffix . " AS title,b.name AS location_name FROM #__eb_events AS a " . " LEFT JOIN #__eb_locations AS b ON b.id = a.location_id " .
				" WHERE (a.published = 1) AND (a.event_date BETWEEN '$startDate' AND '$endDate') AND DATE(event_date) >= '" . JHtml::_('date', 'Now', 'Y-m-d') . "' AND a.access IN (" .
				implode(',', $user->getAuthorisedViewLevels()) . ") ORDER BY a.event_date ASC, a.ordering ASC";
		}
		else
		{
			$query = " SELECT a.*, a,title" . $fieldSuffix . " AS title ,b.name AS location_name FROM #__eb_events AS a " . " LEFT JOIN #__eb_locations AS b ON b.id = a.location_id " .
				" WHERE (a.published = 1) AND (a.event_date BETWEEN '$startDate' AND '$endDate') AND a.access IN (" .
				implode(',', $user->getAuthorisedViewLevels()) . ") ORDER BY a.event_date ASC, a.ordering ASC";
		}
		$db->setQuery($query);
		$events = $db->loadObjectList();

		return $events;
	}
}

?>