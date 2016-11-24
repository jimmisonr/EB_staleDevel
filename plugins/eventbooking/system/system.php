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

			if (!$config->get('multiple_booking') && $config->get('activate_tickets_pdf'))
			{
				$this->generateTicketNumbersForRegistration($row);
			}

			// Update coupon usage
			if ($row->coupon_id)
			{
				$this->updateCouponUsage($row);
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

		// Update coupon usage, increase by 1
		if ($row->coupon_id && !$row->coupon_usage_calculated)
		{
			$this->updateCouponUsage($row);
		}

		if ($config->multiple_booking)
		{
			$this->updateCartRegistrationRecordsStatus($row, $config);
		}

		if ($config->unpublish_event_when_full && strpos($row->payment_method, 'os_offline') === false)
		{
			$this->processUnpublishEvent($row->event_id);
		}

		if (!$config->get('multiple_booking') && $config->get('activate_tickets_pdf') && !$row->ticket_code)
		{
			$this->generateTicketNumbersForRegistration($row);
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
	 * Update coupon usage, increase number usage by 1
	 *
	 * @param EventbookingTableRegistrant $row
	 */
	private function updateCouponUsage($row)
	{
		if ($row->cart_id > 0)
		{
			return;
		}

		$row->coupon_usage_calculated = 1;
		$row->store();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->update('#__eb_coupons')
			->set('used = used + 1')
			->where('id = ' . (int) $row->coupon_id);
		$db->setQuery($query);
		$db->execute();
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
			->set('transaction_id = ' . $db->quote($row->transaction_id))
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

	/**
	 * Generate Ticket Number, Ticket Code for registration
	 *
	 * @param EventbookingTableRegistrant $row
	 */
	private function generateTicketNumbersForRegistration($row)
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/table/event.php';

		$rowEvent = JTable::getInstance('event', 'EventbookingTable');
		$rowEvent->load($row->event_id);

		if ($rowEvent->activate_tickets_pdf)
		{
			jimport('joomla.user.helper');

			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);

			// Get the next ticket number
			$query->select('MAX(ticket_number)')
				->from('#__eb_registrants')
				->where('event_id = ' . $row->event_id);
			$db->setQuery($query);
			$ticketNumber = (int) $db->loadResult() + 1;
			$ticketNumber = max($ticketNumber, $rowEvent->ticket_start_number);


			$ticketCode = '';

			if ($row->is_group_billing)
			{
				$query->clear()
					->select('id')
					->from('#__eb_registrants')
					->where('group_id = ' . $row->id);
				$db->setQuery($query);
				$memberIds = $db->loadColumn();
			}
			else
			{
				$memberIds = array($row->id);
			}

			foreach ($memberIds as $memberId)
			{
				$ticketCode = '';

				while (true)
				{
					$ticketCode = md5(JUserHelper::genRandomPassword(16));
					$query->clear()
						->select('COUNT(*)')
						->from('#__eb_registrants')
						->where('ticket_code = ' . $db->quote($ticketCode));
					$db->setQuery($query);
					$total = $db->loadResult();

					if (!$total)
					{
						break;
					}
				}

				if ($row->is_group_billing)
				{
					$query->clear()
						->update('#__eb_registrants')
						->set('ticket_code = ' . $db->quote($ticketCode))
						->set('ticket_number = ' . $db->quote($ticketNumber))
						->where('id = ' . $memberId);
					$db->setQuery($query);
					$db->execute();
					$ticketNumber++;
				}
			}


			// Store Ticket Code and Ticket Number, use for next step
			if (!$row->is_group_billing)
			{
				$row->ticket_number = $ticketNumber;
			}

			$row->ticket_code = $ticketCode;

			$row->store();
		}
	}
}
