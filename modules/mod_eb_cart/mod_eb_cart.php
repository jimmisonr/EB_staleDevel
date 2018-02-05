<?php
/**
 * @package        Joomla
 * @subpackage     Event Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 - 2018 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die;

if (file_exists(JPATH_ROOT . '/components/com_eventbooking/helper/helper.php'))
{
	// Require library + register autoloader
	require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

	JFactory::getDocument()->addStyleSheet(JUri::root(true) . '/media/com_eventbooking/assets/css/style.css');

	EventbookingHelper::loadLanguage();
	$Itemid = (int) $params->get('item_id');

	if (!$Itemid)
	{
		$Itemid = EventbookingHelper::getItemid();
	}

	$config = EventbookingHelper::getConfig();
	$cart   = new EventbookingHelperCart();
	$rows   = $cart->getEvents();

	if (count($rows) && $config->show_discounted_price)
	{
		foreach ($rows as $row)
		{
			$row->rate = $row->discounted_rate;
		}
	}

	require JModuleHelper::getLayoutPath('mod_eb_cart');
}
