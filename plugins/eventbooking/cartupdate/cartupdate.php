<?php
/**
 * @version		1.5.0
 * @package		Joomla
 * @subpackage	Event Booking
 * @author  Tuan Pham Ngoc
 * @copyright	Copyright (C) 2010 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
// No direct access.
defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');
class plgEventBookingCartUpdate extends JPlugin
{	
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);		
	}
	
	public function onAfterPaymentSuccess($row) {
		$db = JFactory::getDBO() ;
		$sql = 'UPDATE #__eb_registrants SET published=1, payment_date=NOW() WHERE cart_id='.$row->id;
		$db->setQuery($sql) ;
		$db->query();
	} 	
}