<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2016 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die;

EventbookingHelperJquery::colorbox('a.eb-modal');

$item = $this->item ;
$url = JRoute::_(EventbookingHelperRoute::getEventRoute($item->id, 0, $this->Itemid), false);
$canRegister = EventbookingHelper::acceptRegistration($item) ;
$socialUrl = JUri::getInstance()->toString(array('scheme', 'user', 'pass', 'host')).JRoute::_(EventbookingHelperRoute::getEventRoute($item->id, 0, $this->Itemid));

if ($this->config->use_https)
{
	$ssl = 1 ;
}
else
{
	$ssl = 0 ;
}

/* @var EventbookingHelperBootstrap $bootstrapHelper*/
$bootstrapHelper   = $this->bootstrapHelper;
$iconPencilClass   = $bootstrapHelper->getClassMapping('icon-pencil');
$iconOkClass       = $bootstrapHelper->getClassMapping('icon-ok');
$iconRemoveClass   = $bootstrapHelper->getClassMapping('icon-remove');
$iconDownloadClass = $bootstrapHelper->getClassMapping('icon-download');
$btnClass          = $bootstrapHelper->getClassMapping('btn');
$return = base64_encode(JUri::getInstance()->toString());

if ($item->cut_off_date != JFactory::getDbo()->getNullDate())
{
	$registrationOpen = ($item->cut_off_minutes < 0);
}
else
{
	$registrationOpen = ($item->number_event_dates > 0);
}

