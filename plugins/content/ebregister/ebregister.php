<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
jimport('joomla.plugin.plugin');
class plgContentEBRegister extends JPlugin
{

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param object $params  The object that holds the plugin parameters
	 * @since 1.5
	 */
	function plgContentEBRegister(&$subject, $params)
	{
		parent::__construct($subject, $params);
	}

	/**	 	 
	 * Method is called by the view
	 *
	 * @param 	object		The article object.  Note $article->text is also available
	 * @param 	object		The article params
	 * @param 	int			The 'page' number
	 */
	function onContentPrepare($context, &$article, &$params, $limitstart)
	{
		error_reporting(0);
		$app = JFactory::getApplication();
		if ($app->getName() != 'site')
		{
			return true;
		}
		if (strpos($article->text, 'ebregister') === false)
		{
			return true;
		}
		$regex = "#{ebregister (\d+)}#s";
		$article->text = preg_replace_callback($regex, array(&$this, '_replaceEBRegister'), $article->text);
		return true;
	}

	/**
	 * Replace the text with the event detail
	 * 
	 * @param array $matches
	 */
	function _replaceEBRegister(&$matches)
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';
		$input = JFactory::getApplication()->input;
		$db = JFactory::getDbo();
		$config = EventbookingHelper::getConfig();		
		EventbookingHelper::loadLanguage();		
		$document = JFactory::getDocument();
		$document->addScript(JUri::base(true) . '/components/com_eventbooking/assets/js/paymentmethods.js');		
		$document->addStyleSheet(JURI::base(true) . '/components/com_eventbooking/assets/css/style.css');
		if ($config->calendar_theme)
		{
			$theme = $config->calendar_theme ;
		}
		else
		{
			$theme = 'default' ;
		}
		$styleUrl = JUri::base(true).'/components/com_eventbooking/assets/css/themes/'.$theme.'.css';
		$document->addStylesheet( $styleUrl);
		if ($config->load_jquery !== '0')
		{
			EventbookingHelper::loadJQuery();
		}
		if ($config->load_bootstrap_css_in_frontend!== '0')
		{
			EventbookingHelper::loadBootstrap() ;
		}
		
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$eventId = $matches[1];
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_events')
			->where('id=' . $eventId);
		$db->setQuery($query);
		$event = $db->loadObject();
		if (!$event)
		{
			return JText::_('Invalid Event');
		}
		$rowFields = EventbookingHelper::getFormFields($eventId, 0);
		$session = JFactory::getSession();
		$captchaInvalid = (int) $session->get('eb_catpcha_invalid');
		if ($captchaInvalid)
		{
			$formData = $session->get('eb_form_data');
			if ($formData)
			{
				$data = unserialize($formData);
				JRequest::set($data, 'post', false);
			}
			else 
			{
				$data = array();
			}
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
		$form->buildFieldsDependency();
		$paymentMethod = $input->post->getString('payment_method', os_payments::getDefautPaymentMethod(trim($event->payment_methods)));
		$expMonth = $input->post->getInt('exp_month', date('m'));
		$expYear = $input->post->getInt('exp_year', date('Y'));
		$lists['exp_month'] = JHtml::_('select.integerlist', 1, 12, 1, 'exp_month', ' class="input-small" ', $expMonth, '%02d');
		$currentYear = date('Y');
		$lists['exp_year'] = JHtml::_('select.integerlist', $currentYear, $currentYear + 10, 1, 'exp_year', 'class="input-small"', $expYear);
		$extraFee = $form->calculateFee();
		$totalAmount = $event->individual_price + $extraFee;
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
					$discountAmount = $event->discount;
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
					$discountAmount = $discountAmount + $coupon->discount;
				}
			}
		}
		$todayDate = JHtml::_('date', 'now', 'Y-m-d');
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
					$discountAmount = $discountAmount + $event->early_bird_discount_amount;
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
		$message = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		if ($config->enable_captcha && ($user->id == 0 || $config->bypass_captcha_for_registered_user !== '1') &&
			 JPluginHelper::isEnabled('captcha', 'recaptcha'))
		{
			$showCaptcha = 1;
			JPluginHelper::importPlugin('captcha');
			$dispatcher = JDispatcher::getInstance();
			$dispatcher->trigger('onInit', 'dynamic_recaptcha_1');
		}
		else
		{
			$showCaptcha = 0;
		}						
		JFactory::getSession()->set('eb_artcile_url', JUri::getInstance()->toString());		
		//Assign these parameters
		$data['selectedPaymentMethod'] = $paymentMethod;
		$data['lists'] = $lists;
		$data['Itemid'] = EventbookingHelper::getItemid();
		$data['config'] = $config;
		$data['event'] = $event;				
		$data['methods'] = $methods;
		$data['enableCoupon'] = $enableCoupon;
		$data['userId'] = $userId;
		$data['idealEnabled'] = $idealEnabled;
		$data['depositPayment'] = $depositPayment;				
		$data['message'] = $message;
		$data['fieldSuffix'] = $fieldSuffix;
		$data['showCaptcha'] = $showCaptcha;
		$data['form'] = $form;
		$data['totalAmount'] = $totalAmount;
		$data['taxAmount'] = $taxAmount;
		$data['discountAmount'] = $discountAmount;
		$data['amount'] = $amount;		
		return EventbookingHelperHtml::loadCommonLayout(JPATH_ROOT.'/plugins/content/ebregister/ebregister/default.php', $data);
	}
}