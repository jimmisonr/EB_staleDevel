<?php
/**
 * @version        	1.6.9
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die ;
EventbookingHelperJquery::validateForm();
?>
<div id="eb-invite-friend-page" class="eb-container row-fluid">
<h1 class="eb-page-heading"><?php echo JText::_('EB_REGISTRATION_INVITE'); ?></h1>
<div class="eb-message">
	<?php echo str_replace('[EVENT_TITLE]', $this->event->title, $this->inviteMessage) ; ?>
</div>
<div class="clearfix"></div>
<form name="adminForm" id="adminForm" method="post" action="index.php?tmpl=component" class="form form-horizontal">	    			
	<div class="control-group">
		<label class="control-label">
			<?php echo JText::_('EB_NAME'); ?>
		</label>
		<div class="controls">
			<input type="text" name="name" value="<?php echo JRequest::getVar('name') ? JRequest::getVar('name') : $this->user->get('name'); ?>" class="validate[required] inputbox" size="50" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">
			<?php echo JText::_('EB_FRIEND_NAMES'); ?>
			<br />
			<small><?php echo JText::_('EB_ONE_NAME_ONE_LINE'); ?></small>
		</label>
		<div class="controls">
			<textarea rows="5" cols="50" name="friend_names" class="validate[required] inputbox"><?php echo JRequest::getVar('friend_names'); ?></textarea>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">
			<?php echo JText::_('EB_FRIEND_EMAILS'); ?>
			<br />
			<small><?php echo JText::_('EB_ONE_NAME_ONE_LINE'); ?></small>
		</label>
		<div class="controls">
			<textarea rows="5" cols="50" name="friend_emails" class="validate[required] inputbox"><?php echo JRequest::getVar('friend_emails');?></textarea>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">
			<?php echo JText::_('EB_MESSAGE'); ?>				
		</label>
		<div class="controls">
			<textarea rows="10" cols="80" name="message" class="inputbox"><?php echo JRequest::getVar('message'); ?></textarea>
		</div>
	</div>
	<?php 
	if ($this->showCaptcha)
	{
	?>
		<div class="control-group">
			<label class="control-label">
				<?php echo JText::_('EB_CAPTCHA'); ?><span class="required">*</span>
			</label>
			<div class="controls">
				<?php echo $this->captcha; ?>					
			</div>
		</div>
	<?php
	}
	?>
	<div class="form-actions">		
		<input type="submit" value="<?php echo JText::_('EB_INVITE'); ?>" class="btn btn-primary" />			
	</div>
	<script type="text/javascript">
			Eb.jQuery(document).ready(function($){
				$("#adminForm").validationEngine('attach', { 
				    onValidationComplete: function(form, status){
				        if (status == true) {						        
				            form.on('submit', function(e) {
				                e.preventDefault();
				            });
				            return true;
				        }
				        return false;
				    }
				});
			})
	</script>
	<input type="hidden" name="option" value="com_eventbooking" />
	<input type="hidden" name="task" value="send_invite" />
	<input type="hidden" name="event_id" value="<?php echo $this->event->id; ?>" />
	<input type="hidden" name="Itemid" value="<?php echo JRequest::getInt('Itemid', 0); ?>" />
</form>
</div>	