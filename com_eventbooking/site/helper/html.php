<?php

/**
 * @version            1.7.3
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
abstract class EventbookingHelperHtml
{

	public static function getCalendarSetupJs($fields)
	{
		$firstDay = JFactory::getLanguage()->getFirstDay();

		$output = array();
		foreach ($fields as $field)
		{
			$output[] = 'Calendar.setup({
			// Id of the input field
			inputField: "' . $field . '",
			// Format of the input field
			ifFormat: "%Y-%m-%d",
			// Trigger for the calendar (button ID)
			button: "' . $field . '_img",
			// Alignment (defaults to "Bl")
			align: "Tl",
			singleClick: true,
			firstDay: ' . $firstDay . '
			});';
		}

		return implode("\n", $output);
	}

	/**
	 * Load chosen library, used in several view in back-end
	 */
	public static function chosen()
	{
		static $chosenLoaded;
		if (!$chosenLoaded)
		{
			if (version_compare(JVERSION, '3.0', 'ge'))
			{
				JHtml::_('formbehavior.chosen', 'select');
			}
			else
			{
				$document = JFactory::getDocument();
				$document->addScript(JURI::base() . 'components/com_eventbooking/assets/chosen/chosen.jquery.js');
				$document->addStyleSheet(JURI::base() . 'components/com_eventbooking/assets/chosen/chosen.css');
				$document->addScriptDeclaration(
					"jQuery(document).ready(function(){
                            jQuery(\"select\").chosen();
                        });");
			}
			$chosenLoaded = true;
		}
	}

	/**
	 * Build category dropdown
	 *
	 * @param int    $selected
	 * @param string $name
	 * @param string $attr Extra attributes need to be passed to the dropdown
	 *
	 * @return string
	 */
	public static function buildCategoryDropdown($selected, $name = "parent", $attr = null)
	{
		$db          = JFactory::getDbo();
		$query       = $db->getQuery(true);
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		$query->select('id, parent, parent AS parent_id, name' . $fieldSuffix . ' AS name, name' . $fieldSuffix . ' AS title')
			->from('#__eb_categories')
			->where('published=1');
		$db->setQuery($query);
		$rows     = $db->loadObjectList();
		$children = array();
		if ($rows)
		{
			// first pass - collect children
			foreach ($rows as $v)
			{
				$pt   = $v->parent;
				$list = @$children[$pt] ? $children[$pt] : array();
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}
		$list      = JHtml::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0);
		$options   = array();
		$options[] = JHtml::_('select.option', '0', JText::_('Top'));
		foreach ($list as $item)
		{
			$options[] = JHtml::_('select.option', $item->id, '&nbsp;&nbsp;&nbsp;' . $item->treename);
		}

		return JHtml::_('select.genericlist', $options, $name,
			array(
				'option.text.toHtml' => false,
				'option.text'        => 'text',
				'option.value'       => 'value',
				'list.attr'          => 'class="inputbox" ' . $attr,
				'list.select'        => $selected));
	}

	/**
	 * Function to render a common layout which is used in different views
	 *
	 * @param string $layout
	 * @param array  $data
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function loadCommonLayout($layout, $data = array())
	{
		jimport('joomla.filesystem.file');
		$app       = JFactory::getApplication();
		$themeFile = str_replace('/tmpl', '', $layout);
		if (JFile::exists($layout))
		{
			$path = $layout;
		}
		elseif (JFile::exists(JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_eventbooking/' . $themeFile))
		{
			$path = JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_eventbooking/' . $themeFile;
		}
		elseif (JFile::exists(JPATH_ROOT . '/components/com_eventbooking/views/' . $layout))
		{
			$path = JPATH_ROOT . '/components/com_eventbooking/views/' . $layout;
		}
		else
		{
			throw new RuntimeException(JText::_('The given shared template path is not exist'));
		}

		// Start an output buffer.
		ob_start();
		extract($data);

		// Load the layout.
		include $path;

		// Get the layout contents.
		$output = ob_get_clean();

		return $output;
	}

	/**
	 * Get URL to add the given event to Google Calendar
	 *
	 * @param $row
	 *
	 * @return string
	 */
	public static function getAddToGoogleCalendarUrl($row)
	{
		$eventData = self::getEventDataArray($row);

		$queryString['title']       = "text=" . $eventData['title'];
		$queryString['dates']       = "dates=" . $eventData['dates'];
		$queryString['location']    = "location=" . $eventData['location'];
		$queryString['trp']         = "trp=false";
		$queryString['websiteName'] = "sprop=" . $eventData['sitename'];
		$queryString['websiteURL']  = "sprop=name:" . $eventData['siteurl'];
		$queryString['details']     = "details=" . $eventData['details'];

		return "http://www.google.com/calendar/event?action=TEMPLATE&" . implode("&", $queryString);
	}

	/**
	 * Get URL to add the given event to Yahoo Calendar
	 *
	 * @param $row
	 *
	 * @return string
	 */
	public static function getAddToYahooCalendarUrl($row)
	{
		$eventData = self::getEventDataArray($row);

		$urlString['title']      = "title=" . $eventData['title'];
		$urlString['st']         = "st=" . $eventData['st'];
		$urlString['et']         = "et=" . $eventData['et'];
		$urlString['rawdetails'] = "desc=" . $eventData['details'];
		$urlString['location']   = "in_loc=" . $eventData['location'];

		return "http://calendar.yahoo.com/?v=60&view=d&type=20&" . implode("&", $urlString);
	}

	/**
	 * Get event data
	 *
	 * @param $row
	 *
	 * @return mixed
	 */
	public static function getEventDataArray($row)
	{
		$db           = JFactory::getDbo();
		$query        = $db->getQuery(true);
		$config       = JFactory::getConfig();
		$dateFormat   = "Ymd\THis\Z";
		$eventDate    =  JFactory::getDate($row->event_date, new DateTimeZone($config->get('offset')));
		$eventEndDate = JFactory::getDate($row->event_end_date, new DateTimeZone($config->get('offset')));

		$data['title']    = urlencode($row->title);
		$data['dates']    = $eventDate->format($dateFormat) . "/" . $eventEndDate->format($dateFormat);
		$data['st']       = $eventDate->format($dateFormat);
		$data['et']       = $eventEndDate->format($dateFormat);
		$data['duration'] = abs(strtotime($row->event_end_date) - strtotime($row->event_date));

		// Get location data
		$query->select('a.*')
			->from('#__eb_locations AS a')
			->innerJoin('#__eb_events AS b ON a.id=b.location_id')
			->where('b.id=' . $row->id);

		$db->setQuery($query);
		$rowLocation = $db->loadObject();
		if ($rowLocation)
		{
			$locationInformation   = array();
			$locationInformation[] = $rowLocation->name;
			if ($rowLocation->address)
			{
				$locationInformation[] = $rowLocation->address;
			}
			if ($rowLocation->city)
			{
				$locationInformation[] = $rowLocation->city;
			}
			if ($rowLocation->state)
			{
				$locationInformation[] = $rowLocation->state;
			}
			if ($rowLocation->zip)
			{
				$locationInformation[] = $rowLocation->zip;
			}
			if ($rowLocation->country)
			{
				$locationInformation[] = $rowLocation->country;
			}
			$data['location'] = implode(', ', $locationInformation);
		}
		else
		{
			$data['location'] = '';
		}

		$data['sitename']   = urlencode($config->get('sitename'));
		$data['siteurl']    = urlencode(JUri::root());
		$data['rawdetails'] = urlencode($row->description);
		$data['details']    = strip_tags($row->description);
		if (strlen($data['details']) > 100)
		{
			$data['details'] = JString::substr($data['details'], 0, 100) . ' ...';
		}

		$data['details'] = urlencode($data['details']);

		return $data;
	}

	/**
	 * Function to add dropdown menu
	 *
	 * @param string $vName
	 */
	public static function renderSubmenu($vName = 'dashboard')
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__eb_menus')
			->where('published = 1')
			->where('menu_parent_id = 0')
			->order('ordering');
		$db->setQuery($query);
		$menus = $db->loadObjectList();
		$html  = '';
		$html .= '<ul class="nav nav-tabs">';
		for ($i = 0; $n = count($menus), $i < $n; $i++)
		{
			$menu = $menus[$i];
			$query->clear();
			$query->select('*')
				->from('#__eb_menus')
				->where('published = 1')
				->where('menu_parent_id = ' . intval($menu->id))
				->order('ordering');
			$db->setQuery($query);
			$subMenus = $db->loadObjectList();
			if (!count($subMenus))
			{
				$class = '';
				if ($menu->menu_view == $vName)
				{
					$class = ' class="active"';
				}
				$html .= '<li' . $class . '><a href="index.php?option=com_eventbooking&view=' . $menu->menu_view . '"><span class="icon-' . $menu->menu_class . '"></span> ' . JText::_($menu->menu_name) .
					'</a></li>';
			}
			else
			{
				$class = ' class="dropdown"';
				for ($j = 0; $m = count($subMenus), $j < $m; $j++)
				{
					$subMenu = $subMenus[$j];
					$lName   = JRequest::getVar('layout');
					if ((!$subMenu->menu_layout && $vName == $subMenu->menu_view) || ($lName != '' && $lName == $subMenu->menu_layout))
					{
						$class = ' class="dropdown active"';
						break;
					}
				}
				$html .= '<li' . $class . '>';
				$html .= '<a id="drop_' . $menu->id . '" href="#" data-toggle="dropdown" role="button" class="dropdown-toggle"><span class="icon-' . $menu->menu_class . '"></span> ' .
					JText::_($menu->menu_name) . ' <b class="caret"></b></a>';
				$html .= '<ul aria-labelledby="drop_' . $menu->id . '" role="menu" class="dropdown-menu" id="menu_' . $menu->id . '">';
				for ($j = 0; $m = count($subMenus), $j < $m; $j++)
				{
					$subMenu    = $subMenus[$j];
					$layoutLink = '';
					if ($subMenu->menu_layout)
					{
						$layoutLink = '&layout=' . $subMenu->menu_layout;
					}
					$class = '';
					$lName = JRequest::getVar('layout');
					if ((!$subMenu->menu_layout && $vName == $subMenu->menu_view) || ($lName != '' && $lName == $subMenu->menu_layout))
					{
						$class = ' class="active"';
					}
					$html .= '<li' . $class . '><a href="index.php?option=com_eventbooking&view=' . $subMenu->menu_view . $layoutLink .
						'" tabindex="-1"><span class="icon-' . $subMenu->menu_class . '"></span> ' . JText::_($subMenu->menu_name) . '</a></li>';
				}
				$html .= '</ul>';
				$html .= '</li>';
			}
		}
		$html .= '</ul>';
		echo $html;
	}
}