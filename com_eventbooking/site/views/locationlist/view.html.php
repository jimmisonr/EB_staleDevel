<?php
/**
 * @version        	1.6.10
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventBookingViewLocationlist extends JViewLegacy
{

	function display($tpl = null)
	{
		$user = JFactory::getUser();
		if (!$user->authorise('eventbooking.addlocation', 'com_eventbooking'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_("EB_NO_PERMISSION"));
			return;
		}
		$model = $this->getModel();
		$this->items = $model->getData();
		$this->pagination = $model->getPagination();
		$this->Itemid = JRequest::getInt('Itemid', 0);
		
		parent::display($tpl);
	}
}