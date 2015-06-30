<?php
/**
 * @version        	1.7.4
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventBookingViewUpcomingEvents extends JViewLegacy
{

	function display($tpl = null)
	{
		$app      = JFactory::getApplication();
		$document = JFactory::getDocument();
		$active   = $app->getMenu()->getActive();

		$db = JFactory::getDbo();
		$model = $this->getModel();
		$state = $model->getState();
		$items = $model->getData();
		$category = $model->getCategory();
		if ($state->id)
		{
			EventbookingHelper::checkCategoryAccess($state->id);
		}

		$config = EventbookingHelper::getConfig();
		if ($config->process_plugin)
		{
			for ($i = 0, $n = count($items); $i < $n; $i++)
			{
				$item = $items[$i];
				$item->short_description = JHtml::_('content.prepare', $item->short_description);
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
				$params->loadString($item->custom_fields, 'JSON');
				$paramData = array();
				foreach ($customFields as $name => $label)
				{
					$paramData[$name]['title'] = $label;
					$paramData[$name]['value'] = $params->get($name);
				}
				
				$item->paramData = $paramData;
			}
		}

		$params   = EventbookingHelper::getViewParams($active, array('upcomingevents'));
		if ($params->get('page_title'))
		{
			$pageTitle = $params->get('page_title');
		}
		else
		{
			$pageTitle = JText::_('EB_UPCOMING_EVENTS_PAGE_TITLE');
			if ($category)
			{
				$pageTitle = str_replace('[CATEGORY_NAME]', $category->name, $pageTitle);
			}
		}

		$siteNamePosition = JFactory::getConfig()->get('sitename_pagetitles');
		if ($siteNamePosition == 0)
		{
			$document->setTitle($pageTitle);
		}
		elseif ($siteNamePosition == 1)
		{
			$document->setTitle(JFactory::getConfig()->get('sitename') . ' - ' . $pageTitle);
		}
		else
		{
			$document->setTitle($pageTitle . ' - ' . JFactory::getConfig()->get('sitename'));
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

		if ($config->multiple_booking)
		{
			EventbookingHelperJquery::colorbox('eb-colorbox-addcart', '800px', '450px', 'false', 'false');
		}
		if ($config->show_list_of_registrants)
		{
			EventbookingHelperJquery::colorbox('eb-colorbox-register-lists');
		}
		if ($config->show_location_in_category_view || ($this->getLayout() == 'timeline'))
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
		$user = JFactory::getUser();
		$userId = $user->get('id');
		$viewLevels = $user->getAuthorisedViewLevels();
		$this->viewLevels = $viewLevels;
		$this->userId = $userId;
		$this->items = $items;
		$this->Itemid = JRequest::getInt('Itemid', 0);
		$this->config = $config;
		$this->nullDate = $db->getNullDate();
		$this->category = $category;
		$this->pagination = $model->getPagination();
		$this->bootstrapHelper = new EventbookingHelperBootstrap($config->twitter_bootstrap_version);
		$this->params = $params;
		
		parent::display($tpl);
	}
}
