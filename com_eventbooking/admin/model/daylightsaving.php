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
class EventbookingModelDaylightsaving extends RADModel
{

	function process($data)
	{
		$db = $this->getDbo();
		$nullDate = $db->getNullDate();
		$startDate = $data['start_date'];
		$endDate = $data['end_date'];
		//Fix event start date		
		$sql = "UPDATE `#__eb_events` SET event_date = event_date + INTERVAL 1 HOUR WHERE parent_id > 0 AND fixed_daylight_saving_time = 0 AND DATE(event_date) >='$startDate' AND DATE(event_date) <= '$endDate'";
		$db->setQuery($sql);
		$db->execute();
		//Fix event end date
		$sql = "UPDATE `#__eb_events` SET event_end_date = event_end_date + INTERVAL 1 HOUR WHERE event_end_date !='$nullDate' AND parent_id > 0 AND fixed_daylight_saving_time=0 AND DATE(event_date) >='$startDate' AND DATE(event_date) <= '$endDate'";
		$db->setQuery($sql);
		$db->execute();

		$sql = "UPDATE `#__eb_events` SET fixed_daylight_saving_time = 1 WHERE parent_id > 0 AND fixed_daylight_saving_time=0 AND DATE(event_date) >='$startDate' AND DATE(event_date) <= '$endDate'";
		$db->setQuery($sql);
		$db->execute();
	}
}