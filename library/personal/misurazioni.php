<?php
class misurazioni{

static function get_last_measures_by_idmeter($aM){
	$aMis = array('F1');
	if($aM['ID_METERTYPE'] == 1 && $aM['HMETER'] > 1){ # CONTATORE ELETTRICO MULTIORARIO
		$aMis = array('F1', 'F2', 'F3');
	}
	
	$q = "SELECT 
	measures.*, 
	meters.ID_METERTYPE,
	meters.HMETER,
	meters.MATRICULA_ID, 
	meters.REGISTERNUM,	
	metertypes.METERTYPE_".LANG_DEF."
	FROM measures
	LEFT JOIN meters ON measures.ID_METER = meters.ID_METER
	LEFT JOIN metertypes ON meters.ID_METERTYPE = metertypes.ID_METERTYPE
	WHERE measures.ID_METER =  '".$aM['ID_METER']."'
	AND measures.IS_DEL =  '0'
	ORDER BY measures.ANNO_MS ASC ,
	measures.ID_UPLOADTYPE ASC
	LIMIT 0, 10";
	$r = rs::inMatrix($q);
	
	# TRATTO L'ARRAY
	$i = 0; 
	$y = (date('Y') - SHOW_FROM_YEAR ) + 3;

	foreach($r as $k => $v){ # NORMALIZZO L'ARRAY
		if($i == $y) break;
		$vals[$i]['rec'] = $v;
		
 		if($v['STATUS'] != 'Null'){
			$r[$k]['mode'] = 'read';
		} elseif($v['IS_CONFIRMED_MS'] != 1){ // posso modificare la misurazione non validata
			$r[$k]['mode'] = 'write';
		} else {
			$r[$k]['mode'] = 'read';
		} 
		//echo $r[$k]['mode'].' '.$v['STATUS'].' '.$v['ID_MEASURE'].BR;
		
		$i ++;
	}
	return $r;
}

static function get_last_measures_by_idmeter_for_print($aM){
	$aMis = array('F1');
	if($aM['ID_METERTYPE'] == 1 && $aM['HMETER'] > 1){ # CONTATORE ELETTRICO MULTIORARIO
		$aMis = array('F1', 'F2', 'F3');
	}
	
	$q = "SELECT 
	measures.*, 
	meters.ID_METERTYPE,
	meters.HMETER,
	meters.MATRICULA_ID, 
	meters.REGISTERNUM,	
	metertypes.METERTYPE_".LANG_DEF."
	FROM measures
	LEFT JOIN meters ON measures.ID_METER = meters.ID_METER
	LEFT JOIN metertypes ON meters.ID_METERTYPE = metertypes.ID_METERTYPE
	WHERE measures.ID_METER =  '".$aM['ID_METER']."'
	AND measures.IS_DEL =  '0'
	ORDER BY measures.ANNO_MS DESC ,
	measures.ID_UPLOADTYPE DESC
	LIMIT 0, 2";
	$r = rs::inMatrix($q);
	
	# TRATTO L'ARRAY
	$i = 0; 
	$y = ( date('Y') - SHOW_FROM_YEAR ) + 3;

	foreach($r as $k => $v){ # NORMALIZZO L'ARRAY
		if($i == $y) break;
		$vals[$i]['rec'] = $v;
		
		
 		if($v['STATUS'] != 'Null'){
			$r[$k]['mode'] = 'read';
		} elseif($v['IS_CONFIRMED_MS'] != 1){ // posso modificare la misurazione non validata
			$r[$k]['mode'] = 'write';
		} else {
			$r[$k]['mode'] = 'read';
		} 
		//echo $r[$k]['mode'].' '.$v['STATUS'].' '.$v['ID_MEASURE'].BR;
		
		$i ++;
	}
	
	// print_r($r);
	
	return $r;
}

static function get_measures_12_by_idmeter($id_meter, $year){
/*
	$q = "SELECT
	measures12.*,
	meters.ID_METERTYPE,
	meters.HMETER,
	meters.MATRICULA_ID,
	meters.REGISTERNUM,
	metertypes.METERTYPE_".LANG_DEF."
	FROM measures12
	LEFT JOIN meters USING(ID_METER)
	LEFT JOIN metertypes ON meters.ID_METERTYPE = metertypes.ID_METERTYPE
	WHERE 
	measures12.ID_METER='$id_meter' AND
	measures12.YEAR='".$year."'
	";*/

	$q = "SELECT * FROM measures12 
	WHERE 
	measures12.ID_METER='$id_meter' AND
	measures12.ANNO_MS='".$year."'
	";
	$r = rs::inMatrix($q);

	// per i controlli mi è comodo creare una chiave [annomese] con i valori trovati
	$aRet = array();
	foreach($r as $k => $v){
		
		if($r[$k]['D_MEASURE']=='0000-00-00'){
			$r[$k]['D_MEASURE']='';
		}
		
		$aRet[$v['ANNO_MS'].$v['ID_UPLOADTYPE']] = $r[$k];
		unset($r[$k]);
	}
	
	
	# TRATTO L'ARRAY
	//$i = 0;
	//$y = (date('Y') - SHOW_FROM_YEAR ) + 3;
/*
	foreach($r as $k => $v){ // tratto l'array
		if($i == $y) break;
		$vals[$i]['rec'] = $v;

		if($v['STATUS'] != 'Null'){
				$r[$k]['mode'] = 'read';
		} elseif($v['IS_CONFIRMED_MS'] != 1){ // posso modificare la misurazione non validata
			$r[$k]['mode'] = 'write';
		} else {
			$r[$k]['mode'] = 'read';
		}
		*/
		
	//echo $r[$k]['mode'].' '.$v['STATUS'].' '.$v['ID_MEASURE'].BR;
	// $i ++;
	
	return $aRet;
}


static function get_measures_2_by_idmeter($id_meter, $year){

	$q = "SELECT * FROM measures
	WHERE
	measures.ID_METER='$id_meter' AND
	measures.ANNO_MS='".$year."'
	";
	$r = rs::inMatrix($q);

	// per i controlli mi è comodo creare una chiave [annoiduploadtype] con i valori trovati
	$aRet = array();
	foreach($r as $k => $v){
		
		// tratto l'array
		if($v['STATUS'] != 'Null'){
			$r[$k]['mode'] = 'read';
		} elseif($v['IS_CONFIRMED_MS'] != 1){ // posso modificare la misurazione non validata
			$r[$k]['mode'] = 'write';
		} else {
			$r[$k]['mode'] = 'read';
		}
		
		if($r[$k]['D_MEASURE']=='0000-00-00'){
			$r[$k]['D_MEASURE']='';
		}
		
		$aRet[$v['ANNO_MS'].$v['ID_UPLOADTYPE']] = $r[$k];
		unset($r[$k]);
	}

	# TRATTO L'ARRAY
	//$i = 0;
	//$y = (date('Y') - SHOW_FROM_YEAR ) + 3;
	/*
	foreach($r as $k => $v){ // tratto l'array
	if($i == $y) break;
	$vals[$i]['rec'] = $v;

	if($v['STATUS'] != 'Null'){
	$r[$k]['mode'] = 'read';
	} elseif($v['IS_CONFIRMED_MS'] != 1){ // posso modificare la misurazione non validata
	$r[$k]['mode'] = 'write';
	} else {
	$r[$k]['mode'] = 'read';
	}
	*/

	//echo $r[$k]['mode'].' '.$v['STATUS'].' '.$v['ID_MEASURE'].BR;
	// $i ++;

	return $aRet;
}

static function html_tabella_interna($meter, $upload_type, $year){ # $meter � un array di record riguardanti uno o pi� misuratori
	# MEASURES LIST
	$ams = misurazioni::get_last_measures_by_idmeter($meter);
	//var_dump($ams);

	$nColonne = 7;
	
	$aMis = array('F1'); $aF = array('F1', 'F2', 'F3');
	if($meter['ID_METERTYPE'] == 1 && $meter['HMETER'] > 1){ # CONTATORE ELETTRICO MULTIORARIO
		$aMis = array('F1', 'F2', 'F3');
	}
	$aCols = array_merge(array('ANNO_MS', 'ID_UPLOADTYPE', 'D_MEASURE'), $aMis);
	
	$itd = 1; $td = ''; $tr = '';
	foreach($aCols as $int){ #### INTESTAZIONE	
		$itd ++;
	}
	while($itd <= $nColonne){ # TD VUOTI
		$itd ++;
	}
	
	$show_new = true;
	foreach($ams as $idm => $measure){
		if($measure['ID_UPLOADTYPE'] == $upload_type && $measure['ANNO_MS'] == $year){
			$show_new = false;
		}
		$td = '';  $itd = 1;//$CNT = 0;
		foreach($aCols as $kk => $field ){ # PRIMA CELLA DATA
			
			//echo $CNT .' '. $measure['mode'] .' '. $measure['ID_MEASURE'].BR;
			
			//$CNT ++;
		
			if($measure['mode'] == 'read'){
				if($field == 'D_MEASURE'){
					$measure['D_MEASURE'] = dtime::db2my($measure['D_MEASURE']);
				}
				$content = $measure[$field];
			} else {
				$input = new io();
				$input -> type = 'text'; 
				$input -> val = $measure[$field];
				$input -> id = 'edit'.$measure['ID_MEASURE'];
				$input -> css = 'ottanta edit_value';
				if($field == 'D_MEASURE') $input -> css = 'datepicker ottanta edit_value';
				$content = $input -> set($field);
				//$content .= '<input type="button" value="'.DELETE.'" class="g-button g-button-red" />';
			}
		
			$input = new io();
			$input -> type = 'text'; 
			$input = $input -> set($field);
		
			$td .= mytag::in($content, 'td', array('width' => '95'));
			$itd ++;
		}
		
		while($itd <= $nColonne){ # CREO I TD VUOTI
			$puls_elimina = ( $itd == $nColonne && $measure['mode'] != 'read') ? '<input type="button" value="'.DELETE.'" class="g-button g-button-red del-measure" id="del-measure-'.$measure['ID_MEASURE'].'" />' : '&nbsp;';
			
			$td .= mytag::in($puls_elimina, 'td', array('width' => '95', 'align' => 'right'));
			$itd ++;
		}
		$tr .= mytag::in($td, 'tr', array('id' => 'tr-'.$measure['ID_MEASURE']));
	}
		
	//$aCols = array_merge(array('ANNO_MS', 'ID_UPLOADTYPE', 'D_MEASURE'), $aMis);

	if($show_new){	# CAMPI PER LA NUOVA MISURAZIONE
		$td = '';
		$td .= mytag::in('<input type="text" disabled="disabled" class="new_year ottanta" value="'.$year.'" name="ANNO_MS" />', 'td', array());
		
		$td .= mytag::in('<input type="text" disabled="disabled" class="new_uploadtype ottanta" value="'.$upload_type.'" name="ID_UPLOADTYPE" />', 'td', array());
		
		$td .= mytag::in('<input type="text" disabled="disabled" class="new_dmeasure ottanta" value="" name="D_MEASURE" />', 'td', array());
		
		$itd = 3;
		foreach($aF as $kk => $field ){
		
			if(in_array($field, $aMis)){
				$input = new io();
				$input -> type = 'text'; 
				$input -> css = 'ottanta';
				$input -> id = $field.$meter['ID_METER'];
				$input = $input -> set($field);
			} else {
				$input = new io();
				$input -> type = 'hidden'; 
				$input -> id = $field.$meter['ID_METER'];
				$input = $input -> set($field.$meter['ID_METER']);
			}
		
			$td .= mytag::in($input, 'td', array('width' => '95'));
			$itd ++;
		}
		while($itd < $nColonne -1){ # CREO I TD VUOTI
			$td .= mytag::in('&nbsp;', 'td', array('width' => '95'));
			$itd ++;
		}
		$td .= mytag::in('<input type="button" value="'.SAVE.'" class="g-button g-button-yellow save_new" id="save'.$meter['ID_METER'].'" />', 'td', array('align' => 'right', 'width' => '95'));
		$tr .= mytag::in($td, 'tr', array('class' => 'ins_new', 'id' => 'tr'.$meter['ID_METER']));
	}
	
	$table = mytag::in($tr, 'table', array('class' => 'neutra'));
	return $table;
}

static function print_measures_table($meter, $upload_type, $year){ # $meter � un array di record riguardanti uno o pi� misuratori
	# MEASURES LIST
	$ams = misurazioni::get_last_measures_by_idmeter_for_print($meter);
	$nColonne = 7;
	
	$aMis = array('F1'); $aF = array('F1', 'F2', 'F3');
	if( $meter['ID_METERTYPE'] == 1 && $meter['HMETER'] > 1 ){ # CONTATORE ELETTRICO MULTIORARIO
		$aMis = array('F1', 'F2', 'F3');
	}
	$aCols = array_merge(array('ANNO_MS', 'ID_UPLOADTYPE', 'D_MEASURE'), $aMis);
	
	$itd = 1; $td = ''; $tr = '';
	foreach($aCols as $int){ #### INTESTAZIONE	
		$itd ++;
	}
	while($itd <= $nColonne){ # TD VUOTI
		$itd ++;
	}
	
	//$show_new = true;
	foreach($ams as $idm => $measure){
	
		$td = '';  $itd = 1;//$CNT = 0;
		foreach($aCols as $kk => $field ){ # PRIMA CELLA DATA
			$align = 'left';
			if($field == 'D_MEASURE'){
				$measure['D_MEASURE'] = dtime::db2my($measure['D_MEASURE']);
				$content = $measure['D_MEASURE'];
				
			} elseif( $field == 'F1' || $field == 'F2' ||$field == 'F3' ) {
				$align = 'right';
				$content = num::format( $measure[$field], 3, ',', '.' );
			} else {
				$content = $measure[$field];
			}
			/* } else {
				$input = new io();
				$input -> type = 'text'; 
				$input -> val = $measure[$field];
				$input -> id = 'edit'.$measure['ID_MEASURE'];
				$input -> css = 'ottanta edit_value';
				if($field == 'D_MEASURE') $input -> css = 'datepicker ottanta edit_value';
				$content = $input -> set($field);
				//$content .= '<input type="button" value="'.DELETE.'" class="g-button g-button-red" />';
			} */
		
			$input = new io();
			$input -> type = 'text'; 
			$input = $input -> set($field);
		
			$td .= mytag::in($content, 'td', array('width' => '95', 'align' => $align));
			$itd ++;
		}
		
		while($itd <= $nColonne){ # CREO I TD VUOTI
			//$puls_elimina = ( $itd == $nColonne && $measure['mode'] != 'read') ? '<input type="button" value="'.DELETE.'" class="g-button g-button-red del-measure" id="del-measure-'.$measure['ID_MEASURE'].'" />' : '&nbsp;';
			$puls_elimina = '&nbsp;';
			
			if($itd == $nColonne){
				$td .= mytag::in($puls_elimina, 'td', array());
			} else {
				$td .= mytag::in($puls_elimina, 'td', array('width' => '95'));
			}
			$itd ++;
		}
		$tr .= mytag::in($td, 'tr', array());
	}
		
	//$aCols = array_merge(array('ANNO_MS', 'ID_UPLOADTYPE', 'D_MEASURE'), $aMis);

/* 	if($show_new){	# CAMPI PER LA NUOVA MISURAZIONE
		$td = '';
		$td .= mytag::in('<input type="text" disabled="disabled" class="new_year ottanta" value="'.$year.'" name="ANNO_MS" />', 'td', array());
		
		$td .= mytag::in('<input type="text" disabled="disabled" class="new_uploadtype ottanta" value="'.$upload_type.'" name="ID_UPLOADTYPE" />', 'td', array());
		
		$td .= mytag::in('<input type="text" disabled="disabled" class="new_dmeasure ottanta" value="" name="D_MEASURE" />', 'td', array());
		
		$itd = 3;
		foreach($aF as $kk => $field ){
		
			if(in_array($field, $aMis)){
				$input = new io();
				$input -> type = 'text'; 
				$input -> css = 'ottanta';
				$input -> id = $field.$meter['ID_METER'];
				$input = $input -> set($field);
			} else {
				$input = new io();
				$input -> type = 'hidden'; 
				$input -> id = $field.$meter['ID_METER'];
				$input = $input -> set($field.$meter['ID_METER']);
			}
		
			$td .= mytag::in($input, 'td', array('width' => '95'));
			$itd ++;
		}
		while($itd < $nColonne -1){ # CREO I TD VUOTI
			$td .= mytag::in('&nbsp;', 'td', array('width' => '95'));
			$itd ++;
		}
		$td .= mytag::in('<input type="button" value="'.SAVE.'" class="g-button g-button-yellow save_new" id="save'.$meter['ID_METER'].'" />', 'td', array('align' => 'right', 'width' => '95'));
		$tr .= mytag::in($td, 'tr', array('class' => 'ins_new', 'id' => 'tr'.$meter['ID_METER']));
	} */
	
	$td = '';$itd = 1;
	while($itd < $nColonne +1){ # CREO I TD VUOTI
		$td .= mytag::in('&nbsp;', 'td', array());
		$itd ++;
	}
	
	
	
	$tr = mytag::in($td, 'tr', array()).$tr;
	
	$table = mytag::in($tr, 'table', array('class' => 'neutra'));
	return $table;
}

