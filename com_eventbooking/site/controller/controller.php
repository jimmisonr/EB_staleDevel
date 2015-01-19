<?php
/**
 * @version        	1.6.9
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
		$styleUrl = JUri::root(true) . '/components/com_eventbooking/assets/css/style.css';
		$document->addStylesheet($styleUrl);
		JHtml::_('script', EventbookingHelper::getURL() . 'components/com_eventbooking/assets/js/noconflict.js', false, false);
		if ($config->calendar_theme)
		{
			$theme = $config->calendar_theme;
		}
		else
		{
			$theme = 'default';
		}
		$styleUrl = JUri::root(true) . '/components/com_eventbooking/assets/css/themes/' . $theme . '.css';
		$document->addStylesheet($styleUrl);
		$styleUrl = JUri::root(true) . '/components/com_eventbooking/assets/css/custom.css';
		$document->addStylesheet($styleUrl);
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
			#Misc							
			case 'waitinglist_complete':
				JRequest::setVar('view', 'waitinglist');
				JRequest::setVar('layout', 'complete');
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
		$input = JFactory::getApplication()->input;
		$eventId = $input->getInt('event_id', 0);
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
		$user = JFactory::getUser();
		$config = EventbookingHelper::getConfig();
		if ($config->enable_captcha && ($user->id == 0 || $config->bypass_captcha_for_registered_user !== '1'))
		{
			$captchaPlugin = JFactory::getApplication()->getParams()->get('captcha', JFactory::getConfig()->get('captcha'));
			$res = JCaptcha::getInstance($captchaPlugin)->checkAnswer($input->post->get('recaptcha_response_field', '', 'string'));
			if (!$res)
			{
				JError::raiseWarning('', JText::_('EB_INVALID_CAPTCHA_ENTERED'));
				$fromArticle = $input->post->getInt('from_article', 0);
				if ($fromArticle)
				{
					$sesion = JFactory::getSession();
					$formData = JRequest::get('post');
					$sesion->set('eb_form_data', serialize($formData));
					$sesion->set('eb_catpcha_invalid', 1);
					JFactory::getApplication()->redirect($sesion->get('eb_artcile_url'));
					return;
					return;
				}
				else
				{
					$input->set('captcha_invalid', 1);
					$this->execute('individual_registration');
					return;
				}
			}
			else
			{
				$sesion = JFactory::getSession();
				$sesion->clear('eb_catpcha_invalid');
			}
		}
		$post = JRequest::get('post', JREQUEST_ALLOWHTML);
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
		$input = JFactory::getApplication()->input;
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
		if ($config->enable_captcha && ($user->id == 0 || $config->bypass_captcha_for_registered_user !== '1'))
		{
			$captchaPlugin = JFactory::getApplication()->getParams()->get('captcha', JFactory::getConfig()->get('captcha'));
			$res = JCaptcha::getInstance($captchaPlugin)->checkAnswer($input->post->get('recaptcha_response_field', '', 'string'));
			if (!$res)
			{
				$session = JFactory::getSession();
				JError::raiseWarning('', JText::_('EB_INVALID_CAPTCHA_ENTERED'));
				$data = JRequest::get('post', JREQUEST_ALLOWHTML);
				$session->set('eb_group_billing_data', serialize($data));
				$input->set('captcha_invalid', 1);
				JRequest::setVar('view', 'register');
				JRequest::setVar('layout', 'group');
				$this->display();
				return;
			}
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
		$input = JFactory::getApplication()->input;
		$config = EventbookingHelper::getConfig();
		$user = JFactory::getUser();
		if (($config->enable_captcha != 0) && ($user->id == 0 || $config->bypass_captcha_for_registered_user !== '1'))
		{
			$captchaPlugin = JFactory::getApplication()->getParams()->get('captcha', JFactory::getConfig()->get('captcha'));
			$res = JCaptcha::getInstance($captchaPlugin)->checkAnswer($input->post->get('recaptcha_response_field', '', 'string'));
			if (!$res)
			{
				$input->set('captcha_invalid', 1);
				JError::raiseWarning('', JText::_('EB_INVALID_CAPTCHA_ENTERED'));
				JRequest::setVar('view', 'register');
				JRequest::setVar('layout', 'cart');
				$this->display();
				return;
			}
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
				->where('(published=1 OR (payment_method LIKE "os_offline%" AND published != 2))');
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
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$config = EventbookingHelper::getConfig();
		$cart = new EventbookingHelperCart();
		$couponCode = $input->getString('coupon_code', '');
		$data = JRequest::get('post', JREQUEST_ALLOWHTML);
		$response = array();
		if ($couponCode)
		{
			$cart = new EventbookingHelperCart();
			$eventIds = $cart->getItems();
			if (count($eventIds) == 0)
			{
				$eventIds = array(0);
			}
			//Validate the coupon
			$query->select('*')
				->from('#__eb_coupons')
				->where('published=1')
				->where('code="' . $couponCode . '"')
				->where('(valid_from="0000-00-00" OR valid_from <= NOW())')
				->where('(valid_to="0000-00-00" OR valid_to >= NOW())')
				->where('(times = 0 OR times > used)')
				->where(' (event_id=0 OR event_id IN(' . implode(',', $eventIds) . '))');
			$db->setQuery($query);
			$coupon = $db->loadObject();
			if ($coupon)
			{
				$_SESSION['coupon_id'] = $coupon->id;
				$response['coupon_valid'] = 1;
			}
			else
			{
				$response['coupon_valid'] = 0;
				if (isset($_SESSION['coupon_id']))
				{
					unset($_SESSION['coupon_id']);
				}
			}
		}
		else
		{
			$response['coupon_valid'] = 1;
		}
		$rowFields = EventbookingHelper::getFormFields(0, 4);
		$form = new RADForm($rowFields);
		$form->bind($data);
		$totalAmount = $cart->calculateTotal() + $form->calculateFee();
		$discountAmount = $cart->calculateTotalDiscount();
		if ($discountAmount > $totalAmount)
		{
			$discountAmount = $totalAmount;
		}
		if ($config->enable_tax && ($totalAmount - $discountAmount > 0))
		{
			$taxAmount = round(($totalAmount - $discountAmount) * $config->tax_rate / 100, 2);
		}
		else
		{
			$taxAmount = 0;
		}
		$amount = $totalAmount - $discountAmount + $taxAmount;

		// Payment processing fee
		$paymentMethod = JRequest::getVar('payment_method', '');
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
			$paymentProcessingFee         = round($paymentFeeAmount + $amount * $paymentFeePercent / 100, 2);
			$amount += $paymentProcessingFee;
		}
		else
		{
			$paymentProcessingFee         = 0;
		}

		$response['total_amount'] = EventbookingHelper::formatAmount($totalAmount, $config);
		$response['discount_amount'] = EventbookingHelper::formatAmount($discountAmount, $config);
		$response['tax_amount'] = EventbookingHelper::formatAmount($taxAmount, $config);
		$response['payment_processing_fee'] = EventbookingHelper::formatAmount($paymentProcessingFee, $config);
		$response['amount'] = EventbookingHelper::formatAmount($amount, $config);
		echo json_encode($response);
		JFactory::getApplication()->close();
	}
	/**
	 * Validate the coupon code which users entered on the registration form
	 */
	public function validate_coupon()
	{
		$db = JFactory::getDbo();
		$config = EventbookingHelper::getConfig();
		$where = array();
		$eventId = JRequest::getInt('event_id', 0);
		$couponCode = JRequest::getVar('coupon_code', '');
		$where[] = 'published = 1';
		$where[] = ' code="' . $couponCode . '" ';
		$where[] = ' (valid_from="0000-00-00" OR valid_from <= NOW()) ';
		$where[] = ' (valid_to="0000-00-00" OR valid_to >= NOW()) ';
		$where[] = ' (times = 0 OR times > used)';
		if ($config->multiple_booking)
		{
			$cart = new EventbookingHelperCart();
			$eventIds = $cart->getItems();
			if (count($eventIds) == 0)
			{
				$eventIds = array();
				$eventIds[] = 0;
			}
			$where[] = ' (event_id=0 OR event_id IN(' . implode(',', $eventIds) . '))';
		}
		else
		{
			$where[] = ' (event_id=0 OR event_id=' . $eventId . ')';
		}
		$sql = 'SELECT * FROM #__eb_coupons WHERE ' . implode(' AND ', $where);
		$db->setQuery($sql);
		$rowCoupon = $db->loadObject();
		if ($rowCoupon)
		{
			$_SESSION['coupon_id'] = $rowCoupon->id;
			echo 1;
		}
		else
		{
			$_SESSION['coupon_id'] = 0;
			echo 0;
		}
		JFactory::getApplication()->close();
	}

	public function get_depend_fields_status()
	{
		$db = JFactory::getDbo();
		$fieldId = JRequest::getInt('field_id');
		$fieldValues = JRequest::getVar('field_values', '', 'post');
		$fieldSuffix = JRequest::getVar('field_suffix', '', 'post');
		$fieldValues = explode(',', $fieldValues);
		//Get list of depend fields
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_fields')
			->where('depend_on_field_id=' . $fieldId);
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$showFields = array();
		$hideFields = array();
		foreach ($rows as $row)
		{
			$dependOnOptions = explode(",", $row->depend_on_options);
			if (count(array_intersect($fieldValues, $dependOnOptions)))
			{
				$showFields[] = 'field_' . $row->name . ($fieldSuffix ? '_' . $fieldSuffix : '');
			}
			else
			{
				$hideFields[] = 'field_' . $row->name . ($fieldSuffix ? '_' . $fieldSuffix : '');
			}
		}
		echo json_encode(array('show_fields' => implode(',', $showFields), 'hide_fields' => implode(',', $hideFields)));
		JFactory::getApplication()->close();
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
		$app = JFactory::getApplication();
		$Itemid = JRequest::getInt('Itemid', 0);
		$db = JFactory::getDbo();
		$user = JFactory::getUser();
		$id = JRequest::getInt('id');
		$sql = 'SELECT a.id, a.title, b.user_id FROM #__eb_events AS a INNER JOIN #__eb_registrants AS b ON a.id = b.event_id WHERE b.id=' . $id;
		$db->setQuery($sql);
		$rowEvent = $db->loadObject();		
		if (!$rowEvent)
		{
			$app->redirect(JRoute::_('index.php?option=com_eventbooking&Itemid=' . $Itemid), JText::_('EB_INVALID_ACTION'));
		}
		if ($user->get('id') == 0 || ($user->get('id') != $rowEvent->user_id))
		{
			$app->redirect(JRoute::_('index.php?option=com_eventbooking&Itemid=' . $Itemid), JText::_('EB_INVALID_ACTION'));
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
		$where[] = '(a.published = 1 OR (a.payment_method LIKE "os_offline%" AND a.published != 2))';
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
			$sql = 'SELECT a.*, b.event_date, b.title AS event_title, c.code AS coupon_code FROM #__eb_registrants AS a INNER JOIN #__eb_events AS b ON a.event_id = b.id LEFT JOIN #__eb_coupons AS c ON a.coupon_id=c.id WHERE ' .
				 implode(' AND ', $where) . ' ORDER BY a.id ';
		}
		else
		{
			$sql = 'SELECT a.*, b.event_date, b.title AS event_title FROM #__eb_registrants AS a INNER JOIN #__eb_events AS b ON a.event_id = b.id WHERE ' .
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
	/**
	 * Store users into waitinglist database
	 */
	public function save_waitinglist()
	{
		$config = EventbookingHelper::getConfig();
		$user = JFactory::getUser();
		if ($config->enable_captcha && ($user->id == 0 || $config->bypass_captcha_for_registered_user !== '1'))
		{
			$input  = JFactory::getApplication()->input;
			$captchaPlugin = JFactory::getApplication()->getParams()->get('captcha', JFactory::getConfig()->get('captcha'));
			$res = JCaptcha::getInstance($captchaPlugin)->checkAnswer($input->post->get('recaptcha_response_field', '', 'string'));
			if (!$res)
			{
				JError::raiseWarning('', JText::_('EB_INVALID_CAPTCHA_ENTERED'));
				JRequest::setVar('view', 'waitinglist');
				JRequest::setVar('layout', 'default');
				$this->display();
				return;
			}
		}
		$data = JRequest::get('post');
		$model = $this->getModel('waitinglist');
		$model->store($data);
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
		$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('events', JRequest::getInt('Itemid', 0)), false), $msg);
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