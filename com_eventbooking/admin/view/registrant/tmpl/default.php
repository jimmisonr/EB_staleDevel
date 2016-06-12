<?php
/**
 * @version            2.7.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

$selectedState = '';
?>
<form action="index.php?option=com_eventbooking&view=registrant" method="post" name="adminForm" id="adminForm" class="form form-horizontal" enctype="multipart/form-data">
<div class="row-fluid">
	<div class="control-group">
		<label class="control-label">
			<?php echo  JText::_('EB_EVENT'); ?>
		</label>
		<div class="controls">
			<?php echo $this->lists['event_id']; ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">
			<?php echo  JText::_('EB_USER'); ?>
		</label>
		<div class="controls">
			<?php echo EventbookingHelper::getUserInput($this->item->user_id,'user_id',(int) $this->item->id) ; ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">
			<?php echo  JText::_('EB_NB_REGISTRANTS'); ?>
		</label>
		<div class="controls">
			<?php
				if ($this->item->number_registrants > 0)
				{
					echo $this->item->number_registrants ;
				}
				else
				{
				?>
					<input class="text_area" type="text" name="number_registrants" id="number_registrants" size="40" maxlength="250" value="1" />
					<small><?php echo JText::_('EB_NUMBER_REGISTRANTS_EXPLAIN'); ?></small>
				<?php
				}
			?>
		</div>
	</div>
	<?php
		$fields = $this->form->getFields();
		if (isset($fields['state']))
		{
			$selectedState = $fields['state']->value;
		}
		foreach ($fields as $field)
		{
			$fieldType = strtolower($field->type);
			switch ($fieldType)
			{
				case 'heading':
				case 'message':
					break;
				default:
					$controlGroupAttributes = 'id="field_' . $field->name . '" ';
					if ($field->hideOnDisplay)
					{
						$controlGroupAttributes .= ' style="display:none;" ';
					}
					$class = "";
					if ($field->isMasterField)
					{
						if ($field->suffix)
						{
							$class = ' master-field-' . $field->suffix;
						}
						else
						{
							$class = ' master-field';
						}
					}
			?>
			<div class="control-group<?php echo $class; ?>" <?php echo $controlGroupAttributes; ?>>
				<label class="control-label">
					<?php echo $field->title; ?>
				</label>
				<div class="controls">
					<?php echo $field->input; ?>
				</div>
			</div>
			<?php
			}
		}
	?>
	<div class="control-group">
		<label class="control-label">
			<?php echo  JText::_('EB_REGISTRATION_DATE'); ?>
		</label>
		<div class="controls">
			<?php echo  JHtml::_('date', $this->item->register_date, $this->config->date_format, null);?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">
			<?php echo  JText::_('EB_TOTAL_AMOUNT'); ?>
		</label>
		<div class="controls">
			<?php echo $this->config->currency_symbol?><input type="text" name="total_amount" class="input-medium" value="<?php echo $this->item->total_amount > 0 ? round($this->item->total_amount , 2) : null;?>" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">
			<?php echo  JText::_('EB_DISCOUNT_AMOUNT'); ?>
		</label>
		<div class="controls">
			<?php echo $this->config->currency_symbol?><input type="text" name="discount_amount" class="input-medium" value="<?php echo $this->item->discount_amount > 0 ? round($this->item->discount_amount , 2) : null;?>" />
		</div>
	</div>
	<?php
	if ($this->item->late_fee > 0)
	{
	?>
		<div class="control-group">
			<label class="control-label">
				<?php echo  JText::_('EB_LATE_FEE'); ?>
			</label>
			<div class="controls">
				<?php echo $this->config->currency_symbol?><input type="text" name="late_fee" class="input-medium" value="<?php echo $this->item->late_fee > 0 ? round($this->item->late_fee , 2) : null;?>" />
			</div>
		</div>
	<?php
	}

	if ($this->event->tax_rate > 0 || $this->item->tax_amount > 0)
	{
	?>
		<div class="control-group">
			<label class="control-label">
				<?php echo  JText::_('EB_TAX'); ?>
			</label>
			<div class="controls">
				<?php echo $this->config->currency_symbol?><input type="text" name="tax_amount" class="input-medium" value="<?php echo $this->item->tax_amount > 0 ? round($this->item->tax_amount , 2) : null;?>" />
			</div>
		</div>
	<?php
	}

	if ($this->showPaymentFee || $this->item->payment_processing_fee > 0)
	{
	?>
		<div class="control-group">
			<label class="control-label">
				<?php echo  JText::_('EB_PAYMENT_FEE'); ?>
			</label>
			<div class="controls">
				<?php echo $this->config->currency_symbol?><input type="text" name="payment_processing_fee" class="input-medium" value="<?php echo $this->item->payment_processing_fee > 0 ? round($this->item->payment_processing_fee , 2) : null;?>" />
			</div>
		</div>
	<?php
	}
	?>
	<div class="control-group">
		<label class="control-label">
			<?php echo  JText::_('EB_GROSS_AMOUNT'); ?>
		</label>
		<div class="controls">
			<?php echo $this->config->currency_symbol?><input type="text" name="amount" class="input-medium" value="<?php echo $this->item->amount > 0 ? round($this->item->amount , 2) : null;?>" />
		</div>
	</div>
	<?php
		if ($this->config->activate_deposit_feature)
		{
		?>
			<div class="control-group">
				<label class="control-label">
					<?php echo JText::_('EB_DEPOSIT_AMOUNT'); ?>
				</label>
				<div class="controls">
					<?php echo $this->config->currency_symbol?><input type="text" name="deposit_amount" value="<?php echo $this->item->deposit_amount > 0 ? round($this->item->deposit_amount , 2) : null;?>" />
				</div>
			</div>
			<?php
				if ($this->item->payment_status == 0 && $this->item->id)
				{
				?>
					<div class="control-group">
						<label class="control-label">
							<?php echo JText::_('EB_DUE_AMOUNT'); ?>
						</label>
						<div class="controls">
							<?php
							if ($this->item->payment_status == 1)
							{
								$dueAmount = 0;
							}
							else
							{
								$dueAmount = $this->item->amount - $this->item->deposit_amount;
							}
							echo $this->config->currency_symbol?><input type="text" name="due_amount" class="input-medium" value="<?php echo $dueAmount > 0 ? round($dueAmount , 2) : null;?>" />
						</div>
					</div>
				<?php
				}
			?>
			<div class="control-group">
				<label class="control-label">
					<?php echo JText::_('EB_PAYMENT_STATUS'); ?>
				</label>
				<div class="controls">
					<?php echo $this->lists['payment_status'];?>
				</div>
			</div>
		<?php
		}

		if ($this->item->amount > 0)
		{
		?>
			<div class="control-group">
				<label class="control-label">
					<?php echo JText::_('EB_TRANSACTION_ID'); ?>
				</label>
				<div class="controls">
					<input type="text" name="transaction_id" value="<?php echo $this->item->transaction_id;?>" />
				</div>
			</div>
		<?php
		}

		if ($this->item->payment_method == "os_offline_creditcard")
		{
			$params = new JRegistry($this->item->params);
		?>
			<div class="control-group">
				<label class="control-label">
					<?php echo JText::_('EB_FIRST_12_DIGITS_CREDITCARD_NUMBER'); ?>
				</label>
				<div class="controls">
					<?php echo $params->get('card_number'); ?>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">
					<?php echo JText::_('AUTH_CARD_EXPIRY_DATE'); ?>
				</label>
				<div class="controls">
					<?php echo $params->get('exp_date'); ?>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">
					<?php echo JText::_('AUTH_CVV_CODE'); ?>
				</label>
				<div class="controls">
					<?php echo $params->get('cvv'); ?>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">
					<?php echo JText::_('EB_CARD_HOLDER_NAME'); ?>
				</label>
				<div class="controls">
					<?php echo $params->get('card_holder_name'); ?>
				</div>
			</div>
		<?php
		}
		if ($this->config->activate_checkin_registrants)
		{
		?>
			<div class="control-group">
				<label class="control-label">
					<?php echo  JText::_('EB_CHECKED_IN'); ?>
				</label>
				<div class="controls">
					<?php echo EventbookingHelperHtml::getBooleanInput('checked_in', $this->item->checked_in); ?>
				</div>
			</div>
		<?php
		}
	?>
	<div class="control-group">
		<label class="control-label">
			<?php echo  JText::_('EB_REGISTRATION_STATUS'); ?>
		</label>
		<div class="controls">
			<?php echo $this->lists['published'] ; ?>
		</div>
	</div>
	<?php
	if ($this->item->user_ip)
	{
	?>
		<div class="control-group">
			<label class="control-label">
				<?php echo  JText::_('EB_USER_IP'); ?>
			</label>
			<div class="controls">
				<?php echo $this->item->user_ip; ?>
			</div>
		</div>
	<?php
	}
	if ($this->config->collect_member_information && count($this->rowMembers)) 
	{
	?>
		<h3 class="eb-heading"><?php echo JText::_('EB_MEMBERS_INFORMATION') ; ?></h3>
	<?php			
		for ($i = 0 , $n = count($this->rowMembers) ; $i < $n ; $i++) 
		{
			$rowMember = $this->rowMembers[$i] ;			
			$memberId = $rowMember->id ;
			$form = new RADForm($this->memberFormFields);
			$memberData = EventbookingHelper::getRegistrantData($rowMember, $this->memberFormFields);
			$form->setEventId($this->item->event_id);
			$form->bind($memberData);	
			$form->setFieldSuffix($i+1);
			$form->buildFieldsDependency();
			if ($i%2 == 0)
			{
				echo "<div class=\"row-fluid\">\n" ;
			}					
			?>
				<div class="span6">
					<h4><?php echo JText::sprintf('EB_MEMBER_INFORMATION', $i + 1); ;?></h4>
					<?php
						$fields = $form->getFields();
						foreach ($fields as $field)
						{
							if ($i > 0 && $field->row->only_show_for_first_member)
							{
								continue;
							}
							$fieldType = strtolower($field->type);
							switch ($fieldType)
							{
								case 'heading':
								case 'message':
									break;
								default:
									$controlGroupAttributes = 'id="field_' . $field->name . '" ';
									if ($field->hideOnDisplay)
									{
										$controlGroupAttributes .= ' style="display:none;" ';
									}
									$class = '';
									if ($field->isMasterField)
									{
										if ($field->suffix)
										{
											$class = ' master-field-' . $field->suffix;
										}
										else
										{
											$class = ' master-field';
										}
									}
								?>
								<div class="control-group<?php echo $class; ?>" <?php echo $controlGroupAttributes; ?>>
									<label class="control-label">
										<?php echo $field->title; ?>
									</label>
									<div class="controls">
										<?php echo $field->input; ?>
									</div>
								</div>
							<?php
							}
						}
					?>
					<input type="hidden" name="ids[]" value="<?php echo $rowMember->id; ?>" />
				</div>
			<?php	
			if (($i + 1) %2 == 0)
			{
				echo "</div>\n" ;
			}
		}
		if ($i %2 != 0)
		{
			echo "</div>" ;
		}	
	?>				
	</table>	
	<?php	
	}
	?>				
</div>		
<div class="clearfix"></div>	
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="task" value="" />			
	<?php echo JHtml::_( 'form.token' ); ?>
	<script type="text/javascript">
		(function($){

			showHideDependFields = (function(fieldId, fieldName, fieldType, fieldSuffix) {
				$('#ajax-loading-animation').show();
				var masterFieldsSelector;
				if (fieldSuffix)
				{
					masterFieldsSelector = '.master-field-' + fieldSuffix + ' input[type=\'checkbox\']:checked,' + ' .master-field-' + fieldSuffix + ' input[type=\'radio\']:checked,' + ' .master-field-' + fieldSuffix + ' select';
				}
				else
				{
					masterFieldsSelector = '.master-field input[type=\'checkbox\']:checked, .master-field input[type=\'radio\']:checked, .master-field select';
				}
				$.ajax({
					type: 'POST',
					url: siteUrl + 'index.php?option=com_eventbooking&task=get_depend_fields_status&field_id=' + fieldId + '&field_suffix=' + fieldSuffix + langLinkForAjax,
					data: $(masterFieldsSelector),
					dataType: 'json',
					success: function(msg, textStatus, xhr) {
						$('#ajax-loading-animation').hide();
						var hideFields = msg.hide_fields.split(',');
						var showFields = msg.show_fields.split(',');
						for (var i = 0; i < hideFields.length ; i++)
						{
							$('#' + hideFields[i]).hide();
						}
						for (var i = 0; i < showFields.length ; i++)
						{
							$('#' + showFields[i]).show();
						}
					},
					error: function(jqXHR, textStatus, errorThrown) {
						alert(textStatus);
					}
				});
			});
			buildStateField = (function(stateFieldId, countryFieldId, defaultState){
				if($('#' + stateFieldId).length && $('#' + stateFieldId).is('select'))
				{
					//set state
					if ($('#' + countryFieldId).length)
					{
						var countryName = $('#' + countryFieldId).val();
					}
					else 
					{
						var countryName = '';
					}			
					$.ajax({
						type: 'POST',
						url: siteUrl + 'index.php?option=com_eventbooking&task=get_states&country_name='+ countryName+'&field_name='+stateFieldId + '&state_name=' + defaultState,
						success: function(data) {
							$('#field_' + stateFieldId + ' .controls').html(data);
						},
						error: function(jqXHR, textStatus, errorThrown) {						
							alert(textStatus);
						}
					});			
					//Bind onchange event to the country 
					if ($('#' + countryFieldId).length)
					{
						$('#' + countryFieldId).change(function(){
							$.ajax({
								type: 'POST',
								url: siteUrl + 'index.php?option=com_eventbooking&task=get_states&country_name='+ $(this).val()+'&field_name=' + stateFieldId + '&state_name=' + defaultState,
								success: function(data) {
									$('#field_' + stateFieldId + ' .controls').html(data);
								},
								error: function(jqXHR, textStatus, errorThrown) {						
									alert(textStatus);
								}
							});
							
						});
					}						
				}//end check exits state
							
			});
			$(document).ready(function(){
				buildStateField('state', 'country', '<?php echo $selectedState; ?>');
			})
			populateRegistrantData = (function(){
				var userId = $('#user_id_id').val();
				var eventId = $('#event_id').val();
				$.ajax({
					type : 'POST',
					url : 'index.php?option=com_eventbooking&task=get_profile_data&user_id=' + userId + '&event_id=' +eventId,
					dataType: 'json',
					success : function(json){
						var selecteds = [];
						for (var field in json)
						{
							value = json[field];
							if ($("input[name='" + field + "[]']").length)
							{
								//This is a checkbox or multiple select
								if ($.isArray(value))
								{
									selecteds = value;
								}
								else
								{
									selecteds.push(value);
								}
								$("input[name='" + field + "[]']").val(selecteds);
							}
							else if ($("input[type='radio'][name='" + field + "']").length)
							{
								$("input[name="+field+"][value=" + value + "]").attr('checked', 'checked');
							}
							else
							{
								$('#' + field).val(value);
							}
						}						
					}
				})
			});
		})(jQuery);
	</script>
</form>