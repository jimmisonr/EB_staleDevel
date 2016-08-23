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

class EventbookingModelEvent extends EventbookingModelCommonEvent
{
	/**
	 * @param $file
	 *
	 * @return int
	 * @throws Exception
	 */
	public function import($file)
	{
		$events   = EventbookingHelperData::getDataFromFile($file);
		$imported = 0;

		if (count($events))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id, name')
				->from('#__eb_categories');
			$db->setQuery($query);
			$categories = $db->loadObjectList('name');
			$imported   = 0;
			foreach ($events as $event)
			{
				if (empty($event['title']) || empty($event['category']) || empty($event['event_date']))
				{
					continue;
				}

				/* @var EventbookingTableEvent $row */
				$row = $this->getTable();
				$row->bind($event);
				$this->prepareTable($row, 'save');
				$row->store();
				$eventId = $row->id;

				if (!empty($event['id']))
				{
					$query->clear()
						->delete('#__eb_event_categories')
						->where('event_id = ' . (int) $event['id']);
					$db->setQuery($query);
					$db->execute();
				}

				// Main category
				if (is_numeric($event['category']))
				{
					$categoryId = $event['category'];
				}
				else
				{
					$categoryName = trim($event['category']);
					$categoryId   = isset($categories[$categoryName]) ? $categories[$categoryName]->id : 0;
				}

				if ($categoryId)
				{
					$query->clear()
						->insert('#__eb_event_categories')
						->columns('event_id, category_id, main_category')
						->values("$eventId, $categoryId, 1");
					$db->setQuery($query);
					$db->execute();
				}

				$eventCategories = isset($data['additional_categories']) ? $data['additional_categories'] : array();
				$eventCategories = explode(' | ', $eventCategories);

				for ($i = 0, $n = count($eventCategories); $i < $n; $i++)
				{
					$category = trim($eventCategories[$i]);
					if ($category && isset($categories[$category]))
					{
						$categoryId = $categories[$category]->id;
						$query->clear()
							->insert('#__eb_event_categories')
							->columns('event_id, category_id, main_category')
							->values("$eventId, $categoryId, 0");
						$db->setQuery($query);
						$db->execute();
					}
				}

				$imported++;
			}
		}

		return $imported;
	}
}
