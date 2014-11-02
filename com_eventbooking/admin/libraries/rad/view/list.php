<?php
/**
 * View List class, used to render list of records from back-end of your component
 * 
 * @package     Joomla.RAD
 * @subpackage  ViewList
 * @author	Ossolution Team
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
defined('_JEXEC') or die();

class RADViewList extends RADViewHtml
{

	/**
	 * The model state 
	 * @var RADModelState
	 */
	protected $state;

	/**
	 * List of records which will be displayed
	 * 
	 * @var array
	 */
	protected $items;

	/**
	 * The pagination object
	 * 
	 * @var JPagination
	 */
	protected $pagination;

	/**
	 * The array which keeps list of "list" options which will used to display as the filter on the list
	 *
	 * @var Array
	 */
	protected $lists;

	public function __construct($config = array())
	{
		parent::__construct($config);
		
		$this->state = $this->model->getState();
		$this->items = $this->model->getData();
		$this->pagination = $this->model->getPagination();
		//State filter
		$this->lists['filter_state'] = JHtml::_('grid.state', $this->state->filter_state);
		$this->lists['filter_access'] = JHtml::_('access.level', 'filter_access', $this->state->filter_access, 'class="inputbox" onchange="submit();"', false);
		$this->lists['filter_language'] = JHtml::_('select.genericlist', JHtml::_('contentlanguage.existing', true, true), 'filter_language', 
			' class="inputbox" onchange="submit();" ', 'value', 'text', $this->state->filter_language);

        $layout = JRequest::getVar('layout', '');
        if ($layout != 'modal')
        {
            if (version_compare(JVERSION, '3.0', 'ge'))
            {
                EventbookingHelperHtml::renderSubmenu($this->name);
            }
            else
            {
                $component = substr($this->option, 4);
                $helperClass = ucfirst($component) . 'Helper';
                if (is_callable($helperClass . '::addSubmenus'))
                {
                    call_user_func(array($helperClass, 'addSubmenus'), $this->name);
                }
                else
                {
                    call_user_func(array('RADHelper', 'addSubmenus'), $this->name);
                }
            }
            $this->addToolbar();
        }
	}

	/**
	 * Add the page title and toolbar.
	 *	 
	 */
	protected function addToolbar()
	{
		$state = $this->state;
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
		JToolBarHelper::title(JText::_(strtoupper($this->languagePrefix . '_' . RADInflector::singularize($this->name) . '_MANAGEMENT')), 
			'generic.png');
		if ($canDo->get('core.create'))
		{
			JToolBarHelper::addNew('add', 'JTOOLBAR_NEW');
		}
		
		if ($canDo->get('core.edit') && isset($this->items[0]))
		{
			JToolBarHelper::editList('edit', 'JTOOLBAR_EDIT');
		}
		if ($canDo->get('core.create') && isset($this->items[0]))
		{
			JToolBarHelper::custom('copy', 'copy.png', 'copy_f2.png', 'Copy', true);
		}
		if ($canDo->get('core.delete') && isset($this->items[0]))
		{
			JToolBarHelper::deleteList(JText::_($this->languagePrefix.'_DELETE_CONFIRM') , 'remove');
		}
		if ($canDo->get('core.edit.state'))
		{
			if (isset($this->items[0]->published))
			{
				JToolBarHelper::divider();
				JToolBarHelper::custom('publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			}
		}
		
		if ($canDo->get('core.delte'))
		{
			JToolBarHelper::deleteList('', 'delete', 'JTOOLBAR_DELETE');
		}
		
		if ($canDo->get('core.admin'))
		{
			JToolBarHelper::preferences($this->option);
		}
	}
}
