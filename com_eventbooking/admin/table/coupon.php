<?php

/**
 * Coupon Table Class
 *
 */
class EventbookingTableCoupon extends JTable
{

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function __construct(& $db)
	{
		parent::__construct('#__eb_coupons', 'id', $db);
	}
}
