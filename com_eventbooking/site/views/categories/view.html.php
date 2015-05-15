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
		$document = JFactory::getDocument();
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
			$pageTitle = JText::_('EB_SUB_CATEGORIES_PAGE_TITLE');
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
			$this->category = $category;
		}
		else
		{
			$document->setTitle(JText::_('EB_CATEGORIES_PAGE_TITLE'));
		}
		$this->categoryId = $categoryId;
		$this->config = $config;
		$this->items = $items;
		$this->pagination = $pagination;
		$this->Itemid = JFactory::getApplication()->input->getInt('Itemid', 0);
		
		parent::display($tpl);
	}
}