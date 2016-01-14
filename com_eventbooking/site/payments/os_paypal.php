<?php
/**
 * @version            2.3.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

class os_paypal extends RADPayment
{
	/**
	 * Constructor functions, init some parameter
	 *
	 * @param object $params
	 */
	public function __construct($params, $config = array())
	{
		parent::__construct($params, $config);

		$this->mode = $params->get('paypal_mode');

		if ($this->mode)
		{
			$this->url = 'https://www.paypal.com/cgi-bin/webscr';
		}
		else
		{
			$this->url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
		}

		$this->setParameter('business', $params->get('paypal_id'));
		$this->setParameter('rm', 2);
		$this->setParameter('cmd', '_xclick');
		$this->setParameter('no_shipping', 1);
		$this->setParameter('no_note', 1);
		$this->setParameter('lc', 'US');
		$this->setParameter('charset', 'utf-8');
		$this->setParameter('tax', 0);
	}

	/**
	 * Process Payment
	 *
	 * @param object $row
	 * @param array  $data
	 */
	public function processPayment($row, $data)
	{
		$Itemid  = JFactory::getApplication()->input->getInt('Itemid', 0);
		$siteUrl = JUri::base();

		$event = EventbookingHelperDatabase::getEvent($row->event_id);
		if (strlen(trim($event->paypal_email)))
		{
			$this->setParameter('business', $event->paypal_email);
		}

		$this->setParameter('currency_code', $data['currency']);
		$this->setParameter('item_name', $data['item_name']);
		$this->setParameter('amount', round($data['amount'], 2));
		$this->setParameter('custom', $row->id);
		$this->setParameter('return', $siteUrl . 'index.php?option=com_eventbooking&view=complete&Itemid=' . $Itemid);
		$this->setParameter('cancel_return', $siteUrl . 'index.php?option=com_eventbooking&task=cancel&id=' . $row->id . '&Itemid=' . $Itemid);
		$this->setParameter('notify_url', $siteUrl . 'index.php?option=com_eventbooking&task=payment_confirm&payment_method=os_paypal');
		$this->setParameter('address1', $row->address);
		$this->setParameter('address2', $row->address2);
		$this->setParameter('city', $row->city);
		$this->setParameter('country', $data['country']);
		$this->setParameter('first_name', $row->first_name);
		$this->setParameter('last_name', $row->last_name);
		$this->setParameter('state', $row->state);
		$this->setParameter('zip', $row->zip);
		$this->setParameter('email', $row->email);


		$this->renderRedirectForm();
	}

	/**
	 * Verify payment
	 *
	 * @return bool
	 */
	public function verifyPayment()
	{
		$ret = $this->validate();
		if ($ret)
		{
			$id            = $this->notificationData['custom'];
			$transactionId = $this->notificationData['txn_id'];
			$amount        = $this->notificationData['mc_gross'];
			if ($amount < 0)
			{
				return false;
			}
			$row = JTable::getInstance('EventBooking', 'Registrant');
			$row->load($id);
			if (!$row->id)
			{
				return false;
			}
			if ($row->published)
			{
				return false;
			}

			$this->onPaymentSuccess($row, $transactionId);

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Get list of supported currencies
	 *
	 * @return array
	 */
	public function getSupportedCurrencies()
	{
		return array(
			'AUD',
			'BRL',
			'CAD',
			'CZK',
			'DKK',
			'EUR',
			'HKD',
			'HUF',
			'ILS',
			'JPY',
			'MYR',
			'MXN',
			'NOK',
			'NZD',
			'PHP',
			'PLN',
			'GBP',
			'RUB',
			'SGD',
			'SEK',
			'CHF',
			'TWD',
			'THB',
			'TRY',
			'USD'
		);
	}

	/**
	 * Validate the post data from paypal to our server
	 *
	 * @return string
	 */
	protected function validate()
	{
		$this->notificationData = $_POST;

		$hostname = $this->mode ? 'www.paypal.com' : 'www.sandbox.paypal.com';
		$url      = 'ssl://' . $hostname;
		$port     = 443;
		$req      = 'cmd=_notify-validate';

		foreach ($_POST as $key => $value)
		{
			$value = urlencode(stripslashes($value));
			$req .= "&$key=$value";
		}

		$header = '';
		$header .= "POST /cgi-bin/webscr HTTP/1.1\r\n";
		$header .= "Host: $hostname:$port\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen($req) . "\r\n";
		$header .= "User-Agent: Events Booking\r\n";
		$header .= "Connection: Close\r\n\r\n";

		$errNum   = '';
		$errStr   = '';
		$response = '';
		$fp       = fsockopen($url, $port, $errNum, $errStr, 30);

		if (!$fp)
		{
			$response = 'Could not open SSL connection to ' . $hostname . ':' . $port;
			$this->logGatewayData($response);

			return false;
		}

		fputs($fp, $header . $req);
		while (!feof($fp))
		{
			$response .= fgets($fp, 1024);
		}
		fclose($fp);


		$this->logGatewayData($response);

		if (!$this->mode || (stristr($response, "VERIFIED") && ($this->notificationData['payment_status'] == 'Completed')))
		{
			return true;
		}

		return false;
	}
}