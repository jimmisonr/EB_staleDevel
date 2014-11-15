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
$editor = JFactory::getEditor(); 	
?>
<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton) {
		var form = document.adminForm;
		if (pressbutton == 'cancel') {
			Joomla.submitform( pressbutton );
			return;				
		} else {
			<?php echo $editor->save('description'); ?>
			Joomla.submitform( pressbutton );
		}
	}
</script>
<form action="index.php?option=com_eventbooking&view=category" method="post" name="adminForm" id="adminForm">
<div class="row-fluid">	
	<table class="admintable adminform">
		<tr>
			<td width="100" class="key">
				<?php echo  JText::_('EB_NAME'); ?>
			</td>
			<td>
				<input class="text_area" type="text" name="name" id="name" size="40" maxlength="250" value="<?php echo $this->item->name;?>" />
			</td>
		</tr>	
		<tr>
			<td width="100" class="key">
				<?php echo  JText::_('EB_ALIAS'); ?>
			</td>
			<td>
				<input class="text_area" type="text" name="alias" id="alias" maxlength="250" value="<?php echo $this->item->alias;?>" />
			</td>
		</tr>			
		<tr>
			<td class="key">
				<?php echo  JText::_('EB_PARENT'); ?>
			</td>
			<td>
				<?php echo $this->lists['parent']; ?>	
			</td>				
		</tr>
		<tr>
			<td class="key">
				<?php echo  JText::_('EB_LAYOUT'); ?>
			</td>
			<td>
				<?php echo $this->lists['layout']; ?>	
			</td>				
		</tr>	
		<tr>
			<td class="key">
				<?php echo  JText::_('EB_ACCESS_LEVEL'); ?>
			</td>
			<td>
				<?php echo $this->lists['access']; ?>	
			</td>				
		</tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_LANGUAGE'); ?>
			</td>
			<td>
				<?php echo $this->lists['language'] ; ?>
			</td>
		</tr>             	
		<tr>
			<td class="key">
				<?php echo JText::_('EB_COLOR'); ?>
			</td>
			<td>
				<input type="text" name="color_code" class="inputbox color {required:false}" value="<?php echo $this->item->color_code; ?>" size="10" />						
				<?php echo JText::_('EB_COLOR_EXPLAIN'); ?> 
			</td>
		</tr>
        <tr>
            <td width="100" class="key">
                <?php echo  JText::_('EB_META_KEYWORDS'); ?>
            </td>
            <td>
                <textarea rows="5" cols="30" class="input-lage" name="meta_keywords"><?php echo $this->item->meta_keywords; ?></textarea>
            </td>
        </tr>
        <tr>
            <td width="100" class="key">
                <?php echo  JText::_('EB_META_DESCRIPTION'); ?>
            </td>
            <td>
                <textarea rows="5" cols="30" class="input-lage" name="meta_description"><?php echo $this->item->meta_description; ?></textarea>
            </td>
        </tr>
		<tr>
			<td class="key">
				<?php echo JText::_('EB_DESCRIPTION'); ?>
			</td>
			<td>
				<?php echo $editor->display( 'description',  $this->item->description , '100%', '250', '75', '10' ) ; ?>
			</td>
		</tr>				
		<tr>
			<td class="key">
				<?php echo JText::_('EB_PUBLISHED'); ?>
			</td>
			<td>
				<?php echo $this->lists['published']; ?>
			</td>
		</tr>
	</table>										
</div>		
<div class="clearfix"></div>	
	<?php echo JHtml::_( 'form.token' ); ?>
	<input type="hidden" name="cid[]" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="task" value="" />
</form>