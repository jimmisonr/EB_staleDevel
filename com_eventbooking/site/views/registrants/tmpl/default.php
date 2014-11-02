<?php
/**
 * @version        	1.6.6
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2014 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die ;
$cols = 8;
?>
<div id="eb-registrants-management-page" class="eb-container row-fluid">
<h1 class="eb_title"><?php echo JText::_('EB_REGISTRANT_LIST'); ?></h1>
<form action="<?php JRoute::_('index.php?option=com_eventbooking&view=registrants&Itemid='.$this->Itemid );?>" method="post" name="adminForm" id="adminForm">
	<table width="100%">
		<tr>
			<td align="left">
				<?php echo JText::_( 'EB_FILTER' ); ?>:
				<input type="text" name="search" id="search" value="<?php echo $this->lists['search'];?>" class="input-medium" onchange="document.adminForm.submit();" />		
				<button onclick="this.form.submit();" class="btn"><?php echo JText::_( 'EB_GO' ); ?></button>
				<button onclick="document.getElementById('search').value='';this.form.submit();" class="btn"><?php echo JText::_( 'EB_RESET' ); ?></button>		
			</td >	
			<td style="text-align: right;">
				<?php echo $this->lists['event_id'] ; ?>
				<?php echo $this->lists['published'] ; ?>
			</td>
		</tr>
	</table>
<?php
	if (count($this->items)) 
	{
	?>		
		<table class="table table-striped table-bordered table-condensed">
		<thead>
			<tr>
				<th width="5">
					<?php echo JText::_( 'NUM' ); ?>
				</th>				
				<th class="list_first_name">
					<?php echo JHtml::_('grid.sort',  JText::_('EB_FIRST_NAME'), 'tbl.first_name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>						
				<th class="list_last_name">
					<?php echo JHtml::_('grid.sort',  JText::_('EB_LAST_NAME'), 'tbl.last_name', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th class="list_event">
					<?php echo JHtml::_('grid.sort',  JText::_('EB_EVENT'), 'b.title', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<?php
					if ($this->config->show_event_date) 
					{
                        $cols++;
					?>
						<td class="list_event_date">
							<?php echo JHtml::_('grid.sort',  JText::_('EB_EVENT_DATE'), 'b.event_date', $this->lists['order_Dir'], $this->lists['order'] ); ?>
						</td>	
					<?php	
					}
				?>				
				<th class="list_email">
					<?php echo JHtml::_('grid.sort',  JText::_('EB_EMAIL'), 'tbl.email', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
				<th class="list_registrant_number">
					<?php echo JHtml::_('grid.sort',  JText::_('EB_REGISTRANTS'), 'tbl.number_registrants', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>													
				<th class="list_amount">
					<?php echo JHtml::_('grid.sort',  JText::_('EB_AMOUNT'), 'tbl.amount', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
                <?php
                if ($this->config->activate_deposit_feature)
                {
                    $cols++;
                ?>
                    <th class="eb-payment-status" nowrap="nowrap">
                        <?php echo JHtml::_('grid.sort',  JText::_('EB_PAYMENT_STATUS'), 'tbl.payment_status', $this->lists['order_Dir'], $this->lists['order']); ?>
                    </th>
                <?php
                }
                ?>
				<th class="list_id">
					<?php echo JHtml::_('grid.sort',  JText::_('EB_REGISTRATION_STATUS'), 'tbl.published', $this->lists['order_Dir'], $this->lists['order'] ); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<?php
					if ($this->pagination->total > $this->pagination->limit) 
					{
                    ?>
                        <td colspan="<?php echo $cols; ?>">
                            <?php echo $this->pagination->getListFooter(); ?>
                        </td>
				    <?php
					}			
				?>			
			</tr>
		</tfoot>
		<tbody>
		<?php		
		for ($i=0, $n=count( $this->items ); $i < $n; $i++)
		{
			$row = &$this->items[$i];
			$link 	= JRoute::_( 'index.php?option=com_eventbooking&task=edit_registrant&cid[]='. $row->id.'&Itemid='.$this->Itemid);					
			$isMember = $row->group_id > 0 ? true : false ;
			if ($isMember) 
			{
				$groupLink = JRoute::_( 'index.php?option=com_eventbooking&task=edit_registrant&cid[]='. $row->group_id.'&Itemid='.$this->Itemid);
			}				
			?>
			<tr>
				<td>
					<?php echo $this->pagination->getRowOffset( $i ); ?>
				</td>					
				<td>
					<a href="<?php echo $link; ?>">
						<?php echo $row->first_name ?>
					</a>
					<?php
					if ($row->is_group_billing) 
					{
						echo '<br />' ;
						echo JText::_('EB_GROUP_BILLING');
					}
					if ($isMember) 
					{
					?>
						<br />
						<?php echo JText::_('EB_GROUP'); ?><a href="<?php echo $groupLink; ?>"><?php echo $row->group_name ;  ?></a>
					<?php			
					}
					?>
				</td>			
				<td>
					<?php echo $row->last_name ; ?>
				</td>
				<td>
					<?php echo $row->title ; ?>
				</td>
				<?php
					if ($this->config->show_event_date) 
					{
					?>
						<td>
							<?php echo JHtml::_('date', $row->event_date, $this->config->date_format, null) ; ?>
						</td>
					<?php	
					}
				?>				
				<td class="center">
					<?php echo $row->email; ?>
				</td>							
				<td class="center" style="font-weight: bold;">
					<?php echo $row->number_registrants; ?>			
				</td>												
				<td align="right">
					<?php echo number_format($row->amount, 2) ; ?>
				</td>
                <?php
                if ($this->config->activate_deposit_feature) {
                ?>
                    <td>
                        <?php
                        if($row->payment_status == 1)
                        {
                            echo JText::_('EB_FULL_PAYMENT');
                        }
                        else
                        {
                            echo JText::_('EB_PARTIAL_PAYMENT');
                        }
                        ?>
                    </td>
                <?php
                }
                ?>
				<td class="center">
					<?php
						switch($row->published) {
							case 0 :
								echo JText::_('EB_PENDING');
								break ;
							case 1 :
								echo JText::_('EB_PAID');
								break ;
							case 2 :
								echo JText::_('EB_CANCELLED');
								break ;										 
						}
					?>
				</td>
			</tr>
			<?php			
		}
		?>
		</tbody>
	</table>		
	<?php	
	} 
	else 
	{
	?>
		<div class="eb-message"><?php echo JText::_('EB_NO_REGISTRATION_RECORDS');?></div>
	<?php	
	}
?>	
	<input type="hidden" name="task" value="" />	
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />	
	<?php echo JHtml::_( 'form.token' ); ?>			
</form>
</div>