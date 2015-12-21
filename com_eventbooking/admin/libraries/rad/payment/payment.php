<?php
/**
 * @package     Joomla.RAD
 * @subpackage  Payment
 * @author      Ossolution Team
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('JPATH_PLATFORM') or die();

class RADPayment
{

	/**
	 * @var String payment method name
	 */
	public $name = null;
	/**
	 * Title of payment method
	 *
	 * @var string
	 */

	public $title = null;

	/**
	 * Payment Mode
	 * @var bool
	 */
	protected $mode = null;

	/**
	 * The payment gateway url
	 *
	 * @var string
	 */
	protected $url = null;

	/**
	 * Redirect Heading
	 *
	 * @var null
	 */
	protected $redirectHeading = null;

	/**
	 * @var JRegistry Payment method data
	 */
	protected $params = null;

	/**
	 * @var Int of the payment method. 0 : Redirect, 1 : Creditcard
	 */
	protected $type = 0;

	/**
	 * @var bool Show Cardtype or not
	 */
	protected $showCardType = false;

	/**
	 * @var bool Show card holder name or not
	 */
	protected $showCardHolderName = false;

	/**
	 * @var Array Data which will be posted to the payment gateway
	 */
	protected $parameters = null;

	/**
	 * @var Array Data posted from the payment gateway back to server
	 */
	protected $postData = null;

	/**
	 * @var String Absolute path to the IPN log file
	 */
	protected $ipnLogFile = null;


	/**
	 * Constructor function, init the payment method data
	 *
	 * @param $params
	 */

	public function __construct($params, $config = array())
	{
		$this->name = get_class($this);
		if (isset($config['type']))
		{
			$this->type = $config['type'];
		}
		else
		{
			$this->type = 0;
		}

		if (isset($config['show_card_type']))
		{
			$this->showCardType = $config['show_card_type'];
		}
		else
		{
			$this->showCardType = false;
		}

		if (isset($config['show_card_holder_name']))
		{
			$this->showCardHolderName = $config['show_card_holder_name'];
		}
		else
		{
			$this->showCardHolderName = false;
		}

		$this->params     = $params;
		$this->ipnLogFile = JPATH_ROOT . '/components/com_eshop/ipn_logs.txt';
		$this->loadLanguage();
	}

	/**
	 * Getter method for name property
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Setter method for name property
	 *
	 * @param string $value
	 */
	public function setName($value)
	{
		$this->name = $value;
	}

	/**
	 * @return String Get title of the payment method
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param $title String title of the payment method
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 *
	 * Set data for a variable which will be passed to server
	 *
	 * @param $name
	 * @param $value
	 */
	public function setData($name, $value)
	{
		$this->parameters[$name] = $value;
	}

	/**
	 * Get data for a variable
	 *
	 * @param      $name
	 * @param null $default
	 *
	 * @return null
	 */
	public function getData($name, $default = null)
	{
		return isset($this->parameters[$name]) ? $this->parameters[$name] : $default;
	}


	/**
	 * Build the parameters in key=value format used to send to the payment gateway
	 *
	 * @return string
	 */
	public function buildParameters()
	{
		$fields = '';
		foreach ($this->parameters as $key => $value)
		{
			$fields .= "$key=" . urlencode($value) . "&";
		}

		return $fields;
	}

	/**
	 * Load language file for this payment plugin
	 */
	protected function loadLanguage()
	{
		$pluginName = $this->getName();
		$lang       = JFactory::getLanguage();
		$tag        = $lang->getTag();
		if (!$tag)
		{
			$tag = 'en-GB';
		}
		$lang->load($pluginName, JPATH_ROOT, $tag);
	}

	/**
	 * Default function to render payment information, the child class can override it if needed
	 */
	public function renderPaymentInformation()
	{
		?>
		<script type="text/javascript">
			<?php
			if (EshopHelper::getConfigValue('enable_checkout_captcha'))
			{
				$captchaPlugin = JFactory::getApplication()->getParams()->get('captcha', JFactory::getConfig()->get('captcha'));
				if ($captchaPlugin == 'recaptcha')
				{
					$recaptchaPlugin = JPluginHelper::getPlugin('captcha', 'recaptcha');
					$params = new JRegistry($recaptchaPlugin->params);
					$pubkey = $params->get('public_key', '');
					$theme  = $params->get('theme', 'clean');
					?>
			(function ($) {
				$(document).ready(function () {
					Recaptcha.create("<?php echo $pubkey; ?>", "dynamic_recaptcha_1", {theme: "<?php echo $theme; ?>"});
				})
			})(jQuery);
			<?php
		}
	}
	?>
			function checkNumber(input) {
				var num = input.value
				if (isNaN(num)) {
					alert("<?php echo JText::_('ESHOP_ONLY_NUMBER_IS_ACCEPTED'); ?>");
					input.value = "";
					input.focus();
				}
			}
			function checkPaymentData() {
				<?php
					if ($this->type)
					{
					?>
				form = document.getElementById('payment_method_form');
				if (form.card_number.value == "") {
					alert("<?php echo  JText::_('ESHOP_ENTER_CARD_NUMBER'); ?>");
					form.card_number.focus();
					return false;
				}
				if (form.cvv_code.value == "") {
					alert("<?php echo JText::_('ESHOP_ENTER_CARD_CVV_CODE'); ?>");
					form.cvv_code.focus();
					return false;
				}
				<?php
				if ($this->showCardHolderName)
				{
					?>
				if (form.card_holder_name.value == '') {
					alert("<?php echo JText::_('ESHOP_ENTER_CARD_HOLDER_NAME') ; ?>");
					form.card_holder_name.focus();
					return false;
				}
				<?php
			}
			?>
				return true;
				<?php
				}
				else
				{
				?>
				return true;
				<?php
				}
			?>
			}
			Eshop.jQuery(document).ready(function ($) {
				// Confirm button
				$('#button-confirm').click(function () {
					if (checkPaymentData()) {
						<?php
						if (EshopHelper::getConfigValue('enable_checkout_captcha'))
						{
							$captchaPlugin = JFactory::getApplication()->getParams()->get('captcha', JFactory::getConfig()->get('captcha'));
							if ($captchaPlugin == 'recaptcha')
							{
								?>
						jQuery.ajax({
							url: '<?php echo EshopHelper::getSiteUrl(); ?>index.php?option=com_eshop&task=checkout.validateCaptcha',
							type: 'post',
							dataType: 'json',
							data: jQuery('#payment_method_form input[type=\'text\'], #payment_method_form input[type=\'hidden\']'),
							beforeSend: function () {
								$('#button-confirm').attr('disabled', true);
								$('#button-confirm').after('<span class="wait">&nbsp;<img src="components/com_eshop/assets/images/loading.gif" alt="" /></span>');
							},
							complete: function () {
								$('#button-confirm').attr('disabled', false);
								$('.wait').remove();
							},
							success: function (data) {
								if (data['error']) {
									alert(data['error']);
								}
								if (data['success']) {
									$('#payment_method_form').submit();
								}
							}
						});
						<?php
					}
					else
					{
						?>
						$('#payment_method_form').submit();
						<?php
					}
				}
				else
				{
					?>
						$('#payment_method_form').submit();
						<?php
					}
					?>
					}
				})
			})
		</script>
		<form action="<?php echo EshopHelper::getSiteUrl(); ?>index.php?option=com_eshop&task=checkout.processOrder"
		      method="post" name="payment_method_form" id="payment_method_form" class="form form-horizontal">
			<div class="no_margin_left">
				<?php
				if ($this->type)
				{
					$currentYear = date('Y');
					?>
					<div class="control-group">
						<div class="control-label">
							<?php echo JText::_('ESHOP_CARD_NUMBER'); ?><span class="required">*</span>
						</div>
						<div class="controls">
							<input type="text" id="card_number" name="card_number" class="inputbox"
							       onkeyup="checkNumber(this)" value="" class="input-large"/>
						</div>
					</div>
					<div class="control-group">
						<div class="control-label">
							<?php echo JText::_('ESHOP_CARD_EXPIRY_DATE'); ?><span class="required">*</span>
						</div>
						<div class="controls">
							<?php echo JHtml::_('select.integerlist', 1, 12, 1, 'exp_month', ' class="input-small" ', date('m'), '%02d') . '  /  ' . JHtml::_('select.integerlist', $currentYear, $currentYear + 10, 1, 'exp_year', ' class="input-small"'); ?>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label" for="cvv_code">
							<?php echo JText::_('ESHOP_CVV_CODE'); ?><span class="required">*</span>
						</label>

						<div class="controls">
							<input type="text" id="cvv_code" name="cvv_code" class="input-small"
							       onKeyUp="checkNumber(this)" value=""/>
						</div>
					</div>
					<?php
					if ($this->showCardType)
					{
						$options   = array();
						$options[] = JHtml::_('select.option', 'Visa', 'Visa');
						$options[] = JHtml::_('select.option', 'MasterCard', 'MasterCard');
						$options[] = JHtml::_('select.option', 'Discover', 'Discover');
						$options[] = JHtml::_('select.option', 'Amex', 'American Express');
						?>
						<div class="control-group">
							<label class="control-label" for="cvv_code">
								<?php echo JText::_('ESHOP_CARD_TYPE'); ?><span class="required">*</span>
							</label>

							<div class="controls">
								<?php echo JHtml::_('select.genericlist', $options, 'card_type', ' class="input-large" ', 'value', 'text'); ?>
							</div>
						</div>
					<?php
					}
					if ($this->showCardHolderName)
					{
						?>
						<label class="control-label" for="card_holder_name">
							<?php echo JText::_('ESHOP_CARD_HOLDER_NAME'); ?><span class="required">*</span>
						</label>
						<div class="controls">
							<input type="text" id="card_holder_name" name="card_holder_name" class="input-large"
							       value=""/>
						</div>
					<?php
					}
				}
				if (EshopHelper::getConfigValue('enable_checkout_captcha'))
				{
					$captchaPlugin = JFactory::getApplication()->getParams()->get('captcha', JFactory::getConfig()->get('captcha'));
					if ($captchaPlugin)
					{
						?>
						<div class="control-group">
							<label class="control-label" for="recaptcha_response_field">
								<?php echo JText::_('ESHOP_CAPTCHA'); ?><span class="required">*</span>
							</label>

							<div class="controls docs-input-sizes">
								<?php echo JCaptcha::getInstance($captchaPlugin)->display('dynamic_recaptcha_1', 'dynamic_recaptcha_1', 'required'); ?>
							</div>
						</div>
					<?php
					}
				}
				?>
				<div class="no_margin_left">
					<input id="button-confirm" type="button" class="btn btn-primary pull-right"
					       value="<?php echo JText::_('ESHOP_CONFIRM_ORDER'); ?>"/>
				</div>
			</div>
		</form>
	<?php
	}

	/**
	 * Submit post to paypal server
	 */
	public function submitPost()
	{
		if (!$this->redirectHeading)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('title')
				->from('#__eshop_payments')
				->where('name = "' . $this->name . '"');
			$db->setQuery($query);
			$this->redirectHeading = JText::sprintf('ESHOP_REDIRECT_HEADING', JText::_($db->loadResult()));
		}
		?>
		<div class="eshop-heading"><?php echo $this->redirectHeading; ?></div>
		<form method="post" action="<?php echo $this->url; ?>" name="eshop_order_form" id="eshop_order_form">
			<?php
			if (count($this->parameters))
			{
				foreach ($this->parameters as $key => $val)
				{
					echo '<input type="hidden" name="' . $key . '" value="' . $val . '" />';
					echo "\n";
				}
			}
			?>
			<script type="text/javascript">
				function redirect() {
					document.eshop_order_form.submit();
				}
				setTimeout('redirect()', 7000);
			</script>
		</form>
	<?php
	}

	/**
	 * Log gateway data
	 */
	public function logGatewayData($extraData = null)
	{
		$text = '[' . date('m/d/Y g:i A') . '] - ';
		$text .= "Log Data From : " . $this->title . " \n";
		foreach ($this->postData as $key => $value)
		{
			$text .= "$key=$value, ";
		}
		if (strlen($extraData))
		{
			$text .= $extraData;
		}
		$fp = fopen($this->ipnLogFile, 'a');
		fwrite($fp, $text . "\n\n");
		fclose($fp);
	}
}

?>
