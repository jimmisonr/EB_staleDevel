<?php
/**
 * @version            2.7.1
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

class EventbookingViewRegistrantsHtml extends RADViewList
{

	protected function prepareView()
	{
		parent::prepareView();

		$config = EventbookingHelper::getConfig();
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);

		$rows      = EventbookingHelperDatabase::getAllEvents($config->sort_events_dropdown, $config->hide_past_events_from_events_dropdown);
		$options   = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_SELECT_EVENT'), 'id', 'title');
		if ($config->show_event_date)
		{
			for ($i = 0, $n = count($rows); $i < $n; $i++)
			{
				$row       = $rows[$i];
				$options[] = JHtml::_('select.option', $row->id,
					$row->title . ' (' . JHtml::_('date', $row->event_date, $config->date_format, null) . ')' . '', 'id', 'title');
			}
		}
		else
		{
			$options = array_merge($options, $rows);
		}
		$this->lists['filter_event_id'] = JHtml::_('select.genericlist', $options, 'filter_event_id', ' class="inputbox" onchange="submit();"', 'id', 'title', $this->state->filter_event_id);
		$options                        = array();
		$options[]                      = JHtml::_('select.option', -1, JText::_('EB_REGISTRATION_STATUS'));
		$options[]                      = JHtml::_('select.option', 0, JText::_('EB_PENDING'));
		$options[]                      = JHtml::_('select.option', 1, JText::_('EB_PAID'));
		if ($config->activate_waitinglist_feature)
		{
			$options[] = JHtml::_('select.option', 3, JText::_('EB_WAITING_LIST'));
		}
		$options[] = JHtml::_('select.option', 2, JText::_('EB_CANCELLED'));

		$this->lists['filter_published'] = JHtml::_('select.genericlist', $options, 'filter_published', ' class="inputbox" onchange="submit()" ', 'value', 'text',
			$this->state->filter_published);

		if ($config->activate_checkin_registrants)
		{
			$options                        = array();
			$options[]                      = JHtml::_('select.option', -1, JText::_('EB_CHECKIN_STATUS'));
			$options[]                      = JHtml::_('select.option', 0, JText::_('EB_CHECKED_IN'));
			$options[]                      = JHtml::_('select.option', 1, JText::_('EB_NOT_CHECKED_IN'));
			$this->lists['filter_checked_in'] = JHtml::_('select.genericlist', $options, 'filter_checked_in', ' class="inputbox" onchange="submit()" ', 'value', 'text',
				$this->state->filter_checked_in);
		}

		$query->select('COUNT(*)')
			->from('#__eb_payment_plugins')
			->where('published=1');
		$db->setQuery($query);

		$this->config       = $config;
		$this->totalPlugins = (int) $db->loadResult();
		$this->coreFields   = EventbookingHelper::getPublishedCoreFields();
	}

	/**
	 * Override addToolbar method to add custom csv export function
	 * @see RADViewList::addToolbar()
	 */
	protected function addToolbar()
	{
		parent::addToolbar();

		// Instantiate a new JLayoutFile instance and render the batch button
		$layout = new JLayoutFile('joomla.toolbar.batch');

		$bar = JToolbar::getInstance('toolbar');
		$dhtml = $layout->render(array('title' => JText::_('EB_MASS_MAIL')));
		$bar->appendButton('Custom', $dhtml, 'batch');

		JToolbarHelper::custom('resend_email', 'envelope', 'envelope', 'Resend Email', true);
		JToolbarHelper::custom('export', 'download', 'download', 'Export Registration', false);
	}
}