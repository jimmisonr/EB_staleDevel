<?php
/**
 * @version		1.6.1
 * @package		Joomla
 * @subpackage	Event Booking
 * @author  Tuan Pham Ngoc
 * @copyright	Copyright (C) 2010 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */		
defined('_JEXEC') or die ('Restricted access');
$document = & JFactory::getDocument() ;		
$styleUrl = JURI::base(true).'/components/com_eventbooking/assets/css/style.css';		
$document->addStylesheet( $styleUrl, 'text/css', null, null );	
$user = & JFactory::getUser();
require_once JPATH_ROOT.'/components/com_eventbooking/helper/helper.php';
EventBookingHelper::loadLanguage();	
$app = JFactory::getApplication() ;
$db =  JFactory::getDBO();		
$numberLocations = $params->get('number_locations', 0);
$showNumberEvents = $params->get('show_number_events', 1);
if ($app->getLanguageFilter()) {
	$extraWhere = ' AND a.language IN (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')';
} else {
	$extraWhere = '' ;
}
$sql = 	 'SELECT a.id, a.name, COUNT(b.id) AS total_events  FROM #__eb_locations AS a LEFT JOIN #__eb_events AS b ON (a.id = b.location_id AND (b.access = 0 OR b.access IN ('.implode(',', $user->getAuthorisedViewLevels()).'))) WHERE '
.' a.published=1 '
. $extraWhere
.' GROUP BY a.id '
.' HAVING total_events > 0 '
.' ORDER BY a.name '
.( $numberLocations ? ' LIMIT '.$numberLocations : '')
;	
$db->setQuery($sql) ;

$rows = $db->loadObjectList() ;
$itemId = (int) $params->get('item_id');
if (!$itemId)	
    $itemId = EventBookingHelper::getItemid() ;			 
    			
require(JModuleHelper::getLayoutPath('mod_eb_locations', 'default'));