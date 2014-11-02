<?php
class RADFormFieldDate extends RADFormField
{

	/**
	 * The form field type.
	 *
	 * @var    string
	 *	 
	 */
	protected $type = 'Date';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *	 
	 */
	protected function getInput()
	{
		$attributes = $this->buildAttributes();
		return JHtml::_('calendar', $this->value, $this->name, $this->name, '%Y-%m-%d',".$attributes.");
	}
};