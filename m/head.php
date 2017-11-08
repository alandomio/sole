<?php
$aFiles = array(
	'buildings.php' => 'controls.php',
	'controls.php' => 'choose-data.php',
	'choose-data.php' => 'meters.php',
	'meters.php' => 'measures.php'
	);

$aPrec = array_flip($aFiles);

$aT = array(
	'buildings.php' => BUILDINGS,
	'controls.php' => 'Check dates',
	'choose-data.php' => 'Choose data',
	'meters.php' => METERS
	);

$hPrec = array_key_exists($MYFILE -> file, $aPrec) ? io::a($aPrec[$MYFILE -> file], array(), 'Back', array('data-icon' => 'back', 'data-rel'=> 'back')) : '';
$hLogout = io::a('index.php', array('action' => 'logout'), 'Logout', array('data-icon' => 'home', 'class' => 'ui-btn-right', 'data-transition' => 'fade'));


/*<script src="http://code.jquery.com/jquery-1.6.1.min.js"></script> 
*/

$MYFILE -> catch_buffer();
?>
<!DOCTYPE html> 
<html> 
<head> 
<meta charset="utf-8"> 
<meta name="viewport" content="width=device-width, initial-scale=1"> 
<title>Sole Project Mobile</title> 
<link rel="stylesheet"  href="http://code.jquery.com/mobile/1.0b1/jquery.mobile-1.0b1.min.css" /> 
<link rel="stylesheet" href="jquery.ui.datepicker.mobile.css" /> 
<script src="http://code.jquery.com/jquery-1.6.1.min.js"></script>
<script src="jQuery.ui.datepicker.js"></script>
<script src="jquery.ui.datepicker.mobile.js"></script>
<script>
	$(document).bind('mobileinit',function(){
		$.mobile.selectmenu.prototype.options.nativeMenu = false;
	});
	//reset type=date inputs to text
	$( document ).bind( "mobileinit", function(){
	$.mobile.page.prototype.options.degradeInputs.date = true;
	});	
</script>
<script src="http://code.jquery.com/mobile/1.0b1/jquery.mobile-1.0b1.min.js"></script> 
</head> 
<body style="background-color:#000000">
<?php
?>
<div data-role="page" data-theme="a" id="jqm-home" class="type-home"> 
<div data-role="header" data-position="inline">
<?=$hPrec?>
<h1><?=$aT[$MYFILE -> file]?></h1>
<?=$hLogout?>
</div>
<div data-role="content">
<?php
print $MYFILE -> system_errors;
$MYFILE -> print_msg(true);
?>