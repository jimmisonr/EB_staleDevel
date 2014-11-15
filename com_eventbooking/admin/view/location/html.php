<?php
/**
 * @version        	1.6.8
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2014 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventbookingViewLocationHtml extends RADViewItem
{

	function display()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('name AS `value`, name AS `text`')
			->from('#__eb_countries')
			->order('name');				
		$db->setQuery($query);
		$options = array();
		$options[] = JHtml::_('select.option', '', JText::_('EB_SELECT_COUNTRY'));
		$options = array_merge($options, $db->loadObjectList());
		$this->lists['country'] = JHtml::_('select.genericlist', $options, 'country', ' class="inputbox" ', 'value', 'text', $this->item->country);
								
		parent::display();
	}
}