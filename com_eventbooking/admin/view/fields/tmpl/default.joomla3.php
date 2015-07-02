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
JHtml::_('formbehavior.chosen', 'select');
$user	= JFactory::getUser();
$userId	= $user->get('id');
$listOrder =  $this->state->filter_order;
$listDirn 	= $this->state->filter_order_Dir;
$saveOrder	= $listOrder == 'tbl.ordering';
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_eventbooking&view=fields&task=save_order_ajax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'fieldList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
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
<form action="index.php?option=com_eventbooking&view=fields" method="post" name="adminForm" id="adminForm">
<table width="100%">
<tr>
	<td align="left">
		<div class="btn-wrapper input-append">
			<input type="text" placeholder="<?php echo JText::_( 'Filter' ); ?>" name="filter_search" id="filter_search" value="<?php echo $this->state->filter_search;?>" class="input-medium" onchange="document.adminForm.submit();" />
			<button onclick="this.form.submit();" class="btn"><?php echo JText::_( 'Go' ); ?></button>
			<button onclick="document.getElementById('filter_search').value='';this.form.submit();" class="btn"><?php echo JText::_( 'Reset' ); ?></button>
		</div>		
	</td>	
	<td style="float: right;">		
		<?php 
			echo $this->lists['filter_show_core_fields'];
			if ($this->config->custom_field_by_category)
			{
				echo $this->lists['filter_category_id'];
			}
			else 
			{
				echo $this->lists['filter_event_id'];
			}
			echo $this->lists['filter_state'];
			echo $this->pagination->getLimitBox();
		?>				
	</td>
</tr>
</table>
<div id="editcell">
	<table class="adminlist table table-striped" id="fieldList">
	<thead>
		<tr>
			<th width="1%" class="nowrap center hidden-phone">
				<?php echo JHtml::_('grid.sort', '<i class="icon-menu-2"></i>', 'tbl.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
			</th>
			<th width="20" class="text_center">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
			</th>
			<th style="text-align: left;">
				<?php echo JHtml::_('grid.sort', JText::_('EB_NAME'), 'tbl.name', $this->state->filter_order_Dir, $this->state->filter_order); ?>
			</th>
			<th style="text-align: left;">
				<?php echo JHtml::_('grid.sort',  JText::_('EB_TITLE'), 'tbl.title', $this->state->filter_order_Dir, $this->state->filter_order); ?>
			</th>
			<th style="text-align: left;">
				<?php echo JHtml::_('grid.sort',  JText::_('EB_FIELD_TYPE'), 'tbl.field_type', $this->state->filter_order_Dir, $this->state->filter_order); ?>
			</th>
			<th class="title center">
				<?php echo JHtml::_('grid.sort',  JText::_('EB_REQUIRE'), 'tbl.required', $this->state->filter_order_Dir, $this->state->filter_order); ?>
			</th>
			<th class="title center">
				<?php echo JHtml::_('grid.sort',  JText::_('EB_PUBLISHED'), 'tbl.published', $this->state->filter_order_Dir, $this->state->filter_order); ?>
			</th>
			<th width="1%" nowrap="nowrap">
				<?php echo JHtml::_('grid.sort',  JText::_('EB_ID'), 'tbl.id', $this->state->filter_order_Dir, $this->state->filter_order); ?>
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
	$ordering = ($this->state->filter_order == 'tbl.ordering');
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	{
		$row = &$this->items[$i];
		$link 	= JRoute::_( 'index.php?option=com_eventbooking&view=field&id='. $row->id );
		$checked 	= JHtml::_('grid.id',   $i, $row->id );
		$published = JHtml::_('grid.published', $row, $i, 'tick.png', 'publish_x.png');
		$img 	= $row->required ? 'tick.png' : 'publish_x.png';
		$task 	= $row->required ? 'un_required' : 'required';
		$alt 	= $row->required ? JText::_( 'EB_REQUIRED' ) : JText::_( 'EB_NOT_REQUIRED' );
		$action = $row->required ? JText::_( 'EB_NOT_REQUIRE' ) : JText::_( 'EB_REQUIRE' );		
		$img = JHtml::_('image','admin/'.$img, $alt, array('border' => 0), true) ;
		$href = '
		<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task .'\')" title="'. $action .'">'.
		$img .'</a>'
		;
		$canChange	= $user->authorise('core.edit.state',	'com_eventbooking.field.'.$row->id);
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
			<td class="center">
				<?php echo $checked; ?>
			</td>
			<td>
				<a href="<?php echo $link; ?>">
					<?php echo $row->name; ?>
				</a>
			</td>	
			<td>
				<a href="<?php echo $link; ?>">
					<?php echo $row->title; ?>
				</a>
			</td>
			<td>
				<?php
					echo $row->fieldtype;																						
			 	?>
			</td>						
			<td class="center">
				<?php echo $img; ?>
			</td>
			<td class="center">
				<?php echo $published ; ?>
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
	<input type="hidden" name="option" value="com_eventbooking" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->state->filter_order; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->filter_order_Dir; ?>" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>