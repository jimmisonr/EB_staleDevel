<?php
/**
 * @version            2.2.1
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

class os_worldpay extends RADPayment
{
	/**
	 * Constructor function
	 *
	 * @param \Joomla\Registry\Registry $params
	 * @param array                     $config
	 */
	public function __construct($params, $config = array())
	{
		parent::__construct($params, $config);

		$this->setParameter('instId', $params->get('wp_installation_id'));
		$this->setParameter('currency', 'GBP');
		if (!$params->get('worldpay_mode'))
		{
			$this->setParameter('testMode', '100');
			$this->url = 'https://secure-test.worldpay.com/wcc/purchase';
		}
		else
		{
			$this->url = 'https://secure.worldpay.com/wcc/purchase';
		}
	}

	/**
	 * Process payment
	 *
	 * @param JTable $row The registration record
	 * @param array  $data
	 */
	public function processPayment($row, $data)
	{
		$this->setParameter('desc', $data['item_name']);
		$this->setParameter('amount', round($data['amount'], 2));
		$this->setParameter('cartId', $row->id);
		$this->setParameter('address', $row->address . '&#10' . $row->address2 . '&#10' . $row->city . '&#10' . $row->state);
		$this->setParameter('name', $row->first_name . ' ' . $row->last_name);

		//Get country code here
		$countryCode = EventbookingHelper::getCountryCode($row->country ? $row->country : EventbookingHelper::getConfigValue('default_country'));
		if (!$countryCode)
		{
			$countryCode = 'GB';
		}
		$this->setParameter('country', $countryCode);
		$this->setParameter('postcode', $row->zip);
		$this->setParameter('tel', $row->phone);
		$this->setParameter('email', $row->email);
		$this->renderRedirectForm();
	}

	/**
	 * Validate the post data from paypal to our server
	 *
	 * @return string
	 */
	private function validate()
	{
		$this->notificationData = $_POST;

		$input = JFactory::getApplication()->input;

		$cartId      = $input->getInt('cartId', 0);
		$amount      = $input->getFloat('amount', 0);
		$transId     = $input->getString('transId', null);
		$transStatus = $input->getString('transStatus', '');

		$this->logGatewayData();

		if ($transStatus == 'Y')
		{
			$this->notificationData['cartId']  = $cartId;
			$this->notificationData['amount']  = $amount;
			$this->notificationData['transId'] = $transId;

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Process payment
	 *
	 */
	public function verifyPayment()
	{
		$ret = $this->validate();
		if ($ret)
		{
			$id            = $this->notificationData['cartId'];
			$transactionId = $this->notificationData['transId'];
			$amount        = $this->notificationData['amount'];
			if ($amount < 0)
			{
				return false;
			}
			$row = JTable::getInstance('EventBooking', 'Registrant');
			$row->load($id);

			$this->onPaymentSuccess($row, $transactionId);

			return true;
		}
		else
		{
			return false;
		}
	}
}