<?php
/**
 * @version            2.8.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

class EventbookingViewPaymentHtml extends RADViewHtml
{
	/**
	 * Display interface to user
	 *
	 */
	public function display()
	{
		$layout = $this->getLayout();
		if ($layout == 'complete')
		{
			$this->displayPaymentComplete();

			return;
		}

		// Load common js code
		$document = JFactory::getDocument();
		$document->addScriptDeclaration(
			'var siteUrl = "' . EventbookingHelper::getSiteUrl() . '";'
		);
		$document->addScript(JUri::root(true) . '/media/com_eventbooking/assets/js/paymentmethods.js');
		EventbookingHelper::addLangLinkForAjax();

		$config                = EventbookingHelper::getConfig();
		$this->bootstrapHelper = new EventbookingHelperBootstrap($config->twitter_bootstrap_version);

		$input        = $this->input;
		$registrantId = $input->getInt('registrant_id');

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_registrants')
			->where('id = ' . $registrantId);
		$db->setQuery($query);
		$rowRegistrant = $db->loadObject();
		if (empty($rowRegistrant))
		{
			echo JText::_('EB_INVALID_REGISTRATION_RECORD');

			return;
		}

		if ($rowRegistrant->payment_status == 1)
		{
			echo JText::_('EB_DEPOSIT_PAYMENT_COMPLETED');

			return;
		}

		$event = EventbookingHelperDatabase::getEvent($rowRegistrant->event_id);

		$config    = EventbookingHelper::getConfig();
		$user      = JFactory::getUser();
		$userId    = $user->get('id');
		$rowFields = EventbookingHelper::getDepositPaymentFormFields();

		$captchaInvalid = $input->getInt('captcha_invalid', 0);
		if ($captchaInvalid)
		{
			$data = $input->post->getData();
		}
		else
		{
			$data = EventbookingHelper::getRegistrantData($rowRegistrant, $rowFields);

			// IN case there is no data, get it from URL (get for example)
			if (empty($data))
			{
				$data = $input->getData();
			}
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
					$data['last_name']  = substr($name, $pos + 1);
				}
				else
				{
					$data['first_name'] = $name;
					$data['last_name']  = '';
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

		$paymentMethod = $input->post->getString('payment_method', os_payments::getDefautPaymentMethod(trim($event->payment_methods)));

		$expMonth           = $input->post->getInt('exp_month', date('m'));
		$expYear            = $input->post->getInt('exp_year', date('Y'));
		$lists['exp_month'] = JHtml::_('select.integerlist', 1, 12, 1, 'exp_month', ' class="input-small" ', $expMonth, '%02d');
		$currentYear        = date('Y');
		$lists['exp_year']  = JHtml::_('select.integerlist', $currentYear, $currentYear + 10, 1, 'exp_year', 'class="input-small"', $expYear);

		$methods = os_payments::getPaymentMethods(trim($event->payment_methods), false);

		if (count($methods) == 0)
		{
			echo JText::_('EB_ENABLE_PAYMENT_METHODS');

			return;
		}

		$this->loadCaptcha();

		// Assign these parameters
		$this->paymentMethod = $paymentMethod;
		$this->config        = $config;
		$this->event         = $event;
		$this->methods       = $methods;
		$this->lists         = $lists;
		$this->message       = EventbookingHelper::getMessages();
		$this->fieldSuffix   = EventbookingHelper::getFieldSuffix();
		$this->message       = EventbookingHelper::getMessages();
		$this->form          = $form;
		$this->rowRegistrant = $rowRegistrant;

		parent::display();
	}

	/**
	 * Display payment complete page
	 */
	private function displayPaymentComplete()
	{
		$config      = EventbookingHelper::getConfig();
		$message     = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();

		if (strlen(trim(strip_tags($message->{'deposit_payment_thanks_message' . $fieldSuffix}))))
		{
			$thankMessage = $message->{'deposit_payment_thanks_message' . $fieldSuffix};
		}
		else
		{
			$thankMessage = $message->deposit_payment_thanks_message;
		}

		$id = (int) JFactory::getSession()->get('payment_id', 0);

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_registrants')
			->where('id = ' . $id);
		$db->setQuery($query);
		$row = $db->loadObject();

		if (empty($row->id))
		{
			echo JText::_('Invalid Registration Record');

			return;
		}

		$replaces = EventbookingHelper::buildDepositPaymentTags($row, $config);

		foreach ($replaces as $key => $value)
		{
			$key          = strtoupper($key);
			$thankMessage = str_ireplace("[$key]", $value, $thankMessage);
		}

		$this->message = $thankMessage;

		parent::display();
	}

	/**
	 * Load captcha for registration form
	 *
	 * @param bool $initOnly
	 *
	 * @throws Exception
	 */
	private function loadCaptcha($initOnly = false)
	{
		$config      = EventbookingHelper::getConfig();
		$user        = JFactory::getUser();
		$showCaptcha = 0;

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
				$showCaptcha = 1;
				if ($initOnly)
				{
					JCaptcha::getInstance($captchaPlugin)->initialise('dynamic_recaptcha_1');
				}
				else
				{
					$this->captcha = JCaptcha::getInstance($captchaPlugin)->display('dynamic_recaptcha_1', 'dynamic_recaptcha_1', 'required');
				}
			}
			else
			{
				JFactory::getApplication()->enqueueMessage(JText::_('EB_CAPTCHA_NOT_ACTIVATED_IN_YOUR_SITE'), 'error');
			}
		}

		$this->showCaptcha = $showCaptcha;
	}
}