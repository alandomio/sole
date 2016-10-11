markersArray = [];

function addMarker(location) {
  marker = new google.maps.Marker({
    position: location,
    map: map
  });
  markersArray.push(marker);
}

// Removes the overlays from the map, but keeps them in the array
function clearOverlays() {
  if (markersArray) {
    for (i in markersArray) {
      markersArray[i].setMap(null);
    }
  }
}

// Shows any overlays currently in the array
function showOverlays() {
  if (markersArray) {
    for (i in markersArray) {
      markersArray[i].setMap(map);
    }
  }
}

// Deletes all markers in the array by removing references to them
function deleteOverlays() {
  if (markersArray) {
    for (i in markersArray) {
      markersArray[i].setMap(null);
    }
    markersArray.length = 0;
  }
}

function initialize_marker(map){
	var lat = $('#lat').val();
	var lng = $('#lng').val();

	coordinate = new google.maps.LatLng(lat, lng);
	
	marker = new google.maps.Marker();
	marker.draggable = true;
	marker.setPosition(coordinate);
	marker.setMap(map);
	
	google.maps.event.addListener(marker, "dragend", function(event) {
	var point = marker.getPosition();
		$('#lat').val(point.lat());
		$('#lng').val(point.lng());
	});
}

function geocodifica(map){
	var comune = ($('[name="K0_ID_COMUNI"] option:selected').text());
	var provincia = ($('[name="K0_ID_PROVINCE"] option:selected').text());
	var indirizzo = $('[name="ADDRESS_FED"]').val() + ' ' + comune + ' ' + provincia;
	
	geocode = new google.maps.Geocoder;	
	geocode.geocode({address: indirizzo}, function(result, status)	{
		coordinate = result[0].geometry.location;
		$('#lat').val(coordinate.lat());
		$('#lng').val(coordinate.lng());

		if(typeof(marker) != "undefined")	
			marker.setMap(null);
		
		marker = new google.maps.Marker();
		marker.draggable = true;
		marker.setPosition(coordinate);
		marker.setMap(map);
			
		google.maps.event.addListener(marker, "dragend", function(event) {
		var point = marker.getPosition();
			$('#lat').val(point.lat());
			$('#lng').val(point.lng());
		});
		
		map.setCenter(coordinate);
		map.setZoom(14);
	});
}