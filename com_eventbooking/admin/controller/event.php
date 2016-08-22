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
		/* @vare  EventbookingModelEvent $model */
		$model = $this->getModel('Event');

		$numberImportedEvents = $model->import($this->input);
		if ($numberImportedEvents === false)
		{
			$this->setRedirect('index.php?option=com_eventbooking&view=event&layout=import', JText::_('EB_NO_EVENTS_IMPORTED'));
		}
		else
		{
			$this->setRedirect('index.php?option=com_eventbooking&view=events',
				JText::sprintf('EB_NUMBER_EVENTS_IMPORTED', $numberImportedEvents));
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

		require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/vendor/PHPOffice/PHPExcel.php';

		$exporter = new PHPExcel();
		$user     = JFactory::getUser();

		$createdDate = JFactory::getDate('now', JFactory::getConfig()->get('offset'))->toSql(true);
		//Set properties Excel
		$exporter->getProperties()
			->setCreator($user->name)
			->setLastModifiedBy($user->name)
			->setTitle('Events List Exported On ' . $createdDate)
			->setSubject('Events List Exported On ' . $createdDate)
			->setDescription('Events List Exported On ' . $createdDate);

		//Set some styles and layout for Excel file
		$borderedCenter = new PHPExcel_Style();
		$borderedCenter->applyFromArray(
			array(
				'alignment' => array(
					'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				),
				'font'      => array(
					'name' => 'Times New Roman', 'bold' => false, 'italic' => false, 'size' => 11
				),
				'borders'   => array(
					'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
					'right'  => array('style' => PHPExcel_Style_Border::BORDER_THIN),
					'top'    => array('style' => PHPExcel_Style_Border::BORDER_THIN),
					'left'   => array('style' => PHPExcel_Style_Border::BORDER_THIN),
				)
			)
		);

		$borderedLeft = new PHPExcel_Style();
		$borderedLeft->applyFromArray(
			array(
				'alignment' => array(
					'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				),
				'font'      => array(
					'name' => 'Times New Roman', 'bold' => false, 'italic' => false, 'size' => 11
				),
				'borders'   => array(
					'bottom' => array('style' => PHPExcel_Style_Border::BORDER_THIN),
					'right'  => array('style' => PHPExcel_Style_Border::BORDER_THIN),
					'top'    => array('style' => PHPExcel_Style_Border::BORDER_THIN),
					'left'   => array('style' => PHPExcel_Style_Border::BORDER_THIN),
				)
			)
		);


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

		$sheet  = $exporter->setActiveSheetIndex(0);
		$column = 'A';
		$row    = '1';
		foreach ($fields as $field)
		{
			$sheet->setCellValue($column . $row, $field);
			$sheet->getColumnDimension($column)->setAutoSize(true);
			$column++;
		}

		$row = 2;
		foreach ($rowEvents as $rowEvent)
		{
			$column = 'A';
			foreach ($fields as $field)
			{
				$cellData = empty($rowEvent->{$field}) ? '' : $rowEvent->{$field};
				$sheet->setCellValue($column . $row, $cellData);
				$sheet->getColumnDimension($column)->setAutoSize(true);
				$column++;
			}
			$row++;
		}

		header('Content-Type: application/vnd.ms-exporter');
		header('Content-Disposition: attachment;filename=events_list_on' . $createdDate . '.xlsx');
		header('Cache-Control: max-age=0');
		$objWriter = PHPExcel_IOFactory::createWriter($exporter, 'Excel2007');
		$objWriter->save('php://output');

		$this->app->close();
	}
}
