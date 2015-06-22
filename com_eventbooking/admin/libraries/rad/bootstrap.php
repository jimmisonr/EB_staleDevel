<?php
/**
 * Register the prefix so that the classes in RAD library can be auto-load
 */
defined('_JEXEC') or die();

JLoader::registerPrefix('RAD', dirname(__FILE__));
$app = JFactory::getApplication();
if ($app->isAdmin())
{
	JLoader::registerPrefix('Eventbooking', JPATH_ADMINISTRATOR . '/components/com_eventbooking');
	JLoader::register('EventbookingHelper', JPATH_ROOT . '/components/com_eventbooking/helper/helper.php');
	JLoader::register('EventbookingHelperHtml', JPATH_ROOT . '/components/com_eventbooking/helper/html.php');	
	JLoader::register('EventbookingHelperCart', JPATH_ROOT . '/components/com_eventbooking/helper/cart.php');
	JLoader::register('EventbookingHelperRoute', JPATH_ROOT . '/components/com_eventbooking/helper/route.php');
	JLoader::register('EventbookingHelperJquery', JPATH_ROOT . '/components/com_eventbooking/helper/jquery.php');
}
else
{
	JLoader::registerPrefix('Eventbooking', JPATH_ROOT . '/components/com_eventbooking');
}
JLoader::register('os_payments', JPATH_ROOT . '/components/com_eventbooking/payments/os_payments.php');
JLoader::register('os_payment', JPATH_ROOT . '/components/com_eventbooking/payments/os_payment.php');
JLoader::register('JFile', JPATH_LIBRARIES . '/joomla/filesystem/file.php');




