<?php
/**
 * @version        	1.6.9
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die;		
?>
<form action="index.php?option=com_eventbooking&view=waitings" method="post" name="adminForm" id="adminForm">
<table width="100%">
<tr>
	<td align="left">
		<?php echo JText::_( 'Filter' ); ?>:
		<input type="text" name="filter_search" id="filter_search" value="<?php echo $this->state->filter_search;?>" class="text_area search-query" onchange="document.adminForm.submit();" />		
		<button onclick="this.form.submit();" class="btn"><?php echo JText::_( 'Go' ); ?></button>
		<button onclick="document.getElementById('filter_search').value='';this.form.submit();" class="btn"><?php echo JText::_( 'Reset' ); ?></button>		
	</td >	
	<td style="text-align: right;">
		<?php echo $this->lists['filter_event_id']; ?>
	</td>
</tr>
</table>
<div id="editcell">
	<table class="adminlist table table-striped">
	<thead>
		<tr>
			<th width="2%">
				<?php echo JText::_( 'NUM' ); ?>
			</th>
			<th width="2%">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
			</th>			
			<th class="title" style="text-align: left;" width="8%">
				<?php echo JHtml::_('grid.sort',  JText::_('EB_FIRST_NAME'), 'tbl.first_name', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>						
			<th class="title" style="text-align: left;" width="8%">
				<?php echo JHtml::_('grid.sort',  JText::_('EB_LAST_NAME'), 'tbl.last_name', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>
			<th class="title" style="text-align: left;" width="15%">
				<?php echo JHtml::_('grid.sort',  JText::_('EB_EVENT'), 'ev.title', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>			
			<th width="8%" class="title" nowrap="nowrap">
				<?php echo JHtml::_('grid.sort',  JText::_('EB_PHONE'), 'tbl.phone', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>			
			<th width="10%" class="title" nowrap="nowrap">
				<?php echo JHtml::_('grid.sort',  JText::_('EB_EMAIL'), 'tbl.email', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>
			<th width="7%" class="title" nowrap="nowrap">
				<?php echo JHtml::_('grid.sort',  JText::_('EB_NUMBER_REGISTRANTS'), 'tbl.number_registrants', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>
			<th width="10%" class="title" nowrap="nowrap">
				<?php echo JHtml::_('grid.sort',  JText::_('EB_REGISTRATION_DATE'), 'tbl.register_date', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>																																																				
			<th width="3%" class="title" nowrap="nowrap">
				<?php echo JHtml::_('grid.sort',  JText::_('EB_ID'), 'tbl.id', $this->state->filter_order_Dir, $this->state->filter_order ); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr>			
			<td colspan="10">
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
		$link 	= JRoute::_( 'index.php?option=com_eventbooking&view=waiting&id='. $row->id );
		$checked 	= JHtml::_('grid.id',   $i, $row->id );
		?>
		<tr class="<?php echo "row$k"; ?>">
			<td class="center">
				<?php echo $this->pagination->getRowOffset( $i ); ?>
			</td>
			<td class="center"="center">
				<?php echo $checked; ?>
			</td>				
			<td>
				<a href="<?php echo $link; ?>">
					<?php echo $row->first_name ?>
				</a>
			</td>			
			<td>
				<?php echo $row->last_name ; ?>
			</td>
			<td>
				<a href="index.php?option=com_eventbooking&task=edit_event&cid[]=<?php echo $row->event_id; ?>"><?php echo $row->title ; ?></a>
			</td>	
			<td>
				<?php echo $row->phone ; ?>
			</td>					
			<td class="center">
				<?php echo $row->email; ?>
			</td>							
			<td class="center" style="font-weight: bold;">
				<?php echo $row->number_registrants; ?>				
			</td>								
			<td class="center">
				<?php echo JHtml::_('date', $row->register_date, $this->config->date_format); ?>
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
</form>