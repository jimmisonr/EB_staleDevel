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
class EventBookingViewEvent extends JViewLegacy
{

	function display($tpl = null)
	{
		$layout = $this->getLayout();
		if ($layout == 'form')
		{
			$this->_displayForm($tpl);
			return;
		}
		$app = JFactory::getApplication();
		$input = $app->input;
		$document = JFactory::getDocument();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$config = EventbookingHelper::getConfig();
		$Itemid = $input->getInt('Itemid', 0);
		$id = $input->getInt('id', 0);
		if ($id)
		{
			EventbookingHelper::checkEventAccess($id);
		}
		$query->select('COUNT(*)')
			->from('#__eb_events')
			->where('id=' . $id . ' AND published=1');
		$db->setQuery($query);
		$totalEvent = $db->loadResult();
		if (!$totalEvent)
		{
			$app->redirect('index.php?option=com_eventbooking&Itemid=' . $Itemid, JText::_('EB_INVALID_EVENT'));
		}
		$pathway = $app->getPathway();
		$item = $this->get('Data');
		//Use short description in case user don't enter long description
		if (strlen(trim(strip_tags($item->description))) == 0)
		{
			$item->description = $item->short_description;
		}
		
		$app = JFactory::getApplication();
		$menu = $app->getMenu();
		$menuItem = $menu->getActive();
		$categoryId = JRequest::getInt('catid', 0);
		if ($menuItem)
		{
			if (isset($menuItem->query['view']) && ($menuItem->query['view'] == 'categories' || $menuItem->query['view'] == 'category'))
			{
				$parentId = (int) $menuItem->query['id'];
				if ($categoryId)
				{
					$pathway = $app->getPathway();
					$paths = EventbookingHelperData::getCategoriesBreadcrumb($categoryId, $parentId);
					for ($i = count($paths) - 1; $i >= 0; $i--)
					{
						$category = $paths[$i];
						$pathUrl = EventbookingHelperRoute::getCategoryRoute($category->id, $Itemid);
						$pathway->addItem($category->name, $pathUrl);
					}
					$pathway->addItem($item->title);
				}
			}
			elseif (isset($menuItem->query['view']) && ($menuItem->query['view'] == 'calendar'))
			{
				$pathway->addItem($item->title);
			}
		}
		$tmpl = $input->get('tmpl', '');
		$item->description = JHtml::_('content.prepare', $item->description);
		if ($tmpl == 'component')
		{
			$showTaskBar = false;
		}
		else
		{
			$showTaskBar = true;
		}
		$user = JFactory::getUser();
		$userId = $user->get('id', 0);
		if ($item->location_id)
		{
			$query->clear();
			$query->select('*')
				->from('#__eb_locations')
				->where('id=' . $item->location_id);
			$db->setQuery($query);
			$this->location = $db->loadObject();
		}
		$query->clear();
		$query->select('name')
			->from('#__eb_categories')
			->where('id=' . $item->category_id);
		$db->setQuery($query);
		$categoryName = $db->loadResult();
		$pageTitle = JText::_('EB_EVENT_PAGE_TITLE');
		$pageTitle = str_replace('[EVENT_TITLE]', $item->title, $pageTitle);
		$pageTitle = str_replace('[CATEGORY_NAME]', $categoryName, $pageTitle);
		$document->setTitle($pageTitle);
		$nullDate = $db->getNullDate();
		$query->clear();
		$query->select('*')
			->from('#__eb_event_group_prices')
			->where('event_id=' . $item->id)
			->order('id');
		$db->setQuery($query);
		$rowGroupRates = $db->loadObjectList();
		if ($config->event_custom_field && file_exists(JPATH_COMPONENT . '/fields.xml'))
		{
			$params = new JRegistry();
			$params->loadString($item->custom_fields, 'JSON');
			$xml = JFactory::getXML(JPATH_COMPONENT . '/fields.xml');
			$fields = $xml->fields->fieldset->children();
			$customFields = array();
			foreach ($fields as $field)
			{
				$name = $field->attributes()->name;
				$label = JText::_($field->attributes()->label);
				$customFields["$name"] = $label;
			}
			$paramData = array();
			foreach ($customFields as $name => $label)
			{
				$paramData[$name]['title'] = $label;
				$paramData[$name]['value'] = $params->get($name);
			}
			$this->paramData = $paramData;
		}
		if ($item->meta_keywords)
		{
			$document->setMetaData('keywords', $item->meta_keywords);
		}
		if ($item->meta_description)
		{
			$document->setMetaData('description', $item->meta_description);
		}
		if ($config->multiple_booking)
		{
			EventbookingHelperJquery::colorbox('eb-colorbox-addcart', '800px', '450px', 'false', 'false');
		}
		if ($config->show_list_of_registrants)
		{
			EventbookingHelperJquery::colorbox('eb-colorbox-register-lists');
		}		
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
		if ($config->show_invite_friend)
		{
			EventbookingHelperJquery::colorbox('eb-colorbox-invite');
		}
		$viewLevels = $user->getAuthorisedViewLevels();
		JPluginHelper::importPlugin('eventbooking');
		$dispatcher = JDispatcher::getInstance();
		$plugins = $dispatcher->trigger('onEventDisplay', array($item));
		$this->viewLevels = $viewLevels;
		$this->item = $item;
		$this->Itemid = $Itemid;
		$this->config = $config;
		$this->showTaskBar = $showTaskBar;
		$this->userId = $userId;
		$this->nullDate = $nullDate;
		$this->rowGroupRates = $rowGroupRates;
		$this->plugins = $plugins;
		
		parent::display($tpl);
	}

