<?php
/**
 * @version            2.5.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die;

class EventbookingModelRegistrantlist extends EventbookingModelRegistrants
{
	/**
	 * Instantiate the model.
	 *
	 * @param array $config configuration data for the model
	 *
	 */
	public function __construct($config = array())
	{
		$config['remember_states'] = false;
		
		parent::__construct($config);

		$this->state->insert('id', 'int', 0);
	}

	/**
	 * Build where clause of the query
	 *
	 * @see RADModelList::buildQueryWhere()
	 */
	protected function buildQueryWhere(JDatabaseQuery $query)
	{
		parent::buildQueryWhere($query);

		$query->where('tbl.published NOT IN (2,3)');

		return $this;
	}
}