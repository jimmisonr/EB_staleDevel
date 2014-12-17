<?php
/**
 * @version        	1.6.9
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2014 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

/**
 * EventBooking Configuration controller
 *
 * @package		Joomla
 * @subpackage	Event Booking
 */
class EventbookingControllerConfiguration extends EventbookingController
{
    public function __construct($config = array())
    {
        parent::__construct($config);

        $this->registerTask('apply', 'save');
    }

	public function save()
	{
		$data = $this->input->getData();
		$model = $this->getModel();
		$model->store($data);		
		//Publish the neccessary plugins
		$db = JFactory::getDbo();
		if ($data['activate_invoice_feature'])
		{			
			$sql = 'UPDATE #__extensions SET `enabled`= 1 WHERE `element`="invoice" AND `folder`="eventbooking"';
			$db->setQuery($sql);
			$db->execute();			
		}
		if ($data['multiple_booking'])
		{
			$sql = 'UPDATE #__extensions SET `enabled`= 1 WHERE `element`="cartupdate" AND `folder`="eventbooking"';
			$db->setQuery($sql);
			$db->execute();
		}		
        $task = $this->getTask();
        if ($task == 'save')
        {
            $this->setRedirect('index.php?option=com_eventbooking&view=dashboard', JText::_('EB_CONFIGURATION_DATA_SAVED'));
        }
        else
        {
            $this->setRedirect('index.php?option=com_eventbooking&view=configuration', JText::_('EB_CONFIGURATION_DATA_SAVED'));
        }
	}
	
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_eventbooking');
	}
}