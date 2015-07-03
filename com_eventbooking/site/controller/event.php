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
class EventbookingControllerEvent extends EventbookingController
{

	/**
	 * Send invitation to friends
	 * @return void|boolean
	 */
	public function send_invite()
	{
		if (EventbookingHelper::getConfigValue('show_invite_friend'))
		{

			$config = EventbookingHelper::getConfig();
			$user   = JFactory::getUser();
			if ($config->enable_captcha && ($user->id == 0 || $config->bypass_captcha_for_registered_user !== '1'))
			{
				$input = JFactory::getApplication()->input;
				//Check captcha
				$captchaPlugin = JFactory::getApplication()->getParams()->get('captcha', JFactory::getConfig()->get('captcha'));
				$res           = JCaptcha::getInstance($captchaPlugin)->checkAnswer($input->post->get('recaptcha_response_field', '', 'string'));
				if (!$res)
				{
					JError::raiseWarning('', JText::_('EB_INVALID_CAPTCHA_ENTERED'));
					JRequest::setVar('view', 'invite');
					JRequest::setVar('layout', 'default');
					$this->display();

					return;
				}
			}
			$model = $this->getModel('invite');
			$post  = JRequest::get('post');
			$model->sendInvite($post);
			$this->setRedirect(
				JRoute::_('index.php?option=com_eventbooking&view=invite&layout=complete&tmpl=component&Itemid=' . JRequest::getInt('Itemid', 0),
					false));
		}
		else
		{
			JError::raiseError(403, JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));

			return false;
		}
	}

	public function save()
	{
		$post       = $this->input->getData();
		$model      = $this->getModel('event');
		$cid        = $post['cid'];
		$post['id'] = (int) $cid[0];
		$ret        = $model->store($post);
		if ($ret)
		{
			$msg = JText::_('Successfully saving event');
		}
		else
		{
			$msg = JText::_('Error while saving event');
		}

		$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('events', JRequest::getInt('Itemid')), false), $msg);
	}

	/**
	 * Publish the selected events
	 *
	 */
	public function publish()
	{
		//Check unpublish permission
		$user = JFactory::getUser();
		$db   = JFactory::getDbo();
		$id   = JRequest::getInt('id', 0);
		if (!$id)
		{
			$msg = JText::_('EB_INVALID_EVENT');
			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('events', JRequest::getInt('Itemid', 0)), false), $msg);

			return;
		}
		//Get the event object
		$sql = 'SELECT * FROM #__eb_events WHERE id=' . $id;
		$db->setQuery($sql);
		$rowEvent = $db->loadObject();
		if (!$rowEvent)
		{
			$msg = JText::_('EB_INVALID_EVENT');
			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('events', JRequest::getInt('Itemid', 0)), false), $msg);

			return;
		}
		if (!EventbookingHelper::canChangeEventStatus($id))
		{
			$msg = JText::_('EB_NO_PUBLISH_PERMISSION');
			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('events', JRequest::getInt('Itemid', 0)), false), $msg);

			return;
		}
		//OK, enough permission checked. Publish the event		
		$model = $this->getModel('event');
		$ret   = $model->publish($id, 1);
		if ($ret)
		{
			$msg = JText::_('EB_PUBLISH_SUCCESS');
		}
		else
		{
			$msg = JText::_('EB_PUBLISH_ERROR');
		}
		$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('events', JRequest::getInt('Itemid', 0)), false), $msg);
	}

	/**
	 * Unpublish the selected events
	 *
	 */
	public function unpublish()
	{
		$db   = JFactory::getDbo();
		$user = JFactory::getUser();
		$id   = JRequest::getInt('id', 0);
		if (!$id)
		{
			$msg = JText::_('EB_INVALID_EVENT');
			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('events', JRequest::getInt('Itemid', 0)), false), $msg);

			return;
		}
		//Get the event object
		$sql = 'SELECT * FROM #__eb_events WHERE id=' . $id;
		$db->setQuery($sql);
		$rowEvent = $db->loadObject();
		if (!$rowEvent)
		{
			$msg = JText::_('EB_INVALID_EVENT');
			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('events', JRequest::getInt('Itemid')), false), $msg);

			return;
		}

		if (!EventbookingHelper::canChangeEventStatus($id))
		{
			$msg = JText::_('EB_NO_UNPUBLISH_PERMISSION');
			$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('events', JRequest::getInt('Itemid')), false), $msg);

			return;
		}
		$model = $this->getModel('event');
		$ret   = $model->publish($id, 0);
		if ($ret)
		{
			$msg = JText::_('EB_UNPUBLISH_SUCCESS');
		}
		else
		{
			$msg = JText::_('EB_UNPUBLISH_ERROR');
		}
		$this->setRedirect(JRoute::_(EventbookingHelperRoute::getViewRoute('events', JRequest::getInt('Itemid', 0)), false), $msg);
	}

	/**
	 * Redirect user to events mangement page
	 *
	 */
	public function cancel()
	{
		$this->setRedirect(JRoute::_('index.php?option=com_eventbooking&view=events&Itemid=' . JRequest::getInt('Itemid', 0), false));
	}


	public function download_ical()
	{
		$eventId = $this->input->getInt('event_id');
		if ($eventId)
		{
			$config      = EventbookingHelper::getConfig();
			$fieldSuffix = EventbookingHelper::getFieldSuffix();
			$db          = JFactory::getDbo();
			$query       = $db->getQuery(true);
			$query->select('*')
				->select('title' . $fieldSuffix . ' AS title, short_description' . $fieldSuffix . ' AS short_description, description' . $fieldSuffix . ' AS description')
				->from('#__eb_events')
				->where('id = ' . $eventId);
			$db->setQuery($query);
			$event = $db->loadObject();

			$query->clear();
			$query->select('a.*')
				->from('#__eb_locations AS a')
				->innerJoin('#__eb_events AS b ON a.id=b.location_id')
				->where('b.id=' . $eventId);

			$db->setQuery($query);
			$rowLocation = $db->loadObject();

			if ($config->from_name)
			{
				$fromName = $config->from_name;
			}
			else
			{
				$fromName = JFactory::getConfig()->get('from_name');
			}
			if ($config->from_email)
			{
				$fromEmail = $config->from_email;
			}
			else
			{
				$fromEmail = JFactory::getConfig()->get('mailfrom');
			}

			$ics = new EventbookingHelperIcs();
			$ics->setName($event->title)
				->setDescription($event->short_description)
				->setOrganizer($fromEmail, $fromName)
				->setStart($event->event_date)
				->setEnd($event->event_end_date);

			if ($rowLocation)
			{
				$ics->setLocation($rowLocation->name);
			}

			$ics->download();
		}
	}
}