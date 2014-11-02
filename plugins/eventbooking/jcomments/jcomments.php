<?php
/**
 * @version		1.5.1
 * @package		Joomla
 * @subpackage	OS Membership Plugins
 * @author  Tuan Pham Ngoc
 * @copyright	Copyright (C) 2010 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;

class plgEventBookingJcomments extends JPlugin
{	
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_eventbooking/tables');
	}
	/**
	 * Render setting form
	 * @param PlanOSMembership $row
	 */
	function onEventDisplay($row) {	
		ob_start();
		$this->_drawSettingForm($row);		
		$form = ob_get_contents();	
		ob_end_clean();	

		return array('title' => JText::_('Comment'),							
					'form' => $form
		) ;				
	}

	/**
	 * Display form allows users to change settings on subscription plan add/edit screen 
	 * @param object $row
	 */	
	function _drawSettingForm($row) {
		$comments = JPATH_ROOT.'/components/com_jcomments/jcomments.php';
	    if (file_exists($comments))
	    {
	     require_once($comments);
	     echo '<div style="clear:both; padding-top: 10px;"></div>';
	     echo JComments::showComments($row->id, 'com_eventbooking', $row->title);
	    } 
	}
}	