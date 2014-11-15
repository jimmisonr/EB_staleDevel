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
class EventbookingViewDaylightsavingHtml extends RADViewHtml
{

	public function display()
	{
		parent::display();
        $this->addToolbar();
	}

	protected function addToolbar()
	{
        JToolBarHelper::title(JText::_( 'EB_FIX_DAYLIGHT_SAVING_TIME'));
	}
}