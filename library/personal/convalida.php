<?php
class convalida{

# CONVALIDA
function get_meters_by_idbuilding($id_bld){
	# TUTTI I CONTATORI DI UN EDIFICIO
	$q = "SELECT
	flats.*,
	meters.*,
	metertypes.*
	FROM
	meters
	Left Join flats_meters ON meters.ID_METER = flats_meters.ID_METER
	Left Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
	Left Join metertypes ON meters.ID_METERTYPE = metertypes.ID_METERTYPE
	WHERE flats.ID_BUILDING = '$id_bld'
	GROUP BY meters.ID_METER
	ORDER BY meters.CODE_METER ASC
	";
	$r = rs::inMatrix($q);
	$a = array(); # $a[$id_flat][$id_meter] => $aRecord
	foreach($r as $k => $v){
		$a[$v['ID_FLAT']][$v['ID_METER']] = $v;
	}
	return $a;
}

# CONVALIDA
function get_supplymeters_by_idbuilding($id_bld, $and){
	$a = array(); # $a[$id_flat][$id_meter] => $aRecord

	# TUTTI I CONTATORI DI UN EDIFICIO
	$q = "SELECT
	flats.*,
	meters.*,
	metertypes.*
	FROM
	meters
	Left Join flats_meters ON meters.ID_METER = flats_meters.ID_METER
	Left Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
	Left Join metertypes ON meters.ID_METERTYPE = metertypes.ID_METERTYPE
	WHERE flats.ID_BUILDING = '$id_bld'
	$and
	GROUP BY meters.ID_METER
	ORDER BY meters.CODE_METER ASC";

	$r = rs::inMatrix($q);
	
	foreach($r as $k => $v){
		if(!empty($v)){
			$a[$v['ID_FLAT']][$v['ID_METER']] = $v;
		}
	}

	return $a;	
}

function get_flats_and_meters_by_idbuilding($id_bld, $and){
	$a = array(); # $a[$id_flat][$id_meter] => $aRecord

	# TUTTI I CONTATORI DI UN EDIFICIO
	$q = "SELECT
	flats.*,
	meters.*,
	metertypes.*
	FROM
	meters
	Left Join flats_meters ON meters.ID_METER = flats_meters.ID_METER
	Left Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
	Left Join metertypes ON meters.ID_METERTYPE = metertypes.ID_METERTYPE
	WHERE flats.ID_BUILDING = '$id_bld'
	$and
	GROUP BY meters.ID_METER
	ORDER BY meters.CODE_METER ASC";

	$r = rs::inMatrix($q);
	
	foreach($r as $k => $v){
		if(!empty($v)){
			$a[$v['ID_FLAT']][$v['ID_METER']] = $v;
		}
	}
	
	// tutti gli appartamenti dell'edificio permette di visualizzare la riga dell'appartamento anche se non ha misuratori diretti
	$rFlats = sole::get_flats_by_idbuilding($id_bld);
	foreach($rFlats as $k => $v){
		if(!array_key_exists($v['ID_FLAT'], $a)){
			$a[$v['ID_FLAT']] = array();
		}
	}
	return $a;	
}

function check_edited($id_measure){
	$ret = false;
	$qCk = "SELECT IS_EDITED FROM measures WHERE ID_MEASURE = '$id_measure' LIMIT 0,1";
	$rCk = rs::rec2arr($qCk);
	if($rCk['IS_EDITED'] == 1){
		$ret = true;
	}
	return $ret;
}

function update_status($id_measure, $status){
	$success = false;
	
	//if(self::check_edited($id_measure)){
	//	$status = 'Corrected';
	//}
	
	# UPDATE STATUS
	$qUpd = "UPDATE measures SET STATUS = '$status' WHERE ID_MEASURE = '$id_measure'";
	if(mysql_query($qUpd)) $success = true;
	
	return $success;
}

function order_uploads($year, $upload){
	$cols = 3; $ret = array(); $cnt = 0;
	for($i = $cols; $i > 0; $i--){
		$ret[$cnt]['year'] = $year;
		$ret[$cnt]['upload'] = $upload;
		if($upload == 1){
			$upload = 2;
			$year --;
		}
		else{
			$upload --;
		}
		$cnt ++;
	}
	return $ret;
}

function get_last_measures_by_idmeter($aM, $id_uploadtype, $year){
	global $firephp;
	$ret = array('F1' => 0, 'F2' => 0, 'F3' => 0, 'inputs' => '', 'mis' => '', 'mis-1' => '', 'mis-2' => '', 'data' => '', 'STATUS' => 'Null');
	
/*
	$Q = rs::inMatrix("SELECT * FROM tmp_ord");
	arr::stampa($Q);
	echo BR.BR.BR.BR;
*/	
	
	// query su tabella temporanea
	$q = "SELECT 
		tmp_ord.*,
		measures.*,
		meters.*,
		metertypes.METERTYPE_".LANG_DEF.",
		TO_DAYS(D_MEASURE) AS GIORNI 	
		FROM
		tmp_ord
		LEFT JOIN measures ON (tmp_ord.ANNO_MS = measures.ANNO_MS AND tmp_ord.ID_UPLOADTYPE = measures.ID_UPLOADTYPE)AND measures.ID_METER = '".$aM['ID_METER']."'
		LEFT JOIN meters ON ( measures.ID_METER = meters.ID_METER ) 
		LEFT JOIN metertypes ON meters.ID_METERTYPE = metertypes.ID_METERTYPE
		ORDER BY tmp_ord.ID ASC		
	";
	$r = rs::inMatrix($q);
	
	$i = 1; $vals = array(1 => '---', 2 => '---', 3 => '---');
	//$verify = ($cnt = count($r) < 2) ? false : true;

	$verify = true;
	$cnt = 2;
	
	foreach($r as $k => $v){ # TRATTO L'ARRAY
		$vals[$i] = $v;
		
		// print_r($v);
		
		if($i == 1){
			// echo $v['STATUS'].$v['ANNO_MS'].' '.$v['ID_SUPPLYTYPE'].' '.$v['F1'].BR;
			
			$ret['id_measure'] = $v['ID_MEASURE'];
			$ret['STATUS'] = $v['STATUS'];
			$ret['data'] = '
			<input type="text" id="dt'.$aM['ID_METER'].'" name="dt'.$aM['ID_METER'].'" class="datepicker data ottanta" value="'.dtime::my2iso($v['D_MEASURE']).'" readonly="readonly" />';
		
			// valori END e START su sostituzione contatore
			$inputs_startend = '';
			if(!empty($v['ID_CHANGE'])){
				$qChange = "SELECT 
				START_1, START_2, START_3, 
				END_1, END_2, END_3
				FROM changes WHERE ID_CHANGE='{$v['ID_CHANGE']}' LIMIT 1";
				$rChange = rs::rec2arr($qChange);
				foreach($rChange as $field => $value){
					if( !empty($value) ){
						$inputs_startend .= '
			<input type="hidden" name="'.$field.'" value="'.$value.'">';
					}
				}
			}
			
			$ret['inputs'] = '
			<input type="hidden" id="ID_CHANGE" value="'.$v['ID_CHANGE'].'" />
			<input type="hidden" id="ANNO_MS" value="'.$v['ANNO_MS'].'" />
			<input type="hidden" id="ID_UPLOADTYPE" value="'.$v['ID_UPLOADTYPE'].'" />			
			<input type="hidden" id="id_flat" value="'.$aM['ID_FLAT'].'" />
			<input type="hidden" id="id_measure" name="ID_MEASURE"  value="'.$v['ID_MEASURE'].'" />
			'.$inputs_startend;
		}

		$i ++;
	}
	
	$alt = 'NULL';
	# CONTATORE ELETTRICO MULTIORARIO
	$aMis = ($aM['ID_METERTYPE'] == 1 && $aM['HMETER'] > 1) ? array('F1', 'F2', 'F3') : array('F1');
	foreach($aMis as $a => $b){
		if($verify){
			$mis1 = $vals[1][$b];
			$mis2 = $vals[2][$b];
			$mis3 = $vals[3][$b];
			
			// assegno la classe wrong alle precedenti letture "Wrong"
			// le misurazioni privacy non vengono evidenziate in questo momento
			$class_wrong_2 = ''; 
			if($vals[2]['STATUS'] == 'Wrong'){
				$class_wrong_2 = ' wrong';
			}
			
			$class_wrong_3 = '';
			if($vals[3]['STATUS'] == 'Wrong'){
				$class_wrong_3 = ' wrong';
			}
			
			$ret['mis'] .= '
			<input type="hidden" value="'.$vals[1]['TURNAROUND'].'" class="TURNAROUND" />
			<input type="text" value="'.$mis1.'" class="ultima_misurazione ottanta" id="last'.$aM['ID_METER'].'" name="'.$aM['ID_METER'].'" readonly="readonly" />'.BR;
			$ret['mis-1'] .= mytag::in($mis2, 'li', array('class' => 'penultima_misurazione'.$class_wrong_2));
			$ret['mis-2'] .= mytag::in($mis3, 'li', array('class' => 'terzultima_misurazione'.$class_wrong_3));
			
			if(is_numeric($mis1) && is_numeric($mis2)) $cons = $mis1 - $mis2;
			else $cons = $alt;
			
			$ret[$b] = '<input type="hidden" value="'.$cons.'" class="consumo" id="'.$b.$aM['ID_METER'].'" name="'.$aM['ID_METER'].'" />';
		}
		else{
			$ret[$b] = '<input type="hidden" value="'.$alt.'" class="consumo" id="'.$b.$aM['ID_METER'].'"  name="'.$aM['ID_METER'].'" />';
		}
		$ret['inputs'] .= $ret[$b].BR;
	}
	
	if($verify){
		$cons = $vals[1]['GIORNI'] - $vals[2]['GIORNI'];
		$ret['days'] = '<input type="hidden" id="days" value="'.$cons.'" />';
	}
	else{
		$ret['days'] = '<input type="hidden" id="days" value="0" />';
	}

	if($cnt == 1){
		$ret['mis'] = '<div class="alert_msg little" style="font-size:9px; color:#cd0a0a; font-weight:normal;">No data available</div>';
	}
	$ret['mis-1'] = mytag::in($ret['mis-1'], 'ul', array());
	$ret['mis-2'] = mytag::in($ret['mis-2'], 'ul', array());

	$ret['cnt'] = $cnt;

	return $ret;
}

function create_table_tmp_ord($year, $upload){
	$aSchema = self::order_uploads($year, $upload);
	if(!rs::is_table('tmp_ord', false)){
		mysql_query("CREATE TEMPORARY TABLE tmp_ord(ID int not null AUTO_INCREMENT, ANNO_MS int not null, ID_UPLOADTYPE int not null, PRIMARY KEY (ID))");
		foreach($aSchema as $k => $v){
			mysql_query("INSERT INTO tmp_ord (ANNO_MS, ID_UPLOADTYPE) VALUES ('".$v['year']."', '".$v['upload']."')");
		}
	}
}

function chk_is_published($a){
	if(array_key_exists('id_measure', $a)){
		$is_pubblicato = rs::rec2arr("SELECT ID_MEASURE FROM msoutputs WHERE ID_MEASURE = '{$a['id_measure']}'");
		if(!empty($is_pubblicato['ID_MEASURE'])){
			return true;
		} else {
			return false;
		}
	}
}

function mk_validation($id_bld, $id_uploadtype, $year){

	// tabella temporanea di appoggio
	self::create_table_tmp_ord($year, $id_uploadtype);
	$aTord = rs::inMatrix("SELECT * FROM tmp_ord ORDER BY ID ASC");

	$aFunctions = array('npvM2');
	$ret['html'] = '<h2>Direct meters</h2>';

	$hFields = array('PV','PVM2','NPV','NPVM2','CNPV','CNPVM2','NPVM2F1');
	$iCalc = '';
	foreach($hFields as $h => $calc){
		$iCalc .= '<input type="hidden" class="'.$calc.'" value="">';
	}
	
	$lbl_last_ms = 'Upload '.$_REQUEST['upload'].' '.$_REQUEST['year'];
	
	$th_intestazione = '<th width="200">'.METER.'</th>
	<th width="90">'.DATE.'</th>
	<th width="130">'.$aTord[0]['ID_UPLOADTYPE'].'-'.$aTord[0]['ANNO_MS'].'</th>
	<th width="90">'.$aTord[1]['ID_UPLOADTYPE'].'-'.$aTord[1]['ANNO_MS'].'</th>
	<th width="90">'.$aTord[2]['ID_UPLOADTYPE'].'-'.$aTord[2]['ANNO_MS'].'</th>
	<th width="90">NPV/m2</th>
	<th width="90">P.VAL</th>
	<th width="60">Action</th>';
	
	$aPulsImages = array('Null' => 'Good-grey.png',
								'Validated' => 'Good.png',
								'Wrong' => 'Bad.png',
								'Corrected' => 'Modify.png',
								'Privacy' => 'Privacy.png'
								);
	
	$aMF = convalida::get_flats_and_meters_by_idbuilding($id_bld, " AND ID_SUPPLYTYPE = '1' AND ID_RF = '1'"); # FLATS E METERS
	$cnt_app = 0;
	$aSHARED = convalida::get_supplymeters_by_idbuilding($id_bld, " AND ID_SUPPLYTYPE = '2' AND ID_RF = '1'");
	$nSHARED = count($aSHARED);
	
	// per ogni appartamento, per ogni contatore ...
	foreach($aMF as $id_flat => $aMeters){
		
		$qOcc = "SELECT IS_OCCUPIED FROM occupancys WHERE ID_FLAT = '$id_flat' AND ID_UPLOADTYPE = '".$_REQUEST['upload']."' AND ANNO_MS = '".$_REQUEST['year']."' LIMIT 1";
		$rOcc = rs::rec2arr($qOcc);
				
		// valori su occupato
		$checked = ' checked="checked"';
		$icon_check = '<img src="'.IMG_LAYOUT.'icon-yes.png" /> '.OCCUPIED;
		
		if( $rOcc['IS_OCCUPIED'] == '' ){
			// in default il checkbox � checkato, quindi se non esiste il record, lo creo con valore a 1
			$qAddOcc = "REPLACE INTO occupancys SET ID_FLAT='$id_flat', IS_OCCUPIED='1', ID_UPLOADTYPE='{$_REQUEST['upload']}', ANNO_MS='{$_REQUEST['year']}'";
			mysql_query( $qAddOcc );
			$rOcc['IS_OCCUPIED'] = 1;
		}
		
		if($rOcc['IS_OCCUPIED'] < 1 && is_numeric($rOcc['IS_OCCUPIED'])){
			// valori di su non occupato ( ho il record )
			$checked = '';
			$icon_check = '<img src="'.IMG_LAYOUT.'icon-no.png" /> '.OCCUPIED;
		}
		
		$all_validated = sole::check_if_all_validated($aMeters, $_REQUEST['upload'], $_REQUEST['year']);
		$pubblica = '';
		if(!$all_validated){ // valori da validare, in scrittura
			$html['code'] = '<br />
			<input class="ck-occupied" type="checkbox" name="cknumid" value="1"'.$checked.' /> '.OCCUPIED;
			$html['code'] .= '<div class="action_publish">';
			$html['code'] .= '<a class="publish g-button g-button-yellow" id="dir-'.$id_flat.'">'.PUBBLICA.'</a>';
			$html['code'] .= '</div>';
		} else { 
			// valori validati, in sola lettura
			// <input class="ck-occupied" type="checkbox" name="cknumid" value="1"'.$checked.' />
			$html['code'] = '<br />
			<input class="dn" type="checkbox" name="cknumid" value="1"'.$checked.' />
			'.$icon_check;
		}

		/*
		$html['code'] = '
		<br />
		<input class="ck-occupied" type="checkbox" name="cknumid" value="1"'.$checked.' /> '.OCCUPIED.
		$pubblica; 
		*/
		
   		if(empty($nSHARED[$id_flat]) && count($aMeters) < 1){
			$html['code'] = '<input class="ck-occupied" type="checkbox" name="cknumid" value="1"'.$checked.' /> '.OCCUPIED;
		}
	
		$nofirst = $cnt_app == 0 ? '' : ' nofirst';
		$misurazioni_appartamento = ''; $new_line = '';

		$qFLAT = "SELECT * FROM flats WHERE ID_FLAT = '$id_flat'";
		$aFlat = rs::rec2arr($qFLAT);
		
		$privacy = '';
		if($aFlat['IS_PRIVACYFLAT'] == 0){
			$privacy = '<div class="alert_msg" style="padding-left:28px; color:#CD0A0A;">Privacy</div>';
		} elseif(!empty($aFlat['NAME_USER']) && !empty($aFlat['SURNAME_USER'])) {
			$privacy = '<div class="alert_msg_g" style="padding-left:28px;">'.PRIVACY_ACCETTATA.'<br /><strong>'.$aFlat['NAME_USER'].'<br />'.$aFlat['SURNAME_USER'].'</strong></div>';
		}
		else{
			$privacy = '<div class="ok_msg" style="padding-left:28px;">'.PRIVACY_ACCETTATA.'</div>';
		}
		
		$misurazioni_appartamento = '
		<table class="dark appartamento validation direct'.$nofirst.'" id="'.$aFlat['ID_FLAT'].'">
		<tr>
		<td width="90" class="bright" valign="top">
		<strong>'.$aFlat['CODE_FLAT'].'</strong>
		'.$privacy.'
		<p>'.NETAREA.BR.$aFlat['NETAREA'].'</p>
		<form method="post" id="'.$aFlat['ID_FLAT'].'">
		<input type="hidden" name="area_flat" value="'.$aFlat['NETAREA'].'" />
		'.$html['code'].'
		</form>
		</td>
		<td style="padding:0" valign="top"><!-- LIST_MEASURES --></td>
		</tr>
		</table>';
		
		$new_line .= '<table class="neutra misuratore"><tr class="center">'.$th_intestazione.'</tr>';
		
		/*
		if(count($aMeters) == 1){
		$new_line .= '
		<tr>
		<td></td>
		<td></td>
		<td valign="top" align="center"><div class="ok_msg little">'.CHECK_OCCUPIED.'</div></td>
		<td></td>
		<td></td>
		<td width="90"></td>
		<td></td></tr>';
		}*/

		foreach($aMeters as $id_meter => $rec){
			if($id_meter == 'flat') continue;
			# VALORI DELLE ULTIME TRE LETTURE (LA PRIMA � MODIFICABILE)
			$a = convalida::get_last_measures_by_idmeter($rec, $id_uploadtype, $year);
			
			if(array_key_exists('id_measure', $a)){
				$rs_measure = rs::rec2arr("SELECT ID_MEASURE, CNPVM2 FROM msoutputs WHERE ID_MEASURE = '{$a['id_measure']}'");
/* 				if(!empty($rs_measure['ID_MEASURE'])){
					$puls = '<img src="images/convalida.png" alt="Ok" class="img_puls" style="padding-left:24px;" />' .
					
					'<a class="publish g-button g-button-yellow">'.PUBBLICA.'</a>';
				} else {
					$puls = '<a class="publish g-button g-button-yellow">'.PUBBLICA.'</a>';
				} */
			}
			
			// stampa l'ultima misurazione
			$new_line .= '<tr id="'.$rec['ID_METER'].'" type="'.$rec['K2_ID_USAGE'].'">
			<td valign="top">
			'.$iCalc.'
			<ul class="simple">
			<li><strong>'.$rec['CODE_METER'].'</strong></li>
			<li>'.$rec['METERTYPE_'.LANG_DEF].'</li>
			<li>'.$rec['UNIT'].'</li>
			<li>Mt: '.$rec['MATRICULA_ID'].'</li>
			<li>Rn: '.$rec['REGISTERNUM'].'</li>
			</ul>
			</td>
			<td valign="top" class="inputvalues" align="center">'.$a['days'].$a['data'].'</td>
			<td valign="top" class="inputvalues" align="center">'.$a['mis'].'</td>';
			
			// stampa la penultima misurazione
			$new_line .= '<td class="puls_validate" valign="top" align="center">'.$a['mis-1'].'</td>';
			
			// stampa la terzultima misurazione
			$new_line .= '<td class="puls_validate" valign="top" align="center">'.$a['mis-2'].'</td>';
			
			// stampa le colonne coi valori calcolati dinamicamente
			foreach($aFunctions as $k => $val){
				$new_line .= '<td width="90" class="npvm2" tipo="dir" id_tipo="'.$id_flat.'" valign="top" align="center"><span id="'.$val.'"></span>
				<div id="turn-'.$rec['ID_METER'].'" class="turncontrols" title="'.$a['id_measure'].'">
				<img class="restart-meter" src="images/arrow-restart.png" alt="'.$rec['ID_METER'].'" title="Full scale turn around"> 
				<img class="change-meter" src="images/arrow-change.png" alt="'.$rec['ID_METER'].'" title="Change meter">
				</div>
				</td>';
			}
			# P.VAL (valore pubblicato)
			$new_line .= '<td valign="top" align="center">'.$rs_measure['CNPVM2'].'</td>';	

			$image_puls = !empty($a['STATUS']) ? $aPulsImages[$a['STATUS']] : '';
			
			$puls_image = '';
			if($a['cnt'] != 1){
				$puls_image = '<img src="images/'.$image_puls.'" name="'.$rec['ID_METER'].'" alt="dir-'.$id_flat.'" />';
			}
			
			$class_puls = 'puls_validate';
/* 			if(array_key_exists('id_measure', $a) && self::check_edited($a['id_measure']) ){
				$class_puls = 'puls_modify';
			} */
			
			$new_line .= '<td class="'.$class_puls.'" id="main-'.$rec['ID_METER'].'" valign="top" align="right">'.$puls_image.$a['inputs'].'</td>';	
			$new_line .= '</tr>';	
		}

		$ret['html'] .= str_replace('<!-- LIST_MEASURES -->', $new_line.'</table>', $misurazioni_appartamento);
		$cnt_app ++;
	}
	return $ret;
}

##################
function mk_validation_shared($id_bld, $id_uploadtype, $year){
	self::create_table_tmp_ord($year, $id_uploadtype);
	$aTord = rs::inMatrix("SELECT * FROM tmp_ord ORDER BY ID ASC");

	$aFunctions = array('npvM2');
	$ret['html'] = '';

	$th_intestazione = '
	<th width="90">Shared</th>
	<th width="200">'.METER.'</th>
	<th width="90">'.DATE.'</th>
	<th width="130">'.$aTord[0]['ID_UPLOADTYPE'].'-'.$aTord[0]['ANNO_MS'].'</th>
	<th width="90">'.$aTord[1]['ID_UPLOADTYPE'].'-'.$aTord[1]['ANNO_MS'].'</th>
	<th width="90">'.$aTord[2]['ID_UPLOADTYPE'].'-'.$aTord[2]['ANNO_MS'].'</th>
	<th width="90">NPV/m2</th>
	<th width="60">Action</th>';
	
	$aMF = convalida::get_supplymeters_by_idbuilding($id_bld, " AND ID_SUPPLYTYPE = '2' AND ID_RF = '1'"); # FLATS E METERS
	$aPulsImages = array('Null' => 'Good-grey.png',
								'Validated' => 'Good.png',
								'Wrong' => 'Bad.png',
								'Corrected' => 'Modify.png');
	$new_line = ''; $i = 1;
	foreach($aMF as $id_flat => $aMeters){
	
		$misurazioni_appartamento = ''; 
		$hFields = array('PV','PVM2','NPV','NPVM2','CNPV','CNPVM2','NPVM2F1');
		$iCalc = '';
		foreach($hFields as $h => $calc){
			$iCalc .= '<input type="hidden" class="'.$calc.'" value="">';
		}
		
		foreach($aMeters as $id_meter => $rec){
			$aFlats = sole::get_flats_by_idmeter($rec['ID_METER']);
			$lFlat = ''; $mqFlat = 0;
			foreach($aFlats as $kk => $flat){
				$lFlat .= $flat['ID_FLAT'].',';
				$mqFlat += $flat['NETAREA'];
			}
			
			$lFlat = stringa::togli_ultimo($lFlat);
			$idflats = '<input type="hidden" value="'.$lFlat.'" class="idflats">';
			$mqflats = '<input type="hidden" value="'.$mqFlat.'" class="mqflats">';
			
			$a = convalida::get_last_measures_by_idmeter($rec, $id_uploadtype, $year);
			 			
			if(array_key_exists('id_measure', $a)){
				$rs_measure = rs::rec2arr("SELECT ID_MEASURE, CNPVM2 FROM msoutputs WHERE ID_MEASURE = '{$a['id_measure']}'");
				
				if(empty($rs_measure['ID_MEASURE'])){
					$pubblica = '<a class="shared_publish g-button g-button-yellow" id="sha-'.$rec['ID_METER'].'">'.PUBBLICA.'</a>';
				} else {
					$pubblica = '';
				}
				
				
				/* 
				if(!empty($rs_measure['ID_MEASURE'])){
					$puls = '<img src="images/convalida.png" alt="Ok" class="img_puls" style="padding-left:24px;" />' .
					'<a class="shared_publish g-button g-button-yellow">'.PUBBLICA.'</a>';
				} else {
					$puls = '<a class="shared_publish g-button g-button-yellow">'.PUBBLICA.'</a>';
				} */
			}
			
			$new_line .= '
			<tr id="'.$rec['ID_METER'].'" type="'.$rec['ID_METERTYPE'].'">
			<td width="90" class="bright" valign="top">
			<form method="post" class="'.$rec['ID_FLAT'].'">
			'.$idflats.$mqflats.$iCalc.$pubblica.'
			</form>				
			</td>
			<td valign="top">
			<ul class="simple">
			<li><strong>'.$rec['CODE_METER'].'</strong></li>
			<li>Mt: '.$rec['MATRICULA_ID'].'</li>
			<li>Rn: '.$rec['REGISTERNUM'].'</li>
			</ul>
			</td>
			<td valign="top" class="inputvalues" align="center">'.$a['days'].$a['data'].'</td>
			<td valign="top" class="inputvalues" align="center">'.$a['mis'].'</td>';
			
			# MISURAZIONE -1
			$new_line .= '<td class="puls_validate" valign="top" align="center">'.$a['mis-1'].'</td>';	
		
			# MISURAZIONE -2
			$new_line .= '<td class="puls_validate" valign="top" align="center">'.$a['mis-2'].'</td>';	
		
			foreach($aFunctions as $k => $val){ # STAMPA I DIV PER I CALCOLI DINAMICI
				
				//$info_turn = '<img class="restart-meter" src="images/arrow-restart-dis.png" title="Full scale turn around">';
				//$info_change = '<img class="change-meter" src="images/arrow-change-dis.png" title="Change meter">';
				
				$new_line .= '<td width="90" class="npvm2" tipo="sha" id_tipo="'.$rec['ID_METER'].'" valign="top" align="center">
				<span id="'.$val.'"></span>
				<div id="turn-'.$rec['ID_METER'].'" class="turncontrols" title="'.$a['id_measure'].'">
				<img class="restart-meter" src="images/arrow-restart.png" alt="'.$rec['ID_METER'].'" title="Full scale turn around"> 
				<img class="change-meter" src="images/arrow-change.png" alt="'.$rec['ID_METER'].'" title="Change meter">
				</div>
				</td>';
			}
			
			$image_puls = !empty($a['STATUS']) ? $aPulsImages[$a['STATUS']] : '';
			
			$puls_image = '';
			if($a['cnt'] != 1){
				$puls_image = '<img src="images/'.$image_puls.'" name="'.$rec['ID_METER'].'" alt="sha-'.$rec['ID_METER'].'" />';
			//$puls_image = '';
			}
			
			$class_puls = 'puls_validate';
/* 			if(array_key_exists('id_measure', $a) && self::check_edited($a['id_measure']) ){
				$class_puls = 'puls_modify';
			}
 */			
			$new_line .= '<td class="'.$class_puls.'" id="main-'.$rec['ID_METER'].'" valign="top" align="right">'.$puls_image.$a['inputs'].'</td>';	
			$new_line .= '</tr>';	

		$i ++;
		}

		$ret['html'] = '
		<h2>Shared meters</h2>
		<table class="dark appartamento shared validation">
		'.$th_intestazione.$new_line.'
		</table>';
	}
	return $ret;
}

##################

function mk_validation_formulas($id_bld, $id_uploadtype, $year){
	//self::create_table_tmp_ord($year, $id_uploadtype);
	//$aTord = rs::inMatrix("SELECT * FROM tmp_ord ORDER BY ID ASC");

	include_once('../library/classes/evalmath.class.php');
	
	// tutti i misuratori formula dell'edificio
	//$mt_list = sole::get_meters_by_idbuilding($id_bld);
/* 	$i = 1;
	foreach($mtf_list as $kk => $mt){
	echo $mt['ID_METER'].' '.$i.BR;
	$i++;
	} */
	$m = new EvalMath($_REQUEST['year'], $_REQUEST['upload'], $id_bld);
	//$m->suppress_errors = true;
		
	$aFunctions = array('npvM2');
	$ret['html'] = '';

	$th_intestazione = '
	<th width="90">Formula </th>
	<th width="200">'.METER.'</th>
	<th width="90">NPVfull</th>
	<th width="90">NPV/M2</th>';
	
	$aMF = convalida::get_supplymeters_by_idbuilding($id_bld, " AND ID_RF = '2'"); # FLATS E METERS
	//$cnt_app = 0;
	
	$aPulsImages = array('Null' => 'Good-grey.png',
								'Validated' => 'Good.png',
								'Wrong' => 'Bad.png',
								'Corrected' => 'Modify.png');
	
	
	//arr::eko($aMF);
	$misurazioni_appartamento = ''; $new_line = '';
	foreach($aMF as $id_flat => $aMeters){
		foreach($aMeters as $id_meter => $rec){
			$sql = "SELECT COALESCE(ID_MEASURE, '') AS ID_MEASURE, meters.ID_SUPPLYTYPE, msoutputs.CNPVM2
						FROM measures 
						LEFT JOIN meters USING(ID_METER)
						LEFT JOIN msoutputs USING(ID_MEASURE)
						WHERE ID_METER=$id_meter AND ID_UPLOADTYPE=$id_uploadtype AND ANNO_MS=$year";
			$datimisura = rs::rec2arr($sql);
			$sql = "REPLACE INTO measures (ID_MEASURE, ID_METER, ID_UPLOADTYPE, ANNO_MS) VALUES ('".$datimisura['ID_MEASURE']."', $id_meter, $id_uploadtype, $year)";
			mysql_query($sql);
			$id_measure = mysql_insert_id();
			$aFlats = sole::get_flats_by_idmeter($id_meter);
			
			$lFlat = ''; $mqFlat = 0;
			foreach($aFlats as $kk => $flat){
				$lFlat .= $flat['ID_FLAT'].',';
				$mqFlat += $flat['NETAREA'];
			}
			
			$lFlat = stringa::togli_ultimo($lFlat);
			# $_REQUEST['ID_METER']
			
			// $m -> DEBUG = true;
			$dati = $m->e_ws($rec['FORMULA']);
			if ($dati['status']=='nd')
				$output = $dati['status'];
			else
				$output = $dati['value'];
			
			if($rec['ID_SUPPLYTYPE']==2)	//contatore condiviso (sempre valido)
				$status = 'valid';
			
			if($rec['ID_SUPPLYTYPE']==1)	//contatore diretto 
				$status = $dati['status'];
			
			$output_m2 = round($output / $mqFlat, 3);
			
			$idflats = '<input type="hidden" value="'.$lFlat.'" class="idflats">';
			$mqflats = '<input type="hidden" value="'.$mqFlat.'" class="mqflats">';
			
			$hFields = array('PV'=>'','PVM2'=>'','NPV'=>$output,'NPVM2'=>'','CNPV'=>'','CNPVM2'=>'','id_measure'=>$id_measure, 'status'=>$status );

			$iCalc = '';
			foreach($hFields as $h => $calc){
				$iCalc .= '<input type="hidden" class="'.$h.'" value="'.$calc.'">';
			}
			
//			$a = convalida::get_last_measures_by_idmeter($rec, $id_uploadtype, $year);
/* 			if(is_numeric($output) AND !is_numeric($datimisura['CNPVM2'])){
				$pubblica = '<a class="formula_publish g-button g-button-yellow">'.PUBBLICA.'</a>';
			} else {
				$pubblica = ''; // $output.' '.$datimisura['CNPVM2'] ;
			} 	 */

			$pubblica = '<a class="formula_publish g-button g-button-yellow">'.PUBBLICA.'</a>';
			if(is_numeric($datimisura['CNPVM2']))   // la misura è già stata pubblicata
				$pubblica = 'Pubblicato';
			elseif(!is_numeric($output))			// il risultato della formula non è disponibile (ad esempio dipende da formule non convalidate)
				$pubblica = '<a class="g-button">'.PUBBLICA.'</a>'; //.$dati['status'].' '.$dati['value'];
			else									// si suppone che in questo caso il risultato � corretto e quindi pubblicabile
				$pubblica = '<a class="formula_publish g-button g-button-yellow">'.PUBBLICA.'</a>';
				

			
			// controllo in tutte le altre formule se questo misuratore ($rec['CODE_METER']) è usato come formula
			/* $fl_informula = false; $fl_pubblica = true;
			foreach($mt_list as $kk => $mt){

				if(strpos($mt['FORMULA'], $rec['CODE_METER']) !== false){ // il contatore esiste in un'atra formula
					$fl_informula = true;
					
					
					$qChkVal = "SELECT ID_MEASURE FROM msoutputs WHERE ID_MEASURE = 
					(SELECT ID_MEASURE FROM measures WHERE ID_METER = '".$mt['ID_METER']."' AND ANNO_MS = '$year' AND ID_UPLOADTYPE = '$id_uploadtype')
					 LIMIT 1";
					
					
					//echo $qChkVal.' '.$id_measure.BR;
					$rChkVal = rs::rec2arr($qChkVal);
					if(!empty($rChkVal['ID_MEASURE'])){
						
						echo $rChkVal['ID_MEASURE'].BR;
						//$fl_pubblica = false;
						//break;
					}
				}
			}
			
			$pubblica = '<a class="formula_publish g-button g-button-yellow">'.PUBBLICA.'</a>';
 			if($fl_informula){
				if(!$fl_pubblica){
					$pubblica = '';
				}
			}  */
			
			$new_line .= '
			<tr id="'.$rec['ID_METER'].'" type="'.$rec['ID_METERTYPE'].'">
			<td width="90" class="bright" valign="top">
			<form method="post" class="'.$rec['ID_FLAT'].'">
			'.$idflats.$mqflats.$iCalc.'
			<br /><br />'.$pubblica.'</form>				
			</td>
			
			<td valign="top">
			<ul class="simple">
			<li><strong>'.$rec['CODE_METER'].'</strong></li>
			<li>Mt: '.$rec['MATRICULA_ID'].'</li>
			<li>Rn: '.$rec['REGISTERNUM'].'</li>
			<li>Formula: '.$rec['FORMULA'].'</li>
			</ul>
			</td>
			<td valign="top" class="inputvalues" align="center" onclick="alert(\''.$dati['explain'].'\')">'.$output.'</td>
			<td valign="top" class="inputvalues" align="center">'.$output_m2.'</td>
			';
			
			/* 		
				foreach($aFunctions as $k => $val){ # STAMPA I DIV PER I CALCOLI DINAMICI
				$new_line .= '<td width="90" valign="top" align="center"><span id="'.$val.'"></span></td>';
			} */
			
			$puls_image = '';
			$class_puls = '';
			
		//	$new_line .= '<td class="'.$class_puls.'" valign="top" align="right"></td>';	
			$new_line .= '</tr>';	

		}

		$ret['html'] = '
		<h2>Formula meters</h2>
		<table class="dark appartamento formula validation">
		'.$th_intestazione.$new_line.'
		</table>';
	}
	return $ret;
} 

function update_date($id, $date){
	$ret = array('success' => false, 'days' => 0);
	# CONTROLLO CORRETTEZZA DATA
	$dt = new dtime($date);
	
	# UPDATE DATA MISURAZIONE
	if(!$dt -> err()){
		echo "UPDATE measures SET D_MEASURE = '$date' WHERE ID_MEASURE = '$id'";
	}
	
	$qM = "SELECT ID_METER FROM measures WHERE ID_MEASURE = '$id'";
	$rM = rs::rec2arr($qM);
	
	$idMeter = $rM['ID_METER'];
	
	# CALCOLO GIORNI
	$q = "SELECT 
	TO_DAYS(D_MEASURE) AS GIORNI 
	FROM measures
	WHERE measures.ID_MEASURES = '$id_meter'
	AND measures.IS_DEL =  '0'
	ORDER BY measures.D_MEASURE DESC 
	LIMIT 0, 2";

	$cons = $vals[1]['GIORNI'] - $vals[2]['GIORNI'];
	# RESTITUZIONE ARRAY PER JSON
}

function delete_msoutputs($id_building, $id_upload, $year, $mensile=false){
	$success = false;
	if($mensile)	{
		$outputs = 'msoutputs12';
		$measures = 'measures12';
	}	else	{
		$outputs = 'msoutputs';
		$measures = 'measures';
	}

	$q = "SELECT
	meters.ID_METER
	FROM
	meters
	Left Join flats_meters ON meters.ID_METER = flats_meters.ID_METER
	Left Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
	Left Join metertypes ON meters.ID_METERTYPE = metertypes.ID_METERTYPE
	WHERE flats.ID_BUILDING = '$id_building'
	GROUP BY meters.ID_METER";
	
	$r = rs::inMatrix($q);
	
	$ids = '';
	foreach($r as $k => $v){
		$ids .= "'".$v['ID_METER']."', ";
	}
	
	if(!empty($ids)){
		$ids = substr($ids,0, -2);
		
		// query ricerca msoutputs
		$qms = "SELECT
		{$measures}.ID_MEASURE 
		FROM {$measures}
		WHERE
		ANNO_MS = '$year' AND
		ID_UPLOADTYPE = '$id_upload' AND
		ID_METER IN ($ids)
		";
		
		$rms = rs::inMatrix($qms);
		$cnt = 0; $where = '';
		foreach($rms as $k => $v){
			$where .= "ID_MEASURE = '{$v['ID_MEASURE']}' OR ";
			$cnt ++;
		}
		
		
		
		if(!empty($where)){
			$where = substr($where, 0, -4);
			
			if($mensile)	{
				$qdel = "DELETE FROM {$measures} WHERE $where";
				if(mysql_query($qdel)){
					$success = $cnt;
				}
			}
				
			$qchk = "SELECT * FROM {$outputs} WHERE $where";
			$rchk = rs::inMatrix($qchk);
			
			if(count($rchk) > 0){
				
				$qdel = "DELETE FROM {$outputs} WHERE $where";
				if(mysql_query($qdel)){
					$success = $cnt;
				}
			}
		}	
	}
	return $success;
}

function lunghezza_periodo($id_building, $id_uploadtype){
	$id_federation = sole::get_federation_by_id_building($id_building);
	$qPeriodo = "SELECT ND_SUMMER, ND_WINTER FROM federations WHERE ID_FEDERATION='$id_federation' LIMIT 1";
	$rPeriodo = rs::rec2arr($qPeriodo);
	
	
	
	return $id_uploadtype == 1 ? $rPeriodo['ND_SUMMER'] : $rPeriodo['ND_WINTER'];
}


}
?>