	static function get_primary_energy_value($id_measure, $value){
		
		$q_conversion = "
			SELECT 
			federations_conversions.EP AS CONVERSION
			FROM measures
			LEFT JOIN meters ON measures.ID_METER = meters.ID_METER
			LEFT JOIN flats_meters ON meters.ID_METER = flats_meters.ID_METER
			LEFT JOIN flats ON flats_meters.ID_FLAT = flats.ID_FLAT
			LEFT JOIN buildings ON flats.ID_BUILDING = buildings.ID_BUILDING
			LEFT JOIN hcompanys ON buildings.ID_HCOMPANY = hcompanys.ID_HCOMPANY
			LEFT JOIN federations ON hcompanys.ID_FEDERATION = federations.ID_FEDERATION
			LEFT JOIN federations_conversions ON meters.ID_METERTYPE = federations_conversions.ID_METERTYPE AND 
			federations.ID_FEDERATION=federations_conversions.ID_FEDERATION
			WHERE measures.ID_MEASURE = '$id_measure'
			";
		$r_conversion = rs::rec2arr($q_conversion);
		
		$primary_ec = $r_conversion['CONVERSION'];
		
		if(!empty($primary_ec) && is_numeric($primary_ec)){
			$primary_energy = $value * $primary_ec;
		}
		else{
			$primary_energy = $value;
		}
		return $primary_energy;
	}
	
