$(document).ready(function() {
	$(window).resize(function() {
		height = $("#content_map").height();
		width = $("#content_map").width();
		$("#map_canvas").css("height", height + "px");
		$("#map_canvas").css("width", width + "px");
	});
	
	$('#federations1').change(function(){
				 $('#hcompanys1').html('');
				 $('#buildings1').html('');
				aggiornaMappa(1);
				selectHC(1);
				selectBLD(1);
			});
	 $('#hcompanys1').change(function(){
				$('#buildings1').html('');
				aggiornaMappa(1);
				selectBLD(1);
	});
	 $('#buildings1').change(function(){
		 aggiornaMappa(1);
	 });
	 
	$('#month_mode').change(function(){
		periodicity = $( this ).val();
		
		if(periodicity == 2){
			$('#periodicity_2').removeClass('dn');
			$('#periodicity_12').addClass('dn');
		}
		else if(periodicity == 12){
			$('#periodicity_2').addClass('dn');
			$('#periodicity_12').removeClass('dn');
		} else {
			$('#periodicity_2').addClass('dn');
			$('#periodicity_12').addClass('dn');			
		}
	});
			
	$('#show_outputs').click(function(){
		id = $('#buildings1').val();
		year = $('#year').val();
		
		// in base al tipo di periodicità scelta, eseguo operazioni diverse
		periodicity = $('#month_mode').val();
		
		// semestrale
		if(periodicity == 2){
			mode = $('#mode2').val();
		}
		else if(periodicity == 12){
			mode = $('#mode12').val();
		} else {
			mode = '';
		}
		
		if(year != '' &&  id != '' && mode != '' && periodicity != ''){
			selezioneEdificio(id, year, mode, periodicity);
		} else {
				$( "#dialog-confirm" ).dialog({
				resizable: false,
				height:160,
				modal: true,
				buttons: {
					"Ok": function() {
						$( this ).dialog( "close" );
					}
				}
			});
		}
	});	
});

function initialize()	{
	$(document).ready(function() {
		height = $(document).height()-500;
		height = 400;
		width = $(document).width()-380;
		$("#map_canvas").css("height", height + "px");
		$("#map_canvas").css("width", width + "px");
		var latlng = new google.maps.LatLng(41.442, 12.392);
		var myOptions = {
		  zoom: 5,
		  center: latlng,
		  mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		map = new google.maps.Map(document.getElementById("map_canvas"),myOptions);
		$(window).resize();
	});
}

$('[name="ADDRESS_BLD"]').change(function() {
	var comune = ($('[name="K1_ID_COMUNI"] option:selected').text());
	var provincia = ($('[name="K1_ID_PROVINCE"] option:selected').text());
	var indirizzo = $('[name="ADDRESS_BLD"]').val() + ' ' + comune + ' ' + provincia;
	//alert(indirizzo);
	geocode = new google.maps.Geocoder;	
	geocode.geocode({address: indirizzo}, function(result, status)	{
				coordinate = result[0].geometry.location;
				$('#lat').val(coordinate.lat());
				$('#lng').val(coordinate.lng());
				//alert(coordinate);
				if(typeof(marker) != "undefined")	
					marker.setMap(null);
				
				marker = new google.maps.Marker();
				marker.setPosition(coordinate);
				marker.setMap(map);
					
				map.setCenter(coordinate);
				map.setZoom(14);
		});
	//map.
});
	
	
function selectHC(id)	{
	federation = $('#federations' + id).val();
	jQuery.getJSON("ajax/json.php?action=select_hc&fed=" + federation,
	  function(data){
		options = '<option selected="selected" value="">- Scegli housing company</option>	' + "\n\r";
		 jQuery.each(data, function(i,item){
			options += '<option  value="' + item.optionValue + '">' + item.optionDisplay + '</option>' + "\n\r";
		 });
	  $('#hcompanys' + id).html(options); 
	  //map.setCenter(cornice.getCenter());
	  });
}

function selectBLD(id)	{
	hcompanys = $('#hcompanys' + id).val();
	federation = $('#federations' + id).val();
	jQuery.getJSON("ajax/json.php?action=select_bld&hc=" + hcompanys + "&fed=" + federation,
	  function(data){
		options = '<option selected="selected" value="">- Scegli edificio</option>	' + "\n\r";
		 jQuery.each(data, function(i,item){
			options += '<option  value="' + item.optionValue + '">' + item.optionDisplay + '</option>' + "\n\r";
		 });
	  $('#buildings' + id).html(options);
	  });
}
	
function selezioneEdificio(id, year, mode, periodicity){
	id_user = $('#id_user').val();
	
	
	if(periodicity == 2){ // carico gli output semestrali
		$('#tabs-1').loady({
			url: "ajax/json.php?action=list_outputs&id="+id + "&year=" + year + "&mode=" + mode + "&id_user=" + id_user,
		});
	}
	else if(periodicity == 12){ // carico gli output mensili
		$('#tabs-1').loady({
			url: "ajax/json.php?action=list_outputs12&id="+id + "&year=" + year + "&mode=" + mode + "&id_user=" + id_user,
		});
	}

	$( "#tabs" ).tabs( "option", "selected", 1 );
}

function aggiornaMappa(id)	{
	federation = $('#federations' + id).val();
	hcompanys = $('#hcompanys' + id).val();
	buildings = $('#buildings' + id).val();
	
	jQuery.getJSON("ajax/json.php?action=getbuildings&fed=" + federation + "&hc=" + hcompanys + "&bld=" + buildings,
	  function(data){
		var cornice = new google.maps.LatLngBounds();
		deleteOverlays();
		
		jQuery.each(data, function(i,item){
			coordinate = new google.maps.LatLng(parseFloat(item.LAT_BLD), parseFloat(item.LNG_BLD));
			 var marker = new google.maps.Marker({
					  position: coordinate, 
					  map: map,
					  title: item.id
						});
		  
						google.maps.event.addListener(marker, 'click', function(event) {
						console.log(event);
						console.log(marker);
						//	selezioneEdificio(item.id);
		  });
			cornice.extend(coordinate);
		});
	  
	  if (!cornice.isEmpty())
		map.fitBounds(cornice);
		showOverlays();
	  });
	
}