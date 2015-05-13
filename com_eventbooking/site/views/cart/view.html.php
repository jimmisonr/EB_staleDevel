<?php
/**
 * @version        	1.7.3
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventBookingViewCart extends JViewLegacy
{

	/**
	 * Display interface to user
	 *
	 * @param string $tpl
	 */
	function display($tpl = null)
	{
		$layout = $this->getLayout();
		if ($layout == 'mini')
		{
			$this->setLayout('mini');
		}
		else
		{
			$this->setLayout('default');
		}
		$Itemid = JRequest::getInt('Itemid', 0);
		$config = EventbookingHelper::getConfig();
		if (isset($_SESSION['last_category_id']))
		{
			$categoryId = $_SESSION['last_category_id'];
		}
		else
		{
			//Get category ID of the current event			
			$cart = new EventbookingHelperCart();
			$eventIds = $cart->getItems();
			if (count($eventIds))
			{
				$db = JFactory::getDbo();
				$lastEventId = $eventIds[count($eventIds) - 1];
				$sql = 'SELECT category_id FROM #__eb_event_categories WHERE event_id=' . $lastEventId;
				$db->setQuery($sql);
				$categoryId = $db->loadResult();
			}
			else
			{
				$categoryId = 0;
			}
		}
		$items = $this->get('Data');
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
		$this->items = $items;
		$this->config = $config;
		$this->categoryId = $categoryId;
		$this->Itemid = $Itemid;
		$this->jsString = $jsString;
		$this->bootstrapHelper = new EventbookingHelperBootstrap($config->twitter_bootstrap_version);
		
		parent::display($tpl);
	}
}