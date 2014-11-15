<?php
/**
 * @version        	1.6.8
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2014 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventBookingViewCategory extends JViewLegacy
{

	function display($tpl = null)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		$nullDate = $db->getNullDate();
		$config = EventbookingHelper::getConfig();
		$document = JFactory::getDocument();
		$pathway = $app->getPathway();
		$model = $this->getModel();
		$state = $model->getState();
		$categoryId = $state->id;
		$Itemid = $app->input->getInt('Itemid', 0);
		if ($categoryId)
		{
			EventbookingHelper::checkCategoryAccess($categoryId);
		}
		$items = $model->getData();
		$pagination = $model->getPagination();
		$category = $model->getCategory();
		$pageTitle = JText::_('EB_CATEGORY_PAGE_TITLE');
		$pageTitle = str_replace('[CATEGORY_NAME]', $category->name, $pageTitle);
		$document->setTitle($pageTitle);
		
		if ($category->meta_keywords)
		{
			$document->setMetaData('keywords', $category->meta_keywords);
		}
		if ($category->meta_description)
		{
			$document->setMetaData('description', $category->meta_description);
		}
		if ($config->process_plugin)
		{
			for ($i = 0, $n = count($items); $i < $n; $i++)
			{
				$item = $items[$i];
				$item->short_description = JHtml::_('content.prepare', $item->short_description);
			}
			if ($category)
			{
				$category->description = JHtml::_('content.prepare', $category->description);
			}
		}
		if ($config->event_custom_field && $config->show_event_custom_field_in_category_layout)
		{
			$params = new JRegistry();
			$xml = JFactory::getXML(JPATH_COMPONENT . '/fields.xml');
			$fields = $xml->fields->fieldset->children();
			$customFields = array();
			foreach ($fields as $field)
			{
				$name = $field->attributes()->name;
				$label = JText::_($field->attributes()->label);
				$customFields["$name"] = $label;
			}
			for ($i = 0, $n = count($items); $i < $n; $i++)
			{
				$item = & $items[$i];
				$params->loadString($item->custom_fields, 'INI');
				$paramData = array();
				foreach ($customFields as $name => $label)
				{
					$paramData[$name]['title'] = $label;
					$paramData[$name]['value'] = $params->get($name);
				}
				
				$item->paramData = $paramData;
			}
		}
		
		//Handle breadcrump
		$app = JFactory::getApplication();
		$menu = $app->getMenu();
		$menuItem = $menu->getActive();
		if ($menuItem)
		{
			if (isset($menuItem->query['view']) && ($menuItem->query['view'] == 'categories' || $menuItem->query['view'] == 'category'))
			{
				$parentId = (int) $menuItem->query['id'];
				if ($state->id)
				{
					$pathway = $app->getPathway();
					$paths = EventbookingHelperData::getCategoriesBreadcrumb($state->id, $parentId);
					for ($i = count($paths) - 1; $i >= 0; $i--)
					{
						$path = $paths[$i];
						$pathUrl = EventbookingHelperRoute::getCategoryRoute($path->id, $Itemid);
						$pathway->addItem($path->name, $pathUrl);
					}
				}
			}
		}
		$_SESSION['last_category_id'] = $categoryId;
		//Override layout for this category
		$layout = $this->getLayout();
		if ($layout == '' || $layout == 'default')
		{
			if ($category->layout)
			{
				$this->setLayout($category->layout);
			}
		}
		$layout = $this->getLayout();
		if ($layout == 'calendar')
		{
			$this->_displayCalendarView($tpl);
			return;
		}
		$userId = $user->get('id');
		$viewLevels = $user->getAuthorisedViewLevels();
		JLoader::register('EventbookingModelCategories', JPATH_ROOT . '/components/com_eventbooking/models/categories.php');
		if ($categoryId > 0)
		{
			$this->categories = RADModel::getInstance('Categories', 'EventbookingModel', array('table_prefix' => '#__eb_', 'ignore_session' => true))->limitstart(
				0)
				->limit(0)
				->filter_order('tbl.ordering')
				->id($categoryId)
				->getData();
		}
		else
		{
			$this->categories = array();
		}
		if ($config->multiple_booking)
		{
			EventbookingHelperJquery::colorbox('eb-colorbox-addcart', '800px', '450px', 'false', 'false');
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
		$this->viewLevels = $viewLevels;
		$this->userId = $userId;
		$this->items = $items;
		$this->pagination = $pagination;
		$this->config = $config;
		$this->category = $category;
		$this->nullDate = $nullDate;
		$this->Itemid = $Itemid;
		
		parent::display($tpl);
	}

	/**
	 * Display calendar view to user in a category
	 *
	 */
	function _displayCalendarView($tpl)
	{
		$Itemid = JRequest::getInt('Itemid', 0);
		$config = EventbookingHelper::getConfig();
		//Initialize default month and year
		$month = JRequest::getInt('month');
		$year = JRequest::getInt('year');
		if (!$month)
		{
			$month = JRequest::getInt('default_month', 0);
			if ($month)
			{
				JRequest::setVar('month', $month);
			}
		}
		if (!$year)
		{
			$year = JRequest::getInt('default_year', 0);
			if ($year)
			{
				JRequest::setVar('year', $year);
			}
		}
		$category = $this->get('Category');
		$model = $this->getModel();
		list ($year, $month, $day) = $model->getYMD();
		$rows = $model->getEventsByMonth($year, $month);
		$this->data = EventbookingHelperData::getCalendarData($rows, $year, $month);
		$this->month = $month;
		$this->year = $year;
		$listMonth = array(
			JText::_('EB_JAN'), 
			JText::_('EB_FEB'), 
			JText::_('EB_MARCH'), 
			JText::_('EB_APR'), 
			JText::_('EB_MAY'), 
			JText::_('EB_JUNE'), 
			JText::_('EB_JULY'), 
			JText::_('EB_AUG'), 
			JText::_('EB_SEP'), 
			JText::_('EB_OCT'), 
			JText::_('EB_NOV'), 
			JText::_('EB_DEC'));
		$options = array();
		foreach ($listMonth as $key => $monthName)
		{
			if ($key < 9)
			{
				$value = "0" . ($key + 1);
			}
			else
			{
				$value = $key + 1;
			}
			$options[] = JHtml::_('select.option', $value, $monthName);
		}
		$this->searchMonth = JHtml::_('select.genericlist', $options, 'month', 'class="input-medium" onchange="submit();" ', 'value', 'text', $month);
		$options = array();
		for ($i = $year - 3; $i < ($year + 5); $i++)
		{
			$options[] = JHtml::_('select.option', $i, $i);
		}
		$this->searchYear = JHtml::_('select.genericlist', $options, 'year', 'class="input-small" onchange="submit();" ', 'value', 'text', $year);
		$this->category = $category;
		$this->config = $config;
		$this->Itemid = $Itemid;
		$this->listMonth = $listMonth;
		
		parent::display($tpl);
	}
}