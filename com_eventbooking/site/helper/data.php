<?php

/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
class EventbookingHelperData
{
	/**
	 * Get day name from given day number
	 *
	 * @param $dayNumber
	 *
	 * @return mixed
	 */
	public static function getDayName($dayNumber)
	{
		static $days;
		if ($days == null)
		{
			$days = array(
				JText::_('EB_SUNDAY'),
				JText::_('EB_MONDAY'),
				JText::_('EB_TUESDAY'),
				JText::_('EB_WEDNESDAY'),
				JText::_('EB_THURSDAY'),
				JText::_('EB_FRIDAY'),
				JText::_('EB_SATURDAY'),
			);
		}
		$i = $dayNumber % 7;

		return $days[$i];
	}

	/**
	 * Get day name from day number in mini calendar
	 *
	 * @param $dayNumber
	 *
	 * @return mixed
	 */
	public static function getDayNameMini($dayNumber)
	{
		static $daysMini = null;
		if ($daysMini === null)
		{
			$daysMini    = array();
			$daysMini[0] = JText::_('EB_MINICAL_SUNDAY');
			$daysMini[1] = JText::_('EB_MINICAL_MONDAY');
			$daysMini[2] = JText::_('EB_MINICAL_TUESDAY');
			$daysMini[3] = JText::_('EB_MINICAL_WEDNESDAY');
			$daysMini[4] = JText::_('EB_MINICAL_THURSDAY');
			$daysMini[5] = JText::_('EB_MINICAL_FRIDAY');
			$daysMini[6] = JText::_('EB_MINICAL_SATURDAY');
		}
		$i = $dayNumber % 7; //
		return $daysMini[$i];
	}

	/**
	 * Get day name HTML code for a given day
	 *
	 * @param int  $dayNumber
	 * @param bool $colored
	 *
	 * @return string
	 */
	public static function getDayNameHtml($dayNumber, $colored = false)
	{
		$i = $dayNumber % 7; // modulo 7
		if ($i == '0' && $colored === true)
		{
			$dayName = '<span class="sunday">' . self::getDayName($i) . '</span>';
		}
		elseif ($i == '6' && $colored === true)
		{
			$dayName = '<span class="saturday">' . self::getDayName($i) . '</span>';
		}
		else
		{
			$dayName = self::getDayName($i);
		}

		return $dayName;
	}

	/**
	 * Get day name HTML code for a given day
	 *
	 * @param int  $dayNumber
	 * @param bool $colored
	 *
	 * @return string
	 */
	public static function getDayNameHtmlMini($dayNumber, $colored = false)
	{
		$i = $dayNumber % 7; // modulo 7
		if ($i == '0' && $colored === true)
		{
			$dayName = '<span class="sunday">' . self::getDayNameMini($i) . '</span>';
		}
		elseif ($i == '6' && $colored === true)
		{
			$dayName = '<span class="saturday">' . self::getDayNameMini($i) . '</span>';
		}
		else
		{
			$dayName = self::getDayNameMini($i);
		}

		return $dayName;
	}

