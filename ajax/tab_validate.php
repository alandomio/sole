<?php
include_once '../init.php';

$val = convalida::mk_validation($_REQUEST['id'],$_REQUEST['upload'],$_REQUEST['year']);
$periodo = convalida::lunghezza_periodo($_REQUEST['id'],$_REQUEST['upload']);
$val_shared = convalida::mk_validation_shared($_REQUEST['id'],$_REQUEST['upload'],$_REQUEST['year']);
//$val_shared = convalida::mk_validation_shared($_REQUEST['id'],$_REQUEST['upload'],$_REQUEST['year']);



echo '<span style="display:none;" id="periodlength">'.$periodo.'</span>';
echo $val['html'];
echo '<div class="fix"></div>';
echo $val_shared['html'];
echo '<div class="fix"></div>';



if($val['pubblicato'] || $val_shared['pubblicato'] )
	$cancella = "\nabilitaCancPubblicazione(); \n";

if(sole::is_building_monolocale($_REQUEST['id']))
	$step = 5;
else
	$step = 100;
	
//if($val['validato'] || $val_shared['validato'] )
//	$pubblica = "\nabilitaPubblicazione(); \n";

?>
<script>
var allpublished = <?php echo ($val['allpublished']&&$val_shared['allpublished'])?'true':'false'?>;

$(document).ready(function(){

	$('.slider').slider({ 
		animate: "fast",
		min:0, 
		max:100, 
		step:<?echo $step;?>,
		create: function(event, ui)	{
			//console.log(event);
				var slider = event.target;
				var periodDays = parseInt($('#periodlength').html());
				var idAppartamento = $(slider).parents('table.appartamento').first().attr('id');
				if( $(slider).hasClass('dn'))	{
					$(slider).slider('disable');
				}
					
				
				percentuale = $(slider).parent().find('form input[name="cknumid"]').first().is(':checked') * 100;
				console.log(idAppartamento + ': ' + percentuale);

				//var divSlider = $(this).parent().find('.slider').first();
				console.log(slider);
				var occupancy = parseInt($(slider).attr('value'));
				console.log(occupancy);
				if( occupancy>0 || occupancy<100)
					$(slider).slider('value', occupancy); // imposto il valore dello slider in base al valor caricato dal DB
				
	
			},
		slide: function(event, ui)	{
				slider = event.target;
				$(slider).parent().find('span.slidervalue').html($(slider).slider('value') + '%');
			},
		change: function(event, ui)	{
				slider = event.target;
				$(slider).parent().find('span.slidervalue').html($(slider).slider('value') + '%');
				//alert('ok');
				//aggiornaMedieCorrette();
			},	
			
			
	});
	
	<?php echo $pubblica . $cancella; ?>
});


</script>