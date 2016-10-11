<?
include_once '../init.php';

$id_building 	= $_REQUEST['id'];
$year			= $_REQUEST['year'];

/*
1	Energia elettrica
2	Energia termica
3	Gas Naturale
4	Teleriscaldamento
5	Acqua
*/

// [funzione => id_metertype1,id_metertype2..] quando id_metertype è vuoto, significa che utilizzo una regola più complessa  
$aFunctions = array(
	'resa_GT' => '',
	'resa_PV' => '1',
	'resa_ST' => '2',
	'rapporto_area_servita' => '2',
	'consumo_acqua_calda' => '', // SOLARE TERMICO
	'copertura_fabbisogno' => '', // SOLARE TERMICO
	'perdite' => '5'
);

$aUploads = array(1 => 'winter', 2 => 'summer');
$cella_vuota = '<td class="winter"></td><td class="summer"></td>';

// lista misuratori produzione per l'edificio
$aMeters = sole::get_meters_production_by_idbuilding($id_building);

$TR = '';
foreach($aMeters as $k => $meter){
	$taglia = '';
	if(!empty($meter['SIZE']) && $meter['SIZE'] != 0.00){
		$unit = '';
		if($meter['ID_METERTYPE'] == 2){ // termico
			$unit = ' m<sup>2</sup>';
		}
		elseif($meter['ID_METERTYPE'] == 1){ // elettrico
			$unit = ' kWp';
		}
		$taglia = num::format($meter['SIZE'], 2, DEC_SEP, THO_SEP).$unit;
	}
		
	$tmp = '<td>'.$meter['CODE_METER'].'</td>';
	$tmp .= '<td colspan="2">'.$taglia.'</td>';
	$output = new meter($meter['ID_METER']);
	
	foreach($aFunctions as $function => $metertype){
		// eseguo la funzione in base al tipo di misuratore
		if(
			strpos($metertype, $meter['ID_METERTYPE'] ) !== false || 
			($function == 'consumo_acqua_calda' && $meter['THERMAL_TYPE'] == 1 && $meter['ID_METERTYPE'] == 2) ||
			($function == 'copertura_fabbisogno' && $meter['THERMAL_TYPE'] == 1 && $meter['ID_METERTYPE'] == 2) ||
			($function == 'resa_GT' && $meter['THERMAL_TYPE'] == 2 && $meter['ID_METERTYPE'] == 2)
		){
			
			foreach($aUploads as $id_upload => $class){
				if($function == 'rapporto_area_servita'){
					$val = $output -> $function();
				} else {
					$val = $output -> $function($year, $id_upload);
				}
				
				if(is_numeric($val)){
					$val = num::format($val, 2, DEC_SEP, THO_SEP);
				} elseif($val == 'nd'){
					$val = '[Nd]';
				}
				
				$tmp .= '<td class="'.$class.'">'.$val.'</td>';
			}

		} else {
			$tmp .= $cella_vuota;
		}

	}
	
	$TR .= '<tr>'.$tmp.'</tr>'."\n";

}


?>
<table class="list"><tbody>
<tr>
<th width="200"><?=METER?></th>
<th class="bordato" colspan="2"><?=SIZE?></th>
<th class="bordato" colspan="2"><?=RESA_TERMICA_PERC?></th>
<th class="bordato" colspan="2"><?=RESA_PV?></th>
<th class="bordato" colspan="2"><?=RESA_ST?></th>
<th class="bordato" colspan="2"><?=ALLOGGI_COLLETTORI?></th>
<th class="bordato" colspan="2"><?=CONSUMO_ACQUA_CALDA?></th>
<th class="bordato" colspan="2"><?=COPERTURA_FABBISOGNO?></th>
<th class="bordato" colspan="2"><?=USI_E_PERDITE?></th>
</tr>

<tr>
<th></th>
<th width="100" class="bordato" colspan="2"></th>
<th width="100" class="bordato"><?=SBL_WINTER?></th><th width="100" class="bordato"><?=SBL_SUMMER?></th>
<th width="100" class="bordato"><?=SBL_WINTER?></th><th width="100" class="bordato"><?=SBL_SUMMER?></th>
<th width="100" class="bordato"><?=SBL_WINTER?></th><th width="100" class="bordato"><?=SBL_SUMMER?></th>
<th width="100" class="bordato"><?=SBL_WINTER?></th><th width="100" class="bordato"><?=SBL_SUMMER?></th>
<th width="100" class="bordato"><?=SBL_WINTER?></th><th width="100" class="bordato"><?=SBL_SUMMER?></th>
<th width="100" class="bordato"><?=SBL_WINTER?></th><th width="100" class="bordato"><?=SBL_SUMMER?></th>
<th width="100" class="bordato"><?=SBL_WINTER?></th><th width="100" class="bordato"><?=SBL_SUMMER?></th>
</tr>

<?=$TR?>

</tbody></table>