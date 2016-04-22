<?php
/**
 * @version            2.4.3
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

class EventbookingViewMapHtml extends RADViewHtml
{
	public $hasModel = false;

	public function display()
	{
		$this->setLayout('default');
		$locationId     = $this->input->getInt('location_id', 0);
		$location       = EventbookingHelperDatabase::getLocation($locationId);
		$this->location = $location;
		$this->config   = EventbookingHelper::getConfig();

		parent::display();
	}
}