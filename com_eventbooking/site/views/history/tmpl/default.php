<?php
/**
 * @version        	1.7.1
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die ;
$cols = 6;
?>
<div id="eb-registration-history-page" class="eb-container row-fluid eb-event">
<h1 class="eb-page-heading"><?php echo JText::_('EB_REGISTRATION_HISTORY'); ?></h1>
<form action="<?php echo JRoute::_('index.php?option=com_eventbooking&view=history&Itemid='.$this->Itemid); ; ?>" method="post" name="adminForm"  id="adminForm">
<?php
	if (count($this->items)) 
    {
	?>
    <table width="100%" class="hidden-phone">
        <tr>
            <td align="left">
                <?php echo JText::_( 'EB_FILTER' ); ?>:
                <input type="text" name="search" id="search" value="<?php echo $this->lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />		
                <button onclick="this.form.submit();"><?php echo JText::_( 'EB_GO' ); ?></button>
                <button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo JText::_( 'EB_RESET' ); ?></button>		
            </td >	
            <td style="text-align: right;">
                <?php echo $this->lists['event_id']; ?>
            </td>
        </tr>
    </table>			
    <table class="table table-striped table-bordered table-condensed">
        <thead>
            <tr>
                <th width="5" class="hidden-phone">
                    <?php echo JText::_( 'NUM' ); ?>
                </th>						
                <th class="list_event">
                    <?php echo JHtml::_('grid.sort',  JText::_('EB_EVENT'), 'b.title', $this->lists['order_Dir'], $this->lists['order'] ); ?>
                </th>
                <?php
                    if ($this->config->show_event_date) 
					{
						$cols++;
                    ?>
                        <th class="list_event_date">
                            <?php echo JHtml::_('grid.sort',  JText::_('EB_EVENT_DATE'), 'b.event_date', $this->lists['order_Dir'], $this->lists['order'] ); ?>
                        </th>	
                    <?php	
                    }
                ?>	
                <th class="list_event_date">
                    <?php echo JHtml::_('grid.sort',  JText::_('EB_REGISTRATION_DATE'), 'tbl.register_date', $this->lists['order_Dir'], $this->lists['order'] ); ?>
                </th>					
                <th class="list_registrant_number hidden-phone">
                    <?php echo JHtml::_('grid.sort',  JText::_('EB_REGISTRANTS'), 'tbl.number_registrants', $this->lists['order_Dir'], $this->lists['order'] ); ?>
                </th>													
                <th class="list_amount hidden-phone">
                    <?php echo JHtml::_('grid.sort',  JText::_('EB_AMOUNT'), 'tbl.amount', $this->lists['order_Dir'], $this->lists['order'] ); ?>
                </th>																																					
                <th class="list_id">
                    <?php echo JHtml::_('grid.sort',  JText::_('EB_REGISTRATION_STATUS'), 'tbl.published', $this->lists['order_Dir'], $this->lists['order'] ); ?>
                </th>
                <?php
                	if ($this->config->activate_invoice_feature)
                	{
                		$cols++;
                	?>
	                	<td class="center">
							<?php echo JHtml::_('grid.sort',  JText::_('EB_INVOICE_NUMBER'), 'tbl.invoice_number', $this->lists['order_Dir'], $this->lists['order'] ); ?>
						</td>
                	<?php	
                	}
                ?>
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
        $k = 0;			
        for ($i=0, $n=count( $this->items ); $i < $n; $i++)
        {
            $row = &$this->items[$i];
            $link 	= JRoute::_( 'index.php?option=com_eventbooking&task=edit_registrant&cid[]='. $row->id.'&from=history&Itemid='.JRequest::getInt('Itemid', 0));										
            ?>
            <tr>
                <td class="hidden-phone">
                    <?php echo $this->pagination->getRowOffset( $i ); ?>
                </td>							
                <td>
                    <a href="<?php echo $link; ?>"><?php echo $row->title ; ?></a>
                </td>
                <?php
                    if ($this->config->show_event_date) {
                    ?>
                        <td>
                            <?php echo JHtml::_('date', $row->event_date, $this->config->date_format, null) ; ?>
                        </td>
                    <?php	
                    }
                ?>		
                <td class="center">
                    <?php echo JHtml::_('date', $row->register_date, $this->config->date_format) ; ?>			
                </td>										
                <td class="center hidden-phone" style="font-weight: bold;">
                    <?php echo $row->number_registrants; ?>			
                </td>												
                <td align="right" class="hidden-phone">
                    <?php echo EventbookingHelper::formatCurrency($row->amount, $this->config) ; ?>
                </td>						
                <td class="center">
                    <?php
                        switch($row->published) 
						{
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
                <?php
                	if ($this->config->activate_invoice_feature)
                	{                 		               		
                	?>
	                	<td class="center">
	                		<?php 
							if ($row->invoice_number)
							{
							?>
								<a href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=download_invoice&id='.($row->cart_id ? $row->cart_id : ($row->group_id ? $row->group_id : $row->id))); ?>" title="<?php echo JText::_('EB_DOWNLOAD'); ?>"><?php echo EventbookingHelper::formatInvoiceNumber($row->invoice_number, $this->config) ; ?></a>
							<?php	
							}	
							?>								
						</td>
                	<?php	
                	}
                ?>
            </tr>
            <?php
            $k = 1 - $k;
        }
        ?>
        </tbody>
    </table>								
	<?php	
	} 
    else 
    {
		echo '<div align="center" class="info">'.JText::_('EB_YOU_HAVENT_REGISTER_FOR_EVENTS').'</div>' ;
	}
?>	
	<input type="hidden" name="option" value="com_eventbooking" />
	<input type="hidden" name="task" value="" />	
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['order_Dir']; ?>" />	
	<?php echo JHtml::_( 'form.token' ); ?>			
</form>
</div>