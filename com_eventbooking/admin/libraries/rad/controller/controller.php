<?php
/**
 * Generic Controller Class
 *
 * @author      Ossolution Team
 * @package     RAD
 * @subpackage	Controller    
 */
defined('_JEXEC') or die();

class RADController
{

	/**
	 * Array which hold all the controller objects has been created
	 * 
	 * @var Array
	 */
	protected static $instances = array();

	/**
	 * The application object.
	 *
	 * @var    JApplication	 
	 */
	protected $app;

	/**
	 * The input object.
	 *
	 * @var    RADInput	 
	 */
	protected $input;

	/**
	 * Full name of the component being dispatchaed com_foobar
	 * 
	 * @var string
	 */
	protected $option;

	/**
	 * Name of the component, use as prefix for controller, model and view classes
	 * @var string
	 */
	protected $component;

	/**
	 * Name of the controller
	 * @var string
	 */
	protected $name;

	/**
	 * Prefix for Model class, to allow auto-loader, it is forced to ComponentnameModel
	 *
	 * @var string
	 */
	protected $modelPrefix;

	/**
	 * Prefix for View class, to allow auto-loader, it is forced to ComponentnameView
	 *
	 * @var string
	 */
	protected $viewPrefix;

	/**
	 * Language prefix, used for language string
	 * 
	 * @var string
	 */
	protected $languagePrefix;

	/**
	 * Database table prefix, use as prefix for all tables in the component
	 * 
	 * @var string
	 */
	protected $tablePrefix;

	/**
	 * The default view which will be rendered in case there is no view specified
	 *
	 * @var string
	 */
	protected $defaultView;

	/**
	 * The path to component folder
	 * 
	 * @var string
	 */
	protected $basePath;

	/**
	 * Array of class methods
	 *
	 * @var    array
	 */
	protected $methods;

	/**
	 * Array of class methods to call for a given task.
	 *
	 * @var array
	 */
	protected $taskMap = array();

	/**
	 * Current or most recently performed task.
	 *
	 * @var    string	 	
	 */
	protected $task;

	/**
	 * Redirect message.
	 *
	 * @var    string	 	 
	 */
	protected $message;

	/**
	 * Redirect message type.
	 *
	 * @var    string	 
	 */
	protected $messageType;

	/**
	 * URL for redirection.
	 *
	 * @var    string	 
	 */
	protected $redirect;

