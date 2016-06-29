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

class EventbookingControllerLocation extends EventbookingController
{
	/**
	 * save location
	 *
	 */
	public function save()
	{
		$this->csrfProtection();
		if (JFactory::getUser()->authorise('eventbooking.addlocation', 'com_eventbooking'))
		{
			$post  = $this->input->post->getData();
			$model = $this->getModel();
			try
			{
				$model->store($post);
				$msg = JText::_('EB_LOCATION_SAVED');
			}
			catch (Exception $e)
			{
				$msg = JText::_('EB_ERROR_SAVING_LOCATION') . ':' . $e->getMessage();
			}

			$this->setRedirect(JRoute::_('index.php?option=com_eventbooking&view=locations&Itemid=' . $this->input->getInt('Itemid', 0)), $msg);
		}
	}

	/**
	 * Save location from ajax request
	 */
	public function save_ajax()
	{
		$this->csrfProtection();
		if (JFactory::getUser()->authorise('eventbooking.addlocation', 'com_eventbooking'))
		{
			$post  = $this->input->post->getData();
			$model = $this->getModel();

			$json = array();
			try
			{
				$model->store($post);
				$json['success'] = true;
				$json['id']      = $post['id'];
				$json['name']    = $post['name'];
			}
			catch (Exception $e)
			{
				$json['success'] = false;
				$json['message'] = $e->getMessage();
			}

			echo json_encode($json);
			$this->app->close();
		}
	}

	/**
	 * Delete location
	 */
	public function delete()
	{
		$this->csrfProtection();
		// Check permission
		if (!JFactory::getUser()->authorise('eventbooking.addlocation', 'com_eventbooking'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('EB_NO_PERMISSION'));

			return;
		}


		$model = $this->getModel();
		$cid   = JRequest::getVar('cid', array());
		JArrayHelper::toInteger($cid);
		$model->delete($cid);
		$msg = JText::_('EB_LOCATION_REMOVED');
		$this->setRedirect(JRoute::_('index.php?option=com_eventbooking&view=locations&Itemid=' . $this->input->getInt('Itemid', 0)), $msg);
	}

	/**
	 * Cancel location edit, redirect to location list page
	 */
	public function cancel()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_eventbooking&view=locations&Itemid=' . $this->input->getInt('Itemid', 0)));
	}
}