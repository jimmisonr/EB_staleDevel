<?php
/**
 * @version        1.7.2
 * @package        Joomla
 * @subpackage     Event Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die ('');
error_reporting(0);
$document = JFactory::getDocument();
$styleUrl = JURI::base(true) . '/components/com_eventbooking/assets/css/style.css';
$document->addStylesheet($styleUrl, 'text/css', null, null);
$user = JFactory::getUser();
require_once JPATH_ROOT . '/components/com_eventbooking/helper/helper.php';
EventBookingHelper::loadLanguage();
$db           = JFactory::getDBO();
$showCategory = $params->get('show_category', 1);
$showLocation = $params->get('show_location', 0);

$categoryId = JRequest::getInt('category_id', 0);
$locationId = JRequest::getInt('location_id', 0);

$text = JRequest::getString('search', '');
if (empty($text))
{
	$text = JText::_('EB_SEARCH_WORD');
}
$text = htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
//Build Category Drodown
if ($showCategory)
{
	$fieldSuffix = EventbookingHelper::getFieldSuffix();
	$sql = "SELECT id, parent, parent AS parent_id, name" . $fieldSuffix . " AS name, name" . $fieldSuffix . " AS title FROM #__eb_categories WHERE published = 1 AND (`access` = 0 OR `access` IN (" . implode(',', $user->getAuthorisedViewLevels()) . "))" . $extraWhere . ' ORDER BY ordering ';
	$db->setQuery($sql);
	$rows     = $db->loadObjectList();
	$children = array();
	if ($rows)
	{
		// first pass - collect children
		foreach ($rows as $v)
		{
			$pt   = $v->parent;
			$list = @$children[$pt] ? $children[$pt] : array();
			array_push($list, $v);
			$children[$pt] = $list;
		}
	}
	$list      = JHTML::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
	$options   = array();
	$options[] = JHTML::_('select.option', 0, JText::_('EB_SELECT_CATEGORY'));
	foreach ($list as $listItem)
	{
		$options[] = JHTML::_('select.option', $listItem->id, '&nbsp;&nbsp;&nbsp;' . $listItem->treename);
	}
	$lists['category_id'] = JHtml::_('select.genericlist', $options, 'category_id', array(
		'option.text.toHtml' => false,
		'list.attr'          => 'class="inputbox category_box" ',
		'option.text'        => 'text',
		'option.key'         => 'value',
		'list.select'        => $categoryId,
	));
}

//Build location dropdown
if ($showLocation)
{
	$options = array();
	$sql = 'SELECT id, name FROM #__eb_locations  WHERE published=1 ORDER BY name';
	$db->setQuery($sql);
	$options[]            = JHtml::_('select.option', 0, JText::_('EB_SELECT_LOCATION'), 'id', 'name');
	$options              = array_merge($options, $db->loadObjectList());
	$lists['location_id'] = JHtml::_('select.genericlist', $options, 'location_id', ' class="inputbox location_box" ', 'id', 'name', $locationId);
}
$itemId = (int) $params->get('item_id');
if (!$itemId)
{
	$itemId = EventbookingHelper::getItemid();
}

$layout = $params->get('layout_type', 'default');

require(JModuleHelper::getLayoutPath('mod_eb_search', 'default'));