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
	public function preflight($type, $parent)
	{
		if (!version_compare(JVERSION, '3.4.0', 'ge'))
		{
			JError::raiseWarning(null, 'Cannot install Membership Pro in a Joomla release prior to 3.4.0');

			return false;
		}
	}
}