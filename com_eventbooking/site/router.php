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
JLoader::registerPrefix('Eventbooking', JPATH_ROOT . '/components/com_eventbooking');
JLoader::register('RADConfig', JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/config/config.php');

function EventbookingBuildRoute(&$query)
{
	$segments = array();
	$db       = JFactory::getDbo();
	$q        = $db->getQuery(true);
	$queryArr = $query;
	if (isset($queryArr['option']))
	{
		unset($queryArr['option']);
	}
	if (isset($queryArr['Itemid']))
	{
		unset($queryArr['Itemid']);
	}
	//Store the query string to use in the parseRouter method
	$queryString = http_build_query($queryArr);
	$app         = JFactory::getApplication();
	$menu        = $app->getMenu();
	//We need a menu item.  Either the one specified in the query, or the current active one if none specified
	if (empty($query['Itemid']))
	{
		$menuItem = $menu->getActive();
	}
	else
	{
		$menuItem = $menu->getItem($query['Itemid']);
	}
	if (empty($menuItem->query['view']))
	{
		$menuItem->query['view'] = '';
	}
	//Are we dealing with the current view which is attached to a menu item?
	if (($menuItem instanceof stdClass) && isset($query['view']) && isset($query['id']) && $menuItem->query['view'] == $query['view'] &&
		isset($query['id']) && $menuItem->query['id'] == intval($query['id'])
	)
	{
		unset($query['view']);
		if (isset($query['catid']))
		{
			unset($query['catid']);
		}
		unset($query['id']);
	}

	if (($menuItem instanceof stdClass) && isset($query['view']) && ($menuItem->query['view'] == 'calendar') &&
		$menuItem->query['view'] == $query['view']
	)
	{
		unset($query['view']);
	}

	if (($menuItem instanceof stdClass) && isset($query['view']) && ($menuItem->query['view'] == 'events') &&
		$menuItem->query['view'] == $query['view']
	)
	{
		unset($query['view']);
	}

	//Dealing with the catid parameter in the link to event.
	if (($menuItem instanceof stdClass) && ($menuItem->query['view'] == 'category') && isset($query['catid']) &&
		$menuItem->query['id'] == intval($query['catid'])
	)
	{
		if (isset($query['catid']))
		{
			unset($query['catid']);
		}
	}
	$view    = isset($query['view']) ? $query['view'] : '';
	$id      = isset($query['id']) ? (int) $query['id'] : 0;
	$catId   = isset($query['catid']) ? (int) $query['catid'] : 0;
	$eventId = isset($query['event_id']) ? (int) $query['event_id'] : 0;
	$task    = isset($query['task']) ? $query['task'] : '';
	$layout    = isset($query['layout']) ? $query['layout'] : '';
	switch ($view)
	{
		case 'calendar':
			$segments[] = JText::_('EB_CALENDAR');
			break;
		case 'categories':
		case 'category':
			if ($id)
			{
				$segments = array_merge($segments, EventbookingHelperRoute::getCategoriesPath($id, 'alias'));
			}
			unset($query['id']);
			break;
		case 'event':
			if ($id)
			{
				$segments[] = EventbookingHelperRoute::getEventTitle($id);
			}
			if ($layout == 'form')
			{
				$segments[] = 'Edit';
			}
			else
			{
				if ($catId)
				{
					$segments = array_merge(EventbookingHelperRoute::getCategoriesPath($catId, 'alias'), $segments);
				}
			}
			unset($query['id']);
			break;
		case 'location':
		case 'map':
			if (isset($query['location_id']))
			{
				$q->clear();
				$q->select('name')
					->from('#__eb_locations')
					->where('id=' . (int) $query['location_id']);
				$db->setQuery($q);
				$segments[] = $db->loadResult();
				unset($query['location_id']);
			}
			$segments[] = JText::_('EB_SEF_VIEW_' . strtoupper($view));
			break;
		case 'cart':
			$segments[] = JText::_('EB_SEF_VIEW_CART');
			break;
		case 'invite':
			if ($id)
			{
				$segments[] = EventbookingHelperRoute::getEventTitle($id);
			}
			$segments[] = JText::_('EB_SEF_INVITE_FRIEND');
			unset($query['id']);
			break;
		case 'password':
			if ($eventId)
			{
				$segments[] = EventbookingHelperRoute::getEventTitle($eventId);
			}
			$segments[] = 'password validation';
			unset($query['id']);
			break;
		case 'registrantlist':
			if ($id)
			{
				$segments[] = EventbookingHelperRoute::getEventTitle($id);
			}
			$segments[] = JText::_('EB_SEF_REGISTRANTS_LIST');
			unset($query['id']);
			break;
		case 'waitinglist':
			$segments[] = JText::_('EB_SEF_WAITINGLIST_COMPLETE');
			break;
		case 'failure':
			$segments[] = JText::_('EB_SEF_REGISTRATION_FAILURE');
			break;
		case 'cancel':
			$segments[] = JText::_('EB_SEF_REGISTRATION_CANCEL');
			break;
		case 'complete':
			$segments[] = JText::_('EB_SEF_REGISTRATION_COMPLETE');
			break;
		case 'registrationcancel':
			$segments[] = JText::_('EB_SEF_REGISTRATION_CANCELLED');
			break;
		case 'search':
			$segments[] = 'search result';
			break;
		case 'events':
			$segments[] = 'my events';
			break;
	}

	switch ($task)
	{
		case 'register.individual_registration':
			if ($eventId)
			{
				$segments[] = EventbookingHelperRoute::getEventTitle($eventId);
			}
			$segments[] = 'Individual Registration';
			unset($query['task']);
			break;
		case 'register.group_registration':
			if ($eventId)
			{
				$segments[] = EventbookingHelperRoute::getEventTitle($eventId);
			}
			$segments[] = 'Group Registration';
			unset($query['task']);
			break;
		case 'group_billing':
			$segments[] = 'Group Billing';
			unset($query['task']);
			break;
		case 'download_ical':
			if ($eventId)
			{
				$segments[] = EventbookingHelperRoute::getEventTitle($eventId);
			}
			$segments[] = 'download_ical';
			unset($query['task']);
			break;
		case 'edit_registrant':
			$segments[] = 'Edit Registrant';
			unset($query['task']);
			break;
		case 'event.unpublish':
			if ($id)
			{
				$segments[] = EventbookingHelperRoute::getEventTitle($id);
			}
			$segments[] = 'Unpublish';
			unset($query['task']);
			break;

		case 'event.publish':
			if ($id)
			{
				$segments[] = EventbookingHelperRoute::getEventTitle($id);
			}
			$segments[] = 'Publish';
			unset($query['task']);
			break;
		case 'csv_export':
			if ($eventId)
			{
				$segments[] = EventbookingHelperRoute::getEventTitle($eventId);
			}
			$segments[] = JText::_('EB_SEF_CSV_EXPORT');
			unset($query['task']);
			break;
		case 'view_checkout':
			$segments[] = JText::_('EB_SEF_VIEW_CHECKOUT');
			unset($query['task']);
			break;
	}
	if (isset($query['view']))
	{
		unset($query['view']);
	}
	if (isset($query['event_id']))
	{
		unset($query['event_id']);
	}
	if (isset($query['category_id']))
	{
		unset($query['category_id']);
	}
	if (isset($query['catid']))
	{
		unset($query['catid']);
	}
	if (count($segments))
	{
		$segments = array_map('JApplication::stringURLSafe', $segments);
		$key      = md5(implode('/', $segments));
		$q        = $db->getQuery(true);
		$q->select('COUNT(*)')
			->from('#__eb_urls')
			->where('md5_key="' . $key . '"');
		$db->setQuery($q);
		$total = $db->loadResult();
		if (!$total)
		{
			$q->clear();
			$q->insert('#__eb_urls')
				->columns('md5_key, `query`')
				->values("'$key', '$queryString'");
			$db->setQuery($q);
			$db->execute();
		}
	}

	return $segments;
}

/**
 *
 * Parse the segments of a URL.
 *
 * @param    array    The segments of the URL to parse.
 *
 * @return    array    The URL attributes to be used by the application.
 */
function EventbookingParseRoute($segments)
{
	$vars = array();
	if (count($segments))
	{
		$db    = JFactory::getDbo();
		$key   = md5(str_replace(':', '-', implode('/', $segments)));
		$query = $db->getQuery(true);
		$query->select('`query`')
			->from('#__eb_urls')
			->where('md5_key="' . $key . '"');
		$db->setQuery($query);
		$queryString = $db->loadResult();
		if ($queryString)
		{
			parse_str(html_entity_decode($queryString), $vars);
		}
	}

	$app  = JFactory::getApplication();
	$menu = $app->getMenu();
	if ($item = $menu->getActive())
	{
		foreach ($item->query as $key => $value)
		{
			if ($key != 'option' && $key != 'Itemid' && !isset($vars[$key]))
			{
				$vars[$key] = $value;
			}
		}
	}

	if (isset($vars['tmpl']) && !isset($_GET['tmpl']))
	{
		unset($vars['tmpl']);
	}

	return $vars;
}