	static function get_primary_energy_value_by_codename($codename, $id_building, $value){
		
		$q_conversion = "
			SELECT 
			federations_conversions.EP AS CONVERSION
			FROM measures
			LEFT JOIN meters ON measures.ID_METER = meters.ID_METER
			LEFT JOIN flats_meters ON meters.ID_METER = flats_meters.ID_METER
			LEFT JOIN flats ON flats_meters.ID_FLAT = flats.ID_FLAT
			LEFT JOIN buildings ON flats.ID_BUILDING = buildings.ID_BUILDING
			LEFT JOIN hcompanys ON buildings.ID_HCOMPANY = hcompanys.ID_HCOMPANY
			LEFT JOIN federations ON hcompanys.ID_FEDERATION = federations.ID_FEDERATION
			LEFT JOIN federations_conversions ON meters.ID_METERTYPE = federations_conversions.ID_METERTYPE AND 
			federations.ID_FEDERATION=federations_conversions.ID_FEDERATION
			WHERE meters.CODE_METER = '$codename' AND buildings.ID_BUILDING=$id_building GROUP BY hcompanys.ID_FEDERATION
			";
		$r_conversion = rs::rec2arr($q_conversion);
		
		$primary_ec = $r_conversion['CONVERSION'];
		
		//echo $q_conversion.BR;
		
		if(!empty($primary_ec) && is_numeric($primary_ec)){
			$primary_energy = $value * $primary_ec;
		}
		else{
			$primary_energy = $value;
		}
		return $primary_energy;
	}
	

