var geocoder;
var map;
var mapMarker;

geocoder = new google.maps.Geocoder();

function drawMap(id, marker, desc, address, mapZoom) 
{
	var latlng = new google.maps.LatLng(0, 0);

	var mapOptions = {  zoom     : mapZoom,
						center   : latlng,
					    mapTypeId: google.maps.MapTypeId.ROADMAP};


	var map = new google.maps.Map(document.getElementById(id), mapOptions);

	if(!geocoder)
	{
		setTimeout(function(){drawMap(id, marker, desc, address, mapZoom)}, 500);
	}
	else
	{		
		geocoder.geocode( 
			{'address': address},

			function(results, status) {

				if(status == google.maps.GeocoderStatus.OK)					
				{
					map.zoom = mapZoom;
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
