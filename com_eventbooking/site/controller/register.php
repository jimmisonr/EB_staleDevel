<?php
/**
 * @version        	2.0.0
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
class EventbookingControllerRegister extends EventbookingController
{

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
}