<?php
/**
 * @version        	1.6.9
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
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
class EventbookingControllerDaylightsaving extends EventbookingController
{

    function fix_daylight_saving_time()
    {
        $data = $this->input->getData();
        $model =  $this->getModel('daylightsaving');
        $model->process($data);
        $this->setRedirect('index.php?option=com_eventbooking&view=daylightsaving', JText::_('Day Light saving time issue fixed'));
    }
}