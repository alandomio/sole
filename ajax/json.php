<?php
include ('../init.php');
session_start();
error_reporting(7);

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
header("Cache-Control: no-cache, must-revalidate" );
header("Pragma: no-cache" );
header("Content-type: text/x-json");

if ($_REQUEST['action'] == 'getbuildings')	{
	$federation = $_REQUEST['fed'];
	if (is_numeric($_REQUEST['bld']))
		$where = "WHERE ID_BUILDING=" . $_REQUEST['bld'];
	else if (is_numeric($_REQUEST['hc']))
		$where = "WHERE buildings.ID_HCOMPANY=" . $_REQUEST['hc'];
	else if (is_numeric($_REQUEST['fed']))
		$where = "WHERE hcompanys.ID_FEDERATION=" . $federation;
	else
		$where = "WHERE FALSE";
	
	$dati = rs::inMatrix("SELECT ID_BUILDING AS id, LAT_BLD, LNG_BLD FROM buildings
					LEFT JOIN hcompanys ON hcompanys.ID_HCOMPANY=buildings.ID_HCOMPANY
					$where");
	echo json_encode($dati);
}

if ($_REQUEST['action'] == 'get_user_buildings_coords')	{
	$r = sole::building_user($_REQUEST['id']);
	$dati = array();
	foreach($r as $k => $v){
		if(!empty($v['LAT_BLD']) && !empty($v['LNG_BLD']) ){
			$dati[$k]['id'] = $v['ID_BUILDING'];
			$dati[$k]['lat'] = $v['LAT_BLD'];
			$dati[$k]['lng'] = $v['LNG_BLD'];
			$dati[$k]['name'] = $v['NAME_BLD'];
			$dati[$k]['code'] = $v['CODE_BLD'];
		}
	}
	echo json_encode($dati);
}

elseif ($_REQUEST['action'] == 'select_hc')	{
	$federation = $_REQUEST['fed'];
	$query = "SELECT ID_HCOMPANY AS optionValue, CODE_HC AS optionDisplay FROM hcompanys WHERE hcompanys.ID_FEDERATION=" . $federation . " ORDER BY CODE_HC ASC";

	$dati = rs::inMatrix($query);
	echo json_encode($dati);
}

elseif ($_REQUEST['action'] == 'select_bld')	{
	$federation = $_REQUEST['fed'];
	$hcompany = $_REQUEST['hc'];
	
	if($hcompany!='null')	{
		$query = "SELECT ID_BUILDING AS optionValue, CODE_BLD AS optionDisplay FROM buildings WHERE buildings.ID_HCOMPANY=" . $hcompany . " ORDER BY CODE_BLD ASC";
	}
	else	{
		$query = "SELECT ID_BUILDING AS optionValue, CODE_BLD AS optionDisplay 
					FROM buildings 
					LEFT JOIN hcompanys ON hcompanys.ID_HCOMPANY=buildings.ID_HCOMPANY
					WHERE hcompanys.ID_FEDERATION=" . $federation . " ORDER BY CODE_BLD ASC";
	}

	$dati = rs::inMatrix($query);
	echo json_encode($dati);
}


elseif ($_REQUEST['action'] == 'select_flat')	{
	$federation = $_REQUEST['fed'];
	$hcompany = $_REQUEST['hc'];
	$building = $_REQUEST['bld'];
	
	if(strlen($building))	{
		$query = "SELECT ID_FLAT AS optionValue, CODE_FLAT AS optionDisplay FROM flats WHERE flats.ID_BUILDING=" . $building . " ORDER BY CODE_FLAT ASC";
	}
	else	{
		$query = "SELECT ID_BUILDING AS optionValue, CODE_BLD AS optionDisplay 
					FROM buildings 
					LEFT JOIN hcompanys ON hcompanys.ID_HCOMPANY=buildings.ID_HCOMPANY
					WHERE hcompanys.ID_FEDERATION=" . $federation . " ORDER BY CODE_BLD ASC";
	}

	$dati = rs::inMatrix($query);
	echo json_encode($dati);
}


elseif ($_REQUEST['action'] == 'select_hc2bld')	{
	$hc =  $_REQUEST['hc'];
	$query = "SELECT ID_BUILDING AS optionValue, CODE_BLD AS optionDisplay FROM buildings WHERE buildings.ID_HCOMPANY=" . $hc . " ORDER BY CODE_BLD ASC";
	$dati = rs::inMatrix($query);
	echo json_encode($dati);
}

elseif ($_REQUEST['action'] == 'put_contatore_appartamento')	{

	$id_val = $_REQUEST['id_contatore'];
	$id = $_REQUEST['id_appartamento'];


	if($_REQUEST['valore']=='true')	{
		$q = "INSERT INTO flats_meters (ID_METER, ID_FLAT) VALUES ('$id_val', '$id')";
	}
	else{
		$q = "DELETE FROM flats_meters WHERE ID_METER = '$id_val' AND ID_FLAT = '$id'";
	}
	mysql_query($q);
	$messaggio = "<b>Attenzione</b> si è verificato un errore";
	
	echo json_encode(array('success' => true));
}

elseif ($_REQUEST['action'] == 'add_measure'){
	$db = mydb::post2db($id = null, $table = 'measures'); 
	echo json_encode(array('success' => $db['result'], 'message' => $db['message']));
}

elseif ($_REQUEST['action'] == 'crud_contatore'){
	
	$id = array_key_exists('ID_METER', $_POST) ? $_POST['ID_METER'] : NULL;
	
	if(array_key_exists('D_REMOVE', $_POST)){
		if(empty($_POST['D_REMOVE']) || $_POST['D_REMOVE'] == '00/00/0000'){
			$_POST['D_REMOVE'] = false; unset($_POST['D_REMOVE']);
		}
		
		if($id){
			mysql_query("UPDATE meters SET D_REMOVE='0000-00-00' WHERE ID_METER='$id'");
		}
	}
	
	// esegue l'insert o l'update
 	$db = mydb::post2db($id, 'meters'); 
	$meter_id = $db['id'];
	$id = $db['id'];
	
	$message = $db['message'];
	
	// sincronizzazione multicheck
	$scheda = new nw('meters');
	$scheda -> ext_table(array('meterpropertys', 'outputs', 'supplytypes','rfs'));
	
	$scheda -> many_to_many(array(	
				'flats_meters' => array(
				'id' 	=> 'ID_FLAT',
				'title' => 'REGISTERNUM',
				'ext'	=> 'flats',
				'where'	=> "ID_METER",
				'lbl'	=> "Contatori",
				'file' => 'meters_flats_ext.php'
			)
		)
	);

	$scheda -> many_to_many_tot($id);	
	$my_vars = new ordinamento(array());
	
	if(array_key_exists('flat', $_POST)){ # CONTATORE DIRECT (UN SOLO APPARTAMENTO ASSOCIATO)
		$qdel = "DELETE FROM flats_meters WHERE ID_METER = '$meter_id'";
		mysql_query($qdel);
		$qadd = "INSERT INTO flats_meters (ID_FLAT, ID_METER) VALUES ('".$_POST['flat']."', '$meter_id')";
		mysql_query($qadd);
	}
	elseif(count($my_vars -> ba) > 0){ # CONTATORE SHARED (UNO O PIU APPARTAMENTI ASSOCIATI)
		$qdel = "DELETE FROM flats_meters WHERE ID_METER = '$meter_id'";
		mysql_query($qdel);
	
		foreach($my_vars -> ba as $k => $val){
			
			if(array_key_exists('bb'.$k, $_POST)){
				$qadd = "INSERT INTO flats_meters (ID_FLAT, ID_METER) VALUES ('$k', '$meter_id')";
				mysql_query($qadd);
			}
		}
	}
	
	// operazioni da effettuare sui contatori di produzione
	if(!empty($meter_id)){
		
		$delete_productions = false;
	
		if( $_POST['ID_METERTYPE'] == 1 ){ // energia elettrica
		
			if( !empty($_POST['SIZE']) ){
			
				$SIZE = str_replace(',', '.', $_POST['SIZE']);
				if(!is_numeric($SIZE)){
					$SIZE = 0;
				}
			
				$qRep = "REPLACE INTO meters_productions (ID_METER, SIZE) VALUES ('$meter_id', '".prepare4sql($SIZE)."' )";
				mysql_query($qRep);
			}
			elseif( !empty($_POST['IS_DOUBLE'])){
				$qRep = "REPLACE INTO meters_productions (ID_METER) VALUES ('$meter_id')";
				mysql_query($qRep);
			} else {
				$delete_productions = true;
			}
		}

		elseif( $_POST['ID_METERTYPE'] == 5 ){ // acqua
			if(!empty($_POST['altre_utenze'])){
				
				if(!empty($_POST['SUM_DIVISIONAL'])){
					$qRep = "REPLACE INTO meters_productions (ID_METER, SUM_DIVISIONAL) VALUES ('$meter_id', '".prepare4sql($_POST['SUM_DIVISIONAL'])."')";
					mysql_query($qRep);
				} else {
					$qRep = "REPLACE INTO meters_productions (ID_METER) VALUES ('$meter_id')";
					mysql_query($qRep);
				}
			} else {
				$delete_productions = true;
			}
		}
		
		elseif( $_POST['ID_METERTYPE'] == 2 ){ // energia termica
			if(!empty($_POST['THERMAL_TYPE'])){
			
				$SIZE = str_replace(',', '.', $_POST['SIZE_THERMAL']);
				if(!is_numeric($SIZE)){
					$SIZE = 0;
				}

				$tty = $_POST['THERMAL_TYPE'];
				$aReplaceFields = array();
				if($tty == 1){ // solare termico
					$qRep = "REPLACE INTO meters_productions (ID_METER, SIZE, ACS, ETE, THERMAL_TYPE) VALUES ('$meter_id', '".prepare4sql($SIZE)."', '".prepare4sql($_POST['ACS'])."', '".prepare4sql($_POST['ETE'])."', '$tty')";
					mysql_query($qRep);
				}
				elseif($tty == 2){ // generatore
					$qRep = "REPLACE INTO meters_productions (ID_METER, FUEL, THERMAL_TYPE) VALUES ('$meter_id', '".prepare4sql($_POST['FUEL'])."', '$tty')";
					mysql_query($qRep);
				}
			} else {
				$delete_productions = true;
			}
		}
		if($delete_productions){
			mysql_query("DELETE FROM meters_productions WHERE ID_METER='$id'");
		}
	}
	
	if($db['result']){ # OPERAZIONI DA FARE DOPO L'INSERT
		if(array_key_exists('NAME_METER', $_POST)){
			$meter_name = sole::mk_namemeter($meter_id, $_POST['NAME_METER']);
			mysql_query("UPDATE meters SET CODE_METER = '$meter_name' WHERE ID_METER = '$meter_id'");
		}
		
		if(array_key_exists('ID_RF', $_POST)){
			if($_POST['ID_RF'] == 1){ // FOMULA sempre a null su contatori REAL
				mysql_query("UPDATE meters SET FORMULA = NULL WHERE ID_RF = '1' AND ID_METER = '$meter_id'");
			}
		}
		
		if(!empty($id_building))
			mysql_query("UPDATE meters SET ID_BUILDING = '$id_building' WHERE ID_METER = '$meter_id'");
		
		$msg = new myerr();
		$msg -> add_ack( INS_RECORD.' ('.$meter_name.')');
		$message = $msg -> print_msg(false);
	}
	
	if(!empty($scheda -> mmBox)){
		$scheda -> mmSyncro($id, $my_vars -> mm);
	}
	echo json_encode(array('success' => $db['result'], 'message' => $message, 'mode' => $db['mode'], 'id' => $db['id']));
}

elseif ($_REQUEST['action'] == 'dele_measure'){
	$msg = new myerr();	
	$id = $_REQUEST['id_measure'];
	
	$q = "UPDATE measures SET IS_DEL = '1' WHERE ID_MEASURE = '$id'";
	if(!empty($id) && mysql_query($q)){
		$msg -> add_ack('Record '.DELETED);
		$success = true;
	} else {
		$msg -> add_err('Error');
		$success = false;
	}
	$message = $msg -> print_msg(false);
	echo json_encode(array('success' => $success, 'message' => $message));
}

elseif ($_REQUEST['action'] == 'put_misure_convalidate'){ 
	$idUser = $_REQUEST['idu'];
	$af = rs::get_fields('consumptions');
	
	$rec = array();
	foreach($af as $k => $fld){
		if(array_key_exists($fld, $_REQUEST)){
			$val = $_REQUEST[$fld] == 'Nan' ? 0 : $_REQUEST[$fld];
			$rec[$fld] = $val;
		}
	}
	$rec['D_CONVALIDA'] = date('Y-m-d', time());
 	$rec['ID_USER'] = $idUser;
	$q = "INSERT INTO consumptions ".rs::dmlfield($rec)." VALUES ".rs::dmlval($rec);
	mysql_query($q);

	$qUpd = "UPDATE measures SET IS_CONFIMED_MS = '1' WHERE ID_MEASURE = '".$_REQUEST['ID_MEASURE']."'";
	mysql_query($qUpd);

}

elseif ($_REQUEST['action'] == 'put_outputs'){
	$idUser = $_REQUEST['idu'];
	
	$qms = "SELECT ID_UPLOADTYPE, ANNO_MS FROM measures WHERE ID_MEASURE = '{$_REQUEST['ID_MEASURE']}'";
	$rms = rs::rec2arr($qms);
	
	$af = rs::get_fields('msoutputs');
	
	$rec = array();
	foreach($af as $k => $fld){
		if(array_key_exists($fld, $_REQUEST)){
			$val = $_REQUEST[$fld] == 'Nan' ? 0 : $_REQUEST[$fld];
			$rec[$fld] = $val;
		}
	}
	
	if(array_key_exists('OCCUPIED', $_REQUEST)){
		$is_occupied = $_REQUEST['OCCUPIED'] == 'true' ? '1' : '0';

		$qRep = "REPLACE INTO occupancys (ID_FLAT, IS_OCCUPIED, ID_UPLOADTYPE, ANNO_MS) VALUES ('{$_REQUEST['ID_FLAT']}', '$is_occupied', '{$rms['ID_UPLOADTYPE']}', '{$rms['ANNO_MS']}' )";
		mysql_query($qRep);	
	}

 	$rec['ID_USER'] = $idUser;
	$q = "REPLACE INTO msoutputs ".rs::dmlfield($rec)." VALUES ".rs::dmlval($rec);
	if(mysql_query($q))
		echo json_encode(array('success' => true));
}

elseif($_REQUEST['action'] == 'set_stato_occupato'){

	$id_flat = $_REQUEST['id'];
	$year = $_REQUEST['year'];
	$id_uploadtype = $_REQUEST['id_uploadtype'];
	
	if($_REQUEST['occupato'] == 'false'){
		$is_occupied = 0;
	} else {
		$is_occupied = 1;
	}

	$qRep = "REPLACE INTO occupancys (ID_FLAT, IS_OCCUPIED, ID_UPLOADTYPE, ANNO_MS) VALUES ('$id_flat', '$is_occupied', '$id_uploadtype', '$year' )";
	$success = mysql_query($qRep);	

	echo json_encode(array('success' => $success));
}

elseif($_REQUEST['action'] == 'update_status'){
	$success = convalida::update_status($_REQUEST['id_measure'], $_REQUEST['status']);
	echo json_encode(array('success' => $success));
}

elseif ($_REQUEST['action'] == 'get_measures_list'){
	blocks::lista_misurazioni($_REQUEST['id_contatore'], true);
}

elseif($_REQUEST['action']  == 'form_insert_meter'){
	$idg = $_REQUEST['idg'];
	$form = '';
	if($idg < 3){
		ob_start();
		include 'meters_ins_form.php';
		$form = ob_get_clean();
	}
	echo json_encode(array( 'form' => $form ));	
}

elseif($_REQUEST['action']  == 'form_users'){
	ob_start();
	include 'users_form.php';
	$form = ob_get_clean();
	echo json_encode(array('form' => $form));
}

elseif($_REQUEST['action'] == 'get_real_meters_list_by_id_building'){
	$ret = '';
	$a = sole::get_real_by_bld($_REQUEST['id']);
	foreach($a as $mis){
		$lbl = $mis['CODE_METER']; 
		$ret .= '<li class="view_meter" alt="'.$mis['ID_METER'].'" >'.$lbl.'</li>';
	}
	if(!empty($ret)){
		$ret = mytag::in($ret, 'ul', array());
	}
	$ret = '<h2>'.METERS.':</h2>'.$ret;
	echo json_encode(array('list' => $ret));
}

elseif($_REQUEST['action'] == 'get_meters_list_by_id_building'){
	$idg = $_REQUEST['idg'];

	$msg = new myerr();
	$message = '';
	$ret = '';
	
	$link_sinottica = io::a('meters-sinottica.php', array('id_building' => $_REQUEST['id']), TABELLA_SINOTTICA, array('target' => '_blank', 'title' => TABELLA_SINOTTICA, 'class' => 'g-button'));
	
	$a = sole::get_meters_by_idbuilding($_REQUEST['id']);
	
	$aDuplicates = arr::duplicate($a, 'CODE_METER');
	$flag_dup = false;
	
	foreach($a as $mis){
		$class_duplicate = '';
		if(in_array($mis['CODE_METER'], $aDuplicates)){
			$class_duplicate = ' alert_duplicate';
			$flag_dup = true;
		}
		
		$class_removed = '';
		if($mis['D_REMOVE'] != '0000-00-00'){
			$class_removed = ' alert_removed';
		}
		
		$lbl = $mis['CODE_METER'];
		$ret .= '<li class="view_meter'.$class_duplicate.$class_removed.'" alt="'.$mis['ID_METER'].'" >'.$lbl.'</li>';
	}
	
	if($flag_dup){
		$msg -> add_err(DUPLICATED_CODEMETER);
		$message = $msg -> print_msg(false);
	}
	if(!empty($ret)){
	
		if($idg < 3){
			$ret = '<li class="no-border">'.$link_sinottica.'</li>'.$ret;
			$ret = mytag::in($ret, 'ul', array());
		} else {
			$ret = '<li class="no-border">'.$link_sinottica.'</li>';
			$ret = mytag::in($ret, 'ul', array());
		}
	}
	$ret = '<h2>'.METERS.':</h2>'.$ret;

	echo json_encode(array('list' => $ret, 'duplicated' => $flag_dup, 'message' => $message));
}

elseif ($_REQUEST['action'] == 'get_days'){
	$diff_days = 0;
	$data = $_REQUEST['data'];
	$id_misuratore = $_REQUEST['id'];
	
	$aM = sole::get_last_measures($id_misuratore, 2);
	
	if(count($aM)>1){	
		$d1 = dtime::my2db($data);
		$d2 = $aM[1]['D_MEASURE'];
		$diff_days = dtime::diffdays($d1, $d2);
	}
	echo json_encode(array('days' => $diff_days));
}

elseif($_REQUEST['action'] == 'get_multicheck_flats_list_by_id_building'){
	$ret = sole::get_multicheck_flats_list_by_id_building($_REQUEST['id'], $_REQUEST['mode'], false);
	echo json_encode(array('list' => $ret));
}
// vecchio metodo che andrà eliminato dopo i test
elseif($_REQUEST['action']  == 'form_insert_measures'){
	
	$id = $_REQUEST['id'];
	$upload_type = $_REQUEST['upload_type'];
	$year = $_REQUEST['year'];

 	ob_start();
	include 'measures_ins_form.php';
	$form = ob_get_clean();
	echo json_encode(array('form' => $form));
	
}


elseif($_REQUEST['action']  == 'measures_2_form'){
	$id_building = prepare( $_REQUEST['id'] );
	$year = prepare( $_REQUEST['year'] );
	
	$form = '';
	if(is_numeric($id_building) && is_numeric($year)){
		ob_start();
		include 'measures_2_form.php';
		$form = ob_get_clean();
	}
	
	echo $form;
}

elseif($_REQUEST['action']  == 'measures_12_form'){
	
	$id_building = prepare( $_REQUEST['id'] );
	$year = prepare( $_REQUEST['year'] );
	
	$form = '';
	if(is_numeric($id_building) && is_numeric($year)){
		ob_start();
		include 'measures_12_form.php';
		$form = ob_get_clean();
	}
	
	echo $form;
}


elseif($_REQUEST['action']  == 'update_single_measure'){
	$success = false;
	
	$id = $_REQUEST['id'];
	$valore = $_REQUEST['valore'];
	$field = $_REQUEST['field'];
	
	$valore = str_replace(',', '.', $valore);
	
	if(!is_numeric($valore)){ # È UNA DATA
		$valore = dtime::my2db($valore);
	}

	$q = "UPDATE measures SET $field = '$valore' WHERE ID_MEASURE = '$id'";
	if(mysql_query($q)) $success = true;

	echo json_encode(array('success' => $success));
}

elseif($_REQUEST['action']  == 'update_measures'){
	$success = false;
	$aFields = json_decode($_REQUEST['json']); # MISURAZIONI (UNA O PIU')
	
	$sUPDATE = '';
	foreach($aFields as $k => $v){
		$sUPDATE .= 'F'.($k+1)."=".$v.", ";
	}
	if(array_key_exists('DATE', $_REQUEST)){
		$data = dtime::my2db($_REQUEST['DATE']);
		$sUPDATE .= "D_MEASURE = '$data', ";
	}
	$sUPDATE .= "IS_EDITED = '1'";
	
	$q = "UPDATE measures SET $sUPDATE WHERE ANNO_MS = '".$_REQUEST['ANNO_MS']."' AND ID_METER = '".$_REQUEST['ID_METER']."' AND ID_UPLOADTYPE = '".$_REQUEST['ID_UPLOADTYPE']."'";

	if(mysql_query($q)){ $success = true; }
	echo json_encode(array('success' => $success, 'q' => $q));
}

elseif($_REQUEST['action']  == 'save_measure12'){
	$success = false;
	$reset_value = false;
	$error = '';
		
	$rqst = array('year', 'id_uploadtype', 'id_meter', 'fieldname');
	foreach($rqst as $k => $v){
		$var[$v] = prepare( $_REQUEST[$v] );
	}
	
	$qChk = "SELECT ID_MEASURE, {$var['fieldname']} AS CAMPO FROM measures12 WHERE ID_METER='{$var['id_meter']}' AND ANNO_MS='{$var['year']}' AND ID_UPLOADTYPE='{$var['id_uploadtype']}' LIMIT 1";
	$rChk = rs::rec2arr($qChk);
	
	if($_REQUEST['fieldname'] == 'D_MEASURE'){
		$reset_value = dtime::my2iso( $rChk['CAMPO'] );
		if( ! $reset_value)
			$reset_value = '';
	} else {
		$reset_value = $rChk['CAMPO'];
	}
	
	if($_REQUEST['fieldname'] == 'D_MEASURE'){
		$var['value'] = dtime::s2d( $_REQUEST['value'] );
		if( ! $var['value'] ){
			echo json_encode(array('success' => false, 'reset_value' => $reset_value, 'error' => 'Campo non valido'));
			exit();
		}
	} else {
		if( ! empty($_REQUEST['value']) ){
			if( ! is_numeric($_REQUEST['value']) || $_REQUEST['value'] <= 0){
				echo json_encode(array('success' => false, 'reset_value' => $reset_value, 'error' => 'Campo non valido'));
				exit();
			}
		}
		$var['value'] = prepare( $_REQUEST['value'] );
	}
	
	if( ! empty($var['value']) && $var['value'] > 0 && empty($error)){
		if( empty($rChk['ID_MEASURE']) ){ // insert
			$mode = 'insert';
			$q = "INSERT INTO measures12 (ANNO_MS, ID_UPLOADTYPE, ID_METER, {$var['fieldname']}) VALUES ('{$var['year']}', '{$var['id_uploadtype']}', '{$var['id_meter']}', '{$var['value']}')";
		} else { // update
			$mode = 'update';
			$q = "UPDATE measures12 SET {$var['fieldname']}='{$var['value']}' WHERE ID_MEASURE='{$rChk['ID_MEASURE']}'";
		} 
		if(mysql_query($q)){ 
			$success = true;
			if($mode == 'insert'){
				$rChk['ID_MEASURE'] = mysql_insert_id();
			}
			ob_start();
			// aggiorno il consumo per il mese
			misurazioni::calc_consumi12($rChk['ID_MEASURE'], $overwrite=true);

			// aggiorno i consumi per il mese successivo
			if($var['id_uploadtype']==12){
				$var['id_uploadtype']=1;
				$var['year']++;
			} else {
				$var['id_uploadtype']++;
			}
			$q="SELECT * FROM measures12 WHERE ID_METER='{$var['id_meter']}' AND ID_UPLOADTYPE='{$var['id_uploadtype']}' AND ANNO_MS='{$var['year']}' LIMIT 1";
			$row=rs::rec2arr($q);
			if( ! empty($row['ID_MEASURE']) ){
				misurazioni::calc_consumi12($row['ID_MEASURE'], $overwrite=true);
			}
			ob_end_clean();
		}
	} else {
		// l'utente cerca di cancellare il valore, ma non glielo permettiamo, dovrà usare la funzione 'cestino'
		// restituisco il valore trovato in db
		$error="Valore vuoto cancellare usando il check Azione e facendo click su cestino";
	}
	echo json_encode(array('success' => $success, 'reset_value' => $reset_value, 'error' => $error));
}

elseif($_REQUEST['action']  == 'save_measure2'){
	$success = false;
	$reset_value = false;
	$error = '';
	
	$rqst = array('year', 'id_uploadtype', 'id_meter', 'fieldname');
	foreach($rqst as $k => $v){
		$var[$v] = prepare( $_REQUEST[$v] );
	}
	
	$qChk = "SELECT ID_MEASURE, {$var['fieldname']} AS CAMPO FROM measures WHERE ID_METER='{$var['id_meter']}' AND ANNO_MS='{$var['year']}' AND ID_UPLOADTYPE='{$var['id_uploadtype']}' LIMIT 1";
	$rChk = rs::rec2arr($qChk);
	
	if($_REQUEST['fieldname'] == 'D_MEASURE'){
		$reset_value = dtime::my2iso( $rChk['CAMPO'] );
		if( ! $reset_value)
			$reset_value = '';
	} else {
		$reset_value = $rChk['CAMPO'];
	}
	
	if($_REQUEST['fieldname'] == 'D_MEASURE'){
		$var['value'] = dtime::s2d( $_REQUEST['value'] );
		if( ! $var['value'] ){
			echo json_encode(array('success' => false, 'reset_value' => $reset_value, 'error' => 'Campo non valido'));
			exit();
		}
	} else {
		if( !empty($_REQUEST['value']) ){
			if( ! is_numeric($_REQUEST['value']) || $_REQUEST['value'] <= 0 ){
				echo json_encode(array('success' => false, 'reset_value' => $reset_value, 'error' => 'Campo non valido'));
				exit();
			}
		}
		$var['value'] = prepare( $_REQUEST['value'] );
	}
	
	if( ! empty($var['value']) && $var['value'] > 0 && empty($error)){
		if( empty($rChk['ID_MEASURE']) ){ // insert
			$q = "INSERT INTO measures (ANNO_MS, ID_UPLOADTYPE, ID_METER, {$var['fieldname']}) VALUES ('{$var['year']}', '{$var['id_uploadtype']}', '{$var['id_meter']}', '{$var['value']}')";
		} else { // update
			$q = "UPDATE measures SET {$var['fieldname']}='{$var['value']}' WHERE ID_MEASURE='{$rChk['ID_MEASURE']}'";
		} 
		
		if(mysql_query($q)){
			$success = true; 
		}
	} else {
		// l'utente cerca di cancellare il valore, ma non glielo permettiamo, dovrà usare la funzione 'cestino'
		// restituisco il valore trovato in db
		$error="Valore vuoto cancellare usando il check Azione e facendo click su cestino";
	}
	
	echo json_encode(array('success' => $success, 'reset_value' => $reset_value, 'error' => $error));
}

elseif($_REQUEST['action'] == 'insert_measure'){
	$msg = new myerr();	
	$success = false;
	$id = false;
	
	$aF = array(
	'ID_METER' => $_REQUEST['ID_METER'],
	'D_MEASURE' => dtime::my2db($_REQUEST['D_MEASURE']),
	'ID_UPLOADTYPE' => $_REQUEST['ID_UPLOADTYPE'],
	'ANNO_MS' => $_REQUEST['ANNO_MS'],
	'F1' => str_replace(',', '.', $_REQUEST['F1']),
	'F2' => str_replace(',', '.', $_REQUEST['F2']),
	'F3' => str_replace(',', '.', $_REQUEST['F3'])
	);
	
	$multi = sole::is_multi($aF['ID_METER']) ? 3 : 1;
	
	$err = false;
	for($i = 1; $i<=$multi; $i++){
		if(trim($aF['F'.$i]) == ''){
			$msg -> add_err('F'.$i.': '.NOT_NULL_ERR);
			$err = true;
		}
		elseif(!is_numeric($aF['F'.$i])){
			$msg -> add_err('F'.$i.': '.IN_ERR_SINTAX);
			$err = true;
		}
	}
	
	if(empty($aF['D_MEASURE'])){
		$msg -> add_err(D_MEASURE.': '.NOT_NULL_ERR);
		$err = true;
	}
	if(empty($aF['ANNO_MS'])){
		$msg -> add_err(ANNO_MS.': '.NOT_NULL_ERR);
		$err = true;
	}
	if(empty($aF['ID_UPLOADTYPE'])){
		$msg -> add_err(ID_UPLOADTYPE.': '.NOT_NULL_ERR);
		$err = true;
	}
	
	$q = '';
	if(!$err){
		$sFIELDS = '';
		$sVALUES = '';
		
		foreach($aF as $k => $v){
			$sFIELDS .= $k.', ';
			$sVALUES .= "'".$v."', ";
		}
		$sFIELDS = stringa::togli_ultimi($sFIELDS, 2);
		$sVALUES = stringa::togli_ultimi($sVALUES, 2);
		
		$q = "REPLACE INTO measures ( $sFIELDS ) VALUES ( $sVALUES )";
		if(mysql_query($q)){ $success = true; $msg -> add_ack(INS_RECORD); $id = mysql_insert_id(); }
	}
	$message = $msg -> print_msg(false);
	echo json_encode(array('success' => $success, 'message' => $message, 'id' => $id));
}

elseif($_REQUEST['action']  == 'formula_meters'){
	
	# LISTA MISURATORI REAL
	$aM = sole::get_meters_by_idbuilding($_REQUEST['id']);
	$list = '';
	foreach($aM as $k => $v){
		//$block = mytag::in($v['CODE_METER'], );
		$list .= mytag::in($v['CODE_METER'], 'li', array('title' => $v['CODE_METER'], 'class' => 'button meter-formula'));
	}
	$list = mytag::in($list, 'ul', array());
	echo json_encode(array('list' => $list));
}

elseif($_REQUEST['action'] == 'conversion'){ # GESTISCE IL PARAMETRO DI CONVERSIONE PER LA FEDERAZIONE
	$success = false;
	$idtype = $_REQUEST['idtype'];
	$idfederation = $_REQUEST['idfederation'];
	$valore = str_replace(',', '.', $_REQUEST['valore']);
	
	$qChk = "SELECT *  FROM federations_conversions WHERE ID_FEDERATION = '$idfederation' AND ID_METERTYPE = '$idtype' LIMIT 0,1";
	$rChk = rs::rec2arr($qChk);
	
	$mode = !empty($rChk['ID_FEDERATION']) ? 'upd' : 'ins';
	
	$q = '';
	if($mode == 'ins'){
		if(!empty($valore) && is_numeric($valore)){
			$q = "INSERT INTO federations_conversions (ID_FEDERATION, ID_METERTYPE, CONVERSION) VALUES ('$idfederation', '$idtype', '$valore')";
		}
	}
	elseif($mode == 'upd'){
		if(empty($valore)){
			$q = "DELETE FROM federations_conversions WHERE ID_FEDERATION = '$idfederation' AND ID_METERTYPE = '$idtype'";
		}
		else{
			if(is_numeric($valore)){
				$q = "UPDATE federations_conversions SET CONVERSION = '$valore' WHERE ID_FEDERATION = '$idfederation' AND ID_METERTYPE = '$idtype'";
			}
		}
	}
	
	if(!empty($q)){
		if(mysql_query($q)){
			$success = true;
		}
	}
	
	echo json_encode(array('success' => $success, 'q' => $q, 'mode' => $mode));
}

elseif($_REQUEST['action'] == 'get_riepilogo_contatore'){
	$idmeter = $_REQUEST['idmeter'];
	$ret = array('flats' => '', 'meter' => '', 'usages' => '');
	
	$qM = "SELECT * FROM meters WHERE ID_METER = '$idmeter'";
	$rM = rs::rec2arr($qM);

	$ret = sole::get_scheda_meter($idmeter);
	$ret['flats'] = sole::get_multicheck_flats_list_by_id_building($_REQUEST['id_building'], $rM['ID_SUPPLYTYPE'], $idmeter);

	//$ret['usages'] = sole::get_multicheck_usages_list_by_id_meter($idmeter);
	
	$ret['meter'] = '<h2>'.METER.':</h2>'.$ret['main'];
	$ret['valori_iniziali'] = '<h2>'.INIT_VALUES.':</h2>'.$ret['valori_iniziali'];
	
	echo json_encode($ret);
}

elseif ($_REQUEST['action'] == 'crud_users'){
	$id = array_key_exists('ID_USER', $_POST) ? $_POST['ID_USER'] : NULL;
	$db = mydb::post2db($id, 'users'); 
	$id = $db['id'];
	
	if($db['result']){ # OPERAZIONI DA FARE DOPO L'INSERT
	}

	echo json_encode(array('success' => $db['result'], 'message' => $db['message'], 'mode' => $db['mode'], 'id' => $db['id']));
}

elseif ($_REQUEST['action'] == 'crud_activation_users'){ # PER L'UTENTE HHU QUANDO SI REGISTRA SUL SITO
	$id = array_key_exists('ID_USER', $_POST) ? $_POST['ID_USER'] : NULL;
	
	$is_inform = 0; $is_privacyflat = 0;
	if(array_key_exists('IS_PRIVACYFLAT', $_REQUEST) && $_REQUEST['IS_PRIVACYFLAT'] == 1){
		$is_privacyflat = 1;
	}
	if(array_key_exists('IS_INFORM', $_REQUEST) && $_REQUEST['IS_INFORM'] == 1){
		$is_inform = 1;
	}		

	$err = new myerr();
	# CONTROLLI EXTRA
	if(!stringa::is_email($_POST['USER'])){
		$err -> add_err(USER.': '.IN_ERR_EMAIL);
	}
	if($_POST['PASSWORD']!=$_POST['PSW_RPT']){
		$err -> add_err(ERR_RPT_PASSWORD);
	}
	if(empty($_POST['NAME'])){
		$err -> add_err(NAME.': '.NOT_NULL_ERR);
	}
	if(empty($_POST['SURNAME'])){
		$err -> add_err(SURNAME.': '.NOT_NULL_ERR);
	}
	
	if(empty($is_privacyflat)){
		$err -> add_err(IS_PRIVACYFLAT.': '.NOT_NULL_ERR);
	}
	
	if(!empty($err -> err)){
		$db = array('result' => false,
		'message' => $err -> print_msg(false),
		'mode' => '',
		'id' => '0'
		);
	} else {
		$db = mydb::post2db($id, 'users'); 
		$id = $db['id'];
		
		if($db['result']){ # OPERAZIONI DA FARE DOPO L'INSERT
			$code = $_REQUEST['ACTIVATION_CODE'];
			
			$qFlat = "UPDATE flats SET ID_USER = '$id',
			NAME_USER = '".prepare4sql($_POST['NAME'])."',
			SURNAME_USER = '".prepare4sql($_POST['SURNAME'])."',
			IS_PRIVACYFLAT='$is_privacyflat', IS_INFORM = '$is_inform' WHERE ACTIVATION_CODE = '$code'";
			mysql_query($qFlat);
			
			# RICAVO L'ID_HCOMPANY
			$qHC = "SELECT
			hcompanys.ID_HCOMPANY,
			hcompanys.ID_FEDERATION
			FROM
			hcompanys
			Left Join buildings ON hcompanys.ID_HCOMPANY = buildings.ID_HCOMPANY
			Left Join flats ON buildings.ID_BUILDING = flats.ID_BUILDING 
			WHERE flats.ACTIVATION_CODE = '$code'
			LIMIT 0,1
			";
			$rHC = rs::rec2arr($qHC);
			$id_hc = $rHC['ID_HCOMPANY'];
			
			$code_user = md5($_POST['USER'].date('Ymd H:i:s', time()));
			$password = md5($_POST['PASSWORD']);
			
			$qUser = "UPDATE users SET ID_GRUPPI = '5', CODE='$code_user', ID_HCOMPANY='$id_hc', PASSWORD='$password' WHERE ID_USER = '$id'";
			mysql_query($qUser);
			
			mysql_query("REPLACE INTO users_federations SET ID_USER = '$id', ID_FEDERATION = '".$rHC['ID_FEDERATION']."'");
			
			$send_message = "";
			$send_message .= '<strong>'.REGISTRAZIONE_ESEGUITA.'</strong>'.BR.BR;
			$send_message .= USER.": ".$_POST['USER'].BR;
			$send_message .= PASSWORD.": ".$_POST['PASSWORD'].BR.BR;
			$send_message .= NAME.": ".$_POST['NAME'].BR;
			$send_message .= SURNAME.": ".$_POST['SURNAME'].BR.BR;
			//$send_message .= USER.": ".$_POST['USER'].BR;
			$send_message .= PRIVACY_ACCETTATA.BR;
			if($is_inform == 1) $send_message .= IS_INFORM.BR;
			
			# INVIO LA MAIL
			ob_start();
			include CONTATTI.'layout_new_user.php';
			$body = ob_get_clean();	
			###########################################
			$err = ''; $ack = '';
			$mail = new PHPMailer();
			//$mail->IsSMTP();
			//$mail->Host = "mail.rigosalotti.it";
			//$mail->SMTPDebug = 1;
			//$mail->AddReplyTo(MAIL_SUPPORT, NOME_SITO);
			$mail->SetFrom('noreply@sole-project.com', 'Sole Project');
			$mail->AddAddress($_POST['USER'], $_POST['NAME'].' '.$_POST['SURNAME']);
			$mail->Subject = ATTIVAZIONE_UTENTE;
			$mail->MsgHTML($body);
			$mail -> Send();
			//if(!$mail->Send()) { $err[] = ERR_SENDPASSWORD; }
			###############
			
		}
	}
	echo json_encode(array('success' => $db['result'], 'message' => $db['message'], 'mode' => $db['mode'], 'id' => $db['id']));
	
}

elseif ($_REQUEST['action'] == 'select_users'){
	$id_building = $_REQUEST['id_bld'];
	$selected = $_REQUEST['selected'];
	
	$qHC = "SELECT ID_HCOMPANY FROM buildings WHERE ID_BUILDING = '$id_building'";
	$rHC = rs::rec2arr($qHC);
	
	$rec_users = sole::get_users_from_hc($rHC['ID_HCOMPANY'], false);
	$select = io::select_from_recordset($rec_users, 'ID_USER', 'USER', $selected, '- '.CHOOSE.' HHU', array('name' => 'ID_USER'));
	
	echo json_encode(array('select' => $select));
}

elseif ($_REQUEST['action'] == 'get_graph_info'){
	$id_graph = $_REQUEST['id'];
	
	$query = "SELECT * FROM graphtypes WHERE ID = $id_graph";

	

	$dati = rs::inMatrix($query);
	echo json_encode($dati[0]);
}
elseif ($_REQUEST['action'] == 'form_login'){
	ob_start();
	include 'login_form.php';
	$form = ob_get_clean();
	echo json_encode(array('form' => $form));
}

elseif ($_REQUEST['action'] == 'check_activation_code'){
	$msg = new myerr();	

	$success = false;
	$form = '';
	$message = '';
	
	$code = $_REQUEST['code'];
	$qCheck = "SELECT ID_FLAT FROM flats WHERE ACTIVATION_CODE = '$code'";
	$rCheck = rs::rec2arr($qCheck);

	if(!empty($rCheck['ID_FLAT'])){
		$success = true;
		# CREO L'HTML PER IL DIALOG
		ob_start();
		include 'users_activation_form.php';
		$form = ob_get_clean();		
	}
	else{
		$msg -> add_err(ERR_INVALID_CODE);
		$message = $msg -> print_msg(false);
	}
	echo json_encode(array('success' => $success, 'form' => $form, 'message' => $message));
}

elseif ($_REQUEST['action'] == 'get_message'){
	# INIZIALIZZO LE VARIABILI
	list($code, $mode) = request::get(array('code' => NULL, 'mode' => 'ack'));
	$ret = ''; $success = false; $messaggio = '';
	
	if(!empty($code) && defined($code)){
		$messaggio = constant($code);
		$success = true;
	}

	$msg = new myerr();
	if($mode == 'ack'){
		$msg -> add_ack($messaggio);
	}
	else{
		$msg -> add_err($messaggio);
	}
	
	$ret = $msg -> print_msg(false);
	
	echo json_encode(array('success' => $success, 'message' => $ret));
}

elseif ($_REQUEST['action'] == 'delete_meter'){
	# INIZIALIZZO LE VARIABILI
	list($id) = request::get(array('id' => NULL));
	$success = false; $messaggio = '';
	
	$msg = new myerr();
	
	if(sole::delete_meter($id)){
		$msg -> add_ack(DEL_RECORD);
		$success = true;
	}
	else{
		$msg -> add_err(ERR_DELETE_RECORD);
	}
	
	$ret = $msg -> print_msg(false);
	echo json_encode(array('success' => $success, 'message' => $ret));
}
elseif ($_REQUEST['action'] == 'list_outputs'){
	$msg = new myerr();
	list($id, $year, $mode, $id_user) = request::get(array('id' => NULL, 'year' => NULL, 'mode' => NULL, 'id_user' => NULL));
	
	if($mode == 'PRODUCTION'){
		include_once 'output_production.php';
	} else {
		$outputs = new outputs($id, $year, $mode, $id_user);
		//$outputs -> debug(); // = true;
		$outputs -> print_table();
	}
}
elseif ($_REQUEST['action'] == 'list_outputs12'){
	$msg = new myerr();
	list($id, $year, $mode, $id_user) = request::get(array('id' => NULL, 'year' => NULL, 'mode' => NULL, 'id_user' => NULL));

	$outputs = new outputs12($id, $year, $mode, $id_user);
	// $outputs -> debug(); // = true;
	$outputs -> print_table();
}
elseif ($_REQUEST['action'] == 'get_sinottica'){
	$output = array();
	$id_building = $_REQUEST['id_building'];
	
	$r = sole::get_meters_sinottica_by_idbuilding($id_building);
	
	$output['total'] = count($r);
	$output['page'] = 1;

	$k = 0;
	foreach ($r as $row) {	
		$riga = array();
		foreach ($row as $v)	{
			$riga[] = $v;
		}
		$output['rows'][$k]['cell'] = $riga;
		$k++;
	}

	echo json_encode($output);
}

elseif ($_REQUEST['action'] == 'delete_all_ms'){ // cancella le convalide di un edificio per l'invio indicato
	
	$success = false; $cnt = 0;
	$id_building = $_REQUEST['building'];
	$year = $_REQUEST['year'];
	$upload_type = $_REQUEST['upload_type'];
	if($_REQUEST['type']=='mensile'){
		// $delete = convalida::delete_msoutputs($id_building, $upload_type, $year, 'mensile');
		// carico la lista di misuratori mensili per l'edificio
		$meters12=sole::get_real_12_by_id_bld( $id_building);
		foreach( $meters12 as $row ){
			// dopo ogni eliminazioni ricalcola l'output
			$delete = misurazioni::del_measure12($row['ID_METER'], $upload_type, $year);
		}
	} else {
		$delete = convalida::delete_msoutputs($id_building, $upload_type, $year, false);
	}
	
	if($delete){
		$success = true;
		$cnt = $delete;
	}
	echo json_encode(array('success' => $success, 'cnt' => $cnt));
} 


elseif ($_REQUEST['action'] == 'delete_single_measure'){ // cancella una misurazione non convalidata
	$success = false; $message = 'Nessuna operazione eseguita';
	$id_measure = $_REQUEST['id'];
	
	if($id_measure){
		$q = "SELECT msoutputs.ID_MEASURE 
		FROM msoutputs 
		WHERE ID_MEASURE = '$id_measure'";
		$r = rs::rec2arr($q);
		if(!empty($r['ID_MEASURE'])){
			$message = 'Misurazione convalidata, impossibile cancellarla';
		} else {
		
			$qc = "SELECT ID_MEASURE FROM measures WHERE ID_MEASURE = '$id_measure'";
			$rc = rs::rec2arr($qc);
			
			if(!empty($rc['ID_MEASURE'])){		
				$qd = "DELETE FROM measures WHERE ID_MEASURE = '$id_measure'";
				if(mysql_query($qd)){
					$success = true;
					$message = 'Misurazione cancellata';
				}
			} else {
				$message = 'Impossibile eliminare, misurazione non presente';
			}
		}
	}
	
	echo json_encode(array('success' => $success, 'message' => $message));
} 

elseif ($_REQUEST['action'] == 'select_energy'){
	$id_building = $_REQUEST['bld'];
	$dati = array();
	if(strlen($id_building))	{
		$types = sole::get_metertypes_by_idbuilding($id_building, 0);
	}
	
	foreach($types as $k => $v){
		$dati[$k]['optionValue'] = $v['ID_METERTYPE'];
		$dati[$k]['optionDisplay'] = $v['METERTYPE_'.LANG_DEF];
	} 
	
	echo json_encode($dati);	
}

elseif($_REQUEST['action'] == 'get_turn_around_values'){
	$id_meter = $_REQUEST['id'];
 	$id_measure = $_REQUEST['id_measure'];
	
	// misurazione attuale
	$last_measure = rs::rec2arr("SELECT * FROM measures WHERE ID_MEASURE='$id_measure' LIMIT 1");
	
	// imposto i valori per la misurazione precedente
	$anno_ms = $last_measure['ANNO_MS'];
	$id_uploadtype = 1;
	if($last_measure['ID_UPLOADTYPE'] == 1){
		$anno_ms = $last_measure['ANNO_MS'] - 1;
		$id_uploadtype = 2;
	}
	
	// penultima misurazione rispetto a quella attuale
	$before_last_measure = rs::rec2arr("SELECT * FROM measures WHERE ID_METER='$id_meter' AND ANNO_MS='$anno_ms' AND ID_UPLOADTYPE='$id_uploadtype'");	
	
	$qMeter = "SELECT * FROM meters WHERE ID_METER = '$id_meter' LIMIT 1";
	$rMeter = rs::rec2arr($qMeter);
	
	$is_valore_calcolato = false;
	
	$t = $rMeter['END_1'];
	if( $rMeter['SCALA_MT'] == '0.000' || empty($rMeter['SCALA_MT']) ){
		$is_valore_calcolato = true;
		$valore_calcolato = sole::deduci_fondoscala($before_last_measure['F1']);
		$valori = FONDOSCALA_CALCOLATO.'<br />F1: '.$valore_calcolato;
	} else {
		$valori = FONDOSCALA_IN_DB.'<br />F1: '.$rMeter['SCALA_MT'];
	}

	
/* 	if($is_valore_calcolato){
		$valori = FONDOSCALA_CALCOLATO.': '.$sValues;
	} else {
		$valori = FONDOSCALA_IN_DB.': '.$rMeter['SCALA_MT'];
	} */

	echo json_encode(array('row' => $rMeter, 'valori' => $valori, 'intestazione' => FULL_SCALE_TURN_AROUND));
}

elseif($_REQUEST['action'] == 'turn_around_meter'){ // eseguo il giro fondoscala per il contatore ( no i triorari )
	$success = 'r';
	$message = ERR_SAVE;

	$id_meter = $_REQUEST['id'];
	$id_measure = $_REQUEST['id_measure'];
	
	$qMeter = "SELECT * FROM meters WHERE ID_METER = '$id_meter' LIMIT 1";
	$rMeter = rs::rec2arr($qMeter);

	// misurazione attuale
	$last_measure = rs::rec2arr("SELECT * FROM measures WHERE ID_MEASURE='$id_measure' LIMIT 1");
	
	// imposto i valori per la misurazione precedente
	$anno_ms = $last_measure['ANNO_MS'];
	$id_uploadtype = 1;
	if($last_measure['ID_UPLOADTYPE'] == 1){
		$anno_ms = $last_measure['ANNO_MS'] - 1;
		$id_uploadtype = 2;
	}
	
	// penultima misurazione rispetto a quella attuale
	$before_last_measure = rs::rec2arr("SELECT * FROM measures WHERE ID_METER='$id_meter' AND ANNO_MS='$anno_ms' AND ID_UPLOADTYPE='$id_uploadtype'");
	
	// ricavo il nuovo valore del fondoscala se non è stato specificato per il contatore
	// i misuratori triorari non hanno attiva questa procedura
	if( $rMeter['SCALA_MT'] == '0.000' || empty($rMeter['SCALA_MT'])){
		$rMeter['SCALA_MT'] = sole::deduci_fondoscala($before_last_measure['F1']);

		// il valore di fondoscala è stato accettato, quindi aggiorno il db
		$qUpdateFondoscala = "UPDATE meters SET SCALA_MT = '{$rMeter['SCALA_MT']}' WHERE ID_METER = '$id_meter'";
		mysql_query($qUpdateFondoscala);
	}

	// indico la misurazione come fondoscala
	$qUpdateTurnaround = "UPDATE measures SET TURNAROUND='{$rMeter['SCALA_MT']}' WHERE ID_MEASURE = '{$last_measure['ID_MEASURE']}'";
	if(mysql_query($qUpdateTurnaround)){
		$success = 'g';
		$message = TURN_METER_EXECUTED;
	}
	// $consumo = ($rMeter['SCALA_MT'] - $before_last_measure['F1']) + $last_measure['F1'];
	
	echo json_encode(array('id_meter' => $id_meter, 'turnaround' => $rMeter['SCALA_MT'], 'success' => $success, 'message' => $message ));
}

elseif($_REQUEST['action'] == 'replace_meter_form'){ // form di sostituzione contatore
	$id_meter = $_GET['id'];
	
	$qMeter = "SELECT * FROM meters WHERE ID_METER='$id_meter' LIMIT 1";
	$rMeter = rs::rec2arr($qMeter);
	
	if(!empty($rMeter['ID_METER'])){
		$fasce = $rMeter['HMETER'];
		// campi ultime misurazioni e prime misurazioni
		$old = '<div><label>'.MATRICULA_ID.':</label><span>'.$rMeter['MATRICULA_ID'].'</span></div>';
		$new = '<div><label>'.MATRICULA_ID.':</label><input type="text" value="" name="MATRICULA_ID" /></div>';
		
		for($i=1; $i<=$fasce; $i++){
			$old .= '<div><label>'.constant('END_'.$i).':</label><input type="text" value="" name="END_'.$i.'" /></div>';
			$new .= '<div><label>'.constant('START_'.$i).':</label><input type="text" value="" name="START_'.$i.'" /></div>';
		}
	}
	echo json_encode(array( 'row' => $rMeter, 'old_inputs' => $old, 'new_inputs' => $new ));
}

elseif($_REQUEST['action'] == 'save_replace_meter'){ // salva la sostituzione del contatore
	// controllo dati
	$success = true;
	$id = 0;
	$message = ERR_SAVE;
	$measures = array();
	
	$ends = array();
	$starts = array();
	
	// http://sole.infonair.com/ajax/json.php?action=save_replace_meter&ID_METER=702&ID_MEASURE=7194&D_CHANGE=01%2F04%2F2010&END_1=&END_2=&END_3=&MATRICULA_ID=&START_1=&START_2=&START_3=
	$ID_MEASURE = 		prepare4sql($_POST['ID_MEASURE']);
	$MATRICULA_ID = 	prepare4sql($_POST['MATRICULA_ID']);
	
	$data['D_CHANGE'] = dtime::check($_POST['D_CHANGE']) ? dtime::my2db($_POST['D_CHANGE']) : false;
	$data['ID_METER'] = $_POST['ID_METER'];
	
	$qMeter = "SELECT * FROM meters WHERE ID_METER='{$data['ID_METER']}' LIMIT 1";
	$rMeter = rs::rec2arr($qMeter);
	
	for($i=1; $i<=$rMeter['HMETER']; $i++){
		$data['END_'.$i] = str_replace(',','.',$_POST['END_'.$i]);
		$data['START_'.$i] = str_replace(',','.',$_POST['START_'.$i]);
		
		if(!is_numeric($data['END_'.$i]) || $data['END_'.$i] <= 0 ){
			$data['END_'.$i] = 0;
			$measures['end_'.$i] = 0;			
		} else {
			$measures['end_'.$i] = $data['END_'.$i];
		}
		
		if(!is_numeric($data['START_'.$i]) || $data['START_'.$i] <= 0){
			$data['START_'.$i] = 0;
			$measures['start_'.$i] = 0;
		} else {
			$measures['start_'.$i] = $data['START_'.$i];
		}
		
	}
	
	if(empty($data['ID_METER']) || empty($ID_MEASURE) || !$data['D_CHANGE']){
		$success = false;
	}
	
	if($success){
		foreach($data as $field => $value){
			$fields .= $field.", ";
			$values .= "'".prepare4sql($value)."', ";
		}
		
		$qIns = "INSERT INTO changes (".substr($fields, 0, -2).") VALUES (".substr($values, 0, -2).")";
 		if(mysql_query($qIns)){
			$id = mysql_insert_id();
			
			// aggiorno il valore della misurazione relativa
			$qMeasure = "UPDATE measures SET ID_CHANGE='$id' WHERE ID_MEASURE='$ID_MEASURE'";
			mysql_query($qMeasure);

			// aggiorno i dati del contatore
			$qMeter = "UPDATE meters SET MATRICULA_ID='$MATRICULA_ID' WHERE ID_METER='{$data['ID_METER']}'";
			mysql_query($qMeter);
			
			$message = DATA_SAVED;
		}
	}
	echo json_encode(array_merge(array( 'success' => $success, 'message' => $message, 'id_change' => $id, 'id_meter' => $data['ID_METER'], 'id_measure' => $ID_MEASURE), $measures ));
}

elseif($_REQUEST['action'] == 'del_measure12'){ // elimina una misurazione mensile
	$success = false;
	
	$id_meter = prepare( $_REQUEST['id_meter'] );
	$id_uploadtype = prepare( $_REQUEST['id_uploadtype'] );
	$year = prepare( $_REQUEST['year'] );
	
	if($id_meter && $id_uploadtype && $year){
		$success = misurazioni::del_measure12($id_meter, $id_uploadtype, $year);
	}
	echo json_encode( array( 'success' => $success ) );
}

elseif($_REQUEST['action'] == 'del_measure2'){ // elimina una misurazione semestrale
	$success = false;
	
	$id_meter = prepare( $_REQUEST['id_meter'] );
	$id_uploadtype = prepare( $_REQUEST['id_uploadtype'] );
	$year = prepare( $_REQUEST['year'] );
	
	if($id_meter && $id_uploadtype && $year){
		$success = misurazioni::del_measure2($id_meter, $id_uploadtype, $year);
	}
	echo json_encode( array( 'success' => $success ) );
}
?>