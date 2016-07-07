<?php
/**
 * @version            2.8.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;
?>
	<h3 class="eb-event-tickets-heading"><?php echo JText::_('EB_TICKET_INFORMATION'); ?></h3>
	<table class="table table-striped table-bordered table-condensed eb-ticket-information">
		<thead>
		<tr>
			<th>
				<?php echo JText::_('EB_TICKET_TYPE'); ?>
			</th>
			<th class="eb-text-right">
				<?php echo JText::_('EB_PRICE'); ?>
			</th>
			<?php
			if ($config->show_available_place)
			{
			?>
				<th class="center">
					<?php echo JText::_('EB_AVAILABLE_PLACE'); ?>
				</th>
			<?php
			}
			?>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ($ticketTypes as $ticketType)
		{
		?>
			<tr>
				<td>
					<?php echo $ticketType->title; ?>
				</td>
				<td class="eb-text-right">
					<?php echo EventbookingHelper::formatCurrency($ticketType->price, $config); ?>
				</td>
				<?php
				if ($config->show_available_place)
				{
					$available = $ticketType->capacity - $ticketType->registered;
				?>
					<td class="center">
						<?php echo $available; ?>
					</td>
				<?php
				}
				?>
			</tr>
		<?php
		}
		?>
		</tbody>
	</table>
<?php
