<?php
/**
 * Form view class, used to render form allow users to add/edit a record
 * 
 * @package     Joomla.RAD
 * @subpackage  ViewForm
 * @author	Ossolution Team
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die();

class RADViewItem extends RADViewHtml
{

	/**
	 * The model state
	 * 
	 * @var RADModelState
	 */
	protected $state;

	/**
	 * The record which is being added/edited
	 * 
	 * @var Object
	 */
	protected $item;

	/**
	 * The array which keeps list of "list" options which will be displayed on the form
	 * 
	 * @var Array
	 */
	protected $lists;

	/**
	 * Constructor function
	 * 
	 * @param array $config
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);
		
		$this->state = $this->model->getState();
		$this->item = $this->model->getData();
		//Build common HTML select lists which will be used on the form
		if (property_exists($this->item, 'published'))
		{
			$this->lists['published'] = JHtml::_('select.booleanlist', 'published', ' class="inputbox" ', $this->item->published);
		}
		if (property_exists($this->item, 'access'))
		{
			$this->lists['access'] = JHtml::_('access.level', 'access', $this->item->access, 'class="inputbox"', false);
		}
		
		if (property_exists($this->item, 'language'))
		{
			$this->lists['language'] = JHtml::_('select.genericlist', JHtml::_('contentlanguage.existing', true, true), 'language', 
				' class="inputbox" ', 'value', 'text', $this->item->language);
		}

		$this->languages = EventbookingHelper::getLanguages();
		
		$this->addToolbar();
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar()
	{
		JFactory::getApplication()->input->set('hidemainmenu', true);
		
		$user = JFactory::getUser();
		$isNew = ($this->item->id == 0);
		
		if (isset($this->item->checked_out))
		{
			$checkedOut = !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		}
		else
		{
			$checkedOut = false;
		}
		
		$component = substr($this->option, 4);
		$helperClass = ucfirst($component) . 'Helper';
		if (is_callable($helperClass . '::getActions'))
		{
			$canDo = call_user_func(array($helperClass, 'getActions'), $this->option);
		}
		else
		{
			$canDo = call_user_func(array('RADHelper', 'getActions'), $this->option);
		}
		if ($this->item->id)
		{
			$toolbarTitle = $this->languagePrefix.'_'.$this->name.'_EDIT'; 
		}
		else 
		{
			$toolbarTitle = $this->languagePrefix.'_'.$this->name.'_NEW';
		}
		JToolBarHelper::title(JText::_(strtoupper($toolbarTitle)));
		// If not checked out, can save the item.
		if (!$checkedOut && ($canDo->get('core.edit') || ($canDo->get('core.create'))))
		{
			
			JToolBarHelper::apply('apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('save', 'JTOOLBAR_SAVE');
		}
		
		if (!$checkedOut && ($canDo->get('core.create')))
		{
			JToolBarHelper::custom('save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}
		
		if (empty($this->item->id))
		{
			JToolBarHelper::cancel('cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			JToolBarHelper::cancel('cancel', 'JTOOLBAR_CLOSE');
		}
	}
}
