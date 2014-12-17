<?php
/**
 * @version		1.6.4
 * @package		Joomla
 * @subpackage	Event Booking
 * @author  Tuan Pham Ngoc
 * @copyright	Copyright (C) 2010 - 2014 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die ;
JHtml::_('script', JUri::root().'components/com_eventbooking/assets/js/noconflict.js', false, false);
EventbookingHelperJquery::validateForm();
$headerText = JText::_('EB_INDIVIDUAL_REGISTRATION') ;
$headerText = str_replace('[EVENT_TITLE]', $event->title, $headerText) ;
if ($config->use_https) 
{
	$url = JRoute::_('index.php?option=com_eventbooking&task=process_individual_registration&Itemid='.$Itemid, false, 1);
} 
else 
{
	$url = JRoute::_('index.php?option=com_eventbooking&task=process_individual_registration&Itemid='.$Itemid, false);
}
$selectedState = '';
?>
<div id="eb-individual-registration-page" class="eb-container row-fluid">
	<h1 class="eb-page-heading"><?php echo $headerText; ?></h1>	
	<?php	 
	if (strlen(strip_tags($message->{'registration_form_message'.$fieldSuffix})))
	{
		$msg = $message->{'registration_form_message'.$fieldSuffix};
	}
	else 
	{
		$msg = $message->registration_form_message;
	}			
	if (strlen($msg)) 
	{
		$msg = str_replace('[EVENT_TITLE]', $event->title, $msg) ;
		$msg = str_replace('[EVENT_DATE]', JHtml::_('date', $event->event_date, $config->event_date_format, null), $msg) ;
		$msg = str_replace('[AMOUNT]', EventbookingHelper::formatCurrency($amount, $config, $event->currency_symbol), $msg) ;			
	?>								
	<div class="eb-message"><?php echo $msg ; ?></div>							 															
	<?php	
	}
	if (!$userId && $config->user_registration)
	{
		$actionUrl = JRoute::_('index.php?option=com_users&task=user.login');
		$validateLoginForm = true;		
	?>
	<form method="post" action="<?php echo $actionUrl ; ?>" name="eb-login-form" id="eb-login-form" autocomplete="off" class="form form-horizontal">			
		<h3 class="eb-heading"><?php echo JText::_('EB_EXISTING_USER_LOGIN'); ?></h3>			
		<div class="control-group">
			<label class="control-label" for="username">
				<?php echo  JText::_('EB_USERNAME') ?><span class="required">*</span>
			</label>
			<div class="controls">      				
				<input type="text" name="username" id="username" class="input-large validate[required]" value=""/>		
			</div>	
		</div>		
		<div class="control-group">
			<label class="control-label" for="password">
				<?php echo  JText::_('EB_PASSWORD') ?><span class="required">*</span>
			</label>
			<div class="controls">      				
				<input type="password" id="password" name="password" class="input-large validate[required]" value="" />		
			</div>	
		</div>
		<div class="control-group">    			
			<div class="controls">      				
				<input type="submit" value="<?php echo JText::_('EB_LOGIN'); ?>" class="button btn btn-primary" />		
			</div>	
		</div>    								
		<h3 class="eb-heading"><?php echo JText::_('EB_NEW_USER_REGISTER'); ?></h3>
		<?php 
			if (JPluginHelper::isEnabled('system', 'remember'))
			{
			?>
				<input type="hidden" name="remember" value="1" />
			<?php	
			}
		?>		
		<input type="hidden" name="return" value="<?php echo base64_encode(JFactory::getURI()->toString()); ?>" />
		<?php echo JHtml::_( 'form.token' ); ?>			
	</form>	
	<?php	
	}
	else 
	{
		$validateLoginForm = false;
	}
	?>		
	<form method="post" name="adminForm" id="adminForm" action="<?php echo $url; ?>" autocomplete="off" class="form form-horizontal">	
	<?php	 									
		if (!$userId && $config->user_registration) 
		{
			$params = JComponentHelper::getParams('com_users');
			$minimumLength = $params->get('minimum_length', 4);
			($minimumLength) ? $minSize = "minSize[4]" : $minSize = "";
		?>						
		<div class="control-group">
			<label class="control-label" for="username1">
				<?php echo  JText::_('EB_USERNAME') ?><span class="required">*</span>
			</label>
			<div class="controls">      				
				<input type="text" name="username" id="username1" class="input-large validate[required,ajax[ajaxUserCall],<?php echo $minSize;?>]" value="<?php echo JRequest::getVar('username'); ?>" />						
			</div>	
		</div>					
		<div class="control-group">			
			<label class="control-label" for="password1">					
				<?php echo  JText::_('EB_PASSWORD') ?><span class="required">*</span>						
			</label>				
			<div class="controls">
				<input type="password" name="password1" id="password1" class="input-large validate[required,<?php echo $minSize;?>]" value=""/>					
			</div>
		</div>
		<div class="control-group">			
			<label class="control-label" for="password2">					
				<?php echo  JText::_('EB_RETYPE_PASSWORD') ?><span class="required">*</span>					
			</label>				
			<div class="controls">
				<input type="password" name="password2" id="password2" class="input-large validate[required,equals[password1]]" value="" />					
			</div>
		</div>											
		<?php	
		}		
		$fields = $form->getFields();
		if (isset($fields['state']))
		{
			$selectedState = $fields['state']->value;
		}
		foreach ($fields as $field)
		{						
			echo $field->getControlGroup();			
		}
		if ($field->name == 'email')
		{
			$ajaxAsync = 0;
		}
		else
		{
			$ajaxAsync = 1;
		}
		if (($totalAmount > 0) || $form->containFeeFields()) 
		{	
		?>
		<h3 class="eb-heading"><?php echo JText::_('EB_PAYMENT_INFORMATION'); ?></h3>
		<div class="control-group">
			<label class="control-label">
				<?php echo JText::_('EB_AMOUNT'); ?>		
			</label>
			<div class="controls">
				<?php 
					if ($config->currency_position == 0) 
					{
					?>
						<div class="input-prepend inline-display">
							<span class="add-on"><?php echo $event->currency_symbol ? $event->currency_symbol : $config->currency_symbol;?></span>
							<input id="total_amount" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($totalAmount, $config); ?>" />
						</div>
					<?php		
					} 
					else 
					{
					?>
						<div class="input-append inline-display">										
							<input id="total_amount" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($totalAmount, $config); ?>" />
							<span class="add-on"><?php echo $event->currency_symbol ? $event->currency_symbol : $config->currency_symbol;?></span>
						</div>
					<?php										
					}
				?>						
			</div>
		</div>			
		<?php	
		if ($enableCoupon || $discountAmount > 0)
		{
		?>
		<div class="control-group">
			<label class="control-label">
				<?php echo JText::_('EB_DISCOUNT_AMOUNT'); ?>		
			</label>
			<div class="controls">
				<?php 
					if ($config->currency_position == 0) 
					{
					?>
						<div class="input-prepend inline-display">
							<span class="add-on"><?php echo $event->currency_symbol ? $event->currency_symbol : $config->currency_symbol;?></span>
							<input id="discount_amount" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($discountAmount, $config); ?>" />
						</div>
					<?php		
					} 
					else 
					{
					?>
						<div class="input-append inline-display">										
							<input id="discount_amount" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($discountAmount, $config); ?>" />
							<span class="add-on"><?php echo $event->currency_symbol ? $event->currency_symbol : $config->currency_symbol;?></span>
						</div>
					<?php										
					}
				?>						
			</div>
		</div>	
		<?php	
		}
		if($config->enable_tax && $config->tax_rate > 0)
		{
		?>
		<div class="control-group">
			<label class="control-label">
				<?php echo JText::_('EB_TAX_AMOUNT'); ?>		
			</label>
			<div class="controls">
				<?php 
					if ($config->currency_position == 0) 
					{
					?>
					<div class="input-prepend inline-display">
						<span class="add-on"><?php echo $event->currency_symbol ? $event->currency_symbol : $config->currency_symbol;?></span>
						<input id="tax_amount" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($taxAmount, $config); ?>" />
					</div>
					<?php		
					} 
					else 
					{
					?>
					<div class="input-append inline-display">										
						<input id="tax_amount" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($taxAmount, $config); ?>" />
						<span class="add-on"><?php echo $event->currency_symbol ? $event->currency_symbol : $config->currency_symbol;?></span>
					</div>
					<?php										
					}
				?>						
			</div>
		</div>	
		<?php	
		}
		if ($showPaymentFee)
		{
		?>
			<div class="control-group">
				<label class="control-label">
					<?php echo JText::_('EB_PAYMENT_FEE'); ?>
				</label>
				<div class="controls">
					<?php
					if ($config->currency_position == 0)
					{
					?>
						<div class="input-prepend">
							<span class="add-on"><?php echo $config->currency_symbol;?></span>
							<input id="payment_processing_fee" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($paymentProcessingFee, $config); ?>" />
						</div>
					<?php
					}
					else
					{
					?>
						<div class="input-append">
							<input id="payment_processing_fee" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($paymentProcessingFee, $config); ?>" />
							<span class="add-on"><?php echo $config->currency_symbol;?></span>
						</div>
					<?php
					}
					?>
				</div>
			</div>
		<?php
		}
		if ($enableCoupon || $discountAmount > 0 || ($config->enable_tax && $config->tax_rate > 0) || $showPaymentFee)
		{
		?>
		<div class="control-group">
			<label class="control-label">
				<?php echo JText::_('EB_GROSS_AMOUNT'); ?>		
			</label>
			<div class="controls">
				<?php 
				if ($config->currency_position == 0) 
				{
				?>
					<div class="input-prepend inline-display">
						<span class="add-on"><?php echo $event->currency_symbol ? $event->currency_symbol : $config->currency_symbol;?></span>
						<input id="amount" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($amount, $config); ?>" />
					</div>
				<?php		
				} 
				else 
				{
				?>
					<div class="input-append inline-display">										
						<input id="amount" type="text" readonly="readonly" class="input-small" value="<?php echo EventbookingHelper::formatAmount($amount, $config); ?>" />
						<span class="add-on"><?php echo $event->currency_symbol ? $event->currency_symbol : $config->currency_symbol;?></span>
					</div>
				<?php										
				}
				?>						
			</div>
		</div>	
		<?php	
		}
		if ($depositPayment) 
		{
		?>	
		<div class="control-group">
			<label class="control-label" for="payment_type">
				<?php echo JText::_('EB_PAYMENT_TYPE') ;?>				
			</label>
			<div class="controls">      				
				<?php echo $lists['payment_type'] ;?>
			</div>	
		</div>			    									
		<?php    
		}	
		if ($enableCoupon)
		{
		?>
		<div class="control-group">
			<label class="control-label" for="coupon_code"><?php echo  JText::_('EB_COUPON') ?></label>
			<div class="controls">
				<input type="text" class="input-medium" name="coupon_code" id="coupon_code" value="<?php echo JRequest::getVar('coupon_code'); ?>" onchange="validateIndividualRegistrationCoupon();" />
				<span class="invalid" id="coupon_validate_msg" style="display: none;"><?php echo JText::_('EB_INVALID_COUPON'); ?></span>	      				      		
			</div>	
		</div>				
		<?php	
		}		
		if (count($methods) > 1) 
		{
		?>
		<div class="control-group payment_information" id="payment_method_container">
			<label class="control-label" for="payment_method">
				<?php echo JText::_('EB_PAYMENT_OPTION'); ?>
				<span class="required">*</span>				
			</label>
			<div class="controls">      				
				<?php
					$method = null ;
					for ($i = 0 , $n = count($methods); $i < $n; $i++) 
					{
						$paymentMethod = $methods[$i];
						if ($paymentMethod->getName() == $selectedPaymentMethod) 
						{
							$checked = ' checked="checked" ';
							$method = $paymentMethod ;
						}										
						else
						{									 
							$checked = '';
						}		
					?>
						<label class="checkbox">
							<input onclick="changePaymentMethod('individual');" class="validate[required] radio" type="radio" name="payment_method" value="<?php echo $paymentMethod->getName(); ?>" <?php echo $checked; ?> /><?php echo JText::_($paymentMethod->getTitle()); ?>
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
			$method = $methods[0] ;
		?>				
		<div class="control-group payment_information" id="payment_method_container">
			<label class="control-label">
				<?php echo JText::_('EB_PAYMENT_OPTION'); ?>				
			</label>
			<div class="controls">      				
				<?php echo JText::_($method->getTitle()); ?>
			</div>	
		</div>														
		<?php	
		}																			
		if ($method->getCreditCard()) 
		{
			$style = '' ;	
		} 
		else 
		{
			$style = 'style = "display:none"';
		}			
		?>							
		<div class="control-group payment_information" id="tr_card_number" <?php echo $style; ?>>
			<label class="control-label" for="x_card_num">
				<?php echo  JText::_('AUTH_CARD_NUMBER'); ?><span class="required">*</span>				
			</label>
			<div class="controls">      				
				<input type="text" id="x_card_num" name="x_card_num" class="input-large validate[required,creditCard]" value="<?php echo JRequest::getVar('x_card_num'); ?>" />
			</div>	
		</div>								
		<div class="control-group payment_information" id="tr_exp_date" <?php echo $style; ?>>
			<label class="control-label">
				<?php echo JText::_('AUTH_CARD_EXPIRY_DATE'); ?><span class="required">*</span>				
			</label>
			<div class="controls">      				
				<?php echo $lists['exp_month'] .'  /  '.$lists['exp_year'] ; ?>
			</div>	
		</div>	    		
		<div class="control-group payment_information" id="tr_cvv_code" <?php echo $style; ?>>
			<label class="control-label" for="x_card_code">
				<?php echo JText::_('AUTH_CVV_CODE'); ?><span class="required">*</span>				
			</label>
			<div class="controls">      				
				<input type="text" id="x_card_code" name="x_card_code" class="input-large validate[required,custom[number]]" value="<?php echo JRequest::getVar('x_card_code'); ?>" />
			</div>	
		</div>								
		<?php
			if ($method->getCardType()) 
			{
				$style = '' ;
			} 
			else 
			{
				$style = ' style = "display:none;" ' ;										
			}															
		?>				
		<div class="control-group payment_information" id="tr_card_type" <?php echo $style; ?>>
			<label class="control-label" for="card_type">
				<?php echo JText::_('EB_CARD_TYPE'); ?><span class="required">*</span>				
			</label>
			<div class="controls">      				
				<?php echo $lists['card_type'] ; ?>
			</div>	
		</div>											
		<?php
			if ($method->getCardHolderName()) 
			{
				$style = '' ;
			} 
			else 
			{
				$style = ' style = "display:none;" ' ;										
			}
		?>				
		<div class="control-group payment_information" id="tr_card_holder_name" <?php echo $style; ?>>
			<label class="control-label" for="card_holder_name">
				<?php echo JText::_('EB_CARD_HOLDER_NAME'); ?><span class="required">*</span>				
			</label>
			<div class="controls">      				
				<input type="text" id="card_holder_name" name="card_holder_name" class="input-large validate[required]"  value="<?php echo JRequest::getVar('card_holder_name'); ?>" />
			</div>	
		</div>					
		<?php
			if ($method->getName() == 'os_ideal') 
			{
				$style = '' ;
			} 
			else 
			{
				$style = ' style = "display:none;" ' ;
			}					
		?>				
		<div class="control-group payment_information" id="tr_bank_list" <?php echo $style; ?>>
			<label class="control-label" for="bank_id">
				<?php echo JText::_('EB_BANK_LIST'); ?><span class="required">*</span>				
			</label>
			<div class="controls">      				
				<?php echo $lists['bank_id'] ; ?>
			</div>	
		</div>											
		<?php		
	}						
	if ($showCaptcha)
	{
	?>
	<div class="control-group">
		<label class="control-label">
			<?php echo JText::_('EB_CAPTCHA'); ?><span class="required">*</span>
		</label>
		<div class="controls">
			<div id="dynamic_recaptcha_1"></div>						
		</div>
	</div>
	<?php
	}
	if ($config->accept_term ==1)
	{
		EventbookingHelperJquery::colorbox('eb-colorbox-term');
		$articleId  = $event->article_id ? $event->article_id : $config->article_id ;
		require_once JPATH_ROOT.'/components/com_content/helpers/route.php' ;
		if ($config->fix_term_and_condition_popup) 
		{
			$termLink = ContentHelperRoute::getArticleRoute($articleId).'&format=html' ;
			$extra = ' target="_blank" ';
		} 
		else 
		{
			$termLink = ContentHelperRoute::getArticleRoute($articleId).'&tmpl=component&format=html' ;
			$extra = ' class="eb-colorbox-term" ' ;
		}
		?>
		<div class="control-group">			
			<label class="checkbox">
				<input type="checkbox" name="accept_term" value="1" class="validate[required]" data-errormessage="<?php echo JText::_('EB_ACCEPT_TERMS');?>" />
				<?php echo JText::_('EB_ACCEPT'); ?>&nbsp;
					<a <?php echo $extra; ?> href="<?php echo JRoute::_($termLink)?>"><?php echo JText::_('EB_TERM_AND_CONDITION'); ?></a> 
			</label>
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
		if (count($methods) == 1) 
		{
		?>
			<input type="hidden" name="payment_method" value="<?php echo $methods[0]->getName(); ?>" />
		<?php	
		}		
	?>
	<input type="hidden" name="Itemid" value="<?php echo $Itemid; ?>" />
	<input type="hidden" name="event_id" id="event_id" value="<?php echo $event->id ; ?>" />
	<input type="hidden" name="option" value="com_eventbooking" />	
	<input type="hidden" name="task" value="process_individual_registration" />
	<input type="hidden" name="from_article" value="1" />
	<input type="hidden" name="show_payment_fee" value="<?php echo (int)$showPaymentFee ; ?>" />
	<input type="hidden" id="eb_ajax_async" value="<?php echo $ajaxAsync; ?>" />
		<script type="text/javascript">		
				Eb.jQuery(document).ready(function($){
					$("#adminForm").validationEngine();
					<?php
						if ($validateLoginForm)
						{
						?>
							$("#eb-login-form").validationEngine();	
						<?php	
						}	
					?>
					buildStateField('state', 'country', '<?php echo $selectedState; ?>');										
				})
			var siteUrl = "<?php echo EventbookingHelper::getSiteUrl(); ?>";			
			<?php
				echo os_payments::writeJavascriptObjects();					 		 
			?>										
		</script>	
		<?php echo JHtml::_( 'form.token' ); ?>
	</form>					
</div>