<?php
include_once 'init.php';

$user = new autentica($aA5);
$user -> login_standard();

if(array_key_exists('action', $_POST)){
	$action = $_POST['action'];
	$id = $_POST['id'];
	$esegui = true;
	
	if($action == 'remove_hc_all'){
		$qUpdateUsers = "UPDATE users SET ID_HCOMPANY=NULL WHERE ID_HCOMPANY='$id'";
		if($esegui){
			mysql_query($qUpdateUsers);
		} else {
			echo $qUpdateUsers.BR;
		}
		
		// lista edifici
		$aBld = sole::get_buildings_from_hc($id);
		
		foreach($aBld as $bld){
			// lista appartamenti
			$aFlats = sole::get_flats_by_idbuilding($bld['ID_BUILDING']);
			foreach($aFlats as $flat){
				// lista contatori
				$aMeters = sole::get_meters_all_by_idflat($flat['ID_FLAT']);
				foreach($aMeters as $meter){
					// elimino le misurazioni
					$qDeleMeasures = "DELETE FROM measures WHERE ID_METER='{$meter['ID_METER']}'";
					// elimino i dati dei consumi
					$qDeleConsumption = "DELETE FROM consumptions WHERE ID_METER = '{$meter['ID_METER']}'";
					// elimino i misuratori di produzione
					$qDeleMeterProduction = "DELETE FROM meters_productions WHERE ID_METER = '{$meter['ID_METER']}'";
					// elimino il misuratore
					$qDeleMeter = "DELETE FROM meters WHERE ID_METER = '{$meter['ID_METER']}'";
					
					if($esegui){
						mysql_query($qDeleMeasures);
						mysql_query($qDeleConsumption);
						mysql_query($qDeleMeterProduction);
						mysql_query($qDeleMeter);
					} else {
						echo $qDeleMeasures.BR;
						echo $qDeleConsumption.BR;
						echo $qDeleMeterProduction.BR;
						echo $qDeleMeter.BR;
					}						
				}
				
				// elimino l'appartamento
				$qDeleFlatMeter = "DELETE FROM flats_meters WHERE ID_FLAT='{$flat['ID_FLAT']}'";
				$qDeleFlatMsoutputs = "DELETE FROM msoutputs WHERE ID_FLAT='{$flat['ID_FLAT']}'";
				$qDeleOccupancys = "DELETE FROM occupancys WHERE ID_FLAT='{$flat['ID_FLAT']}'";
				$qDeleFlat = "DELETE FROM flats WHERE ID_FLAT = '{$flat['ID_FLAT']}'";
				
				if($esegui){
					mysql_query($qDeleFlatMeter);
					mysql_query($qDeleFlatMsoutputs);
					mysql_query($qDeleOccupancys);
					mysql_query($qDeleFlat);
				} else {
					echo $qDeleFlatMeter.BR;
					echo $qDeleFlatMsoutputs.BR;
					echo $qDeleOccupancys.BR;
					echo $qDeleFlat.BR;
				}				
			}
			// elimino l'edificio e info
			$qDeleBldFiles = "DELETE FROM buildings_files WHERE ID_BUILDING = '{$bld['ID_BUILDING']}'";
			$qDeleBldUsers = "DELETE FROM buildings_users WHERE ID_BUILDING = '{$bld['ID_BUILDING']}'";
			$qDeleBldMeter = "DELETE FROM meters WHERE ID_BUILDING = '{$bld['ID_BUILDING']}'";
			$qDeleBld = "DELETE FROM buildings WHERE ID_BUILDING = '{$bld['ID_BUILDING']}'";
			
			if($esegui){
				mysql_query($qDeleBldFiles);
				mysql_query($qDeleBldUsers);
				mysql_query($qDeleBldMeter);
				mysql_query($qDeleBld);
			} else {
				echo $qDeleBldFiles.BR;
				echo $qDeleBldUsers.BR;
				echo $qDeleBldMeter.BR;
				echo $qDeleBld.BR;
			}
		}
		
		// elimino la cooperativa
		$qDeleHC = "DELETE FROM hcompanys WHERE ID_HCOMPANY = '$id'";
		if($esegui){
			mysql_query($qDeleHC);
		} else {
			echo $qDeleHC.BR;
		}
	}
	elseif($action == 'remove_building_all'){
		// lista appartamenti
		$aFlats = sole::get_flats_by_idbuilding($id);
		foreach($aFlats as $flat){
			// lista contatori
			$aMeters = sole::get_meters_all_by_idflat($flat['ID_FLAT']);
			foreach($aMeters as $meter){
				// elimino le misurazioni
				$qDeleMeasures = "DELETE FROM measures WHERE ID_METER='{$meter['ID_METER']}'";
				// elimino i dati dei consumi
				$qDeleConsumption = "DELETE FROM consumptions WHERE ID_METER = '{$meter['ID_METER']}'";
				// elimino i misuratori di produzione
				$qDeleMeterProduction = "DELETE FROM meters_productions WHERE ID_METER = '{$meter['ID_METER']}'";
				// elimino il misuratore
				$qDeleMeter = "DELETE FROM meters WHERE ID_METER = '{$meter['ID_METER']}'";
				
				if($esegui){
					mysql_query($qDeleMeasures);
					mysql_query($qDeleConsumption);
					mysql_query($qDeleMeterProduction);
					mysql_query($qDeleMeter);
				} else {
					echo $qDeleMeasures.BR;
					echo $qDeleConsumption.BR;
					echo $qDeleMeterProduction.BR;
					echo $qDeleMeter.BR;
				}						
			}
			
			// elimino l'appartamento
			$qDeleFlatMeter = "DELETE FROM flats_meters WHERE ID_FLAT='{$flat['ID_FLAT']}'";
			$qDeleFlatMsoutputs = "DELETE FROM msoutputs WHERE ID_FLAT='{$flat['ID_FLAT']}'";
			$qDeleOccupancys = "DELETE FROM occupancys WHERE ID_FLAT='{$flat['ID_FLAT']}'";
			$qDeleFlat = "DELETE FROM flats WHERE ID_FLAT = '{$flat['ID_FLAT']}'";
			
			if($esegui){
				mysql_query($qDeleFlatMeter);
				mysql_query($qDeleFlatMsoutputs);
				mysql_query($qDeleOccupancys);
				mysql_query($qDeleFlat);
			} else {
				echo $qDeleFlatMeter.BR;
				echo $qDeleFlatMsoutputs.BR;
				echo $qDeleOccupancys.BR;
				echo $qDeleFlat.BR;
			}				
		}
		// elimino l'edificio e info
		$qDeleBldFiles = "DELETE FROM buildings_files WHERE ID_BUILDING = '$id'";
		$qDeleBldUsers = "DELETE FROM buildings_users WHERE ID_BUILDING = '$id'";
		$qDeleBldMeter = "DELETE FROM meters WHERE ID_BUILDING = '$id'";
		$qDeleBld = "DELETE FROM buildings WHERE ID_BUILDING = '$id'";
		
		if($esegui){
			mysql_query($qDeleBldFiles);
			mysql_query($qDeleBldUsers);
			mysql_query($qDeleBldMeter);
			mysql_query($qDeleBld);
		} else {
			echo $qDeleBldFiles.BR;
			echo $qDeleBldUsers.BR;
			echo $qDeleBldMeter.BR;
			echo $qDeleBld.BR;
		}
	}
	
	// elimino il post
	io::headto('remove_from_db.php', array());
	
}

