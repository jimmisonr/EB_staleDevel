<?php
/**
 * @version            2.3.2
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

use Omnipay\AuthorizeNet\Message\AbstractRequest;

class os_authnet extends RADPaymentOmnipay
{
	protected $omnipayPackage = 'AuthorizeNet_AIM';

	/**
	 * Constructor
	 *
	 * @param JRegistry $params
	 * @param array     $config
	 */
	public function __construct($params, $config = array('type' => 1))
	{
		$config['params_map'] = array(
			'apiLoginId'     => 'x_login',
			'transactionKey' => 'x_tran_key',
			'developerMode'  => 'authnet_mode'
		);

		parent::__construct($params, $config);
	}

	/**
	 * Pass additional gateway data to payment gateway
	 *
	 * @param AbstractRequest $request
	 * @param JTable          $row
	 * @param array           $data
	 */
	protected function beforeRequestSend($request, $row, $data)
	{
		$event = EventbookingHelperDatabase::getEvent($row->event_id);
		if ($event->api_login && $event->transaction_key)
		{
			$request->setApiLoginId($event->api_login);
			$request->setTransactionKey($event->transaction_key);
		}

		parent::beforeRequestSend($request, $row, $data);
	}
}