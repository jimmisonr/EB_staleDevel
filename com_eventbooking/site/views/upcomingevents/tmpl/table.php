<?php
/**
 * @version        	1.6.8
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2014 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die ;
JHtml::_('behavior.modal', 'a.eb-modal');
?>
<div id="eb-upcoming-events-page-default" class="eb-container row-fluid">
	<h1 class="eb-page-heading"><?php echo JText::_('EB_UPCOMING_EVENTS') ; ?></h1>	
<?php	
if ($this->config->use_https)
{
	$ssl = 1;
}
else
{
	$ssl = 0;
} 
?> 
<form method="post" name="adminForm" id="adminForm" action="index.php">
	<?php 
		if (count($this->items))
		{
			echo EventbookingHelperHtml::loadCommonLayout('common/events_table.php', array('items' => $this->items, 'config' => $this->config, 'Itemid' => $this->Itemid, 'nullDate' => $this->nullDate , 'ssl' => $ssl , 'viewLevels' => $this->viewLevels, 'categoryId' => @$this->category->id));
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
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />	
	<input type="hidden" name="option" value="com_eventbooking" />		
</form>
</div>