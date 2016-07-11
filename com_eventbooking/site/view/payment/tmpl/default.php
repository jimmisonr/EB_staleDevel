<?php
/**
 * @version            2.8.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die ;
EventbookingHelperJquery::validateForm();

$headerText = JText::_('EB_DEPOSIT_PAYMENT');

if (strlen(strip_tags($this->message->{'deposit_payment_form_message' . $this->fieldSuffix})))
{
	$msg = $this->message->{'deposit_payment_form_message' . $this->fieldSuffix};
}
else
{

	$msg = $this->message->deposit_payment_form_message;
}

$msg = str_replace('[AMOUNT]', EventbookingHelper::formatCurrency($this->rowRegistrant->amount - $this->rowRegistrant->deposit_amount, $this->config, $this->event->currency_symbol), $msg);
$msg = str_replace('[REGISTRATION_ID]', $this->rowRegistrant->id, $msg);

$replaces = EventbookingHelper::buildEventTags($this->event, $this->config);
foreach ($replaces as $key => $value)
{
	foreach ($replaces as $key => $value)
	{
		$key        = strtoupper($key);
		$msg        = str_replace("[$key]", $value, $msg);
		$headerText = str_replace("[$key]", $value, $headerText);
	}
}

if ($this->config->use_https)
{
	$url = JRoute::_('index.php?option=com_eventbooking&task=payment.process&Itemid='.$this->Itemid, false, 1);
}
else
{
	$url = JRoute::_('index.php?option=com_eventbooking&task=payment.process&Itemid='.$this->Itemid, false);
}
$selectedState = '';

// Bootstrap classes
$bootstrapHelper   = $this->bootstrapHelper;
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');

/* @var EventbookingViewRegisterHtml $this */
?>
<div id="eb-deposit-payment-page" class="eb-container">
	<h1 class="eb-page-heading"><?php echo $headerText; ?></h1>
	<form method="post" name="adminForm" id="adminForm" action="<?php echo $url; ?>" autocomplete="off" class="form form-horizontal" enctype="multipart/form-data">
	<?php
	if (strlen($msg))
	{
	?>
		<div class="eb-message"><?php echo $msg; ?></div>
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

		if (count($this->methods) > 1)
		{
			?>
			<div class="<?php echo $controlGroupClass;  ?> payment_information" id="payment_method_container">
				<label class="<?php echo $controlLabelClass; ?>" for="payment_method">
					<?php echo JText::_('EB_PAYMENT_OPTION'); ?>
					<span class="required">*</span>
				</label>

				<div class="<?php echo $controlsClass; ?>">
					<?php
					$method = null;
					for ($i = 0, $n = count($this->methods); $i < $n; $i++)
					{
						$paymentMethod = $this->methods[$i];
						if ($paymentMethod->getName() == $this->paymentMethod)
						{
							$checked = ' checked="checked" ';
							$method  = $paymentMethod;
						}
						else
						{
							$checked = '';
						}
						?>
						<label class="radio">
							<input onclick="changePaymentMethod('individual');" class="validate[required] radio"
								   type="radio" name="payment_method"
								   value="<?php echo $paymentMethod->getName(); ?>" <?php echo $checked; ?> /><?php echo JText::_($paymentMethod->getTitle()); ?>
						</label>
					<?php
					}
					?>
				</div>
			</div>
		<?php
		}
		else
		{
			$method = $this->methods[0];
			?>
			<div class="<?php echo $controlGroupClass;  ?> payment_information" id="payment_method_container">
				<label class="<?php echo $controlLabelClass; ?>">
					<?php echo JText::_('EB_PAYMENT_OPTION'); ?>
				</label>

				<div class="<?php echo $controlsClass; ?>">
					<?php echo JText::_($method->getTitle()); ?>
				</div>
			</div>
		<?php
		}
		if ($method->getCreditCard())
		{
			$style = '';
		}
		else
		{
			$style = 'style = "display:none"';
		}
		?>
		<div class="<?php echo $controlGroupClass;  ?> payment_information" id="tr_card_number" <?php echo $style; ?>>
			<label class="<?php echo $controlLabelClass; ?>" for="x_card_num">
				<?php echo JText::_('AUTH_CARD_NUMBER'); ?><span class="required">*</span>
			</label>

			<div class="<?php echo $controlsClass; ?>">
				<input type="text" id="x_card_num" name="x_card_num"
					   class="input-large validate[required,creditCard]"
					   value="<?php echo $this->escape($this->input->getAlnum('x_card_num')); ?>" onchange="removeSpace(this);"/>
			</div>
		</div>
		<div class="<?php echo $controlGroupClass;  ?> payment_information" id="tr_exp_date" <?php echo $style; ?>>
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo JText::_('AUTH_CARD_EXPIRY_DATE'); ?><span class="required">*</span>
			</label>

			<div class="<?php echo $controlsClass; ?>">
				<?php echo $this->lists['exp_month'] . '  /  ' . $this->lists['exp_year']; ?>
			</div>
		</div>
		<div class="<?php echo $controlGroupClass;  ?> payment_information" id="tr_cvv_code" <?php echo $style; ?>>
			<label class="<?php echo $controlLabelClass; ?>" for="x_card_code">
				<?php echo JText::_('AUTH_CVV_CODE'); ?><span class="required">*</span>
			</label>

			<div class="<?php echo $controlsClass; ?>">
				<input type="text" id="x_card_code" name="x_card_code"
					   class="input-large validate[required,custom[number]]"
					   value="<?php echo $this->escape($this->input->getString('x_card_code')); ?>"/>
			</div>
		</div>
		<?php
		if ($method->getCardType())
		{
			$style = '';
		}
		else
		{
			$style = ' style = "display:none;" ';
		}
		?>
		<div class="<?php echo $controlGroupClass;  ?> payment_information" id="tr_card_type" <?php echo $style; ?>>
			<label class="<?php echo $controlLabelClass; ?>" for="card_type">
				<?php echo JText::_('EB_CARD_TYPE'); ?><span class="required">*</span>
			</label>

			<div class="<?php echo $controlsClass; ?>">
				<?php echo $this->lists['card_type']; ?>
			</div>
		</div>
		<?php
		if ($method->getCardHolderName())
		{
			$style = '';
		}
		else
		{
			$style = ' style = "display:none;" ';
		}
		?>
		<div class="<?php echo $controlGroupClass;  ?> payment_information" id="tr_card_holder_name" <?php echo $style; ?>>
			<label class="<?php echo $controlLabelClass; ?>" for="card_holder_name">
				<?php echo JText::_('EB_CARD_HOLDER_NAME'); ?><span class="required">*</span>
			</label>

			<div class="<?php echo $controlsClass; ?>">
				<input type="text" id="card_holder_name" name="card_holder_name"
					   class="input-large validate[required]"
					   value="<?php echo $this->escape($this->input->getString('card_holder_name')); ?>"/>
			</div>
		</div>
	<?php
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
		<input type="button" class="btn btn-primary" name="btnBack" value="<?php echo  JText::_('EB_BACK') ;?>" onclick="window.history.go(-1);" />
		<input type="submit" class="btn btn-primary" name="btn-submit" id="btn-submit" value="<?php echo JText::_('EB_PROCESS_PAYMENT');?>" />
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
	<input type="hidden" name="registrant_id" id="registrant_id" value="<?php echo $this->rowRegistrant->id ; ?>" />
	<script type="text/javascript">
		var eb_current_page = 'default';
		Eb.jQuery(document).ready(function($){
			$("#adminForm").validationEngine('attach', {
				onValidationComplete: function(form, status){
					if (status == true) {
						form.on('submit', function(e) {
							e.preventDefault();
						});
						form.find('#btn-submit').prop('disabled', true);

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
									exp_year: $('select[name^=exp_year]').val(),
									name: $('#card_holder_name').val()
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
		})
		<?php
			echo os_payments::writeJavascriptObjects();
		?>
		</script>
		<?php echo JHtml::_( 'form.token' ); ?>
	</form>
</div>