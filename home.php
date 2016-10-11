<?php
# V.0.1.8
include_once 'init.php';
list($il, $jsstatis) = request::get(array('il' => NULL,'jsstatis' => NULL));

$user = new autentica($aA5);
$user -> login_standard();

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
		  
	get_user_buildings_coords('.$user -> aUser['ID_USER'].');
  }
</script>
', 'code', 'head');
$initialize = $body_ini;

$html['gmaps'] = '<div id="map_canvas"></div>
';

$html['wizard_1'] = sole::select_fhb('1', 'select_flat');
$html['wizard_2'] = sole::select_fhb('2', 'select_flat');

$graph_name = 'TYPE_'.$lang -> def;
if($lang -> def == 'IT'){
	$graph_name = 'TYPE';
}

# SELECT FEDERATIONS
$input['graphtype'] = new io();
$input['graphtype'] -> type = 'select'; 
$input['graphtype'] -> addblank = true; 
$input['graphtype'] -> aval = rs::id2arr("SELECT ID, ".$graph_name." FROM graphtypes WHERE attivo = 1 ORDER BY ".$graph_name." ASC"); 

if($user -> idg == 5){ // hhu non può visualizzare alcuni tipi di grafici
	unset($input['graphtype'] -> aval[7]);  // Periodo e tipo di fornitura
	unset($input['graphtype'] -> aval[11]); // Report PDF
}

$input['graphtype'] -> css = 'trecento'; 
$input['graphtype'] -> id = 'graphtype'; 
$input['graphtype'] -> txtblank = '- '.CHOOSE_GRAPHIC; 
$input['graphtype'] -> set('graphtype');

$input['y1'] = new io();
$input['y1'] -> type = 'select'; 
$input['y1'] -> addblank = true; 
$input['y1'] -> aval = rs::id2arr("SELECT ANNO_MS, ANNO_MS FROM measures ORDER BY ANNO_MS ASC"); 
$input['y1'] -> css = 'trecento dn'; 
$input['y1'] -> id = 'y1'; 
$input['y1'] -> txtblank = '- '.CHOOSE_INITIAL_YEAR; 
$input['y1'] -> set('y');

$input['y2'] = new io();
$input['y2'] -> type = 'select'; 
$input['y2'] -> addblank = true; 
$input['y2'] -> aval = rs::id2arr("SELECT ANNO_MS, ANNO_MS FROM measures ORDER BY ANNO_MS ASC"); 
$input['y2'] -> css = 'trecento dn'; 
$input['y2'] -> id = 'y2'; 
$input['y2'] -> txtblank = '- '.CHOOSE_END_YEAR; 
$input['y2'] -> set('y');

$input['p'] = new io();
$input['p'] -> type = 'select'; 
$input['p'] -> addblank = true; 
$input['p'] -> aval['1'] = 'winter';
$input['p'] -> aval['2'] = 'summer';
$input['p'] -> css = 'trecento dn'; 
$input['p'] -> id = 'period'; 
$input['p'] -> txtblank = '- '.CHOOSE_PERIOD; 
$input['p'] -> set('y');

$input['entype1'] = new io();
$input['entype1'] -> type = 'select'; 
$input['entype1'] -> addblank = true; 
$input['entype1'] -> aval = rs::id2arr("SELECT ID_METERTYPE, METERTYPE_".LANG_DEF." FROM metertypes  ORDER BY METERTYPE_".LANG_DEF." ASC ");  
$input['entype1'] -> css = 'trecento dn'; 
$input['entype1'] -> id = 'et1'; 
$input['entype1'] -> txtblank = '- '.CHOOSE_ENERGY_TYPE; 
$input['entype1'] -> set('et');

$input['entype2'] = new io();
$input['entype2'] -> type = 'select'; 
$input['entype2'] -> addblank = true; 
$input['entype2'] -> aval = rs::id2arr("SELECT ID_METERTYPE, METERTYPE_".LANG_DEF." FROM metertypes  ORDER BY METERTYPE_".LANG_DEF." ASC "); 
$input['entype2'] -> css = 'trecento dn'; 
$input['entype2'] -> id = 'et2'; 
$input['entype2'] -> txtblank = '- '.CHOOSE_ENERGY_TYPE; 
$input['entype2'] -> set('et');

