<?php
/**
 * @version        	1.7.0
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
require dirname(__FILE__) . '/list.php';

/**
 * EventBooking Component Category Model
 *
 * @package		Joomla
 * @subpackage	EventBooking
 */
class EventBookingModelCategory extends EventBookingModelList
{

	function __construct($config = array())
	{
		parent::__construct($config);
	}
	################################################################
	#Functions used for calendar view
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
			$datenow = JFactory::getDate("+0 seconds");
			list ($year, $month, $day) = explode('-', $datenow->format('Y-m-d'));
			$year = min(2100, abs(intval(JRequest::getVar('year', $year))));
			$month = min(99, abs(intval(JRequest::getVar('month', $month))));
			$day = min(3650, abs(intval(JRequest::getVar('day', $day))));
			if ($day <= '9')
			{
				$day = '0' . $day;
			}
			if ($month <= '9')
			{
				$month = '0' . $month;
			}
			$data = array();
			$data[] = $year;
			$data[] = $month;
			$data[] = $day;
		}
		return $data;
	}

	function getEventsByMonth($year, $month)
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$hidePastEvents = EventbookingHelper::getConfigValue('hide_past_events');
		$showMultipleDayEventInCalendar = EventbookingHelper::getConfigValue('show_multiple_days_event_in_calendar');
		$categoryId = JRequest::getInt('id', 0);
		$startdate = mktime(0, 0, 0, $month, 1, $year);
		$enddate = mktime(23, 59, 59, $month, date('t', $startdate), $year);
		$startdate = date('Y-m-d', $startdate) . " 00:00:00";
		$enddate = date('Y-m-d', $enddate) . " 23:59:59";
		
		$where = array();
		
		$where[] = 'a.`published` = 1';
		if ($categoryId)
		{
			$where[] = "a.id IN (SELECT event_id FROM #__eb_event_categories WHERE category_id=$categoryId )";
		}
		if ($showMultipleDayEventInCalendar)
		{
			$where[] = "((`event_date` BETWEEN '$startdate' AND '$enddate') OR (MONTH(event_end_date) = $month AND YEAR(event_end_date) = $year ))";
		}
		else
		{
			$where[] = "`event_date` BETWEEN '$startdate' AND '$enddate'";
		}
		
		if ($hidePastEvents)
		{
			$currentDate = JHtml::_('date', 'Now', 'Y-m-d');
			$where[] = 'DATE(event_date) >= "' . $currentDate . '"';
		}
		$where[] = "a.access IN (" . implode(',', $user->getAuthorisedViewLevels()) . ")";
		if ($app->getLanguageFilter())
		{
			$where[] = 'a.language IN (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')';
		}
		$query = 'SELECT a.*, SUM(b.number_registrants) AS total_registrants FROM #__eb_events AS a ' . 'LEFT JOIN #__eb_registrants AS b ' .
			 'ON (a.id = b.event_id ) AND b.group_id = 0 AND (b.published=1 OR (b.payment_method LIKE "os_offline%" AND b.published NOT IN (2,3))) ' . 'WHERE ' .
			 implode(' AND ', $where) . ' GROUP BY a.id ' . ' ORDER BY a.event_date ASC, a.ordering ASC';
		$db->setQuery($query);
		
		if ($showMultipleDayEventInCalendar)
		{
			$rows = $db->loadObjectList();
			$rowEvents = array();
			for ($i = 0, $n = count($rows); $i < $n; $i++)
			{
				$row = $rows[$i];
				$arrDates = explode('-', $row->event_date);
				if ($arrDates[0] == $year && $arrDates[1] == $month)
				{
					$rowEvents[] = $row;
				}
				$startDateParts = explode(' ', $row->event_date);
				$startTime = strtotime($startDateParts[0]);
				$startDateTime = strtotime($row->event_date);
				$endDateParts = explode(' ', $row->event_end_date);
				$endTime = strtotime($endDateParts[0]);
				$count = 0;
				while ($startTime < $endTime)
				{
					$count++;
					$rowNew = clone ($row);
					$rowNew->event_date = date('Y-m-d H:i:s', $startDateTime + $count * 24 * 3600);
					$arrDates = explode('-', $rowNew->event_date);
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
} 