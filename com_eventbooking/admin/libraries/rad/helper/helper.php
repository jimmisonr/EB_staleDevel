<?php
/**
 * @package     Joomla.RAD
 * @subpackage  Helper
 * @author		Ossolution Team
 */
defined('_JEXEC') or die();

/**
 * This class provide some common functions used when developing Joomla component
 * 
 * @author Ossolution Team
 */
class RADHelper
{

	/**
	 * Build admin sub-menus based on the menu items setup in XML manifest file of the component
	 * 
	 * @param string $option URL option of the component
	 */
	public static function addSubmenus($option)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
	}

	/**
	 * Get list of actions which the current user can do on the given component
	 * 
	 * @param string $option
	 */
	public static function getActions($option)
	{
		$result = new JObject();
		$user = JFactory::getUser();
		$actions = array('core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete');
		
		foreach ($actions as $action)
		{
			$result->set($action, $user->authorise($action, $option));
		}
		
		return $result;
	}
}