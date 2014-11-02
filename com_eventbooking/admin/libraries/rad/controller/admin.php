<?php
/**
 * Admin Controller Class, implement basic tasks which is used when developing component from admin
 *
 * @author      Ossolution Team
 * @package     RAD
 * @subpackage  ControllerAdmin 
 */
defined('_JEXEC') or die();

class RADControllerAdmin extends RADController
{

	/**
	 * The URL view item variable.
	 *
	 * @var    string	 
	 */
	protected $viewItem;

	/**
	 * The URL view list variable.
	 *
	 * @var    string	 
	 */
	protected $viewList;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     RADControllerAdmin	 
	 * @throws  Exception
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		
		if (isset($config['view_item']))
		{
			$this->viewItem = $config['view_item'];
		}
		else
		{
			$this->viewItem = $this->name;
		}
		
		if (isset($config['view_list']))
		{
			$this->viewList = $config['view_list'];
		}
		else
		{
			$this->viewList = RADInflector::pluralize($this->viewItem);
		}
		
		$this->registerTask('apply', 'save');
		$this->registerTask('save2new', 'save');
		$this->registerTask('unpublish', 'publish');
		$this->registerTask('orderup', 'reorder');
		$this->registerTask('orderdown', 'reorder');
	}

	/**
	 * Display Form allows adding a new record
	 */
	public function add()
	{
		if ($this->allowAdd($this->input->getData()))
		{
			$this->input->set('view', $this->viewItem);
			$this->input->set('edit', false);
			$this->display();
		}
		else
		{
			$this->app->redirect('index.php', JText::_('You are not allowed to add new record'));
		}
	}

	/**
	 * Display Form allows editing record
	 */
	public function edit()
	{
		$data = $this->input->getData();
		if (isset($data['cid']) && count($data['cid']))
		{
			$data['id'] = (int) $data['cid'];
		}
		if ($this->allowEdit($data))
		{
			$this->input->set('view', $this->viewItem);
			$this->input->set('edit', false);
			$this->display();
		}
		else
		{
			$this->app->redirect('index.php', JText::_('You are not allowed to edit this record'));
		}
	}

	/**
	 * Generic save function
	 */
	public function save()
	{
		$data = $this->input->getData();
		$cid = $data['cid'];
		if (count($cid))
		{
			$data['id'] = (int) $cid[0];
			$this->input->set('id', $data["id"]);
		}
				
		if ($this->allowSave($data))
		{
			try
			{
				$model = $this->getModel($this->name, array('fallback_class' => 'RADModelItem'));
				$ret = $model->store($this->input);
				if ($ret)
				{
					$msg = JText::_($this->languagePrefix . '_' . strtoupper($this->name) . '_SAVED');
				}
				else
				{
					$msg = JText::_($this->languagePrefix . '_' . strtoupper($this->name) . '_SAVING_ERROR');
				}
				$task = $this->getTask();
					
				if ($task == 'save')
				{
					$url = JRoute::_('index.php?option=' . $this->option . '&view=' . $this->viewList, false);
				}
				elseif ($task == 'apply')
				{
					$url = JRoute::_('index.php?option=' . $this->option . '&view=' . $this->viewItem . '&id=' . $this->input->getInt('id'), false);
				}
				else
				{
					$url = JRoute::_('index.php?option=' . $this->option . '&view=' . $this->viewItem, false);
				}
				$this->setRedirect($url, $msg);
			}
			catch (Exception $e)
			{				
				$this->app->enqueueMessage($e->getMessage(), 'error');				
				$this->app->redirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->viewItem . '&id=' . $this->input->getInt('id'), false));
			}						
		}
		else
		{
			$this->app->redirect('index.php', JText::_('You are not allowed to save the record'));
		}
	}

	/**
	 * Save ordering of the records
	 */
	public function save_order()
	{
		$order = $this->input->get('order', array(), 'array');
		$cid = $this->input->get('cid', array(), 'array');
		JArrayHelper::toInteger($order);
		JArrayHelper::toInteger($cid);
		for ($i = 0, $n = count($cid); $i < $n; $i++)
		{
			if (!$this->allowEditState($cid[$i]))
			{
				unset($cid[$i]);
			}
		}
		if (count($cid))
		{
			try 
			{
				$model = $this->getModel($this->name, array('fallback_class' => 'RADModelItem'));
				$ret = $model->saveOrder($cid, $order);
				if ($ret)
				{
					$msg = JText::_($this->languagePrefix . '_ORDERING_SAVED');
				}
				else
				{
					$msg = JText::_($this->languagePrefix . '_ORDERING_SAVING_ERROR');
				}				
			}
			catch (Exception $e)
			{
				$msg = null;
				$this->app->enqueueMessage($e->getMessage(), 'error');				
			}													
		}
		else
		{
			$msg = JText::_('No records selected or you are not allowed to change ordering of any selected records');			
		}				
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->viewList, false), $msg);
	}

	/**
	 * Cancel an acction, redirect to view list
	 */
	
	public function cancel()
	{
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->viewList, false));
	}
	/**
	 * Method to change ordering of an record in the list
	 */
	public function reorder()
	{
		$task = $this->getTask();
		$cid = $this->input->get('cid', array(), 'array');
		if (count($cid) && $this->allowEditState($cid[0]))
		{
			try 
			{
				$inc = ($task == 'orderup' ? -1 : 1);
				$model = $this->getModel($this->name, array('fallback_class' => 'RADModelItem'));
				$model->move($inc);
				$msg = JText::_($this->languagePrefix . '_ORDERING_UPDATED');
			}
			catch (Exception $e)
			{
				$this->app->enqueueMessage($e->getMessage(), 'error');
				$msg = null;
			}			
		}
		else
		{
			$msg = JText::_('No record selected or you are not allowed to change ordering of the record');
		}
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->viewList, false), $msg);
	}

	/**
	 * Remove selected records from database
	 */
	public function remove()
	{
		$cid = $this->input->get('cid', array(), 'array');
		JArrayHelper::toInteger($cid);
		for ($i = 0, $n = count($cid); $i < $n; $i++)
		{
			if (!$this->allowDelete($cid[$i]))
			{
				unset($cid[$i]);
			}
		}
		if (count($cid))
		{
			try 
			{
				$model = $this->getModel($this->name, array('fallback_class' => 'RADModelItem'));
				$model->delete($cid);
				$msg = JText::_($this->languagePrefix . '_' . strtoupper(RADInflector::pluralize($this->name)) . '_REMOVED');
			}
			catch (Exception $e)
			{
				$msg = null;
				$this->app->enqueueMessage($e->getMessage(), 'error');				
			}
		}
		else
		{
			$msg = JText::_('You are not allowed to delete any of the selected records');
		}
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->viewList, false), $msg);
	}

	/**
	 * Publish records
	 */
	public function publish()
	{
		$task = $this->getTask();
		$cid = $this->input->get('cid', array(), 'array');
		JArrayHelper::toInteger($cid);
		for ($i = 0, $n = count($cid); $i < $n; $i++)
		{
			if (!$this->allowEditState($cid[$i]))
			{
				unset($cid[$i]);
			}
		}
		if (count($cid))
		{
			try 
			{
				$model = $this->getModel($this->name, array('fallback_class' => 'RADModelItem'));
				if ($task == 'publish')
				{
					$published = 1;
					$msg = JText::_($this->languagePrefix . '_' . strtoupper(RADInflector::pluralize($this->name)) . '_PUBLISHED');
				}
				else
				{
					$published = 0;
					$msg = JText::_($this->languagePrefix . '_' . strtoupper(RADInflector::pluralize($this->name)) . '_UNPUBLISHED');
				}
				$model->publish($cid, $published);
			}			
			catch (Exception $e)
			{
				$msg = null;
				$this->app->enqueueMessage($e->getMessage(), 'error');
			}
		}
		else
		{
			$msg = JText::_('You are not allowed to change state any of the selected records');
		}
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->viewList, false), $msg);
	}

	/**
	 * Copy record
	 */
	public function copy()
	{
		if ($this->allowAdd())
		{
			$cid = $this->input->get('cid', array(), 'array');
			JArrayHelper::toInteger($cid);
			$id = $cid[0];
			$model = $this->getModel($this->name, array('fallback_class' => 'RADModelItem'));
			$newId = $model->copy($id);
			$msg = JText::_($this->languagePrefix . '_' . strtoupper($this->name) . '_COPIED');
			$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->viewItem . '&id=' . $newId, false), $msg);
		}
		else
		{
			JFactory::getApplication()->redirect('index.php', JText::_('You are not allowed to copy record'));
		}
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *	 
	 */
	protected function allowAdd($data = array())
	{
		$user = JFactory::getUser();
		return $user->authorise('core.create', $this->option);
	}

	/**
	 * Method to check if you can edit a new record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key; default is id.
	 *
	 * @return  boolean	 	
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		return JFactory::getUser()->authorise('core.edit', $this->option);
	}

	/**
	 * Method to check if you can save a new or existing record.
	 *
	 * Extended classes can override this if necessary.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean		
	 */
	protected function allowSave($data, $key = 'id')
	{
		$recordId = isset($data[$key]) ? $data[$key] : '0';
		
		if ($recordId)
		{
			return $this->allowEdit($data, $key);
		}
		else
		{
			return $this->allowAdd($data);
		}
	}

	/**
	 * Method to check whether the current user is allowed to delete a record
	 *
	 * @param   int  id  Record ID
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *	 
	 */
	protected function allowDelete($id)
	{
		$user = JFactory::getUser();
		return $user->authorise('core.delete', $this->option);
	}

	/**
	 * Method to check whether the current user can change status (publish, unpublish of a record)
	 *
	 * @param   int  $id  Id of the record
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *	 
	 */
	protected function allowEditState($id)
	{
		$user = JFactory::getUser();
		return $user->authorise('core.edit.state', $this->option);
	}
}
