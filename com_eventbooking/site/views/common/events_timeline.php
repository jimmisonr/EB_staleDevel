<?php 
/**
 * @version        	1.7.0
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die ;
$timeFormat = $config->event_time_format ? $config->event_time_format : 'g:i a';
$dateFormat = $config->date_format;
?>
<div id="eb-events" class="eb-events-timeline">
    <?php		    	
        $activateWaitingList = $config->activate_waitinglist_feature ;                                
        for ($i = 0 , $n = count($events) ;  $i < $n ; $i++)
        {
        	$event = $events[$i] ;
        	$canRegister = EventbookingHelper::acceptRegistration($event);
            $detailUrl = JRoute::_(EventbookingHelperRoute::getEventRoute($event->id, @$category->id, $Itemid));
        	if (($event->event_capacity > 0) && ($event->event_capacity <= $event->total_registrants) && $activateWaitingList && !@$event->user_registered && $event->number_event_dates > 0)
            {
        	    $waitingList = true ;
        	}
            else
            {
        	    $waitingList = false ;
        	}			
        ?>
        <div class="eb-event-container">
	        <div class="eb-event-date-container">
		        <div class="eb-event-date btn-inverse">
			        <div class="eb-event-date-day">
				        <?php echo JHtml::_('date', $event->event_date, 'd', null); ?>
			        </div>
			        <div class="eb-event-date-month">
				        <?php echo JHtml::_('date', $event->event_date, 'M', null); ?>
			        </div>
			        <div class="eb-event-date-year">
				        <?php echo JHtml::_('date', $event->event_date, 'Y', null); ?>
			        </div>
		        </div>
	        </div>
	        <h2 class="eb-even-title-container"><a class="eb-event-title" href="<?php echo $detailUrl; ?>"><?php echo $event->title; ?></a></h2>
	        <div class="eb-event-information row-fluid">
		        <div class="span8">
			        <div class="clearfix">
						<span class="eb-event-date-info">
							<i class="icon-clock">&nbsp;</i>
							<?php echo JHtml::_('date', $event->event_date, $dateFormat, null); ?><span class="eb-time"><?php echo JHtml::_('date', $event->event_date, $timeFormat, null) ?></span>
							<?php
								if ($event->event_end_date != $nullDate)
								{
									$startDate =  JHtml::_('date', $event->event_date, 'Y-m-d', null);
									$endDate   = JHtml::_('date', $event->event_end_date, 'Y-m-d', null);
									if ($startDate == $endDate)
									{
									?>
										- <span class="eb-time"><?php echo JHtml::_('date', $event->event_end_date, $timeFormat, null) ?></span>
									<?php
									}
									else
									{
									?>
										- <?php echo JHtml::_('date', $event->event_end_date, $dateFormat, null); ?><span class="eb-time"><?php echo JHtml::_('date', $event->event_end_date, $timeFormat, null) ?></span>
									<?php
									}
								}
							?>
						</span>
			        </div>
			        <?php
			            if ($event->location_id)
			            {
				        ?>
			            <div class="clearfix">
								<i class="icon-map-marker"></i>
						        <?php
						            if ($event->location_address)
						            {
									?>
							            <a href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=map&location_id='.$event->location_id.'&tmpl=component'); ?>" class="eb-colorbox-map"><span><?php echo $event->location_name ; ?></span></a>
						            <?php
						            }
						            else
						            {
							            echo $event->location_name;
						            }
						        ?>
			            </div>
			            <?php
			            }
			        ?>
		        </div>
		        <div class="span4">
			        <div class="eb-event-price-container btn-primary">
				        <?php
				            if ($event->individual_price > 0)
				            {
					            $symbol        = $event->currency_symbol ? $event->currency_symbol : $config->currency_symbol;
					        ?>
					            <span class="eb-individual-price"><?php echo EventbookingHelper::formatAmount($event->individual_price, $config);?><span class="currency"><?php echo $symbol; ?></span></span>
				            <?php
				            }
				            elseif ($config->show_price_for_free_event)
				            {
					        ?>
					            <span class="eb-individual-price"><?php echo JText::_('EB_FREE'); ?></span>
				            <?php
				            }
				        ?>
			        </div>
		        </div>
	        </div>

	        <div class="eb-description-details">
		        <?php
			        if ($event->thumb && file_exists(JPATH_ROOT.'/media/com_eventbooking/images/thumbs/'.$event->thumb))
			        {
			        ?>
				        <a href="<?php echo JUri::base(true).'/media/com_eventbooking/images/'.$event->thumb; ?>" class="eb-modal"><img src="<?php echo JUri::base(true).'/media/com_eventbooking/images/thumbs/'.$event->thumb; ?>" class="eb-thumb-left"/></a>
			        <?php
			        }
			        if (!$event->short_description)
			        {
				        $event->short_description = $event->description;
			        }
			        echo $event->short_description;
		        ?>
	        </div>
	        <div class="eb-taskbar clearfix">
		        <ul>
			        <?php
			        if ($canRegister)
			        {
				        if ($event->registration_type == 0 || $event->registration_type == 1)
				        {
					        if ($config->multiple_booking)
					        {
						        $url        = 'index.php?option=com_eventbooking&task=add_cart&id=' . (int) $event->id . '&Itemid=' . (int) $Itemid;
						        $extraClass = 'eb-colorbox-addcart';
						        $text       = JText::_('EB_REGISTER');
					        }
					        else
					        {
						        $url        = JRoute::_('index.php?option=com_eventbooking&task=individual_registration&event_id=' . $event->id . '&Itemid=' . $Itemid, false, $ssl);
						        $text       = JText::_('EB_REGISTER_INDIVIDUAL');
						        $extraClass = '';
					        }
					        ?>
					        <li>
						        <a class="btn <?php echo $extraClass;?>"
						           href="<?php echo $url; ?>"><?php echo $text; ?></a>
					        </li>
				        <?php
				        }
				        if (($event->registration_type == 0 || $event->registration_type == 2) && !$config->multiple_booking)
				        {
					        ?>
					        <li>
						        <a class="btn" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=group_registration&event_id='.$event->id.'&Itemid='.$Itemid, false, $ssl) ; ?>"><?php echo JText::_('EB_REGISTER_GROUP');; ?></a>
					        </li>
				        <?php
				        }
			        }
			        elseif ($waitingList)
			        {
				        if ($event->registration_type == 0 || $event->registration_type == 1)
				        {
					        ?>
					        <li>
						        <a class="btn" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=individual_registration&event_id='.$event->id.'&Itemid='.$Itemid, false, $ssl);?>"><?php echo JText::_('EB_REGISTER_INDIVIDUAL_WAITING_LIST'); ; ?></a>
					        </li>
				        <?php
				        }
				        if (($event->registration_type == 0 || $event->registration_type == 2) && !$config->multiple_booking)
				        {
					        ?>
					        <li>
						        <a class="btn" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=group_registration&event_id='.$event->id.'&Itemid='.$Itemid, false, $ssl) ; ?>"><?php echo JText::_('EB_REGISTER_GROUP_WAITING_LIST'); ; ?></a>
					        </li>
				        <?php
				        }
			        }
			        $registrantId = EventbookingHelper::canCancelRegistration($event->id) ;
			        if ($registrantId !== false)
			        {
				        ?>
				        <li>
					        <a class="btn" href="javascript:cancelRegistration(<?php echo $registrantId; ?>)"><?php echo JText::_('EB_CANCEL_REGISTRATION'); ?></a>
				        </li>
			        <?php
			        }

			        if (EventbookingHelper::checkEditEvent($event->id))
			        {
				        ?>
				        <li>
					        <a class="btn" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=edit_event&id='.$event->id.'&Itemid='.$Itemid); ?>">
						        <i class="icon-pencil"></i>
						        <?php echo JText::_('EB_EDIT'); ?>
					        </a>
				        </li>
			        <?php
			        }
			        if (EventbookingHelper::canChangeEventStatus($event->id))
			        {
				        if ($event->published == 1)
				        {
					        $link = JRoute::_('index.php?option=com_eventbooking&task=unpublish_event&id='.$event->id.'&Itemid='.$Itemid);
					        $text = JText::_('EB_UNPUBLISH');
					        $class = 'icon-unpublish';
				        }
				        else
				        {
					        $link = JRoute::_('index.php?option=com_eventbooking&task=publish_event&id='.$event->id.'&Itemid='.$Itemid);
					        $text = JText::_('EB_PUBLISH');
					        $class = 'icon-publish';
				        }
				        ?>
				        <li>
					        <a class="btn" href="<?php echo $link; ?>">
						        <i class="<?php echo $class; ?>"></i>
						        <?php echo $text; ?>
					        </a>
				        </li>
			        <?php
			        }

			        if ($event->total_registrants && EventbookingHelper::canExportRegistrants($event->id))
			        {
				        ?>
				        <li>
					        <a class="btn" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=csv_export&event_id='.$event->id.'&Itemid='.$Itemid); ?>"><?php echo JText::_('EB_EXPORT_REGISTRANTS'); ?></a>
				        </li>
			        <?php
			        }
			        ?>
			        <li>
				        <a class="btn btn-primary" href="<?php echo $detailUrl; ?>">
					        <?php echo JText::_('EB_DETAILS'); ?>
				        </a>
			        </li>
		        </ul>
	        </div>
        </div>
        <?php	        			        		        		      
        }
    ?>	    
</div>

<script type="text/javascript">
    function cancelRegistration(registrantId) {
        var form = document.adminForm ;
        if (confirm("<?php echo JText::_('EB_CANCEL_REGISTRATION_CONFIRM'); ?>")) {
            form.task.value = 'cancel_registration' ;
            form.id.value = registrantId ;
            form.submit() ;
        }
    }
</script>