	static function get_primary_energy_by_metertype($id_flat, $id_metertype, $value){
		# DATO L'ID_FLAT E IL TIPO DI MISURATORE, OTTENGO IL VALORE DI TRASFORAMAZIONE, CHE DOVREI AVERE SEMPRE
		$r_conversion = sole::get_conversions_from_id_flat($id_flat);
		$primary_ec = $r_conversion[$id_metertype];

		if(!empty($primary_ec) && is_numeric($primary_ec)){
			$primary_energy = $value * $primary_ec;
		}
		else{
			$primary_energy = $value;
		}

		return $primary_energy;
	}
	
	static function get_output ($id_measure, $flat, $type, $source='f', $nze=false, $tipoarea='n')	{
		$id_flat = $flat?$flat:0;
		$status = 'valid';
		
		//echo $tipoarea;

		if($nze)	{
			$nze_fields = 'nzes.ID_OUTPUT, nzes.A_NZE AS A, nzes.B_NZE AS B, ';
			$nze_join = 'LEFT JOIN nzes USING (ID_METER)
						LEFT JOIN meters AS ameter ON ameter.CODE_METER=nzes.A_NZE AND ameter.ID_BUILDING=meters.ID_BUILDING';
			
		} else 
			$nze_join = 'LEFT JOIN meters AS ameter ON ameter.CODE_METER=meters.A AND ameter.ID_BUILDING=meters.ID_BUILDING';
		
		
		
		$q = "SELECT meters.*, measures.*, msoutputs.*, flats.*, flats_meters.*, buildings.*, occupancys.*   ,$nze_fields buildings.ID_BUILDING, msoutputs.STATUS AS status,
					COALESCE(buildings_conversions.EP, afederations_conversions.EP, federations_conversions.EP) AS p,
					COALESCE(buildings_conversions.CO2, afederations_conversions.CO2, federations_conversions.CO2) AS c,
					COALESCE(buildings_conversions.EURO, afederations_conversions.EURO,  federations_conversions.EURO) AS e,
					1 AS f,
					COALESCE(flatsdiv.GROSSAREA, flats.GROSSAREA) AS GROSSAREA,
					COALESCE(flatsdiv.NETAREA, flats.NETAREA) AS NETAREA,
					meters.ID_METERTYPE
				FROM msoutputs 
				LEFT JOIN measures USING ( ID_MEASURE ) 
				LEFT JOIN meters USING ( ID_METER ) 
				LEFT JOIN flats_meters USING ( ID_METER ) 
					$nze_join
				LEFT JOIN flats ON flats.ID_FLAT = flats_meters.ID_FLAT
					LEFT JOIN flats AS flatsdiv ON flatsdiv.ID_FLAT = $id_flat
					LEFT JOIN buildings ON flats.ID_BUILDING=buildings.ID_BUILDING
					LEFT JOIN buildings_conversions ON buildings_conversions.ID_BUILDING=buildings.ID_BUILDING AND buildings_conversions.ID_METERTYPE=meters.ID_METERTYPE
					LEFT JOIN hcompanys USING(ID_HCOMPANY)
					
					LEFT JOIN federations_conversions AS afederations_conversions ON afederations_conversions.ID_FEDERATION=hcompanys.ID_FEDERATION AND afederations_conversions.ID_METERTYPE=ameter.ID_METERTYPE
					LEFT JOIN federations_conversions ON federations_conversions.ID_FEDERATION=hcompanys.ID_FEDERATION AND federations_conversions.ID_METERTYPE=meters.ID_METERTYPE
					LEFT JOIN occupancys ON occupancys.ID_FLAT=$id_flat AND occupancys.ID_UPLOADTYPE=measures.ID_UPLOADTYPE AND occupancys.ANNO_MS=measures.ANNO_MS
					
				WHERE ID_MEASURE = $id_measure
				LIMIT 1";
		//echo $q;
		//die();
		$r = rs::rec2arr($q);
		//echo "<br>\n contatore: " . $r['CODE_METER'] . ' ' . $flat . ' ' . $r['CNPVM2'];
		//echo '<pre>'; var_dump($r); echo '</pre>';
		
		
		
		if(empty($r['ID_MEASURE'])){
			return array('value' => false, 'status' => 'nd');
		}
		
		//if($id_measure
		//$flat = rs::rec2arr("SELECT NETAREA FROM flats WHERE ID_FLAT=$flat LIMIT 1");
		
			$area = $r['NETAREA'];
		
		
		if($tipoarea=='n')
			$areacorr = 1;
		else
			$areacorr =  $r['NETAREA'] / $r['GROSSAREA'];
		
		//$metertype = sole::get_metertype_by_idmeter($r['ID_METER']);
		$metertype = $r['ID_METERTYPE'];
		
		//echo "\n$areacorr =  {$r['NETAREA']} / {$r['GROSSAREA']}\n";
		
		// Codice di validazione dei dati 
		// Se il contatore è condiviso e non è formula, allora è sempre 'nd'
		if($r['ID_SUPPLYTYPE']==2)  { //contatore condiviso
			//var_dump($r['ID_METER']);
			if($r['ID_RF'] == 1){ // real
				if( $r['status']=='wrong' || $r['status']=='nd' ){
					$status = 'nd';
				}
			}
			
			// i consumi condivisi devono essere 0 per gli alloggi non occupati
// 			if(!sole::is_occupied($id_flat, $r['ID_UPLOADTYPE'], $r['ANNO_MS'])){
// 				return array('value' => 0, 'status' => $status);
// 			}
			
			
		}
					
		if($r['IS_OCCUPIED'] == 0 || $r['OCCUPANCY'] == 0 )
			return array('value' => 0, 'status' => $status, 'area'=>0);
					
		// Se il contatore è condiviso e formula, allora è sempre corretto
		
	 	if($r['ID_SUPPLYTYPE']==2)  //contatore condiviso
			if($r['ID_RF']==2)
				$status = 'valid'; 

		// Se il contatore è condiviso e risulta ND o WRONG allora l'output è sempre ND
		$m = new EvalMath($r['ANNO_MS'], $r['ID_UPLOADTYPE'], $r['ID_BUILDING']);
		
		//	$m -> suppress_errors = true;
	
		//echo $r['ID_OUTPUT'];
/* 		if($type=='F1'){
			if((!empty($r['NPVM2F1']) && !empty($r['NPVM2'])) && $r['NPVM2'] > 0 ){
				//echo $r['NPVM2F1'].' '.$r['NPVM2'].BR;
				$ret = ($r['NPVM2F1'] / $r['NPVM2']) * 100;
				$output_f1 = $ret;
			} else {
				$output_f1 = 0;
			}
		} */
		
		
		
		$value = 0;
		if ($r['ID_OUTPUT']==1)	{  // value
			if (isset($r['CNPV']))
				$output =  $r['CNPVM2'] * $area;
			else 
				$output =  0; 
			$status = $r['status'];
			//echo $output.BR;
		}
		elseif ($r['ID_OUTPUT']==2){  // A/B
			//echo 'misura: '. $id_measure;
			//$m->DEBUG=true;
			$aA = $m->e_ws($r['A']); $a = $aA['value']; $a_status = $aA['status'];
			//echo 'A: ' . $a_status . BR;
			
			$aB = $m->e_ws($r['B']); $b = $aB['value']; $b_status = $aB['status'];
			
			//echo 'B: ' . $b_status . BR;
			
			if($a_status != 'valid' || $b_status != 'valid')
				$status = 'nd';
			else
				$status = $r['status'];

			$metertype = sole::get_metertype_by_codename($r['A'], $id_flat);
			if (isset($r['CNPV']) AND $a*$b<>0){
				$output = $r['CNPVM2'] * $area * $a / $b;
			} else {
				$output = 0;
			//	$status = 'nd';
			}
		} elseif ($r['ID_OUTPUT']==3){ # M2
			if (isset($r['CNPVM2'])){
				# CONTROLLO SE OCCUPATO
				if(!sole::is_occupied($id_flat, $r['ID_UPLOADTYPE'], $r['ANNO_MS'])){
					$output = 0;
				} else {
					$output = $r['CNPVM2'] * $area;
				}
				
				
				
			}
			else 
				$output = 0; 
		} elseif ($r['ID_OUTPUT']==4)	{  // no output
				$value = false; 
				$status = false;
		} else
			$output = false; 
		//echo $output;
		if($type=='NPVFULL')	
			$value =  $output;
		elseif($type=='NPVM2')	
			$value = $output / $area  * $areacorr;
		elseif($type=='NPVFULLEP')	
			//$value = self::get_primary_energy_by_metertype($id_flat, $metertype, $output);
			$value =  $output;
		elseif($type=='NPVM2EP')	
			//$value = self::get_primary_energy_by_metertype($id_flat, $metertype, $output / $area);
			$value = $output / $area  * $areacorr;
		elseif($type=='F1'){
			if((!empty($r['NPVM2F1']) && !empty($r['NPVM2'])) && $r['NPVM2'] > 0 ){
				$value = ($r['NPVM2F1'] / $r['NPVM2']) * 100;
			} else {
				$value = 0;
			}
		}
		
		//var_dump($r['ID_OUTPUT']);
		
		if($type!='F1'){
			$value = $value * $r[$source];
		}
		//var_dump($r[$source]);
		
		//echo $areacorr;
		//echo $output.BR;
		//echo $area.BR;
		
		$dati_out = array('value' => $value, 'status' => $status, 'area'=>($area / $areacorr), 'measure'=>$id_measure);
		//echo $value . ' ' . $r['CNPVM2'] . ' ' . $tipoarea . ' ' .  $status . BR;
		//var_dump($dati_out);
		
		return $dati_out;
	}
	
	
	
	
	/**
	 * Metodo che calcola gli output per le misurazioni mensili
	 * 
	 * @param int $id_measure
	 * @param int $flat
	 * @param int $type  (NPVFULL|NPVM2|NPVFULLEP|NPVM2EP)
	 * @return array <float $value, string $status> 
	 */
	static function get_output12 ($id_measure, $flat, $type)	{
		$id_flat = $flat;
		//echo $type;
		$status = 'valid';
		$q = "SELECT *, msoutputs12.STATUS AS status FROM msoutputs12
		LEFT JOIN measures12 USING ( ID_MEASURE )
		LEFT JOIN meters USING ( ID_METER )
		LEFT JOIN flats_meters USING ( ID_METER )
		LEFT JOIN flats ON flats.ID_FLAT = flats_meters.ID_FLAT
		WHERE ID_MEASURE = $id_measure
		LIMIT 1";
		//echo $q;
		$r = rs::rec2arr($q);
	
		//var_dump($r);
		if(empty($r['ID_MEASURE'])){
		return array('value' => false, 'status' => 'nd');
		}
	
		//if($id_measure
		$flat = rs::rec2arr("SELECT NETAREA FROM flats WHERE ID_FLAT=$flat LIMIT 1");
		$area = $flat['NETAREA'];

		$metertype = sole::get_metertype_by_idmeter($r['ID_METER']);
		$value = 0;
	
		if (isset($r['CNPV']))
			$output =  $r['CNPVM2'] * $area;
		else
			$output =  0;
		$status = $r['status'];

		if($type=='NPVFULL')
			$value =  $output;
		elseif($type=='NPVM2')
			$value = $output / $area * $areacorr;
		elseif($type=='NPVFULLEP')
			$value = self::get_primary_energy_by_metertype($id_flat, $metertype, $output);
		elseif($type=='NPVM2EP')
			$value = self::get_primary_energy_by_metertype($id_flat, $metertype, $output / $area * $areacorr);
		elseif($type=='F1'){
			if((!empty($r['NPVM2F1']) && !empty($r['NPVM2'])) && $r['NPVM2'] > 0 ){
				$value = ($r['NPVM2F1'] / $r['NPVM2']) * 100;
			} else {
				$value = 0;
			}	
		}
		//echo $type . ':'.$id_measure. ' ' . $value.BR;
		
		return array('value' => $value, 'status' => $status);
	}
	
	
	
