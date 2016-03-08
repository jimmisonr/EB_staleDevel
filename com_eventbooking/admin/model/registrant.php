<?php
/**
 * @version            2.4.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

class EventbookingModelRegistrant extends EventbookingModelCommonRegistrant
{

	/**
	 * Instantiate the model.
	 *
	 * @param array $config configuration data for the model
	 *
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->state->insert('filter_event_id', 'int', 0);
	}

	/**
	 * Initial registrant data
	 *
	 * @see RADModelAdmin::initData()
	 */
	public function initData()
	{
		parent::initData();
		$this->data->event_id = $this->state->filter_event_id;
	}

	/**
	 * Resend confirmation email to registrant
	 *
	 * @param $id
	 *
	 * @return bool True if email is successfully delivered
	 */
	public function resendEmail($id)
	{
		$row = $this->getTable();
		$row->load($id);
		if ($row->group_id > 0)
		{
			// We don't send email to group members, return false
			return false;
		}

		// Load the default frontend language
		$lang = JFactory::getLanguage();
		$tag  = $row->language;
		if (!$tag || $tag == '*')
		{
			$tag = JComponentHelper::getParams('com_languages')->get('site', 'en-GB');
		}
		$lang->load('com_eventbooking', JPATH_ROOT, $tag);

		$config = EventbookingHelper::getConfig();
		EventbookingHelper::sendEmails($row, $config);

		return true;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param array $cid   A list of the primary keys to change.
	 * @param int   $state The value of the published state.
	 *
	 * @throws Exception
	 */
	public function publish($cid, $state = 1)
	{
		$db = $this->getDbo();
		if (($state == 1) && count($cid))
		{
			JPluginHelper::importPlugin('eventbooking');
			$config     = EventbookingHelper::getConfig();
			$row        = new RADTable('#__eb_registrants', 'id', $db);
			foreach ($cid as $registrantId)
			{
				$row->load($registrantId);
				if (!$row->published)
				{
					EventbookingHelper::sendRegistrationApprovedEmail($row, $config);

					// Trigger event
					JFactory::getApplication()->triggerEvent('onAfterPaymentSuccess', array($row));
				}
			}
		}

		$cids  = implode(',', $cid);
		$query = $db->getQuery(true);
		$query->update('#__eb_registrants')
			->set('published = ' . (int) $state)
			->where("(id IN ($cids) OR group_id IN ($cids))")
			->where("payment_method LIKE 'os_offline%'");
		$db->setQuery($query);
		$db->execute();
	}
}