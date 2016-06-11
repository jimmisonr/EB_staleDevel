<?php
/**
 * @version            2.7.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die;

class plgEventBookingDates extends JPlugin
{
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);

		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_eventbooking/table');
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
		if ($row->parent_id > 0)
		{
			return;
		}

		ob_start();
		$this->drawSettingForm($row);
		$form = ob_get_clean();

		return array('title' => JText::_('EB_ADDITIONAL_DATES'),
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
		if ($app->isSite() || $row->parent_id > 0)
		{
			return;
		}

		$config = EventbookingHelper::getConfig();
		$maxNumberDates = (int) $data['count_event_dates'];

		$additionalEventIds = array();
		$numberChildrenEvents = 0;
		for ($i = 0; $i < $maxNumberDates; $i++)
		{
			if (empty($data['event_date_' . $i]))
			{
				continue;
			}

			$rowEvent     = clone ($row);
			$id           = isset($data['event_id_' . $i]) ? $data['event_id_' . $i] : 0;
			$rowEvent->id = $id;

			$rowEvent->event_date     = $data['event_date_' . $i] . ' ' . $data['event_date_hour_' . $i] . ':' . $data['event_date_minute_' . $i] . ':00';
			$rowEvent->event_end_date = $data['event_end_date_' . $i] . ' ' . $data['event_end_date_hour_' . $i] . ':' . $data['event_end_date_minute_' . $i] . ':00';

			$rowEvent->registration_start_date = $data['registration_start_date_' . $i] . ' ' . $data['registration_start_date_hour_' . $i] . ':' . $data['registration_start_date_minute_' . $i] . ':00';
			$rowEvent->cut_off_date            = $data['cut_off_date_' . $i] . ' ' . $data['cut_off_date_hour_' . $i] . ':' . $data['cut_off_date_minute_' . $i] . ':00';

			$rowEvent->location_id        = $data['location_id_' . $i];
			$rowEvent->parent_id          = $row->id;
			$rowEvent->event_type         = 2;
			$rowEvent->is_additional_date = 1;

			if (!$rowEvent->id)
			{
				$rowEvent->alias = JApplicationHelper::stringURLSafe($rowEvent->title . '-' . JHtml::_('date', $rowEvent->event_date, $config->date_format, null));
				$rowEvent->hits = 0;
			}


			$rowEvent->store();

			$numberChildrenEvents++;

			if ($id == 0)
			{
				$isChildEventNew = true;
			}
			else
			{
				$isChildEventNew = false;
			}

			// Store categories
			$this->storeEventCategories($rowEvent->id, $data, $isChildEventNew);

			// Store price
			$this->storeEventGroupRegistrationRates($rowEvent->id, $data, $isChildEventNew);

			$additionalEventIds[] = $rowEvent->id;
		}

		if ($numberChildrenEvents)
		{
			$row->event_type = 1;
		}
		else
		{
			$row->event_type = 0;
		}

		$row->store();



		// Remove the events which are removed by users
		if (!$isNew)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id')
					->from('#__eb_events')
					->where('parent_id = '. $row->id)
					->where('is_additional_date = 1');
			$db->setQuery($query);
			$allChildrenEventIds = $db->loadColumn();

			if (count($allChildrenEventIds))
			{
				$deletedEventIds = array_diff($allChildrenEventIds, $additionalEventIds);

				if (count($deletedEventIds))
				{
					$model = new EventbookingModelEvent();

					$model->delete($deletedEventIds);
				}
			}
		}
	}

	/**
	 * Display form allows users to change settings on subscription plan add/edit screen
	 *
	 * @param object $row
	 */
	private function drawSettingForm($row)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$nullDate = $db->getNullDate();
		$format = 'Y-m-d';

		$query->select('id, event_date, event_end_date, cut_off_date, registration_start_date, location_id')
				->from('#__eb_events')
				->where('parent_id = '. (int) $row->id)
				->where('is_additional_date = 1')
				->order('id');
		$db->setQuery($query);
		$rowEvents = $db->loadObjectList();

		$maxNumberDates = max($this->params->get('max_number_dates', 3), count($rowEvents));

		$options                    = array();
		$options[]                  = JHtml::_('select.option', 0, JText::_('EB_SELECT_LOCATION'), 'id', 'name');
		$options                    = array_merge($options, EventbookingHelperDatabase::getAllLocations());
		?>
		<div id="advance-date_content">
			<?php
			for($i = 0; $i < $maxNumberDates ; $i++)
			{
				if (isset($rowEvents[$i]))
				{
					$rowEvent     = $rowEvents[$i];
					$eventId      = $rowEvent->id;
					$eventDate    = $rowEvent->event_date;
					$eventEndDate = $rowEvent->event_end_date;
					$cutOffDate   = $rowEvent->cut_off_date;
					$registrationStartDate = $rowEvent->registration_start_date;
					$locationId   = $rowEvent->location_id;
					$eventDateHour   = JHtml::_('date', $eventDate, 'G', null);
					$eventDateMinute = JHtml::_('date', $eventDate, 'i', null);

					if ($eventEndDate == $nullDate)
					{
						$eventEndDateHour = 0;
						$eventEndDateMinute = 0;
					}
					else
					{
						$eventEndDateHour   = JHtml::_('date', $eventEndDate, 'G', null);
						$eventEndDateMinute = JHtml::_('date', $eventEndDate, 'i', null);
					}

					if ($cutOffDate == $nullDate)
					{
						$cutOffDateHour = 0;
						$cutOffDateMinute = 0;
					}
					else
					{
						$cutOffDateHour   = JHtml::_('date', $cutOffDate, 'G', null);
						$cutOffDateMinute = JHtml::_('date', $cutOffDate, 'i', null);
					}

					if ($registrationStartDate == $nullDate)
					{
						$registrationStartDateHour = 0;
						$registrationStartDateMinute = 0;
					}
					else
					{
						$registrationStartDateHour   = JHtml::_('date', $registrationStartDate, 'G', null);
						$registrationStartDateMinute = JHtml::_('date', $registrationStartDate, 'i', null);
					}
				}
				else
				{
					$eventId               = 0;
					$eventDate             = $nullDate;
					$eventEndDate          = $nullDate;
					$registrationStartDate = $nullDate;
					$cutOffDate            = $nullDate;
					$locationId            = $row->location_id;

					$eventDateHour   = JHtml::_('date', $row->event_date, 'G', null);
					$eventDateMinute = JHtml::_('date', $row->event_date, 'i', null);

					if ($row->event_end_date == $nullDate)
					{
						$eventEndDateHour = 0;
						$eventEndDateMinute = 0;
					}
					else
					{
						$eventEndDateHour   = JHtml::_('date', $row->event_end_date, 'G', null);
						$eventEndDateMinute = JHtml::_('date', $row->event_end_date, 'i', null);
					}

					if ($row->cut_off_date == $nullDate)
					{
						$cutOffDateHour = 0;
						$cutOffDateMinute = 0;
					}
					else
					{
						$cutOffDateHour   = JHtml::_('date', $row->cut_off_date, 'G', null);
						$cutOffDateMinute = JHtml::_('date', $row->cut_off_date, 'i', null);
					}

					if ($row->registration_start_date == $nullDate)
					{
						$registrationStartDateHour = 0;
						$registrationStartDateMinute = 0;
					}
					else
					{
						$registrationStartDateHour   = JHtml::_('date', $row->registration_start_date, 'G', null);
						$registrationStartDateMinute = JHtml::_('date', $row->registration_start_date, 'i', null);
					}
				}
				?>
				<fieldset id="date_<?php echo $i; ?>" class="form-inline form-inline-header">
					<legend><?php echo JText::sprintf('EB_EVENT_DATE_COUNT', ($i + 1)); ?></legend>
					<input type="hidden" name="event_id_<?php echo $i; ?>" value="<?php echo $eventId; ?>" />
					<input type="hidden" name="count_additional_date[]" value="" />
					<div class="control-group">
						<label class="control-label">
							<?php echo JText::_('EB_EVENT_START_DATE'); ?>
						</label>
						<div class="controls">
							<?php echo JHtml::_('calendar', ($eventDate == $nullDate) ? '' : JHtml::_('date', $eventDate, $format, null), 'event_date_'.$i, 'event_date_'.$i, '%Y-%m-%d', array('class' => 'input-small')) ; ?>
							<?php echo JHtml::_('select.integerlist', 0, 23, 1, 'event_date_hour_'.$i, ' class="input-mini" ', $eventDateHour); ?>
							<?php echo JHtml::_('select.integerlist', 0, 55, 5, 'event_date_minute_'.$i, ' class="input-mini" ', $eventDateMinute, '%02d'); ?>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">
							<?php echo JText::_('EB_EVENT_END_DATE'); ?>
						</label>
						<div class="controls">
							<?php echo JHtml::_('calendar', ($eventEndDate == $nullDate) ? '' : JHtml::_('date', $eventEndDate, $format, null), 'event_end_date_'.$i, 'event_end_date_'.$i, '%Y-%m-%d', array('class' => 'input-small')) ; ?>
							<?php echo JHtml::_('select.integerlist', 0, 23, 1, 'event_end_date_hour_'.$i, ' class="input-mini" ', $eventEndDateHour); ?>
							<?php echo JHtml::_('select.integerlist', 0, 55, 5, 'event_end_date_minute_'.$i, ' class="input-mini" ', $eventEndDateMinute, '%02d'); ?>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">
							<?php echo JText::_('EB_REGISTRATION_START_DATE'); ?>
						</label>
						<div class="controls">
							<?php echo JHtml::_('calendar', ($registrationStartDate == $nullDate) ? '' : JHtml::_('date', $registrationStartDate, $format, null), 'registration_start_date_'.$i, 'registration_start_date_'.$i, '%Y-%m-%d', array('class' => 'input-small')) ; ?>
							<?php echo JHtml::_('select.integerlist', 0, 23, 1, 'registration_start_date_hour_'.$i, ' class="input-mini" ', $registrationStartDateHour); ?>
							<?php echo JHtml::_('select.integerlist', 0, 55, 5, 'registration_start_date_minute_'.$i, ' class="input-mini" ', $registrationStartDateMinute, '%02d'); ?>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">
							<?php echo JText::_('EB_CUT_OFF_DATE'); ?>
						</label>
						<div class="controls">
							<?php echo JHtml::_('calendar', ($cutOffDate == $nullDate) ? '' : JHtml::_('date', $cutOffDate, $format, null), 'cut_off_date_'.$i, 'cut_off_date_'.$i, '%Y-%m-%d', array('class' => 'input-small')) ; ?>
							<?php echo JHtml::_('select.integerlist', 0, 23, 1, 'cut_off_date_hour_'.$i, ' class="input-mini" ', $cutOffDateHour); ?>
							<?php echo JHtml::_('select.integerlist', 0, 55, 5, 'cut_off_date_minute_'.$i, ' class="input-mini" ', $cutOffDateMinute, '%02d'); ?>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">
							<?php echo JText::_('EB_LOCATION'); ?>
						</label>
						<div class="controls">
							<?php echo JHtml::_('select.genericlist', $options, 'location_id_'.$i, '', 'id', 'name', $locationId); ?>
						</div>
					</div>
					<div class="control-group">
						<button type="button" class="btn btn-danger" onclick="removeEventContainer(<?php echo $i; ?>)"><i class="icon-remove"></i><?php echo JText::_('EB_REMOVE'); ?></button>
					</div>
				</fieldset>
				<?php
			}
			?>
		</div>
		<div class="row-fluid">
			<button type="button" class="btn btn-success" onclick="addEventContainer()"><i class="icon-new icon-white"></i><?php echo JText::_('EB_ADD'); ?></button>
			<input type="hidden" id="count_event_dates" name="count_event_dates" value="<?php echo $maxNumberDates; ?>" />
		</div>
		<script language="JavaScript">
			function removeEventContainer(id)
			{
				if (confirm('<?php echo JText::_('EB_REMOVE_ITEM_CONFIRM'); ?>'))
				{
					jQuery('#date_' + id).remove();
				}
			}

			(function($){
				var countDate = '<?php echo $maxNumberDates + 1;?>';
				addEventContainer  = (function(){
					$('#count_event_dates').val(countDate);
					var  html = '<fieldset id="date_' + countDate + '" class="form-inline form-inline-header">'
					html += '<legend class="item_date_' + countDate + '"></legend>';
					html += '<input type="hidden" name="event_id_' + countDate + '" value="0" />';
					//event start date
					html += '<div class="control-group">';
					html += '<label class="control-label"><?php echo JText::_('EB_EVENT_START_DATE'); ?></label>';
					html += '<div class="controls">';
					html += '<div class="input-append">';
					html += '<input type="text" style="width: 100px;" class="input-medium hasTooltip" value="" id="event_date_'+countDate+'" name="event_date_'+countDate+'">';
					html += '<button id="event_date_'+countDate+'_img" class="btn" type="button"><i class="icon-calendar"></i></button>';
					html += '</div>';
					html += '<?php echo preg_replace(array('/\r/', '/\n/'), '', JHtml::_('select.integerlist', 0, 23, 1, 'event_date_hour_'.$i, ' class="input-mini event_date_hour" ', $eventDateHour)); ?><?php echo preg_replace(array('/\r/', '/\n/'), '',JHtml::_('select.integerlist', 0, 55, 5, 'event_date_minute_'.$i, ' class="input-mini event_date_minute" ', $eventDateMinute, '%02d')); ?>';
					html += '</div>';
					html += '</div>';
					//event end date
					html += '<div class="control-group">';
					html += '<label class="control-label"><?php echo JText::_('EB_EVENT_END_DATE'); ?></label>';
					html += '<div class="controls">';
					html += '<div class="input-append">';
					html += '<input type="text" style="width: 100px;" class="input-medium hasTooltip" value="" id="event_end_date_'+countDate+'" name="event_end_date_'+countDate+'">';
					html += '<button id="event_end_date_'+countDate+'_img" class="btn" type="button"><i class="icon-calendar"></i></button>';
					html += '</div>';
					html += '<?php echo preg_replace(array('/\r/', '/\n/'), '', JHtml::_('select.integerlist', 0, 23, 1, 'event_end_date_hour_'.$i, ' class="input-mini event_end_date_hour" ', $eventEndDateHour)); ?> <?php echo preg_replace(array('/\r/', '/\n/'), '', JHtml::_('select.integerlist', 0, 55, 5, 'event_end_date_minute_'.$i, ' class="input-mini event_end_date_minute" ', $eventEndDateMinute, '%02d')); ?>';
					html += '</div>';
					html += '</div>';
					//registranstart date
					html += '<div class="control-group">';
					html += '<label class="control-label"><?php echo JText::_('EB_REGISTRATION_START_DATE'); ?></label>';
					html += '<div class="controls">';
					html += '<div class="input-append">';
					html += '<input type="text" style="width: 100px;" class="input-medium hasTooltip" value="" id="registration_start_date_'+countDate+'" name="registration_start_date_'+countDate+'">';
					html += '<button id="registration_start_date_'+countDate+'_img" class="btn" type="button"><i class="icon-calendar"></i></button>';
					html += '</div>';
					html += '<?php echo preg_replace(array('/\r/', '/\n/'), '', JHtml::_('select.integerlist', 0, 23, 1, 'registration_start_date_hour_'.$i, ' class="registration_start_date_hour input-mini" ', $registrationStartDateHour)); ?> <?php echo preg_replace(array('/\r/', '/\n/'), '', JHtml::_('select.integerlist', 0, 55, 5, 'registration_start_date_minute_'.$i, ' class="registration_start_date_minute input-mini" ', $registrationStartDateMinute, '%02d')); ?>';
					html += '</div>';
					html += '</div>';
					//cut of date
					html += '<div class="control-group">';
					html += '<label class="control-label"><?php echo JText::_('EB_CUT_OFF_DATE'); ?></label>';
					html += '<div class="controls">';
					html += '<div class="input-append">';
					html += '<input type="text" style="width: 100px;" class="input-medium hasTooltip" value="" id="cut_off_date_'+countDate+'" name="cut_off_date_'+countDate+'">';
					html += '<button id="cut_off_date_'+countDate+'_img" class="btn" type="button"><i class="icon-calendar"></i></button>';
					html += '</div>';
					html += '<?php echo preg_replace(array('/\r/', '/\n/'), '', JHtml::_('select.integerlist', 0, 23, 1, 'cut_off_date_hour_'.$i, ' class="cut_off_date_hour input-mini" ', $cutOffDateHour)); ?> <?php echo preg_replace(array('/\r/', '/\n/'), '', JHtml::_('select.integerlist', 0, 55, 5, 'cut_off_date_minute_'.$i, ' class="cut_off_date_minute input-mini" ', $cutOffDateMinute, '%02d')); ?>';
					html += '</div>';
					html += '</div>';
					//location
					html += '<div class="control-group">';
					html += '<label class="control-label"><?php echo JText::_('EB_SELECT_LOCATION'); ?></label>';
					html += '<div class="controls">';
					html += '<?php echo preg_replace(array('/\r/', '/\n/'), '', JHtml::_('select.genericlist', $options, 'location_id_'.$i, 'class="location_id"', 'id', 'name', $locationId)); ?>';
					html += '</div>';
					html += '</div>';
					//remove button
					html += '<div class="control-group">';
					html += '<button type="button" class="btn btn-danger" onclick="removeEventContainer('+countDate+')"><i class="icon-remove"></i><?php echo JText::_('EB_REMOVE'); ?></button>';
					html += '</div>';
					html += '</fieldset>';
					$('#advance-date_content').append(html);
					$('legend.item_date_'+countDate).text('Extra Event Date '+countDate);
					$("#date_"+countDate+" .event_date_hour").attr("name","event_date_hour_"+countDate);
					$("#date_"+countDate+" .event_date_minute").attr("name","event_date_minute_"+countDate);
					$("#date_"+countDate+" .event_end_date_hour").attr("name","event_end_date_hour_"+countDate);
					$("#date_"+countDate+" .event_end_date_minute").attr("name","event_end_date_minute_"+countDate);
					$("#date_"+countDate+" .registration_start_date_hour").attr("name","registration_start_date_hour_"+countDate);
					$("#date_"+countDate+" .registration_start_date_minute").attr("name","registration_start_date_minute_"+countDate);
					$("#date_"+countDate+" .cut_off_date_hour").attr("name","cut_off_date_hour_"+countDate);
					$("#date_"+countDate+" .cut_off_date_minute").attr("name","cut_off_date_minute_"+countDate);
					$("#date_"+countDate+" .location_id").attr("name","location_id_"+countDate);
					Calendar.setup({
						// Id of the input field
						inputField: "event_date_"+countDate,
						// Format of the input field
						ifFormat: "%Y-%m-%d",
						// Trigger for the calendar (button ID)
						button: "event_date_"+countDate+"_img",
						// Alignment (defaults to "Bl")
						align: "Tl",
						singleClick: true,
						firstDay: 0
					});
					Calendar.setup({
						// Id of the input field
						inputField: "registration_start_date_"+countDate,
						// Format of the input field
						ifFormat: "%Y-%m-%d",
						// Trigger for the calendar (button ID)
						button: "registration_start_date_"+countDate+"_img",
						// Alignment (defaults to "Bl")
						align: "Tl",
						singleClick: true,
						firstDay: 0
					});
					Calendar.setup({
						// Id of the input field
						inputField: "event_end_date_"+countDate,
						// Format of the input field
						ifFormat: "%Y-%m-%d",
						// Trigger for the calendar (button ID)
						button: "event_end_date_"+countDate+"_img",
						// Alignment (defaults to "Bl")
						align: "Tl",
						singleClick: true,
						firstDay: 0
					});
					Calendar.setup({
						// Id of the input field
						inputField: "cut_off_date_"+countDate,
						// Format of the input field
						ifFormat: "%Y-%m-%d",
						// Trigger for the calendar (button ID)
						button: "cut_off_date_"+countDate+"_img",
						// Alignment (defaults to "Bl")
						align: "Tl",
						singleClick: true,
						firstDay: 0
					});
					countDate++;
				})
			})(jQuery)
		</script>
		<?php
	}

	/**
	 * Store categories of an event
	 *
	 * @param $eventId
	 * @param $data
	 * @param $isNew
	 */
	protected function storeEventCategories($eventId, $data, $isNew)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		if (!$isNew)
		{
			$query->delete('#__eb_event_categories')->where('event_id=' . $eventId);
			$db->setQuery($query);
			$db->execute();
		}
		$mainCategoryId = (int) $data['main_category_id'];

		if ($mainCategoryId)
		{
			$query->clear();
			$query->insert('#__eb_event_categories')
					->columns('event_id, category_id, main_category')
					->values("$eventId, $mainCategoryId, 1");
			$db->setQuery($query);
			$db->execute();
		}

		$categories = isset($data['category_id']) ? $data['category_id'] : array();

		for ($i = 0, $n = count($categories); $i < $n; $i++)
		{
			$categoryId = (int) $categories[$i];
			if ($categoryId && ($categoryId != $mainCategoryId))
			{
				$query->clear();
				$query->insert('#__eb_event_categories')
						->columns('event_id, category_id, main_category')
						->values("$eventId, $categoryId, 0");
				$db->setQuery($query);
				$db->execute();
			}
		}
	}

	/**
	 * Store group registration rates of an event
	 *
	 * @param $eventId
	 * @param $data
	 * @param $isNew
	 */
	protected function storeEventGroupRegistrationRates($eventId, $data, $isNew)
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		if (!$isNew)
		{
			$query->delete('#__eb_event_group_prices')->where('event_id=' . $eventId);
			$db->setQuery($query);
			$db->execute();
		}

		$prices            = $data['price'];
		$registrantNumbers = $data['registrant_number'];
		for ($i = 0, $n = count($prices); $i < $n; $i++)
		{
			$price            = $prices[$i];
			$registrantNumber = $registrantNumbers[$i];
			if (($registrantNumber > 0) && ($price > 0))
			{
				$query->clear();
				$query->insert('#__eb_event_group_prices')
						->columns('event_id, registrant_number, price')
						->values("$eventId, $registrantNumber, $price");
				$db->setQuery($query);
				$db->execute();
			}
		}
	}
}