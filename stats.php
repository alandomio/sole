<?php
# V.0.1.8
include_once 'init.php';
list($il, $jsstatis) = request::get(array('il' => NULL,'jsstatis' => NULL));

$user = new autentica($aA5);
$user -> login_no_redirect(false);
//include_once stringa::get_conffile($MYFILE -> filename);
$scheda -> img = false;

$MYFILE -> add_js('
<script type="text/javascript">
	$(function() {
		height = $(document).height();
		width = $(document).width();
		$("#map_canvas").height(height-350);
		$("#map_canvas").width(width-320);
	});
	$(window).resize(function() {
		height = $("#content_map").height();
		width = $("#content_map").width();
		$("#map_canvas").css("height", height + "px");
		$("#map_canvas").css("width", width + "px");
	});
</script>' ,'code', 'head');

$MYFILE -> add_js('
<script type="text/javascript">
function invia() {
	var str = $("form").serialize();
	//$("#result").text(str);
	$(\'#ajax_wizard\').load(\'federations_address_bk.php\', str, function(){
		addChange();
	});
}

function addChange(){
	$(\'select\').change(function() {
		//alert(\'addChange\');
		invia();
	});
}

$(document).ready(function(){
	addChange();
})
</script>' ,'code', 'head');


$MYFILE -> add_js('<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>', 'file', 'head');
$MYFILE -> add_js('<script type="text/javascript">
  function initialize() {
    var latlng = new google.maps.LatLng('.DEF_COORDS.');
    var myOptions = {
      zoom: 5,
      center: latlng,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    map = new google.maps.Map(document.getElementById("map_canvas"),
        myOptions);
  }

</script>
', 'code', 'head');
$initialize = $body_ini;

$html['gmaps'] = '<div id="map_canvas"></div>
';

# SELECT FEDERATIONS
$input['federations1'] = new io();
$input['federations1'] -> type = 'select'; 
$input['federations1'] -> addblank = true; 
$input['federations1'] -> aval = rs::id2arr("SELECT ID_FEDERATION, FEDERATION FROM federations ORDER BY FEDERATION ASC"); 
$input['federations1'] -> css = 'trecento'; 
$input['federations1'] -> id = 'federations1'; 
$input['federations1'] -> txtblank = '- Scegli federazione'; 
$input['federations1'] -> set('federations');

$input['federations2'] = new io();
$input['federations2'] -> type = 'select'; 
$input['federations2'] -> addblank = true; 
$input['federations2'] -> aval = rs::id2arr("SELECT ID_FEDERATION, FEDERATION FROM federations ORDER BY FEDERATION ASC"); 
$input['federations2'] -> css = 'trecento'; 
$input['federations2'] -> id = 'federations2'; 
$input['federations2'] -> txtblank = '- Scegli federazione'; 
$input['federations2'] -> set('federations');

$input['hcompanys1'] = new io();
$input['hcompanys1'] -> type = 'select'; 
$input['hcompanys1'] -> addblank = true; 
$input['hcompanys1'] -> aval = rs::id2arr("SELECT ID_HCOMPANY, CODE_HC FROM hcompanys ORDER BY CODE_HC ASC"); 
$input['hcompanys1'] -> css = 'trecento'; 
$input['hcompanys1'] -> id = 'hcompanys1'; 
$input['hcompanys1'] -> txtblank = '- Scegli housing company'; 
$input['hcompanys1'] -> set('hcompanys');

$input['hcompanys2'] = new io();
$input['hcompanys2'] -> type = 'select'; 
$input['hcompanys2'] -> addblank = true; 
$input['hcompanys2'] -> aval = rs::id2arr("SELECT ID_HCOMPANY, CODE_HC FROM hcompanys ORDER BY CODE_HC ASC"); 
$input['hcompanys2'] -> css = 'trecento'; 
$input['hcompanys2'] -> id = 'hcompanys2'; 
$input['hcompanys2'] -> txtblank = '- Scegli housing company'; 
$input['hcompanys2'] -> set('hcompanys');

$input['buildings1'] = new io();
$input['buildings1'] -> type = 'select'; 
$input['buildings1'] -> addblank = true; 
$input['buildings1'] -> aval = rs::id2arr("SELECT ID_BUILDING, CODE_BLD FROM buildings ORDER BY CODE_BLD ASC"); 
$input['buildings1'] -> css = 'trecento'; 
$input['buildings1'] -> id = 'buildings1'; 
$input['buildings1'] -> txtblank = '- Scegli edificio'; 
$input['buildings1'] -> set('buildings');

$input['buildings2'] = new io();
$input['buildings2'] -> type = 'select'; 
$input['buildings2'] -> addblank = true; 
$input['buildings2'] -> aval = rs::id2arr("SELECT ID_BUILDING, CODE_BLD FROM buildings ORDER BY CODE_BLD ASC"); 
$input['buildings2'] -> css = 'trecento'; 
$input['buildings2'] -> id = 'buildings2'; 
$input['buildings2'] -> txtblank = '- Scegli edificio'; 
$input['buildings2'] -> set('buildings');

$input['flats1'] = new io();
$input['flats1'] -> type = 'select'; 
$input['flats1'] -> addblank = true; 
$input['flats1'] -> aval = rs::id2arr("SELECT ID_FLAT, CODE_FLAT FROM flats ORDER BY CODE_FLAT ASC"); 
$input['flats1'] -> aval['media'] = '- media edificio -';
$input['flats1'] -> css = 'trecento'; 
$input['flats1'] -> id = 'flats1'; 
$input['flats1'] -> txtblank = '- Scegli appartamento'; 
$input['flats1'] -> set('flats');

$input['flats2'] = new io();
$input['flats2'] -> type = 'select'; 
$input['flats2'] -> addblank = true; 
$input['flats2'] -> aval = rs::id2arr("SELECT ID_FLAT, CODE_FLAT FROM flats ORDER BY CODE_FLAT ASC"); 
$input['flats2'] -> aval['media'] = '- media edificio -';
$input['flats2'] -> css = 'trecento'; 
$input['flats2'] -> id = 'flats2'; 
$input['flats2'] -> txtblank = '- Scegli appartamento'; 
$input['flats2'] -> set('flats');


$input['graphtype'] = new io();
$input['graphtype'] -> type = 'select'; 
$input['graphtype'] -> addblank = true; 
$input['graphtype'] -> aval = rs::id2arr("SELECT ID, TYPE FROM graphtypes WHERE attivo = 1 ORDER BY TYPE ASC"); 
$input['graphtype'] -> css = 'trecento'; 
$input['graphtype'] -> id = 'graphtype'; 
$input['graphtype'] -> txtblank = '- Scegli tipo grafico'; 
$input['graphtype'] -> set('graphtype');

$input['y1'] = new io();
$input['y1'] -> type = 'select'; 
$input['y1'] -> addblank = true; 
$input['y1'] -> aval = rs::id2arr("SELECT ANNO_MS, ANNO_MS FROM measures ORDER BY ANNO_MS ASC"); 
$input['y1'] -> css = 'trecento dn'; 
$input['y1'] -> id = 'y1'; 
$input['y1'] -> txtblank = '- Scegli anno inizio'; 
$input['y1'] -> set('y');

$input['y2'] = new io();
$input['y2'] -> type = 'select'; 
$input['y2'] -> addblank = true; 
$input['y2'] -> aval = rs::id2arr("SELECT ANNO_MS, ANNO_MS FROM measures ORDER BY ANNO_MS ASC"); 
$input['y2'] -> css = 'trecento dn'; 
$input['y2'] -> id = 'y2'; 
$input['y2'] -> txtblank = '- Scegli anno fine'; 
$input['y2'] -> set('y');

$input['p'] = new io();
$input['p'] -> type = 'select'; 
$input['p'] -> addblank = true; 
$input['p'] -> aval['1'] = 'winter';
$input['p'] -> aval['2'] = 'summer';
$input['p'] -> css = 'trecento dn'; 
$input['p'] -> id = 'period'; 
$input['p'] -> txtblank = '- Scegli periodo'; 
$input['p'] -> set('y');

$input['entype1'] = new io();
$input['entype1'] -> type = 'select'; 
$input['entype1'] -> addblank = true; 
$input['entype1'] -> aval = rs::id2arr("SELECT ID_METERTYPE, METERTYPE_IT FROM metertypes  ORDER BY METERTYPE_IT ASC ");  
$input['entype1'] -> css = 'trecento dn'; 
$input['entype1'] -> id = 'et1'; 
$input['entype1'] -> txtblank = '- Scegli tipo energia'; 
$input['entype1'] -> set('et');

$input['entype2'] = new io();
$input['entype2'] -> type = 'select'; 
$input['entype2'] -> addblank = true; 
$input['entype2'] -> aval = rs::id2arr("SELECT ID_METERTYPE, METERTYPE_IT FROM metertypes  ORDER BY METERTYPE_IT ASC "); 
$input['entype2'] -> css = 'trecento dn'; 
$input['entype2'] -> id = 'et2'; 
$input['entype2'] -> txtblank = '- Scegli tipo energia'; 
$input['entype2'] -> set('et');



include_once HEAD_AR;
# CONFIGURAZIONE 2 DI 2 ##################################################
# FINE CONFIGURAZIONE 2 DI 2 #############################################
?>
<table class="dark">
<tr><th colspan="2">Visualizzazione edifici</th></tr>
<tr><td valign="top" style="width:250px">
<form id="myForm" method="post">
<div id="wizard">
<div id="graph">
<span>Graph type</span>
<? $input['graphtype'] -> get(); ?>
<?  $input['y1'] -> get(); ?>
<? $input['y2'] -> get(); ?>
<? $input['p'] -> get(); ?>
</div>
<div id="wizard1" style="display:none;">
<span>First entity</span>
<? if(count($input['federations1'] -> aval) > 1) $input['federations1'] -> get(); ?>
<? if(count($input['hcompanys1'] -> aval) > 1) $input['hcompanys1'] -> get(); ?>
<? if(count($input['buildings1'] -> aval) > 1) $input['buildings1'] -> get(); ?>
<? $input['flats1'] -> get(); ?>
<? $input['entype1'] -> get(); ?>
</div>
<div id="wizard2" style="display:none;">
<span>Second entity</span>
<? if(count($input['federations2'] -> aval) > 1) $input['federations2'] -> get(); ?>
<? if(count($input['hcompanys2'] -> aval) > 1) $input['hcompanys2'] -> get(); ?>
<? if(count($input['buildings2'] -> aval) > 1) $input['buildings2'] -> get(); ?>
<? $input['flats2'] -> get(); ?>
<? $input['entype2'] -> get(); ?>
</div>


<input type="button" value="Demo grafico" onclick="grafico();" />
</div>
</form>
</td><td id="content_map">
<div id="wizard_right">
<?php
print $html['gmaps'];
?>
</div>
<div class="clear"></div>
</td></tr>
<tr>
<th colspan="2">* campi obbligatori
</th>
</tr>
</table>

<div id="grafico" title="Comsumption graph">
	<img  style="margin:10px 12px;" id="graphimage" src="" />
</div>

<script>
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
			
	$(document).ready(function()	{
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
					selectFLAT(1);
				
		});
		
		$('#federations2').change(function(){
					 $('#hcompanys2').html('');
					 $('#buildings2').html('');
					aggiornaMappa(2);
					selectHC(2);
					selectBLD(2);
				});
		 $('#hcompanys2').change(function(){
					$('#buildings2').html('');
					aggiornaMappa(2);
					selectBLD(2);
		});
		$('#buildings2').change(function(){
					aggiornaMappa(2);
					selectFLAT(2);
				
		});
		
		
		$('#graphtype').change(function() {
			id = $('#graphtype').val();
			jQuery.getJSON("ajax/json.php?action=get_graph_info&id=" + id ,
			function(data){
				if(data.ann1==1)	
					$('#y1').css('display', 'block');
				
				else 
					$('#y1').css('display', 'none');
				
				if(data.ann2==1)	
					$('#y2').css('display', 'block');
				else 
					$('#y2').css('display', 'none');
				
				if(data.periodo==1)	
					$('#period').css('display', 'block');
				else 
					$('#period').css('display', 'none');
					
				if(data.tipo1==1)	
					$('#et1').css('display', 'block');
				else 
					$('#et1').css('display', 'none');
				
				if(data.tipo2==1)	
					$('#et2').css('display', 'block');
				else 
					$('#et2').css('display', 'none');
				
				if(data.ed1==1)	{
					$('#wizard1').css('display', 'block');
					if(data.app1==1)
						$('#wizard1 #flats1').css('display', 'block');
					else 
						$('#wizard1 #flats1').css('display', 'none');
					if(data.mis1==1)
						$('#wizard1 #meters1').css('display', 'block');
					else 
						$('#wizard1 #meters1').css('display', 'none');
				}
				else
					$('#wizard1').css('display', 'none');
				if(data.ed2==1){
					$('#wizard2').css('display', 'block');
					if(data.app2==1)
						$('#wizard2 #flats2').css('display', 'block');
					else 
						$('#wizard2 #flats2').css('display', 'none');
					if(data.mis2==1)
						$('#wizard2 #meters2').css('display', 'block');
					else 
						$('#wizard2 #meters2').css('display', 'none');
				}
				else
					$('#wizard2').css('display', 'none');
				
			  
			});
		});
		
		
		$("#grafico").dialog({
						autoOpen: false,
						height: 620,
						width: 950,
						modal: true,
						buttons: {
							
							Close: function() {
								$(this).dialog('close');
							}
						},
						close: function() {
							//allFields.val('').removeClass('ui-state-error');
						}
					});
		
		
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
		 
		  //map.setCenter(cornice.getCenter());
        });
	}
	
	function selectFLAT(id)	{
		hcompanys = $('#hcompanys' + id).val();
		federation = $('#federations' + id).val();
		building = $('#buildings' + id).val();
		jQuery.getJSON("ajax/json.php?action=select_flat&hc=" + hcompanys + "&fed=" + federation + "&bld=" + building,
        function(data){
			options = '<option selected="selected" value="">- Scegli appartamento</option>	' + "\n\r";
			
          jQuery.each(data, function(i,item){
				options += '<option  value="' + item.optionValue + '">' + item.optionDisplay + '</option>' + "\n\r";
				
				
          });
		  options += '<option value="media">- media -</option>	' + "\n\r";
		  $('#flats' + id).html(options);
		 
		  //map.setCenter(cornice.getCenter());
        });
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
				addMarker(coordinate);
				
				cornice.extend(coordinate);
			});
			
		  
		  if (!cornice.isEmpty())
			map.fitBounds(cornice);
			showOverlays();
		  //map.setCenter(cornice.getCenter());
        });
		
	}
	
	
	function grafico()	{
		type = $('#graphtype').val();
		flat1 = $('#flats1').val();
		flat2 = $('#flats2').val();
		b1 = $('#buildings1').val();
		b2 = $('#buildings2').val();
		y1 = $('#y1').val();
		y2 = $('#y2').val();
		p = $('#period').val();
		$("#grafico #graphimage").attr('src', 'ajax/graph.php?t=' + type + '&f1='+flat1+'&f2='+flat2+ '&b1='+b1+'&b2='+b2 + '&y1='+y1+'&y2='+y2+'&p='+p);
		$("#grafico").dialog('open');
	
	}
	

</script>

<?php
include_once FOOTER_AR;
?>