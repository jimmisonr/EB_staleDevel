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
}