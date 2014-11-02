<?php
/**
 * Form Field class for the Joomla RAD.
 * Supports a message form field
 *
 * @package     Joomla.RAD
 * @subpackage  Form
 */
class RADFormFieldMessage extends RADFormField
{

	/**
	 * The form field type.
	 *
	 * @var string
	 *
	 */
	protected $type = 'Message';

	/**
	 * Method to get the field input markup.
	 *
	 * @return string The field input markup.
	 *        
	 */
	public function getInput()
	{
		$controlGroupAttributes = 'id="field_' . $this->name . '" ';
		if ($this->hideOnDisplay)
		{
			$controlGroupAttributes .= ' style="display:none;" ';
		}
		
		return '<div class="control-group eb-message" ' . $controlGroupAttributes . '>' . $this->description . '</div>';
	}

	/**
	 * Get control group used to display on form
	 *
	 * @see RADFormField::getControlGroup()
	 */
	public function getControlGroup()
	{
		return $this->getInput();
	}

	/**
	 * Get output used for displaying on email and the detail page
	 *
	 * @see RADFormField::getOutput()
	 */
	public function getOutput($tableLess)
	{
		if ($tableLess)
		{
			return $this->getInput();
		}
		else
		{
			return '<tr>' . '<td class="eb-message" colspan="2">' . $this->description . '</td></tr>';
		}
	}
}