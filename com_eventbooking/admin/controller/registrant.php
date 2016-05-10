<?php
/**
 * @version            2.5.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

class EventbookingControllerRegistrant extends EventbookingController
{
	/**
	 * Send batch mail to registrants
	 *
	 */
	public function batch_mail()
	{
		$model = $this->getModel();
		try
		{
			$model->batchMail($this->input);
			$this->setMessage(JText::_('EB_BATCH_MAIL_SUCCESS'));
		}
		catch(Exception $e)
		{
			$this->setMessage($e->getMessage(), 'error');
		}

		$this->setRedirect('index.php?option=com_eventbooking&view=registrants');
	}

	/**
	 * Resend confirmation email to registrants in case they didn't receive it
	 */
	public function resend_email()
	{
		$cid   = $this->input->get('cid', array(), 'array');
		$id    = (int) $cid[0];
		$model = $this->getModel();
		$ret   = $model->resendEmail($id);
		if ($ret)
		{
			$this->setMessage(JText::_('EB_EMAIL_SUCCESSFULLY_RESENT'));
		}
		else
		{
			$this->setMessage(JText::_('EB_COULD_NOT_RESEND_EMAIL_TO_GROUP_MEMBER'), 'notice');
		}

		$this->setRedirect('index.php?option=com_eventbooking&view=registrants');
	}

	/**
	 * Export registrants into a CSV file
	 */
	public function export()
	{
		set_time_limit(0);
		$config    = EventbookingHelper::getConfig();
		$model = $this->getModel('registrants');

		/* @var EventbookingModelRegistrants $model*/
		$model->setState('limitstart', 0)
			->setState('limit', 0)
			->setState('filter_order', 'tbl.id')
			->setState('filter_order_Dir', 'ASC');

		$rows = $model->getData();

		if (count($rows) == 0)
		{
			$this->setMessage(JText::_('There are no registrants to export'));
			$this->setRedirect('index.php?option=com_eventbooking&view=dashboard');

			return;
		}

		$rowFields = EventbookingHelper::getAllEventFields((int) $model->getState('filter_event_id'));
		$fieldIds = array();
		foreach($rowFields as $rowField)
		{
			$fieldIds[] = $rowField->id;
		}

		$fieldValues = $model->getFieldsData($fieldIds);

		EventbookingHelperData::csvExport($rows, $config, $rowFields, $fieldValues);
	}

	/**
	 * Download invoice of the given registration record
	 */
	public function download_invoice()
	{
		$id = $this->input->getInt('id');
		EventbookingHelper::downloadInvoice($id);
	}
}