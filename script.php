<?php

/**
 * @package        Joomla
 * @subpackage     Membership Pro
 * @author         Tuan Pham Ngoc
 * @copyright      Copyright (C) 2012 - 2016 Ossolution Team
 * @license        GNU/GPL, see LICENSE.php
 */
class Pkg_EventbookingInstallerScript
{
	protected $installType;

	public function preflight($type, $parent)
	{
		if (!version_compare(JVERSION, '3.5.0', 'ge'))
		{
			JError::raiseWarning(null, 'Cannot install Events Booking in a Joomla release prior to 3.5.0');

			return false;
		}

		if (version_compare(PHP_VERSION, '5.4.0', '<'))
		{
			JError::raiseWarning(null, 'Events Booking requires PHP 5.4.0+ to work. Please contact your hosting provider, ask them to update PHP version for your hosting account.');

			return false;
		}
	}

	/**
	 * method to install the component
	 *
	 * @return void
	 */
	public function install($parent)
	{
		$this->installType = 'install';
	}

	public function update($parent)
	{
		$this->installType = 'update';
	}
	
	public function postflight($type, $parent)
	{
		$app = JFactory::getApplication();
		$app->setUserState('com_installer.redirect_url', 'index.php?option=com_eventbooking&task=update.update&install_type=' . $this->installType);
		$app->input->set('return', base64_encode('index.php?option=com_eventbooking&task=update.update&install_type=' . $this->installType));
	}
}