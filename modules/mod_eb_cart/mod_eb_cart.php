<?php
/**
 * @version        1.6.6
 * @package        Joomla
 * @subpackage     Event Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 - 2015 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
// Include the syndicate functions only once
if (file_exists(JPATH_ROOT . '/components/com_eventbooking/helper/helper.php'))
{
	$document = JFactory::getDocument();
	$db       = JFactory::getDbo();
	$document->addStyleSheet(JURI::base(true) . 'components/com_eventbooking/assets/css/style.css');
	require_once JPATH_ROOT . '/components/com_eventbooking/helper/helper.php';
	require_once JPATH_ROOT . '/components/com_eventbooking/helper/cart.php';
	require_once JPATH_ROOT . '/components/com_eventbooking/helper/route.php';
	EventBookingHelper::loadLanguage();
	$Itemid = (int) $params->get('item_id');
	if (!$Itemid)
	{
		$Itemid = EventbookingHelper::getItemid();
	}

	$config         = EventbookingHelper::getConfig();
	$cart           = new EventbookingHelperCart();
	$rows = $cart->getEvents();
	if (count($rows) && $config->show_discounted_price)
	{
		foreach ($rows as $row)
		{
			$row->rate = $row->discounted_rate;
		}
	}
	require(JModuleHelper::getLayoutPath('mod_eb_cart'));
}