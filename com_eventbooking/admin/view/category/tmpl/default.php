<?php
/**
 * @version            2.5.0
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

 // no direct access
defined( '_JEXEC' ) or die;

JHtml::_('bootstrap.tooltip');
$document = JFactory::getDocument();
$document->addStyleDeclaration(".hasTip{display:block !important}");
JHtml::_('formbehavior.chosen', 'select');

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
<form action="index.php?option=com_eventbooking&view=category" method="post" name="adminForm" id="adminForm" class="form form-horizontal">
<?php
if ($translatable)
{
	echo JHtml::_('bootstrap.startTabSet', 'category', array('active' => 'general-page'));
	echo JHtml::_('bootstrap.addTab', 'category', 'general-page', JText::_('EB_GENERAL', true));
}
?>
	<div class="control-group">
		<label class="control-label">
			<?php echo  JText::_('EB_NAME'); ?>
		</label>
		<div class="controls">
			<input class="text_area" type="text" name="name" id="name" size="40" maxlength="250" value="<?php echo $this->item->name;?>" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">
			<?php echo  JText::_('EB_ALIAS'); ?>
		</label>
		<div class="controls">
			<input class="text_area" type="text" name="alias" id="alias" maxlength="250" value="<?php echo $this->item->alias;?>" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">
			<?php echo  JText::_('EB_PARENT'); ?>
		</label>
		<div class="controls">
			<?php echo $this->lists['parent']; ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">
			<?php echo  JText::_('EB_LAYOUT'); ?>
		</label>
		<div class="controls">
			<?php echo $this->lists['layout']; ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">
			<?php echo  JText::_('EB_ACCESS_LEVEL'); ?>
		</label>
		<div class="controls">
			<?php echo $this->lists['access']; ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('show_on_submit_event_form', JText::_('EB_SHOW_ON_SUBMIT_EVENT_FORM'), JText::_('EB_SHOW_ON_SUBMIT_EVENT_FORM_EXPLAIN')); ?>
		</label>
		<div class="controls">
			<?php echo EventbookingHelperHtml::getBooleanInput('show_on_submit_event_form', $this->item->show_on_submit_event_form); ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">
			<?php echo JText::_('EB_PUBLISHED'); ?>
		</label>
		<div class="controls">
			<?php echo $this->lists['published']; ?>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">
			<?php echo EventbookingHelperHtml::getFieldLabel('color_code', JText::_('EB_COLOR'), JText::_('EB_COLOR_EXPLAIN')); ?>
		</label>
		<div class="controls">
			<input type="text" name="color_code" class="inputbox color {required:false}" value="<?php echo $this->item->color_code; ?>" size="10" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">
			<?php echo  JText::_('EB_META_KEYWORDS'); ?>
		</label>
		<div class="controls">
			<textarea rows="5" cols="30" class="input-lage" name="meta_keywords"><?php echo $this->item->meta_keywords; ?></textarea>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">
			<?php echo  JText::_('EB_META_DESCRIPTION'); ?>
		</label>
		<div class="controls">
			<textarea rows="5" cols="30" class="input-lage" name="meta_description"><?php echo $this->item->meta_description; ?></textarea>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">
			<?php echo JText::_('EB_DESCRIPTION'); ?>
		</label>
		<div class="controls">
			<?php echo $editor->display( 'description',  $this->item->description , '100%', '250', '75', '10' ) ; ?>
		</div>
	</div>
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
			<div class="control-group">
				<label class="control-label">
					<?php echo JText::_('EB_NAME'); ?>
				</label>
				<div class="controls">
					<input class="input-xlarge" type="text" name="name_<?php echo $sef; ?>" id="name_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'name_' . $sef}; ?>"/>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">
					<?php echo JText::_('EB_ALIAS'); ?>
				</label>
				<div class="controls">
					<input class="input-xlarge" type="text" name="alias_<?php echo $sef; ?>" id="alias_<?php echo $sef; ?>" size="" maxlength="250" value="<?php echo $this->item->{'alias_' . $sef}; ?>"/>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label">
					<?php echo JText::_('EB_DESCRIPTION'); ?>
				</label>
				<div class="controls">
					<?php echo $editor->display('description_' . $sef, $this->item->{'description_' . $sef}, '100%', '250', '75', '10'); ?>
				</div>
			</div>
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