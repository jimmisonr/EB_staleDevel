<?php

/**
 * Form Field class for the Joomla RAD.
 * Supports a checkbox list custom field.
 *
 * @package     Joomla.RAD
 * @subpackage  Form
 */
class RADFormFieldCheckboxes extends RADFormField
{

	/**
	 * The form field type.
	 *
	 * @var string
	 *
	 */
	protected $type = 'Checkboxes';

	/**
	 * Method to instantiate the form field object.
	 *
	 * @param JTable $row
	 *        	the table object store form field definitions
	 * @param mixed $value
	 *        	initial value of the form field
	 *        	
	 */
	public function __construct($row, $value)
	{
		parent::__construct($row, $value);
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return string The field input markup.
	 *        
	 */
	protected function getInput($bootstrapHelper = null)
	{
		$html = array();
		$options = (array) $this->getOptions();
		$attributes = $this->buildAttributes();
		if (is_array($this->value))
		{
			$selectedOptions = $this->value;
		}
		elseif (strpos($this->value, "\r\n"))
		{
			$selectedOptions = explode("\r\n", $this->value);
		}
		elseif (is_string($this->value) && is_array(json_decode($this->value)))
		{
			$selectedOptions = json_decode($this->value);
		}
		else
		{
			$selectedOptions = array($this->value);
		}
		
		$html[] = '<fieldset id="' . $this->name . '" class="row-fluid clearfix"' . '>';
		$html[] = '<ul class="clearfix">';
		$i = 0;
		$size = (int) $this->row->size;
		if (!$size)
		{
			$size = 1;
		}
		$span = intval(12 / $size);
		$numberOptions = count($options);
		foreach ($options as $option)
		{
			$i++;
			$optionValue = trim($option);
			$checked = in_array($optionValue, $selectedOptions) ? 'checked' : '';
			$html[] = '<li class="span' . $span . '">';
			$html[] = '<label for="' . $this->name . $i . '" ><input type="checkbox" id="' . $this->name . $i . '" name="' . $this->name . '[]" value="' .
				 htmlspecialchars($optionValue, ENT_COMPAT, 'UTF-8') . '"' . $checked . $attributes . $this->row->extra_attributes . '/> ' . $option .
				 '</label>';
			$html[] = '</li>';
			if ($i % $size == 0 && $i < $numberOptions)
			{
				$html[] = '</ul>';
				$html[] = '<ul class="clearfix">';
			}
		}
		$html[] = '</ul>';
		
		// End the checkbox field output.
		$html[] = '</fieldset>';
		
		return implode($html);
	}

	protected function getOptions()
	{
		$options = array();
		if (is_array($this->row->values))
		{
			$options = $this->row->values;
		}
		elseif (strpos($this->row->values, "\r\n") !== FALSE)
		{
			$values = explode("\r\n", $this->row->values);
		}
		else
		{
			$values = explode(",", $this->row->values);
		}
		return $values;
	}
}