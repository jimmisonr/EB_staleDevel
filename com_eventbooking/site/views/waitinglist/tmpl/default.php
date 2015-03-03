<?php
/**
 * @version        	1.6.10
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die ;

$headerText = JText::_('EB_JOIN_WAITINGLIST') ;
$headerText = str_replace('[EVENT_TITLE]', $this->event->title, $headerText) ;
?>
<h1 class="eb_title"><?php echo $headerText; ?></h1>
<form method="post" name="adminForm" id="adminForm" action="<?php echo JRoute::_('index.php?option=com_eventbooking&Itemid='.$this->Itemid); ?>" autocomplete="off">
<?php

if (strlen(strip_tags($this->message->{'waitinglist_form_message'.$this->fieldSuffix})))
{
    $msg = $this->message->{'waitinglist_form_message'.$this->fieldSuffix};
}
else
{
    $msg = $this->message->waitinglist_form_message;
}
if (strlen($msg))
{
	$msg = str_replace('[EVENT_TITLE]', $this->event->title, $msg) ;						
?>								
	<div class="msg"><?php echo $msg ; ?></div>							 															
<?php	
}
?>		
<table width="100%" class="os_table" cellspacing="3" cellpadding="3">										
	<tr>			
		<td class="title_cell" width="30%">
			<?php echo  JText::_('EB_FIRST_NAME') ?><span class="required">*</span>
		</td>
		<td class="field_cell">
			<input type="text" class="inputbox" name="first_name" value="<?php echo $this->firstName; ?>" size="25" />
		</td>
	</tr>	
	<?php		
	if ($this->config->swt_lastname) {
	?>
		<tr>			
			<td class="title_cell">
				<?php echo  JText::_('EB_LAST_NAME') ?><?php if ($this->config->rwt_lastname) echo '<span class="required">*</span>'; ?>
			</td>
			<td class="field_cell">
				<input type="text" class="inputbox" name="last_name" value="<?php echo $this->lastName; ?>" size="25" />
			</td>
		</tr>
	<?php	
	}
	if ($this->event->registration_type!= 1 && $this->config->prevent_duplicate_registration !== '1')
	{
		$hideNumberRegistrants = true;
	?>
		<tr>
			<td class="title_cell">
				<?php echo  JText::_('EB_NUMBER_REGISTRANTS') ?><?php echo '<span class="required">*</span>'; ?>
			</td>
			<td class="field_cell">
				<input type="text" class="inputbox" name="number_registrants" value="" size="5" />
			</td>
		</tr>
	<?php
	}				
	else 
	{
		$hideNumberRegistrants = false;
	}
	if ($this->config->swt_organization) {
	?>
		<tr>			
			<td class="title_cell">
				<?php echo  JText::_('EB_ORGANIZATION'); ?><?php if ($this->config->rwt_organization) echo '<span class="required">*</span>'; ?>
			</td>
			<td class="field_cell">
				<input type="text" class="inputbox" name="organization" value="<?php echo $this->organization; ?>" size="30" />
			</td>
		</tr>
	<?php	
	}
	if ($this->config->swt_address) {
	?>
		<tr>			
			<td class="title_cell">
				<?php echo  JText::_('EB_ADDRESS'); ?><?php if ($this->config->rwt_address) echo '<span class="required">*</span>'; ?>
			</td>
			<td class="field_cell">
				<input type="text" class="inputbox" name="address" value="<?php echo $this->address; ?>" size="50" />
			</td>
		</tr>	
	<?php	
	}
	
	if ($this->config->swt_address2) {
	?>
		<tr>			
			<td class="title_cell">
				<?php echo  JText::_('EB_ADDRESS2'); ?><?php if ($this->config->rwt_address2) echo '<span class="required">*</span>'; ?>
			</td>
			<td class="field_cell">
				<input type="text" class="inputbox" name="address2" value="<?php echo $this->address2; ?>" size="50" />
			</td>
		</tr>	
	<?php	
	}			
	if ($this->config->swt_city) {
	?>
		<tr>			
			<td class="title_cell">
				<?php echo  JText::_('EB_CITY'); ?><?php if ($this->config->rwt_city) echo '<span class="required">*</span>'; ?>
			</td>
			<td class="field_cell">
				<input type="text" class="inputbox" name="city" value="<?php echo $this->city; ?>" size="15" />
			</td>
		</tr>		
	<?php	
	}
	if ($this->config->swt_country) {
	?>
		<tr>			
			<td class="title_cell">
				<?php echo  JText::_('EB_COUNTRY'); ?><?php if ($this->config->rwt_country) echo '<span class="required">*</span>'; ?>
			</td>
			<td class="field_cell">
				<?php echo $this->lists['country_list']; ?>
			</td>
		</tr>	
	<?php	
	}					
	if ($this->config->swt_state) {
		if ($this->config->display_state_dropdown) {
		?>
			<tr>			
				<td class="title_cell">
					<?php echo  JText::_('EB_STATE'); ?><?php if ($this->config->rwt_state) echo '<span class="required">*</span>'; ?>
				</td>
				<td class="field_cell">
					<?php echo $this->lists['state'] ; ?>
				</td>
			</tr>		
		<?php	
		} else {
		?>
			<tr>			
				<td class="title_cell">
					<?php echo  JText::_('EB_STATE'); ?><?php if ($this->config->rwt_state) echo '<span class="required">*</span>'; ?>
				</td>
				<td class="field_cell">
					<input type="text" class="inputbox" name="state" value="<?php echo $this->state; ?>" size="15" />
				</td>
			</tr>	
		<?php	
		}			
	}
	if ($this->config->swt_zip) {
	?>
		<tr>			
			<td class="title_cell">
				<?php echo  JText::_('EB_ZIP'); ?><?php if ($this->config->rwt_zip) echo '<span class="required">*</span>'; ?>
			</td>
			<td class="field_cell">
				<input type="text" class="inputbox" name="zip" value="<?php echo $this->zip; ?>" size="15" />
			</td>
		</tr>
	<?php	
	}								
	if ($this->config->swt_phone) {
	?>
		<tr>			
			<td class="title_cell">
				<?php echo  JText::_('EB_PHONE'); ?><?php if ($this->config->rwt_phone) echo '<span class="required">*</span>'; ?>
			</td>
			<td class="field_cell">
				<input type="text" class="inputbox" name="phone" value="<?php echo $this->phone; ?>" size="15" />
			</td>
		</tr>
	<?php
	}
	if ($this->config->swt_fax) {
	?>
		<tr>			
			<td class="title_cell">
				<?php echo  JText::_('EB_FAX'); ?><?php if ($this->config->rwt_fax) echo '<span class="required">*</span>'; ?>
			</td>
			<td class="field_cell">
				<input type="text" class="inputbox" name="fax" value="<?php echo $this->fax; ?>" size="15" />
			</td>
		</tr>
	<?php
	}				
 ?>																		
<tr>			
	<td class="title_cell">
		<?php echo  JText::_('EB_EMAIL'); ?><span class="required">*</span>
	</td>
	<td class="field_cell">
		<input type="text" class="inputbox" name="email" value="<?php echo $this->email; ?>" size="40" />				
	</td>
</tr>				
<?php		
	if ($this->config->swt_comment) {
	?>
		<tr>			
			<td class="title_cell">
				<?php echo  JText::_('EB_COMMENT'); ?><?php if ($this->config->rwt_comment) echo '<span class="required">*</span>'; ?>
			</td>
			<td class="field_cell">
				<textarea rows="7" cols="50" name="comment" class="inputbox"><?php echo $this->comment;?></textarea>
			</td>
		</tr>	
	<?php	
	}		
	if ($this->showCaptcha)
	{
	?>
		<tr>			
			<td class="title_cell">
				<?php echo JText::_('EB_CAPTCHA'); ?><span class="required">*</span>
			</td>
			<td class="field_cell">
				<?php echo $this->captcha; ?>
			</td>
		</tr>	
	<?php	
	}				
	?>									
	<tr>
		<td colspan="2" align="left">
			<input type="button" class="btn btn-primary" name="btnBack" value="<?php echo  JText::_('EB_BACK') ;?>" onclick="window.history.go(-1);">
			<input type="button" class="btn btn-primary" name="btnSubmit" value="<?php echo  JText::_('EB_REGISTRATION_CONFIRMATION') ;?>" onclick="checkData();">				
		</td>
	</tr>										
</table>						
<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
<input type="hidden" name="event_id" value="<?php echo $this->event->id ; ?>" />
<input type="hidden" name="option" value="com_eventbooking" />	
<input type="hidden" name="task" value="save_waitinglist" />			
<script type="text/javascript">
	<?php			
		if ($this->config->display_state_dropdown) {
			echo $this->countryIdsString ;
			echo $this->countryNamesString ;
			echo $this->stateString ;
		} 		 
	?>
	function checkData() {
		var form = document.adminForm ;					
		if (form.first_name.value == '') {
			alert("<?php echo JText::_('EB_REQUIRE_FIRST_NAME'); ?>");
			form.first_name.focus();
			return ;
		}						
		<?php
			if ($this->config->swt_lastname && $this->config->rwt_lastname) {
			?>
				if (form.last_name.value=="") {
					alert("<?php echo JText::_('EB_REQUIRE_LAST_NAME'); ?>");
					form.last_name.focus();
					return;
				}						
			<?php		
			}
			if ($this->config->swt_organization && $this->config->rwt_organization) {
			?>
				if (form.organization.value=="") {
					alert("<?php echo JText::_('EB_REQUIRE_ORGANIZATION'); ?>");
					form.organization.focus();
					return;
				}						
			<?php		
			}
			if ($this->config->swt_address && $this->config->rwt_address) {
			?>
				if (form.address.value=="") {
					alert("<?php echo JText::_('EB_REQUIRE_ADDRESS'); ?>");
					form.address.focus();
					return;	
				}						
			<?php		
			}
			if ($this->config->swt_city && $this->config->rwt_city) {
			?>
				if (form.city.value == "") {
					alert("<?php echo JText::_('EB_REQUIRE_CITY'); ?>");
					form.city.focus();
					return;	
				}						
			<?php		
			}
			if ($this->config->swt_country && $this->config->rwt_country) {
			?>
				if (form.country.value == "") {
					alert("<?php echo JText::_('EB_REQUIRE_COUNTRY'); ?>");
					form.country.focus();
					return;	
				}				
			<?php		
			}			
			if ($this->config->swt_state && $this->config->rwt_state) {
				if ($this->config->display_state_dropdown) {
				?>
					if ((form.state.options.length > 1) && (form.state.value == '')) {
						alert("<?php echo JText::_('EB_REQUIRE_STATE'); ?>");
						form.state.focus();
						return;
					}
				<?php	
				} else {
				?>
					if (form.state.value =="") {
						alert("<?php echo JText::_('EB_REQUIRE_STATE'); ?>");
						form.state.focus();
						return;	
					}
				<?php	
				}							
			}					
			if ($this->config->swt_zip && $this->config->rwt_zip) {
			?>
				if (form.zip.value == "") {
					alert("<?php echo JText::_('EB_REQUIRE_ZIP'); ?>");
					form.zip.focus();
					return;
				}						
			<?php		
			}				
			if ($this->config->swt_phone && $this->config->rwt_phone) {
			?>
				if (form.phone.value == "") {
					alert("<?php echo JText::_('EB_REQUIRE_PHONE'); ?>");
					form.phone.focus();
					return;
				}						
			<?php		
			}																										
		?>				
		if (form.email.value == '') {
			alert("<?php echo JText::_('EB_REQUIRE_EMAIL'); ?>");
			form.email.focus();
			return;
		}							
		var emailFilter = /^\w+[\+\.\w-]*@([\w-]+\.)*\w+[\w-]*\.([a-z]{2,4}|\d+)$/i
		var ret = emailFilter.test(form.email.value);
		if (!ret) {
			alert("<?php echo  JText::_('EB_VALID_EMAIL'); ?>");
			form.email.focus();
			return;
		}																									
		<?php					
		if ($this->config->swt_comment && $this->config->rwt_comment) {
			?>
				if (form.comment.value == "") {
					alert("<?php echo JText::_('EB_REQUIRE_COMMENT'); ?>");
					form.comment.focus();
					return;
				}						
			<?php	
			}									 						
		?>													
		form.submit();
	}											
</script>	
<?php echo JHtml::_( 'form.token' ); ?>
<?php 
	if ($hideNumberRegistrants)
	{
	?>
		<input type="hidden" name="number_registrants" value="1" />
	<?php	
	}
?>
</form>