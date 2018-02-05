<?php
/**
 * @package            Joomla
 * @subpackage         Event Booking
 * @author             Tuan Pham Ngoc
 * @copyright          Copyright (C) 2010 - 2018 Ossolution Team
 * @license            GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die;

if (empty($this->config->social_sharing_buttons))
{
	$shareOptions = array(
		'Delicious',
		'Digg',
		'Facebook',
		'Google',
		'Stumbleupon',
		'Technorati',
		'Twitter',
		'LinkedIn'
	);
}
else
{
	$shareOptions = explode(',', $this->config->social_sharing_buttons);
}

$rootUri = JUri::root(true);
?>
<div id="itp-social-buttons-box" class="row-fluid">
    <div id="eb-share-text"><?php echo JText::_('EB_SHARE_THIS_EVENT'); ?></div>
    <div id="eb-share-button">
        <?php
            $title = $this->item->title;

            if (in_array('Delicious', $shareOptions))
            {
                if (file_exists(JPATH_ROOT.'/images/com_eventbooking/socials/delicious.png'))
                {
	                $iconUrl = $rootUri . '/images/com_eventbooking/socials/delicious.png';
                }
                else
                {
	                $iconUrl = $rootUri . '/media/com_eventbooking/assets/images/socials/delicious.png';
                }

                $alt     = JText::sprintf('EB_SUBMIT_ITEM_IN_SOCIAL_NETWORK', $title, 'Delicious');

                echo '<a href="http://del.icio.us/post?url=' . rawurlencode($socialUrl) . '&amp;title=' . rawurlencode($title) . '" title="' . $alt . '" target="blank" >
                    <img src="' . $iconUrl . '" alt="' . $alt . '" />
                </a>';
            }

            if (in_array('Digg', $shareOptions))
            {
	            if (file_exists(JPATH_ROOT.'/images/com_eventbooking/socials/digg.png'))
	            {
		            $iconUrl = $rootUri . '/images/com_eventbooking/socials/digg.png';
	            }
	            else
	            {
		            $iconUrl = $rootUri . '/media/com_eventbooking/assets/images/socials/digg.png';
	            }

                $alt     = JText::sprintf('EB_SUBMIT_ITEM_IN_SOCIAL_NETWORK', $title, 'Digg');

                echo '<a href="http://digg.com/submit?url=' . rawurlencode($socialUrl) . '&amp;title=' . rawurlencode($title) . '" title="' . $alt . '" target="blank" >
                        <img src="' . $iconUrl . '" alt="' . $alt . '" />
                      </a>';
            }

            if (in_array('Facebook', $shareOptions))
            {
	            if (file_exists(JPATH_ROOT.'/images/com_eventbooking/socials/facebook.png'))
	            {
		            $iconUrl = $rootUri . '/images/com_eventbooking/socials/facebook.png';
	            }
	            else
	            {
		            $iconUrl = $rootUri . '/media/com_eventbooking/assets/images/socials/facebook.png';
	            }

                $alt     = JText::sprintf('EB_SUBMIT_ITEM_IN_SOCIAL_NETWORK', $title, 'FaceBook');

                echo '<a href="http://www.facebook.com/sharer.php?u=' . rawurlencode($socialUrl) . '&amp;t=' . rawurlencode($title) . '" title="' . $alt . '" target="blank" >
                        <img src="' . $iconUrl . '" alt="' . $alt . '" />
                      </a>';
            }

            if (in_array('Google', $shareOptions))
            {
	            if (file_exists(JPATH_ROOT.'/images/com_eventbooking/socials/google.png'))
	            {
		            $iconUrl = $rootUri . '/images/com_eventbooking/socials/google.png';
	            }
	            else
	            {
		            $iconUrl = $rootUri . '/media/com_eventbooking/assets/images/socials/google.png';
	            }

                $alt     = JText::sprintf('EB_SUBMIT_ITEM_IN_SOCIAL_NETWORK', $title, 'Google Bookmarks');

                echo '<a href="http://www.google.com/bookmarks/mark?op=edit&bkmk=' . rawurlencode($socialUrl) . '" title="' . $alt . '" target="blank" >
                        <img src="' . $iconUrl . '" alt="' . $alt . '" />
                        </a>';
            }

            if (in_array('Stumbleupon', $shareOptions))
            {
	            if (file_exists(JPATH_ROOT.'/images/com_eventbooking/socials/stumbleupon.png'))
	            {
		            $iconUrl = $rootUri . '/images/com_eventbooking/socials/stumbleupon.png';
	            }
	            else
	            {
		            $iconUrl = $rootUri . '/media/com_eventbooking/assets/images/socials/stumbleupon.png';
	            }

                $alt     = JText::sprintf('EB_SUBMIT_ITEM_IN_SOCIAL_NETWORK', $title, 'Stumbleupon');

                echo '<a href="http://www.stumbleupon.com/submit?url=' . rawurlencode($socialUrl) . '&amp;title=' . rawurlencode($title) . '" title="' . $alt . '" target="blank" >
                            <img src="' . $iconUrl . '" alt="' . $alt . '" />
                      </a>';
            }

            if (in_array('Technorati', $shareOptions))
            {
	            if (file_exists(JPATH_ROOT.'/images/com_eventbooking/socials/technorati.png'))
	            {
		            $iconUrl = $rootUri . '/images/com_eventbooking/socials/technorati.png';
	            }
	            else
	            {
		            $iconUrl = $rootUri . '/media/com_eventbooking/assets/images/socials/technorati.png';
	            }

                $alt     = JText::sprintf('EB_SUBMIT_ITEM_IN_SOCIAL_NETWORK', $title, 'Technorati');

                echo '<a href="http://technorati.com/faves?add=' . rawurlencode($socialUrl) . '" title="' . $alt . '" target="blank" >
                            <img src="' . $iconUrl . '" alt="' . $alt . '" />
                      </a>';
            }

            if (in_array('Twitter', $shareOptions))
            {
	            if (file_exists(JPATH_ROOT.'/images/com_eventbooking/socials/twitter.png'))
	            {
		            $iconUrl = $rootUri . '/images/com_eventbooking/socials/twitter.png';
	            }
	            else
	            {
		            $iconUrl = $rootUri . '/media/com_eventbooking/assets/images/socials/twitter.png';
	            }

                $alt     = JText::sprintf('EB_SUBMIT_ITEM_IN_SOCIAL_NETWORK', $title, 'Twitter');

                echo '<a href="http://twitter.com/?status=' . rawurlencode($title . " " . $socialUrl) . '" title="' . $alt . '" target="blank" >
                        <img src="' . $iconUrl . '" alt="' . $alt . '" />
                    </a>';
            }

            if (in_array('LinkedIn', $shareOptions))
            {
	            if (file_exists(JPATH_ROOT.'/images/com_eventbooking/socials/linkedin.png'))
	            {
		            $iconUrl = $rootUri . '/images/com_eventbooking/socials/linkedin.png';
	            }
	            else
	            {
		            $iconUrl = $rootUri . '/media/com_eventbooking/assets/images/socials/linkedin.png';
	            }

                $alt     = JText::sprintf('EB_SUBMIT_ITEM_IN_SOCIAL_NETWORK', $title, 'LinkedIn');

	            echo '<a href="http://www.linkedin.com/shareArticle?mini=true&amp;url=' . $socialUrl . '&amp;title=' . $title . '" title="' . $alt . '" target="_blank" ><img src="' . $iconUrl . '" alt="' . $alt . '" /></a>';
            }
        ?>
    </div>
</div>