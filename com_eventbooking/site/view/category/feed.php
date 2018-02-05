<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2018 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

class EventbookingViewCategoryFeed extends RADView
{
	public function display()
	{
		/* @var JDocumentFeed $document */
		$document = JFactory::getDocument();
		$config   = JFactory::getConfig();

		/* @var EventbookingModelCategory $model */
		$model = $this->getModel();
		$model->setState('limitstart', 0)
			->setState('limit', $config->get('feed_limit'));
		$rows = $model->getData();
		$timezone = $config->get('offset');

		$rootUri = JUri::root();

		foreach ($rows as $row)
		{
			$title = html_entity_decode($row->title, ENT_COMPAT, 'UTF-8');
			$link  = JRoute::_(EventbookingHelperRoute::getEventRoute($row->id, $row->category_id, $this->Itemid));

			$date = JFactory::getDate($row->event_date, $timezone);

			if ($row->image && file_exists(JPATH_ROOT . '/' . $row->image))
			{
				$description = '<p><img src="' . $rootUri . '/' . $row->image . '" /></p>';
				$description .= $row->short_description;
			}
			else
			{
				$description = $row->short_description;
			}

			// load individual item creator class
			$item              = new JFeedItem();
			$item->title       = $title;
			$item->link        = $link;
			$item->description = $description;
			$item->category    = $row->category_name;
			$item->date        = $date->format('r');

			$document->addItem($item);
		}
	}
}