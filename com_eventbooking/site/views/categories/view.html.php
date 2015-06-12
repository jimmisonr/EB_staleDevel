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
class EventBookingViewCategories extends JViewLegacy
{

	function display($tpl = null)
	{
		$app      = JFactory::getApplication();
		$document = JFactory::getDocument();
		$active   = $app->getMenu()->getActive();
		$params   = EventbookingHelper::getViewParams($active, array('categories'));

		$config = EventbookingHelper::getConfig();
		$model = $this->getModel();
		$items = $model->getData();
		$pagination = $model->getPagination();
		$this->state = $model->getState();
		$categoryId = $this->state->id;
		if ($categoryId)
		{
			EventbookingHelper::checkCategoryAccess($categoryId);
		}
		if ($categoryId)
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$fieldSuffix = EventbookingHelper::getFieldSuffix();
			$query->select('*, name' . $fieldSuffix . ' AS name')
				->from('#__eb_categories')
				->where('id=' . $categoryId);
			$db->setQuery($query);
			$category = $db->loadObject();
			$this->category = $category;
		}

		// Process page meta data
		if ($params->get('page_title'))
		{
			$pageTitle = $params->get('page_title');
		}
		elseif($categoryId && !empty($category))
		{
			$pageTitle = JText::_('EB_SUB_CATEGORIES_PAGE_TITLE');
			$pageTitle = str_replace('[CATEGORY_NAME]', $category->name, $pageTitle);
		}
		else
		{
			$pageTitle = JText::_('EB_CATEGORIES_PAGE_TITLE');
		}
		$siteNamePosition = JFactory::getConfig()->get('sitename_pagetitles');
		if ($siteNamePosition == 0)
		{
			$document->setTitle($pageTitle);
		}
		elseif ($siteNamePosition == 1)
		{
			$document->setTitle($app->get('sitename') . ' - ' . $pageTitle);
		}
		else
		{
			$document->setTitle($pageTitle . ' - ' . $app->get('sitename'));
		}

		if (!empty($category) && $category->meta_keywords)
		{
			$document->setMetaData('keywords', $category->meta_keywords);
		}
		elseif ($params->get('menu-meta_keywords'))
		{
			$document->setMetadata('keywords', $params->get('menu-meta_keywords'));
		}
		if (!empty($category) && $category->meta_description)
		{
			$document->setMetaData('description', $category->meta_description);
		}
		elseif ($params->get('menu-meta_description'))
		{
			$document->setDescription($params->get('menu-meta_description'));
		}

		if ($params->get('robots'))
		{
			$document->setMetadata('robots', $params->get('robots'));
		}

		// Process content plugin  for categories
		if ($config->process_plugin)
		{
			for ($i = 0, $n = count($items); $i < $n; $i++)
			{
				$item = $items[$i];
				$item->description = JHtml::_('content.prepare', $item->description);
			}
			if (!empty($this->category))
			{
				$this->category->description = JHtml::_('content.prepare', $this->category->description);
			}
		}

		$this->categoryId = $categoryId;
		$this->config = $config;
		$this->items = $items;
		$this->pagination = $pagination;
		$this->Itemid = JFactory::getApplication()->input->getInt('Itemid', 0);
		$this->params = $params;
		
		parent::display($tpl);
	}
}