<?php
/**
 * @version            2.0.5
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die;

class EventbookingViewUpcomingeventsHtml extends RADViewHtml
{

	public function display()
	{
		$app    = JFactory::getApplication();
		$active = $app->getMenu()->getActive();
		$config = EventbookingHelper::getConfig();
		$user   = JFactory::getUser();
		$model  = $this->getModel();
		$state  = $model->getState();
		$items  = $model->getData();

		// Check category access
		if ($state->id)
		{
			$category = EventbookingHelperDatabase::getCategory($state->id);
			if (empty($category) || !in_array($category->access, JFactory::getUser()->getAuthorisedViewLevels()))
			{
				$app->redirect('index.php', JText::_('EB_INVALID_CATEGORY_OR_NOT_AUTHORIZED'));
			}
		}
		else
		{
			$category = null;
		}

		if ($config->process_plugin)
		{
			for ($i = 0, $n = count($items); $i < $n; $i++)
			{
				$item                    = $items[$i];
				$item->short_description = JHtml::_('content.prepare', $item->short_description);
			}
		}

		if ($config->event_custom_field && $config->show_event_custom_field_in_category_layout)
		{
			EventbookingHelperData::prepareCustomFieldsData($items);
		}

		$params = EventbookingHelper::getViewParams($active, array('upcomingevents'));
		if (!$params->get('page_title'))
		{
			$pageTitle = JText::_('EB_UPCOMING_EVENTS_PAGE_TITLE');
			if ($category)
			{
				$pageTitle = str_replace('[CATEGORY_NAME]', $category->name, $pageTitle);
			}

			$params->set('page_title', $pageTitle);
		}

		EventbookingHelperHtml::prepareDocument($params, $category);

		if ($config->multiple_booking)
		{
			if ($this->deviceType == 'mobile')
			{
				EventbookingHelperJquery::colorbox('eb-colorbox-addcart', '100%', '450px', 'false', 'false');
			}
			else
			{
				EventbookingHelperJquery::colorbox('eb-colorbox-addcart', '800px', 'false', 'false', 'false', 'false');
			}
		}
		if ($config->show_list_of_registrants)
		{
			EventbookingHelperJquery::colorbox('eb-colorbox-register-lists');
		}
		if ($config->show_location_in_category_view || ($this->getLayout() == 'timeline'))
		{
			$width  = (int) $config->get('map_width', 800);
			$height = (int) $config->get('map_height', 600);

			if ($this->deviceType == 'mobile')
			{
				EventbookingHelperJquery::colorbox('eb-colorbox-map', '100%', $height . 'px', 'true', 'false');
			}
			else
			{
				EventbookingHelperJquery::colorbox('eb-colorbox-map', $width . 'px', $height . 'px', 'true', 'false');
			}
		}

		$this->viewLevels      = $user->getAuthorisedViewLevels();
		$this->userId          = $user->get('id');
		$this->items           = $items;
		$this->config          = $config;
		$this->nullDate        = JFactory::getDbo()->getNullDate();
		$this->category        = $category;
		$this->pagination      = $model->getPagination();
		$this->bootstrapHelper = new EventbookingHelperBootstrap($config->twitter_bootstrap_version);
		$this->params          = $params;

		parent::display();
	}
}
