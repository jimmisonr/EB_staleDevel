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

class EventbookingControllerConfiguration extends EventbookingController
{
	/**
	 * Save configuration data
	 */
	public function save()
	{
		$data  = $this->input->getData(RAD_INPUT_ALLOWRAW);
		$model = $this->getModel();

		if ($data['multiple_booking'] && $data['activate_tickets_pdf'])
		{
			$data['activate_tickets_pdf'] = 0;

			$this->app->enqueueMessage('Tickets PDF feature only works with Individual / Group Registration for now. Please set Activate Shopping Cart config option to No if you want to use Ticket PDF feature', 'warning');
		}

		$model->store($data);

		$task = $this->getTask();

		if ($task == 'save')
		{
			$this->setRedirect('index.php?option=com_eventbooking&view=dashboard', JText::_('EB_CONFIGURATION_DATA_SAVED'));
		}
		else
		{
			$this->setRedirect('index.php?option=com_eventbooking&view=configuration', JText::_('EB_CONFIGURATION_DATA_SAVED'));
		}
	}

	/**
	 * Cancel configuration action, redirect back to dashboard
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_eventbooking&view=dashboard');
	}
}
