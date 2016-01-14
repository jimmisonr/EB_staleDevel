<?php
/**
 * @version            2.3.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

class os_offline extends RADPayment
{

	/**
	 * Constructor
	 *
	 * @param \Joomla\Registry\Registry $params
	 * @param array                     $config
	 */
	public function __construct($params, $config = array())
	{
		parent::__construct($params, $config);
	}

	/**
	 * Process payment
	 *
	 */
	public function processPayment($row, $data)
	{
		$app    = JFactory::getApplication();
		$Itemid = $app->input->getInt('Itemid', 0);
		$config = EventbookingHelper::getConfig();
		if ($row->is_group_billing)
		{
			EventbookingHelper::updateGroupRegistrationRecord($row->id);
		}
		EventbookingHelper::sendEmails($row, $config);
		$url = JRoute::_('index.php?option=com_eventbooking&view=complete&Itemid=' . $Itemid, false, false);
		$app->redirect($url);
	}
}