//'ajax/report.php';

$link_last_report = ''; $btn_all_report = '';
if($user -> idg == 5){ // HHU
	$link_last_report = io::a('ajax/report.php', array('id' => $user -> aUser['CODE']), DOWNLOAD_REPORT, array('class' => 'g-button g-button-yellow'));
} else {
	$btn_all_report = 
	'<input type="button" value="'.DOWNLOAD_ALL_REPORT.'" class="g-button g-button-yellow m-top dn" id="all_reports" />';
	
	//io::a('ajax/report.php', array('tipo' => 'multiplo'), DOWNLOAD_ALL_REPORT, array('class' => 'g-button g-button-yellow dn', 'id' => 'all_reports', 'style' => 'font-weight:bold; font-size:11px'));
}

if(array_key_exists('first_login', $_GET)){
	if (isset($_SERVER['HTTP_USER_AGENT'])) {
		$agent = trim($_SERVER['HTTP_USER_AGENT']);
	}
	if(!empty($agent) && $user -> idg < 5){
		
		$browsers = ' <a href="http://www.google.it/search?q=Google+Chrome+download">Chrome</a> <a href="http://www.google.it/search?q=Mozilla+Firefox+download">Firefox</a> <a href="http://www.google.it/search?q=safari+download">Safari</a>';
	
		if(strpos($agent, 'Firefox') === false && strpos( $agent, 'Chrome') === false){
			$MYFILE -> add_err(ERR_BROWSER.$browsers);
		}
	}
}

include_once HEAD_AR;
?>
<table class="dark">
<tr><th colspan="2"><?=$MYFILE -> title?></th></tr>
<tr><td valign="top" style="width:250px">
<form id="myForm" method="post">

<div id="wizard">
<div id="graph">
<span><?=GRAPH_TYPE?></span>
<? $input['graphtype'] -> get(); ?>
<? $input['y1'] -> get(); ?>
<? $input['y2'] -> get(); ?>
<? $input['p'] -> get(); ?>
</div>
<div id="wizard1" style="display:none;">
<span><?=FIRST_ENTITY?></span>
<?=$html['wizard_1']?>
<? $input['entype1'] -> get(); ?>
</div>
<div id="wizard2" style="display:none;">
<span><?=SECOND_ENTITY?></span>
<?=$html['wizard_2']?>
<? $input['entype2'] -> get(); ?>
</div>

<input type="button" value="<?=GRAPH_TYPE?>" class="g-button g-button-yellow m-top" id="button_grafico" />
<?=$btn_all_report?>
<?=BR.BR.$link_last_report?>
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

</table>

<div id="grafico" title="<?=CONS_GRAPH?>">
	<img  style="margin:10px 12px;" id="graphimage" src="" />
</div>

