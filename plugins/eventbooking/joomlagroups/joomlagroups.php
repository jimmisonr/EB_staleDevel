<?php
/**
 * @version		1.0.0
 * @package		Joomla
 * @subpackage	Event Booking
 * @author  Tuan Pham Ngoc
 * @copyright	Copyright (C) 2010 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;

class plgEventbookingJoomlagroups extends JPlugin
{	
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		JFactory::getLanguage()->load('plg_eventbooking_joomlagroups', JPATH_ADMINISTRATOR);			
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_eventbooking/tables');
	}
	/**
	 * Render settings from
	 * @param Event $row
	 */
	function onEditEvent($row) {	
		ob_start();
			$this->_drawSettingForm($row);		
			$form = ob_get_contents();	
		ob_end_clean();		
		return array('title' => JText::_('PLG_EVENTBOOKING_JOOMLA_GROUPS_SETTINGS'),							
					'form' => $form
		) ;				
	}

	/**
	 * Store setting into database
	 * @param Event $row
	 * @param Boolean $isNew true if create new plan, false if edit
	 */
	function onAfterSaveEvent($row, $data,$isNew) {
		$params = new JRegistry($row->params);		
		$params->set('joomla_group_ids'			, implode(',',$data['joomla_group_ids']));
		$row->params = $params->toString();
		
		$row->store();
	}
	/**
	 * Run when a membership activated
	 * @param PlanOsMembership $row
	 */		
	function onAfterPaymentSuccess($row) {		
		if ($row->user_id) {
			$user = JFactory::getUser($row->user_id);
			$currentGroups  = $user->get('groups') ;			
			$event = JTable::getInstance('EventBooking','Event');
			$event->load($row->event_id);
			$params = new JRegistry($event->params);
			$groups = explode(',', $params->get('joomla_group_ids'));
			$currentGroups = array_unique(array_merge($currentGroups, $groups)) ;
			$user->set('groups', $currentGroups);
			$user->save(true);					
		}						
	}
	/**
	 * Display form allows users to change setting for this subscription plan 
	 * @param object $row
	 * 
	 */	
	function _drawSettingForm($row) {
		// $row of table osmembership_plans
		$params = new JRegistry($row->params);		
		$joomla_group_ids 			= explode(',',$params->get('joomla_group_ids', ''));
		$joomla_expried_group_ids 	= explode(',',$params->get('joomla_expried_group_ids', ''));		
	?>	
		<table class="admintable adminform" style="width: 90%;">
				<tr>
					<td width="220" class="key">
						<?php echo  JText::_('PLG_EVENTBOOKING_JOOMLA_ASSIGN_TO_JOOMLA_GROUPS'); ?>
					</td>
					<td>
						<?php
							if (version_compare(JVERSION, '1.6.0', 'ge')) {
								echo JHtml::_('access.usergroup', 'joomla_group_ids[]', $joomla_group_ids,  ' multiple="multiple" size="6" ', false) ;
							} else {
								
							}
						?>
					</td>
					<td>
						<?php echo JText::_('PLG_EVENTBOOKING_JOOMLA_ASSIGN_TO_JOOMLA_GROUPS_EXPLAIN'); ?>
					</td>
				</tr>
		</table>	
	<?php							
	}
}	