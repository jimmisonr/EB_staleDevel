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
JHtml::_('behavior.tooltip');
$editor = JFactory::getEditor() ;
$format = 'Y-m-d' ;
?>
<style>
	.calendar {
		vertical-align: bottom;
	}
</style>
<script type="text/javascript">
	function checkData(pressbutton) {
		var form = document.adminForm;
		if (pressbutton == 'cancel_event') {
			Joomla.submitform( pressbutton );
			return;				
		} else {
			//Should have some validations rule here
			//Check something here
			if (form.title.value == '') {
				alert("<?php echo JText::_('EB_PLEASE_ENTER_TITLE'); ?>");
				form.title.focus();
				return ;
			}				
			if (form.event_date.value == '') {
				alert("<?php echo JText::_('EB_ENTER_EVENT_DATE'); ?>");
				form.event_date.focus();
				return ;
			}
			//Check the event price
			if (form.main_category_id.value == 0) 
			{
				alert("<?php echo JText::_("EB_CHOOSE_CATEGORY");  ?>");
				return ;
			}
			//Check the price						
			Joomla.submitform( pressbutton );
		}
	}
</script>
<div class="eb_form_header" style="width:100%;">
	<div style="float: left; width: 40%;"><?php echo JText::_('EB_ADD_EDIT_EVENT'); ?></div>
	<div style="float: right; width: 50%; text-align: right;">
		<input type="button" name="btnSave" value="<?php echo JText::_('EB_SAVE'); ?>" onclick="checkData('save_event');" class="btn btn-primary" />
		<input type="button" name="btnSave" value="<?php echo JText::_('EB_CANCEL_EVENT'); ?>" onclick="checkData('cancel_event');" class="btn btn-primary" />
	</div>	
