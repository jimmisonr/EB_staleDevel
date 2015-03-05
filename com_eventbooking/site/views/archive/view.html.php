<?php
/**
 * @version        	1.7.0
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventBookingViewArchive extends JViewLegacy
{

	function display($tpl = null)
	{
		JFactory::getDocument()->setTitle(JText::_('EB_EVENTS_ARCHIVE'));
		$model = $this->getModel();
		$state = $model->getState();
		$items = $model->getData();
		$config = EventbookingHelper::getConfig();
		if ($config->process_plugin)
		{
			for ($i = 0, $n = count($items); $i < $n; $i++)
			{
				$item = $items[$i];
				$item->short_description = JHtml::_('content.prepare', $item->short_description);
			}
		}
		if ($state->id)
		{
			$this->category = $model->getCategory();
		}
		if ($config->show_list_of_registrants)
		{
			EventbookingHelperJquery::colorbox('eb-colorbox-register-lists');
		}
		if ($config->show_location_in_category_view)
		{
			$width = (int) $config->map_width ;
			if (!$width)
			{
				$width = 800 ;
			}
			$height = (int) $config->map_height ;
			if (!$height)
			{
				$height = 600 ;
			}
			EventbookingHelperJquery::colorbox('eb-colorbox-map', $width.'px', $height.'px', 'true', 'false');
		}
		$this->items = $items;
		$this->pagination = $model->getPagination();
		$this->Itemid = JRequest::getInt('Itemid', 0);
		$this->config = $config;
		$this->nullDate = JFactory::getDbo()->getNullDate();
		$this->categoryId = $state->id;
		
		parent::display($tpl);
	}
}