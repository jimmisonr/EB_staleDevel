<?php
/**
 * @version		1.5.0
 * @package		Joomla
 * @subpackage	Event Booking
 * @author  Tuan Pham Ngoc
 * @copyright	Copyright (C) 2010 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
class plgSystemEBReminder extends JPlugin
{
	function onAfterInitialise()
	{		
		error_reporting(0);
		$secretCode = trim($this->params->get('secret_code'));
		if ($secretCode && (JFactory::getApplication()->input->getString('secret_code') != $secretCode))
		{
			return ;
		}
		if (file_exists(JPATH_ROOT.'/components/com_eventbooking/eventbooking.php'))
		{
			$lastRun = (int) $this->params->get('last_run', 0);
			$numberEmailSendEachTime = (int) $this->params->get('number_registrants', 0);
			$currentTime = time() ;
			$numberMinutes = ($currentTime - $lastRun)/60 ;
			//This plugin win runs in each 10 minutes
			if ($numberMinutes >= 30)
			{
				require_once JPATH_ROOT.'/components/com_eventbooking/helper/helper.php' ;
				require_once JPATH_ROOT.'/components/com_eventbooking/models/reminder.php' ;
				EventBookingModelReminder::sendReminder($numberEmailSendEachTime);
				$db = JFactory::getDbo() ;
				$query = $db->getQuery(true);
				//Store last run time
				$this->params->set('last_run', $currentTime);
				$params = $this->params->toString();

				$query->update('#__extensions')
					->set('params='.$db->quote($params))
					->where('`element`="ebreminder" AND `folder`="system"');
				$db->setQuery($query);
				$db->execute();
			}
		}				
		return true ;		
	}
}
