<?php
/**
 * @version        	1.7.2
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
class EventBookingViewRegister extends JViewLegacy
{

	/**
	 * Display interface to user
	 *
	 * @param string $tpl
	 */
	function display($tpl = null)
	{
		$input = JFactory::getApplication()->input;
		$eventId = $input->getInt('event_id', 0);
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*, IFNULL(SUM(b.number_registrants), 0) AS total_registrants')
			->from('#__eb_events AS a')
			->leftJoin('#__eb_registrants AS b ON (a.id = b.event_id AND b.group_id=0 AND (b.published = 1 OR (b.payment_method LIKE "os_offline%" AND b.published NOT IN (2,3))))')
			->where('a.id=' . $eventId);
		$db->setQuery($query);
		$event = $db->loadObject();
		$layout = $this->getLayout();
		switch ($layout)
		{
			case 'number_members':
				$this->_displayNumberMembersForm($event, $input, $tpl);
				break;
			case 'group_members':
				$this->_displayGroupMembersForm($event, $input, $tpl);
				break;
			case 'group_billing':
				$this->_displayGroupBillingForm($event, $input, $tpl);
				break;
		}
	}

	/**
	 * Display form allow registrant to enter number of members for his group
	 * @param string $tpl
	 */
	function _displayNumberMembersForm($event, $input, $tpl)
	{
		$session = JFactory::getSession();
		$numberRegistrants = $session->get('eb_number_registrants', '');
		if (($event->event_capacity > 0) && ($event->event_capacity <= $event->total_registrants))
		{
			$waitingList = true;
		}
		else
		{
			$waitingList = false;
		}
		if ($waitingList)
		{
			if ($event->max_group_number)
			{
				$this->maxRegistrants = (int) $event->max_group_number;
			}
			else
			{
				// Hardcode max number of group members
				$this->maxRegistrants = 10;
			}
		}
		else
		{
			$this->maxRegistrants = EventbookingHelper::getMaxNumberRegistrants($event);
		}

		if ($event->min_group_number > 0)
		{
			$this->minNumberRegistrants = $event->min_group_number;
		}
		else
		{
			$this->minNumberRegistrants = 2;
		}

		$this->numberRegistrants = $numberRegistrants;
		$this->message = EventbookingHelper::getMessages();
		$this->fieldSuffix = EventbookingHelper::getFieldSuffix();
		$this->Itemid = $input->getInt('Itemid', 0);
		$this->event = $event;
		$this->config = EventbookingHelper::getConfig();
		parent::display($tpl);
	}

	/**
	 * Display form allow registrant to enter information of group members
	 * @param string $tpl
	 */
	function _displayGroupMembersForm($event, $input, $tpl)
	{
		$session = JFactory::getSession();
		$numberRegistrants = (int) $session->get('eb_number_registrants', '');
		$eventId = $input->getInt('event_id', 0);
		//Get Group members form
		$membersData = $session->get('eb_group_members_data', null);
		if ($membersData)
		{
			$membersData = unserialize($membersData);
		}
		else
		{
			$membersData = array();
		}
		$rowFields = EventbookingHelper::getFormFields($eventId, 2);
		$this->numberRegistrants = $numberRegistrants;
		$this->rowFields = $rowFields;
		$this->membersData = $membersData;
		$this->eventId = $eventId;
		$this->showBillingStep = EventbookingHelper::showBillingStep($eventId);
		$this->Itemid = $input->getInt('Itemid', 0);
		$showCaptcha = 0;
		if (!$this->showBillingStep)
		{
			$user = JFactory::getUser();
			$config = EventbookingHelper::getConfig();
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
					$this->captchaPlugin = $captchaPlugin;
				}				
			}			
		}
		
		$this->Itemid = $input->getInt('Itemid', 0);
		$this->event = $event;
		$this->config = EventbookingHelper::getConfig();
		$this->showCaptcha = $showCaptcha;
		$this->defaultCountry = EventbookingHelper::getConfigValue('default_country');
		parent::display($tpl);
	}

	/**
	 * Display billing form allow registrant enter billing information for group registration
	 *
	 * @param $event
	 * @param $input
	 * @param $tpl
	 */
	function _displayGroupBillingForm($event, $input, $tpl)
	{
		$session = JFactory::getSession();
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$config = EventbookingHelper::getConfig();
		$eventId = $input->getInt('event_id', 0);
		$rowFields = EventbookingHelper::getFormFields($eventId, 1);
		$groupBillingData = $session->get('eb_group_billing_data', null);
		if ($groupBillingData)
		{
			$data = unserialize($groupBillingData);
			$captchaInvalid = 1;
		}
		else
		{
			$captchaInvalid = 0;
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

		// Waiting List

		if (($event->event_capacity > 0) && ($event->event_capacity <= $event->total_registrants))
		{
			$waitingList = true;
		}
		else
		{
			$waitingList = false;
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
		$form->prepareFormFields('calculateGroupRegistrationFee();');
		$paymentMethod = $input->post->getString('payment_method', os_payments::getDefautPaymentMethod(trim($event->payment_methods)));
		if ($waitingList)
		{
			$fees = EventbookingHelper::calculateGroupRegistrationFees($event, $form, $data, $config, null);
		}
		else
		{
			$fees = EventbookingHelper::calculateGroupRegistrationFees($event, $form, $data, $config, $paymentMethod);
		}
		$expMonth = $input->post->getInt('exp_month', date('m'));
		$expYear = $input->post->getInt('exp_year', date('Y'));
		$lists['exp_month'] = JHtml::_('select.integerlist', 1, 12, 1, 'exp_month', ' class="input-small" ', $expMonth, '%02d');
		$currentYear = date('Y');
		$lists['exp_year'] = JHtml::_('select.integerlist', $currentYear, $currentYear + 10, 1, 'exp_year', 'class="input-small"', $expYear);
		$methods = os_payments::getPaymentMethods(trim($event->payment_methods));
		$options = array();
		$options[] = JHtml::_('select.option', 'Visa', 'Visa');
		$options[] = JHtml::_('select.option', 'MasterCard', 'MasterCard');
		$options[] = JHtml::_('select.option', 'Discover', 'Discover');
		$options[] = JHtml::_('select.option', 'Amex', 'American Express');
		$lists['card_type'] = JHtml::_('select.genericlist', $options, 'card_type', ' class="inputbox" ', 'value', 'text');
		if (($event->enable_coupon == 0 && $config->enable_coupon) || $event->enable_coupon == 2 || $event->enable_coupon == 3)
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

		// Add support for deposit payment
		$paymentType = $input->post->getInt('payment_type', 0);
		if ($config->activate_deposit_feature && $event->deposit_amount > 0)
		{
			$options = array();
			$options[] = JHtml::_('select.option', 0, JText::_('EB_FULL_PAYMENT'));
			$options[] = JHtml::_('select.option', 1, JText::_('EB_DEPOSIT_PAYMENT'));
			$lists['payment_type'] = JHtml::_('select.genericlist', $options, 'payment_type', ' class="input-large" onchange="showDepositAmount(this);" ', 'value', 'text',
				$input->post->getInt('payment_type', 0));
			$depositPayment = 1;
		}
		else
		{
			$depositPayment = 0;
		}

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
				$this->captchaPlugin = $captchaPlugin;
			}
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

		// Reset some values if waiting list is activated
		if ($waitingList)
		{
			$enableCoupon = false;
			$idealEnabled = false;
			$depositPayment = false;
			$paymentType = false;
			$showPaymentFee = false;
		}

		// Assign these parameters
		$this->paymentMethod = $paymentMethod;
		$this->lists = $lists;
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
		$this->showCaptcha = $showCaptcha;
		$this->captchaInvalid = $captchaInvalid;
		$this->form = $form;
		$this->totalAmount = $fees['total_amount'];
		$this->taxAmount = $fees['tax_amount'];
		$this->discountAmount = $fees['discount_amount'];
		$this->amount = $fees['amount'];
		$this->depositAmount = $fees['deposit_amount'];
		$this->paymentProcessingFee = $fees['payment_processing_fee'];
		$this->showPaymentFee = $showPaymentFee;
		$this->waitingList = $waitingList;

		parent::display($tpl);
	}
}

?>