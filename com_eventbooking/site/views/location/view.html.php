<?php
/**
 * @version            1.7.2
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

class EventBookingViewLocation extends JViewLegacy
{

	function display($tpl = null)
	{
		$this->setLayout('default');
		$db       = JFactory::getDbo();
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
			$params       = new JRegistry();
			$xml          = JFactory::getXML(JPATH_COMPONENT . '/fields.xml');
			$fields       = $xml->fields->fieldset->children();
			$customFields = array();
			foreach ($fields as $field)
			{
				$name                  = $field->attributes()->name;
				$label                 = JText::_($field->attributes()->label);
				$customFields["$name"] = $label;
			}
			for ($i = 0, $n = count($items); $i < $n; $i++)
			{
				$item = $items[$i];
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
		$user             = JFactory::getUser();
		$userId           = $user->get('id');
		$viewLevels       = $user->getAuthorisedViewLevels();
		$this->viewLevels = $viewLevels;
		$this->userId     = $userId;
		$this->items      = $items;
		$this->pagination = $model->getPagination();
		$this->Itemid     = JRequest::getInt('Itemid', 0);
		$this->config     = $config;
		$this->location   = $location;
		$this->nullDate   = $db->getNullDate();
		$this->bootstrapHelper = new EventbookingHelperBootstrap($config->twitter_bootstrap_version);

		parent::display($tpl);
	}
}
