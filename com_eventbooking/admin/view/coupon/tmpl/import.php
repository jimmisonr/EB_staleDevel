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
defined('_JEXEC') or die;
JToolBarHelper::title(JText::_('EB_IMPORT_COUPONS_TITLE'));
JToolBarHelper::custom('coupon.import', 'upload', 'upload', 'Import Coupons', false);
JToolBarHelper::cancel('coupon.cancel');
?>
<form action="index.php?option=com_eventbooking&view=coupon&layout=import" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
	<table class="admintable adminform">
		<tr>
			<td class="key">
				<?php echo JText::_('EB_CSV_FILE'); ?>											
			</td>
			<td>
				<input type="file" name="csv_coupons" size="50">	
			</td>
			<td>
				<?php echo JText::_('EB_CSV_COUPON_FILE_EXPLAIN'); ?>
			</td>
		</tr>
	</table>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_( 'form.token' ); ?>			
</form>