</div>
<div class="clearfix"></div>
<form action="index.php" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form form-horizontal">
<div class="row-fluid">
	<ul class="nav nav-tabs">
		<li class="active"><a href="#basic-information-page" data-toggle="tab"><?php echo JText::_('EB_BASIC_INFORMATION');?></a></li>
		<li><a href="#group-registration-rates-page" data-toggle="tab"><?php echo JText::_('EB_GROUP_REGISTRATION_RATES');?></a></li>
		<li><a href="#misc-page" data-toggle="tab"><?php echo JText::_('EB_MISC');?></a></li>
		<li><a href="#discount-page" data-toggle="tab"><?php echo JText::_('EB_DISCOUNT_SETTING');?></a></li>					
		<?php 
			if ($this->config->event_custom_field) {
			?>
				<li><a href="#extra-information-page" data-toggle="tab"><?php echo JText::_('EB_EXTRA_INFORMATION');?></a></li>
			<?php	
			}
		?>			
	</ul>
	<div class="tab-content">			
		<div class="tab-pane active" id="basic-information-page">			
			<table class="admintable" width="100%">
				<tr>
					<td class="key" width="30%"><?php echo JText::_('EB_TITLE') ; ?></td>
					<td>
						<input type="text" name="title" value="<?php echo $this->item->title; ?>" class="input-xlarge" size="70" />
					</td>
				</tr>	
				<tr>
					<td class="key" width="30%"><?php echo JText::_('EB_ALIAS') ; ?></td>
					<td>
						<input type="text" name="alias" value="<?php echo $this->item->alias; ?>" class="input-xlarge" size="70" />
					</td>
				</tr>					
				<tr>
					<td class="key" valign="top"><?php echo JText::_('EB_MAIN_EVENT_CATEGORY') ; ?></td>
					<td>
						<div style="float: left;"><?php echo $this->lists['main_category_id'] ; ?></div>								
					</td>
				</tr>
				<tr>
					<td class="key" valign="top"><?php echo JText::_('EB_ADDITIONAL_CATEGORIES') ; ?></td>
					<td>
						<div style="float: left;"><?php echo $this->lists['category_id'] ; ?></div>
						<div style="float: left; padding-top: 25px; padding-left: 10px;">Press <strong>Ctrl</strong> to select multiple categories</div>
					</td>
				</tr>				
                <tr>
                    <td class="key"><?php echo JText::_('EB_THUMB_IMAGE') ; ?></td>
                    <td>
                        <input type="file" class="inputbox" name="thumb" size="60" />
                        <?php
                        if ($this->item->thumb) 
						{
                        ?>
                            <a href="<?php echo JURI::root().'media/com_eventbooking/images/'.$this->item->thumb; ?>" class="modal"><img src="<?php echo JURI::root().'media/com_eventbooking/images/thumbs/'.$this->item->thumb; ?>" class="img_preview" /></a>
                            <input type="checkbox" name="del_thumb" value="1" /><?php echo JText::_('EB_DELETE_CURRENT_THUMB'); ?>
                        <?php
                        }
                        ?>
                    </td>
                </tr>
                <tr>
					<td class="key"><?php echo JText::_('EB_LOCATION') ; ?></td>
					<td>
						<?php echo $this->lists['location_id'] ; ?>
					</td>
				</tr>					
				<tr>
					<td class="key">
						<?php echo JText::_('EB_EVENT_START_DATE'); ?>
					</td>				
					<td>					
						<?php echo JHtml::_('calendar', ($this->item->event_date == $this->nullDate) ? '' : JHtml::_('date', $this->item->event_date, $format, null), 'event_date', 'event_date') ; ?>
						<?php echo $this->lists['event_date_hour'].' '.$this->lists['event_date_minute']; ?>					
					</td>
				</tr>		
				<tr>
					<td class="key">
						<?php echo JText::_('EB_EVENT_END_DATE'); ?>
					</td>				
					<td>					
						<?php echo JHtml::_('calendar', ($this->item->event_end_date == $this->nullDate) ? '' : JHtml::_('date', $this->item->event_end_date, $format, null), 'event_end_date', 'event_end_date') ; ?>
						<?php echo $this->lists['event_end_date_hour'].' '.$this->lists['event_end_date_minute'] ; ?>					
					</td>
				</tr>				
				<tr>
					<td class="key">
						<?php echo JText::_('EB_PRICE'); ?>
					</td>				
					<td>
						<input type="text" name="individual_price" id="individual_price" class="input-mini" size="10" value="<?php echo $this->item->individual_price; ?>" />					
					</td>
				</tr>
				<tr>
					<td class="key">
						<span class="editlinktip hasTip" title="<?php echo JText::_( 'EB_EVENT_CAPACITY' );?>::<?php echo JText::_('EB_CAPACITY_EXPLAIN'); ?>"><?php echo JText::_('EB_CAPACITY'); ?></span>
					</td>
					<td>
						<input type="text" name="event_capacity" id="event_capacity" class="input-mini" size="10" value="<?php echo $this->item->event_capacity; ?>" />
					</td>
				</tr>
				<tr>
					<td class="key"><?php echo JText::_('EB_REGISTRATION_TYPE'); ?></td>
					<td>
						<?php echo $this->lists['registration_type'] ; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<span class="editlinktip hasTip" title="<?php echo JText::_( 'EB_CUT_OFF_DATE' );?>::<?php echo JText::_('EB_CUT_OFF_DATE_EXPLAIN'); ?>"><?php echo JText::_('EB_CUT_OFF_DATE') ; ?></span>
					</td>
					<td>
						<?php echo JHtml::_('calendar', ($this->item->cut_off_date == $this->nullDate) ? '' : JHtml::_('date', $this->item->cut_off_date, $format, null), 'cut_off_date', 'cut_off_date') ; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<span class="editlinktip hasTip" title="<?php echo JText::_( 'EB_MAX_NUMBER_REGISTRANTS' );?>::<?php echo JText::_('EB_MAX_NUMBER_REGISTRANTS_EXPLAIN'); ?>"><?php echo JText::_('EB_MAX_NUMBER_REGISTRANTS'); ?></span>
					</td>
					<td>
						<input type="text" name="max_group_number" id="max_group_number" class="input-mini" size="10" value="<?php echo $this->item->max_group_number; ?>" />
					</td>
				</tr>				
				<tr>
					<td width="30%" class="key">
						<?php echo JText::_('EB_PAYPAL_EMAIL'); ?>
					</td>				
					<td width="50%">
						<input type="text" name="paypal_email" class="inputbox" size="50" value="<?php echo $this->item->paypal_email ; ?>" />
					</td>					
				</tr>									
				<tr>
					<td class="key">
						<?php echo JText::_('EB_PUBLISHED'); ?>
					</td>
					<td>
						<?php echo $this->lists['published']; ?>
					</td>
				</tr>															
				<tr>
					<td class="key">
						<?php echo  JText::_('EB_SHORT_DESCRIPTION'); ?>
					</td>
					<td>
						<?php echo $editor->display( 'short_description',  $this->item->short_description , '100%', '180', '90', '6' ) ; ?>					
					</td>
				</tr>					
				<tr>
					<td class="key">
						<?php echo  JText::_('EB_DESCRIPTION'); ?>
					</td>
					<td>
						<?php echo $editor->display( 'description',  $this->item->description , '100%', '250', '90', '10' ) ; ?>					
					</td>
				</tr>																						
			</table>			
		</div>
		<div class="tab-pane" id="group-registration-rates-page">
			<table  id="price_list" width="100%">
				<tr>
					<th width="20%">
						<?php echo JText::_('EB_REGISTRANT_NUMBER'); ?>
					</th>				
					<th>
						<?php echo JText::_('EB_RATE'); ?>
					</th>
				</tr>
				<?php
					$n = max(count($this->prices), 3);
					for ($i = 0 ; $i < $n ; $i++) {
							if (isset($this->prices[$i])) {
								$price = $this->prices[$i] ;
								$registrantNumber = $price->registrant_number ;
								$price = $price->price ;
							} else {
								$registrantNumber =  null ;
								$price =  null ;
							}
					?>
						<tr>
							<td>
								<input type="text" class="input-small" name="registrant_number[]" size="10" value="<?php echo $registrantNumber; ?>" />
							</td>						
							<td>
								<input type="text" class="input-small" name="price[]" size="10" value="<?php echo $price; ?>" />
							</td>
						</tr>
					<?php				 									
					}
				?>
				<tr>
					<td colspan="3">
						<input type="button" class="btn button" value="<?php echo JText::_('EB_ADD'); ?>" onclick="addRow();" />
						&nbsp;
						<input type="button" class="btn button" value="<?php echo JText::_('EB_REMOVE'); ?>" onclick="removeRow();" />
					</td>
				</tr>
			</table>			
		</div>
		<div class="tab-pane" id="misc-page">
			<table class="admintable" width="100%">
				<tr>
					<td width="30%" class="key">
						<?php echo JText::_('EB_NOTIFICATION_EMAILS'); ?>
					</td>				
					<td>
						<input type="text" name="notification_emails" class="inputbox" size="70" value="<?php echo $this->item->notification_emails ; ?>" />
					</td>					
				</tr>
				<tr>
					<td class="key">
						<span class="editlinktip hasTip" title="<?php echo JText::_( 'EB_ACCESS' );?>::<?php echo JText::_('EB_ACCESS_EXPLAIN'); ?>"><?php echo JText::_('EB_ACCESS'); ?></span>
					</td>
					<td>
						<?php echo $this->lists['access']; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<span class="editlinktip hasTip" title="<?php echo JText::_( 'EB_REGISTRATION_ACCESS' );?>::<?php echo JText::_('EB_REGISTRATION_ACCESS_EXPLAIN'); ?>"><?php echo JText::_('EB_REGISTRATION_ACCESS'); ?></span>
					</td>
					<td>
						<?php echo $this->lists['registration_access']; ?>
					</td>
				</tr>
				<tr>
					<td class="key" style="width: 160px;">
						<?php echo JText::_('EB_ENABLE_CANCEL'); ?>
					</td>
					<td>
						<?php echo $this->lists['enable_cancel_registration'] ; ?>
					</td>
				</tr>		
				<tr>
					<td class="key">
						<?php echo JText::_('EB_CANCEL_BEFORE_DATE'); ?>
					</td>
					<td>
						<?php echo JHtml::_('calendar', $this->item->cancel_before_date != $this->nullDate ? JHtml::_('date', $this->item->cancel_before_date, $format, null) : '', 'cancel_before_date', 'cancel_before_date'); ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('EB_AUTO_REMINDER'); ?>
					</td>
					<td>
						<?php echo $this->lists['enable_auto_reminder']; ?>
					</td>
				</tr>			
				<tr>
					<td class="key">
						<?php echo JText::_('EB_REMIND_BEFORE'); ?>
					</td>
					<td>
						<input type="text" name="remind_before_x_days" class="input-mini" size="5" value="<?php echo $this->item->remind_before_x_days; ?>" /> days
					</td>
				</tr>	
				<?php
					if ($this->config->term_condition_by_event) {					
					?>
						<tr>
							<td class="key">
								<?php echo JText::_('EB_TERMS_CONDITIONS'); ?>
							</td>
							<td>
								<?php echo $this->lists['article_id'] ; ?>
							</td>	
						</tr>
					<?php	
					}
				?>
                <tr>
                    <td width="100" class="key">
                        <?php echo  JText::_('EB_META_KEYWORDS'); ?>
                    </td>
                    <td>
                        <textarea rows="5" cols="30" class="input-lage" name="meta_keywords"><?php echo $this->item->meta_keywords; ?></textarea>
                    </td>
                </tr>
                <tr>
                    <td width="100" class="key">
                        <?php echo  JText::_('EB_META_DESCRIPTION'); ?>
                    </td>
                    <td>
                        <textarea rows="5" cols="30" class="input-lage" name="meta_description"><?php echo $this->item->meta_description; ?></textarea>
                    </td>
                </tr>
			</table>		
		</div>			
		<div class="tab-pane" id="discount-page">
			<table class="admintable" width="100%">
				<tr>
					<td class="key" width="30%">
						<span class="editlinktip hasTip" title="<?php echo JText::_( 'EB_MEMBER_DISCOUNT' );?>::<?php echo JText::_('EB_MEMBER_DISCOUNT_EXPLAIN'); ?>"><?php echo JText::_('EB_MEMBER_DISCOUNT'); ?></span>
					</td>
					<td>
						<input type="text" name="discount" id="discount" class="input-mini" size="5" value="<?php echo $this->item->discount; ?>" />&nbsp;&nbsp;<?php echo $this->lists['discount_type'] ; ?>
					</td>
				</tr>
				<tr>
					<td class="key" width="30%">
						<span class="editlinktip hasTip" title="<?php echo JText::_( 'EB_EARLY_BIRD_DISCOUNT' );?>::<?php echo JText::_('EB_EARLY_BIRD_DISCOUNT_EXPLAIN'); ?>"><?php echo JText::_('EB_EARLY_BIRD_DISCOUNT'); ?></span>
					</td>
					<td>
						<input type="text" name="early_bird_discount_amount" id="early_bird_discount_amount" class="input-mini" size="5" value="<?php echo $this->item->early_bird_discount_amount; ?>" />&nbsp;&nbsp;<?php echo $this->lists['early_bird_discount_type'] ; ?>
					</td>
				</tr>
				<tr>
					<td class="key" width="30%">
						<span class="editlinktip hasTip" title="<?php echo JText::_( 'EB_EARLY_BIRD_DISCOUNT_DATE' );?>::<?php echo JText::_('EB_EARLY_BIRD_DISCOUNT_DATE_EXPLAIN'); ?>"><?php echo JText::_('EB_EARLY_BIRD_DISCOUNT_DATE'); ?></span>
					</td>
					<td>				
						<?php echo JHtml::_('calendar', $this->item->early_bird_discount_date != $this->nullDate ? JHtml::_('date', $this->item->early_bird_discount_date, $format, null) : '', 'early_bird_discount_date', 'early_bird_discount_date'); ?>
					</td>
				</tr>
			</table>
		</div>		
		<?php 
			if ($this->config->event_custom_field) {
			?>
				<div class="tab-pane" id="extra-information-page">
					<table class="admintable">				
					<?php
						foreach ($this->form->getFieldset('basic') as $field) {
						?>
							<tr>
								<td class="key" width="30%">
									<?php echo $field->label ;?>
								</td>					
								<td>
									<?php echo  $field->input ; ?>
								</td>
							</tr>
					<?php
						}					
					?>
					</table>								
				</div>
			<?php	
			}
		?>				
	</div>	
</div>
	<input type="hidden" name="option" value="com_eventbooking" />	
	<input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="task" value="" />	
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<?php echo JHtml::_( 'form.token' ); ?>	
	<script type="text/javascript" language="javascript">
		function addRow() {
			var table = document.getElementById('price_list');
			var newRowIndex = table.rows.length - 1 ;
			var row = table.insertRow(newRowIndex);			
			var registrantNumber = row.insertCell(0);							
			var price = row.insertCell(1);						
			registrantNumber.innerHTML = '<input type="text" class="inputbox" name="registrant_number[]" size="10" />';			
			price.innerHTML = '<input type="text" class="inputbox" name="price[]" size="10" />';		
			
		}
		function removeRow() {
			var table = document.getElementById('price_list');
			var deletedRowIndex = table.rows.length - 2 ;
			if (deletedRowIndex >= 1) {
				table.deleteRow(deletedRowIndex);
			} else {
				alert("<?php echo JText::_('EB_NO_ROW_TO_DELETE'); ?>");
			}
		}		
	</script>
</form>