	/**
	 * Display form which allows submitting events
	 * 
	 * @param string $tpl
	 */
	function _displayForm($tpl)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();
		$item = $this->get('Event');
		$config = EventbookingHelper::getConfig();
		if ($config->submit_event_form_layout == 'simple')
		{
			$this->setLayout('simple');
		}
		if ($item->id)
		{
			$ret = EventbookingHelper::checkEditEvent($item->id);
		}
		else
		{
			$ret = EventbookingHelper::checkAddEvent();
		}
		if (!$ret)
		{
			$app = JFactory::getApplication('site');
			//Redirect users to login page if they are not logged in
			$user = JFactory::getUser();
			if (!$user->id)
			{
				$currentUrl = JUri::current();
				$app->redirect('index.php?option=com_users&view=login&return=' . base64_encode($currentUrl));
			}
			else
			{
				$url = JRoute::_('index.php');
				$app->redirect($url, JText::_('EB_NO_ADDING_EVENT_PERMISSION'));
			}
		}
		$prices = $this->get('Prices');
		$config = EventbookingHelper::getConfig();
		//Reset some data for recurring event
		if ($item->recurring_type)
		{
			if ($item->number_days == 0)
				$item->number_days = '';
			if ($item->number_weeks == 0)
				$item->number_weeks = '';
			if ($item->number_months == 0)
				$item->number_months = '';
			if ($item->recurring_occurrencies == 0)
			{
				$item->recurring_occurrencies = '';
			}
		}
		$params = new JRegistry($item->params);
		//Get list of location
		$options = array();
		if ($user->authorise('core.admin', 'com_eventbooking'))
		{
			$sql = 'SELECT id, name FROM #__eb_locations  WHERE published=1 ORDER BY name';
		}
		else
		{
			$sql = 'SELECT id, name FROM #__eb_locations  WHERE published=1 AND user_id=' . $user->id . ' ORDER BY name ';
		}
		$db->setQuery($sql);
		$options[] = JHtml::_('select.option', 0, JText::_('Select Location'), 'id', 'name');
		$options = array_merge($options, $db->loadObjectList());
		$lists['location_id'] = JHtml::_('select.genericlist', $options, 'location_id', ' class="inputbox" ', 'id', 'name', $item->location_id);
		
