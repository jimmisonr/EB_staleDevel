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
?>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('activate_certificate_feature', JText::_('EB_ACTIVATE_CERTIFICATE_FEATURE'), JText::_('EB_ACTIVATE_CERTIFICATE_FEATURE_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('activate_certificate_feature', $config->activate_certificate_feature); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('certificate_prefix', JText::_('EB_CERTIFICATE_PREFIX'), JText::_('EB_CERTIFICATE_PREFIX_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="certificate_prefix" class="inputbox" value="<?php echo $config->get('certificate_prefix', 'CT'); ?>" size="10" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('certificate_number_length', JText::_('EB_CERTIFICATE_NUMBER_LENGTH'), JText::_('EB_CERTIFICATE_NUMBER_LENGTH_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="certificate_number_length" class="inputbox" value="<?php echo $config->get('certificate_number_length', 5); ?>" size="10" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('certificate_layout', JText::_('EB_DEFAULT_CERTIFICATE_LAYOUT'), JText::_('EB_DEFAULT_CERTIFICATE_LAYOUT_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'certificate_layout',  $config->certificate_layout , '100%', '550', '75', '8' ) ;?>
	</div>
</div>