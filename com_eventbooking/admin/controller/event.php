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

class EventbookingControllerEvent extends EventbookingController
{
	/**
	 * Import Events from a csv file
	 */
	public function import()
	{
		$inputFile = $this->input->files->get('input_file');
		$fileName  = $inputFile ['name'];
		$fileExt   = strtolower(JFile::getExt($fileName));

		if (!in_array($fileExt, array('csv', 'xls', 'xlsx')))
		{
			$this->setRedirect('index.php?option=com_eventbooking&view=event&layout=import', JText::_('Invalid File Type. Only CSV, XLS and XLS file types are supported'));

			return;
		}

		/* @var  EventbookingModelEvent $model */
		$model = $this->getModel('Event');
		try
		{
			$numberImportedEvents = $model->import($inputFile['tmp_name']);
			$this->setRedirect('index.php?option=com_eventbooking&view=events', JText::sprintf('EB_NUMBER_EVENTS_IMPORTED', $numberImportedEvents));
		}
		catch (Exception $e)
		{
			$this->setRedirect('index.php?option=com_eventbooking&view=event&layout=import');
			$this->setMessage($e->getMessage(), 'error');
		}
	}

	/**
	 * Export events into an Excel File
	 */
	public function export()
	{
		set_time_limit(0);
		$model = $this->getModel('events');

		/* @var EventbookingModelEvents $model */

		$model->setState('limitstart', 0)
			->setState('limit', 0)
			->setState('filter_order', 'tbl.id')
			->setState('filter_order_Dir', 'ASC');

		$rowEvents = $model->getData();

		if (count($rowEvents) == 0)
		{
			$this->setMessage(JText::_('There are no events to export'));
			$this->setRedirect('index.php?option=com_eventbooking&view=events');

			return;
		}

		$fields = array(
			'id',
			'title',
			'category',
			'additional_categories',
			'thumb',
			'event_date',
			'event_end_date',
			'individual_price',
			'tax_rate',
			'event_capacity',
			'short_description',
			'description',
			'meta_keywords',
			'meta_description',
			'access',
			'registration_access',
			'published'
		);

		EventbookingHelperData::excelExport($fields, $rowEvents, 'events_list');
	}
}
