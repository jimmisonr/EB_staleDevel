<?php 
/**
 * @version		1.5.0
 * @package		Joomla
 * @subpackage	Event Booking
 * @author  Tuan Pham Ngoc
 * @copyright	Copyright (C) 2010 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die ;
if ($config->use_https)
	$checkoutUrl = JRoute::_('index.php?option=com_eventbooking&task=view_checkout&Itemid='.$Itemid, false, 1) ;
else
	$checkoutUrl = JRoute::_('index.php?option=com_eventbooking&task=view_checkout&Itemid='.$Itemid, false) ;
$tabs = array('sectiontableentry1' , 'sectiontableentry2') ;			
?>	
<table width="100%">
	<?php
		if (count($rows)) {
			$k = 0 ;
			for ($i = 0 , $n = count($rows) ; $i < $n ; $i++) {
				$tab = $tabs[$k] ;
				$k = 1 - $k ;
				$row = $rows[$i] ;
				//$link = JRoute::_( 'index.php?option=com_eventbooking&task=view_event&event_id='.$row->id.'&Itemid='.$Itemid);
				$link = EventbookingHelperRoute::getEventRoute($row->id,0,$Itemid);
			?>
				<tr class="<?php echo $tab ?>">
					<td>
						<a href="<?php echo $link; ?>" class="eb_event_link"><div class="eb_event_title"><?php echo $row->title ; ?></div></a>
						<br />
						<span class="qty_title"><?php echo JText::_('EB_QTY'); ?></span>: <span class="qty"><?php echo $row->quantity ;?></span>
						<?php
							if ($row->rate > 0) {
							?>
								<br />
								<span class="eb_rate"><?php echo JText::_('EB_RATE'); ?></span>: <span class="eb_rate"><?php echo EventBookingHelper::formatCurrency($row->rate, $config) ;?></span>	
							<?php	
							}
						?>								
					</td>
				</tr>						
			<?php	
			}
				$tab = $tabs[$k] ;									
			?>
				<tr class="<?php echo $tab; ?>">
					<td style="text-align: center;">
						<input type="button" onclick="checkOut();" value="<?php echo JText::_('EB_CHECKOUT'); ?>" />
					</td>
				</tr>
			<?php						
		} else {
		?>
			<tr>
				<td>
					<?php echo JText::_('EB_CART_EMPTY'); ?>
				</td>
			</tr>										
		<?php	
		}				
	?>
</table>	
<script type="text/javascript">
	function checkOut() {
		location.href = '<?php echo $checkoutUrl; ?>';
	}
</script>