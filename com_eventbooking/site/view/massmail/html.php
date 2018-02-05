<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2018 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

class EventbookingViewMassmailHtml extends RADViewHtml
{
	public function display()
	{
		// Only users with registrants management permission can access to massmail function
		$user = JFactory::getUser();

		if (!$user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'))
		{
			$app = JFactory::getApplication();

			if ($user->get('guest'))
			{
				$active = $app->getMenu()->getActive();
				$option = isset($active->query['option']) ? $active->query['option'] : '';
				$view   = isset($active->query['view']) ? $active->query['view'] : '';

				if ($option == 'com_eventbooking' && $view == 'massmail')
				{
					$returnUrl = 'index.php?Itemid=' . $active->id;
				}
				else
				{
					$returnUrl = JUri::getInstance()->toString();
				}

				$redirectUrl = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode($returnUrl), false);
				$app->redirect($redirectUrl);
			}
			else
			{
				$app->redirect(JUri::root(), JText::_('NOT_AUTHORIZED'));
			}
		}


		$config      = EventbookingHelper::getConfig();
		$db          = JFactory::getDbo();
		$query       = $db->getQuery(true);
		$fieldSuffix = EventbookingHelper::getFieldSuffix();

		if ($fieldSuffix)
		{
			$query->select($db->quoteName(['id', 'title' . $fieldSuffix, 'event_date'], [null, 'title', null]));
		}
		else
		{
			$query->select($db->quoteName(['id', 'title', 'event_date']));
		}

		$query->from('#__eb_events')
			->where('published = 1')
			->order($config->sort_events_dropdown);

		if ($config->hide_past_events_from_events_dropdown)
		{
			$currentDate = $db->quote(JHtml::_('date', 'Now', 'Y-m-d'));
			$query->where('(DATE(event_date) >= ' . $currentDate . ' OR DATE(event_end_date) >= ' . $currentDate . ')');
		}

		if ($config->only_show_registrants_of_event_owner)
		{
			$query->where('created_by = ' . (int) $user->id);
		}

		$db->setQuery($query);

		$lists['event_id'] = EventbookingHelperHtml::getEventsDropdown($db->loadObjectList(), 'event_id', ' class="input-xlarge" ');

		$options   = array();
		$options[] = JHtml::_('select.option', -1, JText::_('EB_DEFAULT_STATUS'));
		$options[] = JHtml::_('select.option', 0, JText::_('EB_PENDING'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_PAID'));

		if ($config->activate_waitinglist_feature)
		{
			$options[] = JHtml::_('select.option', 3, JText::_('EB_WAITING_LIST'));
		}

		$options[] = JHtml::_('select.option', 2, JText::_('EB_CANCELLED'));

		$lists['published'] = JHtml::_('select.genericlist', $options, 'published', ' class="input-xlarge" ', 'value', 'text', $this->input->getInt('published', -1));

		$options   = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_NO'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_YES'));

		$lists['send_to_group_billing'] = JHtml::_('select.genericlist', $options, 'send_to_group_billing', 'class="input-xlarge"', 'value', 'text', $this->input->getInt('send_to_group_billing', 1));
		$lists['send_to_group_members'] = JHtml::_('select.genericlist', $options, 'send_to_group_members', 'class="input-xlarge"', 'value', 'text', $this->input->getInt('send_to_group_members', 1));

		$this->lists = $lists;

		parent::display();
	}
}
