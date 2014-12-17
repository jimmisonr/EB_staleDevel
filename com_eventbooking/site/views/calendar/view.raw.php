<?php
/**
 * @version        	1.6.9
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2014 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventbookingViewCalendar extends JViewLegacy
{

	function display($tpl = null)
	{
		$Itemid = JRequest::getInt('Itemid', 0);
		$month = JRequest::getInt('month');
		$year = JRequest::getInt('year');
		if (!$month)
		{
			$month = JRequest::getInt('default_month', 0);
			if ($month)
			{
				JRequest::setVar('month', $month);
			}
		}
		if (!$year)
		{
			$year = JRequest::getInt('default_year', 0);
			if ($year)
			{
				JRequest::setVar('year', $year);
			}
		}
		$model = $this->getModel('Calendar');
		list ($year, $month, $day) = $model->getYMD();
		$rows = $model->getEventsByMonth($year, $month);
		$this->data = EventbookingHelperData::getCalendarData($rows, $year, $month);
		$this->month = $month;
		$this->year = $year;
		
		$days = array();
		$startday = EventBookingHelper::getConfigValue('calendar_start_date');
		for ($i = 0; $i < 7; $i++)
		{
			$days[$i] = $this->_getDayName(($i + $startday) % 7, true);
		}
		
		$listMonth = array(
			JText::_('EB_JAN'), 
			JText::_('EB_FEB'), 
			JText::_('EB_MARCH'), 
			JText::_('EB_APR'), 
			JText::_('EB_MAY'), 
			JText::_('EB_JUNE'), 
			JText::_('EB_JUL'), 
			JText::_('EB_AUG'), 
			JText::_('EB_SEP'), 
			JText::_('EB_OCT'), 
			JText::_('EB_NOV'), 
			JText::_('EB_DEC'));
		
		$this->days = $days;
		$this->Itemid = $Itemid;
		$this->listMonth = $listMonth;
		
		parent::display($tpl);
	}

	public static function _getDayName($daynb, $colored = false)
	{
		$i = $daynb % 7; // modulo 7
		if ($i == '0' && $colored === true)
		{
			$dayname = '<span class="sunday">' . self::getDayName($i) . '</span>';
		}
		else if ($i == '6' && $colored === true)
		{
			$dayname = '<span class="saturday">' . self::getDayName($i) . '</span>';
		}
		else
		{
			$dayname = self::getDayName($i);
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
	public static function getDayName($daynb = 0, $array = 0)
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
}