<?php
/**
 * @version        	2.0.0
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
class EventbookingViewDashboardHtml extends RADViewHtml
{
	public $hasModel = false;

	public function display()
	{								
		$this->latestRegistrants = RADModel::getInstance('Registrants', 'EventbookingModel', array('table_prefix' => '#__eb_'))
			->limitstart(0)
			->limit(5)
			->filter_order('tbl.id')
			->filter_order_Dir('DESC')
			->getData();
							
		parent::display();
	}
	
	/**
	 * 
	 * Function to create the buttons view.
	 * @param string $link targeturl
	 * @param string $image path to image
	 * @param string $text image description
	 */
	function quickIconButton($link, $image, $text, $id = null)
	{
		$language = JFactory::getLanguage();
		?>
		<div style="float:<?php echo ($language->isRTL()) ? 'right' : 'left'; ?>;" <?php if ($id) echo 'id="'.$id.'"'; ?>>
			<div class="icon">
				<a href="<?php echo $link; ?>" title="<?php echo $text; ?>">
					<?php echo JHtml::_('image', 'administrator/components/com_eventbooking/assets/icons/' . $image, $text); ?>
					<span><?php echo $text; ?></span>
				</a>
			</div>
		</div>
		<?php
	}
}