<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2018 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;

class EventbookingViewHistoryHtml extends RADViewHtml
{
	public function display()
	{
		$user = JFactory::getUser();

		if (!$user->id)
		{
			$app    = JFactory::getApplication();
			$active = $app->getMenu()->getActive();
			$option = isset($active->query['option']) ? $active->query['option'] : '';
			$view   = isset($active->query['view']) ? $active->query['view'] : '';

			if ($option == 'com_eventbooking' && $view == 'history')
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

		/* @var EventbookingModelHistory $model */
		$model              = $this->getModel();
		$state              = $model->getState();
		$config             = EventbookingHelper::getConfig();
		$lists['search']    = StringHelper::strtolower($state->filter_search);
		$lists['order_Dir'] = $state->filter_order_Dir;
		$lists['order']     = $state->filter_order;

		//Get list of events
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id, a.title, a.event_date')
			->from('#__eb_events AS a')
			->where('a.id IN (SELECT event_id FROM #__eb_registrants AS b WHERE (b.user_id = ' . $user->id . ' OR b.email = ' . $db->quote($user->email) . ') AND (b.published >= 1 OR b.payment_method LIKE "os_offline%"))')
			->order('a.title');
		$db->setQuery($query);
		$rows                     = $db->loadObjectList();
		$lists['filter_event_id'] = EventbookingHelperHtml::getEventsDropdown($rows, 'filter_event_id', 'class="input-xlarge" onchange="submit();"', $state->filter_event_id);

		$items = $model->getData();

		$showDueAmountColumn = false;

		$numberPaymentMethods = EventbookingHelper::getNumberNoneOfflinePaymentMethods();

		if ($numberPaymentMethods > 0)
		{
			foreach ($items as $item)
			{
				if ($item->payment_status != 1)
				{
					$showDueAmountColumn = true;
					break;
				}
			}
		}

		// Check to see whether we should show download certificate feature
		$showDownloadCertificate = false;
		$showDownloadTicket      = false;

		foreach ($items as $item)
		{
			$item->show_download_certificate = false;

			if ($item->published == 1 && $item->activate_certificate_feature == 1
				&& $item->event_end_date_minutes >= 0
				&& (!$config->download_certificate_if_checked_in || $item->checked_in)
			)
			{
				$showDownloadCertificate         = true;
				$item->show_download_certificate = true;
			}

			if ($item->ticket_number && $item->payment_status == 1)
			{
				$showDownloadTicket = true;
			}
		}

		// Select none offline payment plugins
		$query->clear()
			->select('id')
			->from('#__eb_payment_plugins')
			->where('published = 1')
			->where('name NOT LIKE "os_offline%"');
		$db->setQuery($query);

		$this->state                   = $state;
		$this->lists                   = $lists;
		$this->items                   = $items;
		$this->pagination              = $model->getPagination();
		$this->config                  = $config;
		$this->showDueAmountColumn     = $showDueAmountColumn;
		$this->showDownloadCertificate = $showDownloadCertificate;
		$this->showDownloadTicket      = $showDownloadTicket;
		$this->onlinePaymentPlugins    = $db->loadColumn();

		parent::display();
	}
}
