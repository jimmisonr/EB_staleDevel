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

class plgEventBookingTicketTypes extends JPlugin
{
	protected $table = '#__eb_ticket_types';

	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}

	/**
	 * Render setting form
	 *
	 * @param JTable $row
	 *
	 * @return array
	 */
	public function onEditEvent($row)
	{
		ob_start();
		$this->drawSettingForm($row);
		$form = ob_get_clean();

		return array(
			'title' => JText::_('EB_TICKET_TYPES'),
			'form'  => $form
		);
	}

	/**
	 * Store setting into database, in this case, use params field of plans table
	 *
	 * @param event   $row
	 * @param Boolean $isNew true if create new plan, false if edit
	 */
	public function onAfterSaveEvent($row, $data, $isNew)
	{
		// The plugin will only be available in the backend
		$app = JFactory::getApplication();
		if ($app->isSite())
		{
			return;
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$ids          = $data['ticket_type_id'];
		$titles       = $data['ticket_type_title'];
		$prices       = $data['ticket_type_price'];
		$descriptions = $data['ticket_type_description'];
		$capacities   = $data['ticket_type_capacity'];

		for ($i = 0, $n = count($titles); $i < $n; $i++)
		{
			$id = $ids[$i];
			if (empty($titles[$i]))
			{
				if ($id)
				{
					$query->clear()
						->delete('#__eb_ticket_types')
						->where('id = ' . $id);

					$db->setQuery($query)
						->execute();
				}

				continue;
			}


			$title       = $db->quote(trim($titles[$i]));
			$description = $db->quote(trim($descriptions[$i]));
			$price       = (float) $prices[$i];
			$capacity    = (int) $capacities[$i];

			$query->clear();
			if ($id)
			{
				$query->update('#__eb_ticket_types')
					->set('title = ' . $title)
					->set('description = ' . $description)
					->set('price = ' . $price)
					->set('capacity = ' . $capacity)
					->where('id = ' . $id);
			}
			else
			{
				$query->insert('#__eb_ticket_types')
					->columns('event_id, title, description, price, capacity')
					->values("$row->id, $title, $description ,$price, $capacity");
			}

			$db->setQuery($query)
				->execute();
		}
	}

	/**
	 * Display form allows users to change settings on subscription plan add/edit screen
	 *
	 * @param object $row
	 */
	private function drawSettingForm($row)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_ticket_types')
			->where('event_id=' . $row->id)
			->order('id');
		$db->setQuery($query);
		$ticketTypes = $db->loadObjectList();
		?>
		<div class="row-fluid">
			<div class="span5">
				<table class="adminlist table table-striped" id="adminForm">
					<thead>
					<tr>
						<th class="nowrap center"><?php echo JText::_('EB_TITLE'); ?></th>
						<th class="nowrap center"><?php echo JText::_('EB_PRICE'); ?></th>
						<th class="nowrap center"><?php echo JText::_('EB_CAPACITY'); ?></th>
						<th class="nowrap center"><?php echo JText::_('EB_DESCRIPTION'); ?></th>
						<th class="nowrap center"><?php echo JText::_('EB_REMOVE'); ?></th>
					</tr>
					</thead>
					<tbody id="additional_options">
					<?php
					$numberTicketTypes = max(count($ticketTypes), 4);
					for ($i = 0; $i < $numberTicketTypes; $i++)
					{
						if (isset($ticketTypes[$i]))
						{
							$ticketType = $ticketTypes[$i];
						}
						else
						{
							$ticketType              = new stdClass;
							$ticketType->id          = 0;
							$ticketType->title       = '';
							$ticketType->description = '';
							$ticketType->price       = '';
						}
						?>
						<tr id="option_<?php echo $i; ?>">
							<td>
								<input type="hidden" name="ticket_type_id[]" value="<?php echo $ticketType->id; ?>"/>
								<input type="text" class="input-medium" name="ticket_type_title[]"
								       value="<?php echo $ticketType->title; ?>"/>
							</td>
							<td><input type="text" class="input-mini" name="ticket_type_price[]"
							           value="<?php echo $ticketType->price; ?>"/></td>
							<td><input type="text" class="input-mini" name="ticket_type_capacity[]"
							           value="<?php echo $ticketType->capacity; ?>"/></td>
							<td><input type="text" class="input-xlarge" name="ticket_type_description[]"
							           value="<?php echo $ticketType->description; ?>"/></td>
							<td>
								<button type="button" class="btn btn-danger"
								        onclick="removeEventContainer(<?php echo $i; ?>)"><i
										class="icon-remove"></i><?php echo JText::_('EB_REMOVE'); ?></button>
							</td>
						</tr>
						<?php
					}
					?>
					</tbody>
				</table>
				<button type="button" class="btn btn-success" onclick="addOptionContainer()"><i
						class="icon-new icon-white"></i><?php echo JText::_('EB_ADD'); ?></button>
			</div>
		</div>
		<script language="JavaScript">
			function removeEventContainer(id) {
				if (confirm('<?php echo JText::_('EB_REMOVE_ITEM_CONFIRM'); ?>')) {
					jQuery('#option_' + id).remove();
				}
			}
			(function ($) {
				var countOption = <?php echo $numberTicketTypes; ?>;
				addOptionContainer = (function () {
					var html = '<tr id="option_' + countOption + '">'
					html += '<td><input type="hidden" name="ticket_type_id[]" value = "0" /><input type="text" class="input-medium" name="ticket_type_title[]" value="" /></td>';
					html += '<td><input type="text" class="input-mini" name="ticket_type_price[]" value="" /></td>';
					html += '<td><input type="text" class="input-mini" name="ticket_type_capacity[]" value="" /></td>';
					html += '<td><input type="text" class="input-xlarge" name="ticket_type_description[]" value="" /></td>';
					html += '<td><button type="button" class="btn btn-danger" onclick="removeEventContainer(' + countOption + ')"><i class="icon-remove"></i><?php echo JText::_('EB_REMOVE'); ?></button></td>';
					html += '</tr>';
					$('#additional_options').append(html);
					countOption++;
				})
			})(jQuery)
		</script>
		<?php
	}
}	