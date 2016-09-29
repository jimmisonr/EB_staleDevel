<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die;

$item = $this->item;
?>
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

					echo JHtml::_('date', $item->event_date, $dateFormat, null);
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
					<?php echo JHtml::_('date', $item->event_end_date, $dateFormat, null); ?>
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
						<?php echo JHtml::_('date', $item->registration_start_date, $dateFormat, null); ?>
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
						echo $item->event_capacity;
					}
					else
					{
						echo JText::_('EB_UNLIMITED');
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
					<?php
						echo $item->total_registrants.' ';
						if ($this->config->show_list_of_registrants && ($item->total_registrants > 0) && EventbookingHelper::canViewRegistrantList())
						{
						?>
							<a href="index.php?option=com_eventbooking&view=registrantlist&id=<?php echo $item->id ?>&tmpl=component"
							   class="eb-colorbox-register-lists"><span class="view_list"><?php echo JText::_("EB_VIEW_LIST"); ?></span></a>
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
					<?php echo $item->event_capacity - $item->total_registrants; ?>
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
					<?php echo JHtml::_('date', $item->cut_off_date, $dateFormat, null); ?>
				</td>
			</tr>
			<?php
		}

		if ($item->individual_price > 0 || ($this->config->show_price_for_free_event))
		{
			$showPrice = true;
		}
		else
		{
			$showPrice = false;
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
						{
							echo EventbookingHelper::formatCurrency($item->individual_price, $this->config, $item->currency_symbol);
						}
						else
						{
							echo '<span class="eb_free">' . JText::_('EB_FREE') . '</span>';
						}
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
								echo ' <em> ' . JText::sprintf('EB_UNTIl_DATE', JHtml::_('date', $item->early_bird_discount_date, $this->config->date_format, null)) . '</em>';
							}
						}
						else
						{
							echo '<span class="eb_free">' . JText::_('EB_FREE') . '</span>';
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
							if ($item->price_text)
							{
								echo $item->price_text;
							}
							elseif ($item->individual_price > 0)
							{
								echo EventbookingHelper::formatCurrency($item->individual_price, $this->config, $item->currency_symbol);
							}
							else
							{
								echo '<span class="eb_free">' . JText::_('EB_FREE') . '</span>';
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
					echo EventbookingHelper::formatCurrency($item->fixed_group_price, $this->config, $item->currency_symbol);
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
					echo ' <em> ' . JText::sprintf('EB_FROM_DATE', JHtml::_('date', $item->late_fee_date, $this->config->date_format, null)) . '</em>';
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
			foreach ($this->paramData as $param)
			{
				if ($param['value'])
				{
				?>
					<tr>
						<td>
							<strong><?php echo $param['title']; ?></strong>
						</td>
						<td>
							<?php echo $param['value']; ?>
						</td>
					</tr>
				<?php
				}
			}
		}

		if ($item->location_id)
		{
			$width = (int) $this->config->map_width;

			if (!$width)
			{
				$width = 500;
			}

			$height = (int) $this->config->map_height;

			if (!$height)
			{
				$height = 450;
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
							<span itemprop="streetAddress"><?php echo $this->location->address; ?></span>
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
						<a href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=map&location_id=' . $item->location_id . '&tmpl=component&format=html'); ?>"
						   class="eb-colorbox-map"
						   title="<?php echo $this->location->name; ?>"><?php echo $this->location->name; ?></a>
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
					for ($i = 0, $n = count($attachments); $i < $n; $i++)
					{
						$attachment = $attachments[$i];
						if ($i > 0)
						{
							echo '<br />';
						}
						?>
						<a href="<?php echo JUri::base() . '/media/com_eventbooking/' . $attachment; ?>"
						   target="_blank"><?php echo $attachment; ?></a>
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