// controlli per la pagina
$select = array();

$qHC = "SELECT ID_HCOMPANY AS id, CODE_HC AS value FROM hcompanys ORDER BY CODE_HC ASC";
$rHC = rs::inMatrix($qHC);

$select['hc'] = '';
foreach($rHC as $row){
	$select['hc'] .= '<option value="'.$row['id'].'">'.$row['value'].'</option>'."\n";
}
if(!empty($select['hc'])){
	$select['hc'] = '<select name="id" style="width:200px">'.$select['hc'].'</select>';
}
unset($rHC);

$qBuilding = "SELECT ID_BUILDING AS id, CODE_BLD AS value FROM buildings ORDER BY CODE_BLD ASC";
$rBuilding = rs::inMatrix($qBuilding);

$select['building'] = '';
foreach($rBuilding as $row){
	$select['building'] .= '<option value="'.$row['id'].'">'.$row['value'].'</option>'."\n";
}
if(!empty($select['hc'])){
	$select['building'] = '<select name="id" style="width:200px">'.$select['building'].'</select>';
}
unset($rBuilding);


$MYFILE -> add_js('<script type="text/javascript" src="'.JS_MAIN.'remove.js" ></script>', 'file','footer');

include_once HEAD_AR;
?>

<form method="post" id="frm_hc_all">
<table class="list">
<tr><td width="200"><?=$select['hc']?></td><td>
<input type="hidden" name="action" value="remove_hc_all" />
<input type="button" id="hc_all" value="Eliminazione definitiva Cooperativa" class="g-button g-button-yellow submit" style="width:240px" /></td></tr>
</table>
</form>

<form method="post" id="frm_building_all">
<table class="list">
<tr><td width="200"><?=$select['building']?></td><td>
<input type="hidden" name="action" value="remove_building_all" />
<input type="button" id="building_all" value="Eliminazione definitiva Edificio" class="g-button g-button-yellow submit" style="width:240px" /></td></tr>
</table>
</form>

<?php
include_once FOOTER_AR;
?>