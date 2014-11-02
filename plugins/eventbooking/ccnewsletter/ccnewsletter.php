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

class plgEventBookingCCNewsletter extends JPlugin
{	
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);		
	}
	
	public function onAfterStoreRegistrant($row) {
        if (!file_exists(JPATH_ADMINISTRATOR.'/components/com_ccnewsletter/ccnewsletter.php'))
        {
            return;
        }
		$db = JFactory::getDBO() ;
		$sql = "SELECT COUNT(*) FROM #__ccnewsletter_subscribers WHERE email='$row->email'";
		$db->setQuery($sql) ;
		$total = $db->loadResult();
		if (!$total) {
			$name = $row->first_name . ' ' . $row->last_name ;
			$sql = "INSERT INTO #__ccnewsletter_subscribers(name, email, sdate)
					VALUES('$name', '$row->email', NOW())	
					";
			$db->setQuery($sql) ;			
			$db->query();			
		}
	} 	
}	