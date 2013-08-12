<!DOCTYPE html>
<html>
  <head>
    <title>Simple Map!</title>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <style>
      html, body, #map-canvas {
        margin: 0;
        padding: 0;
        height: 100%;
      }
    </style>
    <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
    <script>
var map;
function initialize() {
  var mapOptions = {
    zoom: 8,
    center: new google.maps.LatLng(-34.720174, 135.858127),
    mapTypeId: google.maps.MapTypeId.ROADMAP
  };
  map = new google.maps.Map(document.getElementById('map-canvas'),
      mapOptions);
	  
  var marker = new google.maps.Marker({
    position: new google.maps.LatLng(-34.725891, 135.849527),
    map: map,
    title: 'Click to zoom'
  });
  
  google.maps.event.addListener(map, 'center_changed', function() {
    // 3 seconds after the center of the map has changed, pan back to the
    // marker.
    window.setTimeout(function() {
      map.panTo(marker.getPosition());
    }, 3000);
  });

  google.maps.event.addListener(marker, 'click', function() {
    map.setZoom(18);
    map.setCenter(marker.getPosition());
	map.setMapTypeId(google.maps.MapTypeId.HYBRID);
  });
  
  google.maps.event.addListener(map, "click", function(event) {
    addMarker = new google.maps.Marker({
		position: event.latLng,
		map: map,
		draggable: true,
		title: 'User added marker'
	});
	
});
}

google.maps.event.addDomListener(window, 'load', initialize);

    </script>
  </head>
  <body>
    <div id="map-canvas"></div>
  </body>
</html>