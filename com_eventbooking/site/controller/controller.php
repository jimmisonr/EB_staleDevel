<?php
/**
 * @version        	1.7.4
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

/**
 * Event Booking controller
 * @package		Joomla
 * @subpackage	Event Booking
 */
class EventbookingController extends JControllerLegacy
{

	/**
	 * Display information
	 *
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$task = $this->getTask();
		$document = JFactory::getDocument();
		$config = EventbookingHelper::getConfig();
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
		
		parent::display($cachable, $urlparams);
	}
	/**
	 * Check password
	 */
	public function check_event_password()
	{
		$password = JRequest::getVar('password', '');
		$eventId = JRequest::getInt('event_id', 0);
		$return = JRequest::getVar('return', '');
		$model = $this->getModel('Register');
		$success = $model->checkPassword($eventId, $password);
		if ($success)
		{
			JFactory::getSession()->set('eb_passowrd_'.$eventId, 1);
			$return = base64_decode($return);
			$this->setRedirect($return);
		}
		else
		{
			// Redirect back to password view
			$Itemid = JRequest::getInt('Itemid', 0);
			$url = JRoute::_('index.php?option=com_eventbooking&view=password&event_id='.$eventId.'&return='.$return.'&Itemid='.$Itemid, false);
			$this->setMessage(JText::_('EB_INVALID_EVENT_PASSWORD'), 'error');
			$this->setRedirect($url);
		}
	}
	/**
	 * Display registration form for Individual registration
	 */
	public function individual_registration()
	{
		$input = JFactory::getApplication()->input;
		$config = EventbookingHelper::getConfig();
		$user = JFactory::getUser();
		$eventId = $input->getInt('event_id');
		if (!$eventId)
		{
			return;
		}
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_events')
			->where('id=' . $eventId);
		$db->setQuery($query);
		$event = $db->loadObject();
		if (!$event)
		{
			return;
		}
		if ($event->event_password)
		{
			$passwordPassed = JFactory::getSession()->get('eb_passowrd_'.$event->id, 0);
			if (!$passwordPassed)
			{
				$return = base64_encode(JUri::getInstance()->toString());
				JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_eventbooking&view=password&event_id='.$event->id.'&return='.$return.'&Itemid='.$input->getInt('Itemid', 0), false));
			}
		}
		$query->clear();
		if ($config->custom_field_by_category)
		{						
			$query->select('category_id')
			->from('#__eb_event_categories')
			->where('event_id=' . $event->id)
			->where('main_category=1');
			$db->setQuery($query);
			$categoryId = (int) $db->loadResult();
			$query->clear();
			$query->select('COUNT(id)')
			->from('#__eb_fields')
			->where('published=1 AND fee_field=1 AND (category_id = 0 OR category_id=' . $categoryId . ')');
			$db->setQuery($query);
			$total = (int) $db->loadResult();
		}
		else 
		{			
			$query->select('COUNT(id)')
			->from('#__eb_fields')
			->where('published=1 AND fee_field=1 AND (event_id = -1 OR id IN (SELECT field_id FROM #__eb_field_events WHERE event_id='.$eventId.'))');
			$db->setQuery($query);
			$total = (int) $db->loadResult();
		}
		
		if ($config->simply_registration_process && $event->individual_price == 0 && $total == 0 && $user->id)
		{			
			$rowFields = EventbookingHelper::getFormFields($eventId, 0);
			$data = EventbookingHelper::getFormData($rowFields, $eventId, $user->id, $config);			
			$name = $user->name;
			$pos = strpos($name, ' ');
			if ($pos !== false)
			{
				$data['first_name'] = substr($name, 0, $pos);
				$data['last_name'] = substr($name, $pos + 1);
			}
			else
			{
				$data['first_name'] = $name;
			}
			$data['email'] = $user->email;
			$data['event_id'] = $eventId;
			$model = $this->getModel('Register');
			$model->processIndividualRegistration($data);
		}
		else
		{
			$this->display();
		}
	}
	/**
	 * Process individual registration
	 */
	public function process_individual_registration()
	{
		$app     = JFactory::getApplication();
		$session = JFactory::getSession();
		$input   = $app->input;
		$eventId = $input->getInt('event_id', 0);
		if (!$eventId)
		{
			return;
		}
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_events')
			->where('id=' . $eventId);
		$db->setQuery($query);
		$event = $db->loadObject();
		if (!$event)
		{
			return;
		}

		$user   = JFactory::getUser();
		$config = EventbookingHelper::getConfig();
		$emailValid   = true;
		$captchaValid = true;

		// Check email
		$result = $this->_validateEmail($eventId, $input->get('email', '', 'none'));

		if (!$result['success'])
		{
			$emailValid = false;
		}
		else
		{
			if ($config->enable_captcha && ($user->id == 0 || $config->bypass_captcha_for_registered_user !== '1'))
			{
				$captchaPlugin = JFactory::getApplication()->getParams()->get('captcha', JFactory::getConfig()->get('captcha'));
				if (!$captchaPlugin)
				{
					// Hardcode to recaptcha, reduce support request
					$captchaPlugin = 'recaptcha';
				}
				$plugin = JPluginHelper::getPlugin('captcha', $captchaPlugin);
				if ($plugin)
				{
					$captchaValid = JCaptcha::getInstance($captchaPlugin)->checkAnswer($input->post->get('recaptcha_response_field', '', 'string'));
				}
			}
		}

		if (!$emailValid || !$captchaValid)
		{
			// Enqueue the error message
			if (!$emailValid)
			{
				$app->enqueueMessage($result['message'], 'warning');
			}
			else
			{
				$app->enqueueMessage(JText::_('EB_INVALID_CAPTCHA_ENTERED'), 'warning');
			}

			$fromArticle = $input->post->getInt('from_article', 0);
			if ($fromArticle)
			{
				$formData = JRequest::get('post');
				$session->set('eb_form_data', serialize($formData));
				$session->set('eb_catpcha_invalid', 1);
				$app->redirect($session->get('eb_artcile_url'));

				return;
			}
			else
			{
				$input->set('captcha_invalid', 1);
				$this->execute('individual_registration');

				return;
			}
		}
		$session->clear('eb_catpcha_invalid');
		$post  = JRequest::get('post', JREQUEST_ALLOWHTML);
		$model = $this->getModel('Register');
		$model->processIndividualRegistration($post);
	}
	/**
	 * Store number of registrants and return form allow entering group members information
	 */
	public function store_number_registrants()
	{
		$config = EventbookingHelper::getConfig();
		$input = JFactory::getApplication()->input;
		$session = JFactory::getSession();
		$session->set('eb_number_registrants', $input->getInt('number_registrants'));
		if ($config->collect_member_information)
		{
			JRequest::setVar('view', 'register');
			JRequest::setVar('layout', 'group_members');
		}
		else
		{
			JRequest::setVar('view', 'register');
			JRequest::setVar('layout', 'group_billing');
		}
		$this->display();
	}
	/**
	 * Store group members data and display group billing form
	 */
	public function store_group_members_data()
	{
		$input = JFactory::getApplication()->input;
		$membersData = JRequest::get('post', JREQUEST_ALLOWHTML);
		$session = JFactory::getSession();
		$session->set('eb_group_members_data', serialize($membersData));
		$eventId = $input->getInt('event_id', 0);
		$showBillingStep = EventbookingHelper::showBillingStep($eventId);
		if (!$showBillingStep)
		{
			$this->process_group_registration();
		}
		else
		{
			JRequest::setVar('view', 'register');
			JRequest::setVar('layout', 'group_billing');
			$this->display();
		}
	}
	/**
	 * Process group registration
	 */
	public function process_group_registration()
	{
		$app     = JFactory::getApplication();
		$session = JFactory::getSession();
		$input   = $app->input;
		$eventId = $input->getInt('event_id');
		if (!$eventId)
		{
			return;
		}
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_events')
			->where('id=' . $eventId);
		$db->setQuery($query);
		$event = $db->loadObject();
		if (!$event)
		{
			return;
		}
		$config = EventbookingHelper::getConfig();
		$user = JFactory::getUser();

		$emailValid   = true;
		$captchaValid = true;

		// Check email
		$result = $this->_validateEmail($eventId, $input->get('email', '', 'none'));

		if (!$result['success'])
		{
			$emailValid = false;
		}
		else
		{
			if ($config->enable_captcha && ($user->id == 0 || $config->bypass_captcha_for_registered_user !== '1'))
			{
				$captchaPlugin = JFactory::getApplication()->getParams()->get('captcha', JFactory::getConfig()->get('captcha'));
				if (!$captchaPlugin)
				{
					// Hardcode to recaptcha, reduce support request
					$captchaPlugin = 'recaptcha';
				}
				$plugin = JPluginHelper::getPlugin('captcha', $captchaPlugin);
				if ($plugin)
				{
					$captchaValid = JCaptcha::getInstance($captchaPlugin)->checkAnswer($input->post->get('recaptcha_response_field', '', 'string'));
				}
			}
		}

		if (!$emailValid || !$captchaValid)
		{
			// Enqueue the error message
			if (!$emailValid)
			{
				$app->enqueueMessage($result['message'], 'warning');
			}
			else
			{
				$app->enqueueMessage(JText::_('EB_INVALID_CAPTCHA_ENTERED'), 'warning');
			}

			$data = JRequest::get('post', JREQUEST_ALLOWHTML);
			$session->set('eb_group_billing_data', serialize($data));
			$input->set('captcha_invalid', 1);
			JRequest::setVar('view', 'register');
			JRequest::setVar('layout', 'group');
			$this->display();
			return;
		}

		// Check to see if there is a valid number registrants
		$numberRegistrants = (int) $session->get('eb_number_registrants', '');
		if (!$numberRegistrants)
		{
			// Session was lost for some reasons, users will have to start over again
			if ($config->use_https)
			{
				$ssl = 1;
			}
			else
			{
				$ssl = 0;
			}
			$Itemid    = $input->getInt('Itemid', 0);
			$signupUrl = JRoute::_('index.php?option=com_eventbooking&task=group_registration&event_id=' . $eventId . '&Itemid=' . $Itemid, false, $ssl);
			$app->redirect($signupUrl, JText::_('Sorry, your session was expired. Please try again!'));
		}

		$post = JRequest::get('post', JREQUEST_ALLOWHTML);
		$model = $this->getModel('Register');
		$model->processGroupRegistration($post);
	}
	/**
	 * Confirm the payment . Used for Paypal base payment gateway
	 */
	public function payment_confirm()
	{
		$model = $this->getModel('Register');
		$model->paymentConfirm();
	}

