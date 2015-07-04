<?php
/**
 * @version            2.0.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

/**
 * Event Booking controller
 * @package        Joomla
 * @subpackage     Event Booking
 */
class EventbookingController extends RADController
{

	/**
	 * Display information
	 *
	 */
	public function display($cachable = false, array $urlparams = array())
	{
		$task     = $this->getTask();
		$document = JFactory::getDocument();
		$config   = EventbookingHelper::getConfig();
		if ($config->load_jquery !== '0')
		{
			EventbookingHelper::loadJQuery();
		}
		if ($config->load_bootstrap_css_in_frontend !== '0')
		{
			EventbookingHelper::loadBootstrap();
		}
		$rootUrl = JUri::root(true);
		$document->addStylesheet($rootUrl . '/components/com_eventbooking/assets/css/style.css');
		JHtml::_('script', EventbookingHelper::getURL() . 'components/com_eventbooking/assets/js/noconflict.js', false, false);

		// Load bootstrap js
		if ($config->show_save_to_personal_calendar)
		{
			EventbookingHelper::loadBootstrapJs();
		}

		if ($config->calendar_theme)
		{
			$theme = $config->calendar_theme;
		}
		else
		{
			$theme = 'default';
		}
		$document->addStylesheet($rootUrl . '/components/com_eventbooking/assets/css/themes/' . $theme . '.css');
		$document->addStylesheet($rootUrl . '/components/com_eventbooking/assets/css/custom.css');

		switch ($task)
		{
			case 'view_category':
				$this->input->set('view', 'category');
				break;
			case 'individual_registration':
				$this->input->set('view', 'register');
				$this->input->set('layout', 'default');
				break;
			case 'group_registration':
				$this->input->set('view', 'register');
				$this->input->set('layout', 'group');
				break;
			case 'cancel':
				$this->input->set('view', 'cancel');
				$this->input->set('layout', 'default');
				break;

			#Registrants
			case 'edit_registrant':
				$this->input->set('view', 'registrant');
				break;
			case 'add_registrant':
				$this->input->set('view', 'registrant');
				$this->input->set('form', false);
				break;

			#Cart function
			case 'view_cart':
				$this->input->set('view', 'cart');
				$this->input->set('layout', 'default');
				break;
			case 'view_checkout':
			case 'checkout':
				$this->input->set('view', 'register');
				$this->input->set('layout', 'cart');
				break;

			#Adding, managing events from front-end			
			case 'edit_event':
				$this->input->set('view', 'event');
				$this->input->set('layout', 'form');
				break;

			#Location management
			case 'edit_location':
				$this->input->set('view', 'addlocation');
				$this->input->set('layout', 'default');
				break;

			case 'add_location':
				$this->input->set('view', 'addlocation');
				$this->input->set('edit', false);
				break;
			default:

				$view = $this->input->getCmd('view');
				if (!$view)
				{
					$this->input->set('view', 'categories');
					$this->input->set('layout', 'default');
				}
				break;
		}

		parent::display($cachable, $urlparams);
	}

	/**
	 * Send reminder to registrants about events
	 */
	public function event_reminder()
	{
		$model = $this->getModel('reminder');
		$model->sendReminder();
		JFactory::getApplication()->close();
	}


	/**
	 * Process download a file
	 */
	public function download_file()
	{
		$filePath = JPATH_ROOT . '/media/com_eventbooking/files';
		$fileName = basename($this->input->getString('file_name'));
		if (file_exists($filePath . '/' . $fileName))
		{
			while (@ob_end_clean()) ;
			EventbookingHelper::processDownload($filePath . '/' . $fileName, $fileName, true);
			$this->app->close();
		}
		else
		{
			$this->app->redirect('index.php?option=com_eventbooking&Itemid=' . $this->input->getInt('Itemid'), JText::_('File does not exist'));
		}
	}

	/**
	 * Validate the username, make sure it has not been registered by someone else
	 */
	public function validate_username()
	{
		$db         = JFactory::getDbo();
		$query      = $db->getQuery(true);
		$username   = JRequest::getVar('fieldValue');
		$validateId = JRequest::getVar('fieldId');
		$query->select('COUNT(*)')
			->from('#__users')
			->where('username="' . $username . '"');
		$db->setQuery($query);
		$total        = $db->loadResult();
		$arrayToJs    = array();
		$arrayToJs[0] = $validateId;
		if ($total)
		{
			$arrayToJs[1] = false;
		}
		else
		{
			$arrayToJs[1] = true;
		}
		echo json_encode($arrayToJs);
		JFactory::getApplication()->close();
	}

	/**
	 * Validate the email
	 */
	public function validate_email()
	{
		$app          = JFactory::getApplication();
		$db           = JFactory::getDbo();
		$user         = JFactory::getUser();
		$config       = EventbookingHelper::getConfig();
		$query        = $db->getQuery(true);
		$email        = $app->input->get('fieldValue', '', 'string');
		$eventId      = $app->input->getInt('event_id', 0);
		$validateId   = $app->input->get('fieldId', '');
		$arrayToJs    = array();
		$arrayToJs[0] = $validateId;
		if ($config->prevent_duplicate_registration && !$config->multiple_booking)
		{
			$query->clear();
			$query->select('COUNT(id)')
				->from('#__eb_registrants')
				->where('event_id=' . $eventId)
				->where('email="' . $email . '"')
				->where('(published=1 OR (payment_method LIKE "os_offline%" AND published NOT IN (2,3)))');
			$db->setQuery($query);
			$total = $db->loadResult();
			if ($total)
			{
				$arrayToJs[1] = false;
				$arrayToJs[2] = JText::_('EB_EMAIL_REGISTER_FOR_EVENT_ALREADY');
			}
		}
		if (!isset($arrayToJs[1]))
		{
			$query->clear();
			$query->select('COUNT(*)')
				->from('#__users')
				->where('email="' . $email . '"');
			$db->setQuery($query);
			$total = $db->loadResult();
			if (!$total || $user->id || !$config->user_registration)
			{
				$arrayToJs[1] = true;
			}
			else
			{
				$arrayToJs[1] = false;
				$arrayToJs[2] = JText::_('EB_EMAIL_USED_BY_OTHER_CUSTOMER');
			}
		}
		echo json_encode($arrayToJs);
		JFactory::getApplication()->close();
	}

