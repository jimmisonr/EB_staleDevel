<?php
/**
 * @version            1.7.4
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;
error_reporting(0);

class plgContentEbCategory extends JPlugin
{

	function onContentPrepare($context, &$article, &$params, $limitstart)
	{
		if (file_exists(JPATH_ROOT . '/components/com_eventbooking/eventbooking.php'))
		{
			$app = JFactory::getApplication();
			if ($app->getName() != 'site')
			{
				return;
			}
			if (strpos($article->text, 'ebcategory') === false)
			{
				return true;
			}
			$regex         = "#{ebcategory (\d+)}#s";
			$article->text = preg_replace_callback($regex, array(&$this, 'displayEvents'), $article->text);
		}

		return true;
	}

	/**
	 * Replace callback function
	 *
	 * @param array $matches
	 */
	function displayEvents($matches)
	{
		require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';
		$document = JFactory::getDocument();
		$config   = EventbookingHelper::getConfig();
		EventbookingHelper::loadLanguage();
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
		JHtml::_('script', EventbookingHelper::getSiteUrl() . 'components/com_eventbooking/assets/js/noconflict.js', false, false);
		if ($config->multiple_booking)
		{
			EventbookingHelperJquery::colorbox('eb-colorbox-addcart', '800px', '450px', 'false', 'false');
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
		$Itemid = JRequest::getInt('Itemid');
		if (!$Itemid)
		{
			$Itemid = EventbookingHelper::getItemid();
		}
		$categoryId = (int) $matches[1];

		$bootstrapHelper = new EventbookingHelperBootstrap($config->twitter_bootstrap_version);
		//required eb category model
		require_once JPATH_ROOT . '/components/com_eventbooking/models/category.php';
		$categoryModel = new EventBookingModelCategory();
		$items         = $categoryModel->reset()->id($categoryId)->getData();

		return '<div class="clearfix"></div>' . EventbookingHelperHtml::loadCommonLayout('common/events_table.php', array('items' => $items, 'config' => $config, 'Itemid' => $Itemid, 'categoryId' => $categoryId, 'bootstrapHelper' => $bootstrapHelper));
	}
}