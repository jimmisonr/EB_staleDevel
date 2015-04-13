<?php
/**
 * @version        1.7.2
 * @package        Joomla
 * @subpackage     Event Booking
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2010 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
error_reporting(0);
require_once JPATH_ROOT . '/components/com_eventbooking/helper/helper.php';
require_once JPATH_ROOT . '/components/com_eventbooking/helper/route.php';
$document = JFactory::getDocument();
EventBookingHelper::loadLanguage();
$config = EventBookingHelper::getConfig();
$option = JRequest::getCmd('option');
$document->addStyleSheet(JUri::base(true) . "/components/com_eventbooking/assets/css/style.css");
if (($config->load_jquery !== '0') && ($option != 'com_eventbooking'))
{
	EventbookingHelper::loadJQuery();
}
if ($option != 'com_eventbooking')
{
	JHtml::_('script', JUri::root() . '/components/com_eventbooking/assets/js/noconflict.js', false, false);
}
$document->addScript(JUri::base(true) . '/components/com_eventbooking/assets/js/minicalendar.js');
EventbookingHelper::addLangLinkForAjax();
if ($config->calendar_theme)
{
	$theme = $config->calendar_theme;
}
else
{
	$theme = 'default';
}
$document->addStylesheet(JUri::base(true) . '/components/com_eventbooking/assets/css/themes/' . $theme . '.css');
require_once(dirname(__FILE__) . '/helper.php');
$jtask = JRequest::getVar('task');
if ($jtask != 'change_minical')
{
	$month = JRequest::getInt('month');
	if (!$month)
	{
		$month = (int) $params->get('default_month', 0);
		if ($month > 0)
		{
			JRequest::setVar('month', $month);
		}
	}
}
list ($year, $month, $day) = modMiniCalendarHelper::_getYMD();
$data      = modMiniCalendarHelper::_getCalendarData($year, $month, $day);
$listmonth = array(JText::_('EB_JAN'), JText::_('EB_FEB'), JText::_('EB_MARCH'),
	JText::_('EB_APR'), JText::_('EB_MAY'), JText::_('EB_JUNE'), JText::_('EB_JUL'),
	JText::_('EB_AUG'), JText::_('EB_SEP'), JText::_('EB_OCT'), JText::_('EB_NOV'),
	JText::_('EB_DEC'));
if (isset($params))
{
	$itemId = (int) $params->get('item_id');
}
else
{
	$itemId = 0;
}
if (!$itemId)
{
	$itemId = JRequest::getInt('Itemid');
}

require(JModuleHelper::getLayoutPath('mod_eb_minicalendar', 'default'));
?>