	/**
	 * Get list of states for the selected country, using in AJAX request
	 */
	public function get_states()
	{
		$countryName = $this->input->getString('country_name', '');
		$fieldName   = $this->input->getString('field_name', 'state');
		$stateName   = $this->input->getString('state_name', '');
		if (!$countryName)
		{
			$countryName = EventbookingHelper::getConfigValue('default_country');
		}
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->clear();
		$query->select('required')
			->from('#__eb_fields')
			->where('name=' . $db->quote('state'));
		$db->setQuery($query);
		$required = $db->loadResult();
		($required) ? $class = 'validate[required]' : $class = '';

		$query->clear();
		$query->select('country_id')
			->from('#__eb_countries')
			->where('name=' . $db->quote($countryName));
		$db->setQuery($query);
		$countryId = $db->loadResult();

		//get states
		$query->clear();
		$query->select('state_name AS value, state_name AS text')
			->from('#__eb_states')
			->where('country_id=' . (int) $countryId);;
		$db->setQuery($query);
		$states  = $db->loadObjectList();
		$options = array();
		if (count($states))
		{
			$options[] = JHtml::_('select.option', '', JText::_('EB_SELECT_STATE'));
			$options   = array_merge($options, $states);
		}
		else
		{
			$options[] = JHtml::_('select.option', 'N/A', JText::_('EB_NA'));
		}
		echo JHtml::_('select.genericlist', $options, $fieldName, ' class="input-large ' . $class . '" id="' . $fieldName . '"', 'value', 'text',
			$stateName);
		$this->app->close();
	}

	/**
	 * Get depend fields status
	 *
	 */
	public function get_depend_fields_status()
	{
		$app            = JFactory::getApplication();
		$input          = $app->input;
		$db             = JFactory::getDbo();
		$query          = $db->getQuery(true);
		$fieldId        = $input->getInt('field_id', 0);
		$fieldSuffix    = $input->getString('field_suffix', '');
		$languageSuffix = EventbookingHelper::getFieldSuffix();

		//Get list of depend fields
		$allFieldIds = EventbookingHelper::getAllDependencyFields($fieldId);

		$query->select('*')
			->select('title' . $languageSuffix . ' AS title')
			->select('depend_on_options' . $languageSuffix . ' AS depend_on_options')
			->from('#__eb_fields')
			->where('published=1')
			->where('id IN (' . implode(',', $allFieldIds) . ')')
			->order('ordering');
		$db->setQuery($query);
		$rowFields    = $db->loadObjectList();
		$masterFields = array();
		$fieldsAssoc  = array();
		foreach ($rowFields as $rowField)
		{
			if ($rowField->depend_on_field_id)
			{
				$masterFields[] = $rowField->depend_on_field_id;
			}
			$fieldsAssoc[$rowField->id] = $rowField;
		}
		$masterFields = array_unique($masterFields);
		if (count($masterFields))
		{
			$hiddenFields = array();
			foreach ($rowFields as $rowField)
			{
				if ($rowField->depend_on_field_id && isset($fieldsAssoc[$rowField->depend_on_field_id]))
				{
					// If master field is hided, then children field should be hided, too
					if (in_array($rowField->depend_on_field_id, $hiddenFields))
					{
						$hiddenFields[] = $rowField->id;
					}
					else
					{
						if ($fieldSuffix)
						{
							$fieldName = $fieldsAssoc[$rowField->depend_on_field_id]->name . '_' . $fieldSuffix;
						}
						else
						{
							$fieldName = $fieldsAssoc[$rowField->depend_on_field_id]->name;
						}

						$masterFieldValues = $input->get($fieldName, '', 'none');

						if (is_array($masterFieldValues))
						{
							$selectedOptions = $masterFieldValues;
						}
						else
						{
							$selectedOptions = array($masterFieldValues);
						}
						$dependOnOptions = explode(',', $rowField->depend_on_options);
						if (!count(array_intersect($selectedOptions, $dependOnOptions)))
						{
							$hiddenFields[] = $rowField->id;
						}
					}
				}
			}
		}

		$showFields = array();
		$hideFields = array();
		foreach ($rowFields as $rowField)
		{
			if (in_array($rowField->id, $hiddenFields))
			{
				$hideFields[] = 'field_' . $rowField->name . ($fieldSuffix ? '_' . $fieldSuffix : '');
			}
			else
			{
				$showFields[] = 'field_' . $rowField->name . ($fieldSuffix ? '_' . $fieldSuffix : '');
			}
		}
		echo json_encode(array('show_fields' => implode(',', $showFields), 'hide_fields' => implode(',', $hideFields)));

		$app->close();
	}

	/**
	 * Confirm the payment . Used for Paypal base payment gateway
	 */
	public function payment_confirm()
	{
		$model = $this->getModel('Register');
		$model->paymentConfirm();
	}
}