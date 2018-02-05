<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2018 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die;

// Require library + register autoloader
require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

EventbookingHelper::prepareRequestData();

$input  = new RADInput();
$config = require JPATH_ADMINISTRATOR . '/components/com_eventbooking/config.php';

RADController::getInstance($input->getCmd('option', null), $input, $config)
	->execute()
	->redirect();
