<?php
/**
 * @version        	1.7.2
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventBookingViewWaitinglist extends JViewLegacy
{

	/**
	 * Display interface to user
	 *
	 * @param string $tpl
	 */
	function display($tpl = null)
	{
		$db               = JFactory::getDbo();
		$query            = $db->getQuery(true);
		$config           = EventbookingHelper::getConfig();
		$registrationCode = JFactory::getSession()->get('eb_registration_code', '');
		if ($registrationCode)
		{
			$sql = 'SELECT id FROM #__eb_registrants WHERE registration_code="' . $registrationCode . '" ORDER BY id LIMIT 1 ';
			$db->setQuery($sql);
			$id = (int) $db->loadResult();
		}
		else
		{
			JFactory::getApplication()->redirect('index.php', JText::_('EB_INVALID_REGISTRATION_CODE'));
		}
		if (!$id)
		{
			JFactory::getApplication()->redirect('index.php', JText::_('EB_INVALID_REGISTRATION_CODE'));
		}
		$query->select('a.*, b.payment_method')
			->from('#__eb_events  AS a ')
			->innerJoin('#__eb_registrants AS b ON a.id = b.event_id')
			->where('b.id=' . $id);
		$db->setQuery($query);
		$rowEvent    = $db->loadObject();
		$message     = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		if (strlen(strip_tags($message->{'waitinglist_complete_message' . $fieldSuffix})))
		{
			$msg = $message->{'waitinglist_complete_message' . $fieldSuffix};
		}
		else
		{
			$msg = $message->waitinglist_complete_message;
		}

		$query->clear();
		$query->select('*')
			->from('#__eb_registrants')
			->where('id=' . $id);
		$db->setQuery($query);
		$rowRegistrant = $db->loadObject();
		if (EventbookingHelper::isGroupRegistration($rowRegistrant->id))
		{
			$rowFields = EventbookingHelper::getFormFields($rowEvent->id, 1);
		}
		else
		{
			$rowFields = EventbookingHelper::getFormFields($rowEvent->id, 0);
		}
		$form = new RADForm($rowFields);
		$data = EventbookingHelper::getRegistrantData($rowRegistrant, $rowFields);
		$form->bind($data);
		$form->buildFieldsDependency();
		$replaces = EventbookingHelper::buildTags($rowRegistrant, $form, $rowEvent, $config);
		foreach ($replaces as $key => $value)
		{
			$key          = strtoupper($key);
			$msg = str_replace("[$key]", $value, $msg);
		}
		$this->message          = $msg;

		parent::display($tpl);
	}
}