	/**
	 * Method to get instance of a controller
	 *
	 * @param Array $config
	 *
	 * @return RADController
	 */
	public static function getInstance($config = array())
	{
		if (isset($config['input']))
		{
			$input = $config['input'];
		}
		else
		{
			$input = new RADInput();
		}
		$option = $input->getCmd('option');
		$view = $input->getCmd('view');
		$component = substr($option, 4);
		if (!isset(self::$instances[$component . $view]))
		{
			if ($view)
			{
				$class = ucfirst($component) . 'Controller' . ucfirst(RADInflector::singularize($view));
			}
			else
			{
				$class = ucfirst($component) . 'Controller';
			}
			//Fallback to default class
			if (!class_exists($class))
			{
				if (isset($config['fallback_class']))
				{
					$class = $config['fallback_class'];
				}
				else
				{
					$app = JFactory::getApplication();
					if ($app->isAdmin())
					{
						$class = 'RADControllerAdmin';
					}
					else
					{
						$class = 'RADController';
					}
				}
			}
			self::$instances[$option . $view] = new $class($config);
		}
		
		return self::$instances[$option . $view];
	}

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.	 	
	 *	 
	 */
	public function __construct($config = array())
	{
		$this->app = JFactory::getApplication();
		if (isset($config['input']))
		{
			$this->input = $config['input'];
		}
		else
		{
			$this->input = new RADInput();
		}
		//Build the default taskMap based on the class methods
		$xMethods = get_class_methods('RADController');
		$r = new ReflectionClass($this);
		$rMethods = $r->getMethods(ReflectionMethod::IS_PUBLIC);
		foreach ($rMethods as $rMethod)
		{
			$mName = $rMethod->getName();
			if (!in_array($mName, $xMethods) || $mName == 'display')
			{
				$this->taskMap[strtolower($mName)] = $mName;
				$this->methods[] = strtolower($mName);
			}
		}
		$this->option = $this->input->get('option');
		$this->component = substr($this->option, 4);
		$this->modelPrefix = ucfirst($this->component) . 'Model';
		$this->viewPrefix = ucfirst($this->component) . 'View';
		if (isset($config['name']))
		{
			$this->name = $config['name'];
		}
		else
		{
			$this->name = RADInflector::singularize($this->input->get('view'));
			if (!$this->name)
			{
				$this->name = 'controller';
			}
		}
		if ($this->app->isSite())
		{
			$this->basePath = JPATH_ROOT . '/components/' . $this->option;
		}
		else
		{
			$this->basePath = JPATH_ROOT . '/administrator/components/' . $this->option;
		}
		
		if (isset($config['language_prefix']))
		{
			$this->languagePrefix = $config['language_prefix'];
		}
		else
		{
			$this->languagePrefix = strtoupper($this->component);
		}
		
		if (isset($config['table_prefix']))
		{
			$this->tablePrefix = $config['table_prefix'];
		}
		else
		{
			$this->tablePrefix = '#__' . strtolower($this->component) . '_';
		}
		
		if (isset($config['default_view']))
		{
			$this->defaultView = $config['default_view'];
		}
		else
		{
			$this->defaultView = $this->component;
		}
		
		$this->task = $this->input->get('task', 'display');
		if (isset($config['default_task']))
		{
			$this->registerTask('__default', $config['default_task']);
		}
		else
		{
			$this->registerTask('__default', 'display');
		}
	}

	/**
	 * Excute the given task
	 * 
	 * @return RADController return itself to support changing 	 
	 */
	public function execute()
	{
		$task = strtolower($this->task);
		if (isset($this->taskMap[$task]))
		{
			$doTask = $this->taskMap[$task];
		}
		elseif (isset($this->taskMap['__default']))
		{
			$doTask = $this->taskMap['__default'];
		}
		else
		{
			throw new Exception(JText::sprintf('JLIB_APPLICATION_ERROR_TASK_NOT_FOUND', $task), 404);
		}
		$this->$doTask();
		
		return $this;
	}

