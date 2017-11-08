<?php
include_once 'init.php';

$user = new autentica($aA5);
$user -> login_standard();

include_once HEAD_AR;

$esegui = true;

// lista appartamenti
$aFlats = sole::get_flats_by_idbuilding( 44 );
foreach($aFlats as $flat){
	// lista contatori
	$aMeters = sole::get_meters_all_by_idflat($flat['ID_FLAT']);
	foreach($aMeters as $meter){
		// elimino le misurazioni
		$qDeleMeasures = "DELETE FROM measures WHERE ID_METER='{$meter['ID_METER']}'";
		// elimino i dati dei consumi
		$qDeleConsumption = "DELETE FROM consumptions WHERE ID_METER = '{$meter['ID_METER']}'";
		
		// elimino i misuratori di produzione
		//$qDeleMeterProduction = "DELETE FROM meters_productions WHERE ID_METER = '{$meter['ID_METER']}'";
		// elimino il misuratore
		//$qDeleMeter = "DELETE FROM meters WHERE ID_METER = '{$meter['ID_METER']}'";
		
		if($esegui){
			mysql_query($qDeleMeasures);
			mysql_query($qDeleConsumption);
			//mysql_query($qDeleMeterProduction);
			//mysql_query($qDeleMeter);
		} else {
			echo $qDeleMeasures.BR;
			echo $qDeleConsumption.BR;
			//echo $qDeleMeterProduction.BR;
			//echo $qDeleMeter.BR;
		}						
	}

}



include_once FOOTER_AR;
?>