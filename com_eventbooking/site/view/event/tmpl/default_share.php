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

/* @var JDocumentHtml $document */
$document      = JFactory::getDocument();
$rootUri       = JUri::root();
$largeImageUri = '';

$document->setMetaData('og:title', $this->item->title, 'property');

if ($this->item->image && file_exists(JPATH_ROOT . '/' . $this->item->image))
{
	$largeImageUri = $rootUri . $this->item->image;
}
elseif ($this->item->thumb && file_exists(JPATH_ROOT . '/media/com_eventbooking/images/' . $this->item->thumb))
{
	$largeImageUri = $rootUri . 'media/com_eventbooking/images/' . $this->item->thumb;
}
elseif ($this->item->thumb && file_exists(JPATH_ROOT . '/media/com_eventbooking/images/thumbs/' . $this->item->thumb))
{
	$largeImageUri = $rootUri . 'media/com_eventbooking/images/thumbs/' . $this->item->thumb;
}

if ($largeImageUri)
{
	$document->setMetaData('og:image', $largeImageUri, 'property');
}

$document->setMetaData('og:url', JUri::getInstance()->toString(), 'property');

$description = !empty($this->item->meta_description) ? $this->item->meta_description : $this->item->description;
$description = JHtml::_('string.truncate', $description, 200, true, false);
$document->setMetaData('og:description', $description, 'property');

$document->setMetaData('og:site_name', JFactory::getConfig()->get('sitename'), 'property');
?>
<div class="sharing clearfix">
	<!-- FB -->
	<div style="float:left;" id="rsep_fb_like">
		<div id="fb-root"></div>
		<script src="https://connect.facebook.net/en_US/all.js" type="text/javascript"></script>
		<script type="text/javascript">
			FB.init({appId: '340486642645761', status: true, cookie: true, xfbml: true});
		</script>
		<fb:like href="<?php echo JUri::getInstance()->toString(); ?>" send="true" layout="button_count" width="150"
		         show_faces="false"></fb:like>
	</div>

	<!-- Twitter -->
	<div style="float:left;" id="rsep_twitter">
		<a href="https://twitter.com/share" class="twitter-share-button"
		   data-text="<?php echo $this->item->title . " " . $socialUrl; ?>">Tweet</a>
		<script>!function (d, s, id) {
				var js, fjs = d.getElementsByTagName(s)[0];
				if (!d.getElementById(id)) {
					js = d.createElement(s);
					js.id = id;
					js.src = "//platform.twitter.com/widgets.js";
					fjs.parentNode.insertBefore(js, fjs);
				}
			}(document, "script", "twitter-wjs");</script>
	</div>

	<!-- GPlus -->
	<div style="float:left;" id="rsep_gplus">
		<!-- Place this tag where you want the +1 button to render -->
		<g:plusone size="medium"></g:plusone>

		<!-- Place this render call where appropriate -->
		<script type="text/javascript">
			(function () {
				var po = document.createElement('script');
				po.type = 'text/javascript';
				po.async = true;
				po.src = 'https://apis.google.com/js/plusone.js';
				var s = document.getElementsByTagName('script')[0];
				s.parentNode.insertBefore(po, s);
			})();
		</script>
	</div>
</div>