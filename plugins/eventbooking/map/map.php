<?php
/**
 * @version		1.6.5
 * @package		Joomla
 * @subpackage	Events Booking
 * @author  Tuan Pham Ngoc
 * @copyright	Copyright (C) 2012 - 2014 Ossolution Team
 * @license		GNU/GPL, see LICENSE.php
 */

defined( '_JEXEC' ) or die ;

class plgEventBookingMap extends JPlugin
{	
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		JFactory::getLanguage()->load('plg_eventbooking_map', JPATH_ADMINISTRATOR);		
	}
	/**
	 * Render setting form
	 * @param PlanOSMembership $row
	 */
	function onEventDisplay($row) {	
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('a.*')
			  ->from('#__eb_locations AS a')
			  ->innerJoin('#__eb_events AS b ON a.id = b.location_id')
			  ->where('b.id = '.(int)$row->id);
		;
		$db->setQuery($query);
		$event = $db->loadObject();
		
		ob_start();
		$this->drawMap($row,$event);		
		$form = ob_get_clean();					
		if (empty($event->lat) && empty($event->long))
		{
			return '';
		}			
		else
		{
			return array('title' => JText::_('PLG_EB_MAP'),
				'form' => $form
			) ;
		}							
	}

	/**
	 * Display form allows users to change settings on subscription plan add/edit screen 
	 * @param object $row
	 */	
	function drawMap($row,$event) 
	{
		$uri = JUri::getInstance();
		if ($uri->getScheme() == 'https')
		{
			$https = true;
		}
		else 
		{
			$https = false;
		}
		$config = EventbookingHelper::getConfig();
		$zoomLevel = $config->zoom_level ? (int) $config->zoom_level : 10;
		$mapWidth = $this->params->def('map_width', 700);
		$mapHeight = $this->params->def('map_height', 500);
		$bubbleText = "<ul class=\"bubble\">";
		$bubbleText .= "<li class=\"location_name\"><h4>";
		$bubbleText .= addslashes($event->name);
		$bubbleText .= "</h4></li>";
		$bubbleText .= "<li class=\"address\">".addslashes($event->address.', '.$event->city.', '.$event->state.', '.$event->zip.', '.$event->country)."</li>";
		$getDirectionLink = 'http://maps.google.com/maps?f=d&daddr='.$event->lat.','.$event->long.'('.addslashes($event->address.', '.$event->city.', '.$event->state.', '.$event->zip.', '.$event->country).')' ; 	
		$bubbleText .= "<li class=\"address getdirection\"><a href=\"".$getDirectionLink."\" target=\"_blank\">".JText::_('EB_GET_DIRECTION')."</li>";	
		$bubbleText .= "</ul>" ;
	?>
    <script type="text/javascript" src="<?php echo ($https) ? 'https' : 'http'?>://maps.google.com/maps/api/js?sensor=false"></script>
    <script type="text/javascript">
	(function($){
	    $(document).ready(function() {
	      function initialize() 
	        {     
	            var latlng = new google.maps.LatLng(<?php echo $event->lat ?>, <?php echo $event->long; ?>);  
	            var myOptions = {       zoom: <?php echo $zoomLevel; ?>,       center: latlng,       mapTypeId: google.maps.MapTypeId.ROADMAP     };     
	            var map = new google.maps.Map(document.getElementById("map_canvas"),         myOptions);
	
	
	            var marker = new google.maps.Marker({
	                position: latlng,
	                map: map,
	                title: "<?php echo $event->name ; ?>"
	            });
	
	            var contentString = '<?php echo $bubbleText ; ?>' ;
	            var infowindow = new google.maps.InfoWindow({
	                content: contentString,
	                //maxWidth: 20
	            });
	            google.maps.event.addListener(marker, 'click', function() {
	              infowindow.open(map,marker);
	            });
				infowindow.open(map,marker);
	        }  
	     initialize(); 
	    });
	})(jQuery);
    </script>
     <div id="mapform" >
  		 <div id="map_canvas" style="width: <?php echo $mapWidth; ?>px; height: <?php echo $mapHeight; ?>px"></div>
   	</div>
	<?php							
	}
}	