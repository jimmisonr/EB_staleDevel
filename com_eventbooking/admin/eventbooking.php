<?php
/**
 * @version        	1.7.4
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
error_reporting(0);
//Basic ACL support
if (!JFactory::getUser()->authorise('core.manage', 'com_eventbooking'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}
require_once JPATH_ADMINISTRATOR.'/components/com_eventbooking/libraries/rad/bootstrap.php';

if (JLanguageMultilang::isEnabled() && !EventbookingHelper::isSynchronized())
{
	EventbookingHelper::setupMultilingual();
}

$config = array(
	'table_prefix'	=>	'#__eb_',
	'language_prefix'	=>	'EB',
	'fallback_class'	=>	'EventbookingController',
	'default_view'	=>	'dashboard'
);
if (isset($_POST['language']))
{
	$_REQUEST['language'] = $_POST['language'];
}
$controller = RADController::getInstance($config)->execute();
$controller->redirect();