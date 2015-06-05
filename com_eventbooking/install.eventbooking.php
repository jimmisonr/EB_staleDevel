<?php
/**
 * @version        	1.7.3
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

class com_eventbookingInstallerScript
{

	public static $languageFiles = array('en-GB.com_eventbooking.ini');

	protected $installType;

	/**
	 * Method to run before installing the component
	 */
	function preflight($type, $parent)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		//Backup the old language files
		foreach (self::$languageFiles as $languageFile)
		{
			if (JFile::exists(JPATH_ROOT . '/language/en-GB/' . $languageFile))
			{
				JFile::copy(JPATH_ROOT . '/language/en-GB/' . $languageFile, JPATH_ROOT . '/language/en-GB/bak.' . $languageFile);
			}
		}
		//Deleting files/folders which are not using in latest version						
		if (JFolder::exists(JPATH_ADMINISTRATOR . '/components/com_eventbooking/models'))
		{
			JFolder::delete(JPATH_ADMINISTRATOR . '/components/com_eventbooking/models');
		}
		if (JFolder::exists(JPATH_ADMINISTRATOR . '/components/com_eventbooking/views'))
		{
			JFolder::delete(JPATH_ADMINISTRATOR . '/components/com_eventbooking/views');
		}
		if (JFolder::exists(JPATH_ADMINISTRATOR . '/components/com_eventbooking/view/daylightsaving'))
		{
			JFolder::delete(JPATH_ADMINISTRATOR . '/components/com_eventbooking/view/daylightsaving');
		}
		if (JFolder::exists(JPATH_ROOT . '/components/com_eventbooking/views/confirmation'))
		{
			JFolder::delete(JPATH_ROOT . '/components/com_eventbooking/views/confirmation');
		}
		if (JFile::exists(JPATH_ADMINISTRATOR . '/components/com_eventbooking/model/daylightsaving.php'))
		{
			JFile::delete(JPATH_ADMINISTRATOR . '/components/com_eventbooking/model/daylightsaving.php');
		}
		if (JFile::exists(JPATH_ADMINISTRATOR . '/components/com_eventbooking/controller/daylightsaving.php'))
		{
			JFile::delete(JPATH_ADMINISTRATOR . '/components/com_eventbooking/controller/daylightsaving.php');
		}
		if (JFile::exists(JPATH_ROOT . '/components/com_eventbooking/helper/os_cart.php'))
		{
			JFile::delete(JPATH_ROOT . '/components/com_eventbooking/helper/os_cart.php');
		}
		if (JFile::exists(JPATH_ROOT . '/components/com_eventbooking/helper/fields.php'))
		{
			JFile::delete(JPATH_ROOT . '/components/com_eventbooking/helper/fields.php');
		}
		if (JFile::exists(JPATH_ROOT . '/components/com_eventbooking/helper/captcha.php'))
		{
			JFile::delete(JPATH_ROOT . '/components/com_eventbooking/helper/captcha.php');
		}		
		if (JFile::exists(JPATH_ROOT . '/components/com_eventbooking/views/register/tmpl/group_member.php'))
		{
			JFile::delete(JPATH_ROOT . '/components/com_eventbooking/views/register/tmpl/group_member.php');
		}

		//Delete the the waiting list related files
		if (JFile::exists(JPATH_ROOT . '/components/com_eventbooking/views/waitinglist/tmpl/complete.php'))
		{
			JFile::delete(JPATH_ROOT . '/components/com_eventbooking/views/register/tmpl/complete.php');
		}

		if (JFile::exists(JPATH_ROOT . '/components/com_eventbooking/models/waitinglist.php'))
		{
			JFile::delete(JPATH_ROOT . '/components/com_eventbooking/models/waitinglist.php');
		}

		if (JFolder::exists(JPATH_ADMINISTRATOR . '/components/com_eventbooking/view/waiting'))
		{
			JFolder::delete(JPATH_ADMINISTRATOR . '/components/com_eventbooking/view/waiting');
		}

		if (JFolder::exists(JPATH_ADMINISTRATOR . '/components/com_eventbooking/view/waitings'))
		{
			JFolder::delete(JPATH_ADMINISTRATOR . '/components/com_eventbooking/view/waitings');
		}

		if (JFile::exists(JPATH_ADMINISTRATOR . '/components/com_eventbooking/model/waitings.php'))
		{
			JFile::delete(JPATH_ADMINISTRATOR . '/components/com_eventbooking/model/waitings.php');
		}

		if (JFile::exists(JPATH_ADMINISTRATOR . '/components/com_eventbooking/model/waiting.php'))
		{
			JFile::delete(JPATH_ADMINISTRATOR . '/components/com_eventbooking/model/waiting.php');
		}

		//Delete the css files which are now moved to themes folder
		$files = array('default.css', 'fire.css', 'leaf.css', 'ocean.css', 'sky.css', 'tree.css');
		$path = JPATH_ROOT . '/components/com_eventbooking/assets/css/';
		foreach ($files as $file)
		{
			$filePath = $path . $file;
			if (JFile::exists($filePath))
			{
				JFile::delete($filePath);
			}
		}
		#Remove htaccess file to support image feature
		if (JFile::exists(JPATH_ROOT . '/media/com_eventbooking/.htaccess'))
		{
			JFile::delete(JPATH_ROOT . '/media/com_eventbooking/.htaccess');
		}
		//Backup files which need to be keep 
		if (JFile::exists(JPATH_ROOT . '/components/com_eventbooking/fields.xml'))
		{
			JFile::copy(JPATH_ROOT . '/components/com_eventbooking/fields.xml', JPATH_ROOT . '/components/com_eventbooking/bak.fields.xml');
		}
		
		if (JFile::exists(JPATH_ROOT . '/components/com_eventbooking/assets/css/custom.css'))
		{
			JFile::copy(JPATH_ROOT . '/components/com_eventbooking/assets/css/custom.css', 
				JPATH_ROOT . '/components/com_eventbooking/assets/css/bak.custom.css');
		}
		if (JFolder::exists(JPATH_ROOT . '/components/com_eventbooking/assets/validate'))
		{
			JFolder::delete(JPATH_ROOT . '/components/com_eventbooking/assets/validate');
		}
		if (JFolder::exists(JPATH_ROOT . '/components/com_eventbooking/assets/colorbox'))
		{
			JFolder::delete(JPATH_ROOT . '/components/com_eventbooking/assets/colorbox');
		}
	}

	/**
	 * method to install the component
	 *
	 * @return void
	 */
	function install($parent)
	{
		$this->installType = 'install';
	}

	function update($parent)
	{
		$this->installType = 'update';
	}

	/**
	 * Method to run after installing the component
	 */
	function postflight($type, $parent)
	{
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');
		//Restore the modified language strings by merging to language files
		$registry = new JRegistry();
		foreach (self::$languageFiles as $languageFile)
		{
			$backupFile = JPATH_ROOT . '/language/en-GB/bak.' . $languageFile;
			$currentFile = JPATH_ROOT . '/language/en-GB/' . $languageFile;
			if (JFile::exists($currentFile) && JFile::exists($backupFile))
			{
				$registry->loadFile($currentFile, 'INI');
				$currentItems = $registry->toArray();
				$registry->loadFile($backupFile, 'INI');
				$backupItems = $registry->toArray();
				$items = array_merge($currentItems, $backupItems);
				$content = "";
				foreach ($items as $key => $value)
				{
					$content .= "$key=\"$value\"\n";
				}
				JFile::write($currentFile, $content);
				//Delete the backup file
				JFile::delete($backupFile);
			}
		}
		//Restore the renamed files
		if (JFile::exists(JPATH_ROOT . '/components/com_eventbooking/bak.fields.xml'))
		{
			JFile::copy(JPATH_ROOT . '/components/com_eventbooking/bak.fields.xml', JPATH_ROOT . '/components/com_eventbooking/fields.xml');
			JFile::delete(JPATH_ROOT . '/components/com_eventbooking/bak.fields.xml');
		}
		if (JFile::exists(JPATH_ROOT . '/components/com_eventbooking/assets/css/bak.custom.css'))
		{
			JFile::copy(JPATH_ROOT . '/components/com_eventbooking/assets/css/bak.custom.css', 
				JPATH_ROOT . '/components/com_eventbooking/assets/css/custom.css');
			JFile::delete(JPATH_ROOT . '/components/com_eventbooking/assets/css/bak.custom.css');
		}

		if (JFile::exists(JPATH_ROOT . '/components/com_eventbooking/views/register/metadata.xml'))
		{
			JFile::delete(JPATH_ROOT . '/components/com_eventbooking/views/register/metadata.xml');
		}
		
		JFactory::getApplication()->redirect(
			JRoute::_('index.php?option=com_eventbooking&task=update_db_schema&install_type=' . $this->installType, false));
	}
}