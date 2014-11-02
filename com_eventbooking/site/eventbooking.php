<?php
/**
 * @version        	1.6.6
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2014 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
error_reporting(0);
jimport('joomla.filesystem.file');
// Require the controller
define('EB_TBC_DATE', '2099-12-31 00:00:00');
define('EB_ONLY_SHOW_REGISTRANTS_OF_EVENT_OWNER', 0);
define('VIEW_LIST_WIDTH', 800);
define('VIEW_LIST_HEIGHT', 600);
define('TC_POPUP_WIDTH', 800);
define('TC_POPUP_HEIGHT', 600);
require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';
// Init the controller
$controller = new EventbookingController();
// Perform the Request task
$controller->execute(JFactory::getApplication()->input->get('task', 'display'));
$controller->redirect();