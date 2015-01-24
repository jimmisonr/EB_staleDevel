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
defined( '_JEXEC' ) or die ;
class EventbookingModelMassmail extends RADModel
{	
    /**
     * Send email to all registrants of event
     * 
     * @param array $data
     */
	function send($data) {	    
	    if ($data['event_id'] >= 1) {
            $app = JFactory::getApplication();
	    	$mailer = JFactory::getMailer();
	        $config = EventbookingHelper::getConfig() ;
    		$db = JFactory::getDbo();
            if ($config->from_name)
            {
                $fromName = $config->from_name;
            }
            else
            {
                $fromName = $app->getCfg('fromname');
            }
            if ($config->from_email)
            {
                $fromEmail = $config->from_email;
            }
            else
            {
                $fromEmail = $app->getCfg('mailfrom');
            }
	        $query = $db->getQuery(true);
	        $query->select('*')
	        	->from('#__eb_events AS a')
	        	->leftJoin('#__eb_locations AS b ON a.location_id = b.id')
	        	->where('a.id='.(int)$data['event_id']);	        
	        $db->setQuery($query) ;
	        $event = $db->loadObject() ;
	        
	        $replaces = array() ;
	        $replaces['event_title'] = $event->title ;
	        $replaces['event_date'] = JHtml::_('date', $event->event_date, $config->event_date_format, null);
	        $replaces['short_description'] = $event->short_description;
	        $replaces['description'] = $event->description ;
	        $replaces['event_location'] = $event->name.' ('.$event->address.', '.$event->city.', '.$event->zip.', '.$event->country.')' ;
		
	        $query->clear();
	        $query->select('first_name, last_name, email')
	        	->from('#__eb_registrants')
	        	->where('event_id='.$data['event_id'].' AND (published=1 OR (payment_method LIKE "os_offline%" AND published != 2))');	        	       
	        $db->setQuery($query) ;
	        $rows = $db->loadObjectList() ;
	        $emails = array() ;	        
	        $subject = $data['subject'] ;
	        $body = $data['description'] ;
    	    foreach ($replaces as $key=>$value) {
    			$key = strtoupper($key) ;
    			$body = str_replace("[$key]", $value, $body) ;
    		}	        	         		
    		if (count($rows)) {
    		    foreach ($rows as $row) {
    		        $message = $body ;
					
    		        $email = $row->email ;
    		        if (!in_array($email, $emails)) {
    		            $message = str_replace("[FIRST_NAME]", $row->first_name, $message) ;
    		            $message = str_replace("[LAST_NAME]", $row->last_name, $message) ;
    		            $emails[] = $email ;    		            
    		            $mailer->sendMail($fromEmail, $fromName, $email, $subject, $message, 1);
    		            $mailer->ClearAllRecipients();    		                		                		                		               		                		                		            
    		        }    		           
    		    }
    		}
	    }	    	    	   	
	    	       
	    return true ;
	}
}