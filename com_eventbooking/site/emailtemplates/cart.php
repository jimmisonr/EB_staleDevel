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
defined('_JEXEC') or die;
?>
<form id="adminForm" class="form form-horizontal">
    <table class="table table-striped table-bordered table-condensed">
        <thead>
        <tr>
            <th class="col_event">
                <?php echo JText::_('EB_EVENT'); ?>
            </th>
            <?php
                if ($config->show_event_date) 
				{
            ?>
                <th class="col_event_date">
                    <?php echo JText::_('EB_EVENT_DATE'); ?>
                </th>
            <?php
                }
            ?>
            <th class="col_price">
                <?php echo JText::_('EB_PRICE'); ?>
            </th>
            <th class="col_quantity">
                <?php echo JText::_('EB_QUANTITY'); ?>
            </th>
            <th class="col_quantity">
                <?php echo JText::_('EB_SUB_TOTAL'); ?>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php
            $total = 0 ;
            for ($i = 0 , $n = count($items) ; $i < $n; $i++) 
			{
                $item = $items[$i] ;
                $rate = EventbookingHelper::getRegistrationRate($item->event_id, $item->number_registrants);
                $total += $item->number_registrants*$rate;
                $url = JRoute::_(EventbookingHelperRoute::getEventRoute($item->event_id, 0, $Itemid));
            ?>
                <tr>
                    <td class="col_event">
                        <a href="<?php echo $url; ?>"><?php echo $item->title; ?></a>
                    </td>
                    <?php
                        if ($config->show_event_date) 
						{
                        ?>
                            <td class="col_event_date">
                                <?php
                                    if ($item->event_date == EB_TBC_DATE) 
									{
                                        echo JText::_('EB_TBC');
                                    } 
                                    else 
									{
                                        echo JHtml::_('date', $item->event_date,  $config->event_date_format, null);
                                    }
                                ?>
                            </td>
                        <?php
                        }
                    ?>
                    <td class="col_price">
                        <?php echo EventbookingHelper::formatAmount($rate, $config); ?>
                    </td>
                    <td class="col_quantity">
                        <?php echo $item->number_registrants ; ?>
                    </td>
                    <td class="col_price">
                        <?php echo EventbookingHelper::formatAmount($rate*$item->number_registrants, $config); ?>
                    </td>
                </tr>
            <?php
            }
        ?>
        </tbody>
    </table>
    <?php
	    $fields = $form->getFields();
	    foreach ($fields as $field)
	    {
	    	echo $field->getOutput(true);
	    }
	    if ($totalAmount > 0)
	    {
    	?>
    	<div class="control-group">
    		<label class="control-label">
    			<?php echo JText::_('EB_AMOUNT'); ?>
    		</label>
    		<div class="controls">
    			<?php echo EventbookingHelper::formatCurrency($totalAmount, $config); ?>
    		</div>
    	</div>
    	<?php	
    		if ($discountAmount > 0)
    		{
    		?>
    			<div class="control-group">
    				<label class="control-label">
    					<?php echo  JText::_('EB_DISCOUNT_AMOUNT'); ?>
    				</label>
    				<div class="controls">
    					<?php echo EventbookingHelper::formatCurrency($discountAmount, $config); ?>
    				</div>
    			</div>
    		<?php
    		}
    		if ($taxAmount > 0)
    		{
    		?>
    			<div class="control-group">
    				<label class="control-label">
    					<?php echo  JText::_('EB_TAX'); ?>
    				</label>
    				<div class="controls">
    					<?php echo EventbookingHelper::formatCurrency($taxAmount, $config); ?>
    				</div>
    			</div>
    		<?php
    		}
		    if ($paymentProcessingFee > 0)
		    {
			?>
			    <div class="control-group">
				    <label class="control-label">
					    <?php echo  JText::_('EB_PAYMENT_FEE'); ?>
				    </label>
				    <div class="controls">
					    <?php echo EventbookingHelper::formatCurrency($paymentProcessingFee, $config); ?>
				    </div>
			    </div>
		    <?php
		    }
		    if ($discountAmount > 0 || $taxAmount > 0 || $paymentProcessingFee > 0)
    		{
    		?>                
    			<div class="control-group">
    				<label class="control-label">
    					<?php echo  JText::_('EB_GROSS_AMOUNT'); ?>
    				</label>
    				<div class="controls">
    					<?php echo EventbookingHelper::formatCurrency($amount, $config);?>
    				</div>
    			</div>
    		<?php
    		}            
    	}
    	if ($depositAmount > 0)
    	{
    	?>
    	<div class="control-group">
    		<label class="control-label">
    			<?php echo JText::_('EB_DEPOSIT_AMOUNT'); ?>
    		</label>
    		<div class="controls">
    			<?php echo EventbookingHelper::formatCurrency($depositAmount, $config); ?>
    		</div>
    	</div>
    	<div class="control-group">
    		<label class="control-label">
    			<?php echo JText::_('EB_DUE_AMOUNT'); ?>
    		</label>
    		<div class="controls">
    			<?php echo EventbookingHelper::formatCurrency($amount - $depositAmount, $config); ?>
    		</div>
    	</div>
    	<?php
    	}
    	if ($amount > 0)
    	{
    	?>
    	<div class="control-group">
    		<label class="control-label">
    			<?php echo  JText::_('EB_PAYMEMNT_METHOD'); ?>
    		</label>
    		<div class="controls">
    		<?php
    			$method = os_payments::loadPaymentMethod($row->payment_method);
    			if ($method)
    			{
    				echo JText::_($method->title) ;
    			}
    		?>
    		</div>
    	</div>
    	<div class="control-group">
    		<label class="control-label">
    			<?php echo JText::_('EB_TRANSACTION_ID'); ?>
    		</label>
    		<div class="controls">
    			<?php echo $row->transaction_id ; ?>
    		</div>
    	</div>
    	<?php
    	}       	
    ?>   
</form>