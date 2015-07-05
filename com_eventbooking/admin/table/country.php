<?php
/**
 * Countries Table Class
 *
 */
class EventbookignTableCountry extends JTable
{

	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function __construct(& $db)
	{
		parent::__construct('#__eb_countries', 'id', $db);
	}
}
