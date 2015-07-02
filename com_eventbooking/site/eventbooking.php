<?php
/**
 * @version        	2.0.0
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
 
// no direct access
defined('_JEXEC') or die();

error_reporting(E_ALL);

define('EB_TBC_DATE', '2099-12-31 00:00:00');

require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';
EventbookingHelper::prepareRequestData();

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