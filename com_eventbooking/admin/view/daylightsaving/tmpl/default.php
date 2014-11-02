<?php
/**
 * @version        	1.6.6
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2014 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die ;
JHtml::_('behavior.framework');
?>
<script	type="text/javascript">
	Joomla.submitbutton = function(pressbutton) {
		var form = document.adminForm;
		if (form.start_date.value == '') {
			alert("Please choose Daily saving time start date");
			form.start_date.focus();
			return ;			
		}		
		if (form.end_date.value == '') {
			alert("Please choose Daily saving time end date");
			form.end_date.focus();
			return ;			
		}
				
		Joomla.submitform( pressbutton );		
	}
</script>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="row-fluid">			
	<table class="admintable adminform">
		<tr>
			<td colspan="2">
				<p class="text-warning">
					This function is used to fix Day Light saving time issue when you create recurring events. Please be carefully when use this feature. Only use it if you are having issue as mentioned in the forum post below
					http://www.joomdonation.com/62-general-discussion/14100-recurring-event-changes-the-time-on-day-11-onwards.html
				</p>
			</td>
		</tr>
		<tr>
			<td width="100" class="key">
				<?php echo  JText::_('EB_START_DATE'); ?>
			</td>
			<td>
				<?php echo JHtml::_('calendar', '', 'start_date', 'start_date') ; ?>
			</td>
		</tr>
		<tr>
			<td width="100" class="key">
				<?php echo  JText::_('EB_END_DATE'); ?>
			</td>
			<td>
				<?php echo JHtml::_('calendar', '', 'end_date', 'end_date') ; ?>
			</td>
		</tr>											
		<tr>
			<td colspan="2">
				<input type="button" class="btn btn-primary" value="<?php echo JText::_('EB_PROCESS'); ?>" onclick="Joomla.submitbutton('fix_daylight_saving_time');" />
			</td>
		</tr>			
	</table>							
</div>		
<div class="clearfix"></div>	
	<?php echo JHTML::_( 'form.token' ); ?>	
	<input type="hidden" name="option" value="com_eventbooking" />	
	<input type="hidden" name="task" value="" />
</form>