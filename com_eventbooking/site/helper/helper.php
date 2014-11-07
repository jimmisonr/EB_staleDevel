<?php
/**
 * @version        	1.6.6
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2014 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

class EventbookingHelper
{

	/**
     * Return the current installed version
     */
	public static function getInstalledVersion()
	{
		return '1.6.6';
	}

	/**
	 * Get configuration data and store in config object
	 *
	 * @return object
	 */
	public static function getConfig($nl2br = false, $language = null)
	{
		static $config;
		if (!$config)
		{
			$config = new stdClass();
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('config_key, config_value')->from('#__eb_configs');
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			for ($i = 0, $n = count($rows); $i < $n; $i++)
			{
				$row = $rows[$i];
				$key = $row->config_key;
				$value = stripslashes($row->config_value);
				$config->$key = $value;
			}
		}
		
		return $config;
	}

	/**
     * Get specify config value
     *
     * @param string $key
     */
	public static function getConfigValue($key)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('config_value')
			->from('#__eb_configs')
			->where('config_key="' . $key . '"');
		$db->setQuery($query);
		
		return $db->loadResult();
	}
	/**
	 * We only need to generate invoice for paid events only
	 * @param object $row
	 */
	public static function needInvoice($row)
	{
		$config = self::getConfig();
		if ($config->multiple_booking)
		{
			$db = JFactory::getDbo();
			//Get summary total amount
			$sql = 'SELECT SUM(total_amount) FROM #__eb_registrants WHERE id=' . $row->id . ' OR cart_id=' . $row->id;
			$db->setQuery($sql);
			$totalAmount = $db->loadResult();
			if ($totalAmount > 0)
			{
				return true;
			}
			else 
			{
				return false;
			}
		}
		else 
		{
			if ($row->amount > 0  || $row->total_amount > 0)
			{
				return true;
			}
			else
			{
				return false;
			}	
		}		
	}
	/**
     * Get request data, used for RADList model
     * 
     */
	public static function getRequestData()
	{
		$request = $_REQUEST;
		//Remove cookie vars from request
		$cookieVars = array_keys($_COOKIE);
		if (count($cookieVars))
		{
			foreach ($cookieVars as $key)
			{
				if (!isset($_POST[$key]) && !isset($_GET[$key]))
				{
					unset($request[$key]);
				}
			}
		}
		if (isset($request['start']) && !isset($request['limitstart']))
		{
			$request['limitstart'] = $request['start'];
		}
		if (!isset($request['limitstart']))
		{
			$request['limitstart'] = 0;
		}
		return $request;
	}

	/**
	 * Get the email messages used for sending emails or displaying in the form
	 *
	 * @return stdClass
	 */
	public static function getMessages()
	{
		static $message;
		if (!$message)
		{
			$message = new stdClass();
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')->from('#__eb_messages');
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			for ($i = 0, $n = count($rows); $i < $n; $i++)
			{
				$row = $rows[$i];
				$key = $row->message_key;
				$value = stripslashes($row->message);
				$message->$key = $value;
			}
		}
		
		return $message;
	}

	/**
	 * Get field suffix used in sql query
	 *
	 * @return string
	 */
	public static function getFieldSuffix($activeLanguage = null)
	{
		$prefix = '';
		if (JLanguageMultilang::isEnabled())
		{
			if (!$activeLanguage || $activeLanguage == '*')
			{
				$activeLanguage = JFactory::getLanguage()->getTag();
			}
			if ($activeLanguage != self::getDefaultLanguage())
			{
				$prefix = '_' . substr($activeLanguage, 0, 2);
			}
		}
		
		return $prefix;
	}

	public static function getLanguages()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$default = self::getDefaultLanguage();
		$query->select('lang_id, lang_code, title, `sef`')
			->from('#__languages')
			->where('published = 1')
			->where('lang_code != "' . $default . '"')
			->order('ordering');
		$db->setQuery($query);
		$languages = $db->loadObjectList();
		
		return $languages;
	}

	/**
	 * Get front-end default language
	 * 
	 * @return string
	 */
	public static function getDefaultLanguage()
	{
		$params = JComponentHelper::getParams('com_languages');
		return $params->get('site', 'en-GB');
	}

	public static function getRecaptchaLanguage()
	{
		$language = JFactory::getLanguage();
		$tag = explode('-', $language->getTag());
		$tag = $tag[0];
		$available = array('en', 'pt', 'fr', 'de', 'nl', 'ru', 'es', 'tr');
		if (in_array($tag, $available))
		{
			return "lang : '" . $tag . "',";
		}
	}

	/**
	 * Get URL of the site, using for Ajax request
	 */
	public static function getSiteUrl()
	{
		$uri = JUri::getInstance();
		$base = $uri->toString(array('scheme', 'host', 'port'));
		if (strpos(php_sapi_name(), 'cgi') !== false && !ini_get('cgi.fix_pathinfo') && !empty($_SERVER['REQUEST_URI']))
		{
			$script_name = $_SERVER['PHP_SELF'];
		}
		else
		{
			$script_name = $_SERVER['SCRIPT_NAME'];
		}
		$path = rtrim(dirname($script_name), '/\\');
		if ($path)
		{
			return $base . $path . '/';
		}
		else
		{
			return $base . '/';
		}
	}

	/**
	 * Get the form fields to display in registration form
	 * @param int $eventId (ID of the event or ID of the registration record in case the system use shopping cart)
	 * @param int $registrationType
	 * @return array
	 */
	public static function getFormFields($eventId = 0, $registrationType = 0)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();		
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$config = EventbookingHelper::getConfig();
		$query->select('*')
			->from('#__eb_fields')
			->where('published=1')
			->where(' `access` IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
		if (!$app->isAdmin() && $app->getLanguageFilter())
		{
			$query->where(' language IN (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')');
		}
		switch ($registrationType)
		{
			case 0:
				$query->where('display_in IN (0, 1, 3, 5)');
				break;
			case 1:
				$query->where('display_in IN (0, 2, 3)');
				break;
			case 2:
				$query->where('display_in IN (0, 4, 5)');
				break;
		}
		if ($registrationType == 4)
		{
			$cart = new EventbookingHelperCart();
			$items = $cart->getItems();
			if ($config->custom_field_by_category)
			{
				if (!count($items))
				{
					//In this case, we have ID of registration record, so, get list of events from that registration
					$sql = 'SELECT event_id FROM #__eb_registrants WHERE id=' . $eventId;
					$db->setQuery($sql);
					$cartEventId = (int) $db->loadResult();
				}
				else
				{
					$cartEventId = (int) $items[0];
				}
				$sql = 'SELECT category_id FROM #__eb_event_categories WHERE event_id=' . $cartEventId . ' AND main_category = 1';
				$db->setQuery($sql);
				$categoryId = (int) $db->loadResult();
				$query->where('(category_id = 0 OR category_id=' . $categoryId . ')');
			}
			else 
			{
				if (!count($items))
				{
					//In this case, we have ID of registration record, so, get list of events from that registration
					$sql = 'SELECT event_id FROM #__eb_registrants WHERE id=' . $eventId;
					$db->setQuery($sql);
					$items = $db->loadColumn();
				}
				$query->where(' (event_id = -1 OR id IN (SELECT field_id FROM #__eb_field_events WHERE event_id IN (' . implode(',', $items) . ')))');
			}			
		}
		else
		{
			if ($config->custom_field_by_category)
			{
				//Get main category of the event
				$sql = 'SELECT category_id FROM #__eb_event_categories WHERE event_id=' . $eventId . ' AND main_category = 1';
				$db->setQuery($sql);
				$categoryId = (int) $db->loadResult();
				$query->where('(category_id = 0 OR category_id=' . $categoryId . ')');				
			}
			else 
			{
				$query->where(' (event_id = -1 OR id IN (SELECT field_id FROM #__eb_field_events WHERE event_id=' . $eventId . '))');
			}			
		}
		$query->order('ordering');
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	/**
	 * Get the form data used to bind to the RADForm object
	 * @param array $rowFields
	 * @param int $eventId
	 * @param int $userId
	 * @param object $config
	 * @return array
	 */
	public static function getFormData($rowFields, $eventId, $userId, $config)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$data = array();
		if ($userId)
		{
			if ($config->cb_integration == 1)
			{
				$syncronizer = new RADSynchronizerCommunitybuilder();
				$mappings = array();
				foreach ($rowFields as $rowField)
				{
					if ($rowField->field_mapping)
					{
						$mappings[$rowField->name] = $rowField->field_mapping;
					}
				}
				$data = $syncronizer->getData($userId, $mappings);
			}
			elseif ($config->cb_integration == 2)
			{
				$syncronizer = new RADSynchronizerJomsocial();
				$mappings = array();
				foreach ($rowFields as $rowField)
				{
					if ($rowField->field_mapping)
					{
						$mappings[$rowField->name] = $rowField->field_mapping;
					}
				}
				$data = $syncronizer->getData($userId, $mappings);
			}
			elseif ($config->cb_integration == 3)
			{
				$syncronizer = new RADSynchronizerMembershippro();
				$mappings = array();
				foreach ($rowFields as $rowField)
				{
					if ($rowField->field_mapping)
					{
						$mappings[$rowField->name] = $rowField->field_mapping;
					}
				}
				$data = $syncronizer->getData($userId, $mappings);
			}
			elseif ($config->cb_integration == 4)
			{
				$syncronizer = new RADSynchronizerJoomla();
				$mappings = array();
				foreach ($rowFields as $rowField)
				{
					if ($rowField->field_mapping)
					{
						$mappings[$rowField->name] = $rowField->field_mapping;
					}
				}
				$data = $syncronizer->getData($userId, $mappings);
			}
			else
			{
				$query->select('*')
					->from('#__eb_registrants')
					->where('user_id=' . $userId . ' AND event_id=' . $eventId . ' AND first_name != "" AND group_id=0')
					->order('id DESC');
				$db->setQuery($query, 0, 1);
				$rowRegistrant = $db->loadObject();
				if (!$rowRegistrant)
				{
					//Try to get registration record from other events if available
					$query->clear('where')->where('user_id=' . $userId . ' AND first_name != "" AND group_id=0');
					$db->setQuery($query, 0, 1);
					$rowRegistrant = $db->loadObject();
				}
				if ($rowRegistrant)
				{
					$data = self::getRegistrantData($rowRegistrant, $rowFields);
				}
			}
		}
		return $data;
	}

	/**
	 * Get data of registrant using to auto populate registration form
	 * @param Object $rowRegistrant
	 * @param array $rowFields
	 * @return array
	 */
	public static function getRegistrantData($rowRegistrant, $rowFields)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$data = array();
		$query->select('a.name, b.field_value')
			->from('#__eb_fields AS a')
			->innerJoin('#__eb_field_values AS b ON a.id = b.field_id')
			->where('b.registrant_id=' . $rowRegistrant->id);
		$db->setQuery($query);
		$fieldValues = $db->loadObjectList('name');
		for ($i = 0, $n = count($rowFields); $i < $n; $i++)
		{
			$rowField = $rowFields[$i];
			if ($rowField->is_core)
			{
				$data[$rowField->name] = $rowRegistrant->{$rowField->name};
			}
			else
			{
				if (isset($fieldValues[$rowField->name]))
				{
					$data[$rowField->name] = $fieldValues[$rowField->name]->field_value;
				}
			}
		}
		
		return $data;
	}

	/**
	 * Check to see whether we will show billing form on group registration
	 * @param int $eventId
	 * @return boolean
	 */
	public static function showBillingStep($eventId)
	{
        $config = self::getConfig();
        if (!$config->collect_member_information)
        {
            return true;
        }
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('individual_price')
			->from('#__eb_events')
			->where('id=' . $eventId);
		$db->setQuery($query);
		$individualPrice = $db->loadResult();
		if ($individualPrice == 0)
		{
			$config = EventbookingHelper::getConfig();
			if ($config->custom_field_by_category)
			{
				$sql = 'SELECT category_id FROM #__eb_event_categories WHERE event_id=' . $eventId . ' AND main_category = 1';
				$db->setQuery($sql);
				$categoryId = (int) $db->loadResult();
				$sql = 'SELECT COUNT(*) FROM #__eb_fields WHERE fee_field = 1 AND published= 1 AND (category_id = 0 OR category_id=' . $categoryId . ')';
				$db->setQuery($sql);
			}	
			else 
			{
				$sql = 'SELECT COUNT(*) FROM #__eb_fields WHERE fee_field = 1 AND published= 1 AND (event_id = -1 OR id IN (SELECT field_id FROM #__eb_field_events WHERE event_id=' .
					$eventId . '))';
				$db->setQuery($sql);
			}					
			$numberFeeFields = (int) $db->loadResult();
			if ($numberFeeFields == 0)
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * 
	 * 
	 * @return string
	 */
	public static function validateEngine()
	{
		$dateNow = JHtml::_('date', JFactory::getDate(), 'Y/m/d');
		//validate[required,custom[integer],min[-5]] text-input
		$validClass = array(
			"", 
			"validate[custom[integer]]", 
			"validate[custom[number]]", 
			"validate[custom[email]]", 
			"validate[custom[url]]", 
			"validate[custom[phone]]", 
			"validate[custom[date],past[$dateNow]]", 
			"validate[custom[ipv4]]", 
			"validate[minSize[6]]", 
			"validate[maxSize[12]]", 
			"validate[custom[integer],min[-5]]", 
			"validate[custom[integer],max[50]]");
		return json_encode($validClass);
	}


	public static function getURL()
	{
		static $url;
		if (!$url)
		{
			$ssl = self::getConfigValue('use_https');
			$url = self::getSiteUrl();
			if ($ssl)
			{
				$url = str_replace('http://', 'https://', $url);
			}
		}
		return $url;
	}

	/**
	 * Get Itemid of Event Booking extension
	 *
	 * @return int
	 */
	public static function getItemid()
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();
		$query->select('id')
			->from('#__menu AS a')
			->where('a.link LIKE "%index.php?option=com_eventbooking%"')
			->where('a.published=1')
			->where('a.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
		if ($app->isSite() && $app->getLanguageFilter())
		{
			$query->where('a.language IN (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')');
		}
		$query->order('a.access');
		$db->setQuery($query);
		$itemId = $db->loadResult();
		if (!$itemId)
		{
			$Itemid = $app->input->getInt('Itemid', 0);
			if ($Itemid == 1)
			{
				$itemId = 999999;
			}
			else
			{
				$itemId = $Itemid;
			}
		}
		
		return $itemId;
	}

	/**
	 *
	 * @param JUser $user the current logged in user
	 * @param Stdclass $config
	 * @return boolean
	 */
	public static function memberGetDiscount($user, $config)
	{
		if (isset($config->member_discount_groups) && $config->member_discount_groups)
		{
			$userGroups = $user->getAuthorisedGroups();
			if (count(array_intersect(explode(',', $config->member_discount_groups), $userGroups)))
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return true;
		}
	}

	/**
	 * Format the currency according to the settings in Configuration
	 * @param $amount   the input amount
	 * @param $config   the config object
	 * @param null $currencySymbol  the currency symbol. If null, the one in configuration will be used
	 * @return string   the formatted string
	 */
	public static function formatAmount($amount, $config)
	{
		$decimals = isset($config->decimals) ? $config->decimals : 2;
		$dec_point = isset($config->dec_point) ? $config->dec_point : '.';
		$thousands_sep = isset($config->thousands_sep) ? $config->thousands_sep : ',';
		return number_format($amount, $decimals, $dec_point, $thousands_sep);
	}

	/**
     * Format the currency according to the settings in Configuration
     * @param $amount   the input amount
     * @param $config   the config object
     * @param null $currencySymbol  the currency symbol. If null, the one in configuration will be used
     * @return string   the formatted string
     */
	public static function formatCurrency($amount, $config, $currencySymbol = null)
	{
		$decimals = isset($config->decimals) ? $config->decimals : 2;
		$dec_point = isset($config->dec_point) ? $config->dec_point : '.';
		$thousands_sep = isset($config->thousands_sep) ? $config->thousands_sep : ',';
		$symbol = $currencySymbol ? $currencySymbol : $config->currency_symbol;
		
		return $config->currency_position ? (number_format($amount, $decimals, $dec_point, $thousands_sep) . $symbol) : ($symbol .
			 number_format($amount, $decimals, $dec_point, $thousands_sep));
	}

	/**
     * Load Event Booking language file
     */
	public static function loadLanguage()
	{
		static $loaded;
		if (!$loaded)
		{
			$lang = JFactory::getLanguage();
			$tag = $lang->getTag();
			if (!$tag)
				$tag = 'en-GB';
			$lang->load('com_eventbooking', JPATH_ROOT, $tag);
			$loaded = true;
		}
	}

	/**
     * Get email content, used for [REGISTRATION_DETAIL] tag
     *
     * @param object $config
     * @param object $row
     * @return string
     */
	public static function getMemberDetails($config, $rowMember, $rowEvent, $rowLocation, $loadCss = true, $memberForm)
	{
		if ($memberForm)
		{
			$memberForm->buildFieldsDependency();
			$fields = $memberForm->getFields();
			foreach ($fields as $field)
			{
				if ($field->hideOnDisplay)
				{
					unset($fields[$field->name]);
				}
			}
			$memberForm->setFields($fields);
		}		
		$data = array();
		$data['rowMember'] = $rowMember;
		$data['rowEvent'] = $rowEvent;
		$data['config'] = $config;
		$data['rowLocation'] = $rowLocation;
		$data['memberForm'] = $memberForm;
		
		$text = EventbookingHelperHtml::loadCommonLayout(JPATH_ROOT . '/components/com_eventbooking/emailtemplates/email_group_member_detail.php', 
			$data);
		if ($loadCss)
		{
			$text .= "
				<style type=\"text/css\">
				" . JFile::read(JPATH_ROOT . '/components/com_eventbooking/assets/css/style.css') . "
                </style>
            ";
		}
		
		return $text;
	}

	/**
	 * Get email content, used for [REGISTRATION_DETAIL] tag
	 *
	 * @param object $config
	 * @param object $row
	 * @return string
	 */
	public static function getEmailContent($config, $row, $loadCss = true, $form = null)
	{
		$db = JFactory::getDbo();
		$data = array();
		$Itemid = JRequest::getInt('Itemid', 0);		
		if ($form)
		{
			$form->buildFieldsDependency();
			$fields = $form->getFields();
			foreach ($fields as $field)
			{								
				if ($field->hideOnDisplay)
				{
					unset($fields[$field->name]);
				}
			}
			$form->setFields($fields);
		}						
		if ($config->multiple_booking)
		{
			if ($loadCss)
			{
				$layout = 'email_cart.php';
			}
			else
			{
				$layout = 'cart.php';
			}
		}
		else
		{
			if ($row->is_group_billing)
			{
				if ($loadCss)
				{
					$layout = 'email_group_detail.php';
				}
				else
				{
					$layout = 'group_detail.php';
				}
			}
			else
			{
				if ($loadCss)
				{
					$layout = 'email_individual_detail.php';
				}
				else
				{
					$layout = 'individual_detail.php';
				}
			}
		}
		if ($config->multiple_booking)
		{
			$data['row'] = $row;
			$data['config'] = $config;
			$data['Itemid'] = $Itemid;
			$sql = 'SELECT a.*, b.event_date, b.title FROM #__eb_registrants AS a INNER JOIN #__eb_events AS b ON a.event_id=b.id WHERE a.id=' .
				 $row->id . ' OR a.cart_id=' . $row->id;
			$db->setQuery($sql);
			$rows = $db->loadObjectList();
			$sql = 'SELECT SUM(total_amount) FROM #__eb_registrants WHERE id=' . $row->id . ' OR cart_id=' . $row->id;
			$db->setQuery($sql);
			$totalAmount = $db->loadResult();
			
			$sql = 'SELECT SUM(tax_amount) FROM #__eb_registrants WHERE id=' . $row->id . ' OR cart_id=' . $row->id;
			$db->setQuery($sql);
			$taxAmount = $db->loadResult();
			
			$sql = 'SELECT SUM(discount_amount) FROM #__eb_registrants WHERE id=' . $row->id . ' OR cart_id=' . $row->id;
			$db->setQuery($sql);
			$discountAmount = $db->loadResult();
			$amount = $totalAmount - $discountAmount + $taxAmount;
			
			$sql = 'SELECT SUM(deposit_amount) FROM #__eb_registrants WHERE id=' . $row->id . ' OR cart_id=' . $row->id;
			$db->setQuery($sql);
			$depositAmount = $db->loadResult();
			//Added support for custom field feature			
			$data['discountAmount'] = $discountAmount;
			$data['totalAmount'] = $totalAmount;
			$data['items'] = $rows;
			$data['amount'] = $amount;
			$data['taxAmount'] = $taxAmount;
			$data['depositAmount'] = $depositAmount;
			$data['form'] = $form;
		}
		else
		{
			$query = $db->getQuery(true);
			$query->select('*')
				->from('#__eb_events')
				->where('id=' . $row->event_id);
			$db->setQuery($query);
			$rowEvent = $db->loadObject();
			
			$query->clear();
			$query->select('a.*')
				->from('#__eb_locations AS a')
				->innerJoin('#__eb_events AS b ON a.id = b.location_id')
				->where('b.id=' . $row->event_id);
			$db->setQuery($query);
			$rowLocation = $db->loadObject();
			//Override config						
			$data['row'] = $row;
			$data['rowEvent'] = $rowEvent;
			$data['config'] = $config;
			$data['rowLocation'] = $rowLocation;
			$data['form'] = $form;
			if ($row->is_group_billing && $config->collect_member_information)
			{
				$sql = 'SELECT * FROM #__eb_registrants WHERE group_id=' . $row->id;
				$db->setQuery($sql);
				$rowMembers = $db->loadObjectList();
				$data['rowMembers'] = $rowMembers;
			}
		}
		$text = EventbookingHelperHtml::loadCommonLayout(JPATH_ROOT . '/components/com_eventbooking/emailtemplates/' . $layout, $data);
		if ($loadCss)
		{
			$text .= "
				<style type=\"text/css\">
				" . JFile::read(JPATH_ROOT . '/components/com_eventbooking/assets/css/style.css') . "
                </style>
            ";
		}
		
		return $text;
	}

	/**
	 * Build category dropdown
	 *
	 * @param int $selected
	 * @param string $name
	 * @param Boolean $onChange
	 * @return string
	 */
	public static function buildCategoryDropdown($selected, $name = "parent", $onChange = true)
	{
		$db = JFactory::getDbo();
		$sql = "SELECT id, parent, parent AS parent_id, name, name AS title FROM #__eb_categories";
		$db->setQuery($sql);
		$rows = $db->loadObjectList();
		$children = array();
		if ($rows)
		{
			// first pass - collect children
			foreach ($rows as $v)
			{
				$pt = $v->parent;
				$list = @$children[$pt] ? $children[$pt] : array();
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}
		$list = JHtml::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
		$options = array();
		$options[] = JHtml::_('select.option', '0', JText::_('Top'));
		foreach ($list as $item)
		{
			$options[] = JHtml::_('select.option', $item->id, '&nbsp;&nbsp;&nbsp;' . $item->treename);
		}
		
		if ($onChange)
			return JHtml::_('select.genericlist', $options, $name, 
				array(
					'option.text.toHtml' => false, 
					'option.text' => 'text', 
					'option.value' => 'value', 
					'list.attr' => 'class="inputbox" onchange="submit();"', 
					'list.select' => $selected));
		else
			return JHtml::_('select.genericlist', $options, $name, 
				array(
					'option.text.toHtml' => false, 
					'option.text' => 'text', 
					'option.value' => 'value', 
					'list.attr' => 'class="inputbox" ', 
					'list.select' => $selected));
	}

	/**
	 * Parent category select list
	 *
	 * @param object $row
	 * @return void
	 */
	public static function parentCategories($row)
	{
		$db = JFactory::getDbo();
		$sql = "SELECT id, parent, parent AS parent_id, name, name AS title FROM #__eb_categories";
		if ($row->id)
			$sql .= ' WHERE id != ' . $row->id;
		if (!$row->parent)
		{
			$row->parent = 0;
		}
		$db->setQuery($sql);
		$rows = $db->loadObjectList();
		$children = array();
		if ($rows)
		{
			// first pass - collect children
			foreach ($rows as $v)
			{
				$pt = $v->parent;
				$list = @$children[$pt] ? $children[$pt] : array();
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}
		$list = JHtml::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
		
		$options = array();
		$options[] = JHtml::_('select.option', '0', JText::_('Top'));
		foreach ($list as $item)
		{
			$options[] = JHtml::_('select.option', $item->id, '&nbsp;&nbsp;&nbsp;' . $item->treename);
		}
		
		return JHtml::_('select.genericlist', $options, 'parent', 
			array(
				'option.text.toHtml' => false, 
				'option.text' => 'text', 
				'option.value' => 'value', 
				'list.attr' => ' class="inputbox" ', 
				'list.select' => $row->parent));
	}

	public static function attachmentList($attachment, $config)
	{
		jimport('joomla.filesystem.folder');
		$path = JPATH_ROOT . '/media/com_eventbooking';
		$files = JFolder::files($path, 
			strlen(trim($config->attachment_file_types)) ? $config->attachment_file_types : 'bmp|gif|jpg|png|swf|zip|doc|pdf|xls');
		$options = array();
		$options[] = JHtml::_('select.option', '', JText::_('EB_SELECT_ATTACHMENT'));
		for ($i = 0, $n = count($files); $i < $n; $i++)
		{
			$file = $files[$i];
			$options[] = JHtml::_('select.option', $file, $file);
		}
		return JHtml::_('select.genericlist', $options, 'attachment', 'class="inputbox"', 'value', 'text', $attachment);
	}

	/**
	 * Get total document of a category
	 *
	 * @param int $categoryId
	 */
	public static function getTotalEvent($categoryId, $includeChildren = true)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$hidePastEvents = EventbookingHelper::getConfigValue('hide_past_events');
		$db = JFactory::getDbo();
		$arrCats = array();
		$cats = array();
		$arrCats[] = $categoryId;
		$cats[] = $categoryId;
		if ($includeChildren)
		{
			while (count($arrCats))
			{
				$catId = array_pop($arrCats);
				//Get list of children category
				$sql = 'SELECT id FROM #__eb_categories WHERE parent=' . $catId . ' AND published=1';
				$db->setQuery($sql);
				$rows = $db->loadObjectList();
				for ($i = 0, $n = count($rows); $i < $n; $i++)
				{
					$row = $rows[$i];
					$arrCats[] = $row->id;
					$cats[] = $row->id;
				}
			}
		}
		
		if ($hidePastEvents)
			$sql = 'SELECT COUNT(a.id) FROM #__eb_events AS a INNER JOIN #__eb_event_categories AS b ON a.id = b.event_id WHERE b.category_id IN(' .
				 implode(',', $cats) . ') AND published = 1 AND `access` IN (' . implode(',', $user->getAuthorisedViewLevels()) .
				 ') AND event_date >= "'.JHtml::_('date', 'Now', 'Y-m-d').'" ';
		else
			$sql = 'SELECT COUNT(a.id) FROM #__eb_events AS a INNER JOIN #__eb_event_categories AS b ON a.id = b.event_id WHERE b.category_id IN(' .
				 implode(',', $cats) . ') AND `access` IN (' . implode(',', $user->getAuthorisedViewLevels()) . ') AND published = 1 ';
		
		if ($app->getLanguageFilter())
		{
			$sql .= ' AND `language` IN (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')';
		}
		
		$db->setQuery($sql);
		return (int) $db->loadResult();
	}

	/**
	 * Check to see whether this event still accept registration
	 *
	 * @param int $eventId
	 * @return Boolean
	 */
	public static function acceptRegistration($eventId)
	{
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$gid = $user->get('aid');
		if (!$eventId)
			return false;
		$sql = 'SELECT * FROM #__eb_events WHERE id=' . $eventId . ' AND published=1 ';
		$db->setQuery($sql);
		$row = $db->loadObject();
		if (!$row)
			return false;
		if ($row->registration_type == 3)
			return false;
		
		if (!in_array($row->registration_access, $user->getAuthorisedViewLevels()))
		{
			return false;
		}
		
		if ($row->cut_off_date == $db->getNullDate())
		{
			$sql = 'SELECT DATEDIFF(NOW(), event_date) AS number_days FROM #__eb_events WHERE id=' . $eventId;
		}
		else
		{
			$sql = 'SELECT DATEDIFF(NOW(), cut_off_date) AS number_days FROM #__eb_events WHERE id=' . $eventId;
		}
		$db->setQuery($sql);
		$numberDays = $db->loadResult();
		if ($numberDays > 0)
		{
			return false;
		}
		if ($row->event_capacity)
		{
			//Get total registrants for this event
			$sql = 'SELECT SUM(number_registrants) AS total_registrants FROM #__eb_registrants WHERE event_id=' . $eventId .
				 ' AND group_id=0 AND (published=1 OR (payment_method LIKE "os_offline%" AND published != 2))';
			$db->setQuery($sql);
			$numberRegistrants = (int) $db->loadResult();
			if ($numberRegistrants >= $row->event_capacity)
				return false;
		}
		//Check to see whether the current user has registered for the event
		$preventDuplicateRegistration = EventbookingHelper::getConfigValue('prevent_duplicate_registration');
		if ($preventDuplicateRegistration && $user->get('id'))
		{
			$sql = 'SELECT COUNT(id) FROM #__eb_registrants WHERE event_id=' . $eventId . ' AND user_id=' . $user->get('id') .
				 ' AND (published=1 OR (payment_method LIKE "os_offline%" AND published != 2))';
			$db->setQuery($sql);
			$total = $db->loadResult();
			if ($total)
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Get total registrants
	 *
	 */
	public static function getTotalRegistrants($eventId)
	{
		$db = JFactory::getDbo();
		$sql = 'SELECT SUM(number_registrants) AS total_registrants FROM #__eb_registrants WHERE event_id=' . $eventId .
			 ' AND group_id=0 AND (published=1 OR (payment_method LIKE "os_offline%" AND published != 2))';
		$db->setQuery($sql);
		$numberRegistrants = (int) $db->loadResult();
		return $numberRegistrants;
	}

	/**
	 * Get max number of registrants allowed for an event 
	 * @param int $eventId
	 * @param object $config
	 */
	public static function getMaxNumberRegistrants($eventId, $config)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_events')
			->where('id=' . $eventId);
		$db->setQuery($query);
		$event = $db->loadObject();
		$totalRegistrants = EventbookingHelper::getTotalRegistrants($eventId);
		$eventCapacity = (int) $event->event_capacity;
		$maxGroupNumber = (int) $event->max_group_number;
		if ($eventCapacity)
		{
			$maxRegistrants = $eventCapacity - $totalRegistrants;
		}
		else
		{
			$maxRegistrants = -1;
		}
		if ($maxGroupNumber)
		{
			if ($maxRegistrants == -1)
			{
				$maxRegistrants = $maxGroupNumber;
			}
			else
			{
				$maxRegistrants = $maxRegistrants > $maxGroupNumber ? $maxGroupNumber : $maxRegistrants;
			}
		}
		
		if ($maxRegistrants == -1)
		{
			//Default max registrants, we should only allow smaller than 10 registrants to make the form not too long
			$maxRegistrants = 10;
		}
		return $maxRegistrants;
	}

	/**
	 * Get registration rate for group registration
	 *
	 * @param int $eventId
	 * @param int $numberRegistrants
	 * @return 
	 */
	public static function getRegistrationRate($eventId, $numberRegistrants)
	{
		$db = JFactory::getDbo();
		$sql = 'SELECT price FROM #__eb_event_group_prices WHERE event_id=' . $eventId . ' AND registrant_number <= ' . $numberRegistrants .
			 ' ORDER BY registrant_number DESC LIMIT 1';
		$db->setQuery($sql);
		$rate = $db->loadResult();
		if (!$rate)
		{
			$sql = 'SELECT individual_price FROM #__eb_events WHERE id=' . $eventId;
			$db->setQuery($sql);
			$rate = $db->loadResult();
		}
		return $rate;
	}

	/**
	 * Check to see whether the ideal payment plugin installed and activated
	 * @return boolean	 
	 */
	public static function idealEnabled()
	{
		$db = JFactory::getDbo();
		$sql = 'SELECT COUNT(id) FROM #__eb_payment_plugins WHERE name="os_ideal" AND published=1';
		$db->setQuery($sql);
		$total = $db->loadResult();
		if ($total)
		{
			require_once JPATH_COMPONENT . '/payments/ideal/ideal.class.php';
			return true;
		}
		else
		{
			return false;
		}
	}

	/**	 
	 * Get list of banks for ideal payment plugin
	 * @return array
	 */
	public static function getBankLists()
	{
		$idealPlugin = os_payments::loadPaymentMethod('os_ideal');
		$params = new JRegistry($idealPlugin->params);
		$partnerId = $params->get('partner_id');
		$ideal = new iDEAL_Payment($partnerId);
		if (!$params->get('ideal_mode', 0))
		{
			$ideal->setTestmode(true);
		}
		$bankLists = $ideal->getBanks();
		return $bankLists;
	}

	/**
	 * Send notification emails to waiting list users when someone cancel registration
	 *
	 * @param $row
	 * @param $config
	 */
	public static function notifyWaitingList($row, $config)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_waiting_lists')
			->where('event_id='.(int)$row->event_id);
		$db->setQuery($query);
		$registrants = $db->loadObjectList();
		if (count($registrants))
		{
			$mailer = JFactory::getMailer();
			if ($config->from_name)
			{
				$fromName = $config->from_name;
			}
			else
			{
				$fromName = JFactory::getConfig()->get('fromname');
			}
			if ($config->from_email)
			{
				$fromEmail = $config->from_email;
			}
			else
			{
				$fromEmail = JFactory::getConfig()->get('mailfrom');
			}
			$message = EventbookingHelper::getMessages();
			$fieldSuffix = EventbookingHelper::getFieldSuffix();
			$replaces = array();
			$replaces['registrant_first_name'] = $row->first_name;
			$replaces['registrant_last_name'] = $row->last_name;
			if (JFactory::getApplication()->isSite())
			{
				$replaces['event_url'] = JUri::getInstance()->toString(array('scheme', 'user', 'pass', 'host')).JRoute::_(EventbookingHelperRoute::getEventRoute($row->event_id, 0, EventbookingHelper::getItemid()));
			}
			else
			{
				$replaces['event_url'] = JUri::getInstance()->toString(array('scheme', 'user', 'pass', 'host')).EventbookingHelperRoute::getEventRoute($row->event_id, 0, EventbookingHelper::getItemid());
			}
			$query->clear();
			$query->select('*')
				->from('#__eb_events')
				->where('id = ' . (int)$row->event_id);
			$db->setQuery($query);
			$rowEvent = $db->loadObject();
			$replaces['event_title'] = $rowEvent->title;
			$replaces['event_date'] = JHtml::_('date', $rowEvent->event_date, $config->event_date_format, null);
			$replaces['event_end_date'] = JHtml::_('date', $rowEvent->event_end_date, $config->event_date_format, null);

			if (strlen(trim($message->{'registrant_waitinglist_notification_subject' . $fieldSuffix})))
			{
				$subject = $message->{'registrant_waitinglist_notification_subject' . $fieldSuffix};
			}
			else
			{
				$subject = $message->registrant_waitinglist_notification_subject;
			}
			if (empty($subject))
			{
				//Admin has not entered email subject and email message for notification yet, simply return
				return false;
			}
			if (strlen(trim(strip_tags($message->{'registrant_waitinglist_notification_body' . $fieldSuffix}))))
			{
				$body = $message->{'registrant_waitinglist_notification_body' . $fieldSuffix};
			}
			else
			{
				$body = $message->registrant_waitinglist_notification_body;
			}
			foreach($registrants as $registrant)
			{
				$message = $body;
				$replaces['first_name'] = $registrant->first_name;
				$replaces['last_name'] = $registrant->last_name;
				foreach ($replaces as $key => $value)
				{
					$key = strtoupper($key);
					$subject = str_replace("[$key]", $value, $subject);
					$message = str_replace("[$key]", $value, $message);
				}
				//Send email to waiting list users
				$mailer->sendMail($fromEmail, $fromName, $registrant->email, $subject, $message, 1);
				$mailer->ClearAllRecipients();
			}
		}
	}

	/**
	 * Helper function for sending emails to registrants and administrator
	 *
	 * @param RegistrantEventBooking $row
	 * @param object $config
	 */
	public static function sendEmails($row, $config)
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$message = self::getMessages();
		$fieldSuffix = self::getFieldSuffix($row->language);
		$mailer = JFactory::getMailer();
		if ($config->from_name)
		{
			$fromName = $config->from_name;
		}
		else
		{
			$fromName = $app->getCfg('from_name');
		}
		if ($config->from_email)
		{
			$fromEmail = $config->from_email;
		}
		else
		{
			$fromEmail = $app->getCfg('mailfrom');
		}
		$query->select('*')
			->from('#__eb_events')
			->where('id=' . $row->event_id);
		$db->setQuery($query);
		$event = $db->loadObject();
		if ($config->multiple_booking)
		{
			$rowFields = self::getFormFields($row->id, 4);
		}
		elseif ($row->is_group_billing)
		{
			$rowFields = self::getFormFields($row->event_id, 1);
		}
		else
		{
			$rowFields = self::getFormFields($row->event_id, 0);
		}
		$form = new RADForm($rowFields);
		$data = self::getRegistrantData($row, $rowFields);
		$form->bind($data);
		//Need to over-ridde some config options				
		$emailContent = EventbookingHelper::getEmailContent($config, $row, true, $form);
		if ($config->multiple_booking)
		{
			$sql = 'SELECT event_id FROM #__eb_registrants WHERE id=' . $row->id . ' OR cart_id=' . $row->id . ' ORDER BY id';
			$db->setQuery($sql);
			$eventIds = $db->loadColumn();
			$sql = 'SELECT title FROM #__eb_events WHERE id IN (' . implode(',', $eventIds) . ') ORDER BY FIND_IN_SET(id, "' . implode(',', $eventIds) .
				 '")';
			$db->setQuery($sql);
			$eventTitles = $db->loadColumn();
			$eventTitle = implode(', ', $eventTitles);
		}
		else
		{
			$eventTitle = $event->title;
		}
		$replaces = array();
		$replaces['event_title'] = $eventTitle;
		$replaces['event_date'] = JHtml::_('date', $event->event_date, $config->event_date_format, null);
		$replaces['event_end_date'] = JHtml::_('date', $event->event_end_date, $config->event_date_format, null);
		$fields = $form->getFields();
		foreach ($fields as $field)
		{
			if (is_string($field->value) && is_array(json_decode($field->value)))
			{
				$fieldValue = implode(', ', json_decode($field->value));
			}
			else
			{
				$fieldValue = $field->value;
			}
			$replaces[$field->name] = $fieldValue;
		}
		$replaces['transaction_id'] = $row->transaction_id;
		$replaces['date'] = date($config->date_format);
		$replaces['short_description'] = $event->short_description;
		$replaces['description'] = $event->description;
		if ($row->coupon_id)
		{
			$query->clear();
			$query->select('a.code')
				->from('#__eb_coupons AS a')
				->innerJoin('#__eb_registrants AS b ON a.id = b.coupon_id')
				->where('b.id=' . $row->id);
			$db->setQuery($query);
			$data['couponCode'] = $db->loadResult();
		}
		else
		{
			$data['couponCode'] = '';
		}
		if ($config->multiple_booking)
		{
			//Amount calculation
			$sql = 'SELECT SUM(total_amount) FROM #__eb_registrants WHERE id=' . $row->id . ' OR cart_id=' . $row->id;
			$db->setQuery($sql);
			$totalAmount = $db->loadResult();
			
			$sql = 'SELECT SUM(tax_amount) FROM #__eb_registrants WHERE id=' . $row->id . ' OR cart_id=' . $row->id;
			$db->setQuery($sql);
			$taxAmount = $db->loadResult();
			
			$sql = 'SELECT SUM(discount_amount) FROM #__eb_registrants WHERE id=' . $row->id . ' OR cart_id=' . $row->id;
			$db->setQuery($sql);
			$discountAmount = $db->loadResult();
			$amount = $totalAmount - $discountAmount + $taxAmount;
			
			$replaces['total_amount'] = EventbookingHelper::formatCurrency($totalAmount, $config, $event->currency_symbol);
			$replaces['tax_amount'] = EventbookingHelper::formatCurrency($taxAmount, $config, $event->currency_symbol);
			$replaces['discount_amount'] = EventbookingHelper::formatCurrency($discountAmount, $config, $event->currency_symbol);
			$replaces['amount'] = EventbookingHelper::formatCurrency($amount, $config, $event->currency_symbol);
		}
		else
		{
			$replaces['total_amount'] = EventbookingHelper::formatCurrency($row->total_amount, $config, $event->currency_symbol);
			$replaces['tax_amount'] = EventbookingHelper::formatCurrency($row->tax_amount, $config, $event->currency_symbol);
			$replaces['discount_amount'] = EventbookingHelper::formatCurrency($row->discount_amount, $config, $event->currency_symbol);
			$replaces['amount'] = EventbookingHelper::formatCurrency($row->amount, $config, $event->currency_symbol);
		}
		//Add support for location tag
		$query->clear();
		$query->select('a.*')
			->from('#__eb_locations AS a')
			->innerJoin('#__eb_events AS b ON a.id=b.location_id')
			->where('b.id=' . $row->event_id);

		$db->setQuery($query);
		$rowLocation = $db->loadObject();
		if ($rowLocation)
		{
			$locationInformation = array();
			if ($rowLocation->address)
			{
				$locationInformation[] = $rowLocation->address;
			}
			if ($rowLocation->city)
			{
				$locationInformation[] = $rowLocation->city;
			}
			if ($rowLocation->state)
			{
				$locationInformation[] = $rowLocation->state;
			}
			if ($rowLocation->zip)
			{
				$locationInformation[] = $rowLocation->zip;
			}
			if ($rowLocation->country)
			{
				$locationInformation[] = $rowLocation->country;
			}
			$replaces['location'] = $rowLocation->name . ' (' . implode(', ', $locationInformation) . ')';
		}
		else
		{
			$replaces['location'] = '';
		}
		//Notification email send to user
		if (strlen($message->{'user_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'user_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->user_email_subject;
		}
		if (strpos($row->payment_method, 'os_offline') !== false)
		{
			if (strlen(trim(strip_tags($event->user_email_body_offline))))
			{
				$body = $event->user_email_body_offline;
			}
			elseif (strlen($message->{'user_email_body_offline' . $fieldSuffix}))
			{
				$body = $message->{'user_email_body_offline' . $fieldSuffix};
			}
			else
			{
				$body = $message->user_email_body_offline;
			}
		}
		else
		{
			if (strlen(trim(strip_tags($event->user_email_body))))
			{
				$body = $event->user_email_body;
			}
			elseif (strlen($message->{'user_email_body' . $fieldSuffix}))
			{
				$body = $message->{'user_email_body' . $fieldSuffix};
			}
			else
			{
				$body = $message->user_email_body;
			}
		}
		$subject = str_replace('[EVENT_TITLE]', $eventTitle, $subject);
		$body = str_replace('[REGISTRATION_DETAIL]', $emailContent, $body);
		foreach ($replaces as $key => $value)
		{
			$key = strtoupper($key);
			$body = str_replace("[$key]", $value, $body);
		}
		$body = self::convertImgTags($body);
		$attachments = array();
		if ($config->activate_invoice_feature && $config->send_invoice_to_customer && EventbookingHelper::needInvoice($row))
		{
			if (!$row->invoice_number)
			{
				$row->invoice_number = self::getInvoiceNumber();
				$row->store();
			}
			self::generateInvoicePDF($row);
			$attachments[] = JPATH_ROOT . '/media/com_eventbooking/invoices/' . self::formatInvoiceNumber($row->invoice_number, $config) . '.pdf';
		}
		if ($config->multiple_booking)
		{
			$sql = 'SELECT attachment FROM #__eb_events WHERE id IN ('.implode(',', $eventIds).')';
			$db->setQuery($sql);
			$attachmentFiles = $db->loadColumn();
			foreach($attachmentFiles as $attachmentFile)
			{
				if ($attachmentFile)
				{
					$attachments[] = JPATH_ROOT . '/media/com_eventbooking/' . $attachmentFile;
				}
			}	
		}
		else 
		{
			if ($event->attachment)
			{
				$attachments[] = JPATH_ROOT . '/media/com_eventbooking/' . $event->attachment;				
			}	
		}

		//Generate and send ics file to registrants
		if ($config->send_ics_file)
		{
			$ics = new EventbookingHelperIcs();
			$ics->setName($event->title)
				->setDescription($event->description ? $event->description : $event->short_description)
				->setOrganizer($fromEmail, $fromName)
				->setStart($event->event_date)
				->setEnd($event->event_end_date);
			if ($rowLocation)
			{
				$ics->setLocation($rowLocation->name);
			}
			$attachments[] = $ics->save(JPATH_ROOT.'/media/com_eventbooking/icsfiles/');
		}

		$mailer->sendMail($fromEmail, $fromName, $row->email, $subject, $body, 1, null, null, $attachments);
		$mailer->ClearAttachments();
		if ($config->send_email_to_group_members && $row->is_group_billing)
		{
			$sql = 'SELECT * FROM #__eb_registrants WHERE group_id=' . $row->id;
			$db->setQuery($sql);
			$rowMembers = $db->loadObjectList();
			if (count($rowMembers))
			{
				$memberReplaces = array();
				$memberReplaces['event_title'] = $replaces['event_title'];
				$memberReplaces['event_date'] = $replaces['event_date'];
				$memberReplaces['transaction_id'] = $replaces['transaction_id'];
				$memberReplaces['date'] = $replaces['date'];
				$memberReplaces['short_description'] = $replaces['short_description'];
				$memberReplaces['description'] = $replaces['short_description'];
				$memberReplaces['location'] = $replaces['location'];
				$memberFormFields = self::getFormFields($row->event_id, 2);
				foreach ($rowMembers as $rowMember)
				{
					if (!$rowMember->email)
					{
						continue;
					}
					if (strlen($message->{'group_member_email_subject' . $fieldSuffix}))
					{
						$subject = $message->{'group_member_email_subject' . $fieldSuffix};
					}
					else
					{
						$subject = $message->group_member_email_subject;
					}
					$subject = str_replace('[EVENT_TITLE]', $eventTitle, $subject);
					if (strlen(strip_tags($message->{'group_member_email_body' . $fieldSuffix})))
					{
						$body = $message->{'group_member_email_body' . $fieldSuffix};
					}
					else
					{
						$body = $message->group_member_email_body;
					}
					if (!$subject)
					{
						break;
					}
					if (!$body)
					{
						break;
					}
					//Build the member form
					$memberForm = new RADForm($memberFormFields);
					$memberData = self::getRegistrantData($rowMember, $memberFormFields);
					$memberForm->bind($memberData);
					$fields = $memberForm->getFields();
					foreach ($fields as $field)
					{
						if (is_string($field->value) && is_array(json_decode($field->value)))
						{
							$fieldValue = implode(', ', json_decode($field->value));
						}
						else
						{
							$fieldValue = $field->value;
						}
						$memberReplaces[$field->name] = $fieldValue;
					}
					$memberReplaces['member_detail'] = self::getMemberDetails($config, $rowMember, $event, $rowLocation, true, $memberForm);
					foreach ($memberReplaces as $key => $value)
					{
						$key = strtoupper($key);
						$body = str_replace("[$key]", $value, $body);
						$subject = str_replace("[$key]", $value, $subject);
					}
					$body = self::convertImgTags($body);
					$mailer->ClearAllRecipients();
					$mailer->ClearAttachments();
					$mailer->sendMail($fromEmail, $fromName, $rowMember->email, $subject, $body, 1, null);
				}
			}
		}
		
		//Send emails to notification emails
		if (strlen(trim($event->notification_emails)) > 0)
		{
			$config->notification_emails = $event->notification_emails;
		}
		if ($config->notification_emails == '')
		{
			$notificationEmails = $fromEmail;
		}
		else
		{
			$notificationEmails = $config->notification_emails;
		}
		$notificationEmails = str_replace(' ', '', $notificationEmails);
		$emails = explode(',', $notificationEmails);
		if (strlen($message->{'admin_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'admin_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->admin_email_subject;
		}
		$subject = str_replace('[EVENT_TITLE]', $eventTitle, $subject);
		if (strlen(strip_tags($message->{'admin_email_body' . $fieldSuffix})))
		{
			$body = $message->{'admin_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->admin_email_body;
		}
		$body = str_replace('[REGISTRATION_DETAIL]', $emailContent, $body);
		foreach ($replaces as $key => $value)
		{
			$key = strtoupper($key);
			$body = str_replace("[$key]", $value, $body);
		}
		$body = self::convertImgTags($body);
		for ($i = 0, $n = count($emails); $i < $n; $i++)
		{
			$email = $emails[$i];
			$mailer->ClearAllRecipients();
			if ($email)
			{
				$mailer->sendMail($fromEmail, $fromName, $email, $subject, $body, 1);
			}
		}
	}

	/**
	 * Helper function for sending emails to registrants and administrator
	 *
	 * @param RegistrantEventBooking $row
	 * @param object $config
	 */
	public static function sendRegistrationApprovedEmail($row, $config)
	{
		$app = JFactory::getApplication();
		$mailer = JFactory::getMailer();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		EventbookingHelper::loadLanguage();
		$message = self::getMessages();
		$fieldSuffix = self::getFieldSuffix($row->language);
		if ($config->from_name)
		{
			$fromName = $config->from_name;
		}
		else
		{
			$fromName = $app->getCfg('fromname');
		}
		if ($config->from_email)
		{
			$fromEmail = $config->from_email;
		}
		else
		{
			$fromEmail = $app->getCfg('mailfrom');
		}
		if ($config->multiple_booking)
		{
			$rowFields = self::getFormFields($row->id, 4);
		}
		elseif ($row->is_group_billing)
		{
			$rowFields = self::getFormFields($row->event_id, 1);
		}
		else
		{
			$rowFields = self::getFormFields($row->event_id, 0);
		}
		$form = new RADForm($rowFields);
		$data = self::getRegistrantData($row, $rowFields);
		$form->bind($data);
		//Need to over-ridde some config options
		$emailContent = EventbookingHelper::getEmailContent($config, $row, true, $form);
		$query->select('*')
			->from('#__eb_events')
			->where('id=' . $row->event_id);
		$db->setQuery($query);
		$event = $db->loadObject();
		if ($config->multiple_booking)
		{
			$sql = 'SELECT event_id FROM #__eb_registrants WHERE id=' . $row->id . ' OR cart_id=' . $row->id . ' ORDER BY id';
			$db->setQuery($sql);
			$eventIds = $db->loadColumn();
			$sql = 'SELECT title FROM #__eb_events WHERE id IN (' . implode(',', $eventIds) . ') ORDER BY FIND_IN_SET(id, "' . implode(',', $eventIds) .
				 '")';
			$db->setQuery($sql);
			$eventTitles = $db->loadColumn();
			$eventTitle = implode(', ', $eventTitles);
		}
		else
		{
			$eventTitle = $event->title;
		}
		$replaces = array();
		$replaces['event_title'] = $eventTitle;
		$replaces['event_date'] = JHtml::_('date', $event->event_date, $config->event_date_format, null);
		$fields = $form->getFields();
		foreach ($fields as $field)
		{
			if (is_string($field->value) && is_array(json_decode($field->value)))
			{
				$fieldValue = implode(', ', json_decode($field->value));
			}
			else
			{
				$fieldValue = $field->value;
			}
			$replaces[$field->name] = $fieldValue;
		}
		$replaces['transaction_id'] = $row->transaction_id;
		$replaces['amount'] = EventbookingHelper::formatCurrency($row->amount, $config, $event->currency_symbol);
		
		//Add support for location tag
		$sql = 'SELECT a.* FROM #__eb_locations AS a ' . ' INNER JOIN #__eb_events AS b ' . ' ON a.id = b.location_id ' . ' WHERE b.id =' .
			 $row->event_id;
		;
		$db->setQuery($sql);
		$rowLocation = $db->loadObject();
		if ($rowLocation)
		{
			$replaces['location'] = $rowLocation->name . ' (' . $rowLocation->address . ', ' . $rowLocation->city . ',' . $rowLocation->state . ', ' .
				 $rowLocation->zip . ', ' . $rowLocation->country . ')';
		}
		else
		{
			$replaces['location'] = '';
		}
		if (strlen(trim($event->registration_approved_email_subject)))
		{
			$subject = $event->registration_approved_email_subject;
		}
		elseif (strlen($message->{'registration_approved_email_subject' . $fieldSuffix}))
		{
			$subject = $message->{'registration_approved_email_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->registration_approved_email_subject;
		}
		
		if (strlen(trim(strip_tags($event->registration_approved_email_body))))
		{
			$body = $event->registration_approved_email_body;
		}
		elseif (strlen($message->{'registration_approved_email_body' . $fieldSuffix}))
		{
			$body = $message->{'registration_approved_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->registration_approved_email_body;
		}
		
		$subject = str_replace('[EVENT_TITLE]', $eventTitle, $subject);
		$body = str_replace('[REGISTRATION_DETAIL]', $emailContent, $body);
		foreach ($replaces as $key => $value)
		{
			$key = strtoupper($key);
			$body = str_replace("[$key]", $value, $body);
		}
		$body = self::convertImgTags($body);
		$mailer->sendMail($fromEmail, $fromName, $row->email, $subject, $body, 1);
	}

	/**
	 * Send email when users fill-in waitinglist
	 * 
	 * @param  object $row
	 * @param object $config
	 */
	public static function sendWaitinglistEmail($row, $config)
	{
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$mailer = JFactory::getMailer();
		$message = self::getMessages();
		$fieldSuffix = self::getFieldSuffix($row->language);
		if ($config->from_name)
		{
			$fromName = $config->from_name;
		}
		else
		{
			$fromName = $app->getCfg('fromname');
		}
		if ($config->from_email)
		{
			$fromEmail = $config->from_email;
		}
		else
		{
			$fromEmail = $app->getCfg('mailfrom');
		}
		$sql = "SELECT * FROM #__eb_events WHERE id=" . $row->event_id;
		$db->setQuery($sql);
		$event = $db->loadObject();
		//Supported tags
		$replaces = array();
		$replaces['event_title'] = $event->title;
		$replaces['first_name'] = $row->first_name;
		$replaces['last_name'] = $row->last_name;
		$replaces['organization'] = $row->organization;
		$replaces['address'] = $row->address;
		$replaces['address2'] = $row->address;
		$replaces['city'] = $row->city;
		$replaces['state'] = $row->state;
		$replaces['zip'] = $row->zip;
		$replaces['country'] = $row->country;
		$replaces['phone'] = $row->phone;
		$replaces['fax'] = $row->phone;
		$replaces['email'] = $row->email;
		$replaces['comment'] = $row->comment;
		$replaces['number_registrants'] = $row->number_registrants;
		//Notification email send to user
		if (strlen($message->{'watinglist_confirmation_subject' . $fieldSuffix}))
		{
			$subject = $message->{'watinglist_confirmation_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->watinglist_confirmation_subject;
		}
		if (strlen(strip_tags($message->{'watinglist_confirmation_body' . $fieldSuffix})))
		{
			$body = $message->{'watinglist_confirmation_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->watinglist_confirmation_body;
		}
		$subject = str_replace('[EVENT_TITLE]', $event->title, $subject);
		foreach ($replaces as $key => $value)
		{
			$key = strtoupper($key);
			$body = str_replace("[$key]", $value, $body);
		}
		$mailer->sendMail($fromEmail, $fromName, $row->email, $subject, $body, 1);
		//Send emails to notification emails
		if (strlen(trim($event->notification_emails)) > 0)
			$config->notification_emails = $event->notification_emails;
		if ($config->notification_emails == '')
			$notificationEmails = $fromEmail;
		else
			$notificationEmails = $config->notification_emails;
		$notificationEmails = str_replace(' ', '', $notificationEmails);
		$emails = explode(',', $notificationEmails);
		
		if (strlen($message->{'watinglist_notification_subject' . $fieldSuffix}))
		{
			$subject = $message->{'watinglist_notification_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->watinglist_notification_subject;
		}
		if (strlen(strip_tags($message->{'watinglist_notification_body' . $fieldSuffix})))
		{
			$body = $message->{'watinglist_notification_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->watinglist_notification_body;
		}
		$subject = str_replace('[EVENT_TITLE]', $event->title, $subject);
		foreach ($replaces as $key => $value)
		{
			$key = strtoupper($key);
			$body = str_replace("[$key]", $value, $body);
		}
		$body = self::convertImgTags($body);
		for ($i = 0, $n = count($emails); $i < $n; $i++)
		{
			$email = $emails[$i];
			$mailer->ClearAllRecipients();
			$mailer->sendMail($fromEmail, $fromName, $email, $subject, $body, 1);
		}
	}

	/**
	 * Get country code
	 *
	 * @param string $countryName
	 * @return string
	 */
	public static function getCountryCode($countryName)
	{
		$db = JFactory::getDbo();
		$sql = 'SELECT country_2_code FROM #__eb_countries WHERE LOWER(name)="' . JString::strtolower($countryName) . '"';
		$db->setQuery($sql);
		$countryCode = $db->loadResult();
		if (!$countryCode)
			$countryCode = 'US';
		return $countryCode;
	}

	/**
	 * Get color code of an event based on in category
	 * @param int $eventId
	 * @return Array
	 */
	public static function getColorCodeOfEvent($eventId)
	{
		static $colors;
		if (!isset($colors[$eventId]))
		{
			$db = JFactory::getDbo();
			$sql = 'SELECT color_code FROM #__eb_categories AS a INNER JOIN #__eb_event_categories AS b ON a.id = b.category_id WHERE b.event_id=' .
				 $eventId . ' ORDER BY b.id DESC';
			$db->setQuery($sql);
			$colors[$eventId] = $db->loadResult();
		}
		
		return $colors[$eventId];
	}

	/**
	 * Get title of the given payment method
	 * @param string $methodName
	 */
	public static function getPaymentMethodTitle($methodName)
	{
		static $titles;
		if (!isset($titles[$methodName]))
		{
			$db = JFactory::getDbo();
			$sql = 'SELECT title FROM #__eb_payment_plugins WHERE name="' . $methodName . '"';
			$db->setQuery($sql);
			$methodTitle = $db->loadResult();
			if ($methodTitle)
			{
				$titles[$methodName] = $methodTitle;
			}
			else
			{
				$titles[$methodName] = $methodName;
			}
		}
		
		return $titles[$methodName];
	}

	/**
	 * Display copy right information
	 *
	 */
	public static function displayCopyRight()
	{
		echo '<div class="copyright" style="text-align:center;margin-top: 5px;"><a href="http://joomdonation.com/joomla-extensions/events-booking-joomla-events-registration.html" target="_blank"><strong>Event Booking</strong></a> version ' .
			 self::getInstalledVersion() . ', Copyright (C) 2010 - ' . date('Y') .
			 ' <a href="http://joomdonation.com" target="_blank"><strong>Ossolution Team</strong></a></div>';
	}

	/**
     * Load jquery library
     */
	public static function loadJQuery()
	{		
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			JHtml::_('jquery.framework');
		}
		else
		{
			$document = JFactory::getDocument();
			$document->addScript(JUri::root(true) . '/components/com_eventbooking/assets/bootstrap/js/jquery.min.js');
			$document->addScript(JUri::root(true) . '/components/com_eventbooking/assets/bootstrap/js/jquery-noconflict.js');
		}
	}

	/**
	 * Load bootstrap css and javascript file
	 */
	public static function loadBootstrap($loadJs = true)
	{
		$app = JFactory::getApplication();
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			JHtml::_('bootstrap.loadCss');
			if ($loadJs && $app->isAdmin())
			{
				JHtml::_('bootstrap.framework');
			}
			elseif ($loadJs && $app->isSite())
			{
				JHtml::_('script', 'jui/bootstrap.min.js', false, true, false, false, false);
			}
		}
		else
		{
			$document = JFactory::getDocument();
			$document->addStyleSheet(JUri::root(true) . '/components/com_eventbooking/assets/bootstrap/css/bootstrap.css');
			if ($loadJs && $app->isAdmin())
			{
				$document->addScript(JUri::root(true) . '/components/com_eventbooking/assets/bootstrap/js/jquery.min.js');
				$document->addScript(JUri::root(true) . '/components/com_eventbooking/assets/bootstrap/js/jquery-noconflict.js');
				$document->addScript(JUri::root(true) . '/components/com_eventbooking/assets/bootstrap/js/bootstrap.min.js');
			}
			elseif ($loadJs && $app->isSite())
			{
				$document->addScript(JUri::root(true) . '/components/com_eventbooking/assets/bootstrap/js/bootstrap.min.js');
			}
		}
	}

	/**
	 * Get version number of GD version installed
	 * Enter description here ...
	 * @param unknown_type $user_ver
	 */
	public static function getGDVersion($user_ver = 0)
	{
		if (!extension_loaded('gd'))
		{
			return 0;
		}
		
		static $gd_ver = 0;
		
		// just accept the specified setting if it's 1.
		if ($user_ver == 1)
		{
			$gd_ver = 1;
			return 1;
		}
		
		// use static variable if function was cancelled previously.
		if ($user_ver != 2 && $gd_ver > 0)
		{
			return $gd_ver;
		}
		
		// use the gd_info() function if posible.
		if (function_exists('gd_info'))
		{
			$ver_info = gd_info();
			$match = null;
			preg_match('/\d/', $ver_info['GD Version'], $match);
			$gd_ver = $match[0];
			
			return $match[0];
		}
		
		// if phpinfo() is disabled use a specified / fail-safe choice...
		if (preg_match('/phpinfo/', ini_get('disable_functions')))
		{
			if ($user_ver == 2)
			{
				$gd_ver = 2;
				return 2;
			}
			else
			{
				$gd_ver = 1;
				return 1;
			}
		}
		// ...otherwise use phpinfo().
		ob_start();
		phpinfo(8);
		$info = ob_get_contents();
		ob_end_clean();
		$info = stristr($info, 'gd version');
		$match = null;
		preg_match('/\d/', $info, $match);
		$gd_ver = $match[0];
		
		return $match[0];
	}

	/**
	 * 
	 * Resize image to a pre-defined size
	 * @param string $srcFile
	 * @param string $desFile
	 * @param int $thumbWidth
	 * @param int $thumbHeight
	 * @param string $method gd1 or gd2
	 * @param int $quality
	 */
	public static function resizeImage($srcFile, $desFile, $thumbWidth, $thumbHeight, $quality)
	{
		$app = JFactory::getApplication();
		$imgTypes = array(
			1 => 'GIF', 
			2 => 'JPG', 
			3 => 'PNG', 
			4 => 'SWF', 
			5 => 'PSD', 
			6 => 'BMP', 
			7 => 'TIFF', 
			8 => 'TIFF', 
			9 => 'JPC', 
			10 => 'JP2', 
			11 => 'JPX', 
			12 => 'JB2', 
			13 => 'SWC', 
			14 => 'IFF');
		$imgInfo = getimagesize($srcFile);
		if ($imgInfo == null)
		{
			$app->enqueueMessage(JText::_('EB_IMAGE_NOT_FOUND', 'error'));
			return false;
		}
		$type = strtoupper($imgTypes[$imgInfo[2]]);
		$gdSupportedTypes = array('JPG', 'PNG', 'GIF');
		if (!in_array($type, $gdSupportedTypes))
		{
			$app->enqueueMessage(JText::_('EB_ONLY_SUPPORT_TYPES'), 'error');
			return false;
		}
		$srcWidth = $imgInfo[0];
		$srcHeight = $imgInfo[1];
		//Should canculate the ration	        	        	        
		$ratio = max($srcWidth / $thumbWidth, $srcHeight / $thumbHeight, 1.0);
		$desWidth = (int) $srcWidth / $ratio;
		$desHeight = (int) $srcHeight / $ratio;
		$gdVersion = EventbookingHelper::getGDVersion();
		if ($gdVersion <= 0)
		{
			//Simply copy the source to target folder
			jimport('joomla.filesystem.file');
			JFile::copy($srcFile, $desFile);
			return false;
		}
		else
		{
			if ($gdVersion == 1)
			{
				$method = 'gd1';
			}
			else
			{
				$method = 'gd2';
			}
		}
		switch ($method)
		{
			case 'gd1':
				if ($type == 'JPG')
					$srcImage = imagecreatefromjpeg($srcFile);
				elseif ($type == 'PNG')
					$srcImage = imagecreatefrompng($srcFile);
				else
					$srcImage = imagecreatefromgif($srcFile);
				$desImage = imagecreate($desWidth, $desHeight);
				imagecopyresized($desImage, $srcImage, 0, 0, 0, 0, $desWidth, $desHeight, $srcWidth, $srcHeight);
				imagejpeg($desImage, $desFile, $quality);
				imagedestroy($srcImage);
				imagedestroy($desImage);
				break;
			case 'gd2':
				if (!function_exists('imagecreatefromjpeg'))
				{
					echo JText::_('GD_LIB_NOT_INSTALLED');
					return false;
				}
				if (!function_exists('imagecreatetruecolor'))
				{
					echo JText::_('GD2_LIB_NOT_INSTALLED');
					return false;
				}
				if ($type == 'JPG')
					$srcImage = imagecreatefromjpeg($srcFile);
				elseif ($type == 'PNG')
					$srcImage = imagecreatefrompng($srcFile);
				else
					$srcImage = imagecreatefromgif($srcFile);
				if (!$srcImage)
				{
					echo JText::_('JA_INVALID_IMAGE');
					return false;
				}
				$desImage = imagecreatetruecolor($desWidth, $desHeight);
				imagecopyresampled($desImage, $srcImage, 0, 0, 0, 0, $desWidth, $desHeight, $srcWidth, $srcHeight);
				imagejpeg($desImage, $desFile, $quality);
				imagedestroy($srcImage);
				imagedestroy($desImage);
				break;
		}
		
		return true;
	}

	/**
	 * Calcuate total discount for the registration
	 * @return decimal
	 */
	function calcuateDiscount()
	{
		return 10;
	}

	/**
	 * Generate User Input Select
	 * @param int $userId
	 */
	public static function getUserInput($userId, $fieldName = 'user_id')
	{
		// Initialize variables.
		$html = array();
		$link = 'index.php?option=com_users&amp;view=users&amp;layout=modal&amp;tmpl=component&amp;field=user_id';
		// Initialize some field attributes.
		$attr = ' class="inputbox"';
		// Load the modal behavior script.
		JHtml::_('behavior.modal', 'a.modal_user_id');
		// Build the script.
		$script = array();
		$script[] = '	function jSelectUser_user_id(id, title) {';
		$script[] = '		var old_id = document.getElementById("user_id").value;';
		$script[] = '		if (old_id != id) {';
		$script[] = '			document.getElementById("' . $fieldName . '").value = id;';
		$script[] = '			document.getElementById("user_id_name").value = title;';
		$script[] = '		}';
		$script[] = '		SqueezeBox.close();';
		$script[] = '	}';
		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
		// Load the current username if available.
		$table = JTable::getInstance('user');
		if ($userId)
		{
			$table->load($userId);
		}
		else
		{
			$table->name = '';
		}
		// Create a dummy text field with the user name.
		$html[] = '<div class="fltlft">';
		$html[] = '	<input type="text" id="user_id_name"' . ' value="' . htmlspecialchars($table->name, ENT_COMPAT, 'UTF-8') . '"' .
			 ' disabled="disabled"' . $attr . ' />';
		$html[] = '</div>';
		// Create the user select button.
		$html[] = '<div class="button2-left">';
		$html[] = '<div class="blank">';
		$html[] = '<a class="modal_user_id" title="' . JText::_('JLIB_FORM_CHANGE_USER') . '"' . ' href="' . $link . '"' .
			 ' rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
		$html[] = '	' . JText::_('JLIB_FORM_CHANGE_USER') . '</a>';
		$html[] = '</div>';
		$html[] = '</div>';
		// Create the real field, hidden, that stored the user id.
		$html[] = '<input type="hidden" id="' . $fieldName . '" name="' . $fieldName . '" value="' . $userId . '" />';
		
		return implode("\n", $html);
	}

	/**
	 * Get the invoice number for this subscription record
	 */
	public static function getInvoiceNumber()
	{
		$db = JFactory::getDbo();
		$sql = 'SELECT MAX(invoice_number) FROM #__eb_registrants';
		$db->setQuery($sql);
		$invoiceNumber = (int) $db->loadResult();
		if (!$invoiceNumber)
		{
			$invoiceNumber = (int) self::getConfigValue('invoice_start_number');
			if (!$invoiceNumber)
			{
				$invoiceNumber = 1;
			}
		}
		else
		{
			$invoiceNumber++;
		}
		
		return $invoiceNumber;
	}

	/**
	 * Format invoice number
	 * @param string $invoiceNumber
	 * @param Object $config
	 */
	public static function formatInvoiceNumber($invoiceNumber, $config)
	{
		return $config->invoice_prefix .
			 str_pad($invoiceNumber, $config->invoice_number_length ? $config->invoice_number_length : 4, '0', STR_PAD_LEFT);
	}

	/**
	 * Generate invoice PDF
	 * @param object $row
	 */
	public static function generateInvoicePDF($row)
	{
		self::loadLanguage();
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$config = self::getConfig();
		$sitename = $app->getCfg("sitename");
		$sql = 'SELECT * FROM #__eb_events WHERE id=' . $row->event_id;
		$db->setQuery($sql);
		$rowEvent = $db->loadObject();
		require_once JPATH_ROOT . "/components/com_eventbooking/tcpdf/tcpdf.php";
		require_once JPATH_ROOT . "/components/com_eventbooking/tcpdf/config/lang/eng.php";
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor($sitename);
		$pdf->SetTitle('Invoice');
		$pdf->SetSubject('Invoice');
		$pdf->SetKeywords('Invoice');
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetMargins(PDF_MARGIN_LEFT, 0, PDF_MARGIN_RIGHT);
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetFont('times', '', 8);
		$pdf->AddPage();
		if ($config->multiple_booking)
		{
			$invoiceOutput = $config->invoice_format_cart;
		}
		else
		{
			$invoiceOutput = $config->invoice_format;
		}
		$replaces = array();
		$replaces['name'] = $row->first_name . ' ' . $row->last_name;
		$replaces['email'] = $row->email;
		$replaces['organization'] = $row->organization;
		$replaces['address'] = $row->address;
		$replaces['address2'] = $row->address2;
		$replaces['city'] = $row->city;
		$replaces['state'] = $row->state;
		$replaces['zip'] = $row->zip;
		$replaces['country'] = $row->country;
		$replaces['phone'] = $row->phone;
		$replaces['fax'] = $row->fax;
		$replaces['invoice_number'] = self::formatInvoiceNumber($row->invoice_number, $config);
		$replaces['invoice_date'] = date($config->date_format);
		$replaces['transaction_id'] = $row->transaction_id;
		$replaces['short_description'] = $rowEvent->short_description;
		$replaces['description'] = $rowEvent->description;
		$replaces['event_title'] = $rowEvent->title;
		$replaces['event_date'] = JHtml::_('date', $rowEvent->event_date, $config->event_date_format, null);
		
		if ($config->multiple_booking)
		{
			$rowFields = self::getFormFields($row->id, 4);
		}
		elseif ($row->is_group_billing)
		{
			$rowFields = self::getFormFields($row->event_id, 1);
		}
		else
		{
			$rowFields = self::getFormFields($row->event_id, 0);
		}
		
		$sql = 'SELECT field_id, field_value FROM #__eb_field_values WHERE registrant_id=' . $row->id;
		$db->setQuery($sql);
		$rowFieldValues = $db->loadObjectList();
		$fieldValues = array();
		if (count($rowFieldValues))
		{			
			foreach ($rowFieldValues as $rowFieldValue)
			{
				$fieldValues[$rowFieldValue->field_id] = $rowFieldValue->field_value;								
			}
		}
		
		foreach ($rowFields as $rowField)
		{
			if (isset($fieldValues[$rowField->id]))
			{
				$replaces[strtoupper($rowField->name)] = $fieldValues[$rowField->id];
			}
			else 
			{
				$replaces[strtoupper($rowField->name)] = '';
			}
		}
		
		if ($row->published == 0)
		{
			$invoiceStatus = JText::_('EB_INVOICE_STATUS_PENDING');
		}
		elseif ($row->published == 1)
		{
			$invoiceStatus = JText::_('EB_INVOICE_STATUS_PAID');
		}
		else
		{
			$invoiceStatus = JText::_('EB_INVOICE_STATUS_UNKNOWN');
		}
		$replaces['INVOICE_STATUS'] = $invoiceStatus;
		if ($config->multiple_booking)
		{
			$sql = 'SELECT a.title, a.event_date, b.* FROM #__eb_events AS a INNER JOIN #__eb_registrants AS b ' . ' ON a.id = b.event_id ' .
				 ' WHERE b.id=' . $row->id . ' OR b.cart_id=' . $row->id;
			$db->setQuery($sql);
			$rowEvents = $db->loadObjectList();
			
			$sql = 'SELECT SUM(total_amount) FROM #__eb_registrants WHERE id=' . $row->id . ' OR cart_id=' . $row->id;
			$db->setQuery($sql);
			$subTotal = $db->loadResult();
			
			$sql = 'SELECT SUM(tax_amount) FROM #__eb_registrants WHERE id=' . $row->id . ' OR cart_id=' . $row->id;
			$db->setQuery($sql);
			$taxAmount = $db->loadResult();
			
			$sql = 'SELECT SUM(discount_amount) FROM #__eb_registrants WHERE id=' . $row->id . ' OR cart_id=' . $row->id;
			$db->setQuery($sql);
			$discountAmount = $db->loadResult();
			$total = $subTotal - $discountAmount + $taxAmount;
			$replaces['EVENTS_LIST'] = EventbookingHelperHtml::loadCommonLayout(
				JPATH_ROOT . '/components/com_eventbooking/emailtemplates/invoice_items.php', 
				array(
					'rowEvents' => $rowEvents, 
					'subTotal' => $subTotal, 
					'taxAmount' => $taxAmount, 
					'discountAmount' => $discountAmount, 
					'total' => $total, 
					'config' => $config));
			$replaces['SUB_TOTAL'] = EventbookingHelper::formatCurrency($subTotal, $config);
			$replaces['DISCOUNT_AMOUNT'] = EventbookingHelper::formatCurrency($discountAmount, $config);
			$replaces['TAX_AMOUNT'] = EventbookingHelper::formatCurrency($taxAmount, $config);
			$replaces['TOTAL_AMOUNT'] = EventbookingHelper::formatCurrency($total, $config);
		}
		else
		{
			$replaces['ITEM_QUANTITY'] = 1;
			$replaces['ITEM_AMOUNT'] = $replaces['ITEM_SUB_TOTAL'] = self::formatCurrency($row->total_amount, $config);
			$replaces['DISCOUNT_AMOUNT'] = self::formatCurrency($row->discount_amount, $config);
			$replaces['SUB_TOTAL'] = self::formatCurrency($row->total_amount - $row->discount_amount, $config);
			$replaces['TAX_AMOUNT'] = self::formatCurrency($row->tax_amount, $config);
			$replaces['TOTAL_AMOUNT'] = self::formatCurrency($row->total_amount - $row->discount_amount + $row->tax_amount, $config);
			$itemName = JText::_('EB_EVENT_REGISTRATION');
			$itemName = str_replace('[EVENT_TITLE]', $rowEvent->title, $itemName);
			$replaces['ITEM_NAME'] = $itemName;
		}
		foreach ($replaces as $key => $value)
		{
			$key = strtoupper($key);
			$invoiceOutput = str_replace("[$key]", $value, $invoiceOutput);
		}
		
		$invoiceOutput = self::convertImgTags($invoiceOutput);
		$v = $pdf->writeHTML($invoiceOutput, true, false, false, false, '');
		//Filename
		$filePath = JPATH_ROOT . '/media/com_eventbooking/invoices/' . $replaces['invoice_number'] . '.pdf';
		$pdf->Output($filePath, 'F');
	}

	public static function downloadInvoice($id)
	{
		JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_eventbooking/tables');
		$config = self::getConfig();
		$row = JTable::getInstance('EventBooking', 'Registrant');
		$row->load($id);
		$invoiceStorePath = JPATH_ROOT . '/media/com_eventbooking/invoices/';
		if ($row)
		{
			if (!$row->invoice_number)
			{
				$row->invoice_number = self::getInvoiceNumber();
				$row->store();
			}
			$invoiceNumber = self::formatInvoiceNumber($row->invoice_number, $config);
			if ($row->payment_method == 'os_offline' || !file_exists($invoiceStorePath . $invoiceNumber . '.pdf'))
			{
				self::generateInvoicePDF($row);
			}
			$invoicePath = $invoiceStorePath . $invoiceNumber . '.pdf';
			$fileName = $invoiceNumber . '.pdf';
			while (@ob_end_clean());
			self::processDownload($invoicePath, $fileName);
		}
	}

	/**
	 * Convert all img tags to use absolute URL
	 * @param string $html_content
	 */
	public static function convertImgTags($html_content)
	{
		$patterns = array();
		$replacements = array();
		$i = 0;
		$src_exp = "/src=\"(.*?)\"/";
		$link_exp = "[^http:\/\/www\.|^www\.|^https:\/\/|^http:\/\/]";
		$siteURL = JUri::root();
		preg_match_all($src_exp, $html_content, $out, PREG_SET_ORDER);
		foreach ($out as $val)
		{
			$links = preg_match($link_exp, $val[1], $match, PREG_OFFSET_CAPTURE);
			if ($links == '0')
			{
				$patterns[$i] = $val[1];
				$patterns[$i] = "\"$val[1]";
				$replacements[$i] = $siteURL . $val[1];
				$replacements[$i] = "\"$replacements[$i]";
			}
			$i++;
		}
		$mod_html_content = str_replace($patterns, $replacements, $html_content);
		
		return $mod_html_content;
	}

	/**
	 * Process download a file
	 *
	 * @param string $file : Full path to the file which will be downloaded
	 */
	public static function processDownload($filePath, $filename, $detectFilename = false)
	{
		$fsize = @filesize($filePath);
		$mod_date = date('r', filemtime($filePath));
		$cont_dis = 'attachment';
		if ($detectFilename)
		{
			$pos = strpos($filename, '_');
			$filename = substr($filename, $pos + 1);
		}
		$ext = JFile::getExt($filename);
		$mime = self::getMimeType($ext);
		// required for IE, otherwise Content-disposition is ignored
		if (ini_get('zlib.output_compression'))
		{
			ini_set('zlib.output_compression', 'Off');
		}
		header("Pragma: public");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Expires: 0");
		header("Content-Transfer-Encoding: binary");
		header(
			'Content-Disposition:' . $cont_dis . ';' . ' filename="' . $filename . '";' . ' modification-date="' . $mod_date . '";' . ' size=' . $fsize .
				 ';'); //RFC2183
		header("Content-Type: " . $mime); // MIME type
		header("Content-Length: " . $fsize);
		
		if (!ini_get('safe_mode'))
		{ // set_time_limit doesn't work in safe mode
			@set_time_limit(0);
		}
		
		self::readfile_chunked($filePath);
	}

	/**
	 * Get mimetype of a file
	 *
	 * @return string
	 */
	public static function getMimeType($ext)
	{
		require_once JPATH_ROOT . "/components/com_eventbooking/helper/mime.mapping.php";
		foreach ($mime_extension_map as $key => $value)
		{
			if ($key == $ext)
			{
				return $value;
			}
		}
		
		return "";
	}

	/**
	 * Read file
	 *
	 * @param string $filename
	 * @param  $retbytes
	 * @return unknown
	 */
	public static function readfile_chunked($filename, $retbytes = true)
	{
		$chunksize = 1 * (1024 * 1024); // how many bytes per chunk
		$cnt = 0;
		$handle = fopen($filename, 'rb');
		if ($handle === false)
		{
			return false;
		}
		while (!feof($handle))
		{
			$buffer = fread($handle, $chunksize);
			echo $buffer;
			@ob_flush();
			flush();
			if ($retbytes)
			{
				$cnt += strlen($buffer);
			}
		}
		$status = fclose($handle);
		if ($retbytes && $status)
		{
			return $cnt; // return num. bytes delivered like readfile() does.
		}
		return $status;
	}

	/**
	 * Check category access
	 *
	 * @param int $categoryId
	 */
	public static function checkCategoryAccess($categoryId)
	{
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('`access`')
			->from('#__eb_categories')
			->where('id=' . $categoryId);
		$db->setQuery($query);
		$access = (int) $db->loadResult();
		if (!in_array($access, $user->getAuthorisedViewLevels()))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('NOT_AUTHORIZED'));
		}
	}

	/**
	 * Check to see whether the current user can 
	 *
	 * @param int $eventId
	 */
	public static function checkEventAccess($eventId)
	{
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('`access`')
			->from('#__eb_events')
			->where('id=' . $eventId);
		$db->setQuery($query);
		$access = (int) $db->loadResult();
		if (!in_array($access, $user->getAuthorisedViewLevels()))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('NOT_AUTHORIZED'));
		}
	}

	/**
	 * Check to see whether a users to access to registration history
	 * Enter description here
	 */
	public static function checkAccessHistory()
	{
		$user = JFactory::getUser();
		if (!$user->get('id'))
		{
			JFactory::getApplication()->redirect('index.php?option=com_eventbooking', JText::_('NOT_AUTHORIZED'));
		}
	}

	/**
	 * 
	 * Check the access to registrants history from frontend
	 */
	public static function checkRegistrantsAccess()
	{
		if (!JFactory::getUser()->authorise('eventbooking.registrants_management', 'com_eventbooking'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('NOT_AUTHORIZED'));
		}
	}

	/**
	 * Check to see whether the current users can access View List function
	 */
	public static function canViewRegistrantList()
	{
		return JFactory::getUser()->authorise('eventbooking.view_registrants_list', 'com_eventbooking');
	}

	/**
	 * 
	 * Check to see whether this users has permission to edit registrant
	 */
	public static function checkEditRegistrant()
	{
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$cid = Jrequest::getVar('cid', array());
		$registrantId = (int) $cid[0];
		$canAccess = true;
		if (!$registrantId)
		{
			$canAccess = false;
		}
		$sql = 'SELECT user_id, email FROM #__eb_registrants WHERE id=' . $registrantId;
		$db->setQuery($sql);
		$rowRegistrant = $db->loadObject();
		if ($user->authorise('eventbooking.registrants_management', 'com_eventbooking') || ($user->get('id') == $rowRegistrant->user_id) ||
			 ($user->get('email') == $rowRegistrant->email))
		{
			$canAccess = true;
		}
		else
		{
			$canAccess = false;
		}
		if (!$canAccess)
		{
			JFactory::getApplication()->redirect('index.php', JText::_('NOT_AUTHORIZED'));
		}
	}

	/**
	 * Check to see whether this event can be cancelled	 
	 * @param int $eventId
	 */
	public static function canCancel($eventId)
	{
		$db = JFactory::getDbo();
		$sql = 'SELECT COUNT(*) FROM #__eb_events WHERE id=' . $eventId .
			 ' AND enable_cancel_registration = 1 AND (DATEDIFF(cancel_before_date, NOW()) >=0) ';
		$db->setQuery($sql);
		$total = $db->loadResult();
		if ($total)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public static function canExportRegistrants($eventId = 0)
	{
		$user = JFactory::getUser();
		if ($eventId)
		{
			$db = JFactory::getDbo();
			$sql = 'SELECT created_by FROM #__eb_events WHERE id=' . $eventId;
			$db->setQuery($sql);
			$createdBy = (int) $db->loadResult();
			return (($createdBy > 0 && $createdBy == $user->id) || $user->authorise('eventbooking.registrants_management', 'com_eventbooking'));
		}
		else
		{
			return $user->authorise('eventbooking.registrants_management', 'com_eventbooking');
		}
	}

	public static function canChangeEventStatus($eventId)
	{
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		if (!$eventId)
		{
			return false;
		}
		$sql = 'SELECT * FROM #__eb_events WHERE id=' . $eventId;
		$db->setQuery($sql);
		$rowEvent = $db->loadObject();
		if (!$rowEvent)
		{
			return false;
		}
		if ($user->get('guest'))
		{
			return false;
		}
		if ($user->authorise('core.edit.state', 'com_eventbooking') || ($rowEvent->created_by == $user->get('id')))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Check to see whether the users can cancel registration
	 * 
	 * @param int $eventId
	 */
	public static function canCancelRegistration($eventId)
	{
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$email = $user->get('email');
		if (!$userId)
			return false;
		$sql = 'SELECT id FROM #__eb_registrants WHERE event_id=' . $eventId . ' AND (user_id=' . $userId . ' OR email="' . $email .
			 '") AND (published=1 OR (payment_method LIKE "os_offline%" AND published!=2))';
		$db->setQuery($sql);
		$registrantId = $db->loadResult();
		if (!$registrantId)
			return false;
		
		$sql = 'SELECT COUNT(*) FROM #__eb_events WHERE id=' . $eventId .
			 ' AND enable_cancel_registration = 1 AND (DATEDIFF(cancel_before_date, NOW()) >=0) ';
		$db->setQuery($sql);
		$total = $db->loadResult();
		
		if (!$total)
			return false;
		
		return $registrantId;
	}

	/**
	 * Check to see whether the current user can edit registrant
	 *
	 * @param int $eventId
	 * @return boolean
	 */
	public static function checkEditEvent($eventId)
	{
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		if (!$eventId)
		{
			return false;
		}
		$sql = 'SELECT * FROM #__eb_events WHERE id=' . $eventId;
		$db->setQuery($sql);
		$rowEvent = $db->loadObject();
		if (!$rowEvent)
		{
			return false;
		}
		if ($user->get('guest'))
		{
			return false;
		}
		if ($user->authorise('core.edit', 'com_eventbooking') || ($rowEvent->created_by == $user->get('id')))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public static function isGroupRegistration($id)
	{
		if (!$id)
			return false;
		$db = JFactory::getDbo();
		$sql = 'SELECT is_group_billing FROM #__eb_registrants WHERE id=' . $id;
		$db->setQuery($sql);
		$isGroupBilling = (int) $db->loadResult();
		return $isGroupBilling > 0 ? true : false;
	}

	public static function updateGroupRegistrationRecord($groupId)
	{
		$db = JFactory::getDbo();
		$config = EventbookingHelper::getConfig();
		if ($config->collect_member_information)
		{
			$row = JTable::getInstance('EventBooking', 'Registrant');
			$row->load($groupId);
			if ($row->id)
			{
				$sql = "UPDATE #__eb_registrants SET published=$row->published, transaction_id='$row->transaction_id', payment_method='$row->payment_method' WHERE group_id=" .
					 $row->id;
				$db->setQuery($sql);
				$db->execute();
			}
		}
	}

	/**
	 * Check to see whether the current users can add events from front-end
	 * 
	 */
	public static function checkAddEvent()
	{
		$user = JFactory::getUser();
		return ($user->id > 0 && $user->authorise('eventbooking.addevent', 'com_eventbooking'));
	}

	/**
	 * Create a user account	 
	 * @param array $data
	 * @return int Id of created user
	 */
	public static function saveRegistration($data)
	{
		//Need to load com_users language file			
		$lang = JFactory::getLanguage();
		$tag = $lang->getTag();
		if (!$tag)
			$tag = 'en-GB';
		$lang->load('com_users', JPATH_ROOT, $tag);
		$data['name'] = $data['first_name'] . ' ' . $data['last_name'];
		$data['password'] = $data['password2'] = $data['password1'];
		$data['email1'] = $data['email2'] = $data['email'];
		require_once JPATH_ROOT . '/components/com_users/models/registration.php';
		$model = new UsersModelRegistration();
		$ret = $model->register($data);
		$db = JFactory::getDbo();
		//Need to get the user ID based on username
		$sql = 'SELECT id FROM #__users WHERE username="' . $data['username'] . '"';
		$db->setQuery($sql);
		return (int) $db->loadResult();
	}

	/**
	 * Get list of recurring event dates
	 * @param DateTime $startDate
	 * @param DateTime $endDate
	 * @param int $dailyFrequency
	 * @param int $numberOccurencies
	 * @return array
	 */
	public static function getDailyRecurringEventDates($startDate, $endDate, $dailyFrequency, $numberOccurencies)
	{
		$eventDates = array();
		$eventDates[] = $startDate;
		//Convert to unix timestamp for easili maintenance
		$startTime = strtotime($startDate);
		$endTime = strtotime($endDate . ' 23:59:59');
		if ($numberOccurencies)
		{
			$count = 1;
			$i = 1;
			while ($count < $numberOccurencies)
			{
				$i++;
				$count++;
				$nextEventDate = $startTime + ($i - 1) * $dailyFrequency * 24 * 3600;
				$eventDates[] = strftime('%Y-%m-%d %H:%M:%S', $nextEventDate);
			}
		}
		else
		{
			$i = 1;
			while (true)
			{
				$i++;
				$nextEventDate = $startTime + ($i - 1) * 24 * $dailyFrequency * 3600;
				if ($nextEventDate <= $endTime)
				{
					$eventDates[] = strftime('%Y-%m-%d %H:%M:%S', $nextEventDate);
				}
				else
				{
					break;
				}
			}
		}
		return $eventDates;
	}

	/**
	 * Get weekly recurring event dates
	 * @param DateTime $startDate
	 * @param DateTime $endDate
	 * @param Int $weeklyFrequency
	 * @param int $numberOccurrencies
	 * @param array $weekDays
	 * @return array
	 */
	public static function getWeeklyRecurringEventDates($startDate, $endDate, $weeklyFrequency, $numberOccurrencies, $weekDays)
	{
		$eventDates = array();
		$startTime = strtotime($startDate);
		$originalStartTime = $startTime;
		$endTime = strtotime($endDate . ' 23:59:59');
		if ($numberOccurrencies)
		{
			$count = 0;
			$i = 0;
			$weekDay = date('w', $startTime);
			$startTime = $startTime - $weekDay * 24 * 3600;
			while ($count < $numberOccurrencies)
			{
				$i++;
				$startWeekTime = $startTime + ($i - 1) * $weeklyFrequency * 7 * 24 * 3600;
				foreach ($weekDays as $weekDay)
				{
					$nextEventDate = $startWeekTime + $weekDay * 24 * 3600;
					if (($nextEventDate >= $originalStartTime) && ($count < $numberOccurrencies))
					{
						$eventDates[] = strftime('%Y-%m-%d %H:%M:%S', $nextEventDate);
						$count++;
					}
				}
			}
		}
		else
		{
			$weekDay = date('w', $startTime);
			$startTime = $startTime - $weekDay * 24 * 3600;
			while ($startTime < $endTime)
			{
				foreach ($weekDays as $weekDay)
				{
					$nextEventDate = $startTime + $weekDay * 24 * 3600;
					;
					if ($nextEventDate < $originalStartTime)
						continue;
					if ($nextEventDate <= $endTime)
					{
						$eventDates[] = strftime('%Y-%m-%d %H:%M:%S', $nextEventDate);
					}
					else
					{
						break;
					}
				}
				$startTime += $weeklyFrequency * 7 * 24 * 3600;
			}
		}
		return $eventDates;
	}

	/**
	 * Get list of monthly recurring
	 * @param DateTime $startDate
	 * @param DateTime $endDate
	 * @param int $monthlyFrequency
	 * @param int $numberOccurrencies
	 * @param string $monthDays
	 * @return array
	 */
	public static function getMonthlyRecurringEventDates($startDate, $endDate, $monthlyFrequency, $numberOccurrencies, $monthDays)
	{
		$eventDates = array();
		$startTime = strtotime($startDate);
		$hour = date('H', $startTime);
		$minute = date('i', $startTime);
		$originalStartTime = $startTime;
		$endTime = strtotime($endDate . ' 23:59:59');
		$monthDays = explode(',', $monthDays);
		if ($numberOccurrencies)
		{
			$count = 0;
			$currentMonth = date('m', $startTime);
			$currentYear = date('Y', $startTime);
			while ($count < $numberOccurrencies)
			{
				foreach ($monthDays as $day)
				{
					$nextEventDate = mktime($hour, $minute, 0, $currentMonth, $day, $currentYear);
					if (($nextEventDate >= $originalStartTime) && ($count < $numberOccurrencies))
					{
						$eventDates[] = strftime('%Y-%m-%d %H:%M:%S', $nextEventDate);
						$count++;
					}
				}
				$currentMonth += $monthlyFrequency;
				if ($currentMonth > 12)
				{
					$currentMonth -= 12;
					$currentYear++;
				}
			}
		}
		else
		{
			$currentMonth = date('m', $startTime);
			$currentYear = date('Y', $startTime);
			while ($startTime < $endTime)
			{
				foreach ($monthDays as $day)
				{
					$nextEventDate = mktime($hour, $minute, 0, $currentMonth, $day, $currentYear);
					if (($nextEventDate >= $originalStartTime) && ($nextEventDate <= $endTime))
					{
						$eventDates[] = strftime('%Y-%m-%d %H:%M:%S', $nextEventDate);
					}
				}
				$currentMonth += $monthlyFrequency;
				if ($currentMonth > 12)
				{
					$currentMonth -= 12;
					$currentYear++;
				}
				$startTime = mktime(0, 0, 0, $currentMonth, 1, $currentYear);
			}
		}
		return $eventDates;
	}

	public static function getDeliciousButton($title, $link)
	{
		$img_url = "components/com_eventbooking/assets/images/socials/delicious.png";
		return '<a href="http://del.icio.us/post?url=' . rawurlencode($link) . '&amp;title=' . rawurlencode($title) . '" title="Submit ' . $title . ' in Delicious" target="blank" >
		<img src="' . $img_url . '" alt="Submit ' . $title . ' in Delicious" />
		</a>';
	}

	public static function getDiggButton($title, $link)
	{
		$img_url = "components/com_eventbooking/assets/images/socials/digg.png";
		return '<a href="http://digg.com/submit?url=' . rawurlencode($link) . '&amp;title=' . rawurlencode($title) . '" title="Submit ' . $title . ' in Digg" target="blank" >
        <img src="' . $img_url . '" alt="Submit ' . $title . ' in Digg" />
        </a>';
	}

	public static function getFacebookButton($title, $link)
	{
		$img_url = "components/com_eventbooking/assets/images/socials/facebook.png";
		return '<a href="http://www.facebook.com/sharer.php?u=' . rawurlencode($link) . '&amp;t=' . rawurlencode($title) . '" title="Submit ' . $title . ' in FaceBook" target="blank" >
        <img src="' . $img_url . '" alt="Submit ' . $title . ' in FaceBook" />
        </a>';
	}

	public static function getGoogleButton($title, $link)
	{
		$img_url = "components/com_eventbooking/assets/images/socials/google.png";
		return '<a href="http://www.google.com/bookmarks/mark?op=edit&bkmk=' . rawurlencode($link) . '" title="Submit ' . $title . ' in Google Bookmarks" target="blank" >
        <img src="' . $img_url . '" alt="Submit ' . $title . ' in Google Bookmarks" />
        </a>';
	}

	public static function getStumbleuponButton($title, $link)
	{
		$img_url = "components/com_eventbooking/assets/images/socials/stumbleupon.png";
		return '<a href="http://www.stumbleupon.com/submit?url=' . rawurlencode($link) . '&amp;title=' . rawurlencode($title) . '" title="Submit ' .
			 $title . ' in Stumbleupon" target="blank" >
        <img src="' . $img_url . '" alt="Submit ' . $title . ' in Stumbleupon" />
        </a>';
	}

	public static function getTechnoratiButton($title, $link)
	{
		$img_url = "components/com_eventbooking/assets/images/socials/technorati.png";
		return '<a href="http://technorati.com/faves?add=' . rawurlencode($link) . '" title="Submit ' . $title . ' in Technorati" target="blank" >
        <img src="' . $img_url . '" alt="Submit ' . $title . ' in Technorati" />
        </a>';
	}

	public static function getTwitterButton($title, $link)
	{
		$img_url = "components/com_eventbooking/assets/images/socials/twitter.png";
		return '<a href="http://twitter.com/?status=' . rawurlencode($title . " " . $link) . '" title="Submit ' . $title . ' in Twitter" target="blank" >
        <img src="' . $img_url . '" alt="Submit ' . $title . ' in Twitter" />
        </a>';
	}

	/**   
     * 
     * @param string $vName
     */
	public static function addSubMenus($vName = 'dashboard')
	{
		JSubMenuHelper::addEntry(JText::_('Dashboard'), 'index.php?option=com_eventbooking&view=dashboard', $vName == 'dashboard');
		JSubMenuHelper::addEntry(JText::_('Categories'), 'index.php?option=com_eventbooking&view=categories', $vName == 'categories');
		JSubMenuHelper::addEntry(JText::_('Events'), 'index.php?option=com_eventbooking&view=events', $vName == 'events');
		JSubMenuHelper::addEntry(JText::_('Registrants'), 'index.php?option=com_eventbooking&view=registrants', $vName == 'registrants');
		JSubMenuHelper::addEntry(JText::_('Custom Fields'), 'index.php?option=com_eventbooking&view=fields', $vName == 'fields');
		JSubMenuHelper::addEntry(JText::_('Locations'), 'index.php?option=com_eventbooking&view=locations', $vName == 'locations');
		JSubMenuHelper::addEntry(JText::_('Coupons'), 'index.php?option=com_eventbooking&view=coupons', $vName == 'coupons');
		JSubMenuHelper::addEntry(JText::_('Payment Plugins'), 'index.php?option=com_eventbooking&view=plugins', $vName == 'plugins');
		JSubMenuHelper::addEntry(JText::_('Emails & Messages'), 'index.php?option=com_eventbooking&view=message', $vName == 'language');
		JSubMenuHelper::addEntry(JText::_('Translation'), 'index.php?option=com_eventbooking&view=language', $vName == 'language');
		JSubMenuHelper::addEntry(JText::_('Configuration'), 'index.php?option=com_eventbooking&view=configuration', $vName == 'configuration');
	}
}
?>