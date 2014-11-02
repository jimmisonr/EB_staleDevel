<?php
/**
 * @version		1.5.0
 * @package		Joomla
 * @subpackage	Event Booking
 * @author  Tuan Pham Ngoc
 * @copyright	Copyright (C) 2010 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die ;
jimport('joomla.plugin.plugin');
class plgEventBookingJSactivities extends JPlugin
{	
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);		
	}
	
	public function onAfterPaymentSuccess($row) {
        if (!file_exists(JPATH_ADMINISTRATOR.'/components/com_community/community.php'))
        {
            return;
        }
		require_once JPATH_ROOT.'/components/com_eventbooking/helper/helper.php';
		$itemId = EventBookingHelper::getItemid();
		EventBookingHelper::loadLanguage();
		$db = & JFactory::getDBO();
		$user = & JFactory::getUser();
		jimport('joomla.utilities.date');		
		$db	 = & JFactory::getDBO() ;
		$today =& JFactory::getDate();		
		$sql = 'SELECT title FROM #__eb_events WHERE id='.$row->event_id ;
		$db->setQuery($sql);
		$eventTitle = $db->loadResult();
		$url = JRoute::_('index.php?option=com_eventbooking&task=view_event&event_id='.$row->event_id.'&Itemid='.$itemId);
		$eventTitle = '<a href="'.$url.'"><strong>'.$eventTitle.'<strong></a>' ;		
		$obj = new StdClass();		
		$obj->actor 	= $user->id;
		$obj->target 	= $user->id;
		if ($user->id)
			$obj->title		= JText::sprintf('EB_ACTOR_REGISTER_FOR_EVENT', $eventTitle);		
		else 		
			$obj->title		= JText::sprintf('EB_USER_REGISTER_FOR_EVENT', $row->first_name.' '.$row->last_name, $eventTitle);	
		$obj->content	= '';
		$obj->app		= '';
		$obj->cid		= $user->id;
		$obj->params	= null;
		$obj->created	= $today->toMySQL();
		$obj->points	= 0;
		$obj->access	= 0;			
		$db->insertObject('#__community_activities', $obj);	
	} 	
}
