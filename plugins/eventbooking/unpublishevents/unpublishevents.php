<?php
/**
 * @version		1.5.0
 * @package		Joomla
 * @subpackage	Event Booking
 * @author  Tuan Pham Ngoc
 * @copyright	Copyright (C) 2010 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
// No direct access.
defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');
class plgEventBookingUnpublishEvents extends JPlugin
{	
	public function __construct(& $subject, $config)
	{		
		parent::__construct($subject, $config);		
	}
	
	
	function onAfterStoreRegistrant($row) {
		if ($row->payment_method == 'os_offline') {
			$this->_processUnpublishEvent($row->event_id) ;
		}
	}
	
	public function onAfterPaymentSuccess($row) {
		$this->_processUnpublishEvent($row->event_id) ;		
	} 
		
	function _processUnpublishEvent($eventId) {
		$db = & JFactory::getDbo() ;
		$sql = 'SELECT event_capacity FROM #__eb_events WHERE id='.$eventId;
		$db->setQuery($sql);		
		$capacity = (int) $db->loadResult();		
		if ($capacity > 0) {
			$sql = 'SELECT COUNT(*) FROM #__eb_registrants AS b WHERE event_id='.$eventId.' AND b.group_id=0 AND (b.published = 1 OR (b.payment_method LIKE "os_offline%" AND b.published != 2))';
			$db->setQuery($sql);
			$totalRegistrants = (int) $db->loadResult() ;			
			if ($totalRegistrants >= $capacity) {								
				//Unpublish the event
				$sql = 'UPDATE #__eb_events SET published=0 WHERE id='.$eventId;
				$db->setQuery($sql) ;
				$db->query();
			}
		}	
	}
}