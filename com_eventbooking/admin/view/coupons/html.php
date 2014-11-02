<?php
/**
 * @version        	1.6.6
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2014 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventbookingViewCouponsHtml extends RADViewList
{

	function display()
	{
		$dateFormat = EventbookingHelper::getConfigValue('date_format');
		$nullDate = JFactory::getDbo()->getNullDate();
		$discountTypes = array(0 => '%', 1 => EventbookingHelper::getConfigValue('currency_symbol'));
		$this->discountTypes = $discountTypes;
		$this->nullDate = $nullDate;
		$this->dateFormat = $dateFormat;
		
		parent::display();
	}
}