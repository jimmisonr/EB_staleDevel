<?php
/**
 * @version            2.7.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die;

class plgEventbookingJoomlaarticles extends JPlugin
{
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		JFactory::getLanguage()->load('plg_eventbooking_joomlaarticles', JPATH_ADMINISTRATOR);
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_eventbooking/table');
	}
	
	/**
	 * Render settings form
	 *
	 * @param $row
	 *
	 * @return array
	 */
	public function onEditEvent($row)
	{
		ob_start();
		$this->drawSettingForm($row);
		$form = ob_get_contents();
		ob_end_clean();

		return array('title' => JText::_('PLG_EVENTBOOKING_JOOMLA_ARTICLES_SETTINGS'),
		             'form'  => $form
		);
	}

	/**
	 * Store setting into database
	 *
	 * @param Event   $row
	 * @param Boolean $isNew true if create new plan, false if edit
	 */
	public function onAfterSaveEvent($row, $data, $isNew)
	{
		$row->articles_id = $data['article_id'];
		if($data['article_id'] > 0)
		{
			$db     = JFactory::getDbo();
			$query  = $db->getQuery(true);
			$query->select('a.introtext, a.fulltext')
				->from('#__content AS a') 
				->where('id=' . $data['article_id']);
			$db->setQuery($query);
			$art = $db->loadRow();
			$row->short_description = $art[0];
			$row->description = $art[0].' '.$art[1];
		}
		$row->store();
	}


	/**
	 * Display form allows users to change setting for this subscription plan
	 *
	 * @param object $row
	 *
	 */
	private function drawSettingForm($row)
	{
		?>
		<div class="control-group">
			<label class="control-label">
				<?php echo JText::_('PLG_EVENTBOOKING_JOOMLA_ARTICLES_SELECT'); ?>
			</label>
			<div class="controls">
				<?php echo EventbookingHelper::getArticleInput($row->articles_id); ?>
			</div>
		</div>
		<?php
	}
}	