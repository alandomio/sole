<?php
include_once 'init.php';

//$user = new autentica($aA4);
//$user -> login_standard();

//include_once HEAD_AR;
$q = "SELECT ID_METER, CODE_METER, FORMULA, A, B FROM meters";
$r = rs::inMatrix($q);

$a = array('FORMULA', 'A', 'B');

foreach($r as $k => $v){
	if(strpos($v['CODE_METER'], '.') !== false){
 		//echo $v['CODE_METER'].BR;
		$new_code = str_replace('.', '_', $v['CODE_METER']);
		
		// AGGIORNO I NOMI PRINCIPALI
		$upd = "UPDATE meters SET CODE_METER = '$new_code' WHERE ID_METER = '{$v['ID_METER']}'";
 		if(mysql_query ($upd )){
			echo $upd . BR;
		} 
		
		//echo $new_code .BR;
		
		foreach($a as $kk => $field){
			$qf = "SELECT ID_METER, $field FROM meters WHERE $field LIKE '%{$v['CODE_METER']}%'";
			$rf = rs::inMatrix($qf); 
			
 			if(count($rf)>0){
				foreach($rf as $kkk => $vvv){
					$new_val = str_replace($v['CODE_METER'], $new_code, $vvv[$field]);
					
					$upd = "UPDATE meters SET $field = '$new_val' WHERE ID_METER = '{$vvv['ID_METER']}'";
					if(mysql_query ($upd )){
						echo $upd . BR;
						break;
					}
				}
			} 
		} 
	}
}

//include_once FOOTER_AR;
?>