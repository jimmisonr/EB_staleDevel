<?php
/**
 * @version            2.1.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die;

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
	 * Display events from a category
	 *
	 * @param $matches
	 *
	 * @return string
	 * @throws Exception
	 */
	public function displayEvents($matches)
	{

		require_once JPATH_ADMINISTRATOR . '/components/com_eventbooking/libraries/rad/bootstrap.php';
		EventbookingHelper::loadLanguage();
		$categoryId = (int) $matches[1];
		$request    = array('option' => 'com_eventbooking', 'view' => 'category', 'id' => $categoryId, 'limit' => 0, 'hmvc_call' => 1, 'Itemid' => EventbookingHelper::getItemid());
		$input      = new RADInput($request);
		$config     = EventbookingHelper::getComponentSettings('site');
		ob_start();

		//Initialize the controller, execute the task
		RADController::getInstance('com_eventbooking', $input, $config)
			->execute();

		return '<div class="clearfix"></div>' . ob_get_clean();
	}
}