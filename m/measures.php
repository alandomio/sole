<?php
include_once '../init.php';
list($id, $idm) = request::get(array('id' => NULL, 'idm' => NULL));

$qB = "SELECT BUILDING FROM buildings WHERE ID_BUILDING = '$id' LIMIT 0,1";
$rB = rs::rec2arr($qB);
$hB = io::a('index.php', array(), $rB['BUILDING'], array());

$qM = "SELECT MATRICULA_ID, ID_METERPROPERTY FROM meters WHERE ID_METER = '$idm' LIMIT 0,1";
$rM = rs::rec2arr($qB);
$hM = io::a('meters.php', array(), $rB['BUILDING'], array());


$aM = get_meters_by_idbuilding($id);
$li = '<li data-role="list-divider">'.$rB['BUILDING'].' &rsaquo; Meters list </li>';

foreach($aM as $k => $v){
	$h = io::a('measures.php', array('id' => $v['ID_BUILDING'], 'idm' => $v['ID_METER']), $v['MATRICULA_ID'].' '.$v['ID_METERPROPERTY'], array());
	$li .= mytag::in($h, 'li', array());
}
$html['LIST_METERS'] = mytag::in($li, 'ul', array('data-role' => 'listview', 'data-inset' => 'true', 'data-theme' => 'a', 'data-dividertheme' => 'a', 'data-transition' => 'pop'));







include 'head.php';
?>
<div class="content-secondary"> 
<?=$html['LIST_METERS']?>
</div>
<?php
include 'footer.php';
?>