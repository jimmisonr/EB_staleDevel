<?php
/**
 * @version        	1.7.3
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die ;				
$ordering = ($this->state->filter_order == 'tbl.ordering');
JHtml::_('formbehavior.chosen', 'select');
$user	= JFactory::getUser();
$userId	= $user->get('id');
$listOrder =  $this->state->filter_order;
$listDirn 	= $this->state->filter_order_Dir;
$saveOrder	= $listOrder == 'tbl.ordering';
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_eventbooking&view=events&task=save_order_ajax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'eventList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<script type="text/javascript">
	Joomla.orderTable = function()
	{
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		}
		else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>
<form action="index.php?option=com_eventbooking&view=events" method="post" name="adminForm" id="adminForm">
<table width="100%">
<tr>
	<td align="left" style="text-align: left; width:20%; vertical-align: top;">
		<div class="btn-wrapper input-append">
			<input type="text" placeholder="<?php echo JText::_( 'Filter' ); ?>" name="filter_search" id="filter_search" value="<?php echo $this->state->filter_search;?>" class="input-medium" onchange="document.adminForm.submit();" />
			<button onclick="this.form.submit();" class="btn"><?php echo JText::_( 'Go' ); ?></button>
			<button onclick="document.getElementById('filter_search').value='';this.form.submit();" class="btn"><?php echo JText::_( 'Reset' ); ?></button>
		</div>
	</td>
	<td style="float:right;">
		<strong><?php echo JText::_('EB_CATEGORY'); ?></strong>:&nbsp;
		<?php
			echo $this->lists['filter_category_id'] ;
			echo $this->lists['filter_location_id'] ;
			echo $this->lists['filter_state'] ;
			if (JLanguageMultilang::isEnabled())
			{
				echo $this->lists['filter_language'];
			}
			echo $this->lists['filter_past_events'];
			echo $this->pagination->getLimitBox();
		?>
	</td>
</tr>
</table>
<div id="editcell">
	<table class="adminlist table table-striped" id="eventList">
	<thead>
		<tr>
			<th width="1%" class="nowrap center hidden-phone">
				<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'tbl.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
			</th>
			<th class="title" style="text-align: left;">
				<?php echo JHtml::_('grid.sort',  JText::_('EB_TITLE'), 'tbl.title', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>
			<th class="center title" width="10%">
				<?php echo JHtml::_('grid.sort',  JText::_('EB_EVENT_DATE'), 'tbl.event_date', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>			
			<th class="title center" width="7%">
				<?php echo JHtml::_('grid.sort', JText::_('EB_CAPACITY'), 'tbl.event_capacity', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>																							
			<th class="title" width="7%">
				<?php echo JHtml::_('grid.sort',  JText::_('EB_NUMBER_REGISTRANTS'), 'total_registrants', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>
			<?php
				if ($this->config->activate_recurring_event) {
				?>
					<th width="8%" nowrap="nowrap">
						<?php echo JHtml::_('grid.sort', JText::_('EB_EVENT_TYPE'), 'tbl.event_type', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
					</th>	
				<?php	
				} 
			?>		
			<th width="5%" nowrap="nowrap" class="center">
				<?php echo JHtml::_('grid.sort', JText::_('EB_PUBLISHED'), 'tbl.published', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>
			<th width="1%" nowrap="nowrap" class="center">
				<?php echo JHtml::_('grid.sort',  JText::_('EB_ID'), 'tbl.id', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>			
		</tr>
	</thead>
	<?php
		if ($this->config->activate_recurring_event )
			$colspan = 10 ;
		else 
			$colspan = 9 ;
	?>
	<tfoot>
		<tr>
			<td colspan="<?php echo $colspan ; ?>">
				<?php echo $this->pagination->getListFooter(); ?>
			</td>
		</tr>
	</tfoot>
	<tbody>
	<?php
	$k = 0;	  
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	{
		$row = &$this->items[$i];
		$link 	= JRoute::_( 'index.php?option=com_eventbooking&view=event&id='. $row->id );
		$checked 	= JHtml::_('grid.id',   $i, $row->id );
		$published 	= JHtml::_('grid.published', $row, $i, 'tick.png', 'publish_x.png');
		$canChange	= $user->authorise('core.edit.state',	'com_eventbooking.event.'.$row->id);
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td class="order nowrap center hidden-phone">
				<?php
				$iconClass = '';
				if (!$canChange)
				{
					$iconClass = ' inactive';
				}
				elseif (!$saveOrder)
				{
					$iconClass = ' inactive tip-top hasTooltip"';
				}
				?>
				<span class="sortable-handler<?php echo $iconClass ?>">
				<i class="icon-menu"></i>
				</span>
				<?php if ($canChange && $saveOrder) : ?>
					<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $row->ordering ?>" class="width-20 text-area-order "/>
				<?php endif; ?>
			</td>
			<td>
				<?php echo $checked; ?>
			</td>	
			<td>
				<a href="<?php echo $link; ?>">
					<?php echo $row->title ; ?>
				</a>
				<span class="small break-word">
					<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->alias)); ?>
				</span>
				<div class="small">
					<?php echo JText::_('JCATEGORY') . ": " . $this->escape($row->category_name); ?>
				</div>
			</td>
			<td class="center">
				<?php echo JHtml::_('date', $row->event_date, $this->config->date_format, null); ?>
			</td>
			<td class="center">				
				<?php echo $row->event_capacity; ?>											
			</td>									
			<td class="center">
				<?php echo (int) $row->total_registrants ; ?>
			</td>
			<?php
				if ($this->config->activate_recurring_event) {
				?>
					<td align="left">
						<?php
							if ($row->event_type == 0)
								echo JText::_('EB_STANDARD_EVENT');
							elseif($row->event_type == 1) {
								echo JText::_('EB_PARENT_EVENT');
							} else {
								echo JText::_('EB_CHILD_EVENT');
							}								
						?>
					</td>	
				<?php	
				} 
			?>
			<td class="center">
				<?php echo $published; ?>
			</td>								
			<td class="center">
				<?php echo $row->id; ?>
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

	<script type="text/javascript">
		(function($){
			$(document).ready(function(){
				$('#filter_state').addClass('input-medium').removeClass('inputbox');
			})
		})(jQuery);
	</script>
</form>