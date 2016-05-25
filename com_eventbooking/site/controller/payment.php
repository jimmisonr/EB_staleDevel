<?php
/**
 * @version            2.6.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

class EventbookingControllerPayment extends EventbookingController
{
	/**
	 * Process individual registration
	 */
	public function process()
	{
		$app          = JFactory::getApplication();
		$input        = $this->input;
		$registrantId = $input->getInt('registrant_id', 0);

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

		$errors = array();

		// Validate captcha
		if (!$this->validateCaptcha())
		{
			$errors[] = JText::_('EB_INVALID_CAPTCHA_ENTERED');
		}

		$data = $input->post->getData();

		if (count($errors))
		{
			foreach ($errors as $error)
			{
				$app->enqueueMessage($error, 'error');
			}

			$input->set('captcha_invalid', 1);
			$input->set('view', 'payment');
			$input->set('layout', 'default');
			$this->display();

			return;
		}

		$model = $this->getModel('payment');

		$model->processPayment($data);
	}

	/**
	 * Validate captcha on registration form
	 *
	 * @return bool|mixed
	 */
	private function validateCaptcha()
	{
		$result = true;

		$user   = JFactory::getUser();
		$config = EventbookingHelper::getConfig();

		if ($config->enable_captcha && ($user->id == 0 || $config->bypass_captcha_for_registered_user !== '1'))
		{
			$captchaPlugin = $this->app->getParams()->get('captcha', JFactory::getConfig()->get('captcha'));
			if (!$captchaPlugin)
			{
				// Hardcode to recaptcha, reduce support request
				$captchaPlugin = 'recaptcha';
			}
			$plugin = JPluginHelper::getPlugin('captcha', $captchaPlugin);
			if ($plugin)
			{
				$result = JCaptcha::getInstance($captchaPlugin)->checkAnswer($this->input->post->get('recaptcha_response_field', '', 'string'));
			}
		}

		return $result;
	}
}