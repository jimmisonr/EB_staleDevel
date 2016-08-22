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
	 * @param $input
	 *
	 * @return int
	 * @throws Exception
	 */
	public function import($input)
	{
		$events   = $this->getEventsFromInput($input);
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

	/**
	 * Get events data from excel file
	 *
	 * @param RADInput $input
	 *
	 * @return array
	 */
	private function getEventsFromInput($input)
	{
		$events  = array();
		$csvFile = $input->files->get('events_file');
		require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/vendor/PHPOffice/PHPExcel.php';
		try
		{
			$objPHPExcel = PHPExcel_IOFactory::load($csvFile ['tmp_name']);
		}
		catch (Exception $e)
		{
			die('Error loading file "' . pathinfo($csvFile ['tmp_name'], PATHINFO_BASENAME) . '": ' . $e->getMessage());
		}

		$data = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
		if (count($data) > 1)
		{
			for ($i = 2, $n = count($data); $i <= $n; $i++)
			{
				$event = array();
				foreach ($data[1] as $key => $fieldName)
				{
					$event[$fieldName] = $data[$i][$key];
				}

				$events[] = $event;
			}
		}

		return $events;
	}
}
