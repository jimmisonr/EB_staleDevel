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
		<?php echo EventbookingHelperHtml::getFieldLabel('activate_tickets_pdf', JText::_('EB_ACTIVATE_TICKETS_PDF'), JText::_('EB_ACTIVATE_TICKETS_PDF_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('activate_tickets_pdf', $config->activate_tickets_pdf); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('ticket_start_number', JText::_('EB_TICKET_START_NUMBER'), JText::_('EB_TICKET_START_NUMBER_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="invoice_start_number" class="inputbox" value="<?php echo $config->ticket_start_number ? $config->ticket_start_number : 1; ?>" size="10" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('ticket_prefix', JText::_('EB_TICKET_PREFIX'), JText::_('EB_TICKET_PREFIX_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="ticket_prefix" class="inputbox" value="<?php echo $config->get('ticket_prefix', 'TK'); ?>" size="10" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('ticket_number_length', JText::_('EB_TICKET_NUMBER_LENGTH'), JText::_('EB_TICKET_NUMBER_LENGTH_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<input type="text" name="ticket_number_length" class="inputbox" value="<?php echo $config->get('ticket_number_length', 5); ?>" size="10" />
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('default_ticket_layout', JText::_('EB_DEFAULT_TICKET_LAYOUT'), JText::_('EB_DEFAULT_TICKET_LAYOUT_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $editor->display( 'default_ticket_layout',  $config->default_ticket_layout , '100%', '550', '75', '8' ) ;?>
	</div>
</div>