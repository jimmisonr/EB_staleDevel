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
defined('_JEXEC') or die();

class EventbookingViewCategoriesHtml extends RADViewHtml
{

	public function display()
	{
		$app    = JFactory::getApplication();
		$active = $app->getMenu()->getActive();
		$params = EventbookingHelper::getViewParams($active, array('categories'));

		$config      = EventbookingHelper::getConfig();
		$model       = $this->getModel();
		$items       = $model->getData();
		$pagination  = $model->getPagination();
		$this->state = $model->getState();
		$categoryId  = $this->state->id;
		if ($categoryId)
		{
			EventbookingHelper::checkCategoryAccess($categoryId);
		}

		if ($categoryId)
		{
			$db          = JFactory::getDbo();
			$query       = $db->getQuery(true);
			$fieldSuffix = EventbookingHelper::getFieldSuffix();
			$query->select('*, name' . $fieldSuffix . ' AS name')
				->from('#__eb_categories')
				->where('id=' . $categoryId);
			$db->setQuery($query);
			$category       = $db->loadObject();
			$this->category = $category;
		}

		// Process page meta data
		if (!$params->get('page_title'))
		{
			if ($categoryId && !empty($category))
			{
				$pageTitle = JText::_('EB_SUB_CATEGORIES_PAGE_TITLE');
				$pageTitle = str_replace('[CATEGORY_NAME]', $category->name, $pageTitle);
			}
			else
			{
				$pageTitle = JText::_('EB_CATEGORIES_PAGE_TITLE');
			}

			$params->set('page_title', $pageTitle);
		}

		EventbookingHelperHtml::prepareDocument($params, isset($category) ? $category : null);

		// Process content plugin  for categories
		if ($config->process_plugin)
		{
			for ($i = 0, $n = count($items); $i < $n; $i++)
			{
				$item              = $items[$i];
				$item->description = JHtml::_('content.prepare', $item->description);
			}
			if (!empty($this->category))
			{
				$this->category->description = JHtml::_('content.prepare', $this->category->description);
			}
		}

		$this->categoryId = $categoryId;
		$this->config     = $config;
		$this->items      = $items;
		$this->pagination = $pagination;
		$this->params     = $params;

		parent::display();
	}
}