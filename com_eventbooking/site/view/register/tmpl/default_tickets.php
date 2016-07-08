<?php
/**
 * @version            2.7.1
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

?>
	<h3 class="eb-heading"><?php echo JText::_('EB_TICKET_INFORMATION'); ?></h3>
	<table class="table table-striped table-bordered table-condensed">
		<thead>
		<tr>
			<th>
				<?php echo JText::_('EB_TICKET_TYPE'); ?>
			</th>
			<th>
				<?php echo JText::_('EB_PRICE'); ?>
			</th>
			<?php
			if ($this->config->show_available_place)
			{
			?>
				<th class="center">
					<?php echo JText::_('EB_AVAILABLE_PLACE'); ?>
				</th>
			<?php
			}
			?>
			<th>
				<?php echo JText::_('EB_QUANTITY'); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach ($this->ticketTypes as $ticketType)
		{
		?>
			<tr>
				<td>
					<?php echo $ticketType->title; ?>
				</td>
				<td>
					<?php echo EventbookingHelper::formatCurrency($ticketType->price, $this->config); ?>
				</td>
				<?php
				$available = $ticketType->capacity - $ticketType->registered;
				if ($this->config->show_available_place)
				{
				?>
					<td class="center">
						<?php echo $available; ?>
					</td>
				<?php
				}
				?>
				<td class="center">
					<?php
						if ($available > 0)
						{
							$fieldName = 'ticket_type_' . $ticketType->id;
							if ($ticketType->max_tickets_per_booking > 0)
							{
								$available = min($available, $ticketType->max_tickets_per_booking);
							}
							echo JHtml::_('select.integerlist', 0, $available, 1, $fieldName, 'class="ticket_type_quantity input-small" onchange="calculateIndividualRegistrationFee();"', $this->input->getInt($fieldName, 0));
						}
						else
						{
							echo JText::_('EB_NA');
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
