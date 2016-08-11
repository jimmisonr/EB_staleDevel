<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die;

class plgEventbookingSystem extends JPlugin
{

	/**
	 * This method is run after registration record is stored into database
	 *
	 * @param EventbookingTableRegistrant $row
	 */
	public function onAfterStoreRegistrant($row)
	{
		if (strpos($row->payment_method, 'os_offline') !== false)
		{
			$config = EventbookingHelper::getConfig();

			if ($config->activate_invoice_feature)
			{
				$this->processInvoiceNumber($row);
			}

			if ($config->unpublish_event_when_full)
			{
				$this->processUnpublishEvent($row->event_id);
			}
		}
	}

	/**
	 * This method is run after when the status of the registration changed from Pending -> Active
	 *
	 * @param EventbookingTableRegistrant $row
	 */
	public function onAfterPaymentSuccess($row)
	{
		$config = EventbookingHelper::getConfig();

		if ($config->activate_invoice_feature && !$row->invoice_number)
		{
			$this->processInvoiceNumber($row);
		}

		if ($config->multiple_booking)
		{
			$this->updateCartRegistrationRecordsStatus($row, $config);
		}

		if ($config->unpublish_event_when_full && strpos($row->payment_method, 'os_offline') === false)
		{
			$this->processUnpublishEvent($row->event_id);
		}
	}

	/***
	 * Generate invoice number of the registration record
	 *
	 * @param EventbookingTableRegistrant $row
	 */
	private function processInvoiceNumber($row)
	{

		if (EventbookingHelper::needInvoice($row))
		{
			$invoiceNumber       = EventbookingHelper::getInvoiceNumber();
			$row->invoice_number = $invoiceNumber;
			$row->store();
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->update('#__eb_registrants')
				->set('invoice_number=' . $db->quote($invoiceNumber))
				->where('id=' . $row->id . ' OR cart_id=' . $row->id . ' OR group_id=' . $row->id);
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Unpublish event when it is full
	 *
	 * @param int $eventId
	 */
	private function processUnpublishEvent($eventId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('event_capacity')
			->from('#__eb_events')
			->where('id = ' . $eventId);
		$db->setQuery($query);
		$capacity = (int) $db->loadResult();
		if ($capacity > 0)
		{
			$query->clear()
				->select('COUNT(*)')
				->from('#__eb_registrants AS b')
				->where('event_id = ' . (int) $eventId)
				->where('b.group_id = 0')
				->where('(b.published = 1 OR (b.payment_method LIKE "os_offline%" AND b.published NOT IN (2,3)))');
			$db->setQuery($query);
			$totalRegistrants = (int) $db->loadResult();
			if ($totalRegistrants >= $capacity)
			{
				// Un-publish the event
				$query->clear()
					->update('#__eb_events')
					->set('published = 0')
					->where('id = ' . (int) $eventId);
				$db->setQuery($query);
				$db->execute();
			}
		}
	}

	/**
	 * Mark all registration records in cart paid when the payment completed
	 *
	 * @param EventbookingTableRegistrant $row
	 * @param RADConfig                   $config
	 */
	private function updateCartRegistrationRecordsStatus($row, $config)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__eb_registrants')
			->set('published = 1')
			->set('payment_date = NOW()')
			->where('cart_id = ' . (int) $row->id);
		$db->setQuery($query);
		$db->execute();

		if ($config->collect_member_information_in_cart)
		{
			$groupBillingQuery = $db->getQuery(true);
			$groupBillingQuery->select('id')
				->from('#__eb_registrants')
				->where('id = ' . $row->id . ' OR cart_id = ' . $row->id);
			$db->setQuery($groupBillingQuery);
			$billingRecordIds = $db->loadColumn();

			$query->clear('where')
				->where('group_id IN (' . implode(',', $billingRecordIds) . ')');
			$db->setQuery($query);
			$db->execute();
		}
	}
}
