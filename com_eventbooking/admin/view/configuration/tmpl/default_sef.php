<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2017 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die ;
?>
<p class="message"><strong><?php echo JText::_('EB_SEF_SETTING_EXPLAIN'); ?></strong></p>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('insert_event_id', JText::_('EB_INSERT_EVENT_ID'), JText::_('EB_INSERT_EVENT_ID_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo EventbookingHelperHtml::getBooleanInput('insert_event_id', $config->insert_event_id); ?>
	</div>
</div>
<div class="control-group">
	<div class="control-label">
		<?php echo EventbookingHelperHtml::getFieldLabel('insert_category', JText::_('EB_INSERT_CATEGORY'), JText::_('EB_INSERT_CATEGORY_EXPLAIN')); ?>
	</div>
	<div class="controls">
		<?php echo $this->lists['insert_category']; ?>
	</div>
</div>