		$sql = "SELECT id, parent, parent AS parent_id, name, name AS title FROM #__eb_categories";
		$db->setQuery($sql);
		$rows = $db->loadObjectList();
		$children = array();
		if ($rows)
		{
			// first pass - collect children
			foreach ($rows as $v)
			{
				$pt = $v->parent;
				$list = @$children[$pt] ? $children[$pt] : array();
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}
		$list = JHtml::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
		$options = array();
		foreach ($list as $listItem)
		{
			$options[] = JHtml::_('select.option', $listItem->id, '&nbsp;&nbsp;&nbsp;' . $listItem->treename);
		}
		if ($item->id)
		{
			$query->clear();
			$query->select('category_id')
				->from('#__eb_event_categories')
				->where('event_id=' . $item->id)
				->where('main_category=1');
			$db->setQuery($query);
			$mainCategoryId = $db->loadResult();
			$query->clear();
			$query->select('category_id')
				->from('#__eb_event_categories')
				->where('event_id=' . $item->id)
				->where('main_category=0');
			$db->setQuery($query);
			$additionalCategories = $db->loadColumn();
		}
		else
		{
			$mainCategoryId = 0;
			$additionalCategories = array();
		}
		
		$lists['main_category_id'] = JHtml::_('select.genericlist', $options, 'main_category_id', 
			array(
				'option.text.toHtml' => false, 
				'option.text' => 'text', 
				'option.value' => 'value', 
				'list.attr' => '', 
				'list.select' => $mainCategoryId));
		$lists['category_id'] = JHtml::_('select.genericlist', $options, 'category_id[]', 
			array(
				'option.text.toHtml' => false, 
				'option.text' => 'text', 
				'option.value' => 'value', 
				'list.attr' => 'class="inputbox"  size="5" multiple="multiple"', 
				'list.select' => $additionalCategories));
		$options = array();
		$options[] = JHtml::_('select.option', 1, JText::_('%'));
		$options[] = JHtml::_('select.option', 2, $config->currency_symbol);
		$lists['discount_type'] = JHtml::_('select.genericlist', $options, 'discount_type', ' class="input-mini" ', 'value', 'text', 
			$item->discount_type);
		$lists['early_bird_discount_type'] = JHtml::_('select.genericlist', $options, 'early_bird_discount_type', ' class="input-mini" ', 'value', 
			'text', $item->early_bird_discount_type);
		$options = array();
		$options[] = JHtml::_('select.option', 0, JText::_('EB_INDIVIDUAL_GROUP'));
		$options[] = JHtml::_('select.option', 1, JText::_('EB_INDIVIDUAL_ONLY'));
		$options[] = JHtml::_('select.option', 2, JText::_('EB_GROUP_ONLY'));
		$options[] = JHtml::_('select.option', 3, JText::_('EB_DISABLE_REGISTRATION'));
		$lists['registration_type'] = JHtml::_('select.genericlist', $options, 'registration_type', ' class="inputbox" ', 'value', 'text', 
			$item->registration_type);
		$lists['access'] = JHtml::_('access.level', 'access', $item->access, 'class="inputbox"', false);
		$lists['registration_access'] = JHtml::_('access.level', 'registration_access', $item->registration_access, 'class="inputbox"', false);
		$lists['enable_cancel_registration'] = JHtml::_('select.booleanlist', 'enable_cancel_registration', ' class="inputbox" ', 
			$item->enable_cancel_registration);
		$lists['enable_auto_reminder'] = JHtml::_('select.booleanlist', 'enable_auto_reminder', ' class="inputbox" ', $item->enable_auto_reminder);
		
		$lists['published'] = JHtml::_('select.booleanlist', 'published', ' class="inputbox" ', $item->published);
		if ($item->event_date != $db->getNullDate())
		{
			$selectedHour = date('G', strtotime($item->event_date));
			$selectedMinute = date('i', strtotime($item->event_date));
		}
		else
		{
			$selectedHour = 0;
			$selectedMinute = 0;
		}
		$lists['event_date_hour'] = JHtml::_('select.integerlist', 0, 23, 1, 'event_date_hour', ' class="input-mini" ', $selectedHour);
		$lists['event_date_minute'] = JHtml::_('select.integerlist', 0, 60, 5, 'event_date_minute', ' class="input-mini" ', $selectedMinute, '%02d');
		if ($item->event_end_date != $db->getNullDate())
		{
			$selectedHour = date('G', strtotime($item->event_end_date));
			$selectedMinute = date('i', strtotime($item->event_end_date));
		}
		else
		{
			$selectedHour = 0;
			$selectedMinute = 0;
		}
		$lists['event_end_date_hour'] = JHtml::_('select.integerlist', 0, 23, 1, 'event_end_date_hour', ' class="input-mini" ', $selectedHour);
		$lists['event_end_date_minute'] = JHtml::_('select.integerlist', 0, 60, 5, 'event_end_date_minute', ' class="input-mini" ', $selectedMinute, 
			'%02d');
		//Terms and condition article
		$sql = 'SELECT id, title FROM #__content';
		$db->setQuery($sql);
		$rows = $db->loadObjectList();
		$options = array();
		$options[] = JHtml::_('select.option', 0, JText::_('Select article'), 'id', 'title');
		$options = array_merge($options, $rows);
		$lists['article_id'] = JHtml::_('select.genericlist', $options, 'article_id', 'class="inputbox"', 'id', 'title', $item->article_id);
		$nullDate = $db->getNullDate();
		//Custom field handles
		if ($config->event_custom_field)
		{
			$registry = new JRegistry();
			$registry->loadString($item->custom_fields);
			$data = new stdClass();
			$data->params = $registry->toArray();
			$form = JForm::getInstance('pmform', JPATH_ROOT . '/components/com_eventbooking/fields.xml', array(), false, '//config');
			$form->bind($data);
			$this->assignRef('form', $form);
		}
		$Itemid = JRequest::getInt('Itemid', 0);
		$this->assignRef('item', $item);
		$this->assignRef('prices', $prices);
		$this->assignRef('lists', $lists);
		$this->assignRef('nullDate', $nullDate);
		$this->assignRef('config', $config);
		$this->assignRef('Itemid', $Itemid);
		parent::display($tpl);
	}
}