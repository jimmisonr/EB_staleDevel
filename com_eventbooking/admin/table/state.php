<?php
/**
 * State Table Class
 *
 */
class EventbookingTableState extends JTable
{

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function __construct(& $db)
	{
		parent::__construct('#__eb_states', 'id', $db);
	}
}
