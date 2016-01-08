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

class os_authnet extends RADPayment
{

	private $results = array();

	private $approved = false;

	private $response;

	/**
	 * Constructor function
	 *
	 * @param \Joomla\Registry\Registry $params
	 * @param array                     $config
	 */
	public function __construct($params, $config = array('type' => 1))
	{
		parent::__construct($params, $config);

		$this->mode = $params->get('authnet_mode', 0);

		if ($this->mode)
		{
			$this->url = "https://secure.authorize.net/gateway/transact.dll";
		}
		else
		{
			$this->url = "https://test.authorize.net/gateway/transact.dll";
		}

		$this->parameters['x_delim_data']     = "TRUE";
		$this->parameters['x_delim_char']     = "|";
		$this->parameters['x_relay_response'] = "FALSE";
		$this->parameters['x_url']            = "FALSE";
		$this->parameters['x_version']        = "3.1";
		$this->parameters['x_method']         = "CC";
		$this->parameters['x_type']           = "AUTH_CAPTURE";
		$this->parameters['x_login']          = $params->get('x_login');
		$this->parameters['x_tran_key']       = $params->get('x_tran_key');
		$this->parameters['x_invoice_num']    = $this->_invoiceNumber();
	}

	/**
	 * Process payment with the posted data
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	public function processPayment($row, $data)
	{
		$app    = JFactory::getApplication();
		$Itemid = $app->input->getInt('Itemid', 0);

		$event = EventbookingHelperDatabase::getEvent($row->event_id);
		if ($event->api_login && $event->transaction_key)
		{
			$this->parameters['x_login']    = $event->api_login;
			$this->parameters['x_tran_key'] = $event->transaction_key;
		}

		$data['x_exp_date'] = str_pad($data['exp_month'], 2, '0', STR_PAD_LEFT) . '/' . substr($data['exp_year'], 2, 2);
		$data['amount']     = round($data['amount'], 2);
		$retries            = 2;
		$testing            = $this->mode ? "FALSE" : "TRUE";
		$cc_num             = $this->_ccNumber($data["x_card_num"]);
		// Set more parameters for the payment gateway to user
		$authnetValues = array(
			// Payment information
			"x_test_request"       => $testing,
			"x_card_num"           => $data['x_card_num'],
			"x_exp_date"           => $data['x_exp_date'],
			"x_card_code"          => $data['x_card_code'],
			"x_description"        => $data['item_name'],
			"x_amount"             => $data['amount'],
			// ###########3 CUSTOMER DETAILS ################3
			"x_first_name"         => $data['first_name'],
			"x_last_name"          => $data['last_name'],
			"x_address"            => $data['address'],
			"x_city"               => $data['city'],
			"x_state"              => $data['state'],
			"x_phone"              => $data['phone'],
			"x_zip"                => $data['zip'],
			"x_company"            => $data['organization'],
			"x_email"              => $data['email'],
			"x_country"            => $data['country'],
			// ###########3 SHIPPING DETAILS ################3
			"x_ship_to_first_name" => $data['first_name'],
			"x_ship_to_last_name"  => $data['last_name'],
			"x_ship_to_address"    => $data['address'],
			"x_ship_to_city"       => $data['city'],
			"x_ship_to_state"      => $data['state'],
			"x_ship_to_country"    => $data['country'],
			"x_ship_to_zip"        => $data['zip'],
			"x_ship_to_phone"      => $data['phone'],
			"x_ship_to_email"      => $data['email'],
			// ###########3 MERCHANT REQUIRED DETAILS ################3
			"cc_number"            => $cc_num,
			"cc_expdate"           => $data['x_exp_date'],
			"cc_emailid"           => $data['email']);
		foreach ($authnetValues as $key => $value)
		{
			$this->setParameter($key, $value);
		}
		$fields = $this->prepareParameters();
		$ch     = curl_init($this->url);
		$count  = 0;
		while ($count < $retries)
		{
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim($fields, "& "));

			// Uncomment this line if you get no response from payment gateway
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			// If you are using goodaddy hosting, please uncomment the two below lines
			// curl_setopt ($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			// curl_setopt ($ch, CURLOPT_PROXY,"http://proxy.shr.secureserver.net:3128");
			$this->response = curl_exec($ch);
			$this->_parseResults();
			if ($this->getResultResponseFull() == "Approved")
			{
				$this->approved = true;
				break;
			}
			else if ($this->getResultResponseFull() == "Declined")
			{
				$this->approved = false;
				break;
			}
			$count++;
		}
		curl_close($ch);
		if ($this->approved)
		{
			$this->onPaymentSuccess($row, $this->getTransactionID());
			$app->redirect(JRoute::_('index.php?option=com_eventbooking&view=complete&Itemid=' . $Itemid, false, false));

			return true;
		}
		else
		{
			$_SESSION['reason'] = $this->getResponseText();
			$app->redirect(JRoute::_('index.php?option=com_eventbooking&view=failure&id=' . $row->id . '&Itemid=' . $Itemid, false, false));

			return false;
		}
	}

	private function _parseResults()
	{
		$this->results = explode("|", $this->response);
	}


	private function prepareParameters()
	{
		$fields = '';
		foreach ($this->parameters as $key => $value)
		{
			$fields .= "$key=" . urlencode($value) . "&";
		}

		return $fields;
	}


	private function getResultResponseFull()
	{
		$response = array("", "Approved", "Declined", "Error");

		return $response[$this->results[0]];
	}

	private function getResponseText()
	{
		return $this->results[3];
	}

	private function getTransactionID()
	{
		return $this->results[6];
	}

	/*
	 * Helper function to generate invoice number @param string $prefix @param int $length @return string
	 */
	private function _invoiceNumber($prefix = "DC-", $length = 6)
	{
		$chars         = "0123456789";
		$invoiceNumber = "";
		srand((double) microtime() * 1000000);
		for ($i = 0; $i < $length; $i++)
		{
			$invoiceNumber .= $chars[rand() % strlen($chars)];
		}
		$invoiceNumber = $prefix . $invoiceNumber;

		return $invoiceNumber;
	}

	/**
	 * Generate credit card number
	 *
	 * @param string $card_num
	 *
	 * @return string
	 */
	private function _ccNumber($card_num)
	{
		$num    = strlen($card_num);
		$cc_num = "";
		for ($i = 0; $i <= $num - 5; $i++)
		{
			$cc_num .= "x";
		}
		$cc_num .= "-";
		$cc_num .= substr($card_num, $num - 4, 4);

		return $cc_num;
	}
}