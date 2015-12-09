<?php
/**
 * @version            2.2.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

// no direct access
defined( '_JEXEC' ) or die ;
JHtml::_('formbehavior.chosen', 'select');
JToolBarHelper::custom('export', 'download', 'download', 'Export Coupons', false);
?>
<form action="index.php?option=com_eventbooking&view=coupons" method="post" name="adminForm" id="adminForm">
	<div id="j-main-container">
		<div id="filter-bar" class="btn-toolbar">
			<div class="filter-search btn-group pull-left">
				<label for="filter_search" class="element-invisible"><?php echo JText::_('EB_FILTER_SEARCH_COUNPONS_DESC');?></label>
				<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->escape($this->state->filter_search); ?>" class="hasTooltip" title="<?php echo JHtml::tooltipText('EB_SEARCH_COUPONS_DESC'); ?>" />
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><span class="icon-search"></span></button>
				<button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><span class="icon-remove"></span></button>
			</div>
			<div class="btn-group pull-right hidden-phone">
				<?php
					echo $this->lists['filter_event_id'];
					echo $this->lists['filter_state'];
					echo $this->pagination->getLimitBox();
				?>
			</div>
		</div>
		<div class="clearfix"></div>
		<table class="adminlist table table-striped">
			<thead>
			<tr>
				<th width="20">
					<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
				</th>
				<th class="title" style="text-align: left;">
					<?php echo JHtml::_('grid.sort',  JText::_('EB_CODE'), 'tbl.code', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
				</th>
				<th width="15%" class="center title">
					<?php echo JText::_('Discount'); ?>
				</th>
				<th width="10%" class="center title" nowrap="nowrap">
					<?php echo JHtml::_('grid.sort',  JText::_('EB_TIMES'), 'tbl.times', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
				</th>
				<th width="10%" class="center title" nowrap="nowrap">
					<?php echo JHtml::_('grid.sort',  JText::_('EB_USED'), 'tbl.used', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
				</th>
				<th width="10%" class="center title" nowrap="nowrap">
					<?php echo JHtml::_('grid.sort',  JText::_('EB_VALID_FROM'), 'tbl.valid_from', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
				</th>
				<th width="10%" class="center title" nowrap="nowrap">
					<?php echo JHtml::_('grid.sort',  JText::_('EB_VALID_TO'), 'tbl.valid_to', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
				</th>
				<th width="5%" class="center title" nowrap="nowrap">
					<?php echo JHtml::_('grid.sort',  JText::_('EB_PUBLISHED'), 'tbl.published', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
				</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<td colspan="8">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php
			$k = 0;
			for ($i=0, $n=count( $this->items ); $i < $n; $i++)
			{
				$row = $this->items[$i];
				$link 	= JRoute::_( 'index.php?option=com_eventbooking&view=coupon&id='. $row->id );
				$checked 	= JHtml::_('grid.id',   $i, $row->id );
				$published 	= JHtml::_('grid.published', $row, $i, 'tick.png', 'publish_x.png');
				?>
				<tr class="<?php echo "row$k"; ?>">
					<td>
						<?php echo $checked; ?>
					</td>
					<td>
						<a href="<?php echo $link; ?>">
							<?php echo $row->code; ?>
						</a>
					</td>
					<td class="center">
						<?php echo number_format($row->discount, 2).$this->discountTypes[$row->coupon_type]  ;?>
					</td>
					<td class="center">
						<?php echo $row->times; ?>
					</td>
					<td class="center">
						<?php echo $row->used; ?>
					</td>
					<td class="center">
						<?php
						if ($row->valid_from != $this->nullDate)
						{
							echo JHtml::_('date', $row->valid_from, $this->dateFormat, null);
						}
						?>
					</td>
					<td class="center">
						<?php
						if ($row->valid_to != $this->nullDate)
						{
							echo JHtml::_('date', $row->valid_to, $this->dateFormat, null);
						}
						?>
					</td>
					<td class="center">
						<?php echo $published; ?>
					</td>
				</tr>
				<?php
				$k = 1 - $k;
			}
			?>
			</tbody>
		</table>
	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->state->filter_order; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->filter_order_Dir; ?>" />	
	<?php echo JHtml::_( 'form.token' ); ?>
</form>