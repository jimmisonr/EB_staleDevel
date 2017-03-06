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

use Joomla\String\StringHelper;

class EventbookingViewHistoryHtml extends RADViewHtml
{
	public function display()
	{
		if (!JFactory::getUser()->id)
		{
			$redirectUrl = JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode(JUri::getInstance()->toString()));
			JFactory::getApplication()->redirect($redirectUrl);
		}

		$model              = $this->getModel();
		$state              = $model->getState();
		$config             = EventbookingHelper::getConfig();
		$lists['search']    = StringHelper::strtolower($state->filter_search);
		$lists['order_Dir'] = $state->filter_order_Dir;
		$lists['order']     = $state->filter_order;

		//Get list of events
		$rows      = EventbookingHelperDatabase::getAllEvents();
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

		$lists['filter_event_id'] = JHtml::_('select.genericlist', $options, 'filter_event_id', 'class="input-xlarge" onchange="submit();"', 'id', 'title',
			$state->filter_event_id);

		$items = $model->getData();

		$showDueAmountColumn = false;

		$numberPaymentMethods = EventbookingHelper::getNumberNoneOfflinePaymentMethods();

		if ($numberPaymentMethods > 0)
		{
			foreach ($items as $item)
			{
				if ($item->payment_status == 0)
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

			if ($item->ticket_number)
			{
				$showDownloadTicket = true;
			}
		}

		$this->state                   = $state;
		$this->lists                   = $lists;
		$this->items                   = $items;
		$this->pagination              = $model->getPagination();
		$this->config                  = $config;
		$this->showDueAmountColumn     = $showDueAmountColumn;
		$this->showDownloadCertificate = $showDownloadCertificate;
		$this->showDownloadTicket      = $showDownloadTicket;

		parent::display();
	}
}
