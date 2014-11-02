<?php
/**
 * Base view class
 * 
 * @package     Joomla.RAD
 * @subpackage  View
 * @author	Ossolution Team
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die();

abstract class RADView
{

	/**
	 * Name of the view
	 * 
	 * @var string
	 */
	protected $name;

	/**
	 * The model object.
	 *
	 * @var    RADModel	 
	 */
	protected $model;

	/**
	 * Full name of the component
	 *
	 * @var string
	 */
	protected $option;

	/**
	 * Constructor
	 *
	 * @param   array  $config  A named configuration array for object construction.<br/>                        
	 *
	 */
	public function __construct($config = array())
	{
		if (isset($config['name']))
		{
			$this->name = $config['name'];
		}
		else
		{
			$className = get_class($this);
			$pos = strpos('View', $className);
			if ($pos !== false)
			{
				$this->name = substr($className, $pos + 4);
			}
		}
		
		if (isset($config['option']))
		{
			$this->option = $config['option'];
		}
		else
		{
			$className = get_class($this);
			$pos = strpos('View', $className);
			if ($pos !== false)
			{
				$this->option = substr($className, 0, $pos);
			}
		}
				
		if (isset($config['model']))
		{
			$this->model = $config['model'];
		}
	}

	/**
	 * Get name of the current view
	 * 
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Set the model object
	 * 
	 * @param RADModel $model
	 */
	public function setModel(RADModel $model)
	{
		$this->model = $model;
	}

	/**
	 * Get the model object 
	 * 
	 * @return RADModel
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * Helper method to call a model function to get the data. This is used for BC only. With the new design, the view call model method directly
	 * 	 
	 * @param   string  $property  The name of the method to call on the model (without get)
	 * 	 	 
	 * @return  mixed  The return value of the method
	 * 		
	 */
	public function get($property)
	{
		$result = null;
		$model = $this->model;
		if ($model)
		{
			// Model exists, let's build the method name
			$method = 'get' . ucfirst($property);
			
			// Does the method exist?
			if (method_exists($model, $method))
			{
				// The method exists, let's call it and return what we get
				$result = $model->$method();
			}
		}
		
		return $result;
	}

	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return  string  The escaped output.
	 *		
	 */
	public function escape($output)
	{
		return $output;
	}
}