	static function del_measure12($id_meter, $id_uploadtype, $year){
		$return=false;
		if( $id_meter && $id_uploadtype && $year ){
			$q = "DELETE FROM measures12 WHERE ID_METER='".prepare($id_meter)."' AND ID_UPLOADTYPE='".prepare($id_uploadtype)."' AND ANNO_MS='".prepare($year)."'";
			if( mysql_query($q) ){
				// aggiorno i consumi per il mese successivo
				if($id_uploadtype==12){
					$id_uploadtype=1;
					$year++;
				} else {
					$id_uploadtype++;
				}
				$q="SELECT * FROM measures12 WHERE ID_METER='".prepare($id_meter)."' AND ID_UPLOADTYPE='".prepare($id_uploadtype)."' AND ANNO_MS='".prepare($year)."' LIMIT 1";
				$row=rs::rec2arr($q);
				if( ! empty($row['ID_MEASURE']) ){
					ob_start();
					misurazioni::calc_consumi12($row['ID_MEASURE'], $overwrite=true);
					ob_end_clean();
				}
				$return=true;
			}
		}
		return $return;
	}
	
	static function del_measure2($id_meter, $id_uploadtype, $year){
	
		if( $id_meter && $id_uploadtype && $year ){
			$q = "DELETE FROM measures WHERE ID_METER='".prepare($id_meter)."' AND ID_UPLOADTYPE='".prepare($id_uploadtype)."' AND ANNO_MS='".prepare($year)."'";
			return mysql_query($q);
		}
		return false;
	}
	
	
	/**
	 * Metodo per l'inserimento delle misurazioni mensili. Restituisce l'id della misurazione inserita.
	 *  
	 * @param  $year
	 * @param  $id_uploadtype
	 * @param  $id_meter
	 * @param  $date
	 * @param  $F1
	 * @param  $F2
	 * @param  $F3
	 * @param  $overwrite
	 * @return id_measure
	 */
	static function save_measure12($year, $id_uploadtype, $id_meter, $date, $F1, $F2, $F3, $overwrite=false){
		
		$id = false;
			$rqst = array('year', 'id_uploadtype', 'id_meter', 'date', 'F1', 'F2', 'F3' );
		foreach($rqst as $k => $v){
	
				$var[$v] = prepare( $$v );
		}
		
		if(strlen($date) > 0 && strlen($F1) > 0 && $F1 > 0)	{
			$qChk = "SELECT ID_MEASURE FROM measures12 WHERE ID_METER='{$var['id_meter']}' AND ANNO_MS='{$var['year']}' AND ID_UPLOADTYPE='{$var['id_uploadtype']}' LIMIT 1";
			$rChk = rs::rec2arr($qChk);
			if( empty($rChk['ID_MEASURE']) ){ // insert
				$q = "INSERT INTO measures12 (ANNO_MS, ID_UPLOADTYPE, ID_METER, D_MEASURE, F1, F2, F3 ) VALUES ('{$var['year']}', '{$var['id_uploadtype']}', '{$var['id_meter']}', STR_TO_DATE('{$var['date']}', '%d/%m/%Y'), '{$var['F1']}', '{$var['F2']}', '{$var['F3']}')";
				mysql_query($q);
			} else if($overwrite) { // update
				$q = "	UPDATE measures12 SET
				D_MEASURE=STR_TO_DATE('{$var['date']}', '%d/%m/%Y'),
				F1='{$var['F1']}',
				F2='{$var['F2']}',
				F3='{$var['F3']}'
				WHERE ID_MEASURE='{$rChk['ID_MEASURE']}'";
				mysql_query($q);
			
					
			}
			//echo $q;
			if(strlen($q))	{
			$id = mysql_insert_id();
					
				misurazioni::calc_consumi12($id, $overwrite);
			}
			
				
			return $id;
		} else	{
			return false;
		}
	
		
	
	}
	
	
	/**
	 * Metodo per l'inserimento delle misurazioni semestrali. Restituisce l'id della misurazione inserita.
	 * @param  $year
	 * @param  $id_uploadtype
	 * @param  $id_meter
	 * @param  $date
	 * @param  $F1
	 * @param  $F2
	 * @param  $F3
	 * @param  $overwrite
	 * @return id_measure
	 */
	static function save_measure($year, $id_uploadtype, $id_meter, $date, $F1, $F2, $F3, $overwrite=false){
	
		$rqst = array('year', 'id_uploadtype', 'id_meter', 'date', 'F1', 'F2', 'F3' );
		foreach($rqst as $k => $v){
	
				$var[$v] = prepare( $$v );
		}
	
		$qChk = "SELECT ID_MEASURE FROM measures WHERE ID_METER='{$var['id_meter']}' AND ANNO_MS='{$var['year']}' AND ID_UPLOADTYPE='{$var['id_uploadtype']}' LIMIT 1";
		//echo $qChk;
		$rChk = rs::rec2arr($qChk);
		if( empty($rChk['ID_MEASURE']) ){ // insert
			$q = "INSERT INTO measures (ANNO_MS, ID_UPLOADTYPE, ID_METER, D_MEASURE, F1, F2, F3 ) VALUES ('{$var['year']}', '{$var['id_uploadtype']}', '{$var['id_meter']}', STR_TO_DATE('{$var['date']}', '%d/%m/%Y'), '{$var['F1']}', '{$var['F2']}', '{$var['F3']}')";
			
			mysql_query($q);
		} else if($overwrite) { // update
			$q = "	UPDATE measures SET
			D_MEASURE=STR_TO_DATE('{$var['date']}', '%d/%m/%Y'),
			F1='{$var['F1']}',
			F2='{$var['F2']}',
			F3='{$var['F3']}'
			WHERE ID_MEASURE='{$rChk['ID_MEASURE']}'";
			mysql_query($q);
			}
			
			$id = mysql_insert_id();
			return $id;
	
	}
	
	
	
