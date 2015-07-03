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

		/*switch ($task)
		{
			case 'view_category':
				JRequest::setVar('view', 'category');
				break;
			case 'individual_registration':
				JRequest::setVar('view', 'register');
				JRequest::setVar('layout', 'default');
				break;
			case 'group_registration':
				JRequest::setVar('view', 'register');
				JRequest::setVar('layout', 'group');
				break;
			case 'view_calendar':
				JRequest::setVar('view', 'calendar');
				JRequest::setVar('layout', 'default');
				break;
			case 'return':
				JRequest::setVar('view', 'complete');
				JRequest::setVar('layout', 'default');
				break;
			case 'cancel':
				JRequest::setVar('view', 'cancel');
				JRequest::setVar('layout', 'default');
				break;
			#Registrants										
			case 'edit_registrant':
				JRequest::setVar('view', 'registrant');
				break;
			case 'add_registrant':
				JRequest::setVar('view', 'registrant');
				JRequest::setVar('form', false);
				break;
			#Cart function					
			case 'view_cart':
				JRequest::setVar('view', 'cart');
				JRequest::setVar('layout', 'default');
				break;
			case 'view_checkout':
				JRequest::setVar('view', 'register');
				JRequest::setVar('layout', 'cart');
				break;
			case 'checkout':
				JRequest::setVar('view', 'register');
				JRequest::setVar('layout', 'cart');
				break;
			#Adding, managing events from front-end			
			case 'edit_event':
				JRequest::setVar('view', 'event');
				JRequest::setVar('layout', 'form');
				break;
			#Location management			
			case 'edit_location':
				JRequest::setVar('view', 'addlocation');
				JRequest::setVar('layout', 'default');
				break;
			case 'add_location':
				JRequest::setVar('view', 'addlocation');
				JRequest::setVar('edit', false);
				break;
			default:
				$view = JRequest::getVar('view', '');
				if (!$view)
				{
					JRequest::setVar('view', 'categories');
					JRequest::setVar('layout', 'default');
				}
				break;
		}
		*/
		parent::display($cachable, $urlparams);
	}

	/**
	 * Send reminder to registrants about events
	 */
	public function event_reminder()
	{
		$model = $this->getModel('reminder');
		$model->sendReminder();
		exit();
	}


	/**
	 * Process download a file
	 */
	public function download_file()
	{
		$Itemid   = JRequest::getInt('Itemid');
		$filePath = JPATH_ROOT . '/media/com_eventbooking/files';
		$fileName = JRequest::getVar('file_name', '');
		if (file_exists($filePath . '/' . $fileName))
		{
			while (@ob_end_clean()) ;
			EventbookingHelper::processDownload($filePath . '/' . $fileName, $fileName, true);
			JFactory::getApplication()->close();
		}
		else
		{
			JFactory::getApplication()->redirect('index.php?option=com_eventbooking&Itemid=' . $Itemid, JText::_('File does not exist'));
		}
	}


	/**
	 * Get list of states for the selected country, using in AJAX request
	 */
	public function get_states()
	{
		$app         = JFactory::getApplication();
		$countryName = $app->input->get('country_name', '', 'string');
		$fieldName   = $app->input->get('field_name', 'state', 'string');
		$stateName   = $app->input->get('state_name', '', 'string');
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
		//get state
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
		$app->close();
	}

	/**
	 * Helper method for debugging Paypal IPN
	 *
	 */
	public function debug_paypal_ipn()
	{
		error_reporting(E_ALL);
		$ipnMessage = '';
		if ($ipnMessage)
		{
			$pairs = explode(", ", $ipnMessage);
			foreach ($pairs as $pair)
			{
				$keyValue = explode('=', $pair);
				if (count($keyValue) == 2 && $keyValue[1])
				{
					$_POST[$keyValue[0]] = $keyValue[1];
				}
			}

			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_eventbooking/table');
			$method = os_payments::getPaymentMethod('os_paypal');
			$method->verifyPayment();
		}
	}
}