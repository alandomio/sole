<?php
include_once '../init.php';
list($id, $year, $read) = request::get(array('id' => NULL, 'year' => NULL, 'read' => NULL));

if(array_key_exists('choose', $_POST)){
	io::headto($MYFILE -> file, array('year' => $_POST['year'], 'read' => $_POST['read'], 'id' => $id));
}


$qB = "SELECT BUILDING FROM buildings WHERE ID_BUILDING = '$id' LIMIT 0,1";
$rB = rs::rec2arr($qB);
$hB = io::a('index.php', array(), $rB['BUILDING'], array());

$html['options_year'] = '';

$y = date('Y', time());
for($i = $y; $i >= ($y - 1); $i--){
	$html['options_year'] .= '<option value="'.$i.'">'.$i.'</option>';

}

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
	io::headto('controls.php', array('id' => $id));
}

include 'head.php';
?>
<div class="content-secondary"> 
<?=$html['LIST_METERS']?>
</div>
<?php
include 'footer.php';
?>