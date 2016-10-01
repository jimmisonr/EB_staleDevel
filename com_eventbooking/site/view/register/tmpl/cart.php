<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die ;			
EventbookingHelperJquery::validateForm();
JHtml::_('behavior.modal', 'a.eb-modal');
$popup = 'class="eb-modal" rel="{handler: \'iframe\', size: {x: 800, y: 500}}"';
if ($this->config->use_https) 
{
	$formUrl = JRoute::_('index.php?option=com_eventbooking&Itemid='.$this->Itemid, false, 1);
} 
else 
{
    $formUrl = JRoute::_('index.php?option=com_eventbooking&Itemid='.$this->Itemid, 0);
}
$selectedState = '';

$bootstrapHelper   = $this->bootstrapHelper;
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$inputPrependClass = $bootstrapHelper->getClassMapping('input-prepend');
$inputAppendClass  = $bootstrapHelper->getClassMapping('input-append');
$addOnClass        = $bootstrapHelper->getClassMapping('add-on');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');

$layoutData = array(
	'controlGroupClass' => $controlGroupClass,
	'controlLabelClass' => $controlLabelClass,
	'controlsClass'     => $controlsClass,
);

/* @var EventbookingViewRegisterHtml $this */
?>
<div id="eb-cart-registration-page" class="eb-container row-fluid">
<h1 class="eb-page-heading"><?php echo JText::_('EB_CHECKOUT'); ?></h1>
<?php	 
	if (strlen(strip_tags($this->message->{'registration_form_message'.$this->fieldSuffix})))
	{
		$msg = $this->message->{'registration_form_message'.$this->fieldSuffix};
	}
	else 
	{
		$msg = $this->message->registration_form_message;
	}			
	if (strlen($msg)) 
	{
		$msg = str_replace('[EVENT_TITLE]', $this->eventTitle, $msg) ;
		$msg = str_replace('[AMOUNT]', EventBookingHelper::formatCurrency($this->amount, $this->config), $msg) ;			
	?>								
		<div class="eb-message"><?php echo $msg ; ?></div>							 															
	<?php	
	}
?>
<div class="eb-cart-content">		
	<table class="table table-striped table-bordered table-condensed">
		<thead>
			<tr>			
				<th class="col_event">
					<?php echo JText::_('EB_EVENT'); ?>
				</th>
				<?php
					if ($this->config->show_event_date) {
					?>
						<th class="col_event_date">
							<?php echo JText::_('EB_EVENT_DATE'); ?>
						</th>	
					<?php	
					}
				?>																	
				<th class="col_price">
					<?php echo JText::_('EB_PRICE'); ?>
				</th>					
				<th class="col_quantity">
					<?php echo JText::_('EB_QUANTITY'); ?>
				</th>
				<th class="col_subtotal">
					<?php echo JText::_('EB_SUB_TOTAL'); ?>
				</th>
			</tr>
		</thead>
		<tbody>
		<?php			
			$total = 0 ;
			$k = 0 ;			
			for ($i = 0 , $n = count($this->items) ; $i < $n; $i++) 
			{
				$item = $this->items[$i] ;				
				$total += $item->quantity*$item->rate ;				
	        	$url = JRoute::_('index.php?option=com_eventbooking&view=event&id='.$item->id.'&tmpl=component&Itemid='.$this->Itemid);
			?>
				<tr>								
					<td class="col_event">
						<a href="<?php echo $url; ?>" <?php echo $popup; ?>><?php echo $item->title; ?></a>								
					</td>	
					<?php
						if ($this->config->show_event_date) 
						{
						?>
							<td class="col_event_date">
								<?php
                                    if ($item->event_date == EB_TBC_DATE) 
									{
                                        echo JText::_('EB_TBC');
                                    } 
                                    else 
									{
                                        echo JHtml::_('date', $item->event_date, $this->config->event_date_format, null); 
                                    }
								?>								
							</td>
						<?php	
						}
					?>																				
					<td class="col_price">
						<?php echo EventbookingHelper::formatCurrency($item->rate, $this->config); ?>
					</td>
					<td class="col_quantity">
						<?php echo $item->quantity ; ?>
					</td>	
					<td class="col_price">
						<?php echo EventbookingHelper::formatCurrency($item->rate*$item->quantity, $this->config); ?>
					</td>					
				</tr>
			<?php				
				$k = 1 - $k ;				
			}				
			if ($this->config->show_event_date) 
			{
				$cols = 5 ;
			} 
			else 
			{
				$cols = 4 ;
			}										
			?>		
			<tr>				
				<td colspan="<?php echo $cols ; ?>" style="text-align: right;">										
					<input type="button" class="btn" value="<?php echo JText::_('EB_MODIFY_CART'); ?>" onclick="updateCart();" />																									
				</td>								
			</tr>		
		</tbody>		
	</table>				
