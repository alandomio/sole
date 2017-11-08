<?php
include_once 'init.php';
$user = new autentica($aA5);
$user -> login_standard();

# elimina le misurazioni di un edificio
/* $r = sole::get_real_by_bld(26);
foreach($r as $k => $meter){
	mysql_query( $q = "DELETE FROM measures WHERE ANNO_MS = '2010' AND ID_UPLOADTYPE = '1' AND ID_METER = '{$meter['ID_METER']}'");
	
	mysql_query( $q = "DELETE FROM measures WHERE ANNO_MS = '2010' AND ID_UPLOADTYPE = '2' AND ID_METER = '{$meter['ID_METER']}'");
} */

# aggiorna id_building su meters
$q = "SELECT ID_METER FROM meters WHERE ID_BUILDING IS NULL";
$r = rs::inMatrix($q);

foreach($r as $k => $v){
	$id_building = sole::get_id_building_from_id_meter($v['ID_METER']);
	if($id_building){
		$q = "UPDATE meters SET ID_BUILDING = '$id_building' WHERE ID_METER = '{$v['ID_METER']}'";
		mysql_query($q);
		//echo BR;
	}
}

/*
UPDATE meters SET ID_BUILDING = '26' WHERE ID_METER = '1802' ACQ_C_c_civ.6  -  HC4.001.Bellocchi 15
UPDATE meters SET ID_BUILDING = '26' WHERE ID_METER = '1803'
UPDATE meters SET ID_BUILDING = '26' WHERE ID_METER = '1804'
UPDATE meters SET ID_BUILDING = '26' WHERE ID_METER = '1805'


1917
635
1111
1100
1606
1089
1031

*/
?>