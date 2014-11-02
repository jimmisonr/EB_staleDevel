<?php
/**
 * @version		1.5.0
 * @package		Joomla
 * @subpackage	Event Booking
 * @author  Tuan Pham Ngoc
 * @copyright	Copyright (C) 2010 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
// Include the syndicate functions only once
if (file_exists(JPATH_ROOT.'/components/com_eventbooking/helper/helper.php')) {
	$document = JFactory::getDocument();	
	$db = JFactory::getDBO();
	$document->addStyleSheet(JURI::base(true).'components/com_eventbooking/assets/css/style.css') ;
	require_once JPATH_ROOT.'/components/com_eventbooking/helper/helper.php';
	require_once JPATH_ROOT.'/components/com_eventbooking/helper/cart.php';
	require_once JPATH_ROOT.'/components/com_eventbooking/helper/route.php';
	EventBookingHelper::loadLanguage();
	$Itemid = (int) $params->get('item_id');
	if (!$Itemid)	
	    $Itemid = EventBookingHelper::getItemid();
	$config = EventBookingHelper::getConfig() ;
	$currencySymbol = $config->currency_symbol;
	$cart = new EventbookingHelperCart();
	$items = $cart->getItems();
	$quantities = $cart->getQuantities() ;
	$quantityArr = array() ;
	for ($i = 0 , $n = count($items) ; $i < $n ; $i++) {
		$quantityArr[$items[$i]] = $quantities[$i] ;
	}	
	if (count($items)) {
		$sql = 'SELECT * FROM #__eb_events WHERE id IN ('.implode(',', $items).')';
		$db->setQuery($sql) ;
		$rows = $db->loadObjectList();
		for ($i = 0, $n = count($rows) ; $i < $n ; $i++) {
			$row = $rows[$i] ;
			$row->quantity =$quantityArr[$row->id] ;
			$row->rate = EventBookingHelper::getRegistrationRate($row->id, $row->quantity);
		}
	} else {
		$rows = array() ;
	}						
	require(JModuleHelper::getLayoutPath('mod_eb_cart'));	
}