<script>
	$('[name="ADDRESS_BLD"]').change(function() {
				var comune = ($('[name="K1_ID_COMUNI"] option:selected').text());
				var provincia = ($('[name="K1_ID_PROVINCE"] option:selected').text());
				var indirizzo = $('[name="ADDRESS_BLD"]').val() + ' ' + comune + ' ' + provincia;
				
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
					selectEnergy(1);
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
					selectEnergy(2);
		});
		
		$('#graphtype').change(function() {
			id = $('#graphtype').val();
			jQuery.getJSON("ajax/json.php?action=get_graph_info&id=" + id ,
			function(data){
				
				if(data.pdf==1){
					$('#button_grafico').val('<?=REPORT_PDF?>').unbind().click(function(){pdf();});
					
					$('#all_reports').click(function(){all_pdf();});
					$('#all_reports').removeClass('dn');
				} else {
					$('#button_grafico').val('<?=GRAPH_TYPE?>').unbind().click(function(){grafico();});
					$('#all_reports').addClass('dn');
				}
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
			options = '<option selected="selected" value="">- <?=CHOOSE_FLAT?></option>	' + "\n\r";
			
          jQuery.each(data, function(i,item){
				options += '<option  value="' + item.optionValue + '">' + item.optionDisplay + '</option>' + "\n\r";
				
				
          });
		  options += '<option value="media">- media -</option>	' + "\n\r";
		  $('#flats' + id).html(options);
		 
		  //map.setCenter(cornice.getCenter());
        });
	}
	
	function selectEnergy(id)	{
		hcompanys = $('#hcompanys' + id).val();
		federation = $('#federations' + id).val();
		building = $('#buildings' + id).val();
		jQuery.getJSON("ajax/json.php?action=select_energy&hc=" + hcompanys + "&fed=" + federation + "&bld=" + building,
        function(data){
			options = '<option selected="selected" value="">- <?=CHOOSE_ENERGY_TYPE?></option>	' + "\n\r";
			
          jQuery.each(data, function(i,item){
				options += '<option  value="' + item.optionValue + '">' + item.optionDisplay + '</option>' + "\n\r";
				
				
          });

		  $('#et' + id).html(options);
		 
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
        }); 
	}
	
	function get_user_buildings_coords(id)	{
		jQuery.getJSON("ajax/json.php?action=get_user_buildings_coords&id=" + id,
			function(data){
				var cornice = new google.maps.LatLngBounds();
				deleteOverlays();
			    var myOptions = {
			    	      zoom: 5,
			    	      mapTypeId: google.maps.MapTypeId.ROADMAP
			    	    };
				
				map = new google.maps.Map(document.getElementById("map_canvas"),myOptions);
				var infowindow = new google.maps.InfoWindow();
				
				$.each(data, function(i,item){
					coordinate = new google.maps.LatLng(parseFloat(item.lat), parseFloat(item.lng));

				marker = new google.maps.Marker({
					position: coordinate,
					title: 'Hello world!',
					
					map: map
				});
				google.maps.event.addListener(marker, 'click', (function(marker, i) {
				  return function() {
					 infowindow.setContent('<strong>'+item.name+'<br>'+item.code+'</strong><br><br><a href="info-building.php?id='+item.id+'">Open</a>');
					 infowindow.open(map, marker);
				  }
				})(marker, i));
				
					//addMarker(coordinate);
					/*addMarker(
						coordinate,
						{
							html: 'Hello world!'
						});*/
					cornice.extend(coordinate);
				});

				if (!cornice.isEmpty()){
					map.fitBounds(cornice);
				}
				showOverlays();
			});
	}	
	
	function grafico(){
		type = $('#graphtype').val();
		flat1 = $('#flats1').val();
		flat2 = $('#flats2').val();
		b1 = $('#buildings1').val();
		b2 = $('#buildings2').val();
		y1 = $('#y1').val();
		y2 = $('#y2').val();
		p = $('#period').val();
		et = $('#et1').val();
		
		$("#grafico #graphimage").attr('src', 'ajax/graph.php?t=' + type + '&f1='+flat1+'&f2='+flat2+ '&b1='+b1+'&b2='+b2 + '&y1='+y1+'&y2='+y2+'&p='+p+'&et='+et);
		$("#grafico").dialog('open');
	}
	
	function pdf()	{
		flat1 = $('#flats1').val();
		y1 = $('#y1').val();
		//console.log(y1);
		//console.log(y1.length);
		if(  flat1.length <= 0 ){
			alert('Choose flat');
		} else {
			document.location.href="ajax/report.php?y1=" + y1 + "&y2=" + y1 + "&f1=" + flat1;
		}
	}	
	
	function all_pdf()	{
		flat1 = $('#flats1').val();
		y1 = $('#y1').val();
		bld = $('#buildings1').val();
		document.location.href="ajax/report.php?y1=" + y1 + "&bld=" + bld + "&f1=" + flat1 + "&tipo=multiplo";
	}
</script>
<?php
include_once FOOTER_AR;
?>