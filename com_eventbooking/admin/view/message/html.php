<?php
/**
 * @version        	1.7.0
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
class EventbookingViewMessageHtml extends RADViewHtml
{

	function display()
	{	
		$languages = EventbookingHelper::getLanguages();	
		$message = $this->model->getData();				
		$this->message = $message;
		$this->languages = $languages;
		$this->addToolbar();
																															
		parent::display();
	}
	
	
	public function addToolbar()
	{
		JToolBarHelper::title(   JText::_( 'Emails & Messages' ), 'generic.png' );
		JToolBarHelper::save('save');
		JToolBarHelper::cancel('cancel');
		$editor = JFactory::getEditor() ;
	}
}