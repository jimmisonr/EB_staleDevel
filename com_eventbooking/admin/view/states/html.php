<?php
/**
 * @version        	1.7.0
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventbookingViewStatesHtml extends RADViewList
{

	function display()
	{
		$db = JFactory::getDbo();
		$db->setQuery("SELECT `id` AS value, `name` AS text FROM `#__eb_countries` WHERE `published`=1");
		$options = $db->loadObjectList();
		array_unshift($options,JHtml::_('select.option',0,' - '.JText::_('EB_SELECT_COUNTRY').' - '));
		$this->lists['filter_country_id'] = JHtml::_('select.genericlist', $options, 'filter_country_id', ' class="inputbox" onchange="submit();" ','value', 'text', $this->state->filter_country_id);
		
		parent::display();
	}
}