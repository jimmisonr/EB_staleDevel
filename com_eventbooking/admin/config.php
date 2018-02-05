<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2018 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

$config = [
	'class_prefix'    => 'Eventbooking',
	'language_prefix' => 'EB',
	'table_prefix'    => '#__eb_',
];

if (class_exists('EventbookingControllerOverrideController'))
{
	$config['default_controller_class'] = 'EventbookingControllerOverrideController';
}
else
{
	$config['default_controller_class'] = 'EventbookingController';
}

if (JFactory::getApplication()->isAdmin())
{
	$config['default_view'] = 'dashboard';
}
else
{
	$config['default_view'] = 'categories';
}

return $config;