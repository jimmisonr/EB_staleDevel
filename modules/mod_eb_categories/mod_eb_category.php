<?php
/**
 * @version		1.6.1
 * @package		Joomla
 * @subpackage	Event Booking
 * @author  Tuan Pham Ngoc
 * @copyright	Copyright (C) 2010 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die ;	
$app = JFactory::getApplication() ;
$db = JFactory::getDbo() ;
$user = JFactory::getUser();
require_once JPATH_ROOT.'/components/com_eventbooking/helper/helper.php';
require_once JPATH_ROOT.'/components/com_eventbooking/helper/route.php';
EventBookingHelper::loadLanguage();	
$numberCategories = $params->get('number_categories', 0);
if ($app->getLanguageFilter()) {
	$extraWhere = ' AND a.language IN (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')';
} else {
	$extraWhere = '' ;
}

$sql = 	'SELECT a.id, a.name, COUNT(b.id) AS total_categories  FROM #__eb_categories AS a LEFT JOIN #__eb_categories AS b ON (a.id = b.parent AND b.published =1) WHERE '
.' a.parent = 0 AND a.published=1 AND a.access IN ('.implode(',', $user->getAuthorisedViewLevels()).')'
.$extraWhere
.' GROUP BY a.id '
.' ORDER BY a.ordering '
.( $numberCategories ? ' LIMIT '.$numberCategories : '')
;
      	
$db->setQuery($sql) ;	
$rows = $db->loadObjectList() ;
$itemId = (int) $params->get('item_id');
if (!$itemId)		
    $itemId = EventBookingHelper::getItemid() ;			 
require(JModuleHelper::getLayoutPath('mod_eb_category', 'default'));