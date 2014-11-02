<?php
/** * @version		1.6.6 * @package		Joomla * @subpackage	Event Booking * @author  Tuan Pham Ngoc * @copyright	Copyright (C) 2010 - 2014 Ossolution Team * @license		GNU/GPL, see LICENSE.php */
defined('_JEXEC') or die();
error_reporting(0);
require_once JPATH_ROOT . '/components/com_eventbooking/helper/helper.php';
require_once JPATH_ROOT . '/components/com_eventbooking/helper/route.php';
require_once JPATH_ROOT . '/components/com_eventbooking/helper/jquery.php';
EventbookingHelper::loadLanguage();
$app = JFactory::getApplication();
$db = JFactory::getDBO();
$itemId = (int) $params->get('item_id', 0);
if (!$itemId)
{
	$itemId = EventBookingHelper::getItemid();
}
$user = & JFactory::getUser();
$numberEvents = $params->get('number_events', 6);
$categoryIds = $params->get('category_ids', '');
$showCategory = $params->get('show_category', 1);
$showLocation = $params->get('show_location');
$where = array();
$where[] = 'a.published =1 ';
$where[] = 'DATE(event_date) >= CURDATE()';
// $where[] = '(cut_off_date = "'.$db->getNullDate().'" OR DATE(cut_off_date) >= CURDATE())' ;
if ($categoryIds != '')
{
	$where[] = ' a.id IN (SELECT event_id FROM #__eb_event_categories WHERE category_id IN (' . $categoryIds . '))';
}
$where[] = ' a.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')';
if ($app->getLanguageFilter())
{
	$where[] = ' a.language IN (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')';
}
$sql = 'SELECT a.*, c.name AS location_name FROM #__eb_events AS a ' . ' LEFT JOIN #__eb_locations AS c ' . ' ON a.location_id = c.id ' . ' WHERE ' .
	 implode(' AND ', $where) . ' ORDER BY a.event_date ' . ' LIMIT ' . $numberEvents;
$db->setQuery($sql);
$rows = $db->loadObjectList();
for ($i = 0, $n = count($rows); $i < $n; $i++)
{
	$row = $rows[$i];
	// Get all categories
	$sql = 'SELECT a.id, a.name FROM #__eb_categories AS a INNER JOIN #__eb_event_categories AS b ON a.id = b.category_id WHERE b.event_id=' . $row->id;
	$db->setQuery($sql);
	$categories = $db->loadObjectList();
	if (count($categories))
	{
		$itemCategories = array();
		foreach ($categories as $category)
		{
			$itemCategories[] = '<a href="' . EventbookingHelperRoute::getCategoryRoute($category->id, $itemId) . '"><strong>' . $category->name .
				 '</strong></a>';
		}
		$row->categories = implode('&nbsp;|&nbsp;', $itemCategories);
	}
}
$config = EventBookingHelper::getConfig();
$layout = $params->get('layout', 'default');
$document = JFactory::getDocument();
if ($layout == 'default')
{
	$document->addStyleSheet(JUri::base(true) . '/modules/mod_eb_events/css/style.css');
}
else
{
	if ($config->load_bootstrap_css_in_frontend !== '0')
	{
		EventbookingHelper::loadBootstrap();
	}
	$document->addStyleSheet(JUri::base(true) . '/modules/mod_eb_events/css/improved.css');
}
if ($config->calendar_theme)
{
	$theme = $config->calendar_theme;
}
else
{
	$theme = 'default';
}
$styleUrl = JUri::base(true) . '/components/com_eventbooking/assets/css/themes/' . $theme . '.css';
$document->addStyleSheet($styleUrl);
require (JModuleHelper::getLayoutPath('mod_eb_events', $layout));