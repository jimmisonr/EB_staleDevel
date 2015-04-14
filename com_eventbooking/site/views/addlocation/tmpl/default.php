<?php
/**
 * @version        	1.7.2
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die ;
?>
<script type="text/javascript">
	function checkData(pressbutton) {
		var form = document.adminForm;
		if (pressbutton == 'cancel_location') {
			form.task.value = pressbutton;
			form.submit();			
			return;				
		} else {			
			if (form.name.value == '') {
				alert("<?php echo JText::_('EN_ENTER_LOCATION_NAME'); ?>");
				form.name.focus();
				return ;
			}															
			if (form.address.value == '') {
				alert("<?php echo JText::_('EN_ENTER_LOCATION_ADDRESS'); ?>");
				form.address.focus();
				return ;
			}
			if (form.city.value == '') {
				alert("<?php echo JText::_('EN_ENTER_LOCATION_CITY'); ?>");
				form.city.focus();
				return ;
			}
			if (form.zip.value == '') {
				alert("<?php echo JText::_('EN_ENTER_LOCATION_ZIP'); ?>");
				form.zip.focus();
				return ;
			}
			form.task.value = pressbutton;
			form.submit();									
		}
	}

	function deleteLocation() {
		if (confirm("<?php echo JText::_("EB_DELETE_LOCATION_CONFIRM"); ?>")) {
			var form = document.adminForm ;
			form.task.value = 'delete_location';
			form.submit();
		}		
	}
</script>
<div class="eb_form_header" style="width:100%;">
	<div style="float: left; width: 50%;"><?php echo JText::_('EB_ADD_EDIT_LOCATION'); ?></div>
	<div style="float: left; width: 50%; text-align: left;">
		<input type="button" class="btn btn-primary" name="btnSave" value="<?php echo JText::_('EB_SAVE'); ?>" onclick="checkData('save_location');" />
		<?php 
			if ($this->item->id) {
			?>
				<input type="button" class="btn btn-primary" name="btnSave" value="<?php echo JText::_('EB_DELETE_LOCATION'); ?>" onclick="deleteLocation();" />				
			<?php	
			}
		?>
		<input type="button" class="btn btn-primary" name="btnCancel" value="<?php echo JText::_('EB_CANCEL_LOCATION'); ?>" onclick="checkData('cancel_location');" />
	</div>	
</div>
<div class="clearfix"></div>
<form action="index.php" method="post" name="adminForm" id="adminForm">			
	<table class="admintable clearfix" width="100%">
		<tr>
			<td> 
				<?php echo JText::_('EB_NAME'); ?>
				<span class="required">(*)</span>
			</td>
			<td>
				<input class="text_area" type="text" name="name" id="name" size="50" maxlength="250" value="<?php echo $this->item->name;?>" />
			</td>
		</tr>			
		<tr>
			<td> 
				<?php echo JText::_('EB_ADDRESS'); ?>
				<span class="required">(*)</span>
			</td>
			<td>
				<input class="text_area input-xlarge" type="text" name="address" id="address" size="70" maxlength="250" value="<?php echo $this->item->address;?>" />
			</td>
		</tr>		
		<tr>
			<td> 
				<?php echo JText::_('EB_CITY'); ?>
				<span class="required">(*)</span>
			</td>
			<td>
				<input class="text_area" type="text" name="city" id="city" size="30" maxlength="250" value="<?php echo $this->item->city;?>" />
			</td>
		</tr>
		<tr>
			<td> 
				<?php echo JText::_('EB_STATE'); ?>
				<span class="required">(*)</span>
			</td>
			<td>
				<input class="text_area" type="text" name="state" id="state" size="30" maxlength="250" value="<?php echo $this->item->state;?>" />
			</td>
		</tr>
		<tr>
			<td> 
				<?php echo JText::_('EB_ZIP'); ?>
				<span class="required">(*)</span>
			</td>
			<td>
				<input class="text_area" type="text" name="zip" id="zip" size="20" maxlength="250" value="<?php echo $this->item->zip;?>" />
			</td>
		</tr>		
		<tr>
			<td> 
				<?php echo JText::_('EB_COUNTRY'); ?>
				<span class="required">(*)</span>
			</td>
			<td>
				<?php echo $this->lists['country'] ; ?>
			</td>
		</tr>		
		<tr>
			<td> 
				<?php echo JText::_('EB_LATITUDE'); ?>
			</td>
			<td>
				<input class="text_area" type="text" name="lat" id="lat" size="20" maxlength="250" value="<?php echo $this->item->lat;?>" />
			</td>
		</tr>
		<tr>
			<td> 
				<?php echo JText::_('EB_LONGITUDE'); ?>
			</td>
			<td>
				<input class="text_area" type="text" name="long" id="long" size="20" maxlength="250" value="<?php echo $this->item->long;?>" />
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_('EB_PUBLISHED') ; ?>
			</td>
			<td>
				<?php echo $this->lists['published']; ?>
			</td>	
		</tr>
	</table>			
	<div class="clr"></div>
	<input type="hidden" name="option" value="com_eventbooking" />
	<input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<input type="hidden" name="task" value="" />	
	<?php echo JHtml::_( 'form.token' ); ?>
	
</form>