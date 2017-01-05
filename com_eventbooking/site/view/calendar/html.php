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

class EventbookingViewCalendarHtml extends RADViewHtml
{
	public function display()
	{
		$app    = JFactory::getApplication();
		$active = $app->getMenu()->getActive();
		$params = EventbookingHelper::getViewParams($active, array('calendar'));

		$config           = EventbookingHelper::getConfig();
		$showCalendarMenu = $config->activate_weekly_calendar_view || $config->activate_daily_calendar_view;

		$this->currentDateData  = EventbookingModelCalendar::getCurrentDateData();
		$this->showCalendarMenu = $showCalendarMenu;
		$this->config           = $config;

		#Support Weekly and Daily
		$layout = $this->getLayout();

		if ($layout == 'weekly')
		{
			$this->displayWeeklyView();

			return;
		}
		elseif ($layout == 'daily')
		{
			$this->displayDailyView();

			return;
		}

		$model = $this->getModel();
		$rows  = $model->getData();

		$state = $model->getState();
		$year  = $state->year;
		$month = $state->month;

		$this->data  = EventbookingHelperData::getCalendarData($rows, $year, $month);
		$this->month = $month;
		$this->year  = $year;

		$listMonth = array(
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
			JText::_('EB_DEC'),);
		$options   = array();

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

		EventbookingHelperHtml::prepareDocument($params);

		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$message     = EventbookingHelper::getMessages();

		if (strlen($message->{'intro_text' . $fieldSuffix}))
		{
			$introText = $message->{'intro_text' . $fieldSuffix};
		}
		else
		{
			$introText = $message->intro_text;
		}

		if ($active && isset($active->query['layout']) && $active->query['layout'] == 'weekly')
		{
			$layoutQuery = '&layout=default';
		}
		else
		{
			$layoutQuery = '';
		}

		$this->listMonth   = $listMonth;
		$this->params      = $params;
		$this->introText   = $introText;
		$this->layoutQuery = $layoutQuery;

		parent::display();
	}

	/**
	 * Display weekly events
	 */
	protected function displayWeeklyView()
	{
		$config = EventbookingHelper::getConfig();
		$model  = $this->getModel();
		$rows   = $model->getEventsByWeek();

		if ($config->process_plugin)
		{
			foreach ($rows as $row)
			{
				$row->short_description = JHtml::_('content.prepare', $row->short_description);
			}
		}

		$this->events            = $rows;
		$this->first_day_of_week = $model->getState('date');

		parent::display();
	}

	/**
	 * Display daily events
	 */
	protected function displayDailyView()
	{
		EventbookingHelperJquery::colorbox('eb-colorbox-addlocation');
		$config = EventbookingHelper::getConfig();
		$model  = $this->getModel();
		$rows   = $model->getEventsByDaily();
		if ($config->process_plugin)
		{
			foreach ($rows as $row)
			{
				$row->short_description = JHtml::_('content.prepare', $row->short_description);
			}
		}
		$this->events = $rows;
		$this->day    = $model->getState('day');

		parent::display();
	}
}