</div>
<div class="clearfix"></div>	
<?php
	if (!$this->userId && $this->config->user_registration)
	{
		$validateLoginForm = true;
		echo $this->loadCommonLayout('register/tmpl/register_login.php', $layoutData);
	}
	else
	{
		$validateLoginForm = false;
	}
?>
	<form method="post" name="adminForm" id="adminForm" action="<?php echo $formUrl; ?>" autocomplete="off" class="form form-horizontal" enctype="multipart/form-data">
	<?php	 									
		if (!$this->userId && $this->config->user_registration) 
		{
			echo $this->loadCommonLayout('register/tmpl/register_user_registration.php', $layoutData);
		}

		// Collect registrants information
		if ($this->config->collect_member_information_in_cart)
		{
			$count = 0;
			foreach($this->items as $item)
			{
			?>
				<h3 class="eb-heading"><?php echo JText::sprintf('EB_EVENT_REGISTRANTS_INFORMATION', $item->title); ?></h3>
			<?php
				for ($i = 0 ; $i < $item->quantity; $i++)
				{
					$count++;
					$rowFields = EventbookingHelper::getFormFields($item->id, 2);
					$form = new RADForm($rowFields);
					$form->setFieldSuffix($count);
					$form->bind($this->formData, $this->useDefault);
					$form->prepareFormFields('calculateCartRegistrationFee();');
					$form->buildFieldsDependency();
					$fields = $form->getFields();

					//We don't need to use ajax validation for email field for group members
					if (isset($fields['email']))
					{
						$emailField = $fields['email'];
						$cssClass = $emailField->getAttribute('class');
						$cssClass = str_replace(',ajax[ajaxEmailCall]', '', $cssClass);
						$emailField->setAttribute('class', $cssClass);
					}
				?>
					<h4 class="eb-heading"><?php echo JText::sprintf('EB_MEMBER_INFORMATION', $i + 1); ?></h4>
				<?php
					$j = 0;
					foreach ($fields as $field)
					{
						$j++;

						if ($j > 1 && $field->row->only_show_for_first_member)
						{
							continue;
						}

						if ($j > 1 && $field->row->only_require_for_first_member)
						{
							$field->makeFieldOptional();
						}

						echo $field->getControlGroup($bootstrapHelper);
					}
				}
			}
		?>
			<h3 class="eb-heading"><?php echo JText::_('EB_BILLING_INFORMATION'); ?></h3>
		<?php
		}

		$fields = $this->form->getFields();

		if (isset($fields['state']))
		{
			$selectedState = $fields['state']->value;
		}

		foreach ($fields as $field)
		{
			echo $field->getControlGroup($bootstrapHelper);
		}

		if ($this->totalAmount > 0 || $this->form->containFeeFields())
		{
            $showPaymentInformation = true;
		?>
		<h3 class="eb-heading"><?php echo JText::_('EB_PAYMENT_INFORMATION'); ?></h3>
		<?php
		$layoutData['currencySymbol']     = $this->config->currency_symbol;
		$layoutData['onCouponChange']     = 'calculateCartRegistrationFee();';
		$layoutData['addOnClass']         = $addOnClass;
		$layoutData['inputPrependClass']  = $inputPrependClass;
		$layoutData['inputAppendClass']   = $inputAppendClass;
		$layoutData['showDiscountAmount'] = ($this->enableCoupon || $this->discountAmount > 0 || $this->bunldeDiscount > 0);
		$layoutData['showTaxAmount']      = ($this->taxAmount > 0);
		$layoutData['showGrossAmount']    = ($this->enableCoupon || $this->discountAmount > 0 || $this->bunldeDiscount > 0 || $this->taxAmount > 0 || $this->showPaymentFee);

		echo $this->loadCommonLayout('register/tmpl/register_payment_amount.php', $layoutData);

		echo $this->loadCommonLayout('register/tmpl/register_payment_methods.php', $layoutData);
	}

	if ($this->config->accept_term ==1 && $this->config->article_id)
	{
		$layoutData['articleId'] = $this->config->article_id;
		echo $this->loadCommonLayout('register/tmpl/register_terms_and_conditions.php', $layoutData);
	}

	if ($this->showCaptcha)
	{
	?>
	<div class="<?php echo $controlGroupClass;  ?>">
		<label class="<?php echo $controlLabelClass; ?>">
			<?php echo JText::_('EB_CAPTCHA'); ?><span class="required">*</span>
		</label>
		<div class="<?php echo $controlsClass; ?>">
			<?php echo $this->captcha; ?>	
		</div>
	</div>
	<?php
	}
	?>								
	<div class="form-actions">
		<input type="button" class="btn btn-primary" name="btnBack" value="<?php echo  JText::_('EB_BACK') ;?>" onclick="window.history.go(-1);">
		<input type="submit" class="btn btn-primary" name="btn-submit" id="btn-submit" value="<?php echo JText::_('EB_PROCESS_REGISTRATION');?>">
		<img id="ajax-loading-animation" src="<?php echo JUri::base(true);?>/media/com_eventbooking/ajax-loadding-animation.gif" style="display: none;"/>
	</div>																					
	<?php
		if (count($this->methods) == 1) 
		{
		?>
		<input type="hidden" name="payment_method" value="<?php echo $this->methods[0]->getName(); ?>" />
		<?php	
		}		
	?>
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />	
	<input type="hidden" name="option" value="com_eventbooking" />	
	<input type="hidden" name="task" value="cart.process_checkout" />
	<input type="hidden" name="show_payment_fee" value="<?php echo (int)$this->showPaymentFee ; ?>" />
		<script type="text/javascript">		
			var eb_current_page = 'cart';
			Eb.jQuery(function($){
				$(document).ready(function(){
					$("#adminForm").validationEngine('attach', { 
					    onValidationComplete: function(form, status){
					        if (status == true) {						        
					            form.on('submit', function(e) {
					                e.preventDefault();
					            });

						        if (typeof stripePublicKey !== 'undefined' && $('#x_card_num').is(":visible"))
						        {
							        if($('input:radio[name^=payment_method]').length)
							        {
								        var paymentMethod = $('input:radio[name^=payment_method]:checked').val();
							        }
							        else
							        {
								        var paymentMethod = $('input[name^=payment_method]').val();
							        }

							        if (paymentMethod.indexOf('os_stripe') == 0)
							        {
								        Stripe.card.createToken({
									        number: $('#x_card_num').val(),
									        cvc: $('#x_card_code').val(),
									        exp_month: $('select[name^=exp_month]').val(),
									        exp_year: $('select[name^=exp_year]').val()
								        }, stripeResponseHandler);

								        return false;
							        }
						        }

					            return true;
					        }
					        return false;
					    }
					});
					buildStateField('state', 'country', '<?php echo $selectedState; ?>');
					if ($('#email').val())
					{
						$('#email').validationEngine('validate'); 
					}
                    <?php
                    if ($this->amount == 0 && !empty($showPaymentInformation))
                    {
                    //The event is free because of discount, so we need to hide payment information
                    ?>
                    $('.payment_information').css('display', 'none');
                    <?php
                    }
                    ?>
				})
			});
			
			<?php
				echo os_payments::writeJavascriptObjects();					 		 
			?>				

			function updateCart(){
				location.href = '<?php echo JRoute::_(EventbookingHelperRoute::getViewRoute('cart', $this->Itemid)); ?>' ;
			}																
		</script>	
		<?php echo JHtml::_( 'form.token' ); ?>
	</form>					
</div>