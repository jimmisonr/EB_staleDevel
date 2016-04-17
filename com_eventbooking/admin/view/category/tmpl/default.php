<?php
/**
 * @version            2.4.2
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

 // no direct access
defined( '_JEXEC' ) or die ;

$editor = JEditor::getInstance(JFactory::getConfig()->get('editor'));
$translatable = JLanguageMultilang::isEnabled() && count($this->languages);
?>
<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton)
	{
		var form = document.adminForm;
		if (pressbutton == 'cancel')
		{
			Joomla.submitform( pressbutton );
			return;
		}
		else
		{
			<?php echo $editor->save('description'); ?>
			Joomla.submitform( pressbutton );
		}
	}
</script>
<div class="row-fluid">
<form action="index.php?option=com_eventbooking&view=category" method="post" name="adminForm" id="adminForm">
<?php
if ($translatable)
{
	echo JHtml::_('bootstrap.startTabSet', 'category', array('active' => 'general-page'));
	echo JHtml::_('bootstrap.addTab', 'category', 'general-page', JText::_('EB_GENERAL', true));
}
?>
	<table class="admintable adminform">
		<tr>
			<td width="250" class="key">
				<?php echo  JText::_('EB_NAME'); ?>
			</td>
			<td>
				<input class="text_area" type="text" name="name" id="name" size="40" maxlength="250" value="<?php echo $this->item->name;?>" />
			</td>
		</tr>
		<tr>
			<td class="key">
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
				<?php echo JText::_('EB_COLOR'); ?>
			</td>
			<td>
				<input type="text" name="color_code" class="inputbox color {required:false}" value="<?php echo $this->item->color_code; ?>" size="10" />
				<?php echo JText::_('EB_COLOR_EXPLAIN'); ?>
			</td>
		</tr>
		<tr>
			<td class="key">
				<?php echo  JText::_('EB_META_KEYWORDS'); ?>
			</td>
			<td>
				<textarea rows="5" cols="30" class="input-lage" name="meta_keywords"><?php echo $this->item->meta_keywords; ?></textarea>
			</td>
		</tr>
		<tr>
			<td class="key">
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
				<?php echo JText::_('EB_SHOW_ON_SUBMIT_EVENT_FORM'); ?>
				<p class="description" style="font-weight: normal; font-style: italic;"><?php echo JText::_('EB_SHOW_ON_SUBMIT_EVENT_FORM_EXPLAIN'); ?></p>
			</td>
			<td>
				<?php echo $this->lists['show_on_submit_event_form']; ?>
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
<?php
if ($translatable)
{
	echo JHtml::_('bootstrap.endTab');
	echo JHtml::_('bootstrap.addTab', 'category', 'translation-page', JText::_('EB_TRANSLATION', true));
	echo JHtml::_('bootstrap.startTabSet', 'category-translation', array('active' => 'translation-page-'.$this->languages[0]->sef));
	foreach ($this->languages as $language)
	{
		$sef = $language->sef;
		echo JHtml::_('bootstrap.addTab', 'category-translation', 'translation-page-' . $sef, $language->title . ' <img src="' . JUri::root() . 'media/com_eventbooking/flags/' . $sef . '.png" />');
		?>
		<table class="admintable adminform" style="width: 100%;">
			<tr>
				<td class="key" width="250">
					<?php echo JText::_('EB_NAME'); ?>
				</td>
				<td>
					<input class="input-xlarge" type="text" name="name_<?php echo $sef; ?>" id="name_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'name_' . $sef}; ?>"/>
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo JText::_('EB_ALIAS'); ?>
				</td>
				<td>
					<input class="input-xlarge" type="text" name="alias_<?php echo $sef; ?>" id="alias_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'alias_' . $sef}; ?>"/>
				</td>
			</tr>
			<tr>
				<td class="key">
					<?php echo JText::_('EB_DESCRIPTION'); ?>
				</td>
				<td>
					<?php echo $editor->display('description_' . $sef, $this->item->{'description_' . $sef}, '100%', '250', '75', '10'); ?>
				</td>
			</tr>
		</table>
		<?php
		echo JHtml::_('bootstrap.endTab');
	}
	echo JHtml::_('bootstrap.endTabSet');
	echo JHtml::_('bootstrap.endTab');
	echo JHtml::_('bootstrap.endTabSet');
}
?>
<div class="clearfix"></div>
<?php echo JHtml::_( 'form.token' ); ?>
<input type="hidden" name="id" value="<?php echo $this->item->id; ?>" />
<input type="hidden" name="task" value="" />
</form>
</div>