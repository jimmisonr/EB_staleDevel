<?php
/**
 * @version            1.7.3
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

class EventbookingViewCalendar extends JViewLegacy
{

	function display($tpl = null)
	{
		$app      = JFactory::getApplication();
		$document = JFactory::getDocument();
		$active   = $app->getMenu()->getActive();
		$params   = EventbookingHelper::getViewParams($active, array('calendar'));

		$config                 = EventbookingHelper::getConfig();
		$showCalendarMenu       = $config->activate_weekly_calendar_view || $config->activate_daily_calendar_view;
		$this->showCalendarMenu = $showCalendarMenu;
		$this->config           = $config;

		#Support Weekly and Daily
		$layout = $this->getLayout();
		if ($layout == 'weekly')
		{
			$this->_displayWeeklyView($tpl);

			return;
		}
		else if ($layout == 'daily')
		{
			$this->_displayDailyView($tpl);

			return;
		}
		$Itemid = JRequest::getInt('Itemid', 0);
		$month  = JRequest::getInt('month');
		$year   = JRequest::getInt('year');
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
		$rows        = $model->getEventsByMonth($year, $month);
		$this->data  = EventbookingHelperData::getCalendarData($rows, $year, $month);
		$this->month = $month;
		$this->year  = $year;
		$listMonth   = array(
			JText::_('EB_JAN'),
			JText::_('EB_FEB'),
			JText::_('EB_MARCH'),
			JText::_('EB_APR'),
			JText::_('EB_MAY'),
			JText::_('EB_JUNE'),
			JText::_('EB_JULY'),
			JText::_('EB_AUG'),
			JText::_('EB_SEP'),
			JText::_('EB_OCT'),
			JText::_('EB_NOV'),
			JText::_('EB_DEC'));
		$options     = array();
		foreach ($listMonth as $key => $monthName)
		{
			if ($key < 9)
			{
				$value = "0" . ($key + 1);
			}
			else
			{
				$value = $key + 1;
			}
			$options[] = JHtml::_('select.option', $value, $monthName);
		}
		$this->searchMonth = JHtml::_('select.genericlist', $options, 'month', 'class="input-medium" onchange="submit();" ', 'value', 'text', $month);
		$options           = array();
		for ($i = $year - 3; $i < ($year + 5); $i++)
		{
			$options[] = JHtml::_('select.option', $i, $i);
		}
		$this->searchYear = JHtml::_('select.genericlist', $options, 'year', 'class="input-small" onchange="submit();" ', 'value', 'text', $year);

		// Process page meta data
		if ($params->get('page_title'))
		{
			$pageTitle        = $params->get('page_title');
			$siteNamePosition = JFactory::getConfig()->get('sitename_pagetitles');
			if ($siteNamePosition == 0)
			{
				$document->setTitle($pageTitle);
			}
			elseif ($siteNamePosition == 1)
			{
				$document->setTitle($app->get('sitename') . ' - ' . $pageTitle);
			}
			else
			{
				$document->setTitle($pageTitle . ' - ' . $app->get('sitename'));
			}
		}

		if ($params->get('menu-meta_description'))
		{
			$document->setDescription($params->get('menu-meta_description'));
		}

		if ($params->get('menu-meta_keywords'))
		{
			$document->setMetadata('keywords', $params->get('menu-meta_keywords'));
		}

		if ($params->get('robots'))
		{
			$document->setMetadata('robots', $params->get('robots'));
		}

		$this->Itemid    = $Itemid;
		$this->listMonth = $listMonth;
		$this->params    = $params;

		parent::display($tpl);
	}

	/**
	 * display event for weekly
	 *
	 * @param string $tpl
	 */
	function _displayWeeklyView($tpl)
	{
		$this->events            = $this->get('EventsByWeek');
		$day                     = 0;
		$week_number             = date('W', time());
		$year                    = date('Y', time());
		$date                    = date('Y-m-d', strtotime($year . "W" . $week_number . $day));
		$this->first_day_of_week = JRequest::getVar('date', $date);
		$this->Itemid            = JRequest::getInt('Itemid', 0);

		parent::display($tpl);
	}

	/**
	 *
	 * Display Daily layout for event
	 *
	 * @param string $tpl
	 */
	function _displayDailyView($tpl)
	{
		$this->events = $this->get('EventsByDaily');
		$this->day    = JRequest::getVar('day', date('Y-m-d', time()));
		$this->Itemid = JRequest::getInt('Itemid', 0);

		parent::display($tpl);
	}
}