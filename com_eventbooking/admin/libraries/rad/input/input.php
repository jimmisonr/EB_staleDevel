<?php
/**
 * Extends JInput class to allow getting raw data from Input object. This can be removed when we don't provide support for Joomla 2.5.x
 *
 * @author      Ossolution Team
 * @package     RAD
 * @subpackage	Controller
 */
class RADInput extends JInput
{

	/**
	 * Constructor.
	 *
	 * @param   array  $source   Source data (Optional, default is $_REQUEST)
	 * @param   array  $options  Array of configuration parameters (Optional)
	 *	
	 */
	public function __construct($source = null, array $options = array())
	{
		if (!isset($options['filter']))
		{
			//Set default filter so that getHtml can be returned properly
			$options['filter'] = JFilterInput::getInstance(null, null, 1, 1);
		}
					
		parent::__construct($source, $options);

        if (get_magic_quotes_gpc())
        {
            $this->data = self::stripSlashesRecursive($this->data);
        }
	}

	/**
	 * Get the row data from input
	 * 
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}


    protected static function stripSlashesRecursive($value)
    {
        $value = is_array($value) ? array_map(array('RADInput', 'stripSlashesRecursive'), $value) : stripslashes($value);
        return $value;
    }
}