<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die;
require_once JPATH_ROOT . '/components/com_eventbooking/helper/helper.php';

$config = EventbookingHelper::getConfig();
EventbookingHelper::loadLanguage();
JFactory::getDocument()->addStylesheet(JUri::base(true) . '/media/com_eventbooking/assets/css/style.css', 'text/css', null, null);

$db               = JFactory::getDbo();
$query            = $db->getQuery(true);
$numberLocations  = (int) $params->get('number_cities', 0);
$showNumberEvents = (int) $params->get('show_number_events', 1);

$query->select('a.city, COUNT(b.id) AS total_events')
	->from('#__eb_locations AS a')
	->innerJoin('#__eb_events AS b ON a.id = b.location_id')
	->where('a.published = 1')
	->where('b.published = 1')
	->where('b.access IN (' . implode(',', JFactory::getUser()->getAuthorisedViewLevels()) . ')')	
	->where('a.city != ""')
	->group('a.city')
	->order('a.city');

if ($config->hide_past_events)
{
	$currentDate = JHtml::_('date', 'Now', 'Y-m-d');
	$query->where('DATE(b.event_date) >= ' . $db->quote($currentDate));
}

if ($numberLocations)
{
	$db->setQuery($query, 0, $numberLocations);
}
else
{
	$db->setQuery($query);
}
$rows   = $db->loadObjectList();
$itemId = (int) $params->get('item_id');
if (!$itemId)
{
	$itemId = EventbookingHelper::getItemid();
}

require JModuleHelper::getLayoutPath('mod_eb_cities', 'default');