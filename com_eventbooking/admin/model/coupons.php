<?php
/**
 * @version        	1.7.0
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();
class EventbookingModelCoupons extends RADModelList
{

	/**
	 * Constructor function, init data for the model
	 *
	 */
	function __construct($config)
	{
		$config['search_fields'] = array('tbl.code');
		
		parent::__construct($config);
	}
}