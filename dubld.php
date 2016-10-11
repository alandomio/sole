<?php
include_once 'init.php';

$user = new autentica($aA4);
$user -> login_standard();

function array2insert($rs){
	
	$fields = ''; $values = ''; $ret = array();
	foreach($rs as $field => $value){
		//if(!empty($value)){
			$fields .= $field.", ";
			$values .= "'".prepare4sql($value)."', ";
		//}
	}
	$ret['fields'] = "(".substr($fields,0, -2).")";
	$ret['values'] = "(".substr($values,0, -2).")";
	return $ret;
}

$aDuplica = array(43);
$copie = 1;
$execute = true;

include_once HEAD_AR;

foreach($aDuplica as $k => $id_building){
	$qb = "SELECT * FROM buildings WHERE ID_BUILDING='$id_building' LIMIT 1";
	$rb = rs::rec2arr($qb);
	
	$qf = "SELECT * FROM flats WHERE ID_BUILDING='$id_building' LIMIT 1";
	$rf = rs::inMatrix($qf);
	
	for($i=1; $i<=$copie; $i++){
		
		$aMeters = array();
		
		// duplico edificio
		$old = $rb;
		unset($old['ID_BUILDING']);	
		// calcolo il codice edificio
		$aName = sole::mk_namebuilding($old['ID_HCOMPANY'], $old['NAME_BLD']);
		$old['CODE_BLD'] = $aName['fullname'];
		unset($aName);
		// $old['CODE_BLD'] = 'P01.00'.($i+2).'.'.$old['NAME_BLD'];
		
		$sql = array2insert($old);
		$qInsertBuilding = "INSERT INTO buildings ".$sql['fields']." VALUES ".$sql['values'];
		
		if($execute){
			mysql_query($qInsertBuilding);
			$newIdBuilding = mysql_insert_id();
			echo $qInsertBuilding.BR;
		} else {
			echo $qInsertBuilding.BR;
			$newIdBuilding = 'newIdBuilding';
		}
	
		// duplico appartamenti
		foreach($rf as $kk => $flat){
			$old_idFlat = $flat['ID_FLAT'];
			unset($flat['ID_FLAT']);
			$flat['ID_BUILDING'] = $newIdBuilding;
			$flat['ID_USER'] = 47;
			$sql = array2insert($flat);
			$qInsertFlat = "INSERT INTO flats ".$sql['fields']." VALUES ".$sql['values'];
			if($execute){
				mysql_query($qInsertFlat);
				$newIdFlat = mysql_insert_id();
				echo $qInsertFlat.BR;
			} else {
				echo $qInsertFlat.BR;
				$newIdFlat = 0;
			}
			
			// duplico misuratore
			$qmf = "SELECT * FROM flats_meters WHERE flats_meters.ID_FLAT='$old_idFlat'";
			$rmf = rs::inMatrix($qmf);
			
			foreach($rmf as $kkk => $flats_meters){
				
				$qm = "SELECT * FROM meters	WHERE meters.ID_METER='".$flats_meters['ID_METER']."' LIMIT 1";
				$rm = rs::rec2arr($qm);
				$rm['ID_BUILDING'] = $newIdBuilding;

				if(array_key_exists($flats_meters['ID_METER'], $aMeters)){
					// misuratore già inserito, inserisco solo il riferimento all'appartamento
					$qFlatsMeters = "INSERT INTO flats_meters (ID_METER, ID_FLAT) VALUES ('".$aMeters[$flats_meters['ID_METER']]."', '$newIdFlat')";
					
					if($execute){
						mysql_query($qFlatsMeters);
						echo $qFlatsMeters.BR;
					} else {
						echo $qFlatsMeters.BR;
					}				
				} else {
					// inserisco il misuratore
					$old_idMeter = $rm['ID_METER'];
					unset($rm['ID_METER']);
					
					
					$qMetersProductions = "SELECT * FROM meters_productions WHERE ID_METER='".$old_idMeter."'";
					$rMetersProductions = rs::rec2arr($qMetersProductions);
					
					
					$sql = array2insert($rm);
					$qInsertMeter = "INSERT INTO meters ".$sql['fields']." VALUES ".$sql['values'];
					
					if($execute){
						
						mysql_query($qInsertMeter);
						$newIdMeter = mysql_insert_id();
						$qFlatsMeters = "INSERT INTO flats_meters (ID_METER, ID_FLAT) VALUES ('".$newIdMeter."', '$newIdFlat')";
						mysql_query($qFlatsMeters);
						
						if(!empty($rMetersProductions['ID_METER'])){
							$rMetersProductions['ID_METER'] = $newIdMeter;
							$sql = array2insert($rMetersProductions);
							$qInsertMeterProd = "INSERT INTO meters_productions ".$sql['fields']." VALUES ".$sql['values'];
							mysql_query($qInsertMeterProd);
							echo $qInsertMeterProd.BR;
						}
						
						echo $qInsertMeter.BR;
						echo $qFlatsMeters.BR;
						
					} else {
						echo $qInsertMeter.BR;
						$newIdMeter = 0;
						
						if(!empty($rMetersProductions['ID_METER'])){
							$rMetersProductions['ID_METER'] = $newIdMeter;
							$sql = array2insert($rMetersProductions);
							$qInsertMeterProd = "INSERT INTO meters_productions ".$sql['fields']." VALUES ".$sql['values'];
							echo $qInsertMeterProd.BR;
						}
						
						echo $qFlatsMeters = "INSERT INTO flats_meters (ID_METER, ID_FLAT) VALUES ('".$newIdMeter."', '$newIdFlat')".BR;
					}
					$aMeters[$old_idMeter] = $newIdMeter;
				}
			}
		}
	}
}

include_once FOOTER_AR;
?>