<?php
/**
 * @version            2.7.0
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2016 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die ;
$timeFormat        = $config->event_time_format ? $config->event_time_format : 'g:i a';
$dateFormat        = $config->date_format;
$rowFluidClass     = $bootstrapHelper->getClassMapping('row-fluid');
$span8Class        = $bootstrapHelper->getClassMapping('span8');
$span4Class        = $bootstrapHelper->getClassMapping('span4');
$btnClass          = $bootstrapHelper->getClassMapping('btn');
$btnInverseClass   = $bootstrapHelper->getClassMapping('btn-inverse');
$iconOkClass       = $bootstrapHelper->getClassMapping('icon-ok');
$iconRemoveClass   = $bootstrapHelper->getClassMapping('icon-remove');
$iconPencilClass   = $bootstrapHelper->getClassMapping('icon-pencil');
$iconDownloadClass = $bootstrapHelper->getClassMapping('icon-download');
$iconCalendarClass = $bootstrapHelper->getClassMapping('icon-calendar');
$iconMapMakerClass = $bootstrapHelper->getClassMapping('icon-map-marker');
$return = base64_encode(JUri::getInstance()->toString());
?>
<div id="eb-events" class="eb-events-timeline">
	<?php
		$activateWaitingList = $config->activate_waitinglist_feature ;
		for ($i = 0 , $n = count($events) ;  $i < $n ; $i++)
		{
			$event = $events[$i] ;
			$canRegister = EventbookingHelper::acceptRegistration($event);
			$detailUrl = JRoute::_(EventbookingHelperRoute::getEventRoute($event->id, @$category->id, $Itemid));

			if ($event->cut_off_date != $nullDate)
			{
				$registrationOpen = ($event->cut_off_minutes < 0);
			}
			else
			{
				$registrationOpen = ($event->number_event_dates > 0);
			}

			$waitingList = false ;
			if (($event->event_capacity > 0) && ($event->event_capacity <= $event->total_registrants) && $activateWaitingList && !@$event->user_registered && $registrationOpen)
			{
				$waitingList = true ;
			}

			$isMultipleDate = false;
			if ($config->show_children_events_under_parent_event && $event->event_type == 1)
			{
				$isMultipleDate = true;
			}
		?>
		<div class="eb-event-container" itemscope itemtype="http://schema.org/Event">
			<div class="eb-event-date-container">
				<div class="eb-event-date <?php echo $btnInverseClass; ?>">
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
			<h2 class="eb-even-title-container">
				<?php
					if ($config->hide_detail_button !== '1')
					{
					?>
						<a class="eb-event-title" href="<?php echo $detailUrl; ?>" itemprop="url"><span itemprop="name"><?php echo $event->title; ?></span></a>
					<?php
					}
					else
					{
						echo '<span itemprop="name">' . $event->title . '</span>';
					}
				?>
			</h2>
			<div class="eb-event-information <?php echo $rowFluidClass; ?>">				
				<div class="<?php echo $span8Class; ?>">
					<div class="clearfix">
						<span class="eb-event-date-info">
                            <?php
                                if ($event->event_date != EB_TBC_DATE)
                                {
                                ?>
                                    <meta itemprop="startDate" content="<?php echo JFactory::getDate($event->event_date)->format("Y-m-d\TH:i"); ?>">
                                <?php
                                }
                                if ($event->event_end_date != $nullDate)
                                {
                                ?>
                                    <meta itemprop="endDate" content="<?php echo JFactory::getDate($event->event_end_date)->format("Y-m-d\TH:i"); ?>">
                                <?php
                                }				
                            ?>
							<i class="<?php echo $iconCalendarClass; ?>"></i>
							<?php echo JHtml::_('date', $event->event_date, $dateFormat, null); ?>
							<?php
								if (strpos($event->event_date, '00:00:00') === false)
								{
								?>
									<span class="eb-time"><?php echo JHtml::_('date', $event->event_date, $timeFormat, null) ?></span>
								<?php
								}
								if ($event->event_end_date != $nullDate)
								{
									if (strpos($event->event_end_date, '00:00:00') === false)
									{
										$showTime = true;
									}
									else
									{
										$showTime = false;
									}
									$startDate =  JHtml::_('date', $event->event_date, 'Y-m-d', null);
									$endDate   = JHtml::_('date', $event->event_end_date, 'Y-m-d', null);
									if ($startDate == $endDate)
									{
										if ($showTime)
										{
										?>
											-<span class="eb-time"><?php echo JHtml::_('date', $event->event_end_date, $timeFormat, null) ?></span>
										<?php
										}
									}
									else
									{
										echo " - " .JHtml::_('date', $event->event_end_date, $dateFormat, null);
										if ($showTime)
										{
										?>
											<span class="eb-time"><?php echo JHtml::_('date', $event->event_end_date, $timeFormat, null) ?></span>
										<?php
										}
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
								<i class="<?php echo $iconMapMakerClass; ?>"></i>
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
				<div class="<?php echo $span4Class; ?>">
					<div class="eb-event-price-container btn-primary">
						<?php
							if ($config->show_discounted_price)
							{
								$price = $event->discounted_price;
							}
							else
							{
								$price = $event->individual_price;
							}
							if ($price > 0)
							{
								$symbol        = $event->currency_symbol ? $event->currency_symbol : $config->currency_symbol;
							?>
								<span class="eb-individual-price"><?php echo EventbookingHelper::formatCurrency($price, $config, $symbol);?></span>
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

			<div class="eb-description-details" itemprop="description">
				<?php
					if ($event->thumb && file_exists(JPATH_ROOT.'/media/com_eventbooking/images/thumbs/'.$event->thumb))
					{
					?>
						<a href="<?php echo JUri::base(true).'/media/com_eventbooking/images/'.$event->thumb; ?>" class="eb-modal"><img src="<?php echo JUri::base(true).'/media/com_eventbooking/images/thumbs/'.$event->thumb; ?>" class="eb-thumb-left"/></a>
					<?php
					}
					echo $event->short_description;
				?>
			</div>
            <?php
                $ticketsLeft = $event->event_capacity - $event->total_registrants ;
				if ($event->individual_price > 0 || $ticketsLeft > 0)
				{
				?>
					<div style="display:none;" itemprop="offers" itemscope itemtype="http://schema.org/AggregateOffer">
						<?php
						if ($event->individual_price > 0)
						{
						?>
							<span itemprop="lowPrice"><?php echo EventbookingHelper::formatCurrency($event->individual_price, $config, $event->currency_symbol); ?></span>
						<?php
						}

						if ($ticketsLeft > 0)
						{
						?>
							<span itemprop="offerCount"><?php echo $ticketsLeft;?></span>
						<?php
						}
						?>
					</div>
				<?php
				} 
            ?>
			<div class="eb-taskbar clearfix">
				<ul>
					<?php
					if (!$isMultipleDate)
					{
						if ($canRegister)
						{
							$registrationUrl = trim($event->registration_handle_url);
							if ($registrationUrl)
							{
								?>
								<li>
									<a class="<?php echo $btnClass; ?>" href="<?php echo $registrationUrl; ?>" target="_blank"><?php echo JText::_('EB_REGISTER');; ?></a>
								</li>
								<?php
							}
							else
							{
								if ($event->registration_type == 0 || $event->registration_type == 1)
								{
									if ($config->multiple_booking)
									{
										$url        = 'index.php?option=com_eventbooking&task=cart.add_cart&id=' . (int) $event->id . '&Itemid=' . (int) $Itemid;
										if ($event->event_password)
										{
											$extraClass = '';
										}
										else
										{
											$extraClass = 'eb-colorbox-addcart';
										}
										$text       = JText::_('EB_REGISTER');
									}
									else
									{
										$url        = JRoute::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id=' . $event->id . '&Itemid=' . $Itemid, false, $ssl);
										$text       = JText::_('EB_REGISTER_INDIVIDUAL');
										$extraClass = '';
									}
									?>
									<li>
										<a class="<?php echo $btnClass.' '.$extraClass;?>"
										   href="<?php echo $url; ?>"><?php echo $text; ?></a>
									</li>
									<?php
								}
								if (($event->registration_type == 0 || $event->registration_type == 2) && !$config->multiple_booking)
								{
									?>
									<li>
										<a class="<?php echo $btnClass; ?>" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=register.group_registration&event_id='.$event->id.'&Itemid='.$Itemid, false, $ssl) ; ?>"><?php echo JText::_('EB_REGISTER_GROUP');; ?></a>
									</li>
									<?php
								}
							}
						}
						elseif ($waitingList)
						{
							if ($event->registration_type == 0 || $event->registration_type == 1)
							{
								?>
								<li>
									<a class="<?php echo $btnClass; ?>" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id='.$event->id.'&Itemid='.$Itemid, false, $ssl);?>"><?php echo JText::_('EB_REGISTER_INDIVIDUAL_WAITING_LIST'); ; ?></a>
								</li>
								<?php
							}
							if (($event->registration_type == 0 || $event->registration_type == 2) && !$config->multiple_booking)
							{
								?>
								<li>
									<a class="<?php echo $btnClass; ?>" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=register.group_registration&event_id='.$event->id.'&Itemid='.$Itemid, false, $ssl) ; ?>"><?php echo JText::_('EB_REGISTER_GROUP_WAITING_LIST'); ; ?></a>
								</li>
								<?php
							}
						}

						if ($config->show_save_to_personal_calendar)
						{
							?>
							<li>
								<?php echo EventbookingHelperHtml::loadCommonLayout('common/tmpl/save_calendar.php', array('item' => $event, 'Itemid' => $Itemid)); ?>
							</li>
							<?php
						}
						$registrantId = EventbookingHelper::canCancelRegistration($event->id) ;
						if ($registrantId !== false)
						{
							?>
							<li>
								<a class="<?php echo $btnClass; ?>" href="javascript:cancelRegistration(<?php echo $registrantId; ?>)"><?php echo JText::_('EB_CANCEL_REGISTRATION'); ?></a>
							</li>
							<?php
						}

						if (EventbookingHelper::checkEditEvent($event->id))
						{
							?>
							<li>
								<a class="<?php echo $btnClass; ?>" href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=event&layout=form&id='.$event->id.'&Itemid='.$Itemid.'&return='.$return); ?>">
									<i class="<?php echo $iconPencilClass; ?>"></i>
									<?php echo JText::_('EB_EDIT'); ?>
								</a>
							</li>
							<?php
						}
						if (EventbookingHelper::canChangeEventStatus($event->id))
						{
							if ($event->published == 1)
							{
								$link = JRoute::_('index.php?option=com_eventbooking&task=event.unpublish&id='.$event->id.'&Itemid='.$Itemid.'&return='.$return);
								$text = JText::_('EB_UNPUBLISH');
								$class = $iconRemoveClass;
							}
							else
							{
								$link = JRoute::_('index.php?option=com_eventbooking&task=event.publish&id='.$event->id.'&Itemid='.$Itemid.'&return='.$return);
								$text = JText::_('EB_PUBLISH');
								$class = $iconOkClass;
							}
							?>
							<li>
								<a class="<?php echo $btnClass; ?>" href="<?php echo $link; ?>">
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
								<a class="<?php echo $btnClass; ?>" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=registrant.export&event_id='.$event->id.'&Itemid='.$Itemid); ?>">
									<i class="<?php echo $iconDownloadClass; ?>"></i>
									<?php echo JText::_('EB_EXPORT_REGISTRANTS'); ?>
								</a>
							</li>
							<?php
						}
					}
					elseif ($config->show_save_to_personal_calendar)
					{

					?>
						<li>
							<?php echo EventbookingHelperHtml::loadCommonLayout('common/tmpl/save_calendar.php', array('item' => $event, 'Itemid' => $Itemid)); ?>
						</li>
					<?php
					}
					if ($config->hide_detail_button !== '1' || $isMultipleDate)
					{
					?>
						<li>
							<a class="<?php echo $btnClass; ?> btn-primary" href="<?php echo $detailUrl; ?>">
								<?php echo $isMultipleDate ? JText::_('EB_CHOOSE_DATE_LOCATION') : JText::_('EB_DETAILS'); ?>
							</a>
						</li>
					<?php
					}
					?>
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
			form.task.value = 'registrant.cancel' ;
			form.id.value = registrantId ;
			form.submit() ;
		}
	}
</script>