<?php
/**
 * @version            2.4.3
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

class EventbookingHelper
{

	/**
	 * Return the current installed version
	 */
	public static function getInstalledVersion()
	{
		return '2.4.3';
	}

	/**
	 * Get configuration data and store in config object
	 *
	 * @return RADConfig
	 */
	public static function getConfig()
	{
		static $config;

		if (!$config)
		{
			require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/config/config.php';
			$config = new RADConfig('#__eb_configs');

			if ($config->show_event_date)
			{
				$config->set('sort_events_dropdown', 'event_date, title');
			}
			else
			{
				$config->set('sort_events_dropdown', 'title');
			}
		}

		return $config;
	}

	/**
	 * Get specify config value
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	public static function getConfigValue($key, $default = null)
	{
		$config = self::getConfig();

		return $config->get($key, $default);
	}

	/**
	 * Get component settings from json config file
	 *
	 * @param $appName
	 *
	 * @return array
	 */
	public static function getComponentSettings($appName)
	{
		$settings = json_decode(file_get_contents(JPATH_ADMINISTRATOR . '/components/com_eventbooking/config.json'), true);

		return $settings[strtolower($appName)];
	}

	/**
	 * Check to see whether the return value is a valid date format
	 *
	 * @param $value
	 *
	 * @return bool
	 */
	public static function isValidDate($value)
	{
		// basic date format yyyy-mm-dd
		$expr = '/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$/D';

		return (preg_match($expr, $value, $match) && checkdate($match[2], $match[3], $match[1]));
	}

	/**
	 * We only need to generate invoice for paid events only
	 *
	 * @param $row
	 *
	 * @return bool
	 */
	public static function needInvoice($row)
	{
		// Don't generate invoice for waiting list records
		if ($row->published === 3)
		{
			return false;
		}

		if ($row->amount > 0)
		{
			return true;
		}

		$config = self::getConfig();

		if ($config->multiple_booking)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('SUM(amount)')
				->from('#__eb_registrants')
				->where('id=' . $row->id . ' OR cart_id=' . $row->id);
			$db->setQuery($query);
			$totalAmount = $db->loadResult();
			if ($totalAmount > 0)
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the device type (desktop, tablet, mobile) accessing the extension
	 *
	 * @return string
	 */
	public static function getDeviceType()
	{
		$session    = JFactory::getSession();
		$deviceType = $session->get('eb_device_type');

		// If no data found from session, using mobile detect class to detect the device type
		if (!$deviceType)
		{
			if (!class_exists('EB_Mobile_Detect'))
			{
				require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/vendor/serbanghita/Mobile_Detect.php';
			}

			$mobileDetect = new EB_Mobile_Detect();
			$deviceType   = 'desktop';

			if ($mobileDetect->isMobile())
			{
				$deviceType = 'mobile';
			}

			if ($mobileDetect->isTablet())
			{
				$deviceType = 'tablet';
			}

			// Store the device type into session so that we don't have to find it for next request
			$session->set('eb_device_type', $deviceType);
		}


		return $deviceType;
	}

	/**
	 * Get page params of the givem view
	 *
	 * @param $active
	 * @param $views
	 *
	 * @return JRegistry
	 */
	public static function getViewParams($active, $views)
	{
		if ($active && isset($active->query['view']) && in_array($active->query['view'], $views))
		{
			return $active->params;
		}

		return new JRegistry();
	}

	/**
	 *
	 * Apply some fixes for request data
	 *
	 * @return void
	 */
	public static function prepareRequestData()
	{
		//Remove cookie vars from request data
		$cookieVars = array_keys($_COOKIE);
		if (count($cookieVars))
		{
			foreach ($cookieVars as $key)
			{
				if (!isset($_POST[$key]) && !isset($_GET[$key]))
				{
					unset($_REQUEST[$key]);
				}
			}
		}
		if (isset($_REQUEST['start']) && !isset($_REQUEST['limitstart']))
		{
			$_REQUEST['limitstart'] = $_REQUEST['start'];
		}
		if (!isset($_REQUEST['limitstart']))
		{
			$_REQUEST['limitstart'] = 0;
		}
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
			$db      = JFactory::getDbo();
			$query   = $db->getQuery(true);
			$query->select('*')->from('#__eb_messages');
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			for ($i = 0, $n = count($rows); $i < $n; $i++)
			{
				$row           = $rows[$i];
				$key           = $row->message_key;
				$value         = stripslashes($row->message);
				$message->$key = $value;
			}
		}

		return $message;
	}

	/**
	 * Get field suffix used in sql query
	 *
	 * @param null $activeLanguage
	 *
	 * @return string
	 */
	public static function getFieldSuffix($activeLanguage = null)
	{
		$prefix = '';
		if (JLanguageMultilang::isEnabled())
		{
			if (!$activeLanguage)
			{
				$activeLanguage = JFactory::getLanguage()->getTag();
			}
			if ($activeLanguage != self::getDefaultLanguage())
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
				$query->select('`sef`')
					->from('#__languages')
					->where('lang_code = ' . $db->quote($activeLanguage))
					->where('published = 1');
				$db->setQuery($query);
				$sef = $db->loadResult();
				if ($sef)
				{
					$prefix = '_' . $sef;
				}
			}
		}

		return $prefix;
	}

	/**
	 * Get list of language uses on the site
	 *
	 * @return array
	 */
	public static function getLanguages()
	{
		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);
		$default = self::getDefaultLanguage();
		$query->select('lang_id, lang_code, title, `sef`')
			->from('#__languages')
			->where('published = 1')
			->where('lang_code != ' . $db->quote($default))
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

	/**
	 * Get sef of current language
	 *
	 * @return mixed
	 */
	public static function addLangLinkForAjax()
	{
		$langLink = '';
		if (JLanguageMultilang::isEnabled())
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$tag   = JFactory::getLanguage()->getTag();
			$query->select('`sef`')
				->from('#__languages')
				->where('published = 1')
				->where('lang_code=' . $db->quote($tag));
			$db->setQuery($query, 0, 1);
			$langLink = '&lang=' . $db->loadResult();
		}

		JFactory::getDocument()->addScriptDeclaration(
			'var langLinkForAjax="' . $langLink . '";'
		);
	}

	/**
	 * This function is used to check to see whether we need to update the database to support multilingual or not
	 *
	 * @return boolean
	 */
	public static function isSynchronized()
	{
		$db             = JFactory::getDbo();
		$fields         = array_keys($db->getTableColumns('#__eb_categories'));
		$extraLanguages = self::getLanguages();
		if (count($extraLanguages))
		{
			foreach ($extraLanguages as $extraLanguage)
			{
				$prefix = $extraLanguage->sef;
				if (!in_array('name_' . $prefix, $fields))
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Convert payment amount to USD currency in case the currency is not supported by the payment gateway
	 *
	 * @param $amount
	 * @param $currency
	 *
	 * @return float
	 */
	public static function convertAmountToUSD($amount, $currency)
	{
		$http     = JHttpFactory::getHttp();
		$url      = 'http://download.finance.yahoo.com/d/quotes.csv?e=.csv&f=sl1d1t1&s=USD' . $currency . '=X';
		$response = $http->get($url);
		if ($response->code == 200)
		{
			$currencyData = explode(',', $response->body);
			$rate         = floatval($currencyData[1]);
			if ($rate > 0)
			{
				$amount = $amount / $rate;
			}
		}

		return round($amount, 2);
	}

	/**
	 * Synchronize Events Booking database to support multilingual
	 */
	public static function setupMultilingual()
	{
		$db        = JFactory::getDbo();
		$languages = self::getLanguages();
		if (count($languages))
		{
			$categoryTableFields = array_keys($db->getTableColumns('#__eb_categories'));
			$eventTableFields    = array_keys($db->getTableColumns('#__eb_events'));
			$fieldTableFields    = array_keys($db->getTableColumns('#__eb_fields'));
			foreach ($languages as $language)
			{
				$prefix = $language->sef;

				$fieldName = 'name_' . $prefix;
				if (!in_array($fieldName, $categoryTableFields))
				{
					$sql = "ALTER TABLE  `#__eb_categories` ADD  `$fieldName` VARCHAR( 255 );";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'alias_' . $prefix;
				if (!in_array($fieldName, $categoryTableFields))
				{
					$sql = "ALTER TABLE  `#__eb_categories` ADD  `$fieldName` VARCHAR( 255 );";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'description_' . $prefix;
				if (!in_array($fieldName, $categoryTableFields))
				{
					$sql = "ALTER TABLE  `#__eb_categories` ADD  `$fieldName` TEXT NULL;";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'title_' . $prefix;
				if (!in_array('title_' . $prefix, $eventTableFields))
				{
					$sql = "ALTER TABLE  `#__eb_events` ADD  `$fieldName` VARCHAR( 255 );";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'alias_' . $prefix;
				if (!in_array($fieldName, $eventTableFields))
				{
					$sql = "ALTER TABLE  `#__eb_events` ADD  `$fieldName` VARCHAR( 255 );";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'short_description_' . $prefix;
				if (!in_array($fieldName, $eventTableFields))
				{
					$sql = "ALTER TABLE  `#__eb_events` ADD  `$fieldName` TEXT NULL;";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'description_' . $prefix;
				if (!in_array($fieldName, $eventTableFields))
				{
					$sql = "ALTER TABLE  `#__eb_events` ADD  `$fieldName` TEXT NULL;";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'meta_keywords_' . $prefix;
				if (!in_array($fieldName, $eventTableFields))
				{
					$sql = "ALTER TABLE  `#__eb_events` ADD  `$fieldName` VARCHAR( 255 );";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'meta_description_' . $prefix;
				if (!in_array($fieldName, $eventTableFields))
				{
					$sql = "ALTER TABLE  `#__eb_events` ADD  `$fieldName` VARCHAR( 255 );";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'user_email_body_' . $prefix;
				if (!in_array($fieldName, $eventTableFields))
				{
					$sql = "ALTER TABLE  `#__eb_events` ADD  `$fieldName` TEXT NULL;";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'user_email_body_offline_' . $prefix;
				if (!in_array($fieldName, $eventTableFields))
				{
					$sql = "ALTER TABLE  `#__eb_events` ADD  `$fieldName` TEXT NULL;";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'thanks_message_' . $prefix;
				if (!in_array($fieldName, $eventTableFields))
				{
					$sql = "ALTER TABLE  `#__eb_events` ADD  `$fieldName` TEXT NULL;";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'thanks_message_offline_' . $prefix;
				if (!in_array($fieldName, $eventTableFields))
				{
					$sql = "ALTER TABLE  `#__eb_events` ADD  `$fieldName` TEXT NULL;";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'registration_approved_email_body_' . $prefix;
				if (!in_array($fieldName, $eventTableFields))
				{
					$sql = "ALTER TABLE  `#__eb_events` ADD  `$fieldName` TEXT NULL;";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'title_' . $prefix;
				if (!in_array($fieldName, $fieldTableFields))
				{
					$sql = "ALTER TABLE  `#__eb_fields` ADD  `$fieldName` VARCHAR( 255 );";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'description_' . $prefix;
				if (!in_array($fieldName, $fieldTableFields))
				{
					$sql = "ALTER TABLE  `#__eb_fields` ADD  `$fieldName` TEXT NULL;";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'values_' . $prefix;
				if (!in_array($fieldName, $fieldTableFields))
				{
					$sql = "ALTER TABLE  `#__eb_fields` ADD  `$fieldName` TEXT NULL;";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'default_values_' . $prefix;
				if (!in_array($fieldName, $fieldTableFields))
				{
					$sql = "ALTER TABLE  `#__eb_fields` ADD  `$fieldName` TEXT NULL;";
					$db->setQuery($sql);
					$db->execute();
				}

				$fieldName = 'depend_on_options_' . $prefix;
				if (!in_array($fieldName, $fieldTableFields))
				{
					$sql = "ALTER TABLE  `#__eb_fields` ADD  `$fieldName` TEXT NULL;";
					$db->setQuery($sql);
					$db->execute();
				}
			}
		}
	}

	/**
	 * Get language use for re-captcha
	 *
	 * @return string
	 */
	public static function getRecaptchaLanguage()
	{
		$language  = JFactory::getLanguage();
		$tag       = explode('-', $language->getTag());
		$tag       = $tag[0];
		$available = array('en', 'pt', 'fr', 'de', 'nl', 'ru', 'es', 'tr');
		if (in_array($tag, $available))
		{
			return "lang : '" . $tag . "',";
		}
	}

	/**
	 * Build tags related to event
	 *
	 * @param $event
	 * @param $config
	 *
	 * @return array
	 */
	public static function buildEventTags($event, $config)
	{
		$replaces                      = array();
		$replaces['event_title']       = $event->title;
		$replaces['event_date']        = JHtml::_('date', $event->event_date, $config->event_date_format, null);
		$replaces['event_end_date']    = JHtml::_('date', $event->event_end_date, $config->event_date_format, null);
		$replaces['short_description'] = $event->short_description;
		$replaces['description']       = $event->description;
		if ($event->location_id > 0)
		{
			$rowLocation         = EventbookingHelperDatabase::getLocation($event->location_id);
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
			$replaces['location']      = $rowLocation->name . ' (' . implode(', ', $locationInformation) . ')';
			$replaces['location_name'] = $rowLocation->name;
		}
		else
		{
			$replaces['location']      = '';
			$replaces['location_name'] = '';
		}

		return $replaces;
	}

	/**
	 * Build tags array to use to replace the tags use in email & messages
	 *
	 * @param $row
	 * @param $form
	 * @param $event
	 * @param $config
	 *
	 * @return array
	 */
	public static function buildTags($row, $form, $event, $config, $loadCss = true)
	{
		$db       = JFactory::getDbo();
		$query    = $db->getQuery(true);
		$replaces = array();

		// Event information
		if ($config->multiple_booking)
		{
			$query->select('event_id')
				->from('#__eb_registrants')
				->where("(id = $row->id OR cart_id = $row->id)")
				->order('id');
			$db->setQuery($query);
			$eventIds = $db->loadColumn();

			$fieldSuffix = EventbookingHelper::getFieldSuffix();
			$query->clear();
			$query->select('title' . $fieldSuffix . ' AS title')
				->from('#__eb_events')
				->where('id IN (' . implode(',', $eventIds) . ')')
				->order('FIND_IN_SET(id, "' . implode(',', $eventIds) . '")');

			$db->setQuery($query);
			$eventTitles = $db->loadColumn();
			$eventTitle  = implode(', ', $eventTitles);
		}
		else
		{
			$eventTitle = $event->title;
		}

		$replaces['date']              = date($config->date_format);
		$replaces['event_title']       = $eventTitle;
		$replaces['event_date']        = JHtml::_('date', $event->event_date, $config->event_date_format, null);
		$replaces['event_end_date']    = JHtml::_('date', $event->event_end_date, $config->event_date_format, null);
		$replaces['short_description'] = $event->short_description;
		$replaces['description']       = $event->description;

		// Event custom fields
		if ($config->event_custom_field && file_exists(JPATH_ROOT . '/components/com_eventbooking/fields.xml'))
		{
			EventbookingHelperData::prepareCustomFieldsData(array($event));
			foreach ($event->paramData as $customFieldName => $param)
			{
				$replaces[$customFieldName] = $param['value'];
			}
		}

		// Form fields
		$fields = $form->getFields();
		foreach ($fields as $field)
		{
			if ($field->hideOnDisplay)
			{
				$fieldValue = '';
			}
			else
			{
				if (is_string($field->value) && is_array(json_decode($field->value)))
				{
					$fieldValue = implode(', ', json_decode($field->value));
				}
				else
				{
					$fieldValue = $field->value;
				}
			}

			if ($fieldValue && $field->type == 'Date')
			{
				$date = JFactory::getDate($fieldValue);
				if ($date)
				{
					$dateFormat = $config->date_field_format ? $config->date_field_format : '%Y-%m-%d';
					$dateFormat = str_replace('%', '', $dateFormat);
					$fieldValue = $date->format($dateFormat);
				}
			}

			$replaces[$field->name] = $fieldValue;
		}

		if (isset($replaces['last_name']))
		{
			$replaces['name'] = $replaces['first_name'] . ' ' . $replaces['last_name'];
		}
		else
		{
			$replaces['name'] = $replaces['first_name'];
		}

		if ($row->coupon_id)
		{
			$query->clear();
			$query->select('a.code')
				->from('#__eb_coupons AS a')
				->innerJoin('#__eb_registrants AS b ON a.id = b.coupon_id')
				->where('b.id=' . $row->id);
			$db->setQuery($query);
			$replaces['couponCode'] = $db->loadResult();
		}
		else
		{
			$replaces['couponCode'] = '';
		}

		$replaces['user_id'] = $row->user_id;
		if ($row->user_id)
		{
			$query->clear()
				->select('username')
				->from('#__users')
				->where('id = ' . $row->user_id);
			$replaces['username'] = $db->loadResult();
		}
		else
		{
			$replaces['username'] = '';
		}

		if ($config->multiple_booking)
		{
			//Amount calculation
			$query->clear();
			$query->select('SUM(total_amount)')
				->from('#__eb_registrants')
				->where("(id = $row->id OR cart_id = $row->id)");
			$db->setQuery($query);
			$totalAmount = $db->loadResult();

			$query->clear();
			$query->select('SUM(tax_amount)')
				->from('#__eb_registrants')
				->where("(id = $row->id OR cart_id = $row->id)");
			$db->setQuery($query);
			$taxAmount = $db->loadResult();

			$query->clear();
			$query->select('SUM(payment_processing_fee)')
				->from('#__eb_registrants')
				->where("(id = $row->id OR cart_id = $row->id)");
			$db->setQuery($query);
			$paymentProcessingFee = $db->loadResult();

			$query->clear();
			$query->select('SUM(discount_amount)')
				->from('#__eb_registrants')
				->where("(id = $row->id OR cart_id = $row->id)");
			$db->setQuery($query);
			$discountAmount = $db->loadResult();

			$query->clear();
			$query->select('SUM(late_fee)')
				->from('#__eb_registrants')
				->where("(id = $row->id OR cart_id = $row->id)");
			$db->setQuery($query);
			$lateFee = $db->loadResult();

			$amount = $totalAmount - $discountAmount + $paymentProcessingFee + $taxAmount + $lateFee;

			$replaces['total_amount']           = EventbookingHelper::formatCurrency($totalAmount, $config, $event->currency_symbol);
			$replaces['tax_amount']             = EventbookingHelper::formatCurrency($taxAmount, $config, $event->currency_symbol);
			$replaces['discount_amount']        = EventbookingHelper::formatCurrency($discountAmount, $config, $event->currency_symbol);
			$replaces['late_fee']               = EventbookingHelper::formatCurrency($lateFee, $config, $event->currency_symbol);
			$replaces['payment_processing_fee'] = EventbookingHelper::formatCurrency($paymentProcessingFee, $config, $event->currency_symbol);
			$replaces['amount']                 = EventbookingHelper::formatCurrency($amount, $config, $event->currency_symbol);

			$replaces['amt_total_amount']           = $totalAmount;
			$replaces['amt_tax_amount']             = $taxAmount;
			$replaces['amt_discount_amount']        = $discountAmount;
			$replaces['amt_late_fee']               = $lateFee;
			$replaces['amt_amount']                 = $amount;
			$replaces['amt_payment_processing_fee'] = $paymentProcessingFee;
		}
		else
		{
			$replaces['total_amount']           = EventbookingHelper::formatCurrency($row->total_amount, $config, $event->currency_symbol);
			$replaces['tax_amount']             = EventbookingHelper::formatCurrency($row->tax_amount, $config, $event->currency_symbol);
			$replaces['discount_amount']        = EventbookingHelper::formatCurrency($row->discount_amount, $config, $event->currency_symbol);
			$replaces['late_fee']               = EventbookingHelper::formatCurrency($row->late_fee, $config, $event->currency_symbol);
			$replaces['payment_processing_fee'] = EventbookingHelper::formatCurrency($row->payment_processing_fee, $config, $event->currency_symbol);
			$replaces['amount']                 = EventbookingHelper::formatCurrency($row->amount, $config, $event->currency_symbol);
		}

		$replaces['individual_price'] = EventbookingHelper::formatCurrency($event->individual_price, $config, $event->currency_symbol);

		// Add support for location tag
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

		// Registration record related tags
		$replaces['number_registrants'] = $row->number_registrants;
		$replaces['invoice_number']     = $row->invoice_number;
		$replaces['invoice_number']     = EventbookingHelper::formatInvoiceNumber($row->invoice_number, $config);
		$replaces['transaction_id']     = $row->transaction_id;
		$replaces['id']                 = $row->id;
		$replaces['date']               = JHtml::_('date', 'Now', $config->date_format);
		$method                         = os_payments::loadPaymentMethod($row->payment_method);
		if ($method)
		{
			$replaces['payment_method'] = JText::_($method->title);
		}
		else
		{
			$replaces['payment_method'] = '';
		}


		// Registration detail tags
		$replaces['registration_detail'] = self::getEmailContent($config, $row, $loadCss, $form);

		// Cancel link
		$query->clear();
		$query->select('enable_cancel_registration')
			->from('#__eb_events')
			->where('id = ' . $row->event_id);
		$db->setQuery($query);
		$enableCancel = $db->loadResult();
		if ($enableCancel)
		{
			$Itemid = JFactory::getApplication()->input->getInt('Itemid', 0);

			if (!$Itemid)
			{
				$Itemid = self::getItemid();
			}

			$replaces['cancel_registration_link'] = self::getSiteUrl() . 'index.php?option=com_eventbooking&task=registrant.cancel&cancel_code=' . $row->registration_code . '&Itemid=' . $Itemid;
		}
		else
		{
			$replaces['cancel_registration_link'] = '';
		}

		return $replaces;
	}

	/**
	 * Calculate fees use for individual registration
	 *
	 * @param $event
	 * @param $form
	 * @param $data
	 * @param $config
	 * @param $paymentMethod
	 *
	 * @return array
	 */
	public static function calculateIndividualRegistrationFees($event, $form, $data, $config, $paymentMethod = null)
	{
		$fees                  = array();
		$user                  = JFactory::getUser();
		$db                    = JFactory::getDbo();
		$query                 = $db->getQuery(true);
		$couponCode            = isset($data['coupon_code']) ? $data['coupon_code'] : '';
		$totalAmount           = $event->individual_price + $form->calculateFee();
		$discountAmount        = 0;
		$fees['discount_rate'] = 0;
		$nullDate              = $db->getNullDate();
		if ($user->id)
		{
			$discountRate = self::calculateMemberDiscount($event->discount_amounts, $event->discount_groups);
			if ($discountRate > 0)
			{
				$fees['discount_rate'] = $discountRate;
				if ($event->discount_type == 1)
				{
					$discountAmount = $totalAmount * $discountRate / 100;
				}
				else
				{
					$discountAmount = $discountRate;
				}
			}
		}

		if (($event->early_bird_discount_date != $nullDate) && ($event->date_diff >= 0))
		{
			if ($event->early_bird_discount_amount > 0)
			{
				if ($event->early_bird_discount_type == 1)
				{
					$discountAmount = $discountAmount + $totalAmount * $event->early_bird_discount_amount / 100;
				}
				else
				{
					$discountAmount = $discountAmount + $event->early_bird_discount_amount;
				}
			}
		}

		if ($couponCode)
		{
			//Validate the coupon
			$query->clear();
			$query->select('*')
				->from('#__eb_coupons')
				->where('published=1')
				->where('code="' . $couponCode . '"')
				->where('(valid_from="0000-00-00" OR valid_from <= NOW())')
				->where('(valid_to="0000-00-00" OR valid_to >= NOW())')
				->where('(times = 0 OR times > used)')
				->where('(event_id = -1 OR id IN (SELECT coupon_id FROM #__eb_coupon_events WHERE event_id=' . $event->id . '))')
				->order('id DESC');
			$db->setQuery($query);
			$coupon = $db->loadObject();
			if ($coupon)
			{
				$fees['coupon_valid'] = 1;
				$fees['coupon']       = $coupon;
				if ($coupon->coupon_type == 0)
				{
					$discountAmount = $discountAmount + $totalAmount * $coupon->discount / 100;
				}
				else
				{
					$discountAmount = $discountAmount + $coupon->discount;
				}
			}
			else
			{
				$fees['coupon_valid'] = 0;
			}
		}
		else
		{
			$fees['coupon_valid'] = 1;
		}

		if ($discountAmount > $totalAmount)
		{
			$discountAmount = $totalAmount;
		}

		// Late Fee
		$lateFee = 0;
		if (($event->late_fee_date != $nullDate) && $event->late_fee_date_diff >= 0 && $event->late_fee_amount > 0)
		{
			if ($event->late_fee_type == 1)
			{
				$lateFee = $event->individual_price * $event->late_fee_amount / 100;
			}
			else
			{

				$lateFee = $event->late_fee_amount;
			}
		}

		if ($event->tax_rate && ($totalAmount - $discountAmount + $lateFee > 0))
		{
			$taxAmount = round(($totalAmount - $discountAmount + $lateFee) * $event->tax_rate / 100, 2);
		}
		else
		{
			$taxAmount = 0;
		}

		$amount = $totalAmount - $discountAmount + $taxAmount + $lateFee;

		// Payment processing fee
		$paymentFeeAmount  = 0;
		$paymentFeePercent = 0;
		if ($paymentMethod)
		{
			$method            = os_payments::loadPaymentMethod($paymentMethod);
			$params            = new JRegistry($method->params);
			$paymentFeeAmount  = (float) $params->get('payment_fee_amount');
			$paymentFeePercent = (float) $params->get('payment_fee_percent');
		}
		if (($paymentFeeAmount > 0 || $paymentFeePercent > 0) && $amount > 0)
		{
			$fees['payment_processing_fee'] = round($paymentFeeAmount + $amount * $paymentFeePercent / 100, 2);
			$amount += $fees['payment_processing_fee'];
		}
		else
		{
			$fees['payment_processing_fee'] = 0;
		}

		// Calculate the deposit amount as well
		if ($config->activate_deposit_feature && $event->deposit_amount > 0)
		{
			if ($event->deposit_type == 2)
			{
				$depositAmount = $event->deposit_amount;
			}
			else
			{
				$depositAmount = $event->deposit_amount * $amount / 100;
			}
		}
		else
		{
			$depositAmount = 0;
		}

		$fees['total_amount']    = $totalAmount;
		$fees['discount_amount'] = $discountAmount;
		$fees['tax_amount']      = $taxAmount;
		$fees['amount']          = $amount;
		$fees['deposit_amount']  = $depositAmount;
		$fees['late_fee']        = $lateFee;

		return $fees;
	}

	/**
	 * Calculate fees use for group registration
	 *
	 * @param $event
	 * @param $form
	 * @param $data
	 * @param $config
	 * @param $paymentMethod
	 *
	 * @return array
	 */
	public static function calculateGroupRegistrationFees($event, $form, $data, $config, $paymentMethod = null)
	{
		$fees                  = array();
		$session               = JFactory::getSession();
		$user                  = JFactory::getUser();
		$db                    = JFactory::getDbo();
		$query                 = $db->getQuery(true);
		$couponCode            = isset($data['coupon_code']) ? $data['coupon_code'] : '';
		$eventId               = $event->id;
		$extraFee              = $form->calculateFee();
		$numberRegistrants     = (int) $session->get('eb_number_registrants', '');
		$memberFormFields      = EventbookingHelper::getFormFields($eventId, 2);
		$rate                  = EventbookingHelper::getRegistrationRate($eventId, $numberRegistrants);
		$nullDate              = $db->getNullDate();
		$membersForm           = array();
		$membersTotalAmount    = array();
		$membersDiscountAmount = array();
		$membersLateFee        = array();
		$membersTaxAmount      = array();
		// Members data
		if ($config->collect_member_information)
		{
			$membersData = $session->get('eb_group_members_data', null);
			if ($membersData)
			{
				$membersData = unserialize($membersData);
			}
			else
			{
				$membersData = array();
			}
			for ($i = 0; $i < $numberRegistrants; $i++)
			{
				$memberForm = new RADForm($memberFormFields);
				$memberForm->setFieldSuffix($i + 1);
				$memberForm->bind($membersData);
				$memberExtraFee = $memberForm->calculateFee();
				$extraFee += $memberExtraFee;
				$membersTotalAmount[$i]    = $rate + $memberExtraFee;
				$membersDiscountAmount[$i] = 0;
				$membersLateFee[$i]        = 0;
				$membersForm[$i]           = $memberForm;
			}
		}

		if ($event->fixed_group_price > 0)
		{
			$totalAmount = $event->fixed_group_price + $extraFee;
		}
		else
		{
			$totalAmount = $rate * $numberRegistrants + $extraFee;
		}

		// Calculate discount amount
		$discountAmount = 0;
		if ($user->id)
		{
			$discountRate = self::calculateMemberDiscount($event->discount_amounts, $event->discount_groups);
			if ($discountRate > 0)
			{
				if ($event->discount_type == 1)
				{
					$discountAmount = $totalAmount * $discountRate / 100;
					if ($config->collect_member_information)
					{
						for ($i = 0; $i < $numberRegistrants; $i++)
						{
							$membersDiscountAmount[$i] += $membersTotalAmount[$i] * $discountRate / 100;
						}
					}
				}
				else
				{
					$discountAmount = $numberRegistrants * $discountRate;
					if ($config->collect_member_information)
					{
						for ($i = 0; $i < $numberRegistrants; $i++)
						{
							$membersDiscountAmount[$i] += $discountRate;
						}
					}
				}
			}
		}

		if ($couponCode)
		{
			$query->clear();
			$query->select('*')
				->from('#__eb_coupons')
				->where('published=1')
				->where('code="' . $couponCode . '"')
				->where('(valid_from="0000-00-00" OR valid_from <= NOW())')
				->where('(valid_to="0000-00-00" OR valid_to >= NOW())')
				->where('(times = 0 OR times > used)')
				->where('(event_id = -1 OR id IN (SELECT coupon_id FROM #__eb_coupon_events WHERE event_id=' . $event->id . '))')
				->order('id DESC');
			$db->setQuery($query);
			$coupon = $db->loadObject();
			if ($coupon)
			{
				$fees['coupon_valid'] = 1;
				$fees['coupon']       = $coupon;
				if ($coupon->coupon_type == 0)
				{
					$discountAmount = $discountAmount + $totalAmount * $coupon->discount / 100;

					if ($config->collect_member_information)
					{
						for ($i = 0; $i < $numberRegistrants; $i++)
						{
							$membersDiscountAmount[$i] += $membersTotalAmount[$i] * $coupon->discount / 100;
						}
					}
				}
				else
				{
					$discountAmount = $discountAmount + $numberRegistrants * $coupon->discount;

					if ($config->collect_member_information)
					{
						for ($i = 0; $i < $numberRegistrants; $i++)
						{
							$membersDiscountAmount[$i] += $coupon->discount;
						}
					}
				}
			}
			else
			{
				$fees['coupon_valid'] = 0;
			}
		}
		else
		{
			$fees['coupon_valid'] = 1;
		}


		if (($event->early_bird_discount_date != $nullDate) && ($event->date_diff >= 0))
		{
			if ($event->early_bird_discount_amount > 0)
			{
				if ($event->early_bird_discount_type == 1)
				{
					$discountAmount = $discountAmount + $totalAmount * $event->early_bird_discount_amount / 100;
					if ($config->collect_member_information)
					{
						for ($i = 0; $i < $numberRegistrants; $i++)
						{
							$membersDiscountAmount[$i] += $membersTotalAmount[$i] * $event->early_bird_discount_amount / 100;
						}
					}
				}
				else
				{
					$discountAmount = $discountAmount + $numberRegistrants * $event->early_bird_discount_amount;
					if ($config->collect_member_information)
					{
						for ($i = 0; $i < $numberRegistrants; $i++)
						{
							$membersDiscountAmount[$i] += $event->early_bird_discount_amount;
						}
					}
				}
			}
		}

		// Late Fee
		$lateFee = 0;
		if (($event->late_fee_date != $nullDate) && $event->late_fee_date_diff >= 0 && $event->late_fee_amount > 0)
		{
			if ($event->late_fee_type == 1)
			{
				$lateFee = $totalAmount * $event->late_fee_amount / 100;
				if ($config->collect_member_information)
				{
					for ($i = 0; $i < $numberRegistrants; $i++)
					{
						$membersLateFee[$i] = $membersTotalAmount[$i] * $event->late_fee_amount / 100;
					}
				}
			}
			else
			{

				$lateFee = $numberRegistrants * $event->late_fee_amount;
				if ($config->collect_member_information)
				{
					for ($i = 0; $i < $numberRegistrants; $i++)
					{
						$membersLateFee[$i] = $event->late_fee_amount;
					}
				}
			}
		}

		// In case discount amount greater than total amount, reset it to total amount
		if ($discountAmount > $totalAmount)
		{
			$discountAmount = $totalAmount;
		}

		if ($config->collect_member_information)
		{
			for ($i = 0; $i < $numberRegistrants; $i++)
			{
				if ($membersDiscountAmount[$i] > $membersTotalAmount[$i])
				{
					$membersDiscountAmount[$i] = $membersTotalAmount[$i];
				}
			}
		}

		// Calculate tax amount
		if ($event->tax_rate && ($totalAmount - $discountAmount + $lateFee > 0))
		{
			$taxAmount = round(($totalAmount - $discountAmount + $lateFee) * $event->tax_rate / 100, 2);
			if ($config->collect_member_information)
			{
				for ($i = 0; $i < $numberRegistrants; $i++)
				{
					$membersTaxAmount[$i] = round(($membersTotalAmount[$i] - $membersDiscountAmount[$i] + $membersLateFee[$i]) * $event->tax_rate / 100, 2);
				}
			}
		}
		else
		{
			$taxAmount = 0;
			if ($config->collect_member_information)
			{
				for ($i = 0; $i < $numberRegistrants; $i++)
				{
					$membersTaxAmount[$i] = 0;
				}
			}
		}

		// Gross amount
		$amount = $totalAmount - $discountAmount + $taxAmount + $lateFee;

		// Payment processing fee
		$paymentFeeAmount  = 0;
		$paymentFeePercent = 0;
		if ($paymentMethod)
		{
			$method            = os_payments::loadPaymentMethod($paymentMethod);
			$params            = new JRegistry($method->params);
			$paymentFeeAmount  = (float) $params->get('payment_fee_amount');
			$paymentFeePercent = (float) $params->get('payment_fee_percent');
		}
		if (($paymentFeeAmount > 0 || $paymentFeePercent > 0) && $amount > 0)
		{
			$fees['payment_processing_fee'] = round($paymentFeeAmount + $amount * $paymentFeePercent / 100, 2);
			$amount += $fees['payment_processing_fee'];
		}
		else
		{
			$fees['payment_processing_fee'] = 0;
		}

		// Deposit amount
		if ($config->activate_deposit_feature && $event->deposit_amount > 0)
		{
			if ($event->deposit_type == 2)
			{
				$depositAmount = $numberRegistrants * $event->deposit_amount;
			}
			else
			{
				$depositAmount = $event->deposit_amount * $amount / 100;
			}
		}
		else
		{
			$depositAmount = 0;
		}

		$fees['total_amount']            = $totalAmount;
		$fees['discount_amount']         = $discountAmount;
		$fees['late_fee']                = $lateFee;
		$fees['tax_amount']              = $taxAmount;
		$fees['amount']                  = $amount;
		$fees['deposit_amount']          = $depositAmount;
		$fees['members_form']            = $membersForm;
		$fees['members_total_amount']    = $membersTotalAmount;
		$fees['members_discount_amount'] = $membersDiscountAmount;
		$fees['members_tax_amount']      = $membersTaxAmount;
		$fees['members_late_fee']        = $membersLateFee;

		return $fees;
	}

	/**
	 * Calculate registration fee for cart registration
	 *
	 * @param EventbookingHelperCart $cart
	 * @param RADForm                $form
	 * @param                        $data
	 * @param                        $config
	 * @param null                   $paymentMethod
	 *
	 * @return array
	 */
	public static function calculateCartRegistrationFee($cart, $form, $data, $config, $paymentMethod = null)
	{
		$user                 = JFactory::getUser();
		$db                   = JFactory::getDbo();
		$query                = $db->getQuery(true);
		$nullDate             = $db->getNullDate();
		$fees                 = array();
		$recordsData          = array();
		$totalAmount          = 0;
		$discountAmount       = 0;
		$lateFee              = 0;
		$taxAmount            = 0;
		$amount               = 0;
		$depositAmount        = 0;
		$paymentProcessingFee = 0;
		$feeAmount            = $form->calculateFee();
		$items                = $cart->getItems();
		$quantities           = $cart->getQuantities();
		$paymentType          = isset($data['payment_type']) ? $data['payment_type'] : 1;
		$couponCode           = isset($data['coupon_code']) ? $data['coupon_code'] : '';
		$collectRecordsData   = isset($data['collect_records_data']) ? $data['collect_records_data'] : false;
		$paymentFeeAmount     = 0;
		$paymentFeePercent    = 0;
		if ($paymentMethod)
		{
			$method            = os_payments::loadPaymentMethod($paymentMethod);
			$params            = new JRegistry($method->params);
			$paymentFeeAmount  = (float) $params->get('payment_fee_amount');
			$paymentFeePercent = (float) $params->get('payment_fee_percent');
		}

		$couponDiscountedEventIds = array();
		if ($couponCode)
		{
			$query->clear();
			$query->select('*')
				->from('#__eb_coupons')
				->where('code=' . $db->quote($couponCode))
				->where('(event_id = -1 OR id IN (SELECT coupon_id FROM #__eb_coupon_events WHERE event_id IN (' . implode(',', $items) . ')))')
				->order('id DESC');
			$db->setQuery($query);
			$coupon = $db->loadObject();
			if ($coupon)
			{
				$fees['coupon_valid'] = 1;
				if ($coupon->event_id != -1)
				{
					// Get list of events which will receive discount
					$query->clear();
					$query->select('event_id')
						->from('#__eb_coupon_events')
						->where('coupon_id = ' . $coupon->id);
					$db->setQuery($query);
					$couponDiscountedEventIds = $db->loadColumn();
				}
			}
			else
			{
				$fees['coupon_valid'] = 0;
			}
		}
		else
		{
			$fees['coupon_valid'] = 1;
		}

		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			$eventId               = (int) $items[$i];
			$quantity              = (int) $quantities[$i];
			$recordsData[$eventId] = array();
			$event                 = EventbookingHelperDatabase::getEvent($eventId);
			$rate                  = self::getRegistrationRate($eventId, $quantity);
			if ($i == 0)
			{
				$registrantTotalAmount = $rate * $quantity + $feeAmount;
			}
			else
			{
				$registrantTotalAmount = $rate * $quantity;
			}

			$registrantDiscount = 0;

			// Member discount
			if ($user->id)
			{
				$discountRate = EventbookingHelper::calculateMemberDiscount($event->discount_amounts, $event->discount_groups);
				if ($discountRate > 0)
				{
					if ($event->discount_type == 1)
					{
						$registrantDiscount = $registrantTotalAmount * $discountRate / 100;
					}
					else
					{
						$registrantDiscount = $quantity * $discountRate;
					}
				}
			}

			if (($event->early_bird_discount_date != $nullDate) && $event->date_diff >= 0 && $event->early_bird_discount_amount > 0)
			{
				if ($event->early_bird_discount_type == 1)
				{
					$registrantDiscount += $registrantTotalAmount * $event->early_bird_discount_amount / 100;
				}
				else
				{
					$registrantDiscount += $quantity * $event->early_bird_discount_amount;
				}
			}

			// Coupon discount
			if (!empty($coupon) && ($coupon->event_id == -1 || in_array($eventId, $couponDiscountedEventIds)))
			{
				if ($coupon->coupon_type == 0)
				{
					$registrantDiscount = $registrantDiscount + $registrantTotalAmount * $coupon->discount / 100;
				}
				else
				{
					$registrantDiscount = $registrantDiscount + $coupon->discount;
				}
				if ($collectRecordsData)
				{
					$recordsData[$eventId]['coupon_id'] = $coupon->id;
				}
			}

			if ($registrantDiscount > $registrantTotalAmount)
			{
				$registrantDiscount = $registrantTotalAmount;
			}

			// Late Fee
			$registrantLateFee = 0;
			if (($event->late_fee_date != $nullDate) && $event->late_fee_date_diff >= 0 && $event->late_fee_amount > 0)
			{
				if ($event->late_fee_type == 1)
				{
					$registrantLateFee = $registrantTotalAmount * $event->late_fee_amount / 100;
				}
				else
				{

					$registrantLateFee = $quantity * $event->late_fee_amount;
				}
			}

			if ($event->tax_rate > 0)
			{
				$registrantTaxAmount = round($event->tax_rate * ($registrantTotalAmount - $registrantDiscount + $registrantLateFee) / 100, 2);
			}
			else
			{
				$registrantTaxAmount = 0;
			}
			$registrantAmount = $registrantTotalAmount - $registrantDiscount + $registrantTaxAmount + $registrantLateFee;

			if (($paymentFeeAmount > 0 || $paymentFeePercent > 0) && $registrantAmount > 0)
			{
				$registrantPaymentProcessingFee = round($paymentFeeAmount + $registrantAmount * $paymentFeePercent / 100, 2);
				$registrantAmount += $registrantPaymentProcessingFee;
			}
			else
			{

				$registrantPaymentProcessingFee = 0;
			}

			if ($config->activate_deposit_feature && $event->deposit_amount > 0 && $paymentType == 1)
			{
				if ($event->deposit_type == 2)
				{
					$registrantDepositAmount = $event->deposit_amount * $quantity;
				}
				else
				{
					$registrantDepositAmount = round($registrantAmount * $event->deposit_amount / 100, 2);
				}
			}
			else
			{
				$registrantDepositAmount = 0;
			}
			$totalAmount += $registrantTotalAmount;
			$discountAmount += $registrantDiscount;
			$lateFee += $registrantLateFee;
			$depositAmount += $registrantDepositAmount;
			$taxAmount += $registrantTaxAmount;
			$amount += $registrantAmount;
			$paymentProcessingFee += $registrantPaymentProcessingFee;

			if ($collectRecordsData)
			{
				$recordsData[$eventId]['total_amount']           = $registrantTotalAmount;
				$recordsData[$eventId]['discount_amount']        = $registrantDiscount;
				$recordsData[$eventId]['late_fee']               = $registrantLateFee;
				$recordsData[$eventId]['tax_amount']             = $registrantTaxAmount;
				$recordsData[$eventId]['payment_processing_fee'] = $registrantPaymentProcessingFee;
				$recordsData[$eventId]['amount']                 = $registrantAmount;
				$recordsData[$eventId]['deposit_amount']         = $registrantDepositAmount;
			}
		}

		$fees['total_amount']           = $totalAmount;
		$fees['discount_amount']        = $discountAmount;
		$fees['late_fee']               = $lateFee;
		$fees['tax_amount']             = $taxAmount;
		$fees['amount']                 = $amount;
		$fees['deposit_amount']         = $depositAmount;
		$fees['payment_processing_fee'] = $paymentProcessingFee;
		if ($collectRecordsData)
		{
			$fees['records_data'] = $recordsData;
		}

		return $fees;
	}

	/**
	 * Get URL of the site, using for Ajax request
	 *
	 * @return string
	 *
	 * @throws Exception
	 */
	public static function getSiteUrl()
	{
		$uri  = JUri::getInstance();
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
			$siteUrl = $base . $path . '/';
		}
		else
		{
			$siteUrl = $base . '/';
		}
		if (JFactory::getApplication()->isAdmin())
		{
			$adminPos = strrpos($siteUrl, 'administrator/');
			$siteUrl  = substr_replace($siteUrl, '', $adminPos, 14);
		}

		return $siteUrl;
	}

	/**
	 * Get all custom fields for an event
	 *
	 * @param $eventId
	 *
	 * @return array
	 */
	public static function getAllEventFields($eventId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id, name, title, is_core')
			->from('#__eb_fields')
			->where('published = 1')
			->order('ordering');
		
		if ($eventId)
		{
			$config = EventbookingHelper::getConfig();
			if ($config->custom_field_by_category)
			{
				$subQuery = $db->getQuery(true);
				$subQuery->select('category_id')
					->from('#__eb_event_categories')
					->where('event_id = ' . $eventId)
					->where('main_category = 1');
				$db->setQuery($subQuery);
				$categoryId = (int) $db->loadResult();
				$query->where('(category_id = -1 OR id IN (SELECT field_id FROM #__eb_field_categories WHERE category_id=' . $categoryId . '))');
			}
			else
			{
				$query->where('(event_id = -1 OR id IN (SELECT field_id FROM #__eb_field_events WHERE event_id=' . $eventId . '))');
			}
		}
		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Get the form fields to display in registration form
	 *
	 * @param int    $eventId (ID of the event or ID of the registration record in case the system use shopping cart)
	 * @param int    $registrationType
	 * @param string $activeLanguage
	 *
	 * @return array
	 */
	public static function getFormFields($eventId = 0, $registrationType = 0, $activeLanguage = null)
	{
		static $cache;

		$cacheKey = md5(serialize(func_get_args()));
		if (empty($cache[$cacheKey]))
		{
			$user        = JFactory::getUser();
			$db          = JFactory::getDbo();
			$query       = $db->getQuery(true);
			$config      = EventbookingHelper::getConfig();
			$fieldSuffix = EventbookingHelper::getFieldSuffix($activeLanguage);
			$query->select('*')
				->from('#__eb_fields')
				->where('published=1')
				->where(' `access` IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');

			if ($fieldSuffix)
			{
				EventbookingHelperDatabase::getMultilingualFields($query, array('title', 'description', 'values', 'default_values', 'depend_on_options'), $fieldSuffix);
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

			$subQuery = $db->getQuery(true);
			if ($registrationType == 4)
			{
				$cart  = new EventbookingHelperCart();
				$items = $cart->getItems();
				if ($config->custom_field_by_category)
				{
					if (!count($items))
					{
						//In this case, we have ID of registration record, so, get list of events from that registration
						$subQuery->select('event_id')
							->from('#__eb_registrants')
							->where('id = ' . $eventId);
						$db->setQuery($subQuery);
						$cartEventId = (int) $db->loadResult();
						$subQuery->clear();
					}
					else
					{
						$cartEventId = (int) $items[0];
					}

					$subQuery->select('category_id')
						->from('#__eb_event_categories')
						->where('event_id = ' . $cartEventId)
						->where('main_category = 1');
					$db->setQuery($subQuery);
					$categoryId = (int) $db->loadResult();
					$query->where('(category_id = -1 OR id IN (SELECT field_id FROM #__eb_field_categories WHERE category_id=' . $categoryId . '))');
				}
				else
				{
					if (!count($items))
					{
						//In this case, we have ID of registration record, so, get list of events from that registration
						$subQuery->select('event_id')
							->from('#__eb_registrants')
							->where('id = ' . $eventId);
						$db->setQuery($subQuery);
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
					$subQuery->select('category_id')
						->from('#__eb_event_categories')
						->where('event_id = ' . $eventId)
						->where('main_category = 1');
					$db->setQuery($subQuery);
					$categoryId = (int) $db->loadResult();
					$query->where('(category_id = -1 OR id IN (SELECT field_id FROM #__eb_field_categories WHERE category_id=' . $categoryId . '))');
				}
				else
				{
					$query->where(' (event_id = -1 OR id IN (SELECT field_id FROM #__eb_field_events WHERE event_id=' . $eventId . '))');
				}
			}
			$query->order('ordering');
			$db->setQuery($query);

			$cache[$cacheKey] = $db->loadObjectList();
		}

		return $cache[$cacheKey];
	}

	/**
	 * Get the form data used to bind to the RADForm object
	 *
	 * @param array  $rowFields
	 * @param int    $eventId
	 * @param int    $userId
	 * @param object $config
	 *
	 * @return array
	 */
	public static function getFormData($rowFields, $eventId, $userId, $config)
	{
		$data = array();
		if ($userId)
		{
			$mappings = array();
			foreach ($rowFields as $rowField)
			{
				if ($rowField->field_mapping)
				{
					$mappings[$rowField->name] = $rowField->field_mapping;
				}
			}

			JPluginHelper::importPlugin('eventbooking');
			$results = JFactory::getApplication()->triggerEvent('onGetProfileData', array($userId, $mappings));
			if (count($results))
			{
				foreach ($results as $res)
				{
					if (is_array($res) && count($res))
					{
						$data = $res;
						break;
					}
				}
			}

			if (!count($data))
			{
				$db    = JFactory::getDbo();
				$query = $db->getQuery(true);
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
	 *
	 * @param Object $rowRegistrant
	 * @param array  $rowFields
	 *
	 * @return array
	 */
	public static function getRegistrantData($rowRegistrant, $rowFields)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$data  = array();
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
	 *
	 * @param int $eventId
	 *
	 * @return boolean
	 */
	public static function showBillingStep($eventId)
	{
		$config = self::getConfig();
		if (!$config->collect_member_information || $config->show_billing_step_for_free_events)
		{
			return true;
		}
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('individual_price')
			->from('#__eb_events')
			->where('id=' . $eventId);
		$db->setQuery($query);
		$individualPrice = $db->loadResult();
		if ($individualPrice == 0)
		{
			$config = EventbookingHelper::getConfig();
			$query->clear();
			$query->select('COUNT(*)')
				->from('#__eb_fields')
				->where('fee_field = 1')
				->where('published = 1');

			if ($config->custom_field_by_category)
			{
				$categoryQuery = $db->getQuery(true);
				$categoryQuery->select('category_id')
					->from('#__eb_event_categories')
					->where('event_id = ' . $eventId)
					->where('main_category = 1');
				$db->setQuery($categoryQuery);
				$categoryId = (int) $db->loadResult();
				$query->where('(category_id = -1 OR id IN (SELECT field_id FROM #__eb_field_categories WHERE category_id=' . $categoryId . '))');
			}
			else
			{
				$query->where('(event_id = -1 OR id IN (SELECT field_id FROM #__eb_field_events WHERE event_id = ' . $eventId . '))');
			}
			$db->setQuery($query);

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
		$config     = self::getConfig();
		$dateFormat = $config->date_field_format ? $config->date_field_format : '%Y-%m-%d';
		$dateFormat = str_replace('%', '', $dateFormat);
		$dateNow    = JHtml::_('date', JFactory::getDate(), $dateFormat);

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
		$app   = JFactory::getApplication();
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$user  = JFactory::getUser();
		$query->select('id')
			->from('#__menu AS a')
			->where('a.link LIKE "%index.php?option=com_eventbooking%"')
			->where('a.published=1')
			->where('a.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
		if ($app->isSite() && $app->getLanguageFilter())
		{
			$query->where('a.language IN (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
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
	 * Calculate discount rate which the current user will receive
	 *
	 * @param $discount
	 * @param $groupIds
	 *
	 * @return float
	 */
	public static function calculateMemberDiscount($discount, $groupIds)
	{
		$user = JFactory::getUser();
		if (!$discount)
		{
			return 0;
		}
		if (!$groupIds)
		{
			return $discount;
		}
		$userGroupIds = explode(',', $groupIds);
		JArrayHelper::toInteger($userGroupIds);
		$groups = $user->get('groups');
		if (count(array_intersect($groups, $userGroupIds)))
		{
			//Calculate discount amount
			if (strpos($discount, ',') !== false)
			{
				$discountRates = explode(',', $discount);
				$maxDiscount   = 0;
				foreach ($groups as $group)
				{
					$index = array_search($group, $userGroupIds);
					if ($index !== false && isset($discountRates[$index]))
					{
						$maxDiscount = max($maxDiscount, $discountRates[$index]);
					}
				}

				return $maxDiscount;
			}
			else
			{
				return $discount;
			}
		}
		else
		{
			return 0;
		}
	}

	/**
	 * Format the currency according to the settings in Configuration
	 *
	 * @param  float     $amount the input amount
	 * @param  RADConfig $config the config object
	 *
	 * @return string   the formatted string
	 */
	public static function formatAmount($amount, $config)
	{
		$decimals      = isset($config->decimals) ? $config->decimals : 2;
		$dec_point     = isset($config->dec_point) ? $config->dec_point : '.';
		$thousands_sep = isset($config->thousands_sep) ? $config->thousands_sep : ',';

		return number_format($amount, $decimals, $dec_point, $thousands_sep);
	}

	/**
	 * Format the currency according to the settings in Configuration
	 *
	 * @param  float     $amount         the input amount
	 * @param  RADConfig $config         the config object
	 * @param  string    $currencySymbol the currency symbol. If null, the one in configuration will be used
	 *
	 * @return string   the formatted string
	 */
	public static function formatCurrency($amount, $config, $currencySymbol = null)
	{
		$decimals      = isset($config->decimals) ? $config->decimals : 2;
		$dec_point     = isset($config->dec_point) ? $config->dec_point : '.';
		$thousands_sep = isset($config->thousands_sep) ? $config->thousands_sep : ',';
		$symbol        = $currencySymbol ? $currencySymbol : $config->currency_symbol;

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
			$tag  = $lang->getTag();
			if (!$tag)
			{
				$tag = 'en-GB';
			}
			$lang->load('com_eventbooking', JPATH_ROOT, $tag);
			$loaded = true;
		}
	}

	/**
	 * Get group member detail, using for [MEMBER_DETAIL] tag in the email message
	 *
	 * @param      $config
	 * @param      $rowMember
	 * @param      $rowEvent
	 * @param      $rowLocation
	 * @param bool $loadCss
	 * @param      $memberForm
	 *
	 * @return string
	 */
	public static function getMemberDetails($config, $rowMember, $rowEvent, $rowLocation, $loadCss = true, $memberForm)
	{
		$data                = array();
		$data['rowMember']   = $rowMember;
		$data['rowEvent']    = $rowEvent;
		$data['config']      = $config;
		$data['rowLocation'] = $rowLocation;
		$data['memberForm']  = $memberForm;

		$text = EventbookingHelperHtml::loadCommonLayout(JPATH_ROOT . '/components/com_eventbooking/emailtemplates/email_group_member_detail.php',
			$data);
		if ($loadCss)
		{
			$text .= "
				<style type=\"text/css\">
				" . JFile::read(JPATH_ROOT . '/media/com_eventbooking/assets/css/style.css') . "
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
	 *
	 * @return string
	 */
	public static function getEmailContent($config, $row, $loadCss = true, $form = null, $toAdmin = false)
	{
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$data   = array();
		$Itemid = JFactory::getApplication()->input->getInt('Itemid', 0);

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
		if (!$loadCss)
		{
			// Need to pass bootstrap helper
			$data['bootstrapHelper'] = new EventbookingHelperBootstrap($config->twitter_bootstrap_version);
		}
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		if ($config->multiple_booking)
		{
			$data['row']    = $row;
			$data['config'] = $config;
			$data['Itemid'] = $Itemid;

			$query->select('a.*, b.event_date, b.event_end_date')
				->select('b.title' . $fieldSuffix . ' AS title')
				->from('#__eb_registrants AS a')
				->innerJoin('#__eb_events AS b ON a.event_id = b.id')
				->where("(a.id = $row->id OR a.cart_id = $row->id)");
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			$query->clear();
			$query->select('SUM(total_amount)')
				->from('#__eb_registrants')
				->where("(id = $row->id OR cart_id = $row->id)");
			$db->setQuery($query);
			$totalAmount = $db->loadResult();

			$query->clear();
			$query->select('SUM(tax_amount)')
				->from('#__eb_registrants')
				->where("(id = $row->id OR cart_id = $row->id)");
			$db->setQuery($query);
			$taxAmount = $db->loadResult();

			$query->clear();
			$query->select('SUM(discount_amount)')
				->from('#__eb_registrants')
				->where("(id = $row->id OR cart_id = $row->id)");
			$db->setQuery($query);
			$discountAmount = $db->loadResult();

			$query->clear();
			$query->select('SUM(late_fee)')
				->from('#__eb_registrants')
				->where("(id = $row->id OR cart_id = $row->id)");
			$db->setQuery($query);
			$lateFee = $db->loadResult();

			$query->clear();
			$query->select('SUM(payment_processing_fee)')
				->from('#__eb_registrants')
				->where("(id = $row->id OR cart_id = $row->id)");
			$db->setQuery($query);
			$paymentProcessingFee = $db->loadResult();

			$amount = $totalAmount + $paymentProcessingFee - $discountAmount + $taxAmount + $lateFee;

			$query->clear();
			$query->select('SUM(deposit_amount)')
				->from('#__eb_registrants')
				->where("(id = $row->id OR cart_id = $row->id)");
			$db->setQuery($query);
			$depositAmount = $db->loadResult();

			//Added support for custom field feature
			$data['discountAmount']       = $discountAmount;
			$data['lateFee']              = $lateFee;
			$data['totalAmount']          = $totalAmount;
			$data['items']                = $rows;
			$data['amount']               = $amount;
			$data['taxAmount']            = $taxAmount;
			$data['paymentProcessingFee'] = $paymentProcessingFee;
			$data['depositAmount']        = $depositAmount;
			$data['form']                 = $form;
		}
		else
		{
			$query->select('*, title' . $fieldSuffix . ' AS title')
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
			$data['row']         = $row;
			$data['rowEvent']    = $rowEvent;
			$data['config']      = $config;
			$data['rowLocation'] = $rowLocation;
			$data['form']        = $form;
			if ($row->is_group_billing && $config->collect_member_information)
			{
				$query->clear();
				$query->select('*')
					->from('#__eb_registrants')
					->where('group_id = ' . $row->id)
					->order('id');
				$db->setQuery($query);
				$rowMembers         = $db->loadObjectList();
				$data['rowMembers'] = $rowMembers;
			}
		}

		if ($toAdmin && $row->payment_method == 'os_offline_creditcard')
		{
			$cardNumber = JFactory::getApplication()->input->getString('x_card_num', '');
			if ($cardNumber)
			{
				$last4Digits         = substr($cardNumber, strlen($cardNumber) - 4);
				$data['last4Digits'] = $last4Digits;
			}
		}

		$text = EventbookingHelperHtml::loadCommonLayout(JPATH_ROOT . '/components/com_eventbooking/emailtemplates/' . $layout, $data);
		if ($loadCss)
		{
			$text .= "
				<style type=\"text/css\">
				" . JFile::read(JPATH_ROOT . '/media/com_eventbooking/assets/css/style.css') . "
                </style>
            ";
		}

		return $text;
	}

	/**
	 * Build category dropdown
	 *
	 * @param int     $selected
	 * @param string  $name
	 * @param Boolean $onChange
	 *
	 * @return string
	 */
	public static function buildCategoryDropdown($selected, $name = "parent", $onChange = true)
	{
		$db          = JFactory::getDbo();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$sql         = "SELECT id, parent AS parent_id, name" . $fieldSuffix . " AS title FROM #__eb_categories";
		$db->setQuery($sql);
		$rows     = $db->loadObjectList();
		$children = array();
		if ($rows)
		{
			// first pass - collect children
			foreach ($rows as $v)
			{
				$pt   = $v->parent_id;
				$list = @$children[$pt] ? $children[$pt] : array();
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}
		$list      = JHtml::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
		$options   = array();
		$options[] = JHtml::_('select.option', '0', JText::_('Top'));
		foreach ($list as $item)
		{
			$options[] = JHtml::_('select.option', $item->id, '&nbsp;&nbsp;&nbsp;' . $item->treename);
		}

		if ($onChange)
			return JHtml::_('select.genericlist', $options, $name,
				array(
					'option.text.toHtml' => false,
					'option.text'        => 'text',
					'option.value'       => 'value',
					'list.attr'          => 'class="inputbox" onchange="submit();"',
					'list.select'        => $selected));
		else
			return JHtml::_('select.genericlist', $options, $name,
				array(
					'option.text.toHtml' => false,
					'option.text'        => 'text',
					'option.value'       => 'value',
					'list.attr'          => 'class="inputbox" ',
					'list.select'        => $selected));
	}

	/**
	 * Parent category select list
	 *
	 * @param object $row
	 *
	 * @return string
	 */
	public static function parentCategories($row)
	{
		$db          = JFactory::getDbo();
		$query       = $db->getQuery(true);
		$fieldSuffix = EventbookingHelper::getFieldSuffix();

		$query->select('id, parent AS parent_id')
			->select('name' . $fieldSuffix . ' AS title')
			->from('#__eb_categories');
		if ($row->id)
		{
			$query->where('id != ' . $row->id);
		}

		if (!$row->parent)
		{
			$row->parent = 0;
		}

		$db->setQuery($query);
		$rows     = $db->loadObjectList();
		$children = array();
		if ($rows)
		{
			// first pass - collect children
			foreach ($rows as $v)
			{
				$pt   = $v->parent_id;
				$list = @$children[$pt] ? $children[$pt] : array();
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}
		$list = JHtml::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);

		$options   = array();
		$options[] = JHtml::_('select.option', '0', JText::_('Top'));
		foreach ($list as $item)
		{
			$options[] = JHtml::_('select.option', $item->id, '&nbsp;&nbsp;&nbsp;' . $item->treename);
		}

		return JHtml::_('select.genericlist', $options, 'parent',
			array(
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => ' class="inputbox" ',
				'list.select'        => $row->parent));
	}

	/**
	 * Display list of files which users can choose for event attachment
	 *
	 * @param $attachment
	 * @param $config
	 *
	 * @return mixed
	 */
	public static function attachmentList($attachment, $config)
	{
		jimport('joomla.filesystem.folder');
		$path      = JPATH_ROOT . '/media/com_eventbooking';
		$files     = JFolder::files($path,
			strlen(trim($config->attachment_file_types)) ? $config->attachment_file_types : 'bmp|gif|jpg|png|swf|zip|doc|pdf|xls|zip');
		$options   = array();
		$options[] = JHtml::_('select.option', '', JText::_('EB_SELECT_ATTACHMENT'));
		for ($i = 0, $n = count($files); $i < $n; $i++)
		{
			$file      = $files[$i];
			$options[] = JHtml::_('select.option', $file, $file);
		}

		return JHtml::_('select.genericlist', $options, 'available_attachment[]', 'class="advancedSelect input-xlarge" multiple="multiple" size="6" ', 'value', 'text', $attachment);
	}

	/**
	 * Get total events of a category
	 *
	 * @param int  $categoryId
	 * @param bool $includeChildren
	 *
	 * @return int
	 * @throws Exception
	 */
	public static function getTotalEvent($categoryId, $includeChildren = true)
	{
		$user   = JFactory::getUser();
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$config = self::getConfig();

		$arrCats   = array();
		$cats      = array();
		$arrCats[] = $categoryId;
		$cats[]    = $categoryId;
		if ($includeChildren)
		{
			while (count($arrCats))
			{
				$catId = array_pop($arrCats);

				//Get list of children category
				$query->clear();
				$query->select('id')
					->from('#__eb_categories')
					->where('parent = ' . $catId)
					->where('published = 1');
				$db->setQuery($query);
				$childrenCategories = $db->loadColumn();
				$arrCats            = array_merge($arrCats, $childrenCategories);
				$cats               = array_merge($cats, $childrenCategories);
			}
		}

		$query->clear();
		$query->select('COUNT(a.id)')
			->from('#__eb_events AS a')
			->innerJoin('#__eb_event_categories AS b ON a.id = b.event_id')
			->where('b.category_id IN (' . implode(',', $cats) . ')')
			->where('`access` IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
		if ($config->hide_past_events)
		{
			$query->where('event_date >= ' . $db->quote(JHtml::_('date', 'Now', 'Y-m-d')));
		}
		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	/**
	 * Get all dependencies custom fields
	 *
	 * @param $id
	 *
	 * @return array
	 */
	public static function getAllDependencyFields($id)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$queue  = array($id);
		$fields = array($id);

		while (count($queue))
		{
			$masterFieldId = array_pop($queue);

			//Get list of dependency fields of this master field
			$query->clear();
			$query->select('id')
				->from('#__eb_fields')
				->where('depend_on_field_id=' . $masterFieldId);
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			if (count($rows))
			{
				foreach ($rows as $row)
				{
					$queue[]  = $row->id;
					$fields[] = $row->id;
				}
			}
		}

		return $fields;
	}

	/**
	 * Check to see whether this event still accept registration
	 *
	 * @param object $event
	 *
	 * @return Boolean
	 */
	public static function acceptRegistration($event)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$user  = JFactory::getUser();

		$accessLevels = $user->getAuthorisedViewLevels();
		if (empty($event)
			|| !$event->published
			|| !in_array($event->access, $accessLevels)
			|| !in_array($event->registration_access, $accessLevels)
		)
		{
			return false;
		}

		if ($event->registration_type == 3)
		{
			return false;
		}

		if (!in_array($event->registration_access, $user->getAuthorisedViewLevels()))
		{
			return false;
		}

		if ($event->registration_start_minutes < 0)
		{
			return false;
		}

		// If cut off date is entered, we will check registration based on cut of date, not event date
		if ($event->cut_off_date != $db->getNullDate())
		{
			if ($event->cut_off_minutes > 0)
			{
				return false;
			}
		}
		else
		{
			if ($event->number_event_dates < 0)
			{
				return false;
			}
		}

		if ($event->event_capacity && ($event->total_registrants >= $event->event_capacity))
		{
			return false;
		}

		$config = self::getConfig();

		//Check to see whether the current user has registered for the event
		$preventDuplicateRegistration = $config->prevent_duplicate_registration;
		if ($preventDuplicateRegistration && $user->id)
		{
			$query->clear();
			$query->select('COUNT(id)')
				->from('#__eb_registrants')
				->where('event_id = ' . $event->id)
				->where('user_id = ' . $user->id)
				->where('(published=1 OR (payment_method LIKE "os_offline%" AND published NOT IN (2,3)))');
			$db->setQuery($query);
			$total = $db->loadResult();
			if ($total)
			{
				return false;
			}
		}

		if (!$config->multiple_booking)
		{
			// Check for quantity fields
			$query->clear();
			$query->select('*')
				->from('#__eb_fields')
				->where('published=1')
				->where('quantity_field = 1')
				->where('quantity_values != ""')
				->where(' `access` IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');

			if ($config->custom_field_by_category)
			{
				//Get main category of the event
				$categoryQuery = $db->getQuery(true);
				$categoryQuery->select('category_id')
					->from('#__eb_event_categories')
					->where('event_id = ' . $event->id)
					->where('main_category = 1');

				$db->setQuery($categoryQuery);
				$categoryId = (int) $db->loadResult();
				$query->where('(category_id = -1 OR id IN (SELECT field_id FROM #__eb_field_categories WHERE category_id=' . $categoryId . '))');
			}
			else
			{
				$query->where(' (event_id = -1 OR id IN (SELECT field_id FROM #__eb_field_events WHERE event_id=' . $event->id . '))');
			}

			$db->setQuery($query);
			$quantityFields = $db->loadObjectList();
			if (count($quantityFields))
			{
				foreach ($quantityFields as $field)
				{
					$values         = explode("\r\n", $field->values);
					$quantityValues = explode("\r\n", $field->quantity_values);
					if (count($values) && count($quantityValues))
					{
						$multilingualValues = array();
						if (JLanguageMultilang::isEnabled())
						{
							$multilingualValues = RADFormField::getMultilingualOptions($field->id);
						}
						$values = EventbookingHelperHtml::getAvailableQuantityOptions($values, $quantityValues, $event->id, $field->id, ($field->fieldtype == 'Checkboxes') ? true : false, $multilingualValues);
						if (!count($values))
						{
							return false;
						}
					}
				}
			}
		}


		return true;
	}

	/**
	 * Get total registrants of the given event
	 *
	 * @param int $eventId
	 *
	 * @return int
	 */
	public static function getTotalRegistrants($eventId)
	{
		$db  = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('SUM(number_registrants) AS total_registrants')
			->from('#__eb_registrants')
			->where('event_id = '. $eventId)
			->where('group_id = 0')
			->where('(published=1 OR (payment_method LIKE "os_offline%" AND published NOT IN (2,3)))');
		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	/**
	 * Get max number of registrants allowed for an event
	 *
	 * @param $event
	 *
	 * @return int
	 */
	public static function getMaxNumberRegistrants($event)
	{
		$eventCapacity  = (int) $event->event_capacity;
		$maxGroupNumber = (int) $event->max_group_number;
		if ($eventCapacity)
		{
			$maxRegistrants = $eventCapacity - $event->total_registrants;
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
	 * @param $eventId
	 * @param $numberRegistrants
	 *
	 * @return mixed
	 */
	public static function getRegistrationRate($eventId, $numberRegistrants)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('price')
			->from('#__eb_event_group_prices')
			->where('event_id = ' . $eventId)
			->where('registrant_number <= ' . $numberRegistrants)
			->order('registrant_number DESC');
		$db->setQuery($query, 0, 1);
		$rate = $db->loadResult();
		if (!$rate)
		{
			$query->clear();
			$query->select('individual_price')
				->from('#__eb_events')
				->where('id = ' . $eventId);
			$db->setQuery($query);
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
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(id)')
			->from('#__eb_payment_plugins')
			->where('name = "os_ideal"')
			->where('published = 1');
		$db->setQuery($query);
		$total = $db->loadResult();
		if ($total)
		{
			if (file_exists(JPATH_ROOT . '/components/com_eventbooking/payments/Mollie/API/Autoloader.php'))
			{
				require_once JPATH_ROOT . '/components/com_eventbooking/payments/Mollie/API/Autoloader.php';
			}
			else
			{
				require_once JPATH_ROOT . '/components/com_eventbooking/payments/ideal/ideal.class.php';
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Send notification emails to waiting list users when someone cancel registration
	 *
	 * @param $row
	 * @param $config
	 */
	public static function notifyWaitingList($row, $config)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_registrants')
			->where('event_id=' . (int) $row->event_id)
			->where('group_id = 0')
			->where('published = 3')
			->order('id');
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
			$message                           = EventbookingHelper::getMessages();
			$fieldSuffix                       = EventbookingHelper::getFieldSuffix();
			$replaces                          = array();
			$replaces['registrant_first_name'] = $row->first_name;
			$replaces['registrant_last_name']  = $row->last_name;
			if (JFactory::getApplication()->isSite())
			{
				$replaces['event_link'] = JUri::getInstance()->toString(array('scheme', 'user', 'pass', 'host')) . JRoute::_(EventbookingHelperRoute::getEventRoute($row->event_id, 0, EventbookingHelper::getItemid()));
			}
			else
			{
				$replaces['event_link'] = JUri::getInstance()->toString(array('scheme', 'user', 'pass', 'host')) . '/' . EventbookingHelperRoute::getEventRoute($row->event_id, 0, EventbookingHelper::getItemid());
			}
			$query->clear();
			$query->select('*, title' . $fieldSuffix . ' AS title')
				->from('#__eb_events')
				->where('id = ' . (int) $row->event_id);
			$db->setQuery($query);
			$rowEvent                   = $db->loadObject();
			$replaces['event_title']    = $rowEvent->title;
			$replaces['event_date']     = JHtml::_('date', $rowEvent->event_date, $config->event_date_format, null);
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

			if (self::isValidMessage($message->{'registrant_waitinglist_notification_body' . $fieldSuffix}))
			{
				$body = $message->{'registrant_waitinglist_notification_body' . $fieldSuffix};
			}
			else
			{
				$body = $message->registrant_waitinglist_notification_body;
			}
			foreach ($registrants as $registrant)
			{
				$message                = $body;
				$replaces['first_name'] = $registrant->first_name;
				$replaces['last_name']  = $registrant->last_name;
				foreach ($replaces as $key => $value)
				{
					$key     = strtoupper($key);
					$subject = str_replace("[$key]", $value, $subject);
					$message = str_replace("[$key]", $value, $message);
				}
				//Send email to waiting list users
				$mailer->sendMail($fromEmail, $fromName, $registrant->email, $subject, $message, 1);
				$mailer->clearAllRecipients();
			}
		}
	}

	/**
	 * Helper function for sending emails to registrants and administrator
	 *
	 * @param RegistrantEventBooking $row
	 * @param object                 $config
	 */
	public static function sendEmails($row, $config)
	{
		if ($config->send_emails == 3)
		{
			return;
		}
		$db          = JFactory::getDbo();
		$query       = $db->getQuery(true);
		$message     = self::getMessages();
		$fieldSuffix = self::getFieldSuffix($row->language);
		$mailer      = JFactory::getMailer();
		if ($config->from_name)
		{
			$fromName = $config->from_name;
		}
		else
		{
			$fromName = JFactory::getConfig()->get('from_name');
		}
		if ($config->from_email)
		{
			$fromEmail = $config->from_email;
		}
		else
		{
			$fromEmail = JFactory::getConfig()->get('mailfrom');
		}
		$query->select('*, title' . $fieldSuffix . ' AS title')
			->from('#__eb_events')
			->where('id=' . $row->event_id);
		$db->setQuery($query);
		$event = $db->loadObject();

		if ($event->created_by)
		{
			$eventCreator = JUser::getInstance($event->created_by);
			if (!empty($eventCreator->email) && !$eventCreator->authorise('core.admin'))
			{
				$mailer->addReplyTo($eventCreator->email);
			}
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
		$form->buildFieldsDependency();
		$replaces = self::buildTags($row, $form, $event, $config);
		// Need to get rowLocation from database
		$query->clear();
		$query->select('a.*')
			->from('#__eb_locations AS a')
			->innerJoin('#__eb_events AS b ON a.id=b.location_id')
			->where('b.id=' . $row->event_id);

		$db->setQuery($query);
		$rowLocation = $db->loadObject();

		// Notification email send to user
		if ($config->send_emails == 0 || $config->send_emails == 2)
		{
			if (strlen($message->{'user_email_subject' . $fieldSuffix}))
			{
				$subject = $message->{'user_email_subject' . $fieldSuffix};
			}
			else
			{
				$subject = $message->user_email_subject;
			}
			if (!$row->published && strpos($row->payment_method, 'os_offline') !== false)
			{
				if (self::isValidMessage($event->user_email_body_offline))
				{
					$body = $event->user_email_body_offline;
				}
				elseif (self::isValidMessage($message->{'user_email_body_offline' . $fieldSuffix}))
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
				if (self::isValidMessage($event->user_email_body))
				{
					$body = $event->user_email_body;
				}
				elseif (self::isValidMessage($message->{'user_email_body' . $fieldSuffix}))
				{
					$body = $message->{'user_email_body' . $fieldSuffix};
				}
				else
				{
					$body = $message->user_email_body;
				}
			}
			foreach ($replaces as $key => $value)
			{
				$key     = strtoupper($key);
				$subject = str_ireplace("[$key]", $value, $subject);
				$body    = str_ireplace("[$key]", $value, $body);
			}
			$body = self::convertImgTags($body);

			if (strpos($body, '[QRCODE]') !== false)
			{
				EventbookingHelper::generateQrcode($row->id);
				$imgTag = '<img src="' . EventbookingHelper::getSiteUrl() . 'media/com_eventbooking/qrcodes/' . $row->id . '.png" border="0" />';
				$body   = str_ireplace("[QRCODE]", $imgTag, $body);
			}

			$attachments     = array();
			$invoiceFilePath = '';
			if ($config->activate_invoice_feature && $config->send_invoice_to_customer && $row->invoice_number)
			{
				self::generateInvoicePDF($row);
				$invoiceFilePath = JPATH_ROOT . '/media/com_eventbooking/invoices/' . self::formatInvoiceNumber($row->invoice_number, $config) . '.pdf';
				$attachments[]   = $invoiceFilePath;
			}

			if ($config->multiple_booking)
			{
				$query->clear();
				$query->select('attachment')
					->from('#__eb_events')
					->where('id IN (SELECT event_id FROM #__eb_registrants AS a WHERE a.id=' . $row->id . ' OR a.cart_id=' . $row->id . ' ORDER BY a.id)');
				$db->setQuery($query);
				$attachmentFiles = $db->loadColumn();
			}
			else
			{
				if ($event->attachment)
				{
					$attachmentFiles = array($event->attachment);
					$attachments[]   = JPATH_ROOT . '/media/com_eventbooking/' . $event->attachment;
				}
				else
				{
					$attachmentFiles = array();
				}
			}

			// Remove empty value from array
			$attachmentFiles = array_filter($attachmentFiles);

			// Add all valid attachments to email
			if (count($attachmentFiles))
			{
				foreach ($attachmentFiles as $attachmentFile)
				{
					$files = explode('|', $attachmentFile);
					foreach ($files as $file)
					{
						$filePath = JPATH_ROOT . '/media/com_eventbooking/' . $file;
						if (file_exists($filePath))
						{
							$attachments[] = $filePath;
						}
					}
				}
			}

			//Generate and send ics file to registrants
			if ($config->send_ics_file)
			{
				$ics = new EventbookingHelperIcs();
				$ics->setName($event->title)
					->setDescription($event->short_description)
					->setOrganizer($fromEmail, $fromName)
					->setStart($event->event_date)
					->setEnd($event->event_end_date);

				if ($rowLocation)
				{
					$ics->setLocation($rowLocation->name);
				}
				$fileName      = JApplication::stringURLSafe($event->title) . '.ics';
				$attachments[] = $ics->save(JPATH_ROOT . '/media/com_eventbooking/icsfiles/', $fileName);
			}

			$mailer->sendMail($fromEmail, $fromName, $row->email, $subject, $body, 1, null, null, $attachments);

			if ($config->send_email_to_group_members && $row->is_group_billing)
			{
				// Remove invoice from attachment, group members should not receive invoice
				if ($invoiceFilePath && file_exists($invoiceFilePath))
				{
					$mailer->removeAttachment(0);
				}

				$query->clear();
				$query->select('*')
					->from('#__eb_registrants')
					->where('group_id = ' . $row->id)
					->order('id');
				$db->setQuery($query);
				$rowMembers = $db->loadObjectList();
				if (count($rowMembers))
				{
					$memberReplaces                      = array();
					$memberReplaces['event_title']       = $replaces['event_title'];
					$memberReplaces['event_date']        = $replaces['event_date'];
					$memberReplaces['transaction_id']    = $replaces['transaction_id'];
					$memberReplaces['date']              = $replaces['date'];
					$memberReplaces['short_description'] = $replaces['short_description'];
					$memberReplaces['description']       = $replaces['short_description'];
					$memberReplaces['location']          = $replaces['location'];
					$memberFormFields                    = self::getFormFields($row->event_id, 2);
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
						if (self::isValidMessage($message->{'group_member_email_body' . $fieldSuffix}))
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
						$memberForm->buildFieldsDependency();
						$fields = $memberForm->getFields();
						foreach ($fields as $field)
						{
							if ($field->hideOnDisplay)
							{
								$fieldValue = '';
							}
							else
							{
								if (is_string($field->value) && is_array(json_decode($field->value)))
								{
									$fieldValue = implode(', ', json_decode($field->value));
								}
								else
								{
									$fieldValue = $field->value;
								}
							}
							$memberReplaces[$field->name] = $fieldValue;
						}
						$memberReplaces['member_detail'] = self::getMemberDetails($config, $rowMember, $event, $rowLocation, true, $memberForm);
						foreach ($memberReplaces as $key => $value)
						{
							$key     = strtoupper($key);
							$body    = str_ireplace("[$key]", $value, $body);
							$subject = str_ireplace("[$key]", $value, $subject);
						}
						$body = self::convertImgTags($body);
						$mailer->clearAllRecipients();
						$mailer->sendMail($fromEmail, $fromName, $rowMember->email, $subject, $body, 1, null);
					}
				}
			}

			// Clear attachments
			$mailer->clearAttachments();
			$mailer->clearReplyTos();
		}

		// Add invoice to admin email if needed
		if ($config->send_emails == 0 || $config->send_emails == 1)
		{
			if ($config->send_invoice_to_admin && !empty($invoiceFilePath) && file_exists($invoiceFilePath))
			{
				$mailer->addAttachment($invoiceFilePath);
			}

			// Send attachment to admin email if needed
			if ($config->send_attachments_to_admin)
			{
				$attachmentsPath = JPATH_ROOT . '/media/com_eventbooking/files/';
				for ($i = 0, $n = count($rowFields); $i < $n; $i++)
				{
					$rowField = $rowFields[$i];
					if ($rowField->fieldtype == 'File')
					{
						if (isset($replaces[$rowField->name]))
						{
							$fileName = $replaces[$rowField->name];
							if ($fileName && file_exists($attachmentsPath . '/' . $fileName))
							{
								$pos = strpos($fileName, '_');
								if ($pos !== false)
								{
									$originalFilename = substr($fileName, $pos + 1);
								}
								else
								{
									$originalFilename = $fileName;
								}
								$mailer->addAttachment($attachmentsPath . '/' . $fileName, $originalFilename);
							}
						}
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
			$emails             = explode(',', $notificationEmails);
			if (strlen($message->{'admin_email_subject' . $fieldSuffix}))
			{
				$subject = $message->{'admin_email_subject' . $fieldSuffix};
			}
			else
			{
				$subject = $message->admin_email_subject;
			}
			if (self::isValidMessage($message->{'admin_email_body' . $fieldSuffix}))
			{
				$body = $message->{'admin_email_body' . $fieldSuffix};
			}
			else
			{
				$body = $message->admin_email_body;
			}

			if ($row->payment_method == 'os_offline_creditcard')
			{
				$replaces['registration_detail'] = self::getEmailContent($config, $row, true, $form, true);
			}
			foreach ($replaces as $key => $value)
			{
				$key     = strtoupper($key);
				$subject = str_ireplace("[$key]", $value, $subject);
				$body    = str_ireplace("[$key]", $value, $body);
			}
			$body = self::convertImgTags($body);

			if (strpos($body, '[QRCODE]') !== false)
			{
				EventbookingHelper::generateQrcode($row->id);
				$imgTag = '<img src="' . EventbookingHelper::getSiteUrl() . 'media/com_eventbooking/qrcodes/' . $row->id . '.png" border="0" />';
				$body   = str_ireplace("[QRCODE]", $imgTag, $body);
			}

			for ($i = 0, $n = count($emails); $i < $n; $i++)
			{
				$email = $emails[$i];
				$mailer->clearAllRecipients();
				if ($email)
				{
					$mailer->sendMail($fromEmail, $fromName, $email, $subject, $body, 1);
				}
			}

			if (!empty($eventCreator->email) && !$eventCreator->authorise('core.admin') && !in_array($eventCreator->email, $emails))
			{
				$mailer->clearAllRecipients();
				$mailer->sendMail($fromEmail, $fromName, $eventCreator->email, $subject, $body, 1);
			}
		}
	}

	/**
	 * Helper function for sending emails to registrants and administrator
	 *
	 * @param RegistrantEventBooking $row
	 * @param object                 $config
	 */
	public static function sendRegistrationApprovedEmail($row, $config)
	{
		$mailer = JFactory::getMailer();
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		self::loadLanguage();
		$message     = self::getMessages();
		$fieldSuffix = self::getFieldSuffix($row->language);
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
		$form->buildFieldsDependency();
		$query->select('*, title' . $fieldSuffix . ' AS title')
			->from('#__eb_events')
			->where('id=' . $row->event_id);
		$db->setQuery($query);
		$event    = $db->loadObject();
		$replaces = self::buildTags($row, $form, $event, $config);
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

		if (self::isValidMessage($event->registration_approved_email_body))
		{
			$body = $event->registration_approved_email_body;
		}
		elseif (self::isValidMessage($message->{'registration_approved_email_body' . $fieldSuffix}))
		{
			$body = $message->{'registration_approved_email_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->registration_approved_email_body;
		}
		foreach ($replaces as $key => $value)
		{
			$key     = strtoupper($key);
			$subject = str_ireplace("[$key]", $value, $subject);
			$body    = str_ireplace("[$key]", $value, $body);
		}
		$body = self::convertImgTags($body);


		if (strpos($body, '[QRCODE]') !== false)
		{
			EventbookingHelper::generateQrcode($row->id);
			$imgTag = '<img src="' . EventbookingHelper::getSiteUrl() . 'media/com_eventbooking/qrcodes/' . $row->id . '.png" border="0" />';
			$body   = str_ireplace("[QRCODE]", $imgTag, $body);
		}

		if ($config->activate_invoice_feature && $row->invoice_number)
		{
			self::generateInvoicePDF($row);
			$mailer->addAttachment(JPATH_ROOT . '/media/com_eventbooking/invoices/' . self::formatInvoiceNumber($row->invoice_number, $config) . '.pdf');
		}

		$mailer->sendMail($fromEmail, $fromName, $row->email, $subject, $body, 1);
	}

	/**
	 * Send email when users fill-in waitinglist
	 *
	 * @param  object $row
	 * @param object  $config
	 */
	public static function sendWaitinglistEmail($row, $config)
	{
		$db          = JFactory::getDbo();
		$mailer      = JFactory::getMailer();
		$query       = $db->getQuery(true);
		$message     = self::getMessages();
		$fieldSuffix = self::getFieldSuffix($row->language);
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

		$query->select('*, title' . $fieldSuffix . ' AS title')
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
		$form->buildFieldsDependency();
		$replaces = self::buildTags($row, $form, $event, $config);
		//Notification email send to user
		if (strlen($message->{'watinglist_confirmation_subject' . $fieldSuffix}))
		{
			$subject = $message->{'watinglist_confirmation_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->watinglist_confirmation_subject;
		}
		if (self::isValidMessage($message->{'watinglist_confirmation_body' . $fieldSuffix}))
		{
			$body = $message->{'watinglist_confirmation_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->watinglist_confirmation_body;
		}
		$subject = str_ireplace('[EVENT_TITLE]', $event->title, $subject);
		foreach ($replaces as $key => $value)
		{
			$key  = strtoupper($key);
			$body = str_ireplace("[$key]", $value, $body);
		}
		$mailer->sendMail($fromEmail, $fromName, $row->email, $subject, $body, 1);
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
		$emails             = explode(',', $notificationEmails);
		if (strlen($message->{'watinglist_notification_subject' . $fieldSuffix}))
		{
			$subject = $message->{'watinglist_notification_subject' . $fieldSuffix};
		}
		else
		{
			$subject = $message->watinglist_notification_subject;
		}
		if (self::isValidMessage($message->{'watinglist_notification_body' . $fieldSuffix}))
		{
			$body = $message->{'watinglist_notification_body' . $fieldSuffix};
		}
		else
		{
			$body = $message->watinglist_notification_body;
		}
		$subject = str_ireplace('[EVENT_TITLE]', $event->title, $subject);
		foreach ($replaces as $key => $value)
		{
			$key  = strtoupper($key);
			$body = str_ireplace("[$key]", $value, $body);
		}
		$body = self::convertImgTags($body);
		for ($i = 0, $n = count($emails); $i < $n; $i++)
		{
			$email = $emails[$i];
			$mailer->clearAllRecipients();
			$mailer->sendMail($fromEmail, $fromName, $email, $subject, $body, 1);
		}
	}

	/**
	 * Send reminder email to registrants
	 *
	 * @param int  $numberEmailSendEachTime
	 * @param null $bccEmail
	 */
	public static function sendReminder($numberEmailSendEachTime = 0, $bccEmail = null)
	{
		$db      = JFactory::getDbo();
		$query   = $db->getQuery(true);
		$config  = EventbookingHelper::getConfig();
		$message = EventbookingHelper::getMessages();
		$mailer  = JFactory::getMailer();

		$siteUrl = EventbookingHelper::getSiteUrl();

		self::loadLanguage();

		if ($bccEmail)
		{
			$mailer->addBcc($bccEmail);
		}

		if (!$numberEmailSendEachTime)
		{
			$numberEmailSendEachTime = 15;
		}

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

		$eventFields = array('b.id as event_id', 'b.event_date', 'b.title');

		if (JLanguageMultilang::isEnabled())
		{
			$languages = EventbookingHelper::getLanguages();
			if (count($languages))
			{
				foreach ($languages as $language)
				{
					$eventFields[] = 'b.title_' . $language->sef;
				}
			}
		}

		$query->select('a.*')
			->select(implode(',', $eventFields))
			->from('#__eb_registrants AS a')
			->innerJoin('#__eb_events AS b ON a.event_id = b.id')
			->where('(a.published = 1 OR (a.payment_method LIKE "os_offline%" AND a.published = 0))')
			->where('a.is_reminder_sent = 0')
			->where('b.published = 1')
			->where('b.enable_auto_reminder = 1')
			->where('DATEDIFF(b.event_date, NOW()) <= b.remind_before_x_days')
			->where('DATEDIFF(b.event_date, NOW()) >= 0')
			->order('b.event_date, a.register_date');

		$db->setQuery($query, 0, $numberEmailSendEachTime);

		try
		{
			$rows = $db->loadObjectList();
		}
		catch (Exception  $e)
		{
			$rows = array();
		}

		for ($i = 0, $n = count($rows); $i < $n; $i++)
		{
			$row         = $rows[$i];
			$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);
			if (strlen($message->{'reminder_email_subject' . $fieldSuffix}))
			{
				$emailSubject = $message->{'reminder_email_subject' . $fieldSuffix};
			}
			else
			{
				$emailSubject = $message->reminder_email_subject;
			}

			$eventTitle = $row->{'title' . $fieldSuffix};

			$emailSubject = str_ireplace('[EVENT_TITLE]', $eventTitle, $emailSubject);

			if (strlen($message->{'reminder_email_body' . $fieldSuffix}))
			{
				$emailBody = $message->{'reminder_email_body' . $fieldSuffix};
			}
			else
			{
				$emailBody = $message->reminder_email_body;
			}

			$replaces                = array();
			$replaces['event_date']  = JHtml::_('date', $row->event_date, $config->event_date_format, null);
			$replaces['first_name']  = $row->first_name;
			$replaces['last_name']   = $row->last_name;
			$replaces['event_title'] = $eventTitle;

			// On process [REGISTRATION_DETAIL] tag if it is available in the email message
			if (strpos($emailBody, '[REGISTRATION_DETAIL]') !== false)
			{
				// Build this tag
				if ($config->multiple_booking)
				{
					$rowFields = EventbookingHelper::getFormFields($row->id, 4);
				}
				elseif ($row->is_group_billing)
				{
					$rowFields = EventbookingHelper::getFormFields($row->event_id, 1);
				}
				else
				{
					$rowFields = EventbookingHelper::getFormFields($row->event_id, 0);
				}

				$form = new RADForm($rowFields);
				$data = EventbookingHelper::getRegistrantData($row, $rowFields);
				$form->bind($data);
				$form->buildFieldsDependency();

				$replaces['registration_detail'] = EventbookingHelper::getEmailContent($config, $row, true, $form);
			}

			if (strpos($emailBody, '[QRCODE]') !== false)
			{
				EventbookingHelper::generateQrcode($row->id);
				$imgTag    = '<img src="' . $siteUrl . 'media/com_eventbooking/qrcodes/' . $row->id . '.png" border="0" />';
				$emailBody = str_ireplace("[QRCODE]", $imgTag, $emailBody);
			}

			foreach ($replaces as $key => $value)
			{
				$emailBody = str_ireplace('[' . strtoupper($key) . ']', $value, $emailBody);
			}

			$emailBody = EventbookingHelper::convertImgTags($emailBody);
			$mailer->sendMail($fromEmail, $fromName, $row->email, $emailSubject, $emailBody, 1);
			$mailer->clearAddresses();

			$query->clear();
			$query->update('#__eb_registrants')
				->set('is_reminder_sent = 1')
				->where('id = ' . (int) $row->id);
			$db->setQuery($query);
			$db->execute();
		}
	}

	/**
	 * Get country code
	 *
	 * @param string $countryName
	 *
	 * @return string
	 */
	public static function getCountryCode($countryName)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		if (empty($countryName))
		{
			$countryName = self::getConfigValue('default_country');
		}
		$query->select('country_2_code')
			->from('#__eb_countries')
			->where('LOWER(name) = ' . $db->quote(JString::strtolower($countryName)));
		$db->setQuery($query);
		$countryCode = $db->loadResult();
		if (!$countryCode)
		{
			$countryCode = 'US';
		}

		return $countryCode;
	}

	/**
	 * Get color code of an event based on in category
	 *
	 * @param int $eventId
	 *
	 * @return Array
	 */
	public static function getColorCodeOfEvent($eventId)
	{
		static $colors;
		if (!isset($colors[$eventId]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('color_code')
				->from('#__eb_categories AS a')
				->innerJoin('#__eb_event_categories AS b ON (a.id = b.category_id AND b.main_category = 1)')
				->where('b.event_id = ' . $eventId);
			$db->setQuery($query);
			$colors[$eventId] = $db->loadResult();
		}

		return $colors[$eventId];
	}

	/**
	 * Get categories of the given events
	 *
	 * @param array $eventIds
	 *
	 * @return array
	 */
	public static function getCategories($eventIds = array())
	{
		if (count($eventIds))
		{
			$db          = JFactory::getDbo();
			$query       = $db->getQuery(true);
			$fieldSuffix = EventbookingHelper::getFieldSuffix();
			$query->select('a.id, a.name' . $fieldSuffix . ' AS name, a.color_code')
				->from('#__eb_categories AS a')
				->where('published = 1')
				->where('id IN (SELECT category_id FROM #__eb_event_categories WHERE event_id IN (' . implode(',', $eventIds) . ') AND main_category = 1)');
			$db->setQuery($query);

			return $db->loadObjectList();
		}
		else
		{
			return array();
		}
	}

	/**
	 * Get title of the given payment method
	 *
	 * @param string $methodName
	 *
	 * @return string
	 */
	public static function getPaymentMethodTitle($methodName)
	{
		static $titles;
		if (!isset($titles[$methodName]))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('title')
				->from('#__eb_payment_plugins')
				->where('name = ' . $db->quote($methodName));
			$db->setQuery($query);
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
	 * Check if the given message entered via HTML editor has actual data
	 *
	 * @param $string
	 *
	 * @return bool
	 */
	public static function isValidMessage($string)
	{
		$string = strip_tags($string, '<img>');

		// Remove none printable characters
		$string = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x80-\x9F]/u', '', $string);

		$string = trim($string);

		if (strlen($string))
		{
			return true;
		}

		return false;
	}

	/**
	 * Generate user selection box
	 *
	 * @param int    $userId
	 * @param string $fieldName
	 * @param int    $registrantId
	 *
	 * @return string
	 */
	public static function getUserInput($userId, $fieldName = 'user_id', $registrantId = 0)
	{
		JHtml::_('jquery.framework');
		$field = JFormHelper::loadFieldType('User');

		$element = new SimpleXMLElement('<field />');
		$element->addAttribute('name', $fieldName);
		$element->addAttribute('class', 'readonly');

		if (!$registrantId)
		{
			$element->addAttribute('onchange', 'populateRegistrantData();');
		}

		$field->setup($element, $userId);

		return $field->input;
	}

	/**
	 * Generate article selection box
	 *
	 * @param int    $fieldValue
	 * @param string $fieldName
	 *
	 * @return string
	 */
	public static function getArticleInput($fieldValue, $fieldName = 'article_id')
	{
		// Initialize variables.
		JHtml::_('behavior.modal');
		$link = 'index.php?option=com_content&amp;view=articles&amp;layout=modal&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1';
		$html = array();
		?>
		<script type="text/javascript">
			function jSelectArticle(id, title, catid, object, link, lang) {
				var old_id = document.getElementById('<?php echo $fieldName; ?>').value;
				if (old_id != id) {
					document.id('article_name').value = title;
					document.getElementById('<?php echo $fieldName; ?>').value = id;
					jModalClose();
				}
			}
		</script>
		<?php
		$table = JTable::getInstance('content');
		if ($fieldValue)
		{
			$table->load($fieldValue);
		}
		else
		{
			$table->title = '';
		}
		$html[] = '<div class="input-prepend input-append">';
		$html[] = '<div class="media-preview add-on"><span title="" class="hasTipPreview"><span class="icon-eye"></span></span></div>';
		$html[] = '	<input type="text" disabled="disabled" class="input-small hasTipImgpath" id="article_name"' . ' value="' . htmlspecialchars($table->title, ENT_COMPAT, 'UTF-8') . '"' .
			' disabled="disabled"/>';
		// Create the user select button.
		$html[] = '<a title="" rel="{handler: \'iframe\', size: {x: 800, y: 500}}" data-toggle="modal" role="button" class="btn hasTooltip modal" href="' . $link . '" data-original-title="Select or Change article"><span class="icon-file"></span> Select</a>';
		$html[] = '</div>';
		// Create the real field, hidden, that stored the user id.
		$html[] = '<input type="hidden" id="' . $fieldName . '" name="' . $fieldName . '" value="' . $fieldValue . '" />';

		return implode("\n", $html);
	}

	/**
	 * Get the invoice number for this registration record
	 */
	public static function getInvoiceNumber()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('MAX(invoice_number)')
			->from('#__eb_registrants');
		$db->setQuery($query);
		$invoiceNumber = (int) $db->loadResult();
		$invoiceNumber++;

		return max($invoiceNumber, (int) self::getConfigValue('invoice_start_number'));
	}

	/**
	 * Format invoice number
	 *
	 * @param string $invoiceNumber
	 * @param Object $config
	 *
	 * @return string formatted invoice number
	 */
	public static function formatInvoiceNumber($invoiceNumber, $config)
	{
		return $config->invoice_prefix .
		str_pad($invoiceNumber, $config->invoice_number_length ? $config->invoice_number_length : 4, '0', STR_PAD_LEFT);
	}

	/**
	 * Generate invoice PDF
	 *
	 * @param object $row
	 */
	public static function generateInvoicePDF($row)
	{
		self::loadLanguage();
		$db          = JFactory::getDbo();
		$query       = $db->getQuery(true);
		$config      = self::getConfig();
		$fieldSuffix = EventbookingHelper::getFieldSuffix($row->language);
		$sitename    = JFactory::getConfig()->get("sitename");
		$query->select('*, title' . $fieldSuffix . ' AS title')
			->from('#__eb_events')
			->where('id = ' . (int) $row->event_id);
		$db->setQuery($query);
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
		$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		$pdf->SetFont('times', '', 8);
		$pdf->AddPage();

		if ($config->multiple_booking)
		{
			if (self::isValidMessage($config->{'invoice_format_cart' . $fieldSuffix}))
			{
				$invoiceOutput = $config->{'invoice_format_cart' . $fieldSuffix};
			}
			else
			{
				$invoiceOutput = $config->invoice_format_cart;
			}
		}
		else
		{
			if (self::isValidMessage($config->{'invoice_format' . $fieldSuffix}))
			{
				$invoiceOutput = $config->{'invoice_format' . $fieldSuffix};
			}
			else
			{
				$invoiceOutput = $config->invoice_format;
			}
		}

		if (strpos($invoiceOutput, '[QRCODE]') !== false)
		{
			EventbookingHelper::generateQrcode($row->id);
			$imgTag        = '<img src="media/com_eventbooking/qrcodes/' . $row->id . '.png" border="0" />';
			$invoiceOutput = str_ireplace("[QRCODE]", $imgTag, $invoiceOutput);
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
		$form->buildFieldsDependency();

		$replaces                   = self::buildTags($row, $form, $rowEvent, $config);
		$replaces['invoice_number'] = self::formatInvoiceNumber($row->invoice_number, $config);
		$replaces['invoice_date']   = JHtml::_('date', $row->register_date, $config->date_format);

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
		unset($replaces['total_amount']);
		unset($replaces['discount_amount']);
		unset($replaces['tax_amount']);
		if ($config->multiple_booking)
		{
			$sql = 'SELECT a.title' . $fieldSuffix . ' AS title, a.event_date, b.* FROM #__eb_events AS a INNER JOIN #__eb_registrants AS b ' . ' ON a.id = b.event_id ' .
				' WHERE b.id=' . $row->id . ' OR b.cart_id=' . $row->id;
			$db->setQuery($sql);
			$rowEvents                          = $db->loadObjectList();
			$subTotal                           = $replaces['amt_total_amount'];
			$taxAmount                          = $replaces['amt_tax_amount'];
			$discountAmount                     = $replaces['amt_discount_amount'];
			$total                              = $replaces['amt_amount'];
			$paymentProcessingFee               = $replaces['amt_payment_processing_fee'];
			$replaces['EVENTS_LIST']            = EventbookingHelperHtml::loadCommonLayout(
				JPATH_ROOT . '/components/com_eventbooking/emailtemplates/invoice_items.php',
				array(
					'rowEvents'      => $rowEvents,
					'subTotal'       => $subTotal,
					'taxAmount'      => $taxAmount,
					'discountAmount' => $discountAmount,
					'total'          => $total,
					'config'         => $config));
			$replaces['SUB_TOTAL']              = EventbookingHelper::formatCurrency($subTotal, $config);
			$replaces['DISCOUNT_AMOUNT']        = EventbookingHelper::formatCurrency($discountAmount, $config);
			$replaces['TAX_AMOUNT']             = EventbookingHelper::formatCurrency($taxAmount, $config);
			$replaces['TOTAL_AMOUNT']           = EventbookingHelper::formatCurrency($total, $config);
			$replaces['PAYMENT_PROCESSING_FEE'] = EventbookingHelper::formatCurrency($paymentProcessingFee, $config);
		}
		else
		{
			$replaces['ITEM_QUANTITY']          = 1;
			$replaces['ITEM_AMOUNT']            = $replaces['ITEM_SUB_TOTAL'] = self::formatCurrency($row->total_amount, $config);
			$replaces['DISCOUNT_AMOUNT']        = self::formatCurrency($row->discount_amount, $config);
			$replaces['SUB_TOTAL']              = self::formatCurrency($row->total_amount - $row->discount_amount, $config);
			$replaces['TAX_AMOUNT']             = self::formatCurrency($row->tax_amount, $config);
			$replaces['PAYMENT_PROCESSING_FEE'] = self::formatCurrency($row->payment_processing_fee, $config);
			$replaces['TOTAL_AMOUNT']           = self::formatCurrency($row->total_amount - $row->discount_amount + $row->payment_processing_fee + $row->tax_amount, $config);
			$itemName                           = JText::_('EB_EVENT_REGISTRATION');
			$itemName                           = str_ireplace('[EVENT_TITLE]', $rowEvent->title, $itemName);
			$replaces['ITEM_NAME']              = $itemName;
		}
		foreach ($replaces as $key => $value)
		{
			$key           = strtoupper($key);
			$invoiceOutput = str_ireplace("[$key]", $value, $invoiceOutput);
		}

		$v = $pdf->writeHTML($invoiceOutput, true, false, false, false, '');
		//Filename
		$filePath = JPATH_ROOT . '/media/com_eventbooking/invoices/' . $replaces['invoice_number'] . '.pdf';
		$pdf->Output($filePath, 'F');
	}

	/**
	 * Generate QRcode for a transaction
	 *
	 * @param $registrantId
	 */
	public static function generateQrcode($registrantId)
	{
		$filename = $registrantId . '.png';
		if (!file_exists(JPATH_ROOT . '/media/com_eventbooking/qrcodes/' . $filename))
		{
			require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/vendor/phpqrcode/qrlib.php';
			$Itemid     = EventbookingHelperRoute::findView('registrants', EventbookingHelper::getItemid());
			$checkinUrl = self::getSiteUrl() . 'index.php?option=com_eventbooking&task=registrant.checkin&id=' . $registrantId . '&Itemid=' . $Itemid;
			QRcode::png($checkinUrl, JPATH_ROOT . '/media/com_eventbooking/qrcodes/' . $filename);
		}
	}

	/**
	 * Generate and download invoice of given registration record
	 *
	 * @param int $id
	 */
	public static function downloadInvoice($id)
	{
		JTable::addIncludePath(JPATH_ROOT . '/administrator/components/com_eventbooking/table');
		$config = self::getConfig();
		$row    = JTable::getInstance('EventBooking', 'Registrant');
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
			self::generateInvoicePDF($row);
			$invoicePath = $invoiceStorePath . $invoiceNumber . '.pdf';
			$fileName    = $invoiceNumber . '.pdf';
			while (@ob_end_clean()) ;
			self::processDownload($invoicePath, $fileName);
		}
	}

	/**
	 * Convert all img tags to use absolute URL
	 *
	 * @param $html_content
	 *
	 * @return string
	 */
	public static function convertImgTags($html_content)
	{
		$patterns     = array();
		$replacements = array();
		$i            = 0;
		$src_exp      = "/src=\"(.*?)\"/";
		$link_exp     = "[^http:\/\/www\.|^www\.|^https:\/\/|^http:\/\/]";
		$siteURL      = JUri::root();
		preg_match_all($src_exp, $html_content, $out, PREG_SET_ORDER);
		foreach ($out as $val)
		{
			$links = preg_match($link_exp, $val[1], $match, PREG_OFFSET_CAPTURE);
			if ($links == '0')
			{
				$patterns[$i]     = $val[1];
				$patterns[$i]     = "\"$val[1]";
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
		$fsize    = @filesize($filePath);
		$mod_date = date('r', filemtime($filePath));
		$cont_dis = 'attachment';
		if ($detectFilename)
		{
			$pos      = strpos($filename, '_');
			$filename = substr($filename, $pos + 1);
		}
		$ext  = JFile::getExt($filename);
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
	 * @param        $retbytes
	 *
	 * @return unknown
	 */
	public static function readfile_chunked($filename, $retbytes = true)
	{
		$chunksize = 1 * (1024 * 1024); // how many bytes per chunk
		$cnt       = 0;
		$handle    = fopen($filename, 'rb');
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
	 * Check to see whether the current user can
	 *
	 * @param int $eventId
	 */
	public static function checkEventAccess($eventId)
	{
		$user  = JFactory::getUser();
		$db    = JFactory::getDbo();
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
	 * Check to see whether the current users can access View List function
	 */
	public static function canViewRegistrantList()
	{
		return JFactory::getUser()->authorise('eventbooking.viewregistrantslist', 'com_eventbooking');
	}

	/**
	 *
	 * Check to see whether this users has permission to edit registrant
	 */
	public static function checkEditRegistrant($rowRegistrant)
	{
		$user      = JFactory::getUser();
		$canAccess = false;
		if ($user->id)
		{
			if ($user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking')
				|| $user->get('id') == $rowRegistrant->user_id
				|| $user->get('email') == $rowRegistrant->email
			)
			{
				$canAccess = true;
			}
		}
		
		if (!$canAccess)
		{
			JFactory::getApplication()->redirect('index.php', JText::_('NOT_AUTHORIZED'));
		}
	}

	/**
	 * Check to see whether this event can be cancelled
	 *
	 * @param int $eventId
	 *
	 * @return bool
	 */
	public static function canCancel($eventId)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(*)')
			->from('#__eb_events')
			->where('id = ' . $eventId)
			->where(' enable_cancel_registration = 1')
			->where('(DATEDIFF(cancel_before_date, NOW()) >=0)');
		$db->setQuery($query);
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
			$config = EventbookingHelper::getConfig();
			$db     = JFactory::getDbo();
			$query  = $db->getQuery(true);
			$query->select('created_by')
				->from('#__eb_events')
				->where('id = ' . (int) $eventId);
			$db->setQuery($query);
			$createdBy = (int) $db->loadResult();
			if ($config->only_show_registrants_of_event_owner)
			{
				return $createdBy > 0 && $createdBy == $user->id;
			}
			else
			{
				return (($createdBy > 0 && $createdBy == $user->id) || $user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking'));
			}

		}
		else
		{
			return $user->authorise('eventbooking.registrantsmanagement', 'com_eventbooking');
		}
	}

	public static function canChangeEventStatus($eventId)
	{
		$user = JFactory::getUser();

		if ($user->get('guest'))
		{
			return false;
		}

		if ($user->authorise('core.edit.state', 'com_eventbooking'))
		{
			return true;
		}

		return false;
	}

	/**
	 * Check to see whether the user can cancel registration for the given event
	 *
	 * @param $eventId
	 *
	 * @return bool|int
	 */
	public static function canCancelRegistration($eventId)
	{
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$user   = JFactory::getUser();
		$userId = $user->get('id');
		$email  = $user->get('email');

		if (!$userId)
		{
			return false;
		}

		$query->select('id')
			->from('#__eb_registrants')
			->where('event_id = ' . $eventId)
			->where('(user_id = ' . $userId . ' OR email = ' . $db->quote($email) . ')')
			->where('(published=1 OR (payment_method LIKE "os_offline%" AND published NOT IN (2,3)))');

		$db->setQuery($query);
		$registrantId = $db->loadResult();
		if (!$registrantId)
		{
			return false;
		}

		$query->clear();
		$query->select('COUNT(*)')
			->from('#__eb_events')
			->where('id = ' . $eventId)
			->where('enable_cancel_registration = 1')
			->where('DATEDIFF(cancel_before_date, NOW()) >= 0');
		$db->setQuery($query);
		$total = $db->loadResult();

		if (!$total)
		{
			return false;
		}

		return $registrantId;
	}

	/**
	 * Check to see whether the current user can edit registrant
	 *
	 * @param int $eventId
	 *
	 * @return boolean
	 */
	public static function checkEditEvent($eventId)
	{
		$user = JFactory::getUser();

		if ($user->get('guest'))
		{
			return false;
		}

		if (!$eventId)
		{
			return false;
		}

		$rowEvent = EventbookingHelperDatabase::getEvent($eventId);

		if (!$rowEvent)
		{
			return false;
		}

		if ($user->authorise('core.edit', 'com_eventbooking') || ($user->authorise('core.edit.own', 'com_eventbooking') && ($rowEvent->created_by == $user->get('id'))))
		{
			return true;
		}

		return false;
	}

	/**
	 * Update Group Members record to have same information with billing record
	 *
	 * @param int $groupId
	 */
	public static function updateGroupRegistrationRecord($groupId)
	{
		$db     = JFactory::getDbo();
		$config = EventbookingHelper::getConfig();
		if ($config->collect_member_information)
		{
			$row = JTable::getInstance('EventBooking', 'Registrant');
			$row->load($groupId);
			if ($row->id)
			{
				$query = $db->getQuery(true);
				$query->update('#__eb_registrants')
					->set('published = ' . $row->published)
					->set('transaction_id = ' . $db->quote($row->transaction_id))
					->set('payment_method = ' . $db->quote($row->payment_method))
					->where('group_id = ' . $row->id);

				$db->setQuery($query);
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
	 *
	 * @param array $data
	 *
	 * @return int Id of created user
	 */
	public static function saveRegistration($data)
	{
		//Need to load com_users language file
		$lang = JFactory::getLanguage();
		$tag  = $lang->getTag();
		if (!$tag)
			$tag = 'en-GB';
		$lang->load('com_users', JPATH_ROOT, $tag);
		$data['name']     = $data['first_name'] . ' ' . $data['last_name'];
		$data['password'] = $data['password2'] = $data['password1'];
		$data['email1']   = $data['email2'] = $data['email'];
		require_once JPATH_ROOT . '/components/com_users/models/registration.php';
		$model = new UsersModelRegistration();
		$ret   = $model->register($data);

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__users')
			->where('username = ' . $db->quote($data['username']));
		$db->setQuery($query);

		return (int) $db->loadResult();
	}

	/**
	 * Get list of recurring event dates
	 *
	 * @param DateTime $startDate
	 * @param DateTime $endDate
	 * @param int      $dailyFrequency
	 * @param int      $numberOccurencies
	 *
	 * @return array
	 */
	public static function getDailyRecurringEventDates($startDate, $endDate, $dailyFrequency, $numberOccurencies)
	{
		$eventDates   = array($startDate);
		$timeZone     = new DateTimeZone(JFactory::getConfig()->get('offset'));
		$date         = new DateTime($startDate, $timeZone);
		$dateInterval = new DateInterval('P' . $dailyFrequency . 'D');
		if ($numberOccurencies)
		{
			for ($i = 1; $i < $numberOccurencies; $i++)
			{
				$date->add($dateInterval);
				$eventDates[] = $date->format('Y-m-d H:i:s');
			}
		}
		else
		{
			$recurringEndDate = new DateTime($endDate . ' 23:59:59', $timeZone);
			while (true)
			{
				$date->add($dateInterval);
				if ($date <= $recurringEndDate)
				{
					$eventDates[] = $date->format('Y-m-d H:i:s');
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
	 *
	 * @param DateTime $startDate
	 * @param DateTime $endDate
	 * @param Int      $weeklyFrequency
	 * @param int      $numberOccurrences
	 * @param array    $weekDays
	 *
	 * @return array
	 */
	public static function getWeeklyRecurringEventDates($startDate, $endDate, $weeklyFrequency, $numberOccurrences, $weekDays)
	{
		$eventDates = array();

		$timeZone           = new DateTimeZone(JFactory::getConfig()->get('offset'));
		$recurringStartDate = new Datetime($startDate, $timeZone);
		$hour               = $recurringStartDate->format('H');
		$minutes            = $recurringStartDate->format('i');
		$startWeek          = clone $recurringStartDate->modify(('Sunday' == $recurringStartDate->format('l')) ? 'Sunday this week' : 'Sunday last week');
		$startWeek->setTime($hour, $minutes, 0);
		$dateInterval = new DateInterval('P' . $weeklyFrequency . 'W');
		if ($numberOccurrences)
		{
			$count = 0;
			while ($count < $numberOccurrences)
			{
				foreach ($weekDays as $weekDay)
				{
					$date = clone $startWeek;
					if ($weekDay > 0)
					{
						$date->add(new DateInterval('P' . $weekDay . 'D'));
					}
					if (($date >= $recurringStartDate) && ($count < $numberOccurrences))
					{
						$eventDates[] = $date->format('Y-m-d H:i:s');
						$count++;
					}
				}
				$startWeek->add($dateInterval);
			}
		}
		else
		{
			$recurringEndDate = new DateTime($endDate . ' 23:59:59', $timeZone);
			while (true)
			{
				foreach ($weekDays as $weekDay)
				{
					$date = clone $startWeek;
					if ($weekDay > 0)
					{
						$date->add(new DateInterval('P' . $weekDay . 'D'));
					}
					if (($date >= $recurringStartDate) && ($date <= $recurringEndDate))
					{
						$eventDates[] = $date->format('Y-m-d H:i:s');
					}
				}
				if ($date > $recurringEndDate)
				{
					break;
				}

				$startWeek->add($dateInterval);
			}
		}

		return $eventDates;
	}

	/**
	 * Get list of monthly recurring
	 *
	 * @param DateTime $startDate
	 * @param DateTime $endDate
	 * @param int      $monthlyFrequency
	 * @param int      $numberOccurrences
	 * @param string   $monthDays
	 *
	 * @return array
	 */
	public static function getMonthlyRecurringEventDates($startDate, $endDate, $monthlyFrequency, $numberOccurrences, $monthDays)
	{
		$eventDates         = array();
		$timeZone           = new DateTimeZone(JFactory::getConfig()->get('offset'));
		$recurringStartDate = new Datetime($startDate, $timeZone);
		$date               = clone $recurringStartDate;
		$dateInterval       = new DateInterval('P' . $monthlyFrequency . 'M');
		$monthDays          = explode(',', $monthDays);
		if ($numberOccurrences)
		{
			$count = 0;
			while ($count < $numberOccurrences)
			{
				$currentMonth = $date->format('m');
				$currentYear  = $date->format('Y');
				foreach ($monthDays as $day)
				{
					$date->setDate($currentYear, $currentMonth, $day);
					if (($date >= $recurringStartDate) && ($count < $numberOccurrences))
					{
						$eventDates[] = $date->format('Y-m-d H:i:s');
						$count++;
					}
				}
				$date->add($dateInterval);
			}
		}
		else
		{
			$recurringEndDate = new DateTime($endDate . ' 23:59:59', $timeZone);
			while (true)
			{
				$currentMonth = $date->format('m');
				$currentYear  = $date->format('Y');
				foreach ($monthDays as $day)
				{
					$date->setDate($currentYear, $currentMonth, $day);
					if (($date >= $recurringStartDate) && ($date <= $recurringEndDate))
					{
						$eventDates[] = $date->format('Y-m-d H:i:s');
					}
				}
				if ($date > $recurringEndDate)
				{
					break;
				}
				$date->add(new DateInterval('P' . $monthlyFrequency . 'M'));
			}
		}

		return $eventDates;
	}


	/**
	 * Get list of event dates for recurring events happen on specific date in a month
	 *
	 * @param $startDate
	 * @param $endDate
	 * @param $monthlyFrequency
	 * @param $numberOccurrences
	 * @param $n
	 * @param $day
	 *
	 * @return array
	 */
	public static function getMonthlyRecurringAtDayInWeekEventDates($startDate, $endDate, $monthlyFrequency, $numberOccurrences, $n, $day)
	{
		$eventDates         = array();
		$timeZone           = new DateTimeZone(JFactory::getConfig()->get('offset'));
		$recurringStartDate = new Datetime($startDate, $timeZone);
		$date               = clone $recurringStartDate;
		$dateInterval       = new DateInterval('P' . $monthlyFrequency . 'M');

		if ($numberOccurrences)
		{
			$count = 0;
			while ($count < $numberOccurrences)
			{
				$currentMonth = $date->format('M');
				$currentYear  = $date->format('Y');
				$timeString   = "$n $day";
				$timeString .= " of $currentMonth $currentYear";
				$date->modify($timeString);
				$date->setTime($recurringStartDate->format('H'), $recurringStartDate->format('i'), 0);
				if (($date >= $recurringStartDate) && ($count < $numberOccurrences))
				{
					$eventDates[] = $date->format('Y-m-d H:i:s');
					$count++;
				}

				$date->add($dateInterval);
			}
		}
		else
		{
			$recurringEndDate = new DateTime($endDate . ' 23:59:59', $timeZone);
			while (true)
			{
				$currentMonth = $date->format('M');
				$currentYear  = $date->format('Y');
				$timeString   = "$n $day";
				$timeString .= " of $currentMonth $currentYear";
				$date->modify($timeString);
				$date->setTime($recurringStartDate->format('H'), $recurringStartDate->format('i'), 0);
				if (($date >= $recurringStartDate) && ($date <= $recurringEndDate))
				{
					$eventDates[] = $date->format('Y-m-d H:i:s');
				}
				if ($date > $recurringEndDate)
				{
					break;
				}
				$date->add(new DateInterval('P' . $monthlyFrequency . 'M'));
			}
		}

		return $eventDates;
	}


	public static function getDeliciousButton($title, $link)
	{
		$img_url = JUri::root(true) . "/media/com_eventbooking/assets/images/socials/delicious.png";

		return '<a href="http://del.icio.us/post?url=' . rawurlencode($link) . '&amp;title=' . rawurlencode($title) . '" title="Submit ' . $title . ' in Delicious" target="blank" >
		<img src="' . $img_url . '" alt="Submit ' . $title . ' in Delicious" />
		</a>';
	}

	public static function getDiggButton($title, $link)
	{
		$img_url = JUri::root(true) . "/media/com_eventbooking/assets/images/socials/digg.png";

		return '<a href="http://digg.com/submit?url=' . rawurlencode($link) . '&amp;title=' . rawurlencode($title) . '" title="Submit ' . $title . ' in Digg" target="blank" >
        <img src="' . $img_url . '" alt="Submit ' . $title . ' in Digg" />
        </a>';
	}

	public static function getFacebookButton($title, $link)
	{
		$img_url = JUri::root(true) . "/media/com_eventbooking/assets/images/socials/facebook.png";

		return '<a href="http://www.facebook.com/sharer.php?u=' . rawurlencode($link) . '&amp;t=' . rawurlencode($title) . '" title="Submit ' . $title . ' in FaceBook" target="blank" >
        <img src="' . $img_url . '" alt="Submit ' . $title . ' in FaceBook" />
        </a>';
	}

	public static function getGoogleButton($title, $link)
	{
		$img_url = JUri::root(true) . "/media/com_eventbooking/assets/images/socials/google.png";

		return '<a href="http://www.google.com/bookmarks/mark?op=edit&bkmk=' . rawurlencode($link) . '" title="Submit ' . $title . ' in Google Bookmarks" target="blank" >
        <img src="' . $img_url . '" alt="Submit ' . $title . ' in Google Bookmarks" />
        </a>';
	}

	public static function getStumbleuponButton($title, $link)
	{
		$img_url = JUri::root(true) . "/media/com_eventbooking/assets/images/socials/stumbleupon.png";

		return '<a href="http://www.stumbleupon.com/submit?url=' . rawurlencode($link) . '&amp;title=' . rawurlencode($title) . '" title="Submit ' .
		$title . ' in Stumbleupon" target="blank" >
        <img src="' . $img_url . '" alt="Submit ' . $title . ' in Stumbleupon" />
        </a>';
	}

	public static function getTechnoratiButton($title, $link)
	{
		$img_url = JUri::root(true) . "/media/com_eventbooking/assets/images/socials/technorati.png";

		return '<a href="http://technorati.com/faves?add=' . rawurlencode($link) . '" title="Submit ' . $title . ' in Technorati" target="blank" >
        <img src="' . $img_url . '" alt="Submit ' . $title . ' in Technorati" />
        </a>';
	}

	public static function getTwitterButton($title, $link)
	{
		$img_url = JUri::root(true) . "/media/com_eventbooking/assets/images/socials/twitter.png";

		return '<a href="http://twitter.com/?status=' . rawurlencode($title . " " . $link) . '" title="Submit ' . $title . ' in Twitter" target="blank" >
        <img src="' . $img_url . '" alt="Submit ' . $title . ' in Twitter" />
        </a>';
	}

	public static function getLinkedInButton($title, $link)
	{
		$img_url = JUri::root(true) . "/media/com_eventbooking/assets/images/socials/linkedin.png";

		return '<a href="http://www.linkedin.com/shareArticle?mini=true&amp;url=' . $link . '&amp;title=' . $title . '" title="Submit ' . $title . ' in LinkedIn" target="_blank" ><img src="' . $img_url . '" alt="Submit ' . $title . ' in LinkedIn" /></a>';
	}

	/**
	 * Calculate level for categories, used when upgrade from old version to new version
	 *
	 * @param     $id
	 * @param     $list
	 * @param     $children
	 * @param int $maxlevel
	 * @param int $level
	 *
	 * @return mixed
	 */
	public static function calculateCategoriesLevel($id, $list, &$children, $maxlevel = 9999, $level = 1)
	{
		if (@$children[$id] && $level <= $maxlevel)
		{
			foreach ($children[$id] as $v)
			{
				$id        = $v->id;
				$v->level  = $level;
				$list[$id] = $v;
				$list      = self::calculateCategoriesLevel($id, $list, $children, $maxlevel, $level + 1);
			}
		}

		return $list;
	}
}