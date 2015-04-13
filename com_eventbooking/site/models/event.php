<?php
/**
 * @version        	1.7.1
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

/**
 * Event Booking Component Event Model
 *
 * @package		Joomla
 * @subpackage	Event Booking
 */
class EventBookingModelEvent extends JModelLegacy
{

	/**
	 * Event id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * Event data
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Constructor function
	 *
	 */
	function __construct()
	{
		parent::__construct();
		$id = JFactory::getApplication()->input->getInt('id', 0);
		if ($id)
		{
			$this->setId($id);
		}
	}

	/**
	 * Get event detail
	 *
	 * @return object
	 */
	function getData()
	{
		if (empty($this->_data))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true);
			$id = JRequest::getInt('id', 0);
			$fieldSuffix = EventbookingHelper::getFieldSuffix();
			$currentDate = JHtml::_('date', 'Now', 'Y-m-d H:i:s');
			$query->select('a.*')
				->select('title' . $fieldSuffix . ' AS title, short_description' . $fieldSuffix . ' AS short_description_description, description' . $fieldSuffix . ' AS description')
				->select('meta_keywords' . $fieldSuffix . ' AS meta_keywords, meta_description' . $fieldSuffix . ' AS meta_description')
				->select("DATEDIFF(event_date, '$currentDate') AS number_event_dates")
				->select("TIMESTAMPDIFF(MINUTE, registration_start_date, '$currentDate') AS registration_start_minutes")
				->select("TIMESTAMPDIFF(MINUTE, cut_off_date, '$currentDate') AS cut_off_minutes")
				->select("DATEDIFF(early_bird_discount_date, '$currentDate') AS date_diff")
				->select('IFNULL(SUM(b.number_registrants), 0) AS total_registrants')
				->from('#__eb_events AS a')
				->leftJoin('#__eb_registrants AS b ON (a.id = b.event_id AND b.group_id=0 AND (b.published = 1 OR (b.payment_method LIKE "os_offline%" AND b.published NOT IN (2,3))))')
				->where('a.id = '. $id)
				->where('a.published = 1')
				->group('a.id');
			$db->setQuery($query);
			$row = $db->loadObject();
			if ($row)
			{
				// Get the main category of this event
				$query->clear();
				$query->select('category_id')
					->from('#__eb_event_categories')
					->where('event_id = '. $id)
					->where('main_category = 1');
				$db->setQuery($query);
				$row->category_id = (int)$db->loadResult();
				$rows = array();
				$rows[] = $row;
				EventbookingHelperData::calculateDiscount($rows);
				$this->_data = $rows[0];
			}
			else
			{
				$this->_data = null;
			}
		}
		return $this->_data;
	}

	###############################################Added for supporting add/edit events from front-end##########################################################
	/**
	 * Method to set the event identifier
	 *
	 * @access	public
	 * @param	int event identifier
	 */
	function setId($id)
	{
		// Set event id and wipe data
		$this->_id = $id;
		$this->_data = null;
	}

	/**
	 * Method to get a package
	 *
	 * @since 1.5
	 */
	function &getEvent()
	{
		if (empty($this->_data))
		{
			if ($this->_id)
			{
				$this->_loadData();
			}
			else
			{
				$this->_initData();
			}
		}
		return $this->_data;
	}

	/**
	 * Method to store an event
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
	function store(&$data)
	{
		$db = $this->getDbo();
		//save thumbnail images
		$config = EventbookingHelper::getConfig();
		if ($_FILES['thumb']['name'])
		{
			$fileExt = JString::strtoupper(JFile::getExt($_FILES['thumb']['name']));
			$supportedTypes = array('JPG', 'PNG', 'GIF');
			if (in_array($fileExt, $supportedTypes))
			{
				if (JFile::exists(JPATH_ROOT . '/media/com_eventbooking/images/' . JString::strtolower($_FILES['thumb']['name'])))
				{
					$fileName = time() . '_' . JString::strtolower($_FILES['thumb']['name']);
				}
				else
				{
					$fileName = JString::strtolower($_FILES['thumb']['name']);
				}
				$imagePath = JPATH_ROOT . '/media/com_eventbooking/images/' . $fileName;
				$thumbPath = JPATH_ROOT . '/media/com_eventbooking/images/thumbs/' . $fileName;
				JFile::upload($_FILES['thumb']['tmp_name'], $imagePath);
				if (!$config->thumb_width)
				{
					$config->thumb_width = 120;
				}
				if (!$config->thumb_height)
				{
					$config->thumb_height = 120;
				}
				EventbookingHelper::resizeImage($imagePath, $thumbPath, $config->thumb_width, $config->thumb_height, 95);
				$data['thumb'] = $fileName;
			}
		}
		
		//Init default data
		if (!isset($data['weekdays']))
		{
			$data['weekdays'] = array();
		}
		if (!isset($data['monthdays']))
		{
			$data['monthdays'] = '';
		}
		if (!$data['number_days'])
		{
			$data['number_days'] = 1;
		}
		
		if (!$data['number_weeks'])
		{
			$data['number_week'] = 1;
		}
		if (!$data['recurring_occurrencies'])
		{
			$data['recurring_occurrencies'] = 0;
		}
		if (!$data['recurring_end_date'])
		{
			$data['recurring_end_date'] = $db->getNullDate();
		}
		if (isset($data['recurring_type']) && $data['recurring_type'])
		{
			return $this->_storeRecurringEvent($data);
		}
		else
		{
			$row = $this->getTable('EventBooking', 'Event');
			if ($data['id'])
			{
				$row->load($data['id']);
			}
			else
			{
				$user = JFactory::getUser();
				$row->created_by = $user->get('id');
			}
			
			if (!$data['alias'])
			{
				$data['alias'] = JApplication::stringURLSafe($data['title']);
			}
			
			if (!$row->bind($data, array('category_id')))
			{
				$this->setError($db->getErrorMsg());
				return false;
			}
			//remove thumbnail
			if (isset($data['del_thumb']))
			{
				
				if (JFile::exists(JPATH_ROOT . '/media/com_eventbooking/images/' . $row->thumb))
				{
					JFile::delete(JPATH_ROOT . '/media/com_eventbooking/images/' . $row->thumb);
				}
				if (JFile::exists(JPATH_ROOT . '/media/com_eventbooking/images/thumbs/' . $row->thumb))
				{
					JFile::delete(JPATH_ROOT . '/media/com_eventbooking/images/thumbs/' . $row->thumb);
				}
				$data['thumb'] = '';
			}
			
			$eventDateHour = $data['event_date_hour'];
			$row->event_date .= ' ' . $eventDateHour . ':' . $data['event_date_minute'] . ':00';
			
			$eventDateHour = $data['event_end_date_hour'];
			$row->event_end_date .= ' ' . $eventDateHour . ':' . $data['event_end_date_minute'] . ':00';
			$eventCustomField = EventbookingHelper::getConfigValue('event_custom_field');
			if ($eventCustomField)
			{
				$params = JRequest::getVar('params', null, 'post', 'array');
				if (is_array($params))
				{
					$row->custom_fields = json_encode($params);
				}
			}
			//Check ordering of the fieds		
			if (!$row->id)
			{
				$where = ' category_id = ' . (int) $row->category_id;
				$row->ordering = $row->getNextOrder($where);
			}
			if (!$row->store())
			{
				$this->setError($db->getErrorMsg());
				return false;
			}
			$query = $db->getQuery(true);
			$query->select('COUNT(*)')
				->from('#__eb_events')
				->where('alias=' . $db->quote($row->alias))
				->where('id !=' . $row->id);
			$db->setQuery($query);
			$total = $db->loadResult();
			if ($total)
			{
				$alias = $row->id . '-' . $row->alias;
				$query->clear();
				$query->update('#__eb_events')
					->set('alias=' . $db->quote($alias))
					->where('id=' . $row->id);
				$db->setQuery($query);
				$db->execute();
			}
			
			$sql = 'DELETE FROM #__eb_event_group_prices WHERE event_id = ' . $row->id;
			$db->setQuery($sql);
			$db->execute();
			$prices = $data['price'];
			$registrantNumbers = $data['registrant_number'];
			for ($i = 0, $n = count($prices); $i < $n; $i++)
			{
				$price = $prices[$i];
				$registrantNumber = $registrantNumbers[$i];
				if (($registrantNumber > 0) && ($price > 0))
				{
					$sql = "INSERT INTO #__eb_event_group_prices(event_id, registrant_number, price) VALUES($row->id, $registrantNumber, $price)";
					$db->setQuery($sql);
					$db->execute();
				}
			}
			
			$query->clear();
			$query->delete('#__eb_event_categories')->where('event_id=' . $row->id);
			$db->setQuery($query);
			$db->execute();
			$mainCategoryId = (int) $data['main_category_id'];
			if ($mainCategoryId)
			{
				$query->clear();
				$query->insert('#__eb_event_categories')
					->columns('event_id, category_id, main_category')
					->values("$row->id, $mainCategoryId, 1");
				$db->setQuery($query);
				$db->execute();
			}
			$categories = isset($data['category_id']) ? $data['category_id'] : array();
			for ($i = 0, $n = count($categories); $i < $n; $i++)
			{
				$categoryId = (int) $categories[$i];
				if ($categoryId && ($categoryId != $mainCategoryId))
				{
					$query->clear();
					$query->insert('#__eb_event_categories')
						->columns('event_id, category_id, main_category')
						->values("$row->id, $categoryId, 0");
					$db->setQuery($query);
					$db->execute();
				}
			}
			
			return true;
		}
	}

	/**
	 * Store the event in case recurring feature activated
	 * @param array $data
	 */
	function _storeRecurringEvent($data)
	{
		$db = $this->getDbo();
		$row = $this->getTable('EventBooking', 'Event');
		if ($data['id'])
		{
			$row->load($data['id']);
		}
		else
		{
			$user = JFactory::getUser();
			$row->created_by = $user->get('id');
		}
		if (!$data['alias'])
		{
			$data['alias'] = JApplication::stringURLSafe($data['title']);
		}
		if (!$row->bind($data, array('category_id', 'params')))
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		$row->event_type = 1;
		$eventDateHour = $data['event_date_hour'];
		$row->event_date .= ' ' . $eventDateHour . ':' . $data['event_date_minute'] . ':00';
		$eventDateHour = $data['event_end_date_hour'];
		
		$row->weekdays = implode(',', $data['weekdays']);
		$row->event_end_date .= ' ' . $eventDateHour . ':' . $data['event_end_date_minute'] . ':00';
		//Adjust event start date and event end date				
		if ($data['recurring_type'] == 1)
		{
			$eventDates = EventbookingHelper::getDailyRecurringEventDates($row->event_date, $data['recurring_end_date'], (int) $data['number_days'], 
				(int) $data['recurring_occurrencies']);
			$row->recurring_frequency = $data['number_days'];
		}
		elseif ($data['recurring_type'] == 2)
		{
			$eventDates = EventbookingHelper::getWeeklyRecurringEventDates($row->event_date, $data['recurring_end_date'], (int) $data['number_weeks'], 
				(int) $data['recurring_occurrencies'], $data['weekdays']);
			$row->recurring_frequency = $data['number_weeks'];
		}
		else
		{
			//Monthly recurring
			$eventDates = EventbookingHelper::getMonthlyRecurringEventDates($row->event_date, $data['recurring_end_date'], 
				(int) $data['number_months'], (int) $data['recurring_occurrencies'], $data['monthdays']);
			$row->recurring_frequency = $data['number_months'];
		}
		$eventDuration = abs(strtotime($row->event_end_date) - strtotime($row->event_date));
		if (strlen(trim($row->cut_off_date)))
		{
			$cutOffDuration = abs(strtotime($row->cut_off_date) - strtotime($row->event_date));
		}
		else
		{
			$cutOffDuration = 0;
		}
		if (strlen(trim($row->cancel_before_date)))
		{
			$cancelDuration = abs(strtotime($row->cancel_before_date) - strtotime($row->event_date));
		}
		else
		{
			$cancelDuration = 0;
		}
		if (strlen(trim($row->early_bird_discount_date)))
		{
			$earlyBirdDuration = abs(strtotime($row->early_bird_discount_date) - strtotime($row->event_date));
		}
		else
		{
			$earlyBirdDuration = 0;
		}
		if (count($eventDates) == 0)
		{
			$app = JFactory::getApplication();
			$app->redirect('index.php?option=com_eventbooking&view=events', JText::_('Invalid recurring setting'));
		}
		else
		{
			$row->event_date = $eventDates[0];
			$row->event_end_date = strftime('%Y-%m-%d %H:%M:%S', strtotime($row->event_date) + $eventDuration);
		}
		$config = EventbookingHelper::getConfig();
		$eventCustomField = EventbookingHelper::getConfigValue('event_custom_field');
		if ($eventCustomField)
		{
			$params = JRequest::getVar('params', null, 'post', 'array');
			if (is_array($params))
			{
				$row->custom_fields = json_encode($params);
			}
		}
		//Check ordering of the fieds		
		if (!$row->id)
		{
			$where = ' category_id = ' . (int) $row->category_id;
			$row->ordering = $row->getNextOrder($where);
			$user = JFactory::getUser();
			$row->created_by = $user->get('id');
		}
		if (!$row->store())
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		
		$query = $db->getQuery(true);
		$query->select('COUNT(*)')
			->from('#__eb_events')
			->where('alias=' . $db->quote($row->alias))
			->where('id !=' . $row->id);
		$db->setQuery($query);
		$total = $db->loadResult();
		if ($total)
		{
			$alias = $row->id . '-' . $row->alias;
			$query->clear();
			$query->update('#__eb_events')
				->set('alias=' . $db->quote($alias))
				->where('id=' . $row->id);
			$db->setQuery($query);
			$db->execute();
		}
		
		$sql = 'DELETE FROM #__eb_event_group_prices WHERE event_id = ' . $row->id;
		$db->setQuery($sql);
		$db->execute();
		$prices = $data['price'];
		$registrantNumbers = $data['registrant_number'];
		for ($i = 0, $n = count($prices); $i < $n; $i++)
		{
			$price = $prices[$i];
			$registrantNumber = $registrantNumbers[$i];
			if (($registrantNumber > 0) && ($price > 0))
			{
				$sql = "INSERT INTO #__eb_event_group_prices(event_id, registrant_number, price) VALUES($row->id, $registrantNumber, $price)";
				$db->setQuery($sql);
				$db->execute();
			}
		}
		$query->clear();
		$query->delete('#__eb_event_categories')->where('event_id=' . $row->id);
		$db->setQuery($query);
		$db->execute();
		$mainCategoryId = (int) $data['main_category_id'];
		if ($mainCategoryId)
		{
			$query->clear();
			$query->insert('#__eb_event_categories')
				->columns('event_id, category_id, main_category')
				->values("$row->id, $mainCategoryId, 1");
			$db->setQuery($query);
			$db->execute();
		}
		$categories = isset($data['category_id']) ? $data['category_id'] : array();
		for ($i = 0, $n = count($categories); $i < $n; $i++)
		{
			$categoryId = (int) $categories[$i];
			if ($categoryId && ($categoryId != $mainCategoryId))
			{
				$query->clear();
				$query->insert('#__eb_event_categories')
					->columns('event_id, category_id, main_category')
					->values("$row->id, $categoryId, 0");
				$db->setQuery($query);
				$db->execute();
			}
		}
		/**
		 * In case creating new event, we will create children events
		 */
		if (!$this->_id)
		{
			for ($i = 1, $n = count($eventDates); $i < $n; $i++)
			{
				$rowChildEvent = clone ($row);
				$rowChildEvent->id = 0;
				$rowChildEvent->event_date = $eventDates[$i];
				$rowChildEvent->event_end_date = strftime('%Y-%m-%d %H:%M:%S', strtotime($eventDates[$i]) + $eventDuration);
				if ($cutOffDuration)
				{
					$rowChildEvent->cut_off_date = strftime('%Y-%m-%d %H:%M:%S', strtotime($rowChildEvent->event_date) - $cutOffDuration);
				}
				if ($cancelDuration)
				{
					$rowChildEvent->cancel_before_date = strftime('%Y-%m-%d %H:%M:%S', strtotime($rowChildEvent->event_date) - $cancelDuration);
				}
				if ($earlyBirdDuration)
				{
					$rowChildEvent->early_bird_discount_date = strftime('%Y-%m-%d %H:%M:%S', 
						strtotime($rowChildEvent->event_date) - $earlyBirdDuration);
				}
				$rowChildEvent->event_type = 2;
				$rowChildEvent->parent_id = $row->id;
				$rowChildEvent->created_by = $row->created_by;
				$rowChildEvent->recurring_type = 0;
				$rowChildEvent->recurring_frequency = 0;
				$rowChildEvent->weekdays = '';
				$rowChildEvent->monthdays = '';
				$rowChildEvent->recurring_end_date = $db->getNullDate();
				$rowChildEvent->recurring_occurrencies = 0;
				$rowChildEvent->alias = JApplication::stringURLSafe(
					$rowChildEvent->title . '-' . JHtml::_('date', $rowChildEvent->event_date, $config->date_format, null));
				$rowChildEvent->store();
				$query->clear();
				$query->select('COUNT(*)')
					->from('#__eb_events')
					->where('alias=' . $db->quote($rowChildEvent->alias))
					->where('id !=' . $rowChildEvent->id);
				$db->setQuery($query);
				$total = $db->loadResult();
				if ($total)
				{
					$alias = $rowChildEvent->id . '-' . $rowChildEvent->alias;
					$query->clear();
					$query->update('#__eb_events')
						->set('alias=' . $db->quote($alias))
						->where('id=' . $rowChildEvent->id);
					$db->setQuery($query);
					$db->execute();
				}
				//Event Price
				for ($j = 0, $m = count($prices); $j < $m; $j++)
				{
					$price = $prices[$j];
					$registrantNumber = $registrantNumbers[$j];
					if (($registrantNumber > 0) && ($price > 0))
					{
						$sql = "INSERT INTO #__eb_event_group_prices(event_id, registrant_number, price) VALUES($rowChildEvent->id, $registrantNumber, $price)";
						$db->setQuery($sql);
						$db->execute();
					}
				}
				$sql = 'INSERT INTO #__eb_event_categories(event_id, category_id, main_category) ' .
					 "SELECT $rowChildEvent->id, category_id, main_category FROM #__eb_event_categories WHERE event_id=$row->id";
				$db->setQuery($sql);
				$db->execute();
			}
		}
		elseif (isset($data['update_children_event']))
		{
			$sql = 'SELECT id FROM #__eb_events WHERE parent_id=' . $row->id;
			$db->setQuery($sql);
			if (version_compare(JVERSION, '3.0', 'ge'))
				$children = $db->loadColumn();
			else
				$children = $db->loadResultArray();
			if (count($children))
			{
				$fieldsToUpdate = array(
					'category_id', 
					'location_id', 
					'title', 
					'short_description', 
					'description', 
					'access', 
					'registration_access', 
					'individual_price', 
					'event_capacity', 
					'cut_off_date', 
					'registration_type', 
					'max_group_number', 
					'discount_type', 
					'discount', 
					'paypal_email', 
					'paypal_email', 
					'notification_emails', 
					'user_email_body', 
					'user_email_body_offline', 
					'thanks_message', 
					'thanks_message_offline', 
					'params', 
					'published');
				$rowChildEvent = JTable::getInstance('EventBooking', 'Event');
				foreach ($children as $childId)
				{
					$rowChildEvent->load($childId);
					foreach ($fieldsToUpdate as $field)
						$rowChildEvent->$field = $row->$field;
					$rowChildEvent->store();
					$sql = 'DELETE FROM #__eb_event_group_prices WHERE event_id=' . $rowChildEvent->id;
					$db->setQuery($sql);
					$db->execute();
					for ($i = 0, $n = count($prices); $i < $n; $i++)
					{
						$price = $prices[$i];
						$registrantNumber = $registrantNumbers[$i];
						if (($registrantNumber > 0) && ($price > 0))
						{
							$sql = "INSERT INTO #__eb_event_group_prices(event_id, registrant_number, price) VALUES($rowChildEvent->id, $registrantNumber, $price)";
							$db->setQuery($sql);
							$db->execute();
						}
					}
					$sql = 'DELETE FROM #__eb_event_categories WHERE event_id = ' . $rowChildEvent->id;
					$db->setQuery($sql);
					$db->execute();
					$sql = 'INSERT INTO #__eb_event_categories(event_id, category_id, main_category) '
						. "SELECT $rowChildEvent->id, category_id, main_category FROM #__eb_event_categories WHERE event_id=$row->id";
					$db->setQuery($sql);
					$db->execute();
				}
			}
		}
		return true;
	}

	/**
	 * Init event data
	 *
	 */
	function _initData()
	{
		$db = JFactory::getDbo();
		$config = EventbookingHelper::getConfig();
		$row = new stdClass();
		$row->id = 0;
		$row->category_id = 0;
		$row->location_id = 0;
		$row->title = null;
		$row->event_date = $db->getNullDate();
		$row->event_end_date = $db->getNullDate();
		$row->short_description = null;
		$row->description = null;
		$row->individual_price = null;
		$row->event_capacity = null;
		$row->cut_off_date = $db->getNullDate();
		$row->registration_type = isset($config->registration_type) ? $config->registration_type : 0;
		$row->access = isset($config->access) ? $config->access : 1;
		$row->registration_access = isset($config->registration_access) ? $config->registration_access : 1;
		$row->max_group_number = 0;
		$row->discount_type = 0;
		$row->discount = 0;
		$row->enable_cancel_registration = 0;
		$row->cancel_before_date = $db->getNullDate();
		$row->enable_auto_reminder = null;
		$row->remind_before_x_days = 3;
		$row->early_bird_discount_type = null;
		$row->early_bird_discount_amount = null;
		$row->early_bird_discount_date = $db->getNullDate();
		$row->article_id = $config->article_id;
		$row->recurring_type = 0;
		$row->number_days = '';
		$row->number_weeks = '';
		$row->number_months = '';
		$row->recurring_frequency = 0;
		$row->weekdays = null;
		$row->monthdays = null;
		$row->recurring_end_date = $db->getNullDate();
		$row->recurring_occurrencies = null;
		$row->paypal_email = null;
		$row->notification_emails = null;
		$row->user_email_body = null;
		$row->user_email_body_offline = null;
		$row->thanks_message = null;
		$row->thanks_message_offline = null;
		$row->params = null;
		$row->custom_fields = null;
		$row->ordering = 0;
		$row->published = isset($config->default_event_status) ? $config->default_event_status : 0;
		$this->_data = $row;
	}

	/**
	 * Load event information from database
	 * 
	 */
	function _loadData()
	{
		$db = $this->getDbo();
		$sql = 'SELECT * FROM #__eb_events WHERE id=' . $this->_id;
		$db->setQuery($sql);
		$row = $db->loadObject();
		$activateRecurringEvent = EventbookingHelper::getConfigValue('activate_recurring_event');
		if ($activateRecurringEvent)
		{
			if ($row->recurring_type == 1)
			{
				$row->number_days = $row->recurring_frequency;
				$row->number_weeks = 0;
				$row->number_months = 0;
			}
			elseif ($row->recurring_type == 2)
			{
				$row->number_weeks = $row->recurring_frequency;
				$row->number_days = 0;
				$row->number_months = 0;
			}
			elseif ($row->recurring_type == 3)
			{
				$row->number_months = $row->recurring_frequency;
				$row->number_days = 0;
				$row->number_weeks = 0;
			}
		}
		$this->_data = $row;
	}

	/**
	 * Publish / unpublish an event 
	 *
	 * @param int $id
	 * @param int $state
	 */
	function publish($id, $state)
	{
		$db = $this->getDbo();
		$sql = " UPDATE #__eb_events SET published=$state WHERE id=$id";
		$db->setQuery($sql);
		if (!$db->execute())
			return false;
		return true;
	}

	/**
	 * Save the order of events
	 *
	 * @param array $cid
	 * @param array $order
	 */
	function saveOrder($cid, $order)
	{
		$db = $this->getDbo();
		$row = JTable::getInstance('EventBooking', 'Event');
		$groupings = array();
		// update ordering values
		for ($i = 0; $i < count($cid); $i++)
		{
			$row->load((int) $cid[$i]);
			// track parents
			//$groupings[] = $row->category_id ;
			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];
				if (!$row->store())
				{
					$this->setError($db->getErrorMsg());
					return false;
				}
			}
		}
		// execute updateOrder for each parent group
		/*
		$groupings = array_unique( $groupings );
		foreach ($groupings as $group){
			$row->reorder('category_id = '.(int) $group);
		}
		*/
		return true;
	}

	/**
	 * Change ordering of a category
	 *
	 */
	function move($direction)
	{
		$db = $this->getDbo();
		$row = JTable::getInstance('EventBooking', 'Event');
		$row->load($this->_id);
		if (!$row->move($direction, ' published >= 0 '))
		{
			$this->setError($db->getErrorMsg());
			return false;
		}
		return true;
	}

	/**
	 * Get price setting for the event
	 *
	 */
	function getPrices()
	{
		$db = $this->getDbo();
		if ($this->_id)
		{
			$sql = 'SELECT * FROM #__eb_event_group_prices WHERE event_id=' . $this->_id . ' ORDER BY id ';
			$db->setQuery($sql);
			$prices = $db->loadObjectList();
		}
		else
		{
			$prices = array();
		}
		return $prices;
	}

	/**
	 * Copy an event to create new event
	 *
	 * @param int $id
	 */
	function copy($id)
	{
		$db = $this->getDbo();
		$rowOld = JTable::getInstance('EventBooking', 'Event');
		$rowOld->load($id);
		$row = JTable::getInstance('EventBooking', 'Event');
		$data = JArrayHelper::fromObject($rowOld);
		$row->bind($data);
		$row->id = 0;
		$row->title = 'Copy of ' . $row->title;
		$row->store();
		//We will insert group rate for this event
		$sql = 'INSERT INTO #__eb_event_group_prices(event_id, registrant_number, price) ' . ' SELECT ' . $row->id .
			 ' , registrant_number, price FROM #__eb_event_group_prices ' . ' WHERE event_id=' . $id;
		$db->setQuery($sql);
		$db->execute();
		
		//Need to enter categories for this event
		

		$sql = 'INSERT INTO #__eb_event_categories(event_id, category_id) ' . ' SELECT ' . $row->id . ' , category_id FROM #__eb_event_categories ' .
			 ' WHERE event_id=' . $id;
		$db->setQuery($sql);
		$db->execute();
		
		return $row->id;
	}
} 
