<?php
/**
 * * @version        	2.0.0
 * @package        	Joomla
 * @subpackage		Event Booking
 * @author  		Tuan Pham Ngoc
 * @copyright    	Copyright (C) 2010 - 2015 Ossolution Team
 * @license        	GNU/GPL, see LICENSE.php
 */
// no direct access
defined('_JEXEC') or die();

class EventBookingViewInvite extends JViewLegacy
{

	function display($tpl = null)
	{
		$layout = $this->getLayout();
		if ($layout == 'complete')
		{
			$this->_displayInviteComplete($tpl);
		}
		else
		{
			$db = JFactory::getDbo();
			$user = JFactory::getUser();
			$config = EventbookingHelper::getConfig();
			$query = $db->getQuery(true);
			$message = EventbookingHelper::getMessages();
			$fieldSuffix = EventbookingHelper::getFieldSuffix();
			if (strlen(trim(strip_tags($message->{'invitation_form_message' . $fieldSuffix}))))
			{
				$inviteMessage = $message->{'invitation_form_message' . $fieldSuffix};
			}
			else
			{
				$inviteMessage = $message->invitation_form_message;
			}
			$showCaptcha = 0;
			if ($config->enable_captcha && ($user->id == 0 || $config->bypass_captcha_for_registered_user !== '1'))
			{
				$captchaPlugin = JFactory::getApplication()->getParams()->get('captcha', JFactory::getConfig()->get('captcha'));
				if(!$captchaPlugin)
				{
					// Hardcode to recaptcha, reduce support request
					$captchaPlugin = 'recaptcha';
				}
				$plugin = JPluginHelper::getPlugin('captcha', $captchaPlugin);
				if ($plugin)
				{
					$showCaptcha = 1;									
					$this->captcha = JCaptcha::getInstance($captchaPlugin)->display('dynamic_recaptcha_1', 'dynamic_recaptcha_1', 'required');
					$this->captchaPlugin = $captchaPlugin;
				}
				else
				{
					JFactory::getApplication()->enqueueMessage(JText::_('EB_CAPTCHA_NOT_ACTIVATED_IN_YOUR_SITE'), 'error');
				}
			}			
			$eventId = JRequest::getInt('id', 0);
			$query->select('*, title' . $fieldSuffix . ' AS title')
				->from('#__eb_events')
				->where('id=' . $eventId);
			$db->setQuery($query);
			$this->event = $db->loadObject();
			$this->user = $user;
			$this->inviteMessage = $inviteMessage;
			$this->showCaptcha = $showCaptcha;
			$this->bootstrapHelper = new EventbookingHelperBootstrap($config->twitter_bootstrap_version);

			parent::display($tpl);
		}
	}

	/**
	 * Display invitation complete message	
	 * @param string $tpl
	 */
	function _displayInviteComplete($tpl)
	{
		$message = EventbookingHelper::getMessages();
		$fieldSuffix = EventbookingHelper::getFieldSuffix();
		if (strlen(trim(strip_tags($message->{'invitation_complete' . $fieldSuffix}))))
		{
			$this->message = $message->{'invitation_complete' . $fieldSuffix};
		}
		else
		{
			$this->message = $message->invitation_complete;
		}
		parent::display($tpl);
	}
}