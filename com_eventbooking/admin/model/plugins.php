<?php
/**
 * @version        	1.6.6
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2014 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventbookingModelPlugins extends RADModelList
{

	/**
	 * Constructor function	 
	 */
	function __construct($config)
	{
		$config['table_prefix'] = '#__eb_payment_';
		
		parent::__construct($config);				
	}
}