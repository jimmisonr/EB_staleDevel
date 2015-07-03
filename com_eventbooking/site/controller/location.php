<?php
/**
 * @version            2.0.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

/**
 * Event Booking controller
 * @package        Joomla
 * @subpackage     Event Booking
 */
class EventbookingControllerLocation extends EventbookingController
{
	/**
	 * save location
	 *
	 */
	public function save_location()
	{
		$post       = JRequest::get('post', JREQUEST_ALLOWHTML);
		$model      = $this->getModel('addlocation');
		$cid        = $post['cid'];
		$post['id'] = (int) $cid[0];
		$ret        = $model->store($post);
		if ($ret)
		{
			$msg = JText::_('EB_LOCATION_SAVED');
		}
		else
		{
			$msg = JText::_('EB_SAVING_LOCATION_ERROR');
		}

		$this->setRedirect(JRoute::_('index.php?option=com_eventbooking&view=locationlist&Itemid=' . JRequest::getInt('Itemid', 0)), $msg);
	}

	public function delete_location()
	{
		$model = $this->getModel('addlocation');
		$cid   = JRequest::getVar('cid', array());
		JArrayHelper::toInteger($cid);
		$model->delete($cid);
		$msg = JText::_('EB_LOCATION_REMOVED');
		$this->setRedirect(JRoute::_('index.php?option=com_eventbooking&view=locationlist&Itemid=' . JRequest::getInt('Itemid', 0)), $msg);
	}

	/**
	 * Redirect user to locations
	 *
	 */
	public function cancel_location()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_eventbooking&view=locationlist&Itemid=' . JRequest::getInt('Itemid', 0)));
	}
}