	/**
	 * Build the data used for rendering calendar
	 *
	 * @param $rows
	 * @param $year
	 * @param $month
	 *
	 * @return array
	 */
	public static function getCalendarData($rows, $year, $month, $mini = false)
	{
		$rowCount         = count($rows);
		$data             = array();
		$data['startday'] = $startDay = (int) EventbookingHelper::getConfigValue('calendar_start_date');
		$data['year']     = $year;
		$data['month']    = $month;
		$data["daynames"] = array();
		$data["dates"]    = array();
		$month            = intval($month);
		if ($month <= '9')
		{
			$month = '0' . $month;
		}

		// Get days in week
		for ($i = 0; $i < 7; $i++)
		{
			if ($mini)
			{
				$data["daynames"][$i] = self::getDayNameMini(($i + $startDay) % 7);
			}
			else
			{
				$data["daynames"][$i] = self::getDayName(($i + $startDay) % 7);
			}
		}

		// Today date data
		$date       = new DateTime('now', new DateTimeZone(JFactory::getConfig()->get('offset')));
		$todayDay   = $date->format('d');
		$todayMonth = $date->format('m');
		$todayYear  = $date->format('Y');

		// Start days in month
		$date->setDate($year, $month, 1);
		$start = ($date->format('w') - $startDay + 7) % 7;

		//Previous month
		$preMonth = clone $date;
		$preMonth->modify('-1 month');
		$priorMonth = $preMonth->format('m');
		$priorYear  = $preMonth->format('Y');

		$dayCount = 0;
		for ($a = $start; $a > 0; $a--)
		{
			$data["dates"][$dayCount]                 = array();
			$data["dates"][$dayCount]["monthType"]    = "prior";
			$data["dates"][$dayCount]["month"]        = $priorMonth;
			$data["dates"][$dayCount]["year"]         = $priorYear;
			$data["dates"][$dayCount]['countDisplay'] = 0;
			$dayCount++;
		}
		sort($data["dates"]);

		// Current month
		$end = $date->format('t');
		for ($d = 1; $d <= $end; $d++)
		{
			$data["dates"][$dayCount]                 = array();
			$data["dates"][$dayCount]['countDisplay'] = 0;
			$data["dates"][$dayCount]["monthType"]    = "current";
			$data["dates"][$dayCount]["month"]        = $month;
			$data["dates"][$dayCount]["year"]         = $year;
			if ($month == $todayMonth && $year == $todayYear && $d == $todayDay)
			{
				$data["dates"][$dayCount]["today"] = true;
			}
			else
			{
				$data["dates"][$dayCount]["today"] = false;
			}
			$data["dates"][$dayCount]['d']      = $d;
			$data["dates"][$dayCount]['events'] = array();
			if ($rowCount > 0)
			{
				foreach ($rows as $row)
				{
					$date_of_event = explode('-', $row->event_date);
					$date_of_event = (int) $date_of_event[2];
					if ($d == $date_of_event)
					{
						$i                                      = count($data["dates"][$dayCount]['events']);
						$data["dates"][$dayCount]['events'][$i] = $row;
					}
				}
			}

			$dayCount++;
		}

		// Following month
		$date->modify('+1 month');
		$days        = (7 - $date->format('w') + $startDay) % 7;
		$followMonth = $date->format('m');
		$followYear  = $date->format('Y');

		$data["followingMonth"] = array();
		for ($d = 1; $d <= $days; $d++)
		{
			$data["dates"][$dayCount]                 = array();
			$data["dates"][$dayCount]["monthType"]    = "following";
			$data["dates"][$dayCount]["month"]        = $followMonth;
			$data["dates"][$dayCount]["year"]         = $followYear;
			$data["dates"][$dayCount]['countDisplay'] = 0;
			$dayCount++;
		}

		return $data;
	}

