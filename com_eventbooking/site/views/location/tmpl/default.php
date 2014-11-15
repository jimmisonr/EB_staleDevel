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

$greyBox = JUri::base().'components/com_eventbooking/assets/js/greybox/';
?>
<script type="text/javascript">
    var GB_ROOT_DIR = "<?php echo $greyBox ; ?>";
</script>
<script type="text/javascript" src="<?php echo $greyBox; ?>AJS.js"></script>
<script type="text/javascript" src="<?php echo $greyBox; ?>AJS_fx.js"></script>
<script type="text/javascript" src="<?php echo $greyBox; ?>gb_scripts.js"></script>
<link href="<?php echo $greyBox; ?>gb_styles.css" rel="stylesheet" type="text/css" />
<?php
$width = (int) $this->config->map_width ;
if (!$width)
{
    $width = 500 ;
}
$height = (int) $this->config->map_height ;
if (!$height)
{
    $height = 450 ;
}
$param = null ;
if ($this->config->use_https)
{
    $ssl = 1;
}
else
{
    $ssl = 0;
}

$getDirectionLink = 'http://maps.google.com/maps?f=d&daddr='.$this->location->lat.','.$this->location->long.'('.addslashes($this->location->address.', '.$this->location->city.', '.$this->location->state.', '.$this->location->zip.', '.$this->location->country).')' ;
JHtml::_('behavior.modal', 'a.eb-modal');
?>
<h1 class="eb-page-heading"><?php echo JText::sprintf('EB_EVENTS_FROM_LOCATION', $this->location->name); ?><a href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=map&location_id='.$this->location->id.'&tmpl=component&format=html'); ?>" rel="gb_page_center[<?php echo $width; ?>, <?php echo $height; ?>]" title="<?php echo $this->location->name ; ?>" class="location_link view_map_link"><?php echo JText::_('EB_VIEW_MAP'); ?></a><a class="view_map_link" href="<?php echo $getDirectionLink ; ?>" target="_blank"><?php echo JText::_('EB_GET_DIRECTION'); ?></a></h1>
<form method="post" name="adminForm" id="adminForm" action="<?php echo JRoute::_('index.php?option=com_eventbooking&view=location&location_id='.$this->location->id.'&Itemid='.$this->Itemid) ; ?>">
    <?php
    if (count($this->items))
    {
        echo EventbookingHelperHtml::loadCommonLayout('common/events_default.php', array('events' => $this->items, 'config' => $this->config, 'Itemid' => $this->Itemid, 'nullDate' => $this->nullDate , 'param' => $param, 'ssl' => $ssl, 'width' => $width, 'height' => $height , 'viewLevels' => $this->viewLevels));
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
</form>