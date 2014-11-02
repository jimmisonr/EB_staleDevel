<?php
/**
 * @version		1.5.0
 * @package		Joomla
 * @subpackage	Event Booking
 * @author  Tuan Pham Ngoc
 * @copyright	Copyright (C) 2010 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
class plgEventBookingAcajoom extends JPlugin
{	
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}
	
	public function onAfterStoreRegistrant($row) {
        if (!file_exists(JPATH_ADMINISTRATOR.'/components/com_acajoom/acajoom.php'))
        {
            return;
        }
		$db = JFactory::getDBO() ;		
		$sql = "SELECT COUNT(*) FROM #__acajoom_subscribers WHERE email='$row->email'";
		$db->setQuery($sql) ;
		$total = $db->loadResult();
		if (!$total) {
			$user = JFactory::getUser();
			$userId = $user->get('id');
			$name = $row->first_name . ' ' . $row->last_name ;
			$lang = JRequest::getVar('lang', 'eng');		
			$sql = "INSERT INTO #__acajoom_subscribers(user_id, name, email, confirmed, language_iso, subscribe_date)
					VALUES($userId, '$name', '$row->email', 1, '$lang' , NOW())
			
			";
			$db->setQuery($sql) ;			
			$db->query();
			$subcrId = $db->insertId();					
			$sql = "SELECT id FROM #__acajoom_lists WHERE published = 1 ";
			$db->setQuery($sql) ;
			$rows = $db->loadObjectList();
			for ($i = 0 , $n = count($rows) ; $i < $n ; $i++) {
				$row = $rows[$i] ;
				$listId = $row->id ;
				$sql = "INSERT INTO #__acajoom_queue(`type`, subscriber_id, list_id, mailing_id, issue_nb, send_date, suspend, delay , acc_level, published)
								VALUES(1, $subcrId, $listId, 0, 0, '0000-00-00 00:00:00', 0, 0, 29, 1)
						";
				$db->setQuery($sql) ;
				$db->query();
			}					
		}	
	} 	
}	