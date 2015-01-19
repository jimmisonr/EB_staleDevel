<?php	/** * @version		1.6.6 * @package		Joomla * @subpackage	Event Booking * @author  Tuan Pham Ngoc * @copyright	Copyright (C) 2010 - 2015 Ossolution Team * @license		GNU/GPL, see LICENSE.php */defined('_JEXEC') or die ();	JHtml::_('script', JUri::root().'components/com_eventbooking/assets/js/noconflict.js', false, false);	
if ($showLocation) {	$width = (int) $config->map_width ;	if (!$width)	{		$width = 800 ;	}	$height = (int) $config->map_height ;	if (!$height)	{		$height = 600 ;	}	EventbookingHelperJquery::colorbox('eb-colorbox-map', $width.'px', $height.'px', 'true', 'false');
}if (count($rows)) {?>	<table class="eb_event_list" width="100%">		<?php			foreach ($rows as  $row) 			{			?>					<tr>					<td class="eb_event">						<a href="<?php echo JRoute::_(EventbookingHelperRoute::getEventRoute($row->id, 0, $itemId)); ?>" class="eb_event_link"><?php echo $row->title ; ?></a>						<br />						<span class="event_date"><?php echo JHTML::_('date', $row->event_date, $config->event_date_format, null); ?></span>						<?php							if ($showCategory) 							{							?>								<br />										<span><?php echo $row->number_categories > 1 ? JText::_('EB_CATEGORIES') : JText::_('EB_CATEGORY'); ?>:&nbsp;&nbsp;<?php echo $row->categories ; ?></span>							<?php								}							if ($showLocation && strlen($row->location_name)) 							{							?>								<br />										<a href="<?php echo JRoute::_('index.php?option=com_eventbooking&view=map&location_id='.$row->location_id.'&tmpl=component&format=html&Itemid='.$itemId); ?>" class="eb-colorbox-map"><?php echo $row->location_name ; ?></a>							<?php	 							}						?>																</td>				</tr>			<?php			}		?>	</table><?php	} else {?>	<div class="eb_empty"><?php echo JText::_('EB_NO_UPCOMING_EVENTS') ?></div><?php	}?>