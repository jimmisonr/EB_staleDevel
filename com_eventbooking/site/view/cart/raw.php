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
defined('_JEXEC') or die;

class EventbookingViewCartRaw extends RADViewHtml
{
	/**
	 * Display shopping cart
	 */
	public function display()
	{
		$this->setLayout('mini');
		$config     = EventbookingHelper::getConfig();
		$categoryId = (int) JFactory::getSession()->get('last_category_id', 0);
		if (!$categoryId)
		{
			//Get category ID of the current event
			$cart     = new EventbookingHelperCart();
			$eventIds = $cart->getItems();
			if (count($eventIds))
			{
				$db          = JFactory::getDbo();
				$query       = $db->getQuery(true);
				$lastEventId = $eventIds[count($eventIds) - 1];
				$query->select('category_id')
					->from('#__eb_event_categories')
					->where('event_id = ' . (int) $lastEventId);
				$db->setQuery($query);
				$categoryId = $db->loadResult();
			}
		}
		$items = $this->model->getData();
		//Generate javascript string
		$jsString = " var arrEventIds = new Array() \n; var arrQuantities = new Array();\n";
		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			$item = $items[$i];
			if ($item->event_capacity == 0)
			{
				$availbleQuantity = -1;
			}
			else
			{
				$availbleQuantity = $item->event_capacity - $item->total_registrants;
			}
			$jsString .= "arrEventIds[$i] = $item->id ;\n";
			$jsString .= "arrQuantities[$i] = $availbleQuantity ;\n";
		}
		$this->items           = $items;
		$this->config          = $config;
		$this->categoryId      = $categoryId;
		$this->jsString        = $jsString;
		$this->bootstrapHelper = new EventbookingHelperBootstrap($config->twitter_bootstrap_version);

		parent::display();
	}
}