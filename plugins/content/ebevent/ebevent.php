<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
jimport('joomla.plugin.plugin');

class plgContentEBEvent extends JPlugin
{

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param object $params  The object that holds the plugin parameters
	 *
	 * @since 1.5
	 */
	function plgContentEBEvent(&$subject, $params)
	{
		parent::__construct($subject, $params);
	}

	/**
	 * Method is called by the view
	 *
	 * @param    object         The article object.  Note $article->text is also available
	 * @param    object         The article params
	 * @param    int            The 'page' number
	 */
	function onContentPrepare($context, &$article, &$params, $limitstart)
	{
		error_reporting(0);
		$app = JFactory::getApplication();
		if ($app->getName() != 'site')
		{
			return true;
		}
		if (strpos($article->text, 'ebevent') === false)
		{
			return true;
		}
		$regex         = "#{ebevent (\d+)}#s";
		$article->text = preg_replace_callback($regex, array(&$this, '_replaceEBEvent'), $article->text);

		return true;
	}

	/**
	 * Replace the text with the event detail
	 *
	 * @param array $matches
	 */
	function _replaceEBEvent(&$matches)
	{
		$app    = JFactory::getApplication();
		$Itemid = JRequest::getInt('Itemid');
		require_once(JPATH_ROOT . '/components/com_eventbooking/helper/helper.php');
		require_once(JPATH_ROOT . '/components/com_eventbooking/helper/route.php');
		require_once(JPATH_ROOT . '/components/com_eventbooking/payments/os_payment.php');
		require_once(JPATH_ROOT . '/components/com_eventbooking/payments/os_payments.php');
		EventBookingHelper::loadLanguage();
		$config                      = EventBookingHelper::getConfig();
		$viewConfig['name']          = 'form';
		$viewConfig['base_path']     = JPATH_ROOT . '/plugins/content/ebevent/ebevent';
		$viewConfig['template_path'] = JPATH_ROOT . '/plugins/content/ebevent/ebevent';
		$viewConfig['layout']        = 'default';
		$view                        = new JViewLegacy($viewConfig);
		$document                    = JFactory::getDocument();
		$document->addStyleSheet(JURI::base(true) . '/components/com_eventbooking/assets/css/style.css');
		if ($config->calendar_theme)
		{
			$theme = $config->calendar_theme;
		}
		else
		{
			$theme = 'default';
		}
		$styleUrl = JUri::base(true) . '/components/com_eventbooking/assets/css/themes/' . $theme . '.css';
		$document->addStylesheet($styleUrl);
		if ($config->load_jquery !== '0')
		{
			EventbookingHelper::loadJQuery();
		}
		if ($config->load_bootstrap_css_in_frontend !== '0')
		{
			EventbookingHelper::loadBootstrap();
		}
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$id = $matches[1];
		if ($id)
		{
			EventBookingHelper::checkEventAccess($id);
		}

		// Get event information
		$currentDate = JHtml::_('date', 'Now', 'Y-m-d');
		$query->select('a.*')
			->select('DATEDIFF(event_date, "'.$currentDate.'") AS number_event_dates')
			->select('DATEDIFF(cut_off_date, "'.$currentDate.'") AS number_cut_off_dates')
			->select('DATEDIFF(early_bird_discount_date, "'.$currentDate.'") AS date_diff')
			->select('IFNULL(SUM(b.number_registrants), 0) AS total_registrants')
			->from('#__eb_events AS a')
			->leftJoin('#__eb_registrants AS b ON (a.id = b.event_id AND b.group_id=0 AND (b.published = 1 OR (b.payment_method LIKE "os_offline%" AND b.published NOT IN (2,3))))')
			->where('a.id = '. $id)
			->where('a.published = 1');
		$db->setQuery($query);
		$item = $db->loadObject();
		if (!$item)
		{
			$app->redirect('index.php?option=com_eventbooking&Itemid=' . $Itemid, JText::_('EB_INVALID_EVENT'));
		}
		if (strlen(trim(strip_tags($item->description))) == 0)
		{
			$item->description = $item->short_description;
		}

		if ($config->process_plugin)
		{
			$item->description = JHtml::_('content.prepare', $item->description);
		}
		$user   = JFactory::getUser();
		$userId = $user->get('id', 0);
		if ($item->location_id)
		{
			$query->clear();
			$query->select('*')
				->from('#__eb_locations')
				->where('id = '. (int) $item->location_id);
			$db->setQuery($query);
			$location = $db->loadObject();
			$view->location = $location;
		}
		$nullDate = $db->getNullDate();
		$query->clear();
		$query->select('*')
			->from('#__eb_event_group_prices')
			->where('event_id = '. $item->id)
			->order('id');
		$db->setQuery($query);
		$rowGroupRates = $db->loadObjectList();

		if ($config->event_custom_field && file_exists(JPATH_ROOT . '/components/com_eventbooking/fields.xml'))
		{
			$params = new JRegistry();
			$params->loadString($item->custom_fields, 'JSON');
			$xml          = JFactory::getXML(JPATH_ROOT . '/components/com_eventbooking/fields.xml');
			$fields       = $xml->fields->fieldset->children();
			$customFields = array();
			foreach ($fields as $field)
			{
				$name                  = $field->attributes()->name;
				$label                 = JText::_($field->attributes()->label);
				$customFields["$name"] = $label;
			}
			$paramData = array();
			foreach ($customFields as $name => $label)
			{
				$paramData[$name]['title'] = $label;
				$paramData[$name]['value'] = $params->get($name);
			}
			$view->paramData = $paramData;
		}
		$view->item          = $item;
		$view->Itemid        = $Itemid;
		$view->config        = $config;
		$view->userId        = $userId;
		$view->nullDate      = $nullDate;
		$view->rowGroupRates = $rowGroupRates;
		$view->showTaskBar   = 1;
		ob_start();
		$view->display();
		$text = ob_get_contents();
		ob_end_clean();

		return $text;
	}
}