$offset = JFactory::getConfig()->get('offset');
?>
<div id="eb-event-page" class="eb-container eb-category-<?php echo $item->category_id; ?>eb-event<?php if ($item->featured) echo ' eb-featured-event'; ?>" itemscope itemtype="http://schema.org/Event">
	<div class="eb-box-heading clearfix">
		<h1 class="eb-page-heading">
			<span itemprop="name"><?php echo $item->title; ?></span>
		</h1>
	</div>
	<div id="eb-event-details" class="eb-description">
		<?php
			// Facebook, twitter, Gplus share buttons
			if ($this->config->show_fb_like_button)
			{
				echo $this->loadTemplate('share');
			}
		?>

		<div class="eb-description-details clearfix" itemprop="description">
			<?php
				if ($this->config->get('show_image_in_event_detail', 1) && $this->config->display_large_image && $item->image && file_exists(JPATH_ROOT . '/' . $item->image))
				{
				?>
					<img src="<?php echo JUri::base(true) . '/' . $item->image; ?>" class="eb-event-large-image img-polaroid"/>
				<?php
				}
				elseif ($this->config->get('show_image_in_event_detail', 1) && $item->thumb && file_exists(JPATH_ROOT . '/media/com_eventbooking/images/thumbs/' . $item->thumb))
				{
				?>
					<a href="<?php echo JUri::base(true).'/media/com_eventbooking/images/'.$item->thumb; ?>" class="eb-modal"><img src="<?php echo JUri::base(true).'/media/com_eventbooking/images/thumbs/'.$item->thumb; ?>" class="eb-thumb-left"/></a>
				<?php
				}

				echo $item->description;
			?>
		</div>

		<div id="eb-event-info" class="clearfix <?php echo $bootstrapHelper->getClassMapping('row-fluid'); ?>">
			<?php
			if (!empty($this->items))
			{
				echo EventbookingHelperHtml::loadCommonLayout('common/tmpl/events_children.php', array('items' => $this->items, 'config' => $this->config, 'Itemid' => $this->Itemid, 'nullDate' => $this->nullDate, 'ssl' => $ssl, 'viewLevels' => $this->viewLevels, 'categoryId' => $this->item->category_id, 'bootstrapHelper' => $this->bootstrapHelper));
			}
			else
			{
				$leftCssClass = 'span8';
				if (empty($this->rowGroupRates))
				{
					$leftCssClass = 'span12';
				}
			?>
				<div id="eb-event-info-left" class="<?php echo $bootstrapHelper->getClassMapping($leftCssClass); ?>">
					<h3 id="eb-event-properties-heading">
						<?php echo JText::_('EB_EVENT_PROPERTIES'); ?>
					</h3>
					<?php
					echo $this->loadTemplate('event_properties');

					if ($item->activate_waiting_list == 2)
					{
						$activateWaitingList = $this->config->activate_waitinglist_feature;
					}
					else
					{
						$activateWaitingList = $item->activate_waiting_list;
					}

					if (($item->event_capacity > 0) && ($item->event_capacity <= $item->total_registrants) && $activateWaitingList && !@$item->user_registered && $registrationOpen)
					{
						$waitingList = true ;
					}
					else
					{
						$waitingList = false ;
					}

					if (!$canRegister && $item->registration_type != 3 && $this->config->display_message_for_full_event && !$waitingList && $item->registration_start_minutes >= 0)
					{
						if (@$item->user_registered)
						{
							$msg = JText::_('EB_YOU_REGISTERED_ALREADY');
						}
						elseif (!in_array($item->registration_access, $this->viewLevels))
						{
							$msg = JText::_('EB_LOGIN_TO_REGISTER') ;
						}
						else
						{
							$msg = JText::_('EB_NO_LONGER_ACCEPT_REGISTRATION') ;
						}
						?>
							<div class="text-info eb-notice-message"><?php echo $msg ; ?></div>
						<?php
					}
					?>
				</div>

				<?php
				if (count($this->rowGroupRates))
				{
					echo $this->loadTemplate('group_rates');
				}
			}
			?>
		</div>
		<div class="clearfix"></div>
	<?php
	if (!empty($item->ticketTypes))
	{
		echo EventbookingHelperHtml::loadCommonLayout('common/tmpl/tickettypes.php', array('ticketTypes' => $item->ticketTypes, 'config' => $this->config));
	?>
		<div class="clearfix"></div>
	<?php
	}

	$ticketsLeft = $item->event_capacity - $item->total_registrants ;

	if ($item->individual_price > 0 || $ticketsLeft > 0)
	{
	?>
		<div style="display:none;" itemprop="offers" itemscope itemtype="http://schema.org/AggregateOffer">
			<meta itemprop="url" content="<?php echo JUri::getInstance()->toString();?>">
			<?php
			if ($item->individual_price > 0)
			{
			?>
				<span itemprop="lowPrice"><?php echo EventbookingHelper::formatCurrency($item->individual_price, $this->config, $item->currency_symbol); ?></span>
			<?php
			}

			if ($ticketsLeft > 0)
			{
			?>
				<span itemprop="offerCount"><?php echo $ticketsLeft;?></span>
			<?php
			}
			?>
		</div>
	<?php
	}

	if ($this->showTaskBar)
	{
	?>
		<div class="eb-taskbar clearfix">
			<ul>
				<?php
					$layoutData = array(
						'item'                => $this->item,
						'config'              => $this->config,
						'showRegisterButtons' => empty($this->items),
						'canRegister'         => $canRegister,
						'Itemid'              => $this->Itemid,
						'ssl'                 => $ssl,
						'btnClass'            => $btnClass,
						'iconOkClass'         => $iconOkClass,
						'iconRemoveClass'     => $iconRemoveClass,
						'iconDownloadClass'   => $iconDownloadClass,
						'registrationOpen'    => $registrationOpen,
						'return'              => $return,
						'iconPencilClass'     => $iconPencilClass,
						'showInviteFriend'    => true,
					);

					echo EventbookingHelperHtml::loadCommonLayout('common/tmpl/buttons.php', $layoutData);
				?>
			</ul>
		</div>
	<?php
	}

	if (count($this->plugins))
	{
		echo $this->loadTemplate('plugins');
	}

	if ($this->config->show_social_bookmark)
	{
		echo $this->loadTemplate('social_buttons', array('socialUrl' => $socialUrl));
	}
?>
	</div>
</div>

<form name="adminForm" id="adminForm" action="index.php" method="post">
	<input type="hidden" name="option" value="com_eventbooking" />
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="id" value="" />
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<?php echo JHtml::_( 'form.token' ); ?>
</form>

<script language="javascript">
	function cancelRegistration(registrantId) {
		var form = document.adminForm ;
		if (confirm("<?php echo JText::_('EB_CANCEL_REGISTRATION_CONFIRM'); ?>")) {
			form.task.value = 'registrant.cancel' ;
			form.id.value = registrantId ;
			form.submit() ;
		}
	}
</script>