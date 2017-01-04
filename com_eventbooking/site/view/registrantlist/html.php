<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2017 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

/**
 * @property EventbookingModelRegistrantlist $model
 */
class EventbookingViewRegistrantlistHtml extends RADViewHtml
{
	public function display()
	{
		if (!EventbookingHelperAcl::canViewRegistrantList())
		{
			return;
		}

		$state   = $this->model->getState();
		$eventId = $state->id;
		if ($eventId)
		{
			$rows = $this->model->getData();

			$config  = EventbookingHelper::getConfig();
			$db      = JFactory::getDbo();
			$query   = $db->getQuery(true);

			// Check to see whether we need to display custom fields data for this event
			$query->select('custom_field_ids')
				->from('#__eb_events')
				->where('id = ' . $eventId);
			$db->setQuery($query);
			$customFieldIds = $db->loadResult();
			$customFieldIds = trim($customFieldIds);
			if (!$customFieldIds)
			{
				$customFieldIds = trim($config->registrant_list_custom_field_ids);
			}
			if ($customFieldIds)
			{
				$fields      = explode(',', $customFieldIds);
				$fieldTitles = array();
				$fieldSuffix = EventbookingHelper::getFieldSuffix();
				$query->clear();
				$query->select('id, name,title' . $fieldSuffix . ' AS title, is_core')
					->from('#__eb_fields')
					->where('id IN (' . $customFieldIds . ')');
				$db->setQuery($query);	
				$rowFields = $db->loadObjectList();
				foreach ($rowFields as $rowField)
				{
					$fieldTitles[$rowField->id] = $rowField->title;
				}

				$this->fieldTitles  = $fieldTitles;
				$this->fieldValues  = $this->model->getFieldsData($fields);
				$this->fields       = $fields;
				$displayCustomField = true;
			}
			else
			{
				$displayCustomField = false;
			}
			$this->items              = $rows;
			$this->pagination         = $this->model->getPagination();
			$this->config             = $config;
			$this->displayCustomField = $displayCustomField;
			$this->bootstrapHelper    = new EventbookingHelperBootstrap($config->twitter_bootstrap_version);
			$this->coreFields         = EventbookingHelper::getPublishedCoreFields();

			parent::display();
		}
	}
}
