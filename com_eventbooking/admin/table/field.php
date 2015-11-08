<?php
/**
 * @version            2.1.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2015 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

class EventbookingTableField extends JTable
{

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	public function __construct(& $db)
	{
		parent::__construct('#__eb_fields', 'id', $db);
	}
}
