<?php
/**
 * @version        1.7.2
 * @package        Joomla
 * @subpackage     Event Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die ;
$db = JFactory::getDbo();
require_once JPATH_ROOT . '/components/com_eventbooking/helper/helper.php';
require_once JPATH_ROOT . '/components/com_eventbooking/helper/route.php';
EventBookingHelper::loadLanguage();
$fieldSuffix      = EventbookingHelper::getFieldSuffix();
$numberCategories = $params->get('number_categories', 0);
$sql = 'SELECT a.id, a.name' . $fieldSuffix . ' AS name, COUNT(b.id) AS total_categories  FROM #__eb_categories AS a LEFT JOIN #__eb_categories AS b ON (a.id = b.parent AND b.published =1) WHERE '
	. ' a.parent = 0 AND a.published=1 AND a.access IN (' . implode(',', JFactory::getUser()->getAuthorisedViewLevels()) . ')'
	. ' GROUP BY a.id '
	. ' ORDER BY a.ordering '
	. ($numberCategories ? ' LIMIT ' . $numberCategories : '');
$db->setQuery($sql);
$rows   = $db->loadObjectList();
$itemId = (int) $params->get('item_id');
if (!$itemId)
{
	$itemId = EventbookingHelper::getItemid();
}
require(JModuleHelper::getLayoutPath('mod_eb_category', 'default'));