<?php
/**
 * @version		1.6.1
 * @package		Joomla
 * @subpackage	Event Booking
 * @author  Tuan Pham Ngoc
 * @copyright	Copyright (C) 2010 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die ;

class modMiniCalendarHelper
{
/**
	 * Get JDate object of current time
	 *
	 * @return object JDate
	 */
	public static function _getNow() 
	{		
		static $datenow = null;
		if (!isset($datenow)) 
		{
			$datenow =& JFactory::getDate("+0 seconds");
		}
		return $datenow;
	}
	/**
	 * Get array Year, Month, Day from current Request, fallback to current date
	 *
	 * @return array
	 */
 	public static function _getYMD()
	{
		static $data;
		if (!isset($data))
		{
			list($year, $month, $day) = explode('-', JHtml::_('date', 'Now', 'Y-m-d'));
			$year	= min(2100,abs(intval(JRequest::getVar('year',	$year))));
			$month	= min(99,abs(intval(JRequest::getVar('month',	$month))));
			$day	= min(3650,abs(intval(JRequest::getVar('day',	$day))));
			if( $day <= '9' )
			{
				$day = '0' . $day;
			}
			if( $month <= '9') 
			{
				$month = '0' . $month;
			}
			$data = array();
			$data[]=$year;
			$data[]=$month;
			$data[]=$day;
		}
		return $data;
	}
		

	public static function _listIcalEventsByMonth( $year, $month){
		$app = JFactory::getApplication() ;
		$db = JFactory::getDbo();
		$user = & JFactory::getUser() ;
		$startdate 	= mktime( 0, 0, 0,  $month,  1, $year );
		$enddate 	= mktime( 23, 59, 59,  $month, date( 't', $startdate), $year );
		$startdate = date('Y-m-d',$startdate)." 00:00:00";
		$enddate = date('Y-m-d',$enddate)." 23:59:59";
		if ($app->getLanguageFilter()) 
		{			$extraWhere = ' AND `language` IN (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')';		} else 
		{			$extraWhere = '' ;		}
		$query = " SELECT * FROM #__eb_events "			." WHERE (`published` = 1) AND (`event_date` BETWEEN '$startdate' AND '$enddate') AND (`access`=0 OR `access` IN (".implode(',', $user->getAuthorisedViewLevels())."))  "
			. $extraWhere			." ORDER BY event_date ASC, ordering ASC"		;		
		$db->setQuery($query);
		return $db->loadObjectList();
	}
	
	
	/**
	 * returns name of the day longversion
	 * @param	daynb		int		# of day
	 * @param	colored		bool	color sunday	[ new mic, because inside tooltips a color forces an error! ]
	 **/
	public static function _getDayName( $daynb, $colored = false )
	{

		$i = $daynb % 7; // modulo 7
		if( $i == '0' && $colored === true)
		{
			$dayname = '<span class="sunday">' . modMiniCalendarHelper::getDayName($i) . '</span>';
		}
		else if( $i == '6' && $colored === true)
		{
			$dayname = '<span class="saturday">' . modMiniCalendarHelper::getDayName($i) . '</span>';
		}
		else 
		{
			$dayname = modMiniCalendarHelper::getDayName($i);
		}
		return $dayname;
	}
	
	/**
	 * Returns name of the day longversion
	 * 
	 * @static
	 * @param	int		daynb	# of day
	 * @param	int		array, 0 return single day, 1 return array of all days
	 * @return	mixed	localised short day letter or array of names
	 **/
	public static function getDayName( $daynb=0, $array=0)
	{
		static $days = null;
		if ($days === null) 
		{
			$days = array();
			$days[0] = JText::_('EB_MINICAL_SUNDAY');
			$days[1] = JText::_('EB_MINICAL_MONDAY');
			$days[2] = JText::_('EB_MINICAL_TUESDAY');
			$days[3] = JText::_('EB_MINICAL_WEDNESDAY');
			$days[4] = JText::_('EB_MINICAL_THURSDAY');
			$days[5] = JText::_('EB_MINICAL_FRIDAY');
			$days[6] = JText::_('EB_MINICAL_SATURDAY');
		}
		if ($array == 1) 
		{
			return $days;
		}
		$i = $daynb % 7; //
		return $days[$i];
	}
	

	/**
	 * Gets calendar data for use in main calendar and module
	 *
	 * @param int $year
	 * @param int $month
	 * @param int $day
	 * @param boolean $short - use true for module which only requires knowledge of if dat has an event
	 * @param boolean $veryshort - use true for module which only requires dates and nothing about events
	 * @return array - calendar data array
	 */
	public static function _getCalendarData( $year, $month, $day)
	{				
		$rows = modMiniCalendarHelper::_listIcalEventsByMonth($year, $month);	
		$rowcount = count( $rows );		
		$data = array();
		$data['year'] = $year;
		$data['month'] = $month;
		$month = intval($month);
		if( $month <= '9' ) {
			$month = '0' . $month;
		}
		$data['startday'] = $startday =  EventBookingHelper::getConfigValue('calendar_start_date');		
		// get days in week
		$data["daynames"] = array();
		for( $i = 0; $i < 7; $i++ ) {
			$data["daynames"][$i] = modMiniCalendarHelper::_getDayName(($i + $startday) % 7, true );
		}				
		$data["dates"]=array();		
		//Start days
		$start = (( date( 'w', mktime( 0, 0, 0, $month, 1, $year )) - $startday + 7 ) % 7 );		
		// previous month
		$priorMonth = $month-1;
		$priorYear = $year;		
		if ($priorMonth<=0) {
			$priorMonth+=12;
			$priorYear-=1;
		}		
		$dayCount=0;
		for( $a = $start; $a > 0; $a-- ){
			$data["dates"][$dayCount]=array();
			$data["dates"][$dayCount]["monthType"]="prior";
			$data["dates"][$dayCount]["month"]=$priorMonth;
			$data["dates"][$dayCount]["year"]=$priorYear;
			$data["dates"][$dayCount]['countDisplay']=0;
			$dayCount++;
		}
		sort($data["dates"]);
		//Current month
		$end = date( 't', mktime( 0, 0, 0,( $month + 1 ), 0, $year ));
		for( $d = 1; $d <= $end; $d++ ){
			$data["dates"][$dayCount]=array();
			// utility field used to keep track of events displayed in a day!
			$data["dates"][$dayCount]['countDisplay']=0;
			$data["dates"][$dayCount]["monthType"]="current";
			$data["dates"][$dayCount]["month"]=$month;
			$data["dates"][$dayCount]["year"]=$year;		
						
			$t_datenow = modMiniCalendarHelper::_getNow();
			$now_adjusted = $t_datenow->toUnix(true);
			if( $month == strftime( '%m', $now_adjusted)
			&& $year == strftime( '%Y', $now_adjusted)
			&& $d == strftime( '%d', $now_adjusted)) {
				$data["dates"][$dayCount]["today"]=true;
			}else{
				$data["dates"][$dayCount]["today"]=false;
			}
			$data["dates"][$dayCount]['d']=$d;			
			$link = JRoute::_( 'index.php?option=com_eventbooking&task=day.listevents&year='
			. $year . '&month=' . $month . '&Itemid=' . JRequest::getVar('Itemid'));
			$data["dates"][$dayCount]["link"]=$link;
			$data["dates"][$dayCount]['events'] = array();
			if( $rowcount > 0 ){
				foreach ($rows as $row) {
						$date_of_event = explode('-',$row->event_date);
						$date_of_event = (int)$date_of_event[2];						
						if ($d == $date_of_event ){							
							$i=count($data["dates"][$dayCount]['events']);
							$data["dates"][$dayCount]['events'][$i] = $row;
						}					
				}
			}
			
			$dayCount++;
		}		
		// followmonth
		$days 	= ( 7 - date( 'w', mktime( 0, 0, 0, $month + 1, 1, $year )) + $startday ) %7;
		$d		= 1;
		$followMonth = $month+1;
		$followYear = $year;
		if ($followMonth>12) {
			$followMonth-=12;
			$followYear+=1;
		}
		$data["followingMonth"]=array();
		for( $d = 1; $d <= $days; $d++ ) {
			$data["dates"][$dayCount]=array();
			$data["dates"][$dayCount]["monthType"]="following";
			$data["dates"][$dayCount]["month"]=$followMonth;
			$data["dates"][$dayCount]["year"]=$followYear;
			$data["dates"][$dayCount]['countDisplay']=0;
			$dayCount++;
		}
		return $data;		
	}
}
