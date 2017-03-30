<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2017 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

class EventbookingControllerScan extends EventbookingController
{
	public function icody()
	{
		$ticketCode = $this->input->getString('value');

		$success = false;
		$message = '';

		if ($ticketCode)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('id')
				->from('#__eb_registrants')
				->where('ticket_code = ' . $db->quote($ticketCode));
			$db->setQuery($query);
			$id = (int) $db->loadResult();
			if ($id)
			{
				/* @var EventbookingModelRegistrant $model */
				$model  = $this->getModel('Registrant');
				$result = $model->checkin($id);

				switch ($result)
				{
					case 0:
						$message = JText::_('EB_INVALID_REGISTRATION_RECORD');
						break;
					case 1:
						$message = JText::_('EB_REGISTRANT_ALREADY_CHECKED_IN');
						break;
					case 2:
						$message = JText::_('EB_CHECKED_IN_SUCCESSFULLY');
						$success = true;
						break;
				}
			}
			else
			{
				$message = JText::_('EB_INVALID_TICKET_CODE');
			}
		}
		else
		{
			$message = JText::_('EB_TICKET_CODE_IS_EMPTY');
		}

		if ($success)
		{
			$title = JText::_('EB_CHECKIN_SUCCESS');
		}
		else
		{
			$title = JText::_('EB_CHECKIN_FAILURE');
		}


		echo static::getIcodyMessage($title, $message);

		$this->app->close();
	}

	/**
	 * @param $title
	 * @param $msg
	 *
	 * @return string
	 */
	public static function getIcodyMessage($title, $msg)
	{
		$message = '<?xml version="1.0" encoding="UTF-8"?>';
		$message .= '<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">';
		$message .= '<plist version="1.0">';
		$message .= '<dict>';
		$message .= '    <key>type</key>';
		$message .= '    <string>alert</string>';
		$message .= '    <key>title</key>';
		$message .= '   <string>' . $title . '</string>';
		$message .= '   <key>message</key>';
		$message .= '    <string>' . $msg . '</string>';
		$message .= '</dict>';
		$message .= '</plist>';

		return $message;
	}
}
