<?php
/**
 * @version            2.0.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
error_reporting(0);
// Require the controller
define('EB_TBC_DATE', '2099-12-31 00:00:00');
define('VIEW_LIST_WIDTH', 800);
define('VIEW_LIST_HEIGHT', 600);
define('TC_POPUP_WIDTH', 800);
define('TC_POPUP_HEIGHT', 600);

EventbookingHelper::prepareRequestData();
require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

$input = new RADInput();
$task  = $input->getCmd('task', '');
//Handle BC for existing payment plugins
if ($task == 'payment_confirm')
{
	//Lets Register controller handle these tasks
	$input->set('task', 'register.' . $task);
}
$config = EventbookingHelper::getComponentSettings('site');

RADController::getInstance($input->getCmd('option', null), $input, $config)
	->execute()
	->redirect();