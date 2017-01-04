<?php
/**
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2017 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die ;
if ($this->config->use_https)
{
	$ssl = 1 ;
}
else
{
	$ssl = 0 ;
}
EventbookingHelperJquery::colorbox('a.eb-modal');
?>
<div id="eb-upcoming-events-page-columns" class="eb-container">
<h1 class="eb-page-heading"><?php echo $this->params->get('page_heading') ? $this->params->get('page_heading') : JText::_('EB_UPCOMING_EVENTS'); ?></h1>
<?php
	$message = empty($this->category->description) ? $this->introText : $this->category->description;
	if (EventbookingHelper::isValidMessage($message))
	{
	?>
		<div class="eb-description"><?php echo $message; ?></div>
	<?php
	}
?>
<form method="post" name="adminForm" id="adminForm" action="index.php">
	<?php
		if (count($this->items))
		{
			echo EventbookingHelperHtml::loadCommonLayout('common/tmpl/events_columns.php', array('events' => $this->items, 'config' => $this->config, 'Itemid' => $this->Itemid, 'nullDate' => $this->nullDate , 'ssl' => $ssl, 'viewLevels' => $this->viewLevels, 'category' => $this->category, 'bootstrapHelper' => $this->bootstrapHelper));
		}
		else
		{
		?>
			<p class="text-info"><?php echo JText::_('EB_NO_UPCOMING_EVENTS') ?></p>
		<?php
		}
		if ($this->pagination->total > $this->pagination->limit)
		{
		?>
			<div class="pagination">
				<?php echo $this->pagination->getPagesLinks(); ?>
			</div>
		<?php
		}
	?>
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid ; ?>" />
	<input type="hidden" name="option" value="com_eventbooking" />
	<input type="hidden" name="id" value="0" />
	<input type="hidden" name="task" value="" />
	<script type="text/javascript">
		function cancelRegistration(registrantId) {
			var form = document.adminForm ;
			if (confirm("<?php echo JText::_('EB_CANCEL_REGISTRATION_CONFIRM'); ?>")) {
				form.task.value = 'registrant.cancel' ;
				form.id.value = registrantId ;
				form.submit() ;
			}
		}
	</script>
</form>
</div>