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
		if (!version_compare(JVERSION, '3.4.0', 'ge'))
		{
			JError::raiseWarning(null, 'Cannot install Membership Pro in a Joomla release prior to 3.4.0');

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
		JFactory::getApplication()->redirect(
			JRoute::_('index.php?option=com_eventbooking&task=update_db_schema&install_type=' . $this->installType, false));
	}
}