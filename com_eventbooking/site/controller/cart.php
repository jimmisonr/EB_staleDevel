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
class EventbookingControllerCart extends EventbookingController
{
	/**
	 *
	 * Add an events and store it to
	 */
	function add_cart()
	{
		$data  = JRequest::get();
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
		$Itemid     = JRequest::getInt('Itemid', 0);
		$redirect   = JRequest::getInt('redirect', 1);
		$eventIds   = JRequest::getVar('event_id');
		$quantities = JRequest::getVar('quantity');
		$model      = $this->getModel('cart');
		if (!$redirect)
		{
			$eventIds   = explode(',', $eventIds);
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
		$Itemid   = JRequest::getInt('Itemid', 0);
		$id       = JRequest::getInt('id', 0);
		$model    = &$this->getModel('cart');
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
		$app          = JFactory::getApplication();
		$input        = $app->input;
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
			$user   = JFactory::getUser();
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

		$cart  = new EventbookingHelperCart();
		$items = $cart->getItems();
		if (!count($items))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('Sorry, your session was expired. Please try again!'));
		}

		$post  = JRequest::get('post');
		$model = $this->getModel('cart');
		$model->processCheckout($post);
	}

	/**
	 * Calculate registration fee, then update information on cart registration form
	 */
	function calculate_cart_registration_fee()
	{
		$input               = JFactory::getApplication()->input;
		$config              = EventbookingHelper::getConfig();
		$paymentMethod       = $input->getString('payment_method', '');
		$data                = JRequest::get('post', JREQUEST_ALLOWHTML);
		$data['coupon_code'] = $input->getString('coupon_code', '');
		$cart                = new EventbookingHelperCart();
		$response            = array();
		$rowFields           = EventbookingHelper::getFormFields(0, 4);
		$form                = new RADForm($rowFields);
		$form->bind($data);

		$fees                               = EventbookingHelper::calculateCartRegistrationFee($cart, $form, $data, $config, $paymentMethod);
		$response['total_amount']           = EventbookingHelper::formatAmount($fees['total_amount'], $config);
		$response['discount_amount']        = EventbookingHelper::formatAmount($fees['discount_amount'], $config);
		$response['tax_amount']             = EventbookingHelper::formatAmount($fees['tax_amount'], $config);
		$response['payment_processing_fee'] = EventbookingHelper::formatAmount($fees['payment_processing_fee'], $config);
		$response['amount']                 = EventbookingHelper::formatAmount($fees['amount'], $config);
		$response['deposit_amount']         = EventbookingHelper::formatAmount($fees['deposit_amount'], $config);
		$response['coupon_valid']           = $fees['coupon_valid'];
		echo json_encode($response);
		JFactory::getApplication()->close();
	}
}