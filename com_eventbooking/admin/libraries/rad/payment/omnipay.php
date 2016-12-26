<?php
/**
 * Part of the Ossolution Payment Package
 *
 * @copyright  Copyright (C) 2015 - 2016 Ossolution Team. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_LIBRARIES . '/omnipay/vendor/autoload.php';

use Ossolution\Payment\OmnipayPayment;

/**
 * Payment class which use Omnipay payment class for processing payment
 *
 * @since 1.0
 */
class RADPaymentOmnipay extends OmnipayPayment
{
	/**
	 * Flag to determine whether this payment method has payment processing fee
	 *
	 * @var bool
	 */
	public $paymentFee;

	/**
	 * Method to check whether we need to show card type on form for this payment method.
	 * Always return false as when use Omnipay, we don't need card type parameter. It can be detected automatically
	 * from given card number
	 *
	 * @return bool|int
	 */
	public function getCardType()
	{
		return 0;
	}

	/**
	 * Method to check whether we need to show card holder name in the form
	 *
	 * @return bool|int
	 */
	public function getCardHolderName()
	{
		return $this->type;
	}

	/**
	 * Method to check whether we need to show card cvv input on form
	 *
	 * @return bool|int
	 */
	public function getCardCvv()
	{
		return $this->type;
	}

	/**
	 * This method need to be implemented by the payment plugin class. It needs to set url which users will be
	 * redirected to after a successful payment. The url is stored in paymentSuccessUrl property
	 *
	 * @param int   $id
	 * @param array $data
	 *
	 * @return void
	 */
	protected function setPaymentSuccessUrl($id, $data = array())
	{

		$input  = JFactory::getApplication()->input;
		$task   = $input->getCmd('task');
		$Itemid = $input->getInt('Itemid', EventbookingHelper::getItemid());

		if ($task == 'process')
		{
			$this->paymentSuccessUrl = JRoute::_('index.php?option=com_eventbooking&view=payment&layout=complete&Itemid=' . $Itemid, false, false);
		}
		else
		{
			if (JPluginHelper::isEnabled('system', 'cache'))
			{
				$this->paymentSuccessUrl = JRoute::_('index.php?option=com_eventbooking&view=complete&Itemid=' . $Itemid . '&pt=' . time(), false, false);
			}
			else
			{
				$this->paymentSuccessUrl = JRoute::_('index.php?option=com_eventbooking&view=complete&Itemid=' . $Itemid, false, false);
			}
		}
	}

	/**
	 * This method need to be implemented by the payment plugin class. It needs to set url which users will be
	 * redirected to when the payment is not success for some reasons. The url is stored in paymentFailureUrl property
	 *
	 * @param int   $id
	 * @param array $data
	 *
	 * @return void
	 */
	protected function setPaymentFailureUrl($id, $data = array())
	{
		$input = JFactory::getApplication()->input;

		if (empty($id))
		{
			$id = $input->getInt('id', 0);
		}

		$Itemid = $input->getInt('Itemid', EventbookingHelper::getItemid());

		$task = $input->getCmd('task');

		if ($task == 'process')
		{
			$this->paymentFailureUrl = JRoute::_('index.php?option=com_eventbooking&view=failure&Itemid=' . $Itemid, false, false);
		}
		else
		{
			$this->paymentFailureUrl = JRoute::_('index.php?option=com_eventbooking&view=failure&id=' . $id . '&Itemid=' . $Itemid, false, false);
		}
	}

	/**
	 * This method need to be implemented by the payment plugin class. It is called when a payment success. Usually,
	 * this method will update status of the order to success, trigger onPaymentSuccess event and send notification emails
	 * to administrator(s) and customer
	 *
	 * @param JTable $row
	 * @param string $transactionId
	 *
	 * @return void
	 */
	protected function onPaymentSuccess($row, $transactionId)
	{
		$config = EventbookingHelper::getConfig();
		if ($row->process_deposit_payment)
		{
			$row->deposit_payment_transaction_id = $transactionId;
			$row->payment_status                 = 1;
			$row->store();

			JPluginHelper::importPlugin('eventbooking');
			$dispatcher = JEventDispatcher::getInstance();
			$dispatcher->trigger('onDepositPaymentSuccess', array($row));

			EventbookingHelperMail::sendDepositPaymentEmail($row, $config);
		}
		else
		{
			$row->transaction_id = $transactionId;
			$row->payment_date   = gmdate('Y-m-d H:i:s');
			$row->published      = 1;
			$row->store();

			if ($row->is_group_billing)
			{
				EventbookingHelper::updateGroupRegistrationRecord($row->id);
			}

			JPluginHelper::importPlugin('eventbooking');
			$dispatcher = JEventDispatcher::getInstance();
			$dispatcher->trigger('onAfterPaymentSuccess', array($row));
			EventbookingHelper::sendEmails($row, $config);
		}
	}

	/**
	 * This method need to be implemented by the payment gateway class. It needs to init the JTable order record,
	 * update it with transaction data and then call onPaymentSuccess method to complete the order.
	 *
	 * @param int    $id
	 * @param string $transactionId
	 *
	 * @return mixed
	 */
	protected function onVerifyPaymentSuccess($id, $transactionId)
	{
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
	}

	/**
	 * This method is usually called by payment method class to add additional data
	 * to the request message before that message is actually sent to the payment gateway
	 *
	 * @param \Omnipay\Common\Message\AbstractRequest $request
	 * @param JTable                                  $row
	 * @param array                                   $data
	 */
	protected function beforeRequestSend($request, $row, $data)
	{
		parent::beforeRequestSend($request, $row, $data);

		// Set return, cancel and notify URL
		$Itemid  = JFactory::getApplication()->input->getInt('Itemid', 0);
		$siteUrl = JUri::base();
		$request->setCancelUrl($siteUrl . 'index.php?option=com_eventbooking&task=cancel&id=' . $row->id . '&Itemid=' . $Itemid);
		$request->setReturnUrl($siteUrl . 'index.php?option=com_eventbooking&task=payment_confirm&id=' . $row->id . '&payment_method=' . $this->name . '&Itemid=' . $Itemid);
		$request->setNotifyUrl($siteUrl . 'index.php?option=com_eventbooking&task=payment_confirm&id=' . $row->id . '&payment_method=' . $this->name . '&notify=1&Itemid=' . $Itemid);
		$request->setAmount($data['amount']);
		$request->setCurrency($data['currency']);
		$request->setDescription($data['item_name']);

		if (empty($this->redirectHeading))
		{
			$language    = JFactory::getLanguage();
			$languageKey = 'EB_WAIT_' . strtoupper(substr($this->name, 3));

			if ($language->hasKey($languageKey))
			{
				$redirectHeading = JText::_($languageKey);
			}
			else
			{
				$redirectHeading = JText::sprintf('EB_REDIRECT_HEADING', $this->getTitle());
			}

			$this->setRedirectHeading($redirectHeading);
		}
	}
}
