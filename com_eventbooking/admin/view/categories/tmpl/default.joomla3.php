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
	$saveOrderingUrl = 'index.php?option=com_eventbooking&view=categories&task=save_order_ajax&tmpl=component';
	JHtml::_('sortablelist.sortable', 'categoryList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>
<form action="index.php?option=com_eventbooking&view=categories" method="post" name="adminForm" id="adminForm">
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
			echo $this->lists['filter_state'];
			echo $this->pagination->getLimitBox();
		?>
	</td>
</tr>
</table>
<div id="editcell">
	<table class="adminlist table table-striped" id="categoryList">
	<thead>
		<tr>
			<th width="1%" class="nowrap center hidden-phone">
				<?php echo JHtml::_('searchtools.sort', '', 'a.lft', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
			</th>
			<th width="20">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
			</th>
			<th class="title" style="text-align: left;">
				<?php echo JHtml::_('grid.sort',  JText::_('EB_NAME'), 'tbl.name', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>											
			<th class="center title" width="15%">
				<?php echo JText::_('EB_NUMBER_EVENTS'); ?>
			</th>			
			<th width="5%">
				<?php echo JHtml::_('grid.sort',  JText::_('EB_PUBLISHED'), 'tbl.published', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>
			<th width="2%">
				<?php echo JHtml::_('grid.sort',  JText::_('EB_ID'), 'tbl.id', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>													
		</tr>
	</thead>
	<tfoot>
		<tr>
			<td colspan="7">
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
		$this->ordering[$row->parent_id][] = $row->id;
		$orderkey   = array_search($row->id, $this->ordering[$row->parent_id]);
		$link 	= JRoute::_( 'index.php?option=com_eventbooking&view=category&id='. $row->id );
		$checked 	= JHtml::_('grid.id',   $i, $row->id );
		$published 	= JHtml::_('grid.published', $row, $i, 'tick.png', 'publish_x.png');
		$canChange	= $user->authorise('core.edit.state',	'com_eventbooking.category.'.$row->id);
		// Get the parents of item for sorting
		if ($row->parent_id > 0)
		{
			$parentsStr = "";
			$_currentParentId = $row->parent_id;
			$parentsStr = " " . $_currentParentId;
			for ($i2 = 0; $i2 < $row->parent_id; $i2++)
			{
				foreach ($this->ordering as $l => $v)
				{
					$v = implode("-", $v);
					$v = "-" . $v . "-";
					if (strpos($v, "-" . $_currentParentId . "-") !== false)
					{
						$parentsStr .= " " . $l;
						$_currentParentId = $l;
						break;
					}
				}
			}
		}
		else
		{
			$parentsStr = "";
		}
		?>
		<tr class="<?php echo "row$k"; ?>" sortable-group-id="<?php echo $row->parent_id; ?>" item-id="<?php echo $row->id ?>" parents="<?php echo $parentsStr ?>" level="<?php echo $row->parent_id ?>">
			<td class="order nowrap center hidden-phone">
				<?php
				$iconClass = '';
				if (!$canChange)
				{
					$iconClass = ' inactive';
				}
				elseif (!$saveOrder)
				{
					$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
				}
				?>
				<span class="sortable-handler<?php echo $iconClass ?>">
					<i class="icon-menu"></i>
				</span>
				<?php if ($canChange && $saveOrder) : ?>
					<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $orderkey + 1; ?>" />
				<?php endif; ?>
			</td>
			<td>
				<?php echo $checked; ?>
			</td>	
			<td>
				<a href="<?php echo $link; ?>">
					<?php echo $row->treename; ?>
				</a>
				<span class="small break-word">
					<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->alias)); ?>
				</span>
			</td>									
			<td class="center">
				<?php echo $row->total_events; ?>
			</td>												
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
	<input type="hidden" name="option" value="com_eventbooking" />
	<input type="hidden" name="task" value="show_categories" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->state->filter_order; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->state->filter_order_Dir; ?>" />	
	<?php echo JHtml::_( 'form.token' ); ?>
</form>