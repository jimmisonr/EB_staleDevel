<?php
/**
 * @version            2.4.2
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die ; 
JToolBarHelper::title(JText::_('EB_BATCH_COUPONS_TITLE'));
JToolBarHelper::custom('coupon.batch', 'upload', 'upload', 'Generate Coupons', false);
JToolBarHelper::cancel('coupon.cancel');	
?>
<form action="index.php?option=com_eventbooking&view=coupon" method="post" name="adminForm" id="adminForm">
<div class="row-fluid">
			<table class="admintable adminform">
				<tr>
					<td width="200" class="key">
						<?php echo  JText::_('EB_NUMBER_COUPONS'); ?>
					</td>
					<td>
						<input class="input-mini" type="text" name="number_coupon" id="number_coupon" size="15" maxlength="250" value="" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('EB_EVENTS'); ?>
					</td>
					<td>
						<?php echo $this->lists['event_id']; ?>
					</td>
				</tr>
				<tr>
					<td width="100" class="key">
						<?php echo  JText::_('EB_DISCOUNT'); ?>
					</td>
					<td>
						<input class="text_area" type="text" name="discount" id="discount" size="10" maxlength="250" value="" />&nbsp;&nbsp;<?php echo $this->lists['coupon_type'] ; ?>
					</td>
				</tr>
				<tr>
					<td width="100" class="key">
						<?php echo  JText::_('EB_CHARACTERS_SET'); ?>
					</td>
					<td>
						<input class="text_area" type="text" name="characters_set" id="characters_set" size="15" maxlength="250" value="" />
					</td>
				</tr>
				<tr>
					<td width="100" class="key">
						<?php echo  JText::_('EB_PREFIX'); ?>
					</td>
					<td>
						<input class="text_area" type="text" name="prefix" id="prefix" size="15" maxlength="250" value="" />
					</td>
				</tr>
				<tr>
					<td width="100" class="key">
						<?php echo  JText::_('EB_COUPON_LENGTH'); ?>
					</td>
					<td>
						<input class="text_area" type="text" name="length" id="length" size="15" maxlength="250" value="" />
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('EB_VALID_FROM_DATE'); ?>
					</td>
					<td>
						<?php echo JHtml::_('calendar', '', 'valid_from', 'valid_from') ; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('EB_VALID_TO_DATE'); ?>
					</td>
					<td>
						<?php echo JHtml::_('calendar', '', 'valid_to', 'valid_to') ; ?>
					</td>
				</tr>
				<tr>
					<td class="key">
						<?php echo JText::_('EB_TIMES'); ?>
					</td>
					<td>
						<input class="text_area" type="text" name="times" id="times" size="5" maxlength="250" value="<?php echo $this->item->times;?>" />
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
		</table>							
</div>		
<div class="clr"></div>	
	<?php echo JHtml::_( 'form.token' ); ?>
	<input type="hidden" name="used" value="<?php echo $this->item->used;?>" />
	<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="task" value="" />
</form>