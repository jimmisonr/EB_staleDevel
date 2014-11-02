<?php
/**
 * @version		1.6.5
 * @package		Joomla
 * @subpackage	Events Booking
 * @author  Tuan Pham Ngoc
 * @copyright	Copyright (C) 2012 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die();

/**
 * Events Booking invoice plugin
 *
 * @package		Joomla
 * @subpackage	Events Booking
 */
class plgEventbookingInvoice extends JPlugin
{

	/**
	 * Run when a registrant is stored
	 * @param RegistrantEventBooking $row
	 */
	function onAfterPaymentSuccess($row)
	{
		if (!$row->invoice_number)
		{
			$this->_processInvoiceNumber($row);
		}
		
		return true;
	}
	
	/**
	 * Run when a registrant is stored
	 * @param RegistrantEventBooking $row
	 */
	
	function onAfterStoreRegistrant($row)
	{
		if ((strpos($row->payment_method, 'os_offline') !== false) && !$row->invoice_number)
		{
			$this->_processInvoiceNumber($row);
		}
	}
	
	
	private function _processInvoiceNumber($row)
	{
		if (EventbookingHelper::needInvoice($row))
		{
			$invoiceNumber = EventbookingHelper::getInvoiceNumber();
			$row->invoice_number = $invoiceNumber;
			$row->store();
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->update('#__eb_registrants')
				->set('invoice_number='.$db->quote($invoiceNumber))
				->where('id='.$row->id.' OR cart_id='.$row->id.' OR group_id='.$row->id);
			$db->setQuery($query);
			$db->execute();
		}		
	}
}