	/**
	 * Calculate the discounted prices for events
	 *
	 * @param $rows
	 */
	public static function calculateDiscount($rows)
	{
		$db       = JFactory::getDbo();
		$query    = $db->getQuery(true);
		$user     = JFactory::getUser();
		$config   = EventbookingHelper::getConfig();
		$nullDate = $db->getNullDate();
		$userId   = $user->get('id');
		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row = $rows[$i];

			if ($userId > 0)
			{
				$query->select('COUNT(id)')
					->from('#__eb_registrants')
					->where('user_id = ' . $userId)
					->where('event_id = ' . $row->id)
					->where('(published=1 OR (payment_method LIKE "os_offline%" AND published NOT IN (2,3)))');
				$db->setQuery($query);
				$row->user_registered = $db->loadResult();
				$query->clear();
			}

			// Calculate discount price
			if ($config->show_discounted_price)
			{
				$discount = 0;
				if (($row->early_bird_discount_date != $nullDate) && ($row->date_diff >= 0))
				{
					if ($row->early_bird_discount_type == 1)
					{
						$discount += $row->individual_price * $row->early_bird_discount_amount / 100;
					}
					else
					{
						$discount += $row->early_bird_discount_amount;
					}
				}
				if ($userId > 0)
				{
					$discountRate = EventbookingHelper::calculateMemberDiscount($row->discount_amounts, $row->discount_groups);
					if ($discountRate > 0)
					{
						if ($row->discount_type == 1)
						{
							$discount += $row->individual_price * $discountRate / 100;
						}
						else
						{
							$discount += $discountRate;
						}
					}
				}

				$row->discounted_price = $row->individual_price - $discount;
			}

			$lateFee = 0;
			if (($row->late_fee_date != $nullDate) && $row->late_fee_date_diff >= 0 && $row->late_fee_amount > 0)
			{
				if ($row->late_fee_type == 1)
				{
					$lateFee = $row->individual_price * $row->late_fee_amount / 100;
				}
				else
				{

					$lateFee = $row->late_fee_amount;
				}
			}

			$row->late_fee = $lateFee;
		}
	}

	/**
	 * Get all children categories of a given category
	 *
	 * @param int $id
	 *
	 * @return array
	 */
	public static function getAllChildrenCategories($id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$queue       = array($id);
		$categoryIds = array($id);

		while (count($queue))
		{
			$categoryId = array_pop($queue);

			//Get list of children categories of the current category
			$query->clear()
				->select('id')
				->from('#__eb_categories')
				->where('parent = ' . $categoryId)
				->where('published = 1');
			$db->setQuery($query);
			$db->setQuery($query);
			$children = $db->loadColumn();
			if (count($children))
			{
				$queue       = array_merge($queue, $children);
				$categoryIds = array_merge($categoryIds, $children);
			}
		}

		return $categoryIds;
	}

	/**
	 * Get parent categories of the given category
	 *
	 * @param $categoryId
	 *
	 * @return array
	 */
	public static function getParentCategories($categoryId)
	{
		$db          = JFactory::getDbo();
		$parents     = array();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		while (true)
		{
			$sql = "SELECT id, name'.$fieldSuffix.' AS name, parent FROM #__eb_categories WHERE id = " . $categoryId . " AND published=1";
			$db->setQuery($sql);
			$row = $db->loadObject();
			if ($row)
			{
				$parents[]  = $row;
				$categoryId = $row->parent;
			}
			else
			{
				break;
			}
		}

		return $parents;
	}

	/**
	 * Get all ticket types of this event
	 *
	 * @param $eventId
	 *
	 * @return array
	 */
	public static function getTicketTypes($eventId)
	{
		static $ticketTypes;

		if (!isset($ticketTypes[$eventId]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*, 0 AS registered')
				->from('#__eb_ticket_types')
				->where('event_id = ' . $eventId)
				->order('id');
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			$query->clear()
				->select('a.ticket_type_id')
				->select('IFNULL(SUM(a.quantity), 0) AS registered')
				->from('#__eb_registrant_tickets AS a')
				->innerJoin('#__eb_registrants AS b ON a.registrant_id = b.id')
				->where('b.event_id = ' . $eventId)
				->where('b.group_id = 0')
				->where('(b.published = 1 OR (b.payment_method LIKE "os_offline%" AND b.published NOT IN (2,3)))')
				->group('a.ticket_type_id');
			$db->setQuery($query);
			$rowTickets = $db->loadObjectList('ticket_type_id');

			if (count($rowTickets))
			{
				foreach ($rows as $row)
				{
					if (isset($rowTickets[$row->id]))
					{
						$row->registered = $rowTickets[$row->id]->registered;
					}
				}
			}

			$ticketTypes[$eventId] = $rows;
		}

		return $ticketTypes[$eventId];

	}

	/***
	 * Get categories used to generate breadcrump
	 *
	 * @param $id
	 * @param $parentId
	 *
	 * @return array
	 */
	public static function getCategoriesBreadcrumb($id, $parentId)
	{
		$db          = JFactory::getDbo();
		$query       = $db->getQuery(true);
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$query->select('id, name' . $fieldSuffix . ' AS name, parent')->from('#__eb_categories')->where('published=1');
		$db->setQuery($query);
		$categories = $db->loadObjectList('id');
		$paths      = array();
		while ($id != $parentId)
		{
			if (isset($categories[$id]))
			{
				$paths[] = $categories[$id];
				$id      = $categories[$id]->parent;
			}
			else
			{
				break;
			}
		}

		return $paths;
	}

	/**
	 * Pre-process event's data before passing to the view for displaying
	 *
	 * @param array  $rows
	 * @param string $context
	 */
	public static function preProcessEventData($rows, $context = 'list')
	{
		// Calculate discounted price
		self::calculateDiscount($rows);

		// Get categories data for each events
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.id, a.name, a.alias')
			->from('#__eb_categories AS a')
			->innerJoin('#__eb_event_categories AS b ON a.id = b.category_id')
			->order('b.id');

		if ($fieldSuffix = EventbookingHelper::getFieldSuffix())
		{
			EventbookingHelperDatabase::getMultilingualFields($query, array('a.name', 'a.alias'), $fieldSuffix);
		}

		foreach ($rows as $row)
		{
			$query->where('event_id=' . $row->id);
			$db->setQuery($query);
			$row->categories     = $db->loadObjectList();
			$row->category_id    = $row->categories[0]->id;
			$row->category_name  = $row->categories[0]->name;
			$row->category_alias = $row->categories[0]->alias;

			$query->clear('where');
		}

		// Process content plugin
		foreach ($rows as $row)
		{
			if ($context == 'list')
			{
				$row->short_description = JHtml::_('content.prepare', $row->short_description);
			}
			else
			{
				$row->description = JHtml::_('content.prepare', $row->description);
			}
		}

		$config = EventbookingHelper::getConfig();

		// Process event custom fields data
		if ($config->event_custom_field && ($config->show_event_custom_field_in_category_layout || $context == 'item'))
		{
			EventbookingHelperData::prepareCustomFieldsData($rows);
		}

		// Calculate price including tax
		if ($config->show_price_including_tax)
		{
			foreach ($rows as $row)
			{
				$taxRate                = $row->tax_rate;
				$row->individual_price  = round($row->individual_price * (1 + $taxRate / 100), 2);
				$row->fixed_group_price = round($row->fixed_group_price * (1 + $taxRate / 100), 2);
				if ($config->show_discounted_price)
				{
					$row->discounted_price = round($row->discounted_price * (1 + $taxRate / 100), 2);
				}
			}
		}

		// Get ticket types for events
		if ($config->display_ticket_types)
		{
			foreach ($rows as $row)
			{
				if ($row->has_multiple_ticket_types)
				{
					$row->ticketTypes = self::getTicketTypes($row->id);
				}
			}
		}
	}

	/**
	 * Decode custom fields data and store it for each event record
	 *
	 * @param $items
	 */
	public static function prepareCustomFieldsData($items)
	{
		$xml          = JFactory::getXML(JPATH_ROOT . '/components/com_eventbooking/fields.xml');
		$fields       = $xml->fields->fieldset->children();
		$customFields = array();
		foreach ($fields as $field)
		{
			$name                  = $field->attributes()->name;
			$label                 = JText::_($field->attributes()->label);
			$customFields["$name"] = $label;
		}

		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			$item   = $items[$i];
			$params = new JRegistry();
			$params->loadString($item->custom_fields, 'JSON');
			$paramData = array();
			foreach ($customFields as $name => $label)
			{
				$paramData[$name]['title'] = $label;
				$paramData[$name]['value'] = $params->get($name);
			}

			if (!property_exists($item, $name))
			{
				$item->{$name} = $params->get($name);
			}

			$item->paramData = $paramData;
		}
	}

	/**
	 * Export registration records into csv file
	 *
	 * @param $rows
	 * @param $config
	 * @param $rowFields
	 * @param $fieldValues
	 * @param $eventId
	 *
	 * @throws Exception
	 */
	public static function csvExport($rows, $config, $rowFields, $fieldValues, $eventId = 0)
	{
		if (count($rows))
		{
			error_reporting(E_ALL);
			$browser   = JFactory::getApplication()->client->browser;
			$mime_type = ($browser == JApplicationWebClient::IE || $browser == JApplicationWebClient::OPERA) ? 'application/octetstream' : 'application/octet-stream';
			$filename  = "registrants_list";
			header('Content-Encoding: UTF-8');
			header('Content-Type: ' . $mime_type . ' ;charset=UTF-8');
			header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
			if ($browser == JApplicationWebClient::IE)
			{
				header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: public');
			}
			else
			{
				header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
				header('Pragma: no-cache');
			}
			$fp = fopen('php://output', 'w');
			fwrite($fp, "\xEF\xBB\xBF");
			$delimiter = $config->csv_delimiter ? $config->csv_delimiter : ',';

			$showGroup = false;
			foreach ($rows as $row)
			{
				if ($row->is_group_billing || $row->group_id > 0)
				{
					$showGroup = true;
					break;
				}
			}

			// Determine whether we need to show payment method column
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('name, title')
				->from('#__eb_payment_plugins')
				->where('published=1');
			$db->setQuery($query);
			$plugins = $db->loadObjectList('name');

			$showPaymentMethodColumn = false;
			if (count($plugins) > 1)
			{
				$showPaymentMethodColumn = true;
			}

			if ($eventId)
			{
				$event = EventbookingHelperDatabase::getEvent($eventId);
				if ($event->has_multiple_ticket_types)
				{
					$ticketTypes = EventbookingHelperData::getTicketTypes($eventId);

					$ticketTypeIds = array();
					foreach ($ticketTypes as $ticketType)
					{
						$ticketTypeIds[] = $ticketType->id;
					}

					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);
					$query->select('registrant_id, ticket_type_id, quantity')
						->from('#__eb_registrant_tickets')
						->where('ticket_type_id IN (' . implode(',', $ticketTypeIds) . ')');
					$db->setQuery($query);

					$registrantTickets = $db->loadObjectList();

					$tickets = array();
					foreach ($registrantTickets as $registrantTicket)
					{
						$tickets[$registrantTicket->registrant_id][$registrantTicket->ticket_type_id] = $registrantTicket->quantity;
					}
				}
			}

			$fields   = array();
			$fields[] = JText::_('EB_EVENT');
			if ($config->show_event_date)
			{
				$fields[] = JText::_('EB_EVENT_DATE');
			}
			if ($showGroup)
			{
				$fields[] = JText::_('EB_GROUP');
			}
			if (count($rowFields))
			{
				foreach ($rowFields as $rowField)
				{
					$fields[] = $rowField->title;
				}
			}

			if (!empty($ticketTypes))
			{
				foreach ($ticketTypes as $ticketType)
				{
					$fields[] = $ticketType->title;
				}
			}
			$fields[] = JText::_('EB_NUMBER_REGISTRANTS');
			$fields[] = JText::_('EB_AMOUNT');
			$fields[] = JText::_('EB_DISCOUNT_AMOUNT');
			$fields[] = JText::_('EB_LATE_FEE');
			$fields[] = JText::_('EB_TAX');
			$fields[] = JText::_('EB_GROSS_AMOUNT');
			if ($config->activate_deposit_feature)
			{
				$fields[] = JText::_('EB_DEPOSIT_AMOUNT');
				$fields[] = JText::_('EB_DUE_AMOUNT');
			}
			if ($config->show_coupon_code_in_registrant_list)
			{
				$fields[] = JText::_('EB_COUPON');
			}
			$fields[] = JText::_('EB_REGISTRATION_DATE');
			if ($showPaymentMethodColumn)
			{
				$fields[] = JText::_('EB_PAYMENT_METHOD');
			}
			$fields[] = JText::_('EB_TRANSACTION_ID');
			$fields[] = JText::_('EB_PAYMENT_STATUS');
			if ($config->activate_invoice_feature)
			{
				$fields[] = JText::_('EB_INVOICE_NUMBER');
			}
			$fields[] = JText::_('EB_ID');
			fputcsv($fp, $fields, $delimiter);
			foreach ($rows as $r)
			{

				$fields   = array();
				$fields[] = $r->title;
				if ($config->show_event_date)
				{
					$fields[] = JHtml::_('date', $r->event_date, $config->date_format, null);
				}
				if ($showGroup)
				{
					if ($r->is_group_billing)
					{
						$fields[] = $r->first_name . ' ' . $r->last_name;
					}
					elseif ($r->group_id > 0)
					{
						$fields[] = $r->group_name;
					}
					else
					{
						$fields[] = '';
					}
				}

				foreach ($rowFields as $rowField)
				{
					if ($rowField->is_core)
					{
						$fields[] = @$r->{$rowField->name};
					}
					else
					{
						$fieldValue = @$fieldValues[$r->id][$rowField->id];
						if (is_string($fieldValue) && is_array(json_decode($fieldValue)))
						{
							$fieldValue = implode(', ', json_decode($fieldValue));
						}
						$fields[] = $fieldValue;
					}
				}

				if (!empty($ticketTypes))
				{
					foreach ($ticketTypes as $ticketType)
					{
						if (!empty($tickets[$r->id][$ticketType->id]))
						{
							$fields[] = $tickets[$r->id][$ticketType->id];
						}
						else
						{
							$fields[] = 0;
						}
					}
				}

				$fields[] = $r->number_registrants;
				$fields[] = EventbookingHelper::formatAmount($r->total_amount, $config);
				$fields[] = EventbookingHelper::formatAmount($r->discount_amount, $config);
				$fields[] = EventbookingHelper::formatAmount($r->late_fee, $config);
				$fields[] = EventbookingHelper::formatAmount($r->tax_amount, $config);
				$fields[] = EventbookingHelper::formatAmount($r->amount, $config);
				if ($config->activate_deposit_feature)
				{
					if ($r->deposit_amount > 0)
					{
						$fields[] = EventbookingHelper::formatAmount($r->deposit_amount, $config);
						$fields[] = EventbookingHelper::formatAmount($r->amount - $r->deposit_amount, $config);
					}
					else
					{
						$fields[] = '';
						$fields[] = '';
					}
				}

				if ($config->show_coupon_code_in_registrant_list)
				{
					$fields[] = $r->coupon_code;
				}

				$fields[] = JHtml::_('date', $r->register_date, $config->date_format);
				if ($showPaymentMethodColumn)
				{
					if ($r->payment_method && isset($plugins[$r->payment_method]))
					{
						$fields[] = JText::_($plugins[$r->payment_method]->title);
					}
					else
					{
						$fields[] = '';
					}
				}
				$fields[] = $r->transaction_id;
				switch ($r->published)
				{
					case 0:
						$fields[] = JText::_('EB_PENDING');
						break;
					case 1:
						$fields[] = JText::_('EB_PAID');
						break;
					case 2:
						$fields[] = JText::_('EB_CANCELLED');
						break;
					case 3:
						$fields[] = JText::_('EB_WAITING_LIST');
						break;
				}
				if ($config->activate_invoice_feature)
				{
					if ($r->invoice_number)
					{
						$fields[] = EventbookingHelper::formatInvoiceNumber($r->invoice_number, $config);
					}
					else
					{
						$fields[] = '';
					}
				}
				$fields[] = $r->id;
				fputcsv($fp, $fields, $delimiter);
			}
			fclose($fp);
		}
		JFactory::getApplication()->close();
	}
}