	/**
	 * Method to display a view
	 *
	 * This function is provide as a default implementation, in most cases
	 * you will need to override it in your own controllers.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  RADControllerBase  A RADControllerBase object to support chaining.	 	
	 */
	public function display($cachable = false, $urlparams = array())
	{
		$viewType = JFactory::getDocument()->getType();
		$viewName = $this->input->get('view', $this->defaultView);
		$viewLayout = $this->input->get('layout', 'default');
		$config = array('template_path' => $this->basePath . '/view/' . $viewName . '/tmpl', 'view_type' => $viewType, 'layout' => $viewLayout);
		$view = $this->getView($viewName, $config);
		$view->display();
		
		return $this;
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param   string  $name    The model name. Optional.	 
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  object  The model.
	 *	 
	 */
	public function getModel($name = '', $config = array())
	{
		if (empty($name))
		{
			$name = $this->name;
		}
		$defaultConfig = array(
			'option' => $this->option, 
			'name' => $name, 
			'language_prefix' => $this->languagePrefix, 
			'table_prefix' => $this->tablePrefix);
		
		if ($this->app->isAdmin())
		{
			If (RADInflector::isPlural($name))
			{
				$defaultConfig['fallback_class'] = 'RADModelList';
			}
			else
			{
				$defaultConfig['fallback_class'] = 'RADModelItem';
			}
		}
		
		$config = array_merge($defaultConfig, $config);
		$model = RADModel::getInstance($name, $this->modelPrefix, $config);
		$model->set($this->input->getData());
		
		return $model;
	}

	/**
	 * Method to get a reference to the current view and load it if necessary.
	 *
	 * @param   string  $name    The view name. Optional, defaults to the controller name.	 
	 * @param   array   $config  Configuration array for view. Optional.
	 *
	 * @return  JViewBase Reference to the view or an error.
	 *	 
	 * @throws  Exception
	 */
	public function getView($name = '', $config = array())
	{
		if (empty($name))
		{
			$name = $this->component;
		}
		if (isset($config['view_type']))
		{
			$type = $config['view_type'];
		}
		else
		{
			$type = 'html';
		}
		$model = $this->getModel($name);
		$defaultConfig = array(
			'option' => $this->option, 
			'name' => $name, 
			'model' => $model, 
			'view_type' => $type, 
			'language_prefix' => $this->languagePrefix, 
			'template_path' => $this->basePath . '/view/' . $name . '/tmpl');
		
		//The full class Name of the view
		$class = ucfirst($this->viewPrefix) . ucfirst($name) . ucfirst($type);


		$config = array_merge($defaultConfig, $config);
		if (!class_exists($class))
		{
			if ($this->app->isAdmin())
			{
				If (RADInflector::isPlural($name))
				{
					$class = 'RADViewList';
				}
				else
				{
					$class = 'RADViewItem';
				}
			}
			else
			{
				$class = 'RADView' . ucfirst($type);
			}
		}
		$view = new $class($config);
		
		return $view;
	}

	/**
	 * Sets the internal message that is passed with a redirect
	 *
	 * @param   string  $text  Message to display on redirect.
	 * @param   string  $type  Message type. Optional, defaults to 'message'.
	 *
	 * @return  string  Previous message
	 *	 
	 */
	public function setMessage($text, $type = 'message')
	{
		$previous = $this->message;
		$this->message = $text;
		$this->messageType = $type;
		
		return $previous;
	}

	/**
	 * Set a URL for browser redirection.
	 *
	 * @param   string  $url   URL to redirect to.
	 * @param   string  $msg   Message to display on redirect. Optional, defaults to value set internally by controller, if any.
	 * @param   string  $type  Message type. Optional, defaults to 'message' or the type set by a previous call to setMessage.
	 *
	 * @return  RADControllerBase  This object to support chaining.
	 *	 
	 */
	public function setRedirect($url, $msg = null, $type = null)
	{
		$this->redirect = $url;
		if ($msg !== null)
		{
			// Controller may have set this directly
			$this->message = $msg;
		}
		
		// Ensure the type is not overwritten by a previous call to setMessage.
		if (empty($type))
		{
			if (empty($this->messageType))
			{
				$this->messageType = 'message';
			}
		}
		// If the type is explicitly set, set it.
		else
		{
			$this->messageType = $type;
		}
		
		return $this;
	}

	/**
	 * Redirects the browser or returns false if no redirect is set.
	 *
	 * @return  boolean  False if no redirect exists.
	 *	 
	 */
	public function redirect()
	{
		if ($this->redirect)
		{
			$this->app->redirect($this->redirect, $this->message, $this->messageType);
		}
		
		return false;
	}

	/**
	 * Get the last task that is being performed or was most recently performed.
	 *
	 * @return  string  The task that is being performed or was most recently performed.
	 *	 
	 */
	public function getTask()
	{
		return $this->task;
	}

	/**
	 * Register (map) a task to a method in the class.
	 *
	 * @param   string  $task    The task.
	 * @param   string  $method  The name of the method in the derived class to perform for this task.
	 *
	 * @return  RADControllerBase  A RADControllerBase object to support chaining.
	 * 	 	
	 */
	public function registerTask($task, $method)
	{
		if (in_array(strtolower($method), $this->methods))
		{
			$this->taskMap[strtolower($task)] = $method;
		}
		
		return $this;
	}

	/**
	 * Get the application object.
	 *
	 * @return  JApplicationBase  The application object.
	 *
	 */
	public function getApplication()
	{
		return $this->app;
	}

	/**
	 * Get the input object.
	 *
	 * @return  JInput  The input object.
	 *
	 */
	public function getInput()
	{
		return $this->input;
	}
}