	/**
	 * 
	 * Add an events and store it to 
	 */
	function add_cart()
	{
		$data = JRequest::get();
		$model = $this->getModel('cart');
		$model->processAddToCart($data);
		JRequest::setVar('view', 'cart');
		JRequest::setVar('layout', 'mini');
		$this->display();
		JFactory::getApplication()->close();
	}

	/**
	 * 
	 * Update cart with new quantities
	 */
	public function update_cart()
	{
		$Itemid = JRequest::getInt('Itemid', 0);
		$redirect = JRequest::getInt('redirect', 1);
		$eventIds = JRequest::getVar('event_id');
		$quantities = JRequest::getVar('quantity');
		$model = $this->getModel('cart');
		if (!$redirect)
		{
			$eventIds = explode(',', $eventIds);
			$quantities = explode(',', $quantities);
		}
		$model->processUpdateCart($eventIds, $quantities);
		if ($redirect)
		{
			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('cart', $Itemid), false));
		}
		else
		{
			JRequest::setVar('view', 'cart');
			JRequest::setVar('layout', 'mini');
			$this->display();
			JFactory::getApplication()->close();
		}
	}

	/**
	 * Remove an event from shopping cart
	 *
	 */
	public function remove_cart()
	{
		$redirect = JRequest::getInt('redirect', 1);
		$Itemid = JRequest::getInt('Itemid', 0);
		$id = JRequest::getInt('id', 0);
		$model = & $this->getModel('cart');
		$model->removeEvent($id);
		if ($redirect)
		{
			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('cart', $Itemid), false));
		}
		else
		{
			JRequest::setVar('view', 'cart');
			JRequest::setVar('layout', 'mini');
			$this->display();
			JFactory::getApplication()->close();
		}
	}
	/**
	 * Process checkout
	 */
	public function process_checkout()
	{
		$app     = JFactory::getApplication();
		$input   = $app->input;
		$emailValid   = true;
		$captchaValid = true;

		// Check email
		$result = $this->_validateEmail(0, $input->get('email', '', 'none'));

		if (!$result['success'])
		{
			$emailValid = false;
		}
		else
		{
			$config = EventbookingHelper::getConfig();
			$user = JFactory::getUser();
			if ($config->enable_captcha && ($user->id == 0 || $config->bypass_captcha_for_registered_user !== '1'))
			{
				$captchaPlugin = JFactory::getApplication()->getParams()->get('captcha', JFactory::getConfig()->get('captcha'));
				if (!$captchaPlugin)
				{
					// Hardcode to recaptcha, reduce support request
					$captchaPlugin = 'recaptcha';
				}
				$plugin = JPluginHelper::getPlugin('captcha', $captchaPlugin);
				if ($plugin)
				{
					$captchaValid = JCaptcha::getInstance($captchaPlugin)->checkAnswer($input->post->get('recaptcha_response_field', '', 'string'));
				}
			}
		}

		if (!$emailValid || !$captchaValid)
		{
			// Enqueue the error message
			if (!$emailValid)
			{
				$app->enqueueMessage($result['message'], 'warning');
			}
			else
			{
				$app->enqueueMessage(JText::_('EB_INVALID_CAPTCHA_ENTERED'), 'warning');
			}
			$input->set('captcha_invalid', 1);
			JRequest::setVar('view', 'register');
			JRequest::setVar('layout', 'cart');
			$this->display();
			return;

		}

		$cart                   = new EventbookingHelperCart();
		$items                  = $cart->getItems();
		if (!count($items))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('Sorry, your session was expired. Please try again!'));
		}

		$post = JRequest::get('post');
		$model = $this->getModel('cart');
		$model->processCheckout($post);
	}
	/**
	 * Validate the username, make sure it has not been registered by someone else
	 */
	public function validate_username()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$username = JRequest::getVar('fieldValue');
		$validateId = JRequest::getVar('fieldId');
		$query->select('COUNT(*)')
			->from('#__users')
			->where('username="' . $username . '"');
		$db->setQuery($query);
		$total = $db->loadResult();
		$arrayToJs = array();
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
		$app = JFactory::getApplication();
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$config = EventbookingHelper::getConfig();
		$query = $db->getQuery(true);
		$email = $app->input->get('fieldValue', '', 'string');
		$eventId = $app->input->getInt('event_id', 0);
		$validateId = $app->input->get('fieldId', '');
		$arrayToJs = array();
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
	 * Validate to see whether this email can be used to register for this event or not
	 *
	 * @param $eventId
	 * @param $email
	 *
	 * @return array
	 */
	protected function _validateEmail($eventId, $email)
	{
		$user   = JFactory::getUser();
		$db     = JFactory::getDbo();
		$query  = $db->getQuery(true);
		$config = EventbookingHelper::getConfig();
		$result = array(
			'success' => true,
			'message' => ''
		);

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
				$result['success'] = false;
				$result['message'] = JText::_('EB_EMAIL_REGISTER_FOR_EVENT_ALREADY');
			}
		}

		if ($result['success'] && $config->user_registration && !$user->id)
		{
			$query->clear();
			$query->select('COUNT(*)')
				->from('#__users')
				->where('email="' . $email . '"');
			$db->setQuery($query);
			$total = $db->loadResult();
			if ($total)
			{
				$result['success'] = false;
				$result['message'] = JText::_('EB_EMAIL_REGISTER_FOR_EVENT_ALREADY');
			}
		}

		return $result;
	}
	/**
	 * Calculate registration fee, then update the information on registration form
	 */
	function calculate_individual_registration_fee()
	{
		$config = EventbookingHelper::getConfig();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$eventId = JRequest::getInt('event_id', 0);
		$data = JRequest::get('post', JREQUEST_ALLOWHTML);
		$paymentMethod = JRequest::getVar('payment_method', '');

		$query->select('*')
			->from('#__eb_events')
			->where('id=' . $eventId);
		$db->setQuery($query);
		$event = $db->loadObject();
		$rowFields = EventbookingHelper::getFormFields($eventId, 0);
		$form = new RADForm($rowFields);
		$form->bind($data);
		$fees = EventbookingHelper::calculateIndividualRegistrationFees($event, $form, $data, $config, $paymentMethod);

		$response = array();
		$response['total_amount'] = EventbookingHelper::formatAmount($fees['total_amount'], $config);
		$response['discount_amount'] = EventbookingHelper::formatAmount($fees['discount_amount'], $config);
		$response['tax_amount'] = EventbookingHelper::formatAmount($fees['tax_amount'], $config);
		$response['payment_processing_fee'] = EventbookingHelper::formatAmount($fees['payment_processing_fee'], $config);
		$response['amount'] = EventbookingHelper::formatAmount($fees['amount'], $config);
		$response['deposit_amount'] = EventbookingHelper::formatAmount($fees['deposit_amount'], $config);
		$response['coupon_valid'] = $fees['coupon_valid'];

		echo json_encode($response);
		JFactory::getApplication()->close();
	}
	/**
	 * Calculate registration fee, then update information on group registration form
	 */
	function calculate_group_registration_fee()
	{
		$config = EventbookingHelper::getConfig();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$eventId = JRequest::getInt('event_id', 0);
		$data = JRequest::get('post', JREQUEST_ALLOWHTML);
		$paymentMethod = JRequest::getVar('payment_method', '');
		$query->select('*')
			->from('#__eb_events')
			->where('id=' . $eventId);
		$db->setQuery($query);
		$event = $db->loadObject();
		$rowFields = EventbookingHelper::getFormFields($eventId, 1);
		$form = new RADForm($rowFields);
		$form->bind($data);

		$fees = EventbookingHelper::calculateGroupRegistrationFees($event, $form, $data, $config, $paymentMethod);

		$response = array();
		$response['total_amount'] = EventbookingHelper::formatAmount($fees['total_amount'], $config);
		$response['discount_amount'] = EventbookingHelper::formatAmount($fees['discount_amount'], $config);
		$response['tax_amount'] = EventbookingHelper::formatAmount($fees['tax_amount'], $config);
		$response['payment_processing_fee'] = EventbookingHelper::formatAmount($fees['payment_processing_fee'], $config);
		$response['amount'] = EventbookingHelper::formatAmount($fees['amount'], $config);
		$response['deposit_amount'] = EventbookingHelper::formatAmount($fees['deposit_amount'], $config);
		$response['coupon_valid'] = $fees['coupon_valid'];
		echo json_encode($response);
		JFactory::getApplication()->close();
	}
	/**
	 * Calculate registration fee, then update information on cart registration form
	 */
	function calculate_cart_registration_fee()
	{
		$input = JFactory::getApplication()->input;
		$config = EventbookingHelper::getConfig();
		$paymentMethod = $input->getString('payment_method', '');
		$data = JRequest::get('post', JREQUEST_ALLOWHTML);
		$data['coupon_code'] = $input->getString('coupon_code', '');
		$cart = new EventbookingHelperCart();
		$response = array();
		$rowFields = EventbookingHelper::getFormFields(0, 4);
		$form = new RADForm($rowFields);
		$form->bind($data);

		$fees = EventbookingHelper::calculateCartRegistrationFee($cart, $form, $data, $config, $paymentMethod);
		$response['total_amount'] = EventbookingHelper::formatAmount($fees['total_amount'], $config);
		$response['discount_amount'] = EventbookingHelper::formatAmount($fees['discount_amount'], $config);
		$response['tax_amount'] = EventbookingHelper::formatAmount($fees['tax_amount'], $config);
		$response['payment_processing_fee'] = EventbookingHelper::formatAmount($fees['payment_processing_fee'], $config);
		$response['amount'] = EventbookingHelper::formatAmount($fees['amount'], $config);
		$response['deposit_amount'] = EventbookingHelper::formatAmount($fees['deposit_amount'], $config);
		$response['coupon_valid'] = $fees['coupon_valid'];
		echo json_encode($response);
		JFactory::getApplication()->close();
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
	 * Save the registration record and back to registration record list
	 */
	public function save_registrant()
	{
		$Itemid = JRequest::getInt('Itemid', 0);
		$model = & $this->getModel('registrant');
		$post = JRequest::get('post');
		$model->store($post);
		$from = JRequest::getVar('from', '');
		if ($from == 'history')
		{
			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('history', $Itemid), false));
		}
		else
		{
			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('registrants', $Itemid), false));
		}
	}

	/**
	 * Cancel registration for the event
	 */
	public function cancel_registration()
	{
		$app              = JFactory::getApplication();
		$db               = JFactory::getDbo();
		$query            = $db->getQuery(true);
		$user             = JFactory::getUser();
		$Itemid           = JRequest::getInt('Itemid', 0);
		$id               = JRequest::getInt('id');
		$registrationCode = JRequest::getVar('cancel_code', '');
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		if ($id)
		{
			$query->select('a.id, a.title' . $fieldSuffix . ' AS title, b.user_id, cancel_before_date, DATEDIFF(cancel_before_date, NOW()) AS number_days')
				->from('#__eb_events AS a')
				->innerJoin('#__eb_registrants AS b ON a.id = b.event_id')
				->where('b.id = ' . $id);
		}
		else
		{
			$query->select('a.id, a.title' . $fieldSuffix . ' AS title, b.id AS registrant_id, b.user_id, cancel_before_date, DATEDIFF(cancel_before_date, NOW()) AS number_days')
				->from('#__eb_events AS a')
				->innerJoin('#__eb_registrants AS b ON a.id = b.event_id')
				->where('b.registration_code = ' . $db->quote($registrationCode));
		}
		$db->setQuery($query);
		$rowEvent = $db->loadObject();

		if (!$rowEvent)
		{
			$app->redirect(JRoute::_('index.php?option=com_eventbooking&Itemid=' . $Itemid), JText::_('EB_INVALID_ACTION'));
		}

		if (($user->get('id') == 0 && !$registrationCode) || ($user->get('id') != $rowEvent->user_id))
		{
			$app->redirect(JRoute::_('index.php?option=com_eventbooking&Itemid=' . $Itemid), JText::_('EB_INVALID_ACTION'));
		}

		if ($rowEvent->number_days < 0)
		{
			$msg = JText::sprintf('EB_CANCEL_DATE_PASSED', JHtml::_('date', $rowEvent->cancel_before_date, EventbookingHelper::getConfigValue('date_format'), null));
			$app->redirect(JRoute::_('index.php?option=com_eventbooking&Itemid=' . $Itemid), $msg);
		}

		if ($registrationCode)
		{
			$id = $rowEvent->registrant_id;
		}

		$model = $this->getModel('register');
		$model->cancelRegistration($id);
		$this->setRedirect(JRoute::_('index.php?option=com_eventbooking&view=registrationcancel&id=' . $id . '&Itemid=' . $Itemid, false));
	}
	/**
	 * Send invitation to friends
	 * @return void|boolean
	 */
	public function send_invite()
	{
		if (EventbookingHelper::getConfigValue('show_invite_friend'))
		{
			
			$config = EventbookingHelper::getConfig();
			$user = JFactory::getUser();
			if ($config->enable_captcha && ($user->id == 0 || $config->bypass_captcha_for_registered_user !== '1'))
			{
				$input = JFactory::getApplication()->input;
				//Check captcha
				$captchaPlugin = JFactory::getApplication()->getParams()->get('captcha', JFactory::getConfig()->get('captcha'));
				$res = JCaptcha::getInstance($captchaPlugin)->checkAnswer($input->post->get('recaptcha_response_field', '', 'string'));
				if (!$res)
				{
					JError::raiseWarning('', JText::_('EB_INVALID_CAPTCHA_ENTERED'));
					JRequest::setVar('view', 'invite');
					JRequest::setVar('layout', 'default');
					$this->display();
					return;
				}
			}
			$model = $this->getModel('invite');
			$post = JRequest::get('post');
			$model->sendInvite($post);
			$this->setRedirect(
				JRoute::_('index.php?option=com_eventbooking&view=invite&layout=complete&tmpl=component&Itemid=' . JRequest::getInt('Itemid', 0), 
					false));
		}
		else
		{
			JError::raiseError(403, JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));
			return false;
		}
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
	 * Export registrants data into a csv file
	 */
	public function csv_export()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$config = EventbookingHelper::getConfig();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$eventId = JRequest::getInt('event_id');
		if (!EventbookingHelper::canExportRegistrants($eventId))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('EB_NOT_ALLOWED_TO_EXPORT'));
		}
		if (!$eventId)
		{
			JFactory::getApplication()->redirect('index.php', JText::_('EB_PLEASE_CHOOSE_AN_EVENT_TO_EXPORT_REGISTRANTS'));
		}
		$where = array();
		$where[] = '(a.published = 1 OR (a.payment_method LIKE "os_offline%" AND a.published NOT IN (2,3)))';
		if ($eventId)
		{
			$where[] = ' a.event_id=' . $eventId;
		}
		if (isset($config->include_group_billing_in_csv_export) && !$config->include_group_billing_in_csv_export)
		{
			$where[] = ' a.is_group_billing = 0 ';
		}
		if (!$config->include_group_members_in_csv_export)
		{
			$where[] = ' a.group_id = 0 ';
		}
		if ($config->show_coupon_code_in_registrant_list)
		{
			$sql = 'SELECT a.*, b.event_date, b.title' . $fieldSuffix . ' AS event_title, c.code AS coupon_code FROM #__eb_registrants AS a INNER JOIN #__eb_events AS b ON a.event_id = b.id LEFT JOIN #__eb_coupons AS c ON a.coupon_id=c.id WHERE ' .
				 implode(' AND ', $where) . ' ORDER BY a.id ';
		}
		else
		{
			$sql = 'SELECT a.*, b.event_date, b.title' . $fieldSuffix . ' AS event_title FROM #__eb_registrants AS a INNER JOIN #__eb_events AS b ON a.event_id = b.id WHERE ' .
				implode(' AND ', $where) . ' ORDER BY a.id ';
		}
		$db->setQuery($sql);
		$rows = $db->loadObjectList();
		if (count($rows) == 0)
		{
			JFactory::getApplication()->redirect('index.php', JText::_('EB_NO_REGISTRANTS_TO_EXPORT'));
		}
		if ($eventId)
		{
			if ($config->custom_field_by_category)
			{
				$query->clear();
				$query->select('category_id')
				->from('#__eb_event_categories')
				->where('event_id=' . $eventId)
				->where('main_category=1');
				$db->setQuery($query);
				$categoryId = (int) $db->loadResult();
				$sql = 'SELECT id, name, title, is_core FROM #__eb_fields WHERE published=1 AND (category_id=0 OR category_id=' . $categoryId .
				') ORDER BY ordering';
			}
			else 
			{
				$sql = 'SELECT id, name, title, is_core FROM #__eb_fields WHERE published=1 AND (event_id = -1 OR id IN (SELECT field_id FROM #__eb_field_events WHERE event_id='.$eventId.')) ORDER BY ordering';
			}			
		}
		else
		{
			$sql = 'SELECT id, name, title, is_core FROM #__eb_fields WHERE published=1  ORDER BY ordering';
		}
		$db->setQuery($sql);
		$rowFields = $db->loadObjectList();
		//Get the custom fields value and store them into an array
		$sql = 'SELECT id FROM #__eb_registrants AS a WHERE ' . implode(' AND ', $where);
		$db->setQuery($sql);
		$registrantIds = array(0);
		$registrantIds = array_merge($registrantIds, $db->loadColumn());
		$sql = 'SELECT registrant_id, field_id, field_value FROM #__eb_field_values WHERE registrant_id IN (' . implode(',', $registrantIds) . ')';
		$db->setQuery($sql);
		$rowFieldValues = $db->loadObjectList();
		$fieldValues = array();
		for ($i = 0, $n = count($rowFieldValues); $i < $n; $i++)
		{
			$rowFieldValue = $rowFieldValues[$i];
			$fieldValues[$rowFieldValue->registrant_id][$rowFieldValue->field_id] = $rowFieldValue->field_value;
		}
		//Get name of groups
		$groupNames = array();
		$sql = 'SELECT id, first_name, last_name FROM #__eb_registrants AS a WHERE is_group_billing = 1' .
			 (COUNT($where) ? ' AND ' . implode(' AND ', $where) : '');
		$db->setQuery($sql);
		$rowGroups = $db->loadObjectList();
		if (count($rowGroups))
		{
			foreach ($rowGroups as $rowGroup)
			{
				$groupNames[$rowGroup->id] = $rowGroup->first_name . ' ' . $rowGroup->last_name;
			}
		}
		EventbookingHelperData::csvExport($rows, $config, $rowFields, $fieldValues, $groupNames);
	}
	###########################Submitting events from front-end################################
	public function save_event()
	{
		$post = JRequest::get('post', JREQUEST_ALLOWHTML);
		$model = $this->getModel('event');
		$cid = $post['cid'];
		$post['id'] = (int) $cid[0];
		$ret = $model->store($post);
		if ($ret)
		{
			$msg = JText::_('Successfully saving event');
		}
		else
		{
			$msg = JText::_('Error while saving event');
		}

		$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('events', JRequest::getInt('Itemid')), false), $msg);
	}

	/**
	 * Publish the selected events
	 *
	 */
	public function publish_event()
	{
		//Check unpublish permission
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$id = JRequest::getInt('id', 0);
		if (!$id)
		{
			$msg = JText::_('EB_INVALID_EVENT');
			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('events', JRequest::getInt('Itemid', 0)), false), $msg);
			return;
		}
		//Get the event object
		$sql = 'SELECT * FROM #__eb_events WHERE id=' . $id;
		$db->setQuery($sql);
		$rowEvent = $db->loadObject();
		if (!$rowEvent)
		{
			$msg = JText::_('EB_INVALID_EVENT');
			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('events', JRequest::getInt('Itemid', 0)), false), $msg);
			return;
		}
		if (!EventbookingHelper::canChangeEventStatus($id))
		{
			$msg = JText::_('EB_NO_PUBLISH_PERMISSION');
			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('events', JRequest::getInt('Itemid', 0)), false), $msg);
			return;
		}
		//OK, enough permission checked. Publish the event		
		$model = $this->getModel('event');
		$ret = $model->publish($id, 1);
		if ($ret)
		{
			$msg = JText::_('EB_PUBLISH_SUCCESS');
		}
		else
		{
			$msg = JText::_('EB_PUBLISH_ERROR');
		}
		$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('events', JRequest::getInt('Itemid', 0)), false), $msg);
	}

	/**
	 * Unpublish the selected events
	 *
	 */
	public function unpublish_event()
	{
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$id = JRequest::getInt('id', 0);
		if (!$id)
		{
			$msg = JText::_('EB_INVALID_EVENT');
			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('events', JRequest::getInt('Itemid', 0)), false), $msg);
			return;
		}
		//Get the event object
		$sql = 'SELECT * FROM #__eb_events WHERE id=' . $id;
		$db->setQuery($sql);
		$rowEvent = $db->loadObject();
		if (!$rowEvent)
		{
			$msg = JText::_('EB_INVALID_EVENT');
			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('events', JRequest::getInt('Itemid')), false), $msg);
			return;
		}
		
		if (!EventbookingHelper::canChangeEventStatus($id))
		{
			$msg = JText::_('EB_NO_UNPUBLISH_PERMISSION');
			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('events', JRequest::getInt('Itemid')), false), $msg);
			return;
		}
		$model = $this->getModel('event');
		$ret = $model->publish($id, 0);
		if ($ret)
		{
			$msg = JText::_('EB_UNPUBLISH_SUCCESS');
		}
		else
		{
			$msg = JText::_('EB_UNPUBLISH_ERROR');
		}
		$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('events', JRequest::getInt('Itemid', 0)), false), $msg);
	}

	/**
	 * Redirect user to events mangement page
	 *
	 */
	public function cancel_event()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_eventbooking&view=events&Itemid=' . JRequest::getInt('Itemid', 0), false));
	}

	/**
	 * save location
	 *
	 */
	public function save_location()
	{
		$post = JRequest::get('post', JREQUEST_ALLOWHTML);
		$model = $this->getModel('addlocation');
		$cid = $post['cid'];
		$post['id'] = (int) $cid[0];
		$ret = $model->store($post);
		if ($ret)
		{
			$msg = JText::_('EB_LOCATION_SAVED');
		}
		else
		{
			$msg = JText::_('EB_SAVING_LOCATION_ERROR');
		}
		
		$this->setRedirect(JRoute::_('index.php?option=com_eventbooking&view=locationlist&Itemid=' . JRequest::getInt('Itemid', 0)), $msg);
	}

	public function delete_location()
	{
		$model = $this->getModel('addlocation');
		$cid = JRequest::getVar('cid', array());
		JArrayHelper::toInteger($cid);
		$model->delete($cid);
		$msg = JText::_('EB_LOCATION_REMOVED');
		$this->setRedirect(JRoute::_('index.php?option=com_eventbooking&view=locationlist&Itemid=' . JRequest::getInt('Itemid', 0)), $msg);
	}

	/**
	 * Redirect user to locations
	 *
	 */
	public function cancel_location()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_eventbooking&view=locationlist&Itemid=' . JRequest::getInt('Itemid', 0)));
	}

	public function download_invoice()
	{
		$user = JFactory::getUser();
		if (!$user->id)
		{
			JFactory::getApplication()->redirect('index.php', JText::_('You do not have permission to download the invoice'));
		}
		$id = JRequest::getInt('id');
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_eventbooking/tables');
		$row = JTable::getInstance('eventbooking', 'Registrant');
		$row->load($id);
		if (!$row->id || ($row->user_id) != $user->id)
		{
			JFactory::getApplication()->redirect('index.php', JText::_('You do not have permission to download the invoice'));
		}
		EventbookingHelper::downloadInvoice($id);
	}

	/**
	 * Process download a file
	 */
	public function download_file()
	{
		$Itemid = JRequest::getInt('Itemid');
		$filePath = JPATH_ROOT.'/media/com_eventbooking/files';
		$fileName = JRequest::getVar('file_name', '');
		if (file_exists($filePath . '/' . $fileName))
		{
			while (@ob_end_clean());
			EventbookingHelper::processDownload($filePath . '/' . $fileName, $fileName, true);
			JFactory::getApplication()->close();
		}
		else
		{
			JFactory::getApplication()->redirect('index.php?option=com_eventbooking&Itemid=' . $Itemid, JText::_('File does not exist'));
		}
	}

	public function download_ical()
	{
		$eventId = $this->input->getInt('event_id');
		if ($eventId)
		{
			$config = EventbookingHelper::getConfig();
			$fieldSuffix = EventbookingHelper::getFieldSuffix();
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('*')
				->select('title' . $fieldSuffix . ' AS title, short_description' . $fieldSuffix . ' AS short_description, description' . $fieldSuffix . ' AS description')
				->from('#__eb_events')
				->where('id = '. $eventId);
			$db->setQuery($query);
			$event = $db->loadObject();

			$query->clear();
			$query->select('a.*')
				->from('#__eb_locations AS a')
				->innerJoin('#__eb_events AS b ON a.id=b.location_id')
				->where('b.id=' . $eventId);

			$db->setQuery($query);
			$rowLocation = $db->loadObject();

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

			$ics->download();
		}
	}

	/**
	 * Get list of states for the selected country, using in AJAX request 
	 */
	public function get_states()
	{
		$app = JFactory::getApplication();
		$countryName = $app->input->get('country_name', '', 'string');
		$fieldName = $app->input->get('field_name', 'state', 'string');
		$stateName = $app->input->get('state_name', '', 'string');
		if (!$countryName)
		{
			$countryName = EventbookingHelper::getConfigValue('default_country');
		}
		$db = JFactory::getDbo();
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
			->where('country_id=' . (int) $countryId);
		;
		$db->setQuery($query);
		$states = $db->loadObjectList();
		$options = array();
		if (count($states))
		{
			$options[] = JHtml::_('select.option', '', JText::_('EB_SELECT_STATE'));
			$options = array_merge($options, $states);
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
				if(count($keyValue) == 2 && $keyValue[1])
				{
					$_POST[$keyValue[0]] = $keyValue[1];
				}
			}

			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_eventbooking/table');
			$method = os_payments::getPaymentMethod('os_paypal');
			$method->verifyPayment();
		}
	}
}