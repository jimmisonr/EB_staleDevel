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

class EventBookingViewRegister extends JViewLegacy
{

	/**
	 * Display interface to user
	 *
	 * @param string $tpl
	 */
	public function display($tpl = null)
	{
		$layout = $this->getLayout();
		EventbookingHelper::addLangLinkForAjax();
		$config = EventbookingHelper::getConfig();
		$this->bootstrapHelper = new EventbookingHelperBootstrap($config->twitter_bootstrap_version);
		if ($layout == 'cart')
		{
			$this->_displayCart($tpl);
			return;
		}
		$input = JFactory::getApplication()->input;
		$eventId = $input->getInt('event_id', 0);
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$currentDate = JHtml::_('date', 'Now', 'Y-m-d H:i:s');
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$query->select('a.*, a.title' . $fieldSuffix . ' AS title')
			->select("DATEDIFF(event_date, '$currentDate') AS number_event_dates")
			->select("TIMESTAMPDIFF(MINUTE, registration_start_date, '$currentDate') AS registration_start_minutes")
			->select("TIMESTAMPDIFF(MINUTE, cut_off_date, '$currentDate') AS cut_off_minutes")
			->select("DATEDIFF(early_bird_discount_date, '$currentDate') AS date_diff")
			->select('IFNULL(SUM(b.number_registrants), 0) AS total_registrants')
			->from('#__eb_events AS a')
			->leftJoin('#__eb_registrants AS b ON (a.id = b.event_id AND b.group_id=0 AND (b.published = 1 OR (b.payment_method LIKE "os_offline%" AND b.published NOT IN (2,3))))')
			->where('a.id = '. $eventId)
			->where('a.published = 1')
			->group('a.id');
		$db->setQuery($query);
		$event = $db->loadObject();
		if (!EventbookingHelper::acceptRegistration($event))
		{
			$waitingList = EventbookingHelper::getConfigValue('activate_waitinglist_feature');
			if (!$waitingList || !$event->number_event_dates)
			{
				JFactory::getApplication()->redirect(JRoute::_('index.php?option=com_eventbooking&Itemid=' . $input->getInt('Itemid', 0), false),
					JText::_('EB_ERROR_REGISTRATION'));
			}
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
		$pageTitle = JText::_('EB_EVENT_REGISTRATION');
		$pageTitle = str_replace('[EVENT_TITLE]', $event->title, $pageTitle);
		JFactory::getDocument()->setTitle($pageTitle);
		$layout = $this->getLayout();
		switch ($layout)
		{
			case 'group':
				$this->_displayGroupForm($event, $input, $tpl);
				break;
			default:
				$this->_displayIndividualRegistrationForm($event, $input, $tpl);
				break;
		}
	}

	/**
	 * Display individual registration Form
	 *
	 * @param string $tpl
	 */
	private function _displayIndividualRegistrationForm($event, $input, $tpl)
	{
		JFactory::getDocument()->addScript(JUri::base(true) . '/components/com_eventbooking/assets/js/paymentmethods.js');
		$config    = EventbookingHelper::getConfig();
		$user      = JFactory::getUser();
		$userId    = $user->get('id');
		$eventId   = $event->id;
		$rowFields = EventbookingHelper::getFormFields($eventId, 0);
		if (($event->event_capacity > 0) && ($event->event_capacity <= $event->total_registrants))
		{
			$waitingList = true;
		}
		else
		{
			$waitingList = false;
		}
		$captchaInvalid = $input->getInt('captcha_invalid', 0);
		if ($captchaInvalid)
		{
			$data = JRequest::get('post', JREQUEST_ALLOWHTML);
		}
		else
		{
			$data = EventbookingHelper::getFormData($rowFields, $eventId, $userId, $config);
		}
		if ($userId && !isset($data['first_name']))
		{
			//Load the name from Joomla default name
			$name = $user->name;
			if ($name)
			{
				$pos = strpos($name, ' ');
				if ($pos !== false)
				{
					$data['first_name'] = substr($name, 0, $pos);
					$data['last_name'] = substr($name, $pos + 1);
				}
				else
				{
					$data['first_name'] = $name;
					$data['last_name'] = '';
				}
			}
		}
		if ($userId && !isset($data['email']))
		{
			$data['email'] = $user->email;
		}
		if (!isset($data['country']) || !$data['country'])
		{
			$data['country'] = $config->default_country;
		}
		//Get data				
		$form = new RADForm($rowFields);
		if ($captchaInvalid)
		{
			$useDefault = false;
		}
		else
		{
			$useDefault = true;
		}
		$form->bind($data, $useDefault);
		$form->prepareFormFields('calculateIndividualRegistrationFee();');
		$paymentMethod = $input->post->getString('payment_method', os_payments::getDefautPaymentMethod(trim($event->payment_methods)));
		if ($waitingList)
		{
			$fees = EventbookingHelper::calculateIndividualRegistrationFees($event, $form, $data, $config, null);
		}
		else
		{
			$fees = EventbookingHelper::calculateIndividualRegistrationFees($event, $form, $data, $config, $paymentMethod);
		}
		$expMonth = $input->post->getInt('exp_month', date('m'));
		$expYear = $input->post->getInt('exp_year', date('Y'));
		$lists['exp_month'] = JHtml::_('select.integerlist', 1, 12, 1, 'exp_month', ' class="input-small" ', $expMonth, '%02d');
		$currentYear = date('Y');
		$lists['exp_year'] = JHtml::_('select.integerlist', $currentYear, $currentYear + 10, 1, 'exp_year', 'class="input-small"', $expYear);
		$data['coupon_code'] =  $input->post->getString('coupon_code', '');

		$methods = os_payments::getPaymentMethods(trim($event->payment_methods));
		$options = array();
		$options[] = JHtml::_('select.option', 'Visa', 'Visa');
		$options[] = JHtml::_('select.option', 'MasterCard', 'MasterCard');
		$options[] = JHtml::_('select.option', 'Discover', 'Discover');
		$options[] = JHtml::_('select.option', 'Amex', 'American Express');
		$lists['card_type'] = JHtml::_('select.genericlist', $options, 'card_type', ' class="inputbox" ', 'value', 'text');
		if (($event->enable_coupon == 0 && $config->enable_coupon) || $event->enable_coupon == 1 || $event->enable_coupon == 3)
		{
			$enableCoupon = 1;
		}
		else
		{
			$enableCoupon = 0;
		}

		$idealEnabled = EventbookingHelper::idealEnabled();
		if ($idealEnabled)
		{
			$bankLists = EventbookingHelper::getBankLists();
			$options = array();
			foreach ($bankLists as $bankId => $bankName)
			{
				$options[] = JHtml::_('select.option', $bankId, $bankName);
			}
			$lists['bank_id'] = JHtml::_('select.genericlist', $options, 'bank_id', ' class="inputbox" ', 'value', 'text',
				$input->post->getInt('bank_id'));
		}

		// Check to see if there is payment processing fee or not
		$showPaymentFee = false;
		foreach($methods as $method)
		{
			if ($method->paymentFee)
			{
				$showPaymentFee = true;
				break;
			}
		}

		// Add support for deposit payment
		$paymentType = $input->post->getInt('payment_type', 0);
		if ($config->activate_deposit_feature && $event->deposit_amount > 0)
		{
			$options = array();
			$options[] = JHtml::_('select.option', 0, JText::_('EB_FULL_PAYMENT'));
			$options[] = JHtml::_('select.option', 1, JText::_('EB_DEPOSIT_PAYMENT'));
			$lists['payment_type'] = JHtml::_('select.genericlist', $options, 'payment_type', ' class="input-large" onchange="showDepositAmount(this);" ', 'value', 'text',
				$paymentType);
			$depositPayment = 1;
		}
		else
		{
			$depositPayment = 0;
		}

		// Captcha
		$showCaptcha = 0;
		if ($config->enable_captcha && ($user->id == 0 || $config->bypass_captcha_for_registered_user !== '1'))
		{
			$captchaPlugin = JFactory::getApplication()->getParams()->get('captcha', JFactory::getConfig()->get('captcha'));
			if(!$captchaPlugin)
			{
				// Hardcode to recaptcha, reduce support request
				$captchaPlugin = 'recaptcha';
			}
			// Check to make sure Captcha is enabled
			$plugin = JPluginHelper::getPlugin('captcha', $captchaPlugin);
			if ($plugin)
			{
				$showCaptcha = 1;					
				$this->captcha = JCaptcha::getInstance($captchaPlugin)->display('dynamic_recaptcha_1', 'dynamic_recaptcha_1', 'required');
			}
			else
			{
				JFactory::getApplication()->enqueueMessage(JText::_('EB_CAPTCHA_NOT_ACTIVATED_IN_YOUR_SITE'), 'error');
			}
		}

		// Reset some values if waiting list is activated
		if ($waitingList)
		{
			$enableCoupon = false;
			$idealEnabled = false;
			$depositPayment = false;
			$paymentType = false;
			$showPaymentFee = false;
		}
		else
		{
			$form->setEventId($eventId);
		}
		// Assign these parameters
		$this->paymentMethod = $paymentMethod;
		$this->Itemid = $input->getInt('Itemid', 0);
		$this->config = $config;
		$this->event = $event;
		$this->methods = $methods;
		$this->enableCoupon = $enableCoupon;
		$this->userId = $userId;
		$this->lists = $lists;
		$this->idealEnabled = $idealEnabled;
		$this->depositPayment = $depositPayment;
		$this->paymentType = $paymentType;
		$this->message = EventbookingHelper::getMessages();
		$this->fieldSuffix = EventbookingHelper::getFieldSuffix();
		$this->showCaptcha = $showCaptcha;
		$this->form = $form;
		$this->totalAmount = $fees['total_amount'];
		$this->taxAmount = $fees['tax_amount'];
		$this->discountAmount = $fees['discount_amount'];
		$this->depositAmount = $fees['deposit_amount'];
		$this->amount = $fees['amount'];
		$this->paymentProcessingFee = $fees['payment_processing_fee'];
		$this->showPaymentFee = $showPaymentFee;
		$this->discountRate = $fees['discount_rate'];
		$this->waitingList = $waitingList;

		parent::display($tpl);
	}

	/**
	 * Display Group Form
	 *
	 * @param string $tpl
	 */
	private function _displayGroupForm($event, $input, $tpl)
	{
        $user = JFactory::getUser();
		$document = JFactory::getDocument();
		$document->addScript(JUri::base(true) . '/components/com_eventbooking/assets/js/paymentmethods.js');
		$document->addScriptDeclaration('var siteUrl="' . EventbookingHelper::getSiteUrl() . '";');
		$this->Itemid = $input->getInt('Itemid', 0);
		$this->event = $event;
		$this->message = EventbookingHelper::getMessages();
		$this->fieldSuffix = EventbookingHelper::getFieldSuffix();
		$this->config = EventbookingHelper::getConfig();
		$this->captchaInvalid = $input->get('captcha_invalid', 0);
		$this->showBillingStep = EventbookingHelper::showBillingStep($event->id);	
		$config = EventbookingHelper::getConfig();
		if ($config->enable_captcha && ($user->id == 0 || $config->bypass_captcha_for_registered_user !== '1'))
		{
			$captchaPlugin = JFactory::getApplication()->getParams()->get('captcha', JFactory::getConfig()->get('captcha'));
			if(!$captchaPlugin)
			{
				// Hardcode to recaptcha, reduce support request
				$captchaPlugin = 'recaptcha';
			}
			$plugin = JPluginHelper::getPlugin('captcha', $captchaPlugin);
			if ($plugin)
			{				
				JCaptcha::getInstance($captchaPlugin)->initialise('dynamic_recaptcha_1');
			}
			else
			{
				JFactory::getApplication()->enqueueMessage(JText::_('EB_CAPTCHA_NOT_ACTIVATED_IN_YOUR_SITE'), 'error');
			}
		}

		if (($event->event_capacity > 0) && ($event->event_capacity <= $event->total_registrants))
		{
			$waitingList = true;
		}
		else
		{
			$waitingList = false;
		}
		$this->waitingList = $waitingList;

		EventbookingHelperJquery::colorbox('eb-colorbox-term');
		parent::display($tpl);
	}

	/**
	 * 
	 * Display registration page for cart
	 * @param string $tpl
	 */
	private function _displayCart($tpl)
	{
		JFactory::getDocument()->addScript(JUri::base(true) . '/components/com_eventbooking/assets/js/paymentmethods.js');
		$app = JFactory::getApplication();
		$input = $app->input;
		$db = JFactory::getDbo();
		$config = EventbookingHelper::getConfig();
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$cart = new EventbookingHelperCart();
		$items = $cart->getItems();
		if (!count($items))
		{
			$url = JRoute::_('index.php?option=com_eventbooking&Itemid=' . $input->getInt('Itemid', 0));
			$app->redirect($url, JText::_('EB_NO_EVENTS_FOR_CHECKOUT'));
		}
		$eventId = (int) $items[0];
		$query = $db->getQuery(true);
		$rowFields = EventbookingHelper::getFormFields(0, 4);
		$captchaInvalid = $input->getInt('captcha_invalid', 0);
		if ($captchaInvalid)
		{
			$data = JRequest::get('post', JREQUEST_ALLOWHTML);
		}
		else
		{
			$data = EventbookingHelper::getFormData($rowFields, $eventId, $userId, $config);
		}
		if ($userId && !isset($data['first_name']))
		{
			//Load the name from Joomla default name
			$name = $user->name;
			if ($name)
			{
				$pos = strpos($name, ' ');
				if ($pos !== false)
				{
					$data['first_name'] = substr($name, 0, $pos);
					$data['last_name'] = substr($name, $pos + 1);
				}
				else
				{
					$data['first_name'] = $name;
					$data['last_name'] = '';
				}
			}
		}
		if ($userId && !isset($data['email']))
		{
			$data['email'] = $user->email;
		}
		if (!isset($data['country']) || !$data['country'])
		{
			$data['country'] = $config->default_country;
		}
		//Get data
		$form = new RADForm($rowFields);
		if ($captchaInvalid)
		{
			$useDefault = false;
		}
		else
		{
			$useDefault = true;
		}
		$form->bind($data, $useDefault);
		$form->prepareFormFields('calculateCartRegistrationFee();');
		$paymentMethod = $input->post->getString('payment_method', os_payments::getDefautPaymentMethod());
		$expMonth = $input->post->getInt('exp_month', date('m'));
		$expYear = $input->post->getInt('exp_year', date('Y'));
		$lists['exp_month'] = JHtml::_('select.integerlist', 1, 12, 1, 'exp_month', ' class="input-small" ', $expMonth, '%02d');
		$currentYear = date('Y');
		$lists['exp_year'] = JHtml::_('select.integerlist', $currentYear, $currentYear + 10, 1, 'exp_year', 'class="input-small"', $expYear);
		$data['coupon_code'] =  $input->post->getString('coupon_code', '');
		$fees = EventbookingHelper::calculateCartRegistrationFee($cart, $form, $data, $config, $paymentMethod);
		$events = $cart->getEvents();
		$methods = os_payments::getPaymentMethods();
		$options = array();
		$options[] = JHtml::_('select.option', 'Visa', 'Visa');
		$options[] = JHtml::_('select.option', 'MasterCard', 'MasterCard');
		$options[] = JHtml::_('select.option', 'Discover', 'Discover');
		$options[] = JHtml::_('select.option', 'Amex', 'American Express');
		$lists['card_type'] = JHtml::_('select.genericlist', $options, 'card_type', ' class="inputbox" ', 'value', 'text');
		//Coupon will be enabled if there is atleast one event has coupon
		$query->clear();
		$query->select('enable_coupon')
			->from('#__eb_events')
			->where('id IN (' . implode(',', $items) . ')');
		$db->setQuery($query);
		$enableCoupons = $db->loadColumn();
		$enableCoupon = 0;
		for ($i = 0, $n = count($enableCoupons); $i < $n; $i++)
		{
			if ($enableCoupons[$i] > 0 || ($enableCoupons[$i] == 0 && $config->enable_coupon))
			{
				$enableCoupon = 1;
				break;
			}
		}
		$idealEnabled = EventbookingHelper::idealEnabled();
		if ($idealEnabled)
		{
			$bankLists = EventbookingHelper::getBankLists();
			$options = array();
			foreach ($bankLists as $bankId => $bankName)
			{
				$options[] = JHtml::_('select.option', $bankId, $bankName);
			}
			$lists['bank_id'] = JHtml::_('select.genericlist', $options, 'bank_id', ' class="inputbox" ', 'value', 'text', 
				$input->post->getInt('bank_id'));
		}
		##Add support for deposit payment
		$paymentType = $input->post->getInt('payment_type', 0);
		if ($config->activate_deposit_feature)
		{
			$options = array();
			$options[] = JHtml::_('select.option', 0, JText::_('EB_FULL_PAYMENT'));
			$options[] = JHtml::_('select.option', 1, JText::_('EB_DEPOSIT_PAYMENT'));
			$lists['payment_type'] = JHtml::_('select.genericlist', $options, 'payment_type', ' class="input-large" onchange="showDepositAmount(this);" ', 'value', 'text',
				$paymentType);
			$depositPayment = 1;
		}
		else
		{
			$depositPayment = 0;
		}
		$message = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$showCaptcha = 0;
		if ($config->enable_captcha && ($user->id == 0 || $config->bypass_captcha_for_registered_user !== '1'))
		{			
			$captchaPlugin = JFactory::getApplication()->getParams()->get('captcha', JFactory::getConfig()->get('captcha'));
			if(!$captchaPlugin)
			{
				// Hardcode to recaptcha, reduce support request
				$captchaPlugin = 'recaptcha';
			}
			$plugin = JPluginHelper::getPlugin('captcha', $captchaPlugin);
			if ($plugin)
			{
				$showCaptcha = 1;								
				$this->captcha = JCaptcha::getInstance($captchaPlugin)->display('dynamic_recaptcha_1', 'dynamic_recaptcha_1', 'required');
			}
			else
			{
				JFactory::getApplication()->enqueueMessage(JText::_('EB_CAPTCHA_NOT_ACTIVATED_IN_YOUR_SITE'), 'error');
			}
		}		
		$query->clear();
		$query->select('title'.$fieldSuffix.' AS title')
			->from('#__eb_events')
			->where('id IN (' . implode(',', $items) . ')')
			->order('FIND_IN_SET(id, "' . implode(',', $items) . '")');
		$db->setQuery($query);
		$eventTitle = implode(', ', $db->loadColumn());

		// Check to see if there is payment processing fee or not
		$showPaymentFee = false;
		foreach($methods as $method)
		{
			if ($method->paymentFee)
			{
				$showPaymentFee = true;
				break;
			}
		}

		// Assign these parameters
		$this->paymentMethod = $paymentMethod;
		$this->lists = $lists;
		$this->config = $config;
		$this->methods = $methods;
		$this->enableCoupon = $enableCoupon;
		$this->userId = $userId;
		$this->lists = $lists;
		$this->idealEnabled = $idealEnabled;
		$this->depositPayment = $depositPayment;
		$this->message = $message;
		$this->fieldSuffix = $fieldSuffix;
		$this->showCaptcha = $showCaptcha;
		$this->form = $form;
		$this->totalAmount = $fees['total_amount'];
		$this->taxAmount = $fees['tax_amount'];
		$this->discountAmount = $fees['discount_amount'];
		$this->depositAmount = $fees['deposit_amount'];
		$this->paymentProcessingFee = $fees['payment_processing_fee'];
		$this->amount = $fees['amount'];
		$this->items = $events;
		$this->eventTitle = $eventTitle;
		$this->form = $form;
		$this->Itemid = $input->getInt('Itemid', 0);
		$this->showPaymentFee = $showPaymentFee;
		$this->paymentType = $paymentType;

		parent::display($tpl);
	}
}