	/**
	 * Metodo che calcola i consumi relativi a una misurazione e li inserisce nella tabella msoutputs12
	 * 
	 * @param int $id_measure
	 */
	static function calc_consumi12($id_measure, $overwrite=false)	{
		static $mesi = array(0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
		
		
		// Leggo la misurazione
		$query = "SELECT * FROM measures12
				WHERE
				measures12.ID_MEASURE='$id_measure'";
		$r = rs::inMatrix($query);
		$misurazione = $r[0];
		
		if($misurazione['ID_UPLOADTYPE']==1)	{
			$invio_prec = 12;
			$anno_prec = $misurazione['ANNO_MS'] - 1;
		} else	{
			$invio_prec=$misurazione['ID_UPLOADTYPE']-1;
			$anno_prec = $misurazione['ANNO_MS'];
		}
			
		$meter = new meter($misurazione['ID_METER']);
		
		// Leggo la misurazione precedente
		$query = "SELECT *, DATEDIFF('{$misurazione['D_MEASURE']}', D_MEASURE) AS periodo FROM measures12
		WHERE
		measures12.ID_METER='{$misurazione['ID_METER']}' AND ANNO_MS='{$anno_prec}' AND ID_UPLOADTYPE='{$invio_prec}'";
		// echo $query.BR; 
		$r = rs::inMatrix($query);
		$misurazione_prec = $r[0];
		
		if($overwrite)
			$write = "REPLACE";
		else
			$write = "INSERT";
		
		if(is_array($misurazione_prec) && $misurazione_prec['periodo'] > 0 && $misurazione['F1'] > 0 )	{
			// Calcolo il PV
			if($misurazione['F2'] > 0)	{		// è triorario
				$pv = $misurazione['F1'] - $misurazione_prec['F1'];
				$pv += $misurazione['F2'] - $misurazione_prec['F2'];
				$pv += $misurazione['F3'] - $misurazione_prec['F3'];
			}	else	{
				$pv = $misurazione['F1'] - $misurazione_prec['F1'];
			}
			
			//normalizzo
			$npv = $pv / $misurazione_prec['periodo'] * $mesi[$misurazione['ID_UPLOADTYPE']];
			
			$pvm2 = round($pv / $meter->get_net_area(), 4);
			$npvm2 = round($npv / $meter->get_net_area(), 4);
			
			
			if($pv < 0)
				$status = 'nd';
			else
				$status = 'valid';
			
			// Se è diretto inserisco anche l'ID alloggio, altrimenti lo setto a 0
			if($meter->is_direct())	{
				$flats = sole::get_flats_by_idmeter($misurazione['ID_METER']);
					
				$sql = "$write INTO msoutputs12  (ID_MEASURE, ID_FLAT, PV, PVM2, NPV, NPVM2, CNPV, CNPVM2, STATUS)
				VALUES ('{$id_measure}', '{$flats[0]['ID_FLAT']}', $pv, $pvm2, $npv, $npvm2, $npv, $npvm2,  '$status')";
					
			} else	{
				$sql = "$write INTO msoutputs12  (ID_MEASURE, ID_FLAT, PV, PVM2, NPV, NPVM2, CNPV, CNPVM2, '$status')
				VALUES ('{$id_measure}', 0, $pv, $pvm2, $npv, $npvm2, $npv, $npvm2, 'valid')";
				}	
				
			// echo $sql.BR;
			mysql_query($sql);
		}	else {
			if($meter->is_direct())	{
				$flats = sole::get_flats_by_idmeter($misurazione['ID_METER']);
				$flat = $flats[0]['ID_FLAT'];
			} else 
				$flat = 0;
			// misurazione non disponibile
			$sql = "$write INTO msoutputs12  (ID_MEASURE, ID_FLAT, STATUS)
			VALUES ('{$id_measure}', $flat, 'nd')";
			// echo $sql;
			mysql_query($sql);
		}
	}
	
}


?>