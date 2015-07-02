<?php
/**
 * * @version        	2.0.0
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventBookingViewPassword extends JViewLegacy
{

	function display($tpl = null)
	{
		$this->setLayout('default');
		$this->Itemid = JRequest::getInt('Itemid', 0);
		$this->return = JRequest::getVar('return', '', 'none');
		$this->eventId = JRequest::getInt('event_id');
		$this->eventUrl = EventbookingHelperRoute::getEventRoute($this->eventId, 0, $this->Itemid);
		$config = EventbookingHelper::getConfig();
		$this->bootstrapHelper = new EventbookingHelperBootstrap($config->twitter_bootstrap_version);

		parent::display($tpl);
	}
}