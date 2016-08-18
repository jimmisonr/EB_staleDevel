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

class EventbookingModelDiscount extends RADModelAdmin
{
	/**
	 * Post - process, Store discount rule mapping with events.
	 *
	 * @param EventbookingTableDiscount $row
	 * @param RADInput                  $input
	 * @param bool                      $isNew
	 */
	protected function afterStore($row, $input, $isNew)
	{
		$eventIds   = $input->get('event_id', array(), 'array');
		$discountId = $row->id;
		$db         = $this->getDbo();
		$query      = $db->getQuery(true);
		if (!$isNew)
		{
			$query->delete('#__eb_discount_events')->where('discount_id = ' . $discountId);
			$config = EventbookingHelper::getConfig();
			if ($config->hide_past_events_from_events_dropdown)
			{
				$currentDate = $db->quote(JHtml::_('date', 'Now', 'Y-m-d'));
				$query->where('event_id IN (SELECT id FROM #__eb_events AS a WHERE a.published = 1 AND (DATE(a.event_date) >= ' . $currentDate . ' OR DATE(a.event_end_date) >= ' . $currentDate . '))');
			}
			$db->setQuery($query);
			$db->execute();
		}

		foreach ($eventIds as $eventId)
		{
			$query->clear();
			$query->insert('#__eb_discount_events')->columns('discount_id, event_id');
			for ($i = 0, $n = count($eventIds); $i < $n; $i++)
			{
				$eventId = (int) $eventId;
				$query->values("$discountId, $eventId");
			}
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Delete the mapping between discount and events before the actual discounts are being deleted
	 *
	 * @param array $cid Ids of deleted record
	 */
	protected function beforeDelete($cid)
	{
		if (count($cid))
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true);
			$cids  = implode(',', $cid);
			$query->delete('#__eb_discount_events')
				->where('discount_id IN (' . $cids . ')');
			$db->setQuery($query);
			$db->execute();
		}
	}
}
