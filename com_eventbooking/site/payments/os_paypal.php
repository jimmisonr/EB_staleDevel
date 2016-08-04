<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

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

		if ($row->process_deposit_payment)
		{
			$this->setParameter('return', $siteUrl . 'index.php?option=com_eventbooking&view=payment&layout=complete&Itemid=' . $Itemid);
		}
		else
		{
			$this->setParameter('return', $siteUrl . 'index.php?option=com_eventbooking&view=complete&Itemid=' . $Itemid);
		}

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
			if ($row->published && $row->payment_status)
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
			'USD',
		);
	}

	/**
	 * Validate the post data from paypal to our server
	 *
	 * @return string
	 */
	protected function validate()
	{
		$errNum                 = "";
		$errStr                 = "";
		$urlParsed              = parse_url($this->url);
		$host                   = $urlParsed['host'];
		$path                   = $urlParsed['path'];
		$postString             = '';
		$response               = '';
		$this->notificationData = $_POST;
		foreach ($_POST as $key => $value)
		{
			$postString .= $key . '=' . urlencode(stripslashes($value)) . '&';
		}
		$postString .= 'cmd=_notify-validate';
		$fp = fsockopen($host, '80', $errNum, $errStr, 30);
		if (!$fp)
		{
			$response = 'Could not open SSL connection to ' . $this->url;
			$this->logGatewayData($response);

			return false;
		}
		fputs($fp, "POST $path HTTP/1.1\r\n");
		fputs($fp, "Host: $host\r\n");
		fputs($fp, "User-Agent: Events Booking\r\n");
		fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
		fputs($fp, "Content-length: " . strlen($postString) . "\r\n");
		fputs($fp, "Connection: close\r\n\r\n");
		fputs($fp, $postString . "\r\n\r\n");
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
