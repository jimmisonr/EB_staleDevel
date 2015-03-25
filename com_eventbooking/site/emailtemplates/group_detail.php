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
defined('_JEXEC') or die;	  
?>
<form id="adminForm" class="form form-horizontal">
    <div class="control-group">
            <h3 class="eb-heading"><?php echo JText::_('EB_GENERAL_INFORMATION') ; ?></h3>
    </div>
    <div class="control-group">
        <label class="control-label">
            <?php echo  JText::_('EB_EVENT_TITLE') ?>
        </label>
        <div class="controls">
            <?php echo $rowEvent->title ; ?>
        </div>
    </div>
    <?php
        if ($config->show_event_date)
        {
        ?>
        <div class="control-group">
            <label class="control-label">
                <?php echo  JText::_('EB_EVENT_DATE') ?>
            </label>
            <div class="controls">
                <?php
                        if ($rowEvent->event_date == EB_TBC_DATE)
                        {
                            echo JText::_('EB_TBC');
                        }
                        else
                        {
                            echo JHtml::_('date', $rowEvent->event_date, $config->event_date_format, null) ;
                        }
                    ?>
            </div>
        </div>
        <?php
        }
        if ($config->show_event_location_in_email && $rowLocation)
        {
            $location = $rowLocation ;
            $locationInformation = array();
            if ($location->address)
            {
            	$locationInformation[] = $location->address;
            }
            if ($location->city)
            {
            	$locationInformation[] = $location->city;
            }
            if ($location->state)
            {
            	$locationInformation[] = $location->state;
            }
            if ($location->zip)
            {
            	$locationInformation[] = $location->zip;
            }
            if ($location->country)
            {
            	$locationInformation[] = $location->country;
            }
        ?>
            <div class="control-group">
                <label class="control-label">
                    <?php echo  JText::_('EB_LOCATION') ?>
                </label>
                <div class="controls">
                    <?php echo $location->name.' ('.implode(', ', $locationInformation).')' ; ?>
                </div>
            </div>
        <?php
        }
    ?>
    <div class="control-group">
        <label class="control-label">
            <?php echo  JText::_('EB_NUMBER_REGISTRANTS') ?>
        </label>
        <div class="controls">
            <?php echo $row->number_registrants ; ?>
        </div>
    </div>
    <?php
        $showBillingStep = EventbookingHelper::showBillingStep($row->event_id);
        if ($showBillingStep)
        {
        ?>
            <div class="control-group">
                <h3 class="eb-heading"><?php echo JText::_('EB_BILLING_INFORMATION') ; ?></h3>
            </div>
        <?php
            //Show data for form
            $fields = $form->getFields();
            foreach ($fields as $field)
            {
	            if ($field->hideOnDisplay)
	            {
		            continue;
	            }
                echo $field->getOutput();
            }
            if ($row->total_amount > 0)
            {
                ?>
                <div class="control-group">
                    <label class="control-label">
                        <?php echo JText::_('EB_AMOUNT'); ?>
                    </label>
                    <div class="controls">
                        <?php echo EventbookingHelper::formatCurrency($row->total_amount, $config, $rowEvent->currency_symbol); ?>
                    </div>
                </div>
                <?php
                if ($row->discount_amount > 0)
                {
                    ?>
                    <div class="control-group">
                        <label class="control-label">
                            <?php echo  JText::_('EB_DISCOUNT_AMOUNT'); ?>
                        </label>
                        <div class="controls">
                            <?php echo EventbookingHelper::formatCurrency($row->discount_amount, $config, $rowEvent->currency_symbol); ?>
                        </div>
                    </div>
                <?php
                }
                if ($row->tax_amount > 0)
                {
                    ?>
                    <div class="control-group">
                        <label class="control-label">
                            <?php echo  JText::_('EB_TAX'); ?>
                        </label>
                        <div class="controls">
                            <?php echo EventbookingHelper::formatCurrency($row->tax_amount, $config, $rowEvent->currency_symbol); ?>
                        </div>
                    </div>
                <?php
                }
	            if ($row->payment_processing_fee > 0)
	            {
		        ?>
		            <div class="control-group">
			            <label class="control-label">
				            <?php echo  JText::_('EB_PAYMENT_FEE'); ?>
			            </label>
			            <div class="controls">
				            <?php echo EventbookingHelper::formatCurrency($row->payment_processing_fee, $config, $rowEvent->currency_symbol); ?>
			            </div>
		            </div>
	            <?php
	            }
                if ($row->discount_amount > 0 || $row->tax_amount > 0 || $row->payment_processing_fee > 0)
                {
                ?>
                    <div class="control-group">
                        <label class="control-label">
                            <?php echo  JText::_('EB_GROSS_AMOUNT'); ?>
                        </label>
                        <div class="controls">
                            <?php echo EventbookingHelper::formatCurrency($row->amount, $config, $rowEvent->currency_symbol) ; ?>
                        </div>
                    </div>
                <?php
                }
            }
            if ($row->deposit_amount > 0)
            {
                ?>
                <div class="control-group">
                    <label class="control-label">
                        <?php echo JText::_('EB_DEPOSIT_AMOUNT'); ?>
                    </label>
                    <div class="controls">
                        <?php echo EventbookingHelper::formatCurrency($row->deposit_amount, $config, $rowEvent->currency_symbol); ?>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">
                        <?php echo JText::_('EB_DUE_AMOUNT'); ?>
                    </label>
                    <div class="controls">
                        <?php echo EventbookingHelper::formatCurrency($row->amount - $row->deposit_amount, $config, $rowEvent->currency_symbol); ?>
                    </div>
                </div>
            <?php
            }
            if ($row->amount > 0)
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
        }
        if ($config->collect_member_information && count($rowMembers))
        {
        ?>
            <div class="control-group">
                <h3 class="eb-heading"><?php echo JText::_('EB_MEMBERS_INFORMATION') ; ?></h3>
            </div>
            <?php
            	$rowFields = EventbookingHelper::getFormFields($row->event_id, 2);
            	$memberForm = new RADForm($rowFields);                           
                for ($i = 0 , $n  = count($rowMembers); $i < $n; $i++)
                {
                    $rowMember = $rowMembers[$i] ;
                    if ($i %2 == 0)
                    {                    	
                        echo "<div class=\"row-fluid\">\n" ;                        
                    }  
                    $memberData = EventbookingHelper::getRegistrantData($rowMember, $rowFields);
                    $memberForm->bind($memberData); 
                    
                    //Build dependency
                    $memberForm->buildFieldsDependency();
                    $fields = $memberForm->getFields();
                    foreach ($fields as $field)
                    {
                    	if ($field->hideOnDisplay)
                    	{
                    		unset($fields[$field->name]);
                    	}
                    }
                    $memberForm->setFields($fields);
                ?>
                <div class="span6">
                    <div class="control-group">
                        <h4 class="eb-heading"><?php echo JText::sprintf('EB_MEMBER_INFORMATION', $i + 1) ; ?></h4>
                    </div>
                    <?php
	                    $fields = $memberForm->getFields();
	                    foreach ($fields as $field)
	                    {
	                    	echo $field->getOutput();
	                    }	
                    ?>
                    </div>
                <?php
                if (($i + 1) %2 == 0)
                {
                    echo "</div>\n" ;
                }
            }
            if ($i %2 != 0)
            {
                echo "</div>" ;
            }
        }        
    ?>
</form>