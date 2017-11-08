<?php
include_once '../init.php';
list($id, $year, $read) = request::get(array('id' => NULL, 'year' => NULL, 'read' => NULL));

$qB = "SELECT BUILDING FROM buildings WHERE ID_BUILDING = '$id' LIMIT 0,1";
$rB = rs::rec2arr($qB);
$hB = io::a('index.php', array(), $rB['BUILDING'], array());

if(!empty($year) && !empty($read)){ # LISTA CONTATORI
	$aM = sole::get_meters_by_idbuilding($id);
	$li = '<li data-role="list-divider">'.$rB['BUILDING'].' &rsaquo; Meters list </li>';
	
	foreach($aM as $k => $v){
		$h = io::a('measures.php', array('id' => $v['ID_BUILDING'], 'idm' => $v['ID_METER']), $v['MATRICULA_ID'].' '.$v['ID_METERPROPERTY'], array());
		$li .= mytag::in($h, 'li', array());
	}
	$html['LIST_METERS'] = mytag::in($li, 'ul', array('data-role' => 'listview', 'data-inset' => 'true', 'data-theme' => 'a', 'data-dividertheme' => 'a', 'data-transition' => 'pop'));
}
else{ # FORM ANNO E NUMERO LETTURA
	ob_start();
	?>
	<p class="intro"><strong>Scelta dati</strong></p> 
	<?
	print $MYFILE -> system_errors;
	$MYFILE -> print_msg(true);
	?>
	<form action="meters.php" id="fmeters" method="post" data-ajax="false">

    <label for="date">Date Input:</label>
    <input type="date" name="date" id="date" value="" data-theme="a" />
    
    
	<input type="submit" name="choose" value="OK" data-theme="a" />
	</form>
	<?php
	$html['LIST_METERS'] = ob_get_clean();
}

include 'head.php';
?>
<div class="content-secondary"> 
<?=$html['LIST_METERS']?>
</div>
<?php
include 'footer.php';
?>