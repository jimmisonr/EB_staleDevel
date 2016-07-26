<?php
/**
 * @version            2.8.1
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

/**
 * EventBooking Plugin controller
 *
 * @package        Joomla
 * @subpackage     Event Booking
 */
class EventbookingControllerPlugin extends EventbookingController
{
	/**
	 * Install a payment plugin
	 */
	public function install()
	{
		if (version_compare(JVERSION, '3.4.0', 'ge'))
		{
			$plugin = $this->input->files->get('plugin_package', null, 'raw');
		}
		else
		{
			$plugin = $this->input->files->get('plugin_package', null, 'none');
		}
		$model  = $this->getModel();
		try
		{
			$model->install($plugin);
			$this->setRedirect(JRoute::_('index.php?option=com_eventbooking&view=plugins', false), JText::_('EB_PLUGIN_INSTALLED'));
		}
		catch (Exception $e)
		{
			$this->app->enqueueMessage($e->getMessage(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_eventbooking&view=plugins', false), JText::_('EB_PLUGIN_INSTALL_FAILED'));
		}
	}

	/**
	 * Uninstall a payment plugin
	 */
	public function uninstall()
	{
		$model = $this->getModel();
		$cid   = $this->input->get('cid', array(), 'array');
		$model->uninstall($cid[0]);
		$this->setRedirect(JRoute::_('index.php?option=com_eventbooking&view=plugins', false), JText::_('EB_PLUGIN_UNINSTALLED'));
	}
}
