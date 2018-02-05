<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2018 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

defined('_JEXEC') or die;
JHtml::_('bootstrap.tooltip');
$document = JFactory::getDocument();
$document->addStyleDeclaration(".hasTip{display:block !important}");

$translatable = JLanguageMultilang::isEnabled() && count($this->languages);
$editor = JEditor::getInstance(JFactory::getConfig()->get('editor'));
$fields = EventbookingHelperHtml::getAvailableMessagesTags();
JHtml::_('behavior.tabstate');
?>
<form action="index.php?option=com_eventbooking&view=message" method="post" name="adminForm" id="adminForm" class="form-horizontal eb-configuration">
	<?php echo JHtml::_('bootstrap.startTabSet', 'message', array('active' => 'registration-form-messages-page')); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'message', 'registration-form-messages-page', JText::_('EB_REGISTRATION_FORM_MESSAGES', true)); ?>
	    <?php echo $this->loadTemplate('registration_form', ['editor' => $editor, 'fields' => $fields]); ?>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'message', 'registration-email-messages-page', JText::_('EB_REGISTRATION_EMAIL_MESSAGES', true)); ?>
        <?php echo $this->loadTemplate('registration_email', ['editor' => $editor, 'fields' => $fields]); ?>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'message', 'submit-event-email-messages-page', JText::_('EB_SUBMIT_EVENT_EMAIL_MESSAGES', true)); ?>
	    <?php echo $this->loadTemplate('submit_event_email', ['editor' => $editor, 'fields' => $fields]); ?>
	<?php echo JHtml::_('bootstrap.endTab');?>
	<?php echo JHtml::_('bootstrap.addTab', 'message', 'invitation-messages-page', JText::_('EB_INVITATION_MESSAGES', true)); ?>
	    <?php echo $this->loadTemplate('invitation_message', ['editor' => $editor, 'fields' => $fields]); ?>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'message', 'waitinglist-messages-page', JText::_('EB_WAITINGLIST_MESSAGES', true)); ?>
	<?php echo $this->loadTemplate('waitinglist_message', ['editor' => $editor, 'fields' => $fields]); ?>
	<?php

	echo JHtml::_('bootstrap.endTab');
	echo JHtml::_('bootstrap.addTab', 'message', 'pay-deposit-form-messages-page', JText::_('EB_DEPOSIT_PAYMENT_MESSAGES', true));
	echo $this->loadTemplate('remainder_payment', ['editor' => $editor, 'fields' => $fields]);
	echo JHtml::_('bootstrap.endTab');

	// Add support for custom settings layout
	if (file_exists(__DIR__ . '/default_custom_settings.php'))
	{
		echo JHtml::_('bootstrap.addTab', 'message', 'custom-settings-page', JText::_('EB_MESSAGE_CUSTOM_SETTINGS', true));
		echo $this->loadTemplate('custom_settings', array('editor' => $editor, 'fields' => $fields));
		echo JHtml::_('bootstrap.endTab');
	}

	if ($translatable)
	{
		echo JHtml::_('bootstrap.addTab', 'message', 'translation-page', JText::_('EB_TRANSLATION', true));
		echo $this->loadTemplate('translation', array('editor' => $editor, 'fields' => $fields));
		echo JHtml::_('bootstrap.endTab');
	}
	echo JHtml::_('bootstrap.endTabSet');
	?>
	<div class="clearfix"></div>
	<input type="hidden" name="task" value="" />
</form>