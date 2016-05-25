<?php
/**
 * @version            2.6.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

EventbookingHelperJquery::colorbox('a.eb-modal');

$item = $this->item ;
$url = JRoute::_(EventbookingHelperRoute::getEventRoute($item->id, 0, $this->Itemid), false);
$canRegister = EventbookingHelper::acceptRegistration($item) ;
$socialUrl = JUri::getInstance()->toString(array('scheme', 'user', 'pass', 'host')).JRoute::_(EventbookingHelperRoute::getEventRoute($item->id, 0, $this->Itemid));
if ($this->config->use_https)
{
	$ssl = 1 ;
}
else
{
	$ssl = 0 ;
}

$bootstrapHelper   = $this->bootstrapHelper;
$iconPencilClass   = $bootstrapHelper->getClassMapping('icon-pencil');
$iconOkClass       = $bootstrapHelper->getClassMapping('icon-ok');
$iconRemoveClass   = $bootstrapHelper->getClassMapping('icon-remove');
$iconDownloadClass = $bootstrapHelper->getClassMapping('icon-download');
$btnClass          = $bootstrapHelper->getClassMapping('btn');
$return = base64_encode(JUri::getInstance()->toString());

if ($item->cut_off_date != JFactory::getDbo()->getNullDate())
{
	$registrationOpen = ($item->cut_off_minutes < 0);
}
else
{
	$registrationOpen = ($item->number_event_dates > 0);
}

$offset = JFactory::getConfig()->get('offset');
?>
<div id="eb-event-page" class="eb-container eb-event" itemscope itemtype="http://schema.org/Event">
	<div class="eb-box-heading clearfix">
		<h1 class="eb-page-heading">
			<span itemprop="name"><?php echo $item->title; ?></span>
		</h1>
	</div>
	<div id="eb-event-details" class="eb-description">
		<?php
			if ($this->config->show_fb_like_button)
			{
				$document = JFactory::getDocument();
				$document->addCustomTag('<meta property="og:title" content="'.$item->title.'"/>');
				if ($item->thumb && file_exists(JPATH_ROOT.'/media/com_eventbooking/images/thumbs/'.$item->thumb))
				{
					$document->addCustomTag('<meta property="og:image" content="'.JUri::base().'media/com_eventbooking/images/thumbs/'.$item->thumb.'"/>');
				}
				$document->addCustomTag('<meta property="og:url" content="'.JUri::getInstance()->toString().'"/>');
				$document->addCustomTag('<meta property="og:description" content="'.$item->title.'"/>');
				$document->addCustomTag('<meta property="og:site_name" content="'.JFactory::getConfig()->get('sitename').'"/>');
			?>
				<div class="sharing clearfix" >
					<!-- FB -->
					<div style="float:left;" id="rsep_fb_like">
						<div id="fb-root"></div>
						<script src="https://connect.facebook.net/en_US/all.js" type="text/javascript"></script>
						<script type="text/javascript">
							FB.init({appId: '340486642645761', status: true, cookie: true, xfbml: true});
						</script>
						<fb:like href="<?php echo $socialUrl; ?>" send="true" layout="button_count" width="150" show_faces="false"></fb:like>
					</div>

					<!-- Twitter -->
					<div style="float:left;" id="rsep_twitter">
						<a href="https://twitter.com/share" class="twitter-share-button" data-text="<?php echo $this->item->title." ".$socialUrl; ?>">Tweet</a>
						<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
					</div>

					<!-- GPlus -->
					<div style="float:left;" id="rsep_gplus">
						<!-- Place this tag where you want the +1 button to render -->
						<g:plusone size="medium"></g:plusone>

						<!-- Place this render call where appropriate -->
						<script type="text/javascript">
							(function() {
								var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
								po.src = 'https://apis.google.com/js/plusone.js';
								var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
							})();
						</script>
					</div>
				</div>
			<?php
			}
		?>
		<div class="eb-description-details clearfix" itemprop="description">
			<?php
				if ($item->thumb && file_exists(JPATH_ROOT.'/media/com_eventbooking/images/thumbs/'.$item->thumb))
				{
				?>
					<a href="<?php echo JUri::base(true).'/media/com_eventbooking/images/'.$item->thumb; ?>" class="eb-modal"><img src="<?php echo JUri::base(true).'/media/com_eventbooking/images/thumbs/'.$item->thumb; ?>" class="eb-thumb-left"/></a>
				<?php
				}
				echo $item->description ;
			?>
		</div>
		<div id="eb-event-info" class="clearfix <?php echo $bootstrapHelper->getClassMapping('row-fluid'); ?>">
		<div id="eb-event-info-left" class="<?php echo $bootstrapHelper->getClassMapping('span8'); ?>">
			<h3 id="eb-event-properties-heading">
				<?php echo JText::_('EB_EVENT_PROPERTIES'); ?>
			</h3>
			<table class="table table-bordered table-striped">
				<tbody>
					<tr>
						<td style="width: 30%;">
							<strong><?php echo JText::_('EB_EVENT_DATE') ?></strong>
						</td>
						<td>
							<?php
							   if ($item->event_date == EB_TBC_DATE)
							   {
								   echo JText::_('EB_TBC');
							   }
							   else
							   {
							   ?>
								   <meta itemprop="startDate" content="<?php echo JFactory::getDate($item->event_date)->format("Y-m-d\TH:i"); ?>">
							   <?php
								   if (strpos($item->event_date, '00:00:00') !== false)
								   {
									   $dateFormat = $this->config->date_format;
								   }
								   else
								   {
									   $dateFormat = $this->config->event_date_format;
								   }
								   echo JHtml::_('date', $item->event_date, $dateFormat, null) ;
							   }
							?>
						</td>
					</tr>
					<?php
						if ($item->event_end_date != $this->nullDate)
						{
							if (strpos($item->event_end_date, '00:00:00') !== false)
							{
								$dateFormat = $this->config->date_format;
							}
							else
							{
								$dateFormat = $this->config->event_date_format;
							}
							?>
							<tr>
								<td>
									<strong><?php echo JText::_('EB_EVENT_END_DATE'); ?></strong>
								</td>
								<td>
									<meta itemprop="endDate" content="<?php echo JFactory::getDate($item->event_end_date)->format("Y-m-d\TH:i"); ?>">
									<?php echo JHtml::_('date', $item->event_end_date, $dateFormat, null) ; ?>
								</td>
							</tr>
						<?php
						}

						if ($item->registration_start_date != $this->nullDate)
						{
							if (strpos($item->registration_start_date, '00:00:00') !== false)
							{
								$dateFormat = $this->config->date_format;
							}
							else
							{
								$dateFormat = $this->config->event_date_format;
							}
						?>
							<tr>
								<td>
									<strong><?php echo JText::_('EB_REGISTRATION_START_DATE'); ?></strong>
								</td>
								<td>
									<?php echo JHtml::_('date', $item->registration_start_date, $dateFormat, null);?>
								</td>
							</tr>
						<?php
						}

					if ($this->config->show_capacity == 1 || ($this->config->show_capacity == 2 && $item->event_capacity))
						{
						?>
							<tr>
								<td>
									<strong><?php echo JText::_('EB_CAPACITY'); ?></strong>
								</td>
								<td>
									<?php
										if ($item->event_capacity)
										{
											echo $item->event_capacity ;
										}
										else
										{
											echo JText::_('EB_UNLIMITED') ;
										}
									?>
								</td>
							</tr>
						<?php
						}
						if ($this->config->show_registered && $item->registration_type != 3)
						{
						?>
							<tr>
								<td>
									<strong><?php echo JText::_('EB_REGISTERED'); ?></strong>
								</td>
								<td>
									<?php echo $item->total_registrants ; ?>
									<?php
									if ($this->config->show_list_of_registrants && ($item->total_registrants > 0) && EventbookingHelper::canViewRegistrantList())
									{
									?>
											&nbsp;&nbsp;&nbsp;
											<a href="index.php?option=com_eventbooking&view=registrantlist&id=<?php echo $item->id ?>&tmpl=component" class="eb-colorbox-register-lists"><span class="view_list"><?php echo JText::_("EB_VIEW_LIST"); ?></span></a>
									<?php
									}
									?>
								</td>
							</tr>
						<?php
						}
						if ($this->config->show_available_place && $item->event_capacity)
						{
						?>
							<tr>
								<td>
									<strong><?php echo JText::_('EB_AVAILABLE_PLACE'); ?></strong>
								</td>
								<td>
									<?php echo $item->event_capacity - $item->total_registrants ; ?>
								</td>
							</tr>
						<?php
						}
						if ($this->nullDate != $item->cut_off_date)
						{
							if (strpos($item->cut_off_date, '00:00:00') !== false)
							{
								$dateFormat = $this->config->date_format;
							}
							else
							{
								$dateFormat = $this->config->event_date_format;
							}
						?>
						<tr>
							<td>
								<strong><?php echo JText::_('EB_CUT_OFF_DATE'); ?></strong>
							</td>
							<td>
								<?php echo JHtml::_('date', $item->cut_off_date, $dateFormat, null) ; ?>
							</td>
						</tr>
						<?php
						}
						if (($item->individual_price > 0) || ($this->config->show_price_for_free_event))
						{
							$showPrice = true ;
						}
						else
						{
							$showPrice = false ;
						}
						if ($this->config->show_discounted_price && ($item->individual_price != $item->discounted_price))
						{
							if ($showPrice)
							{
							?>
								<tr>
									<td>
										<strong><?php echo JText::_('EB_ORIGINAL_PRICE'); ?></strong>
									</td>
									<td class="eb_price">
										<?php
											if ($item->individual_price > 0)
												echo EventbookingHelper::formatCurrency($item->individual_price, $this->config, $item->currency_symbol) ;
											else
												echo '<span class="eb_free">'.JText::_('EB_FREE').'</span>' ;
										?>
									</td>
								</tr>
								<tr>
									<td>
										<strong><?php echo JText::_('EB_DISCOUNTED_PRICE'); ?></strong>
									</td>
									<td class="eb_price">
										<?php
											if ($item->discounted_price > 0)
											{
												echo EventbookingHelper::formatCurrency($item->discounted_price, $this->config, $item->currency_symbol);
												if ($item->early_bird_discount_amount > 0 && $item->early_bird_discount_date != $this->nullDate)
												{
													echo ' <em> '.JText::sprintf('EB_UNTIl_DATE', JHtml::_('date', $item->early_bird_discount_date, $this->config->date_format, null)).'</em>';
												}
											}
											else
											{
												echo '<span class="eb_free">'.JText::_('EB_FREE').'</span>' ;
											}
										?>
									</td>
								</tr>
							<?php
							}
						}
						else
						{
							if ($showPrice)
							{
							?>
								<tr>
									<td>
										<strong><?php echo JText::_('EB_INDIVIDUAL_PRICE'); ?></strong>
									</td>
									<td class="eb_price">
										<?php
											if ($item->individual_price > 0)
											{
												echo EventbookingHelper::formatCurrency($item->individual_price, $this->config, $item->currency_symbol) ;
											}
											else
											{
												echo '<span class="eb_free">'.JText::_('EB_FREE').'</span>' ;
											}
										?>
									</td>
								</tr>
							<?php
							}
						}

						if ($item->fixed_group_price > 0)
						{
						?>
							<tr>
								<td>
									<strong><?php echo JText::_('EB_FIXED_GROUP_PRICE'); ?></strong>
								</td>
								<td class="eb_price">
									<?php
										echo EventbookingHelper::formatCurrency($item->fixed_group_price, $this->config, $item->currency_symbol) ;
									?>
								</td>
							</tr>
						<?php
						}

						if ($item->late_fee > 0)
						{
						?>
							<tr class="eb-event-property">
								<td class="eb-event-property-label">
									<?php echo JText::_('EB_LATE_FEE'); ?>
								</td>
								<td class="eb-event-property-value">
									<?php
									echo EventbookingHelper::formatCurrency($item->late_fee, $this->config, $item->currency_symbol);
									echo ' <em> '.JText::sprintf('EB_FROM_DATE', JHtml::_('date', $item->late_fee_date, $this->config->date_format, null)).'</em>';
									?>
								</td>
							</tr>
						<?php
						}

						if ($this->config->show_event_creator)
						{
						?>
							<tr class="eb-event-property">
								<td class="eb-event-property-label">
									<?php echo JText::_('EB_CREATED_BY'); ?>
								</td>
								<td class="eb-event-property-value">
									<a href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=search&created_by=' . $item->created_by . '&Itemid=' . $this->Itemid); ?>"><?php echo $item->creator_name; ?></a>
								</td>
							</tr>
						<?php
						}
						if ($this->config->event_custom_field)
						{
							foreach($this->paramData as $param)
							{
								if ($param['value'])
								{
								?>
									<tr>
										<td>
											<strong><?php echo $param['title']; ?></strong>
										</td>
										<td>
											<?php echo $param['value'] ; ?>
										</td>
									</tr>
								<?php
								}
							}
						}
						if ($item->location_id)
						{
							$width = (int) $this->config->map_width ;
							if (!$width)
							{
								$width = 500 ;
							}
							$height = (int) $this->config->map_height ;
							if (!$height)
							{
								$height = 450 ;
							}
						?>
							<tr>
								<td>
									<strong><?php echo JText::_('EB_LOCATION'); ?></strong>
								</td>
								<td>
									<?php
										if ($this->location->address)
										{
										?>
											<div style="display:none" itemprop="location" itemscope itemtype="http://schema.org/Place">
												<div itemprop="name"><?php echo $this->location->name; ?></div>
												<div itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
												<span itemprop="streetAddress">
													<?php echo $this->location->address; ?>
												</span>
												<?php
													if ($this->location->city && $this->location->state && $this->location->zip)
													{
													?>
														<span itemprop="addressLocality"><?php echo $this->location->city; ?></span>,
														<span itemprop="addressRegion"><?php echo $this->location->state; ?></span>
														<span itemprop="postalCode"><?php echo $this->location->zip; ?></span>
														<span itemprop="addressCountry"><?php echo $this->location->country; ?></span>
													<?php
													}
												?>
												</div>
											</div>
											<a href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=map&location_id='.$item->location_id.'&tmpl=component&format=html'); ?>" class="eb-colorbox-map" title="<?php echo $this->location->name ; ?>"><?php echo $this->location->name ; ?></a>
										<?php
										}
										else
										{
											echo $this->location->name;
										}
									?>
								</td>
							</tr>
						<?php
						}
						if ($item->attachment && !empty($this->config->show_attachment_in_frontend))
						{
						?>
							<tr>
								<td>
									<strong><?php echo JText::_('EB_ATTACHMENT'); ?></strong>
								</td>
								<td>
									<?php
										$attachments = explode('|', $item->attachment);
										for ($i = 0, $n = count($attachments) ; $i < $n; $i++)
										{
											$attachment = $attachments[$i];
											if ($i > 0)
											{
												echo '<br />';
											}
											?>
												<a href="<?php echo JUri::base().'/media/com_eventbooking/'.$attachment;?>" target="_blank"><?php echo $attachment; ?></a>
											<?php
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
			$activateWaitingList = $this->config->activate_waitinglist_feature ;
			if (($item->event_capacity > 0) && ($item->event_capacity <= $item->total_registrants) && $activateWaitingList && !@$item->user_registered && $registrationOpen)
			{
				$waitingList = true ;
			}
			else
			{
				$waitingList = false ;
			}
			if (!$canRegister && $item->registration_type != 3 && $this->config->display_message_for_full_event && !$waitingList && $item->registration_start_minutes >= 0)
			{
				if (@$item->user_registered)
				{
					$msg = JText::_('EB_YOU_REGISTERED_ALREADY');
				}
				elseif (!in_array($item->registration_access, $this->viewLevels))
				{
					$msg = JText::_('EB_LOGIN_TO_REGISTER') ;
				}
				else
				{
					$msg = JText::_('EB_NO_LONGER_ACCEPT_REGISTRATION') ;
				}
			?>
				<div class="text-info eb-notice-message"><?php echo $msg ; ?></div>
			<?php
			}
		?>
		</div>
		<div id="eb-event-info-right" class="<?php echo $bootstrapHelper->getClassMapping('span4'); ?>">
			<?php
				if (count($this->rowGroupRates))
				{
				?>
					<h3 id="eb-event-group-rates-heading">
						<?php echo JText::_('EB_GROUP_RATE'); ?>
					</h3>
					<table class="table table-bordered table-striped">
						<thead>
							<tr>
								<th class="eb_number_registrant_column">
									<?php echo JText::_('EB_NUMBER_REGISTRANTS'); ?>
								</th>
								<th class="sectiontableheader eb_rate_column">
									<?php echo JText::_('EB_RATE_PERSON'); ?>(<?php echo $this->item->currency_symbol ? $this->item->currency_symbol : $this->config->currency_symbol; ?>)
								</th>
							</tr>
						</thead>
						<tbody>
						<?php
							$i = 0 ;
							if ($this->config->show_price_including_tax)
							{
								$taxRate = $this->item->tax_rate;
							}
							else
							{
								$taxRate = 0;
							}
							foreach ($this->rowGroupRates as $rowRate)
							{
								$groupRate = round($rowRate->price * (1 + $taxRate / 100), 2);
							?>
							<tr>
								<td class="eb_number_registrant_column">
									<?php echo $rowRate->registrant_number ; ?>
								</td>
								<td class="eb_rate_column">
									<?php echo EventbookingHelper::formatAmount($groupRate, $this->config); ?>
								</td>
							</tr>
							<?php
							}
						?>
						</tbody>
					</table>
				<?php
				}
			?>
		</div>
	</div>
	<div class="clearfix"></div>
	<?php
	$ticketsLeft = $item->event_capacity - $item->total_registrants ;
	if ($item->individual_price > 0 || $ticketsLeft > 0)
	{
	?>
		<div style="display:none;" itemprop="offers" itemscope itemtype="http://schema.org/AggregateOffer">
			<meta itemprop="url" content="<?php echo JUri::getInstance()->toString();?>">
			<?php
			if ($item->individual_price > 0)
			{
			?>
				<span itemprop="lowPrice"><?php echo EventbookingHelper::formatCurrency($item->individual_price, $this->config, $item->currency_symbol); ?></span>
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
	if ($this->showTaskBar)
	{
	?>
		<div class="eb-taskbar clearfix">
			<ul>
				<?php
					if ($canRegister)
					{
						$registrationUrl = trim($item->registration_handle_url);
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
							if ($item->registration_type == 0 || $item->registration_type == 1)
							{
								if ($this->config->multiple_booking)
								{
									$url        = 'index.php?option=com_eventbooking&task=cart.add_cart&id=' . (int) $item->id . '&Itemid=' . (int) $this->Itemid;
									if ($item->event_password)
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
									$url        = JRoute::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id=' . $item->id . '&Itemid=' . $this->Itemid, false, $ssl);
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
							if (($item->registration_type == 0 || $item->registration_type == 2) && !$this->config->multiple_booking)
							{
							?>
								<li>
									<a class="<?php echo $btnClass; ?>" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=register.group_registration&event_id='.$item->id.'&Itemid='.$this->Itemid, false, $ssl) ; ?>"><?php echo JText::_('EB_REGISTER_GROUP');; ?></a>
								</li>
							<?php
							}
						}
					}
					elseif ($waitingList)
					{
						if ($item->registration_type == 0 || $item->registration_type == 1)
						{
						?>
							<li>
								<a class="<?php echo $btnClass; ?>" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=register.individual_registration&event_id='.$item->id.'&Itemid='.$this->Itemid, false, $ssl);?>"><?php echo JText::_('EB_REGISTER_INDIVIDUAL_WAITING_LIST'); ; ?></a>
							</li>
						<?php
						}
						if (($item->registration_type == 0 || $item->registration_type == 2) && !$this->config->multiple_booking)
						{
						?>
							<li>
								<a class="<?php echo $btnClass; ?>" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=register.group_registration&event_id='.$item->id.'&Itemid='.$this->Itemid, false, $ssl) ; ?>"><?php echo JText::_('EB_REGISTER_GROUP_WAITING_LIST'); ; ?></a>
							</li>
						<?php
						}
					}
					if ($this->config->show_save_to_personal_calendar)
					{
					?>
						<li>
							<?php echo EventbookingHelperHtml::loadCommonLayout('common/tmpl/save_calendar.php', array('item' => $item, 'Itemid' => $this->Itemid)); ?>
						</li>
					<?php
					}
					if ($this->config->show_invite_friend && $registrationOpen)
					{
					?>
						<li>
							<a class="<?php echo $btnClass; ?> eb-colorbox-invite" href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=invite&tmpl=component&id='.$item->id.'&Itemid='.$this->Itemid, false) ; ?>"><?php echo JText::_('EB_INVITE_FRIEND'); ?></a>
						</li>
					<?php
					}

					$registrantId = EventbookingHelper::canCancelRegistration($item->id) ;
					if ($registrantId !== false)
					{
					?>
						<li>
							<a class="<?php echo $btnClass; ?>" href="javascript:cancelRegistration(<?php echo $registrantId; ?>)"><?php echo JText::_('EB_CANCEL_REGISTRATION'); ?></a>
						</li>
					<?php
					}

					if (EventbookingHelper::checkEditEvent($item->id))
					{
					?>
						<li>
							<a class="<?php echo $btnClass; ?>" href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=event&layout=form&id='.$item->id.'&Itemid='.$this->Itemid.'&return='.$return); ?>">
								<i class="<?php echo $iconPencilClass; ?>"></i>
								<?php echo JText::_('EB_EDIT'); ?>
							</a>
						</li>
					<?php
					}
					if (EventbookingHelper::canChangeEventStatus($item->id))
					{
						if ($item->published == 1)
						{
							$link = JRoute::_('index.php?option=com_eventbooking&task=event.unpublish&id='.$item->id.'&Itemid='.$this->Itemid.'&return='.$return);
							$text = JText::_('EB_UNPUBLISH');
							$class = $iconRemoveClass;
						}
						else
						{
							$link = JRoute::_('index.php?option=com_eventbooking&task=event.publish&id='.$item->id.'&Itemid='.$this->Itemid.'&return='.$return);
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
					if ($item->total_registrants && EventbookingHelper::canExportRegistrants($item->id))
					{
					?>
					   <li>
							<a class="<?php echo $btnClass; ?>" href="<?php echo JRoute::_('index.php?option=com_eventbooking&task=registrant.export&event_id='.$item->id.'&Itemid='.$this->Itemid); ?>">
								 <i class="<?php echo $iconDownloadClass; ?>"></i>
								<?php echo JText::_('EB_EXPORT_REGISTRANTS'); ?>
							</a>
					   </li>
					<?php
					}
					?>
			</ul>
		</div>
	<?php
	}
	if (count($this->plugins))
	{
		echo $this->loadTemplate('plugins');
	}

	if ($this->config->show_social_bookmark)
	{
		if (empty($this->config->social_sharing_buttons))
		{
			$shareOptions = array(
				'Delicious',
				'Digg',
				'Facebook',
				'Google',
				'Stumbleupon',
				'Technorati',
				'Twitter',
				'LinkedIn'
			);
		}
		else
		{
			$shareOptions = explode(',', $this->config->social_sharing_buttons);
		}
	?>
		<div id="itp-social-buttons-box" class="row-fluid">
			<div id="eb-share-text"><?php echo JText::_('EB_SHARE_THIS_EVENT'); ?></div>
			<div id="eb-share-button">
				<?php
					$title = $item->title;
					$html  = '';
					if (in_array('Delicious', $shareOptions))
					{
						$html .= EventbookingHelper::getDeliciousButton($title, $socialUrl);
					}
					if (in_array('Digg', $shareOptions))
					{
						$html .= EventbookingHelper::getDiggButton($title, $socialUrl);
					}
					if (in_array('Facebook', $shareOptions))
					{
						$html .= EventbookingHelper::getFacebookButton($title, $socialUrl);
					}
					if (in_array('Google', $shareOptions))
					{
						$html .= EventbookingHelper::getGoogleButton($title, $socialUrl);
					}
					if (in_array('Stumbleupon', $shareOptions))
					{
						$html .= EventbookingHelper::getStumbleuponButton($title, $socialUrl);
					}
					if (in_array('Technorati', $shareOptions))
					{
						$html .= EventbookingHelper::getTechnoratiButton($title, $socialUrl);
					}
					if (in_array('Twitter', $shareOptions))
					{
						$html .= EventbookingHelper::getTwitterButton($title, $socialUrl);
					}
					if (in_array('LinkedIn', $shareOptions))
					{
						$html .= EventbookingHelper::getLinkedInButton($title, $socialUrl);
					}

					echo $html ;
				?>
			</div>
		</div>
	<?php
	}
?>
	</div>
</div>

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<input type="hidden" name="option" value="com_eventbooking" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="" />
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>

<script language="javascript">
	function cancelRegistration(registrantId) {
		var form = document.adminForm ;
		if (confirm("<?php echo JText::_('EB_CANCEL_REGISTRATION_CONFIRM'); ?>")) {
			form.task.value = 'registrant.cancel' ;
			form.id.value = registrantId ;
			form.submit() ;
		}
	}
</script>