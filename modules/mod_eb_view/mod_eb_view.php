<?php
/**
 * @package        Joomla
 * @subpackage     Events Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 - 2018 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

error_reporting(0);

if (file_exists(JPATH_ROOT . '/components/com_eventbooking/eventbooking.php'))
{
	// Require library + register autoloader
	require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';

	$view        = $params->get('view', 'categories');
	$queryString = $params->get('query_string', '');
	EventbookingHelper::loadLanguage();
	$request = array('option' => 'com_eventbooking', 'view' => $view, 'hmvc_call' => 1);

	if ($queryString)
	{
		parse_str($queryString, $vars);
		$request = array_merge($request, $vars);
	}

	if (!isset($request['Itemid']))
	{
		$request['Itemid'] = EventbookingHelper::getItemid();
	}

	if (!isset($request['limitstart']))
	{
		$appInput   = JFactory::getApplication()->input;
		$start      = $appInput->get->getInt('start', 0);
		$limitStart = $appInput->get->getInt('limitstart', 0);

		if ($start && !$limitStart)
		{
			$limitStart = $start;
		}

		$request['limitstart'] = $limitStart;
	}

	$input  = new RADInput($request);
	$config = require JPATH_ADMINISTRATOR . '/components/com_eventbooking/config.php';
	RADController::getInstance('com_eventbooking', $input, $config)
		->execute();
}
