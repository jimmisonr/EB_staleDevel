<?php
/**
 * @version            2.4.3
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

EventbookingHelper::prepareRequestData();
$input  = new RADInput();
$config = EventbookingHelper::getComponentSettings('site');

RADController::getInstance($input->getCmd('option', null), $input, $config)
	->execute()
	->redirect();