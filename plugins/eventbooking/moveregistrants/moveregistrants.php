<?php
/**
 * @version		1.5.0
 * @package		Joomla
 * @subpackage	Event Booking
 * @author  Tuan Pham Ngoc
 * @copyright	Copyright (C) 2010 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined( '_JEXEC' ) or die( 'Restricted access' );
class plgEventBookingMoveRegistrants extends JPlugin
{	
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);		
	}	
	/**
	 * Move users from waiting list into registrants 
	 * @param object $row
	 */
	public function onRegistrationCancel($row) {		
		require_once JPATH_ROOT.'/components/com_eventbooking/helper/helper.php';		
		require_once JPATH_ADMINISTRATOR.'/components/com_eventbooking/table/eventbooking.php';
		$db = & JFactory::getDbo() ;
		$config = EventBookingHelper::getConfig();
		$fields = array(
			'user_id',
			'event_id',
			'first_name',
			'last_name',
			'organization',
			'address',
			'address2',
			'city',
			'state',
			'country',
			'zip',
			'phone',
			'fax',
			'email',
			'number_registrants',
			'register_date'																										
		);
		$totalRegistrants = 0 ;
		while ($totalRegistrants < $row->number_registrants) {
			$data = array();
			$remainingNumberRegistrants = $row->number_registrants - $totalRegistrants ;
			$sql = 'SELECT * FROM #__eb_waiting_lists WHERE event_id='.$row->event_id.' AND number_registrants <='.$remainingNumberRegistrants.' ORDER BY id LIMIT 1 ' ;
			$db->setQuery($sql);
			$waitingRegistrant = $db->loadObject() ;
			if ($waitingRegistrant) {
				$registrant = & JTable::getInstance('EventBooking', 'Registrant') ;
				foreach($fields as $field) {
					$data[$field] = $waitingRegistrant->{$field};
				}
				$registrant->bind($data);
				$registrant->register_date = date('Y-m-d H:i:s');
				if ($registrant->number_registrants >= 2) {
					$registrant->is_group_billing = 1 ;
				}
				$registrant->published = 1 ;
				$registrant->store();
				if ($registrant->number_registrants >=2) {
					$numberRegistrants = $registrant->number_registrants ;
					$rowMember = & JTable::getInstance('EventBooking', 'Registrant') ;
					for ($i = 0 ; $i < $numberRegistrants ; $i++) {
						$rowMember->id = 0 ;
						$rowMember->group_id = $registrant->id ;
						$rowMember->number_registrants = 1 ;
						$rowMember->published = 0 ;
						$rowMember->register_date = date('Y-m-d H:i:s');
						$rowMember->store() ;
					}
				}					
				EventBookingHelper::sendEmails($registrant, $config);
				if ($waitingRegistrant->number_registrants) {
					$totalRegistrants += $waitingRegistrant->number_registrants ;
				} else {
					$totalRegistrants++ ;
				}																		
				$sql = 'DELETE FROM #__eb_waiting_lists WHERE id='.$waitingRegistrant->id ;
				$db->setQuery($sql) ;
				$db->query();
				
			} else {
				break ;
			}			
		}

		
		return true ;
	} 	
}	