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

class EventbookingViewLocationHtml extends RADViewHtml
{

	public function display()
	{
		$layout = $this->getLayout();
		if ($layout == 'form')
		{
			$this->displayForm();

			return;
		}

		$document = JFactory::getDocument();
		$model    = $this->getModel();
		$items    = $model->getData();
		$location = $model->getLocation();
		$document->setTitle($location->name);
		$config = EventbookingHelper::getConfig();
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

		$user                  = JFactory::getUser();
		$userId                = $user->get('id');
		$viewLevels            = $user->getAuthorisedViewLevels();
		$this->viewLevels      = $viewLevels;
		$this->userId          = $userId;
		$this->items           = $items;
		$this->pagination      = $model->getPagination();
		$this->config          = $config;
		$this->location        = $location;
		$this->nullDate        = JFactory::getDbo()->getNullDate();
		$this->bootstrapHelper = new EventbookingHelperBootstrap($config->twitter_bootstrap_version);

		parent::display();
	}

	protected function displayForm()
	{
		$user = JFactory::getUser();
		if (!$user->authorise('eventbooking.addlocation', 'com_eventbooking'))
		{
			JFactory::getApplication()->redirect('index.php', JText::_("EB_NO_PERMISSION"));

			return;
		}

		$item               = $this->model->getLocationData();
		$options            = array();
		$options[]          = JHtml::_('select.option', '', JText::_('Select Country'), 'id', 'name');
		$options            = array_merge($options, EventbookingHelperDatabase::getAllCountries());
		$lists['country']   = JHtml::_('select.genericlist', $options, 'country', ' class="inputbox" ', 'id', 'name', $item->country);
		$lists['published'] = JHtml::_('select.booleanlist', 'published', '', $item->published);
		$this->item         = $item;
		$this->lists        = $lists;
		$this->config       = EventbookingHelper::getConfig();

		parent::display();
	}
}
