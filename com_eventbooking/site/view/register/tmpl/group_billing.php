<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die;

/* @var EventbookingViewRegisterHtml $this */

if ($this->config->use_https)
{
	$url = JRoute::_('index.php?option=com_eventbooking&task=register.process_group_registration&Itemid='.$this->Itemid, false, 1);
}
else
{
	$url = JRoute::_('index.php?option=com_eventbooking&task=register.process_group_registration&Itemid='.$this->Itemid, false);
}
$selectedState = '';

$bootstrapHelper   = $this->bootstrapHelper;
$controlGroupClass = $bootstrapHelper->getClassMapping('control-group');
$inputPrependClass = $bootstrapHelper->getClassMapping('input-prepend');
$inputAppendClass  = $bootstrapHelper->getClassMapping('input-append');
$addOnClass        = $bootstrapHelper->getClassMapping('add-on');
$controlLabelClass = $bootstrapHelper->getClassMapping('control-label');
$controlsClass     = $bootstrapHelper->getClassMapping('controls');
$btnClass          = $bootstrapHelper->getClassMapping('btn');

$layoutData = array(
	'controlGroupClass' => $controlGroupClass,
	'controlLabelClass' => $controlLabelClass,
	'controlsClass' => $controlsClass,
);

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
<form method="post" name="adminForm" id="adminForm" action="<?php echo $url; ?>" autocomplete="off" class="form form-horizontal" enctype="multipart/form-data">
<?php
	if (!$this->userId && $this->config->user_registration)
	{
		echo $this->loadCommonLayout('register/tmpl/register_user_registration.php', $layoutData);
	}

	$fields = $this->form->getFields();

	if (isset($fields['state']))
	{
		$selectedState = $fields['state']->value;
	}

	$dateFields = array();
	foreach ($fields as $field)
	{
		echo $field->getControlGroup($bootstrapHelper);

		if ($field->type == "Date")
		{
			$dateFields[] = $field->name;
		}
	}
	if (($this->totalAmount > 0) || $this->form->containFeeFields())
	{
	?>
	<h3 class="eb-heading"><?php echo JText::_('EB_PAYMENT_INFORMATION'); ?></h3>
	<?php
	if ($this->enableCoupon)
	{
	?>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>" for="coupon_code"><?php echo  JText::_('EB_COUPON') ?></label>
			<div class="<?php echo $controlsClass; ?>">
				<input type="text" class="input-medium" name="coupon_code" id="coupon_code" value="<?php echo $this->escape($this->input->getString('coupon_code')); ?>" onchange="calculateGroupRegistrationFee();" />
				<span class="invalid" id="coupon_validate_msg" style="display: none;"><?php echo JText::_('EB_INVALID_COUPON'); ?></span>
			</div>
		</div>
	<?php
	}
	?>
	<div class="<?php echo $controlGroupClass; ?>">
		<label class="<?php echo $controlLabelClass; ?>">
			<?php echo JText::_('EB_AMOUNT'); ?>
		</label>
		<div class="<?php echo $controlsClass; ?>">
			<?php
				if ($this->config->currency_position == 0)
				{
				?>
					<div class="<?php echo $inputPrependClass; ?> inline-display">
						<span class="<?php echo $addOnClass; ?>"><?php echo $this->event->currency_symbol ? $this->event->currency_symbol : $this->config->currency_symbol;?></span>
						<input id="total_amount" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($this->totalAmount, $this->config); ?>" />
					</div>
				<?php
				}
				else
				{
				?>
					<div class="<?php echo $inputAppendClass; ?> inline-display">
						<input id="total_amount" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($this->totalAmount, $this->config); ?>" />
						<span class="<?php echo $addOnClass; ?>"><?php echo $this->event->currency_symbol ? $this->event->currency_symbol : $this->config->currency_symbol;?></span>
					</div>
				<?php
				}
			?>
		</div>
	</div>
	<?php
		if ($this->enableCoupon || $this->discountAmount > 0 || $this->bundleDiscountAmount > 0)
		{
		?>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo JText::_('EB_DISCOUNT_AMOUNT'); ?>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<?php
					if ($this->config->currency_position == 0)
					{
					?>
						<div class="<?php echo $inputPrependClass; ?> inline-display">
							<span class="<?php echo $addOnClass; ?>"><?php echo $this->event->currency_symbol ? $this->event->currency_symbol : $this->config->currency_symbol;?></span>
							<input id="discount_amount" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($this->discountAmount, $this->config); ?>" />
						</div>
					<?php
					}
					else
					{
					?>
						<div class="<?php echo $inputAppendClass; ?> inline-display">
							<input id="discount_amount" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($this->discountAmount, $this->config); ?>" />
							<span class="<?php echo $addOnClass; ?>"><?php echo $this->event->currency_symbol ? $this->event->currency_symbol : $this->config->currency_symbol;?></span>
						</div>
					<?php
					}
				?>
			</div>
		</div>
		<?php
		}
		if($this->lateFee > 0)
		{
		?>
			<div class="<?php echo $controlGroupClass;  ?>">
				<label class="<?php echo $controlLabelClass; ?>">
					<?php echo JText::_('EB_LATE_FEE'); ?>
				</label>
				<div class="<?php echo $controlsClass; ?>">
					<?php
					if ($this->config->currency_position == 0)
					{
						?>
						<div class="<?php echo $inputPrependClass;  ?> inline-display">
							<span class="<?php echo $addOnClass;?>"><?php echo $this->event->currency_symbol ? $this->event->currency_symbol : $this->config->currency_symbol;?></span>
							<input id="late_fee" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($this->lateFee, $this->config); ?>" />
						</div>
					<?php
					}
					else
					{
						?>
						<div class="<?php echo $inputAppendClass;  ?> inline-display">
							<input id="late_fee" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($this->lateFee, $this->config); ?>" />
							<span class="<?php echo $addOnClass;?>"><?php echo $this->event->currency_symbol ? $this->event->currency_symbol : $this->config->currency_symbol;?></span>
						</div>
					<?php
					}
					?>
				</div>
			</div>
		<?php
		}
		if($this->event->tax_rate > 0)
		{
		?>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo JText::_('EB_TAX_AMOUNT'); ?>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<?php
					if ($this->config->currency_position == 0)
					{
					?>
						<div class="<?php echo $inputPrependClass; ?> inline-display">
							<span class="<?php echo $addOnClass; ?>"><?php echo $this->event->currency_symbol ? $this->event->currency_symbol : $this->config->currency_symbol;?></span>
							<input id="tax_amount" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($this->taxAmount, $this->config); ?>" />
						</div>
					<?php
					}
					else
					{
					?>
						<div class="<?php echo $inputAppendClass; ?> inline-display">
							<input id="tax_amount" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($this->taxAmount, $this->config); ?>" />
							<span class="<?php echo $addOnClass; ?>"><?php echo $this->event->currency_symbol ? $this->event->currency_symbol : $this->config->currency_symbol;?></span>
						</div>
					<?php
					}
				?>
			</div>
		</div>
		<?php
		}
		if ($this->showPaymentFee)
		{
		?>
			<div class="<?php echo $controlGroupClass; ?>">
				<label class="<?php echo $controlLabelClass; ?>">
					<?php echo JText::_('EB_PAYMENT_FEE'); ?>
				</label>
				<div class="<?php echo $controlsClass; ?>">
					<?php
					if ($this->config->currency_position == 0)
					{
					?>
						<div class="<?php echo $inputPrependClass; ?>">
							<span class="<?php echo $addOnClass; ?>"><?php echo $this->config->currency_symbol;?></span>
							<input id="payment_processing_fee" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($this->paymentProcessingFee, $this->config); ?>" />
						</div>
					<?php
					}
					else
					{
					?>
						<div class="<?php echo $inputAppendClass; ?>">
							<input id="payment_processing_fee" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($this->paymentProcessingFee, $this->config); ?>" />
							<span class="<?php echo $addOnClass; ?>"><?php echo $this->config->currency_symbol;?></span>
						</div>
					<?php
					}
					?>
				</div>
			</div>
		<?php
		}
		if ($this->enableCoupon || $this->discountAmount > 0 || $this->bundleDiscountAmount > 0 || $this->event->tax_rate > 0 || $this->showPaymentFee)
		{
		?>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo JText::_('EB_GROSS_AMOUNT'); ?>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<?php
					if ($this->config->currency_position == 0)
					{
					?>
						<div class="<?php echo $inputPrependClass; ?> inline-display">
							<span class="<?php echo $addOnClass; ?>"><?php echo $this->event->currency_symbol ? $this->event->currency_symbol : $this->config->currency_symbol;?></span>
							<input id="amount" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($this->amount, $this->config); ?>" />
						</div>
					<?php
					}
					else
					{
					?>
						<div class="<?php echo $inputAppendClass; ?> inline-display">
							<input id="amount" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($this->amount, $this->config); ?>" />
							<span class="<?php echo $addOnClass; ?>"><?php echo $this->event->currency_symbol ? $this->event->currency_symbol : $this->config->currency_symbol;?></span>
						</div>
					<?php
					}
				?>
			</div>
		</div>
		<?php
		}
		if ($this->depositPayment)
		{
			if ($this->paymentType == 1)
			{
				$style = '';
			}
			else
			{
				$style = 'style = "display:none"';
			}
			?>
			<div id="deposit_amount_container" class="<?php echo $controlGroupClass; ?>"<?php echo $style; ?>>
				<label class="<?php echo $controlLabelClass; ?>" for="payment_type">
					<?php echo JText::_('EB_DEPOSIT_AMOUNT') ;?>
				</label>
				<div class="<?php echo $controlsClass; ?>">
					<?php
					if ($this->config->currency_position == 0)
					{
					?>
						<div class="<?php echo $inputPrependClass; ?> inline-display">
							<span class="<?php echo $addOnClass; ?>"><?php echo $this->event->currency_symbol ? $this->event->currency_symbol : $this->config->currency_symbol;?></span>
							<input id="deposit_amount" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($this->depositAmount, $this->config); ?>" />
						</div>
					<?php
					}
					else
					{
					?>
						<div class="<?php echo $inputAppendClass; ?> inline-display">
							<input id="deposit_amount" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($this->depositAmount, $this->config); ?>" />
							<span class="<?php echo $addOnClass; ?>"><?php echo $this->event->currency_symbol ? $this->event->currency_symbol : $this->config->currency_symbol;?></span>
						</div>
					<?php
					}
					?>
				</div>
			</div>
			<div class="<?php echo $controlGroupClass; ?>">
				<label class="<?php echo $controlLabelClass; ?>" for="payment_type">
					<?php echo JText::_('EB_PAYMENT_TYPE') ;?>
				</label>
				<div class="<?php echo $controlsClass; ?>">
					<?php echo $this->lists['payment_type'] ;?>
				</div>
			</div>
			<?php
		}

		if (!$this->waitingList)
		{
			echo $this->loadCommonLayout('register/tmpl/register_payment_methods.php', $layoutData);
		}
	}

	$articleId  = $this->event->article_id ? $this->event->article_id : $this->config->article_id ;
	if ($this->config->accept_term ==1 && $articleId)
	{
		if (JLanguageMultilang::isEnabled())
		{
			$associations = JLanguageAssociations::getAssociations('com_content', '#__content', 'com_content.item', $articleId);
			$langCode     = JFactory::getLanguage()->getTag();
			if (isset($associations[$langCode]))
			{
				$article = $associations[$langCode];
			}
		}

		if (!isset($article))
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id, catid')
				->from('#__content')
				->where('id = ' . (int) $articleId);
			$db->setQuery($query);
			$article = $db->loadObject();
		}

		require_once JPATH_ROOT . '/components/com_content/helpers/route.php';
		EventbookingHelperJquery::colorbox('eb-colorbox-term');
		$termLink = ContentHelperRoute::getArticleRoute($article->id, $article->catid) . '&tmpl=component&format=html';
		?>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="checkbox">
				<input type="checkbox" name="accept_term" value="1" class="validate[required]" data-errormessage="<?php echo JText::_('EB_ACCEPT_TERMS');?>" />
				<?php echo JText::_('EB_ACCEPT'); ?>&nbsp;
				<?php
				echo "<a class=\"eb-colorbox-term\" href=\"".JRoute::_($termLink)."\">"."<strong>".JText::_('EB_TERM_AND_CONDITION')."</strong>"."</a>\n";
				?>
			</label>
		</div>
		<?php
	}

	if ($this->showCaptcha)
	{
	?>
		<div class="<?php echo $controlGroupClass; ?>">
			<label class="<?php echo $controlLabelClass; ?>">
				<?php echo JText::_('EB_CAPTCHA'); ?><span class="required">*</span>
			</label>
			<div class="<?php echo $controlsClass; ?>">
				<?php echo $this->captcha; ?>
			</div>
		</div>
	<?php
	}
	if ($this->waitingList)
	{
		$buttonText = JText::_('EB_PROCESS');
	}
	else
	{
		$buttonText = JText::_('EB_PROCESS_REGISTRATION');
	}
	?>
	<div class="form-actions">
		<input type="button" class="btn btn-primary" name="btn-group-billing-back" id="btn-group-billing-back" value="<?php echo  JText::_('EB_BACK') ;?>">
		<input type="submit" class="btn btn-primary" name="btn-process-group-billing" id="btn-process-group-billing" value="<?php echo $buttonText;?>">
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
	<input type="hidden" name="event_id" value="<?php echo $this->event->id; ?>" />
	<input type="hidden" name="show_payment_fee" value="<?php echo (int)$this->showPaymentFee ; ?>" />
	<script type="text/javascript">
		var eb_current_page = 'group_billing';
		<?php echo os_payments::writeJavascriptObjects();?>
			Eb.jQuery(document).ready(function($){
				<?php
					if (count($dateFields))
					{
						echo EventbookingHelperHtml::getCalendarSetupJs($dateFields);
					}
					if ($this->amount == 0)
					{
					?>
						$('.payment_information').css('display', 'none');
					<?php
					}
				?>
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
				<?php
					if ($validateLoginForm)
					{
					?>
						$("#eb-login-form").validationEngine();
					<?php
					}

				?>
				buildStateField('state', 'country', '<?php echo $selectedState; ?>');
				<?php
					if ($this->showCaptcha && $this->captchaPlugin == 'recaptcha')
					{
						$captchaPlugin = JPluginHelper::getPlugin('captcha', 'recaptcha');
						$params = $captchaPlugin->params;
						$version    = $params->get('version', '1.0');
						$pubkey = $params->get('public_key', '');
						if ($version == '1.0')
						{
							$theme  = $params->get('theme', 'clean');
						?>
							Recaptcha.create("<?php echo $pubkey; ?>", "dynamic_recaptcha_1", {theme: "<?php echo $theme; ?>"});
						<?php
						}
						else
						{
							$theme = $params->get('theme2', 'light');
							$langTag = JFactory::getLanguage()->getTag();
							if (JFactory::getApplication()->isSSLConnection())
							{
								$file = 'https://www.google.com/recaptcha/api.js?hl=' . $langTag . '&onload=onloadCallback&render=explicit';
							}
							else
							{
								$file = 'http://www.google.com/recaptcha/api.js?hl=' . $langTag . '&onload=onloadCallback&render=explicit';
							}
							JHtml::_('script', $file, true, true);
							?>
								grecaptcha.render("dynamic_recaptcha_1", {sitekey: "' . <?php echo $pubkey;?> . '", theme: "' . <?php echo $theme; ?> . '"});
							<?php
						}
					}
				?>
				$('#btn-group-billing-back').click(function(){
					$.ajax({
						url: siteUrl + 'index.php?option=com_eventbooking&view=register&layout=group_members&event_id=<?php echo $this->event->id; ?>&Itemid=<?php echo $this->Itemid; ?>&format=raw' + langLinkForAjax,
						type: 'post',
						dataType: 'html',
						beforeSend: function() {
							$('#btn-group-billing-back').attr('disabled', true);
						},
						complete: function() {
							$('#btn-group-billing-back').attr('disabled', false);
						},
						success: function(html) {
							$('#eb-group-members-information .eb-form-content').html(html);
							$('#eb-group-billing .eb-form-content').slideUp('slow');
							<?php ($this->config->collect_member_information) ? $idAjax = 'eb-group-members-information' : $idAjax = 'eb-number-group-members';?>
							$('#<?php echo $idAjax; ?> .eb-form-content').slideDown('slow');
						},
						error: function(xhr, ajaxOptions, thrownError) {
							alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
						}
					});
				});
				//term colorbox term
				 $(".eb-colorbox-term").colorbox({
					 href: $(this).attr('href'),
					 innerHeight: '80%',
					 innerWidth: '80%',
					 overlayClose: true,
					 iframe: true,
					 opacity: 0.3
				});
				<?php
					if ($this->config->collect_member_information)
					{
					?>
						$('html, body').animate({scrollTop:$('#eb-group-members-information').position().top}, 'slow');
					<?php
					}
				?>
			})
	</script>
</form>
