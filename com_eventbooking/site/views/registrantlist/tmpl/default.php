<?php
/**
 * @version        	1.7.0
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined( '_JEXEC' ) or die ;
?>
<div id="eb-registrants-list-page" class="eb-container row-fluid">
<h1 class="eb_title"><?php echo JText::_('EB_REGISTRANT_LIST'); ?></h1>	
<?php    
if (count($this->items)) 
{
?>		
	<table class="table table-striped table-bordered table-condensed">
	<thead>
		<tr>
			<th width="5" class="hidden-phone">
				<?php echo JText::_( 'NUM' ); ?>
			</th>				
			<th>
				<?php echo JText::_('EB_FIRST_NAME'); ?>
			</th>
			<th>						
				<?php echo JText::_('EB_LAST_NAME'); ?>
			</th>								
			<th class="hidden-phone">
				<?php echo JText::_('EB_REGISTRANTS'); ?>
			</th>																
			<th class="hidden-phone">
				<?php echo JText::_('EB_REGISTRATION_DATE'); ?>
			</th>
			<?php
				if ($this->displayCustomField)
				{
					foreach($this->fields as $fieldId)
					{
					?>
						<th class="hidden-phone">
							<?php echo $this->fieldTitles[$fieldId] ; ?>
						</th>
					<?php	
					}	
				}
			?>
		</tr>
	</thead>		
	<tbody>
	<?php
	$k = 0;	
	for ($i=0, $n=count( $this->items ); $i < $n; $i++)
	{
		$row = &$this->items[$i];											
		?>
		<tr>
			<td class="hidden-phone">
				<?php echo $i+1 ; ?>
			</td>					
			<td>					
					<?php echo $row->first_name ?>					
			</td>			
			<td>
				<?php echo $row->last_name ; ?>
			</td>
			<td class="hidden-phone">
				<?php echo $row->number_registrants ; ?>
			</td>				
			<td class="hidden-phone">
				<?php echo JHtml::_('date', $row->register_date, $this->config->date_format) ; ?>
			</td>	
			<?php
				if ($this->displayCustomField)
				{
					foreach($this->fields as $fieldId)
					{
						if (isset($this->fieldValues[$row->id][$fieldId]))
						{
							$fieldValue = $this->fieldValues[$row->id][$fieldId];
						}
						else
						{
							$fieldValue = '';
						}
					?>
						<td class="hidden-phone">
							<?php echo $fieldValue ?>
						</td>
					<?php	
					}	
				}
			?>							
		</tr>
		<?php
		$k = 1 - $k;
	}
	?>
	</tbody>
</table>		
<?php	
} 
else 
{
?>
	<div class="eb-message"><?php echo JText::_('EB_NO_REGISTRATION_RECORDS');?></div>
<?php	
}
?>
</div>