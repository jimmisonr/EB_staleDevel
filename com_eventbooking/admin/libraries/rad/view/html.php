<?php
/**
 * Html view class
 * 
 * @package     Joomla.RAD
 * @subpackage  ViewHtml
 * @author	Ossolution Team
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die();

jimport('joomla.filesystem.path');

class RADViewHtml extends RADView
{

	/**
	 * Prefix of the language items used in the view
	 * 
	 * @var string
	 */
	protected $languagePrefix;

	/**
	 * The view layout.
	 *
	 * @var    string	 
	 */
	protected $layout = 'default';

	/**
	 * The paths queue.
	 *
	 * @var    Array	 
	 */
	protected $paths = array();

	/**
	 * Method to instantiate the view.
	 *
	 * @param   $config A named configuration array for object construction	 
	 *	 
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		
		if (isset($config['layout']))
		{
			$this->layout = $config['layout'];
		}
		
		if (isset($config['language_prefix']))
		{
			$this->languagePrefix = $config['language_prefix'];
		}
		else
		{
			$this->languagePrefix = strtoupper($this->option);
		}
		
		if (isset($config['template_path']))
		{
			$this->addTemplatePath($config['template_path']);
		}
		else
		{
			throw new RuntimeException('You need to give template path for the view');
		}
	}

	/**
	 * Display the view
	 */
	public function display()
	{
		echo $this->render();
	}

	/**
	 * Magic toString method that is a proxy for the render method.
	 *
	 * @return  string		
	 */
	public function __toString()
	{
		return $this->render();
	}

	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return  string  The escaped output.	 	
	 */
	public function escape($output)
	{
		return htmlspecialchars($output, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Method to get the view layout.
	 *
	 * @return  string  The layout name.	
	 */
	public function getLayout()
	{
		return $this->layout;
	}

	/**
	 * Method to get the layout path.
	 *
	 * @param   string  $layout  The layout name.
	 *
	 * @return  mixed  The layout file name if found, false otherwise.
	 *
	 * @since   12.1
	 */
	public function getPath($layout)
	{
		// Get the layout file name.
		$file = JPath::clean($layout . '.php');
		
		// Find the layout file path.
		$path = JPath::find($this->paths, $file);
		
		return $path;
	}

	/**
	 * Method to get the view paths.
	 *
	 * @return  SplPriorityQueue  The paths queue.
	 *
	 * @since   12.1
	 */
	public function getPaths()
	{
		return $this->paths;
	}

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 * 	 	
	 * @throws  RuntimeException
	 */
	public function render()
	{
		//Add support for template override
		$app = JFactory::getApplication();
		$fallback = JPATH_THEMES . '/' . $app->getTemplate() . '/html/' . $this->option . '/' . $this->getName();
		$this->addTemplatePath($fallback);
		// Get the layout path.
		$path = $this->getPath($this->getLayout());
		
		// Check if the layout path was found.
		if (!$path)
		{
			throw new RuntimeException('Layout Path Not Found');
		}
		
		// Start an output buffer.
		ob_start();
		
		// Load the layout.
		include $path;
		
		// Get the layout contents.
		$output = ob_get_clean();
		
		return $output;
	}

	/**
	 * Load sub-template for the current layout
	 * @param string $template The sub-template to load
	 * @throws RuntimeException
	 * @return string The output of sub-layout
	 */
	public function loadTemplate($template, $data = array())
	{
		extract($data);
		//Add support for template override
		$app = JFactory::getApplication();
		$fallback = JPATH_THEMES . '/' . $app->getTemplate() . '/html/' . $this->option . '/' . $this->getName();
		$this->addTemplatePath($fallback);
		// Get the layout path.
		$path = $this->getPath($this->getLayout() . '_' . $template);
		
		// Check if the layout path was found.
		if (!$path)
		{
			throw new RuntimeException('Layout Path Not Found');
		}
		
		// Start an output buffer.
		ob_start();
		
		// Load the layout.
		include $path;
		
		// Get the layout contents.
		$output = ob_get_clean();
		
		return $output;
	}

	/**
	 * Load a template file from a given absolute path 
	 * 
	 * @param string $path the absolute path to the template file to load
	 * 
	 * @return 
	 */
	public function loadAnyTemplate($path, $data = array())
	{
				
		ob_start();
		
		// Load the layout.
		include $path;
		
		// Get the layout contents.
		$output = ob_get_clean();
		
		return $output;
	}

	/**
	 * Method to set the view layout.
	 *
	 * @param   string  $layout  The layout name.
	 *
	 * @return  RADViewHtml  Method supports chaining.
	 *	 
	 */
	public function setLayout($layout)
	{
		$this->layout = $layout;
		
		return $this;
	}

	/**
	 * Add Template path to the queue
	 * 
	 * @param mixed $path The directory or stream, or an array of either, to search.
	 */
	public function addTemplatePath($path)
	{
		// Just force to array
		settype($path, 'array');
		
		// Loop through the path directories
		foreach ($path as $dir)
		{
			// No surrounding spaces allowed!
			$dir = trim($dir);
			
			// Add trailing separators as needed
			if (substr($dir, -1) != DIRECTORY_SEPARATOR)
			{
				// Directory
				$dir .= DIRECTORY_SEPARATOR;
			}
			
			// Add to the top of the search dirs
			array_unshift($this->paths, $dir);
		}
	}

	/**
	 * Method to set the view paths.
	 *
	 * @param   $paths  The paths queue.
	 *
	 * @return  RADViewHtml  Method supports chaining.
	 *	 
	 */
	public function setPaths($paths)
	{
		$this->paths = $paths;
		
		return $this;
	}
}
