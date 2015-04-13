<?php
/**
 * @version		1.6.1
 * @package		Joomla
 * @subpackage	Event Booking
 * @author  Tuan Pham Ngoc
 * @copyright	Copyright (C) 2010 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */
defined('_JEXEC') or die ( 'Restricted access');
if (count($rows))
{
?>
	<ul class="menu">
		<?php				
			foreach ($rows  as $row)
			{
			?>
				<li>
					<a href="<?php echo JRoute::_(EventbookingHelperRoute::getCategoryRoute($row->id, $itemId)); ?>"><?php echo $row->name; ?></a>
				</li>
			<?php	
			}
		?>			
	</ul>
<?php
}
?>					

