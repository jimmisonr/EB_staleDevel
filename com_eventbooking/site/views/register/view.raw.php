<?php
/**
 * @version        	1.6.6
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2014 Ossolution Team
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
		$query->select('*')
			->from('#__eb_events')
			->where('id=' . $eventId);
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
		$eventId = JFactory::getApplication()->input->getInt('event_id');
		$this->maxRegistrants = EventbookingHelper::getMaxNumberRegistrants($eventId, EventbookingHelper::getConfig());
		$this->numberRegistrants = $numberRegistrants;
		$this->message = EventbookingHelper::getMessages();
		$this->fieldSuffix = EventbookingHelper::getFieldSuffix();
		$this->eventId = $eventId;
		
		$this->showBillingStep = EventbookingHelper::showBillingStep($eventId);
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
				if ($captchaPlugin)
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
	 * @param string $tpl
	 */
	function _displayGroupBillingForm($event, $input, $tpl)
	{
		$session = JFactory::getSession();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$config = EventbookingHelper::getConfig();
		$eventId = $input->getInt('event_id', 0);
		$numberRegistrants = (int) $session->get('eb_number_registrants');
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
		$form->buildFieldsDependency();
		$paymentMethod = $input->post->getString('payment_method', os_payments::getDefautPaymentMethod(trim($event->payment_methods)));
		$expMonth = $input->post->getInt('exp_month', date('m'));
		$expYear = $input->post->getInt('exp_year', date('Y'));
		$lists['exp_month'] = JHtml::_('select.integerlist', 1, 12, 1, 'exp_month', ' class="input-small" ', $expMonth, '%02d');
		$currentYear = date('Y');
		$lists['exp_year'] = JHtml::_('select.integerlist', $currentYear, $currentYear + 10, 1, 'exp_year', 'class="input-small"', $expYear);
		$extraFee = $form->calculateFee();
		$memberFormFields = EventbookingHelper::getFormFields($eventId, 2);
		//Member data
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
			$extraFee += $memberForm->calculateFee();
		}
		$rate = EventbookingHelper::getRegistrationRate($eventId, $numberRegistrants);
		if ($event->fixed_group_price > 0)
		{
			$totalAmount = $event->fixed_group_price + $extraFee;
		}
		else
		{
			$totalAmount = $rate * $numberRegistrants + $extraFee;
		}
		$discountAmount = 0;
		if ($user->get('id') && EventbookingHelper::memberGetDiscount($user, $config))
		{
			if ($event->discount > 0)
			{
				if ($event->discount_type == 1)
				{
					$discountAmount = $totalAmount * $event->discount / 100;
				}
				else
				{
					$discountAmount = $numberRegistrants * $event->discount;
				}
			}
		}
		$couponCode = $input->post->getString('coupon_code', '');
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
				->where('(event_id=0 OR event_id=' . $eventId . ')');
			$db->setQuery($query);
			$coupon = $db->loadObject();
			if ($coupon)
			{
				if ($coupon->coupon_type == 0)
				{
					$discountAmount = $discountAmount + $totalAmount * $coupon->discount / 100;
				}
				else
				{
					$discountAmount = $discountAmount + $numberRegistrants * $coupon->discount;
				}
			}
		}
		$todayDate = JHtml::_('date', 'now', 'Y-m-d');
		//Early bird discount
		$query->clear();
		$query->select('COUNT(id)')
			->from('#__eb_events')
			->where('id=' . $eventId)
			->where('DATEDIFF(early_bird_discount_date, "' . $todayDate . '") >= 0');
		$db->setQuery($query);
		$total = $db->loadResult();
		if ($total)
		{
			$earlyBirdDiscountAmount = $event->early_bird_discount_amount;
			if ($earlyBirdDiscountAmount > 0)
			{
				if ($event->early_bird_discount_type == 1)
				{
					$discountAmount = $discountAmount + $totalAmount * $event->early_bird_discount_amount / 100;
				}
				else
				{
					$discountAmount = $discountAmount + $numberRegistrants * $event->early_bird_discount_amount;
				}
			}
		}
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
		##Add support for deposit payment
		if ($config->activate_deposit_feature && $event->deposit_amount > 0)
		{
			$options = array();
			$options[] = JHtml::_('select.option', 0, JText::_('EB_FULL_PAYMENT'));
			$options[] = JHtml::_('select.option', 1, JText::_('EB_DEPOSIT_PAYMENT'));
			$lists['payment_type'] = JHtml::_('select.genericlist', $options, 'payment_type', ' class="input-large" ', 'value', 'text', 
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
			if ($captchaPlugin)
			{
				$showCaptcha = 1;				
				$this->captcha = JCaptcha::getInstance($captchaPlugin)->display('dynamic_recaptcha_1', 'dynamic_recaptcha_1', 'required');
				$this->captchaPlugin = $captchaPlugin;
			}
		}	
		$this->Itemid = $input->getInt('Itemid', 0);
		$this->event = $event;			
		//Assign these parameters		
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
		$this->showCaptcha = $showCaptcha;
		$this->captchaInvalid = $captchaInvalid;
		$this->form = $form;
		$this->totalAmount = $totalAmount;
		$this->taxAmount = $taxAmount;
		$this->discountAmount = $discountAmount;
		$this->amount = $amount;
		
		parent::display($tpl);
	}
}

?>