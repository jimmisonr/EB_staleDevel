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

class EventbookingViewEventHtml extends RADViewHtml
{

	public function display()
	{
		$layout = $this->getLayout();
		if ($layout == 'form')
		{
			$this->_displayForm();

			return;
		}
		$app    = JFactory::getApplication();
		$active = $app->getMenu()->getActive();

		$user   = JFactory::getUser();
		$config = EventbookingHelper::getConfig();
		$model  = $this->getModel();
		$state  = $model->getState();
		$item   = $model->getData();

		// Check to make sure the event is valid and user is allowed to access to it

		if (empty($item) || !in_array($item->access, $user->getAuthorisedViewLevels()))
		{
			$app->redirect('index.php', JText::_('EB_INVALID_EVENT'));
		}

		//Use short description in case user don't enter long description
		if (strlen(trim(strip_tags($item->description))) == 0)
		{
			$item->description = $item->short_description;
		}

		$categoryId = $state->catid;
		if ($active)
		{
			$pathway = $app->getPathway();
			if (isset($active->query['view']) && ($active->query['view'] == 'categories' || $active->query['view'] == 'category'))
			{
				$parentId = (int) $active->query['id'];
				if ($categoryId)
				{
					$paths = EventbookingHelperData::getCategoriesBreadcrumb($categoryId, $parentId);
					for ($i = count($paths) - 1; $i >= 0; $i--)
					{
						$category = $paths[$i];
						$pathUrl  = EventbookingHelperRoute::getCategoryRoute($category->id, $this->Itemid);
						$pathway->addItem($category->name, $pathUrl);
					}
					$pathway->addItem($item->title);
				}
			}
			elseif (isset($active->query['view']) && ($active->query['view'] == 'calendar'))
			{
				$pathway->addItem($item->title);
			}
		}

		$item->description = JHtml::_('content.prepare', $item->description);


		if ($item->location_id)
		{
			$this->location = EventbookingHelperDatabase::getLocation($item->location_id);
		}


		if ($config->event_custom_field && file_exists(JPATH_COMPONENT . '/fields.xml'))
		{
			EventbookingHelperData::prepareCustomFieldsData(array($item));
			$this->paramData = $item->paramData;
		}

		$params = EventbookingHelper::getViewParams($active, array('calendar', 'upcomingevents', 'categories', 'category', 'event'));

		// Process page meta data
		if (!$params->get('page_title'))
		{
			$pageTitle = JText::_('EB_EVENT_PAGE_TITLE');
			$pageTitle = str_replace('[EVENT_TITLE]', $item->title, $pageTitle);
			$category  = EventbookingHelperDatabase::getCategory($item->category_id);
			$pageTitle = str_replace('[CATEGORY_NAME]', $category->name, $pageTitle);
			$params->set('page_title', $pageTitle);
		}

		EventbookingHelperHtml::prepareDocument($params, $item);

		if ($config->multiple_booking)
		{
			EventbookingHelperJquery::colorbox('eb-colorbox-addcart', '800px', '450px', 'false', 'false');
		}
		if ($config->show_list_of_registrants)
		{
			EventbookingHelperJquery::colorbox('eb-colorbox-register-lists');
		}
		$width = (int) $config->map_width;
		if (!$width)
		{
			$width = 800;
		}
		$height = (int) $config->map_height;
		if (!$height)
		{
			$height = 600;
		}
		EventbookingHelperJquery::colorbox('eb-colorbox-map', $width . 'px', $height . 'px', 'true', 'false');
		if ($config->show_invite_friend)
		{
			EventbookingHelperJquery::colorbox('eb-colorbox-invite');
		}

		JPluginHelper::importPlugin('eventbooking');
		$dispatcher = JDispatcher::getInstance();
		$plugins    = $dispatcher->trigger('onEventDisplay', array($item));

		if ($this->input->get('tmpl', '') == 'component')
		{
			$this->showTaskBar = false;
		}
		else
		{
			$this->showTaskBar = true;
		}

		$this->viewLevels      = $user->getAuthorisedViewLevels();
		$this->item            = $item;
		$this->config          = $config;
		$this->userId          = $user->id;
		$this->nullDate        = JFactory::getDbo()->getNullDate();
		$this->rowGroupRates   = EventbookingHelperDatabase::getGroupRegistrationRates($item->id);
		$this->plugins         = $plugins;
		$this->bootstrapHelper = new EventbookingHelperBootstrap($config->twitter_bootstrap_version);

		parent::display();
	}

	public function _displayForm()
	{

	}
}