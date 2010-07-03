/**
 * Plugin Name: Multi Google Maps
 * Plugin URI : http://wordpress.org/extend/plugins/multi-google-maps/
 * Description: This plugin supports to insert Multi Google Map V.3 Objects into your post.
 * Version    : 0.4.3
 * Author     : Siripol Noikajana
 * Author URI : http://wordpress.org/extend/plugins/multi-google-maps/
 * License    : GPL2
 */

var geocoder;
var map;
var mapMarker;

geocoder = new google.maps.Geocoder();

function drawMap(id, marker, desc, address, zoom) 
{


	var latlng = new google.maps.LatLng(0, 0);

	var mapOptions = {
	  zoom: 8,
	  center: latlng,
	  mapTypeId: google.maps.MapTypeId.ROADMAP
	};


	var map = new google.maps.Map(document.getElementById(id), mapOptions);

	if(!geocoder)
	{
		setTimeout(function(){drawMap(id, marker, desc, address, zoom)}, 500);
	}
	else
	{		
		geocoder.geocode( 
			{'address': address},

			function(results, status) {

				if(status == google.maps.GeocoderStatus.OK)					
				{
					map.zoom = 15;
					map.setCenter(results[0].geometry.location);

					mapMarker = new google.maps.Marker({
						map: map, 
						position: results[0].geometry.location,
						title: marker
					});										    

					message = '<div id="content"><strong style="font-size:12px">' + marker + '</strong><br/>' +
							  '<span style="font-size:10px">' + desc + '</span></div>';

					infowindow = new google.maps.InfoWindow(
						{   
							content: message
						});
			
					//infowindow.open(map,mapMarker);

					google.maps.event.addListener(mapMarker, 'click', function() {
						infowindow.open(map,mapMarker);
					});		
				}
			}
		);
	}
}
