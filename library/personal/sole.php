<?php
class sole{

function get_users_from_hc($id_hc, $id_group){ # RESTITUISCE GLI UTENTI COLLEGATI AD UN APPARTAMENTO DELLA CORRISPONDENTE HC
	$AND = '';
	if(!empty($id_group)){
		$AND = " AND users.ID_GRUPPI = '$id_group' ";
	}
	$q = "SELECT * FROM users
	WHERE users.ID_HCOMPANY = '$id_hc'
	$AND";
	return rs::inMatrix($q);
}

function get_buildings_from_hc($id_hc){
	$q = "SELECT * FROM buildings WHERE ID_HCOMPANY = '$id_hc' ORDER BY CODE_BLD ASC";
	return rs::inMatrix($q);
}


function get_flats_num($id_building)	{
	$sql = "SELECT COUNT(ID_FLAT) AS flats FROM flats WHERE ID_BUILDING=$id_building GROUP BY ID_BUILDING";
	$dati = rs::rec2arr($sql);
	return $dati['flats'];
}

function building_user($id){ # RESTITUISCE TUTTI I RECORD DEGLI EDIFICI LEGATI ALL'ID UTENTE INDICATO
	global $user;
	
	if(empty($id)){ # L'ATTUALE USER LOGGATO
		$id = $user -> aUser['ID_USER'];
		$rU = $user -> aUser;
	}
	else{ # CARICO I DATI DI UN ALTRO USER SPECIFICATO COME ARGOMENTO DELLA FUNZIONE
		$qU = "SELECT * FROM users WHERE ID_USER = '$id'";
		$rU = rs::rec2arr($qU);
	}
	if($rU['ID_GRUPPI'] == '1'){ # ADMIN vede tutto
		$qB = "SELECT * FROM buildings";
	}
	elseif($rU['ID_GRUPPI'] == '2'){ # FM > federazione > housing companies > buildings
		$qB = "SELECT
		buildings.*,
		federations.ID_USER
		FROM
		federations
		Inner Join hcompanys ON federations.ID_FEDERATION = hcompanys.ID_FEDERATION
		Inner Join buildings ON hcompanys.ID_HCOMPANY = buildings.ID_HCOMPANY
		WHERE
		federations.ID_USER = '$id'";
	}
	elseif($rU['ID_GRUPPI'] == '3'){ # MHCU
		$qB = "SELECT buildings.* FROM buildings
		Inner Join hcompanys ON buildings.ID_HCOMPANY = hcompanys.ID_HCOMPANY
		WHERE hcompanys.ID_USER = '$id'";
	}
	if($rU['ID_GRUPPI'] == '4' || $rU['ID_GRUPPI'] == '5'){ # HCU HHU tabella molti a molti buildings_users
		$qB = "SELECT buildings.*
		FROM buildings_users
		Inner Join buildings ON buildings_users.ID_BUILDING = buildings.ID_BUILDING
		WHERE buildings_users.ID_USER = '$id'";
	}
	return rs::inMatrix($qB);
}

function get_meters_sinottica_by_idbuilding($id_building){

	$q = "SELECT
	meters.ID_METER,
	meters.CODE_METER,
	meters.MATRICULA_ID,
	meters.REGISTERNUM,
	usages.DESCRIPTOR_".LANG_DEF." AS K2_ID_USAGE,
	meters.NAME_METER,
	meters.SCALA_MT,
	DATE_FORMAT(D_FIRSTVALUE, '%d/%c/%Y') AS D_FIRSTVALUE,
	meters.START_1,
	meters.START_2,
	meters.START_3,
	meters.END_1,
	meters.END_2,
	meters.END_3,
	meterpropertys.METERPROPERTY,
	supplytypes.SUPPLYTYPE,
	rfs.RF,
	outputs.OUTPUT,
	meters.FORMULA,
	meters.A,
	meters.B
	FROM
	meters
	Inner Join flats_meters ON meters.ID_METER = flats_meters.ID_METER
	Inner Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
	Inner Join metertypes ON meters.ID_METERTYPE = metertypes.ID_METERTYPE
	LEFT JOIN descriptors AS usages ON meters.K2_ID_USAGE = usages.ID_DESCRIPTOR
	LEFT JOIN meterpropertys USING(ID_METERPROPERTY)
	LEFT JOIN supplytypes USING(ID_SUPPLYTYPE)
	LEFT JOIN rfs USING(ID_RF)
	LEFT JOIN outputs USING(ID_OUTPUT)
	WHERE flats.ID_BUILDING = '$id_building' AND
	meters.IS_DEL <> '1'
	GROUP BY meters.ID_METER
	ORDER BY meters.CODE_METER ASC
	";
	$r = rs::inMatrix($q);
	
	foreach($r as $k => $v){
		$aF = self::get_flats_by_idmeter($v['ID_METER']);
		$flats = '';
		foreach($aF as $kk => $vv){
			$flats .= $vv['CODE_FLAT'].'('.$vv['NETAREA'].'mq) ';
		}
		$r[$k]['FLATS'] = $flats;
		unset($r[$k]['ID_METER']);
	}
	return $r;
}

function get_meters_by_idbuilding($id){
	# TUTTI I CONTATORI DI UN EDIFICIO
	$q = "SELECT
	flats.*,
	meters.*,
	metertypes.*
	FROM
	meters
	Inner Join flats_meters ON meters.ID_METER = flats_meters.ID_METER
	Inner Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
	Inner Join metertypes ON meters.ID_METERTYPE = metertypes.ID_METERTYPE
	WHERE flats.ID_BUILDING = '$id' AND
	meters.IS_DEL <> '1'
	GROUP BY meters.ID_METER
	ORDER BY meters.CODE_METER ASC
	";
	$r = rs::inMatrix($q);
	return $r;	
}

function get_meters_production_by_idbuilding($id){
	// contatori produzione edificio
	$q = "SELECT
	flats.*,
	meters.*,
	metertypes.*,
	meters_productions.*
	FROM
	meters
	Inner Join flats_meters ON meters.ID_METER = flats_meters.ID_METER
	Inner Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
	Inner Join metertypes ON meters.ID_METERTYPE = metertypes.ID_METERTYPE
	Left Join meters_productions ON meters.ID_METER = meters_productions.ID_METER
	WHERE flats.ID_BUILDING = '$id' AND
	meters_productions.ID_METER IS NOT NULL AND
	meters.IS_DEL <> '1'
	GROUP BY meters.ID_METER
	ORDER BY meters.CODE_METER ASC
	";
	$r = rs::inMatrix($q);
	return $r;	
}

function get_meters_formula_by_idbuilding($id){
	# TUTTI I CONTATORI FORMULA DI UN EDIFICIO
	$q = "SELECT
	flats.*,
	meters.*,
	metertypes.*
	FROM
	meters
	Inner Join flats_meters ON meters.ID_METER = flats_meters.ID_METER
	Inner Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
	Inner Join metertypes ON meters.ID_METERTYPE = metertypes.ID_METERTYPE
	WHERE flats.ID_BUILDING = '$id' AND
	meters.ID_RF = '2' AND
	meters.IS_DEL <> '1'
	GROUP BY meters.ID_METER
	";
	
	$r = rs::inMatrix($q);
	return $r;	
}

function get_meters_by_idflat($id){
	# TUTTI I CONTATORI DI UN EDIFICIO
	$q = "SELECT
	flats.*,
	meters.*,
	metertypes.*
	FROM
	meters
	Inner Join flats_meters ON meters.ID_METER = flats_meters.ID_METER
	Inner Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
	Inner Join metertypes ON meters.ID_METERTYPE = metertypes.ID_METERTYPE
	WHERE flats_meters.ID_FLAT = '$id' AND
	meters.IS_DEL <> '1'
	GROUP BY meters.ID_METER
	ORDER BY meters.ID_METER ASC
	";
	$r = rs::inMatrix($q);
	return $r;	
}

function get_meters_all_by_idflat($id){
	// lista di tutti i contatori dell'edificio, anche quelli eliminati
	$q = "SELECT
	flats.*,
	meters.*
	FROM
	meters
	Inner Join flats_meters ON meters.ID_METER = flats_meters.ID_METER
	Inner Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
	WHERE flats_meters.ID_FLAT = '$id'
	GROUP BY meters.ID_METER
	ORDER BY meters.ID_METER ASC
	";
	return rs::inMatrix($q);
}

function get_real_by_bld($id){ // LISTA DEI MISURATORI REAL PER EDIFICIO
	
	$add_q_dismesso = func_num_args()>1 ? "AND ( meters.D_REMOVE >= '".dtime::my2db( func_get_arg(1) )."' OR meters.D_REMOVE='0000-00-00' )" : '';
	
	$q = "SELECT
	flats.*,
	meters.*,
	metertypes.*
	FROM
	meters
	Inner Join flats_meters ON meters.ID_METER = flats_meters.ID_METER
	Inner Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
	Inner Join metertypes ON meters.ID_METERTYPE = metertypes.ID_METERTYPE
	WHERE flats.ID_BUILDING = '$id' AND
	meters.ID_RF = '1' AND
	meters.IS_DEL <> '1'
	$add_q_dismesso
	GROUP BY meters.ID_METER
	ORDER BY meters.CODE_METER ASC
	";
	$r = rs::inMatrix($q);
	return $r;	
}

function get_real_2_by_id_bld($id, $prima_condivisi, $d_dismissione ){ // LISTA DEI MISURATORI REAL PER EDIFICIO
	
	// personalizzazione query
	$order_condivisi = $prima_condivisi ? " meters.ID_SUPPLYTYPE DESC, " : '';
	$add_q_dismesso = $d_dismissione ? "AND ( meters.D_REMOVE >= '".dtime::my2db( $d_dismissione )."' OR meters.D_REMOVE='0000-00-00' )" : '';
	
	$q = "SELECT
	flats.*,
	meters.*,
	metertypes.*
	FROM
	meters
	Inner Join flats_meters ON meters.ID_METER = flats_meters.ID_METER
	Inner Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
	Inner Join metertypes ON meters.ID_METERTYPE = metertypes.ID_METERTYPE
	WHERE flats.ID_BUILDING = '$id' AND
	meters.ID_RF = '1' AND
	meters.IS_DEL <> '1'
	$add_q_dismesso
	GROUP BY meters.ID_METER
	ORDER BY {$order_condivisi}meters.CODE_METER ASC
	";
	
	return rs::inMatrix($q);
}

function get_real_12_by_id_bld($id, $prima_condivisi=false){ // misuratori real mensili per l'edificio
	//	$add_q_dismesso = func_num_args()>1 ? "AND ( meters.D_REMOVE >= '".dtime::my2db( func_get_arg(1) )."' OR meters.D_REMOVE='0000-00-00' )" : '';
	$order_condivisi = $prima_condivisi ? " meters.ID_SUPPLYTYPE DESC, " : '';
	
	$q = "SELECT
	flats.*,
	meters.*,
	metertypes.*
	FROM
	meters
	Inner Join flats_meters ON meters.ID_METER = flats_meters.ID_METER
	Inner Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
	Inner Join metertypes ON meters.ID_METERTYPE = metertypes.ID_METERTYPE
	WHERE flats.ID_BUILDING = '$id' AND
	meters.IS_12 = '1' AND
	meters.ID_RF = '1' AND
	meters.IS_DEL <> '1'
	GROUP BY meters.ID_METER
	ORDER BY {$order_condivisi}meters.CODE_METER ASC
	";
	
	return rs::inMatrix($q);	
}



function get_usages_by_idflat($id){ # TUTTI GLI UTILIZZI ATTRIBUITI TRAMITE I CONTATORI AD UN EDIFICIO
	$q = "
	SELECT 
	descriptors.ID_DESCRIPTOR AS ID,
	descriptors.DESCRIPTOR_".LANG_DEF." AS ETICHETTA
	FROM descriptors
	LEFT JOIN meters ON descriptors.ID_DESCRIPTOR = meters.K2_ID_USAGE
	LEFT JOIN flats_meters USING(ID_METER)
	WHERE flats_meters.ID_FLAT = '$id'
	AND meters.IS_DEL <> '1'
	GROUP BY descriptors.ID_DESCRIPTOR
	";	
	$r = rs::inMatrix($q);
	return $r;	
}

function get_flats_by_idbuilding($id_building){
	return rs::inMatrix("SELECT * FROM flats WHERE ID_BUILDING = '$id_building' ORDER BY CODE_FLAT ASC");
}

function get_idbuilding_by_idflat($id_flat){
	$q = "SELECT ID_BUILDING FROM flats WHERE ID_FLAT = '$id_flat'";
	$r = rs::rec2arr($q);
	return $r['ID_BUILDING'];
}


function get_avg_npvm2($id_building, $usage, $anno, $uploadtype)	{
	$flats = sole::get_flats_by_idbuilding($id_building);
	$N_flat=0;
	$consumo=0;
	foreach($flats as $flat)	{
		$sql = "SELECT measures.* FROM flats_meters
					LEFT JOIN meters USING(ID_METER)
					LEFT JOIN measures USING(ID_METER)
					WHERE flats_meters.ID_FLAT=" . $flat['ID_FLAT'] .
				" AND meters.K2_ID_USAGE=" . $usage .
				" AND measures.ANNO_MS=" . $anno .
				" AND measures.ID_UPLOADTYPE=" . $uploadtype;
		//echo $sql;
		$meters = rs::inMatrix($sql);
		
		$overallstatus = 'valid';
		$consumptions = 0;
		foreach($meters as $meter)	{

			$dati = misurazioni::get_output($meter['ID_MEASURE'], $flat['ID_FLAT'], 'NPVM2');
			//if($meter['ID_METER']==781)
				//var_dump($dati);
			//error_log($dati);
			$consumptions += $dati['value'];
			$status = $dati['status'];
			
			// Se almeno una delle misurazioni � ND o Wrong allora devo invalidare tutto il valore accumulato
			if($status=='nd')
				$overallstatus = 'nd';
			elseif($status=='wrong')
				if($status!='nd')
					$overallstatus = 'wrong';

		}
		if($overallstatus=='valid')	{
			//var_dump($consumptions);
			$consumo += $consumptions;
			$N_flat++;
		}
	}
		
	if($N_flat > 0)
		$media = $consumo / $N_flat;
	else
		$media = 0;
		
	return $media;

}


function get_metertypes_by_idflats ($flat1, $flat2) {
	$sql = "SELECT metertypes.ID_METERTYPE, metertypes.METERTYPE_".LANG_DEF." FROM meters
					LEFT JOIN flats_meters	USING ( ID_METER ) 
					LEFT JOIN metertypes USING ( ID_METERTYPE ) 
					WHERE (flats_meters.ID_FLAT=$flat1 OR flats_meters.ID_FLAT=$flat2)
					GROUP BY metertypes.ID_METERTYPE
					";
	$r = rs::inMatrix($sql);
	return $r;
	}
	
function get_metertypes_by_idbuilding ($b1, $b2=0) {
	$sql = "SELECT metertypes.ID_METERTYPE, metertypes.METERTYPE_".LANG_DEF." FROM meters
					LEFT JOIN flats_meters	USING ( ID_METER ) 
					LEFT JOIN flats USING (ID_FLAT)
					LEFT JOIN metertypes USING ( ID_METERTYPE ) 
					WHERE (flats.ID_BUILDING='$b1' OR flats.ID_BUILDING='$b2')
					GROUP BY metertypes.ID_METERTYPE
					";
	$r = rs::inMatrix($sql);
	return $r;
	}
	
function get_usages_by_idflats ($flat1, $metertype) {
	$id_building = sole::get_idbuilding_by_idflat($flat1);
	$output = new outputs($id_building);
	
	
	$output->set_schema();
	
	
	
	$usages = $output->schema[$metertype];
	//print_r($usages);
	
	//echo count($usages).BR;
	
	if(count($usages)>0)
		foreach ($usages as $k=>$v)	{
			$ret_usages[$k]['ID_USAGE'] = $v;
			$ret_usages[$k]['description'] = io::get_dp($v);
		}
	

	$sql = "SELECT meters.K2_ID_USAGE AS ID_USAGE, descriptors.DESCRIPTOR_".LANG_DEF." AS description FROM meters
					LEFT JOIN flats_meters	USING ( ID_METER ) 
					LEFT JOIN metertypes USING ( ID_METERTYPE ) 
					LEFT JOIN descriptors ON descriptors.ID_DESCRIPTOR=meters.K2_ID_USAGE 
					WHERE (flats_meters.ID_FLAT=$flat1 OR flats_meters.ID_FLAT=$flat2) AND metertypes.ID_METERTYPE=$metertype AND meters.K2_ID_USAGE IS NOT NULL
					GROUP BY meters.K2_ID_USAGE
					";
	

	return $ret_usages;
}

// Questo metodo restituisce gli usi di energia per i quali sono vengono utilizzati solo contatori diretti
function get_directusages_by_idflats ($flat1, $metertype) {

	$usages = sole::get_usages_by_idflats ($flat1, $metertype);
	
	if(count($usages)>0)
		foreach ($usages as $k=>$v)	{
			// cerco se ci sono contatori condivisi
			$sql = "SELECT meters.ID_METER FROM meters
						LEFT JOIN flats_meters USING(ID_METER)
					WHERE ID_FLAT=$flat1 AND K2_ID_USAGE={$v['ID_USAGE']} AND ID_SUPPLYTYPE=2 AND ID_OUTPUT<>4 ";
			$r = rs::inMatrix($sql);
			if( count($r) == 0 )
				$direct[] = $v;
		
		}
	
	return $direct;
}


function get_direct_hourly_meters($flat)	{
	$sql = "SELECT * FROM meters
						LEFT JOIN flats_meters USING(ID_METER)
						LEFT JOIN metertypes USING(ID_METERTYPE)
					WHERE ID_FLAT=$flat AND ID_SUPPLYTYPE=1 AND HMETER>1";
					
	$r = rs::inMatrix($sql);
	return $r;
	
}



function get_usages_by_idbuilding($id_building, $metertype=false) {
	if($metertype)
		$where_metertype = "AND metertypes.ID_METERTYPE=$metertype";
	else
		$where_metertype = '';
		
	$sql = "SELECT meters.K2_ID_USAGE AS ID_USAGE, descriptors.DESCRIPTOR_".LANG_DEF." AS description, meters.* FROM meters
					LEFT JOIN flats_meters	USING ( ID_METER ) 
					LEFT JOIN flats USING (ID_FLAT)
					LEFT JOIN metertypes USING ( ID_METERTYPE ) 
					LEFT JOIN descriptors ON descriptors.ID_DESCRIPTOR=meters.K2_ID_USAGE 
					WHERE flats.ID_BUILDING=$id_building $where_metertype AND meters.K2_ID_USAGE IS NOT NULL 
					GROUP BY meters.K2_ID_USAGE
					";
	//echo $sql;
	$r = rs::inMatrix($sql);
	
	foreach($r as $k=>$v)	{
		if($v['ID_OUTPUT'] == 2){ # ID_OUTPUT = A/B
			$q_new_type = "SELECT meters.*,
			metertypes.*
			FROM
			meters
			Inner Join flats_meters ON meters.ID_METER = flats_meters.ID_METER
			Inner Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
			Inner Join metertypes ON meters.ID_METERTYPE = metertypes.ID_METERTYPE
			WHERE flats.ID_BUILDING = '".$id_building."' AND
			meters.CODE_METER = '".$v['A']."'
			LIMIT 0,1
			";

			$r_new_type = rs::rec2arr($q_new_type);

			$r[$k]['K2_ID_USAGE'] = $r_new_type['K2_ID_USAGE'];
			$r[$k]['ID_METERTYPE'] = $r_new_type['ID_METERTYPE'];
			$r[$k]['METERTYPE_'.LANG_DEF] = $r_new_type['METERTYPE_'.LANG_DEF];
			$r[$k]['description'] = $r_new_type['DESCRIPTOR_'.LANG_DEF];
			
		}
		else{
			
		}
	}

	$output = new outputs($id_building);
	$output->set_schema();
	//$usages = $output->schema[$metertype];
	//var_dump($output->schema);
	
	$i=0;
	if($metertype)	{
		$schema = $output->schema[$metertype];
		if(count($schema))
			foreach ($schema as $k=>$v)	{
					//echo $k;
					$ret_usages[$i]['metertype'] = $metertype;
					$ret_usages[$i]['METERTYPE_'.LANG_DEF] = $metertype;
					$ret_usages[$i]['ID_USAGE'] = $v;
					$ret_usages[$i]['description'] = io::get_dp($v);
					$i++;
				}
	}
	else
		foreach ($output->schema as $metertype => $metertype_schema)
			foreach ($metertype_schema as $k=>$v)	{
				//echo $k;
				$ret_usages[$i]['metertype'] = $metertype;
				$ret_usages[$i]['METERTYPE_'.LANG_DEF] = $metertype;
				$ret_usages[$i]['ID_USAGE'] = $v;
				$ret_usages[$i]['description'] = io::get_dp($v);
				$i++;
			}
	
	return $ret_usages;
}

# TABELLE VARIE
function table_mk_flatslist($rs, $flds, $dos){ # $dos: DIRECT OR SHARED, 1 => DIRECT, 2 => SHARED
	$ret = ''; $cnt = 0;
	foreach($rs as $rec){ # CREO LA LISTA
		$celle='';
		foreach($flds as $k=>$v){
			$color = $cnt%2 == 0 ? '' : ' class="contrast"';
			if(substr($k,0,2)=='D_') $rec[$k] = dtime::my2iso($rec[$k]); // DATA
			if(substr($k,0,3)=='DT_') $rec[$k] = dtime::my2isodt($rec[$k]); // DATA
			elseif(substr($k,0,3)=='IS_') $rec[$k] = !empty($rec[$k]) ? '<div class="icon_check_att"></div>' : '<div class="icon_check_dis"></div>'; // SI NO
			elseif(substr($k,0,3)=='ID_' || $k == $sk_app->f_path) $rec[$k]; // IMMAGINE
			elseif($k == 'EMAIL'){ $rec[$k]; /* NON FACCIO NIENTE */ }
			else{
				$rec['FULL_'.$k] = $rec[$k];
				$rec[$k] = strcut($rec[$k],'...',30);
			} 
			$celle.='<td>'.$rec[$k].'</td>';
		}
		if(array_key_exists($rec[$sk_app -> f_id], $ext_ids)) {
			$ck_multiplo =  '<input type="checkbox" id="ck'.$rec[$sk_app->f_id].'" id="ck'.$rec[$sk_app->f_id].'" name="ck'.$rec[$sk_app->f_id].'" onclick="update('.$rec[$sk_app->f_id].', '.$id.');" value="1" class="checkbox" checked="checked" />';
		}
		else $ck_multiplo = '<input type="checkbox" id="ck'.$rec[$sk_app->f_id].'" name="ck'.$rec[$sk_app->f_id].'" onclick="update('.$rec[$sk_app->f_id].', '.$id.');"  value="1" class="checkbox"  />';
		
		$href_mod = '';

		if(array_key_exists('title',$aInfo)){
			$info = strlen($rec['FULL_'.$aInfo['description']])>0 ? '<a href="'.$rec['FULL_'.$aInfo['description']].'" onclick="return false;" class="Tips1" title="'.$rec['FULL_'.$aInfo['title']].'"><img src="'.IMG_AR.'icon_info.gif" /></a>' : '';
		} else { $info = ''; }
		$ret.='<tr'.$color.' valign="top">'.$celle.'
		<td class="comandi_lista">'.$info.$href_mod.$ck_multiplo.'
		</td></tr>'."\n";
		$cnt++;
	}
	return $ret;
}

function table_mk_lista($rs, $flds){
	$ret = ''; $cnt = 0;
	foreach($rs as $rec){ # CREO LA LISTA
		$celle='';
		foreach($flds as $k=>$v){
			$color = $cnt%2 == 0 ? '' : ' class="contrast"';
			if(substr($k,0,2)=='D_') $rec[$k] = dtime::my2iso($rec[$k]); // DATA
			if(substr($k,0,3)=='DT_') $rec[$k] = dtime::my2isodt($rec[$k]); // DATA
			elseif(substr($k,0,3)=='IS_') $rec[$k] = !empty($rec[$k]) ? '<div class="icon_check_att"></div>' : '<div class="icon_check_dis"></div>'; // SI NO
			elseif(substr($k,0,3)=='ID_' || $k == $sk_app->f_path) $rec[$k]; // IMMAGINE
			elseif($k == 'EMAIL'){ $rec[$k]; /* NON FACCIO NIENTE */ }
			else{
				$rec['FULL_'.$k] = $rec[$k];
				$rec[$k] = strcut($rec[$k],'...',30);
			} 
			$celle.='<td>'.$rec[$k].'</td>';
		}
		if(array_key_exists($rec[$sk_app -> f_id], $ext_ids)) {
			$ck_multiplo =  '<input type="checkbox" id="ck'.$rec[$sk_app->f_id].'" id="ck'.$rec[$sk_app->f_id].'" name="ck'.$rec[$sk_app->f_id].'" onclick="update('.$rec[$sk_app->f_id].', '.$id.');" value="1" class="checkbox" checked="checked" />';
		}
		else $ck_multiplo = '<input type="checkbox" id="ck'.$rec[$sk_app->f_id].'" name="ck'.$rec[$sk_app->f_id].'" onclick="update('.$rec[$sk_app->f_id].', '.$id.');"  value="1" class="checkbox"  />';
		
		$href_mod = '';

		if(array_key_exists('title',$aInfo)){
			$info = strlen($rec['FULL_'.$aInfo['description']])>0 ? '<a href="'.$rec['FULL_'.$aInfo['description']].'" onclick="return false;" class="Tips1" title="'.$rec['FULL_'.$aInfo['title']].'"><img src="'.IMG_AR.'icon_info.gif" /></a>' : '';
		} else { $info = ''; }
		$ret.='<tr'.$color.' valign="top">'.$celle.'
		<td class="comandi_lista">'.$info.$href_mod.$ck_multiplo.'
		</td></tr>'."\n";
		$cnt++;
	}
	return $ret;
}

function select_fhb($des){ # SELECT MULTI SCELTA FEDERAZIONI, HOUSING COMPANIES, BUILDINGS
	global $user;
	$add_flats = func_num_args()>1 ? func_get_arg(1) : false;
	
	### PREDISPONGO LE QUERIES
	$q_hcompany = "SELECT ID_HCOMPANY, CODE_HC FROM hcompanys ORDER BY CODE_HC ASC";
	$q_building = "SELECT ID_BUILDING, CODE_BLD FROM buildings ORDER BY CODE_BLD ASC";
	$q_flat = "SELECT ID_FLAT, CODE_FLAT FROM flats ORDER BY CODE_FLAT ASC";
	
	if($user -> idg == 2){ # FM
		$q_hcompany = "SELECT ID_HCOMPANY, CODE_HC FROM hcompanys 
		WHERE ID_FEDERATION = '".$user -> aUser['ID_FEDERATION']."'
		ORDER BY CODE_HC ASC";
		
		$q_building = "SELECT ID_BUILDING, CODE_BLD FROM buildings 
		Left Join hcompanys USING(ID_HCOMPANY)
		WHERE hcompanys.ID_FEDERATION = '".$user -> aUser['ID_FEDERATION']."'
		ORDER BY CODE_BLD ASC";
		
		$q_flat = "SELECT ID_FLAT, CODE_FLAT FROM flats 
		Left Join buildings USING(ID_BUILDING)
		Left Join hcompanys USING(ID_HCOMPANY)
		WHERE hcompanys.ID_FEDERATION = '".$user -> aUser['ID_FEDERATION']."'
		ORDER BY CODE_FLAT ASC";
	}	
	elseif($user -> idg == 3){ # MHCU
		$id_user = $user -> aUser['ID_USER'];
		$q_hcompany = "SELECT ID_HCOMPANY, CODE_HC FROM hcompanys 
		WHERE ID_USER = '$id_user'
		ORDER BY CODE_HC ASC";
		
		$q_building = "SELECT ID_BUILDING, CODE_BLD FROM buildings 
		Left Join hcompanys USING(ID_HCOMPANY)
		WHERE hcompanys.ID_USER = '$id_user'
		ORDER BY CODE_BLD ASC";
		
		$q_flat = "SELECT ID_FLAT, CODE_FLAT FROM flats 
		Left Join buildings USING(ID_BUILDING)
		Left Join hcompanys USING(ID_HCOMPANY)
		WHERE hcompanys.ID_USER = '$id_user'
		ORDER BY CODE_FLAT ASC";
	}
	elseif($user -> idg == 4){ # HCU
		$q_building = "SELECT ID_BUILDING, CODE_BLD FROM buildings 
		WHERE ID_HCOMPANY = '".$user -> aUser['ID_HCOMPANY']."'
		ORDER BY CODE_BLD ASC";
		;
		
		$q_flat = "SELECT ID_FLAT, CODE_FLAT FROM flats 
		Left Join buildings USING(ID_BUILDING)
		WHERE buildings.ID_HCOMPANY = '".$user -> aUser['ID_HCOMPANY']."'
		ORDER BY CODE_FLAT ASC";
	} 
	elseif($user -> idg == 5){ # HCU
		$q_flat = "SELECT ID_FLAT, CODE_FLAT FROM flats 
		WHERE flats.ID_USER = '".$user -> aUser['ID_USER']."'
		LIMIT 1";
	} 
	
	### CREO I CONTROLLI
	if($user -> idg == 1){ # SELECT Federations
		# SELECT FEDERATIONS
		$input['federations'.$des] = new io();
		$input['federations'.$des] -> type = 'select'; 
		$input['federations'.$des] -> addblank = true; 
		$input['federations'.$des] -> aval = rs::id2arr("SELECT ID_FEDERATION, FEDERATION FROM federations ORDER BY FEDERATION ASC"); 
		$input['federations'.$des] -> css = 'duecento'; 
		$input['federations'.$des] -> id = 'federations'.$des; 
		$input['federations'.$des] -> txtblank = '- '.CH_FEDERATION; 
		$input['federations'.$des] -> set('federations'.$des);
	}
	
	if($user -> idg < 4){ # SELECT Housing Companies
		$input['hcompanys'.$des] = new io();
		$input['hcompanys'.$des] -> type = 'select'; 
		$input['hcompanys'.$des] -> addblank = true; 
		$input['hcompanys'.$des] -> aval = rs::id2arr($q_hcompany); 
		$input['hcompanys'.$des] -> css = 'duecento'; 
		$input['hcompanys'.$des] -> id = 'hcompanys'.$des; 
		$input['hcompanys'.$des] -> txtblank = '- '.CH_HCOMPANY; 
		$input['hcompanys'.$des] -> set('hcompanys'.$des);
	}
	if($user -> idg < 5){ # SELECT Buildings
		$input['buildings'.$des] = new io();
		$input['buildings'.$des] -> type = 'select'; 
		$input['buildings'.$des] -> addblank = true; 
		$input['buildings'.$des] -> aval = rs::id2arr($q_building); 
		$input['buildings'.$des] -> css = 'duecento'; 
		$input['buildings'.$des] -> id = 'buildings'.$des; 
		$input['buildings'.$des] -> txtblank = '- '.CH_BUILDING; 
		$input['buildings'.$des] -> set('buildings'.$des);
	}
	
	if($add_flats){ # SELECT Flats
		$input['flats'.$des] = new io();
		$input['flats'.$des] -> type = 'select'; 
		
		if($user -> idg != 5)
			$input['flats'.$des] -> addblank = true; 
		
		$input['flats'.$des] -> aval = rs::id2arr($q_flat); 
		$input['flats'.$des] -> css = 'duecento'; 
		$input['flats'.$des] -> id = 'flats'.$des; 
		$input['flats'.$des] -> txtblank = '- '.CH_FLAT; 
		$input['flats'.$des] -> set('flats');
	}
	
	ob_start();
	foreach($input as $k => $v){
		$input[$k] -> get();
	}
	$ret = ob_get_clean();
	return $ret;
}

function mk_namebuilding($id_housingcompany, $text){
	$ret = array('xxx' => '', 'yyy' => '', 'text' => '', 'fullname' => '');
	
	$qhc = "SELECT CODE_HC FROM hcompanys WHERE ID_HCOMPANY = '$id_housingcompany'";
	$rhc = rs::rec2arr($qhc);
	$xxx = $rhc['CODE_HC'];
	
	$qcb = "SELECT COUNT(*) AS N_BUILDS FROM buildings WHERE ID_HCOMPANY = '$id_housingcompany'";
	$rcb = rs::rec2arr($qcb);
	
	$n = $rcb['N_BUILDS'];
	if(empty($n)) $n = 1;
	
	$yyy = stringa::zero_fill($n, 3);
# AGGIUNGO UN SEPARATORE .
	$aBlocks = array('xxx','yyy','text');
	foreach($aBlocks as $k => $v){
		if(!empty($$v)){ 
			$$v .= '.';
		}
	}
	$fullname = $xxx.$yyy.$text;
	if(substr($fullname, -1) == '.'){
		$fullname = stringa::togli_ultimo($fullname);
	}
	
	$ret = array('xxx' => $xxx, 'yyy' => $yyy, 'text' => $text, 'fullname' => $fullname, 'err' => false);
	if(strlen($xxx) != 3 || strlen($yyy) != 3){ # ERRORE SE LE PRIME DUE TERZINE NON SONO GIUSTAMENTE VALORIZZATE
		$ret['err'] = true;
	}
	return $ret;
}

function mk_nameflat($id_building){
	$ret = '000';
	
	$qcb = "SELECT COUNT(*) AS N_FLATS FROM flats WHERE ID_BUILDING = '$id_building'";
	$rcb = rs::rec2arr($qcb);
	
	$n = $rcb['N_FLATS'];
	if(empty($n)) $n = 1;
	
	$yyy = stringa::zero_fill($n, 3);
	$ret = $yyy;
	
	return $ret;
}

function mk_namemeter($id_meter, $text){
	$sep = '_';
	$ret = '';
	$yyy = '';

	
	if(!empty($text)){
		$text = stringa::alphanum_replace_with($text, '_');
		$text = str_replace(array('__', '___', '____', '_____'), '_', $text);
	}
	
	$qmt = "SELECT meters.*,
	metertypes.*
	FROM meters 
	Left Join metertypes ON meters.ID_METERTYPE = metertypes.ID_METERTYPE
	WHERE ID_METER = '$id_meter'";
	$rmt = rs::rec2arr($qmt);
	
	if($rmt['ID_SUPPLYTYPE'] == 1){ # DIRECT
		$qf = "SELECT flats_meters.*,
		flats.*
		FROM flats_meters
		Left Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
		WHERE flats_meters.ID_METER = '$id_meter'
		LIMIT 0,1
		";
		$rf = rs::rec2arr($qf);
		
		$xxx = strtoupper($rmt['STYPE']);		
		$st = 'D';
		$yyy = $rf['CODE_FLAT'];
	}
	elseif($rmt['ID_SUPPLYTYPE'] == 2){
		$st = 'C';
		$xxx = strtoupper($rmt['STYPE']);		
	}
	
	# AGGIUNGO UN SEPARATORE .
	$aBlocks = array('xxx','st','text','yyy');
	foreach($aBlocks as $k => $v){
		if(!empty($$v)){ 
			$$v .= $sep;
		}
	}

	$ret = $xxx.$st.$text.$yyy;
	if(substr($ret, -strlen($sep)) == $sep){
		$ret = stringa::togli_ultimo($ret);
	}
	return $ret;
}

function select_year($id){
	$aOpts = array(); 

	$y = date('Y');
	for($i = $y; $i >= SHOW_FROM_YEAR; $i--){
		$aOpts[$i] =$i;
	}

	$input = new io();
	$input -> type = 'select'; 
	$input -> addblank = true; 
	$input -> aval = $aOpts; //rs::id2arr("SELECT ID_UPLOADTYPE, UPLOADTYPE FROM uploadtypes ORDER BY UPLOADTYPE ASC"); 
	$input -> css = 'duecento'; 
	$input -> id = $id; 
	$input -> txtblank = S_CHOOSE.' '.strtolower(ANNO); 
	return $input -> set($id);
}

function select_months($name){
	//echo LANG_DEF
	if(LANG_DEF=='IT')
		$mesi = array(1=>'Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre','Dicembre');
	else
		$mesi = array(1=>'January', 'Febraury', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November','December');
		
	// $aOpts = array(); 

	$input = new io();
	$input -> type = 'select'; 
	$input -> addblank = true; 
	$input -> aval = $mesi; 
	$input -> id = $name; 
	$input -> txtblank = S_CHOOSE; 
	return $input -> set($name);
}

function select_uploadtype($name){

	$input = new io();
	$input -> type = 'select'; 
	$input -> addblank = true; 
	$input -> aval = array('1' => '1', '2' => '2');
	$input -> id = $name; 
	$input -> txtblank = S_CHOOSE; 
	return $input -> set($name);
}





function is_multi($id_meter){
	$ret = false;
	$qMeter = "SELECT ID_METERTYPE, HMETER FROM meters WHERE ID_METER = '$id_meter'";
	$rMeter = rs::rec2arr($qMeter);

	if($rMeter['ID_METERTYPE'] == 1 && $rMeter['HMETER'] > 1){ # CONTATORE ELETTRICO MULTIORARIO
		$ret = true;
	}
	return $ret;
}

function get_last_measures($id_meter, $n){
	# ULTIMA E PENULTIMA MISURAZIONE
	$q = "SELECT measures.*, 
	meters.ID_METERTYPE,
	meters.HMETER,
	meters.MATRICULA_ID, 
	meters.REGISTERNUM,	
	TO_DAYS(D_MEASURE) AS GIORNI
	FROM measures
	LEFT JOIN meters ON measures.ID_METER = meters.ID_METER
	WHERE measures.ID_METER =  '".$id_meter."'
	AND measures.IS_DEL = '0'
	ORDER BY measures.ANNO_MS DESC,
	measures.ID_UPLOADTYPE DESC
	LIMIT 0 , $n";
	
	$r = rs::inMatrix($q);
	return $r;
}

function get_flats_by_idmeter($idmeter){
	$q = "SELECT flats.*,
	flats_meters.*
	FROM flats_meters
	Left Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
	WHERE flats_meters.ID_METER = '$idmeter';
	";
	return rs::inMatrix($q);
}

function get_unit_by_metertype($metertype)	{
	$q = "SELECT UNIT FROM metertypes WHERE ID_METERTYPE = '$metertype'";
	$r = rs::rec2arr($q);
	return $r['UNIT'];

}

function get_metertype_description($metertype)	{
	$q = "SELECT METERTYPE_".LANG_DEF." FROM metertypes WHERE ID_METERTYPE = '$metertype'";
	$r = rs::rec2arr($q);
	return $r['METERTYPE_'.LANG_DEF];
}

function get_scheda_meter($idmeter){

	$id_building = self::get_id_building_from_id_meter($idmeter);
	
	list($jsusages) = request::get(array('jsusages' => NULL));
	$ret = array();
	$scheda = new nw('meters');
	$scheda -> ext_table(array('meterpropertys', 'outputs', 'supplytypes','rfs', 'metertypes'));
	$scheda -> descriptors('descriptors', array('K2_ID_USAGE'));
	
	$scheda -> many_to_many(array('flats_meters' => array(
									'id' 	=> 'ID_FLAT',
									'title' => 'REGISTERNUM',
									'ext'	=> 'flats',
									'where'	=> "ID_METER",
									'lbl'	=> "Contatori",
									'file' => 'meters_flats_ext.php'
							)
						)
					);
	$scheda -> many_to_many_tot($idmeter);
	
	$my_vars = new ordinamento(array());
	$my_vars->tabella = $scheda->table;	
	
	$fil = " WHERE ".$scheda -> table.".".$scheda -> f_id."='$idmeter' "; 
	
	$qTotRec="SELECT * FROM ".$scheda->table;

	$atabelle = $scheda->atable;
	$lable=rs::sql2lbl($qTotRec);
	$rec=rs::rec2arr($qrs=$qTotRec." ".$fil);
	
	$db=new dbio();
	//$aRet = rs::showfull($atabelle, $rec,$lable,$add=array(),$self_join=array(), $my_vars);

	$aRet = rs::showfullpersonal($atabelle, $rec, $lable, array(), array('CODE_METER'), array(
	'ID_OUTPUT' => "SELECT ID_OUTPUT, OUTPUT from outputs ORDER BY ID_OUTPUT ASC"), $my_vars);	
	
	list($db->a_name, $db->a_val,$db->a_type,$db->a_maxl,$db->a_default,$db->a_not_null,$db->a_lable,$db->a_dec,$db->a_fkey,$db->a_aval,$db->a_addblank,$db->a_comment,$db->a_sql_type, $db->a_js, $db->a_disabled, $aFval) = $aRet;
	
	$db -> a_val = array_merge($db->a_val,$rec);
	$db -> dbset();

	$db -> D_FIRSTVALUE -> id = 'edit_D_FIRSTVALUE';
	$db -> D_FIRSTVALUE -> type = 'text';
	
	$db -> ID_RF -> type = 'lable';
	
	$db -> FORMULA -> id = 'edit_FORMULA';
	$db -> FORMULA -> readonly = 'readonly';
	
	$db -> ID_OUTPUT -> id = 'edit_ID_OUTPUT';
	$db -> ID_OUTPUT -> addblank = 1;
	$db -> ID_OUTPUT -> txtblank = S_CHOOSE.' output';

	$db -> A -> id = 'edit_A';
	$db -> A -> css = 'a_b';
	$db -> A -> readonly = 'readonly';
	$db -> A -> title = '0';
	
	$db -> B -> id = 'edit_B';
	$db -> B -> css = 'a_b';
	$db -> B -> readonly = 'readonly';
	$db -> B -> title = '1';
	
	$db -> IS_12 -> type = 'select';
	$db -> IS_12 -> aval = array('0' => SOLO_SEMESTRALE , '1' => IS_12);
	
	$db -> FORMULA -> id = 'edit_FORMULA';
	
	$db -> ID_METER -> type = 'hidden';
	$db -> ID_METER -> id = 'ID_METER';

	$db -> K2_ID_USAGE -> type = 'radio';

	$db -> D_FIRSTVALUE -> css = 'cento';
	$db -> NAME_METER -> css = 'cento';
	$db -> START_1 -> css = 'cento';
	$db -> START_2 -> css = 'cento';
	$db -> START_3 -> css = 'cento';
	$db -> MATRICULA_ID -> css = 'cento';
	$db -> SCALA_MT -> css = 'cento';
	$db -> FORMULA -> css = 'cento';
	$db -> IS_12 -> css = 'cento';
	$db -> ID_OUTPUT -> css = 'cento';
	$db -> IS_DOUBLE -> lable = false;
	$db -> D_REMOVE -> type = 'text';
	$db -> D_REMOVE -> css = 'datepicker cento';
		
	$db -> ID_METERTYPE -> css = 'dn';

	if(empty($db -> ID_BUILDING -> val) && !empty($id_building)){
		$qUpdBld = "UPDATE meters SET ID_BUILDING='$id_building' WHERE ID_METER='$idmeter'";
		mysql_query( $qUpdBld );
	}

	// lista valori misuratore di produzione
	$qProd = "SELECT * FROM meters_productions WHERE ID_METER = '$idmeter' LIMIT 1";
	$rProd = rs::rec2arr($qProd);
	
	
	$list_prod = '';
	if(!empty($rProd['ID_METER'])){ // è un misuratore di produzione
		
		
		// lista misuratori per l'edificio ($id)
		$aListMeters = sole::get_meters_by_idbuilding($id_building);
		$select_meters = '';
		foreach($aListMeters as $mis){
			$select_meters .= '<option value="'.$mis['ID_METER'].'">'.$mis['CODE_METER'].'</option>'."\n";
		}
		unset( $aListMeters );
		
		// predispongo i vari select
		$select_acs = '<select name="ACS" id="ACS" style="width:104px"><option value="">- '.CHOOSE.'</option>'.$select_meters.'</select>';
		$select_ete = '<select name="ETE" id="ETE" style="width:104px"><option value="">- '.CHOOSE.'</option>'.$select_meters.'</select>';
		//$select_combustibile = '<select name="FUEL" id="FUEL" style="width:104px"><option value="">- '.CHOOSE.'</option>'.$select_meters.'</select>';
		//$select_sum_divisional = '<select name="SUM_DIVISIONAL" id="SUM_DIVISIONAL" style="width:104px"><option value="0">- '.CHOOSE.'</option>'.$select_meters.'</select>';
		unset($select_meters);
		
		// imposto il default dei select

		if(!empty($rProd['ACS'])){
			$select_acs = str_replace('value="'.$rProd['ACS'].'"', 'value="'.$rProd['ACS'].'" selected="selected"', $select_acs);
		}
		if(!empty($rProd['ETE'])){
			$select_ete = str_replace('value="'.$rProd['ETE'].'"', 'value="'.$rProd['ETE'].'" selected="selected"', $select_ete);
		}
// 		if(!empty($rProd['SUM_DIVISIONAL'])){
// 			$select_sum_divisional = str_replace('value="'.$rProd['SUM_DIVISIONAL'].'"', 'value="'.$rProd['SUM_DIVISIONAL'].'" selected="selected"', $select_sum_divisional);
// 		}
// 		if(!empty($rProd['FUEL'])){
// 			$select_combustibile = str_replace('value="'.$rProd['FUEL'].'"', 'value="'.$rProd['FUEL'].'" selected="selected"', $select_combustibile);
// 		}
		
		// energia elettrica
		if($db -> ID_METERTYPE -> val == 1){ 
			$list_prod .= '<li><span><input type="text" name="SIZE" value="'.$rProd['SIZE'].'" class="cento" /></span>'.TAGLIA_IMPIANTO.':</li>'."\n";
		}
		
		// energia termica
		elseif($db -> ID_METERTYPE -> val == 2){ 
			
			if($rProd['THERMAL_TYPE'] == 1){ // solare termico
				$list_prod .= '<li><input type="hidden" name="THERMAL_TYPE" value="1" /><span>'.SOLARE_TERMICO.'</span>'.THERMAL_TYPE.':</li>'."\n";
				$list_prod .= '<li><span><input type="text" name="SIZE_THERMAL" value="'.$rProd['SIZE'].'" class="cento" /></span>'.SIZE_THERMAL.':</li>'."\n";
				$list_prod .= '<li><span>'.$select_acs.'</span>'.ACS.':</li>'."\n";
				$list_prod .= '<li><span>'.$select_ete.'</span>'.ETE.':</li>'."\n";
			}
			elseif($rProd['THERMAL_TYPE'] == 2){ // generatore
				// questi controlli vengono generati pi� in basso nella pagina perch� associati a controlli javascript
			}
		}
		
		elseif($db -> ID_METERTYPE -> val == 5){ // acqua
			// questi controlli vengono generati pi� in basso nella pagina perch� associati a controlli javascript
			// $list_prod .= '<li><span>'.$select_sum_divisional.'</span>'.SUM_DIVISIONAL.':</li>'."\n";
		}		
		
	}
	
	if(!empty($list_prod)){
		$list_prod = '
		<tr><td colspan="2">
		<h2>'.DATI_IMPIANTO.':</h2>
		<ul id="production_meters">
		'.$list_prod.'
		</ul>
		</td></tr>';
	}
	
	// campi produzione modificabili
	$box_production = ''; 
	$metertype = $db -> ID_METERTYPE -> val;
	if($metertype == 2 || $metertype == 5){
		// lista misuratori per l'edificio ($id)
		
		$aListMeters = sole::get_meters_by_idbuilding($id_building);
		$select_FUEL = '';
		$select_SUM_DIVISIONAL = '';
		foreach($aListMeters as $mis){
			
			$selected = $rProd['FUEL'] == $mis['ID_METER'] ? ' selected="selected"' : '';
			$select_FUEL .= '<option value="'.$mis['ID_METER'].'"'.$selected.'>'.$mis['CODE_METER'].'</option>'."\n";
			
			$selected = $rProd['SUM_DIVISIONAL'] == $mis['ID_METER'] ? ' selected="selected"' : '';
			$select_SUM_DIVISIONAL .= '<option value="'.$mis['ID_METER'].'"'.$selected.'>'.$mis['CODE_METER'].'</option>'."\n";
		}
		unset( $aListMeters );
		
		$class = (empty($rProd['ID_METER'])) ? 'dn' : '';
		$checked = (empty($rProd['ID_METER'])) ? '' : ' checked="checked"';

		$select_FUEL = '<select name="FUEL" id="dialog_FUEL" class="'.$class.'" style="width:104px;"><option value="0">- '.CHOOSE.'</option>'.$select_FUEL.'</select>';
		
		$select_SUM_DIVISIONAL = '<select name="SUM_DIVISIONAL" id="dialog_SUM_DIVISIONAL" class="'.$class.'" style="width:104px;" ><option value="0">- '.CHOOSE.'</option>'.$select_SUM_DIVISIONAL.'</select>';
		
		if($db -> ID_METERTYPE -> val == 2 && $rProd['THERMAL_TYPE'] != 1){ // energia termica
			$sel_gen = $rProd['THERMAL_TYPE'] == 2 ? ' selected="selected"' : '';

			$box_production = '
			<tr>
			<td>'.THERMAL_TYPE.'</td>
			<td><select style="width:104px;" name="THERMAL_TYPE" id="dialog_choose_thermal">
			<option value="" selected="selected"></option>
			<option value="2" '.$sel_gen.'>'.GENERATORE.'</option>
			</select></td></tr>
			<tr><td></td><td>
			'.$select_FUEL.'</td></tr>';
		}	
		elseif($db -> ID_METERTYPE -> val == 5){ // acqua
			$box_production = '<tr><td>'.ALTRE_UTENZE.'</td><td>
			<input type="checkbox" name="altre_utenze" id="dialog_altre_utenze" '.$checked.'>
			</td></tr>
			<tr><td></td><td>
			'.$select_SUM_DIVISIONAL.'
			</td></tr>';
		}
		unset($select_SUM_DIVISIONAL, $select_FUEL);
	}
	
	# CONTROLLI PER MODIFICA / ELIMINAZIONE
	//echo $qFormula = "SELECT ID_METER FROM meters WHERE FORMULA LIKE '%".$db -> CODE_METER -> val."%' OR A LIKE '%".$db -> CODE_METER -> val."%' OR B LIKE '%".$db -> CODE_METER -> val."%'";
	
	// $id_building = self::get_id_building_from_id_meter($idmeter);

	$qFormula = "SELECT ID_METER FROM meters 
	Left Join flats_meters USING(ID_METER)
	Left Join flats USING (ID_FLAT)
	WHERE 
	flats.ID_BUILDING = '".$id_building."' AND 
	(FORMULA LIKE '%".$db -> CODE_METER -> val."%' OR A LIKE '%".$db -> CODE_METER -> val."%' OR B LIKE '%".$db -> CODE_METER -> val."%')";
	
	$rFormula = rs::inMatrix($qFormula);
	$is_formula = false;
	foreach($rFormula as $k => $v){
		if(!empty($v['ID_METER'])){
			$is_formula = true;
			break;
		}
	}
	/*
	Ambito edificio!!!
	
	SELECT ID_METER FROM meters 
	Left Join flats_meters USING(ID_METER)
	Left Join flats USING (ID_FLAT)
	WHERE FORMULA LIKE '%ACQ_D_f_001%' OR A LIKE '%ACQ_D_f_001%' OR B LIKE '%ACQ_D_f_001%'
	WHERE flats.ID_BUILDING = '2'
	
	*/
	$qMeasures = "SELECT ID_MEASURE FROM measures WHERE ID_METER = '".$db -> ID_METER -> val."' LIMIT 0,1";
	$rMeasures = rs::rec2arr($qMeasures);
	$is_measures = false;
	if(!empty($rMeasures['ID_MEASURE'])){
		$is_measures = true;
	}
	
	
	if($is_formula){ # CREO UN HIDDEN IN CASO DI CAMPO NON MODIFICABILE. SERVE PER LA CREAZIONE DEL NOME CONTATORE
		$db -> NAME_METER -> type = 'hidden';
		$NAME_METER = $db -> NAME_METER -> val.$db -> NAME_METER -> set();
	} else {
		$NAME_METER = $db -> NAME_METER -> set();
	}
	
	if($db -> ID_RF -> val == 2){ // permetto la modifica solo se real
		$tr_IS_12 = '';
	} else {
		$tr_IS_12 = '<tr><td width="160">'.IS_12.': </td><td>'.$db -> IS_12 -> set().'</td></tr>';
	}
	
	$hid_dele = (!$is_formula && !$is_measures) ? 1 : 0;
	$hid_dele = '<input id="act_delete" type="hidden" value="'.$hid_dele.'" />';
	
	if($db -> HMETER -> val > 1 ){
		$tr['START'] = '
		<tr><td>'.START_1.': </td><td>'.$db -> START_1 -> set().'</td></tr>
		<tr><td>'.START_2.': </td><td>'.$db -> START_2 -> set().'</td></tr>
		<tr><td>'.START_3.': </td><td>'.$db -> START_3 -> set().'</td></tr>';
	} else {
	$tr['START'] = '
		<tr><td>'.START_1.': </td><td>'.$db -> START_1 -> set().'</td></tr>';
	}
	
	if($db -> ID_RF -> val == 2){ # FORMULA
		$tr['FORMULA'] = '<tr><td>Formula: </td><td>'.$db -> FORMULA -> set().'</td></tr>';
	} else { # REAL
		$tr['FORMULA'] = '<tr><td>Formula: </td><td>Real</td></tr>';
	}
	
	$tr['IS_DOUBLE'] = '';
	if($db -> ID_METERTYPE -> val == 1){
		$tr['IS_DOUBLE'] = '<tr><td>'.IS_DOUBLE.': </td><td>'.$db -> IS_DOUBLE -> set().'</td></tr>';
	}
	
	if($db -> ID_METERTYPE -> val == 1){
		$tr['IS_DOUBLE'] = '<tr><td>'.IS_DOUBLE.': </td><td>'.$db -> IS_DOUBLE -> set().'</td></tr>';
	}
	
	$qM = "SELECT
		metertypes.METERTYPE_".LANG_DEF." AS METERTYPE,
		metertypes.UNIT,
		meterpropertys.METERPROPERTY,
		supplytypes.SUPPLYTYPE
		FROM
		meters
		LEFT JOIN metertypes USING(ID_METERTYPE)
		LEFT JOIN meterpropertys USING(ID_METERPROPERTY)
		LEFT JOIN supplytypes ON meters.ID_SUPPLYTYPE = supplytypes.ID_SUPPLYTYPE
		WHERE meters.ID_METER = '$idmeter'
	";
	$rM = rs::rec2arr($qM);

	$ret['main'] = $db -> ID_METER -> set().'
	<table class="neutra riepilogo">
	<tr><td colspan="2">
	<ul class="simple">
	<li>'.$rM['METERTYPE'].'</li>
	<li>'.$rM['UNIT'].'</li>
	<li>'.$rM['METERPROPERTY'].'</li>
	<li>'.$rM['SUPPLYTYPE'].'</li>
	<ul>
	</td></tr>
	'.$db -> ID_METERTYPE -> set().'
	'.$list_prod.'
	
	'.$box_production.'aaa
	'.$tr_IS_12.'
	<tr><td width="160">'.NAME_METER.': </td><td>'.$NAME_METER.'</td></tr>
	<tr><td>'.MATRICULA_ID.':</td><td>'.$db -> MATRICULA_ID -> set().'</td></tr>
	<tr><td>'.SCALA_MT.':</td><td>'.$db -> SCALA_MT -> set().'</td></tr>
	'.$tr['IS_DOUBLE'].'
	'.$tr['FORMULA'].'
	<tr><td>'.ID_OUTPUT.': </td><td>'.$db -> ID_OUTPUT -> set().'</td></tr>
	<tr id="tr_A"><td>A: </td><td>'.$db -> A -> set().'</td></tr>
	<tr id="tr_B"><td>B: </td><td>'.$db -> B -> set().'</td></tr>
	</table>
	<br />
	<h2>'.INIT_VALUES.'</h2>
	<table class="neutra riepilogo">
	<tr><td width="160">'.D_FIRSTVALUE.':</td><td>'.$db -> D_FIRSTVALUE -> set().'</td></tr>
	'.$tr['START'].'
	<tr><td>'.D_REMOVE.': </td><td>'.$db -> D_REMOVE -> set().'</td></tr>
	</table>'.	
	$hid_dele;

	
	$ret['valori_iniziali'] = '';
	
/* 	<table class="neutra riepilogo">
	<tr><td>'.D_FIRSTVALUE.':</td><td>'.$db -> D_FIRSTVALUE -> set().'</td></tr>
	'.$tr['START'].'
	</table>'.
	$hid_dele; */
	
	$ret['usages'] = '
<div><h2>'.USAGE.'</h2></div>
<div class="mmBox">'.$db -> K2_ID_USAGE -> set().'</div>';
	
	return $ret;
}

function get_multicheck_flats_list_by_id_building($id_building, $mode, $id_meter){
	$ret = array('list' => '');
	$r = sole::get_flats_by_idbuilding($id_building);
	$rmm = array();
	
	if($id_meter){
		$qmm = "SELECT * FROM flats_meters WHERE ID_METER = '$id_meter'";
		$rmm = rs::inMatrix($qmm);
		$rmm = arr::semplifica($rmm, 'ID_FLAT');
	}
	$list = '';
	foreach($r as $flat){
		$checked = '';
		if(array_key_exists($flat['ID_FLAT'], $rmm)){
			$checked = ' checked="checked"';
		}
		if($mode == 1){ # DIRECT (RADIO BUTTON)
			$list .= '<div class="cb">
			<input type="radio" name="flat" value="'.$flat['ID_FLAT'].'"'.$checked.' /><div class="lable">'.$flat['CODE_FLAT'].'</div>
			</div>';
		}
		elseif($mode == 2){ # SHARED (CHECKBOX)
			$list .= '<div class="cb">
			<input type="hidden" name="ba'.$flat['ID_FLAT'].'" value="1" />
			<input type="checkbox" name="bb'.$flat['ID_FLAT'].'" value="1" '.$checked.' />
			<div class="lable">'.$flat['CODE_FLAT'].'</div>
			</div>';
		}
	}
	$ret = mytag::in($list, 'div', array('class' => 'mmBox'));
	$ret = '<div>'.CHOOSE.' '.strtolower(FLATS).'</div>'.$ret;

	if(empty($list)) $ret = '';
	return $ret;
}

function get_multicheck_usages_list_by_id_meter($id_meter){
	$scheda = new nw('meters');
	$scheda -> ext_table(array('supplytypes'));
	$scheda -> add_mm('usages', $id_meter);
	return '<div>'.CHOOSE.' '.strtolower(USAGE).'</div>'.$scheda -> mmBox['usages'];
}

function get_flat_info($id_flat){
	$ret = array();
	
	$q = "
	SELECT 
	flats.*,
	buildings.*,
	hcompanys.*
	FROM flats
	Left Join buildings ON flats.ID_BUILDING = buildings.ID_BUILDING
	Left Join hcompanys ON buildings.ID_HCOMPANY = hcompanys.ID_HCOMPANY
	WHERE 
	flats.ID_FLAT = '$id_flat'
	LIMIT 0,1
	";
	
	$ret = rs::rec2arr($q);
	return $ret;
}

function get_id_building_from_id_meter($id_meter){
	$q = "SELECT buildings.ID_BUILDING FROM
	buildings
	Left Join flats USING(ID_BUILDING)
	Left Join flats_meters USING(ID_FLAT)
	Left Join meters USING(ID_METER)
	WHERE
	meters.ID_METER = '$id_meter'
	GROUP BY meters.ID_METER
	LIMIT 1
	";
	$r = rs::rec2arr($q);
	return $r['ID_BUILDING'];
}

function get_building_info($id_building){
	$ret = array();
	
	$q = "
	SELECT 
	buildings.*,
	hcompanys.*
	FROM buildings
	Left Join hcompanys ON buildings.ID_HCOMPANY = hcompanys.ID_HCOMPANY
	WHERE 
	buildings.ID_BUILDING = '$id_building'
	LIMIT 0,1
	";
	
	$ret = rs::rec2arr($q);
	return $ret;
}

function get_meter_output($meter_code, $year, $period) {
	$sql = "SELECT meters.*, msoutputs 
			FROM meters
			LEFT JOIN measures USING(ID_METER)
			LEFT JOIN msoutput USING(ID_MEASURE)
			WHERE meters.CODE_METER='$meter_code' AND measures.ANNO_MS=$year AND measures.ID_UPLOADTYPE=$period";
}

function delete_meter($id){
	$ret = false;
	
	$q = "DELETE FROM meters WHERE ID_METER = $id";
	if(mysql_query($q)){
		$ret = true;
	}
	return $ret;
}

function get_metertype_by_codename($code_name, $id_flat){
	$q = "SELECT meters.ID_METERTYPE
	FROM
	meters
	Inner Join flats_meters ON meters.ID_METER = flats_meters.ID_METER
	Inner Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
	WHERE flats.ID_FLAT = '$id_flat' AND
	meters.CODE_METER = '$code_name'
	LIMIT 1
	";
	
	$r = rs::rec2arr($q);
	return $r['ID_METERTYPE'];
}

function get_idmeter_by_codename($code_name, $id_building)	{
// 	$q = "SELECT meters.ID_METER
// 	FROM
// 	meters
// 	WHERE meters.ID_BUILDING = '$id_building' AND
// 	meters.CODE_METER = '$code_name'
// 	LIMIT 1
// 	";
	
	$q = "SELECT meters.ID_METER
			FROM	meters
			Inner Join flats_meters ON meters.ID_METER = flats_meters.ID_METER
			Inner Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
			WHERE flats.ID_BUILDING = '$id_building' AND	meters.IS_DEL <> '1' AND meters.CODE_METER = '$code_name'
			GROUP BY meters.ID_METER
	";

	$r = rs::rec2arr($q);
	return $r['ID_METER'];
}

function get_metertype_by_idmeter($id_meter){
	$q = "SELECT meters.ID_METERTYPE	FROM meters
	WHERE meters.ID_METER = '$id_meter'
	LIMIT 1
	";
	
	$r = rs::rec2arr($q);
	return $r['ID_METERTYPE'];
}


function get_conversions_from_id_flat($id_flat){
	$ret = array('1' => 1, '2' => 1, '3' => 1, '4' => '1', '5' => 1); 
  	$q = "
	SELECT 
	federations_conversions.ID_METERTYPE,
	federations_conversions.CONVERSION
	FROM flats
	Left Join buildings ON flats.ID_BUILDING = buildings.ID_BUILDING
	Left Join hcompanys ON buildings.ID_HCOMPANY = hcompanys.ID_HCOMPANY
	Right Join federations_conversions ON hcompanys.ID_FEDERATION = federations_conversions.ID_FEDERATION
	WHERE 
	flats.ID_FLAT = '$id_flat'
	";	
	
	$r = rs::inMatrix($q);
	foreach($r as $k => $v){
		$ret[$v['ID_METERTYPE']] = $v['CONVERSION'];
	}
	return $ret;
}

function is_occupied($id_flat, $id_uploadtype, $anno){
	$q = "SELECT * FROM occupancys WHERE ID_FLAT = '$id_flat' AND ID_UPLOADTYPE = '$id_uploadtype' AND ANNO_MS = '$anno' LIMIT 1";
	$r = rs::rec2arr($q);
	if($r['IS_OCCUPIED'] == 1) $ret = true;
	else $ret = false;
	
	return $ret;
}


function get_flat_by_usercode($code)	{
	$q = "SELECT ID_FLAT FROM flats LEFT JOIN users USING (ID_USER) WHERE CODE='$code'";
	$r = rs::rec2arr($q);
	
	return $r['ID_FLAT'];
}

function get_coords_buildings(){
		$q = "SELECT 
		regioni.DESCRIPTOR_".LANG_DEF." AS REGIONE,
		province.DESCRIPTOR_".LANG_DEF." AS PROVINCIA,
		comuni.DESCRIPTOR_".LANG_DEF." AS COMUNE,
		buildings.ID_BUILDING,
		buildings.CODE_BLD,
		buildings.NAME_BLD,
		buildings.YEAR_BLD,
		buildings.LNG_BLD,
		buildings.LAT_BLD,
		buildings.ADDRESS_BLD,
		hcompanys.NAME_HC
		FROM
		buildings
		LEFT JOIN descriptors AS regioni ON buildings.K1_ID_REGIONI = regioni.ID_DESCRIPTOR
		LEFT JOIN descriptors AS province ON buildings.K1_ID_PROVINCE = province.ID_DESCRIPTOR
		LEFT JOIN descriptors AS comuni ON buildings.K1_ID_COMUNI = comuni.ID_DESCRIPTOR
		LEFT JOIN hcompanys USING(ID_HCOMPANY)
		WHERE
		buildings.IS_HIDE <> '1' AND
		buildings.LNG_BLD IS NOT NULL AND
		buildings.LAT_BLD IS NOT NULL
		";
		
		$r = rs::inMatrix($q);
		
		$coords = ''; $cnt = 1;
		foreach($r as $k => $v){
	
				$link = '<a href="info-building.php?id='.$v['ID_BUILDING'].'">'.VIEW.'</a>';
			
				$coords .= "['".addslashes($v['NAME_HC'])." - ".$v['NAME_BLD']." <br /> ".$link."', ".$v['LAT_BLD'].", ".$v['LNG_BLD'].", ".$cnt."],";
				$cnt ++;
			//}
		}
		$coords = stringa::togli_ultimo($coords);
		
		return '
		<script type="text/javascript">
		var locations = [
		'.$coords.'
		];

		var map = new google.maps.Map(document.getElementById(\'map_canvas\'), {
		zoom: 5,
		center: new google.maps.LatLng(45.65262, 13.770307199999946),
		mapTypeId: google.maps.MapTypeId.ROADMAP
		});

		var infowindow = new google.maps.InfoWindow();

		var marker, i;

		for (i = 0; i < locations.length; i++) {  
		marker = new google.maps.Marker({
		  position: new google.maps.LatLng(locations[i][1], locations[i][2]),
		  map: map
		});

		google.maps.event.addListener(marker, \'click\', (function(marker, i) {
		  return function() {
			 infowindow.setContent(locations[i][0]);
			 infowindow.open(map, marker);
		  }
		})(marker, i));
		}
		</script>
		';
}

// controlla se tutte le misurazioni di un invio sono state convalidate
function check_if_all_validated($aMeters, $upload_type, $year){
	$all_validated = true;
	foreach($aMeters as $id_meter => $meter_info){
 		$qChk = "SELECT measures.ID_MEASURE AS ms 
		FROM 
		msoutputs
		Left Join measures USING(ID_MEASURE)
		WHERE 
		measures.ID_METER = '$id_meter' AND
		measures.ANNO_MS = '$year' AND
		measures.ID_UPLOADTYPE = '$upload_type'
		LIMIT 1";
		
		$rChk = rs::rec2arr($qChk);
		
		if(empty($rChk['ms'])){
			$all_validated = false;
		}
		unset($rChk);
	}
	return $all_validated;
}


function deduci_fondoscala($number){
	$number = ltrim($number, 0);
	
	if($number < 1){
		return ERROR_DEDUCI_FONDOSCALA;
	}
 	elseif( strpos($number, '.') !== false){
		$lunghezza = strpos($number, '.');
	} 
	elseif( strpos($number, ',') !== false){
		$lunghezza = strpos($number, ',');
	} else {
		$lunghezza = strlen($number);
	}
	
	if($lunghezza == 0){
		$lunghezza = 1;
	}
	
	$return = ''; 
 	for($i = 1; $i<=$lunghezza; $i++){
		$return .= '9';
	} 
	return $return.'.999';
}

function get_federation_by_id_building($id_building){
	$qFederation = "SELECT ID_FEDERATION FROM hcompanys
	LEFT JOIN buildings USING (ID_HCOMPANY)
	WHERE buildings.ID_BUILDING='$id_building' LIMIT 1";
	$rFederation = rs::rec2arr($qFederation);
	return $rFederation['ID_FEDERATION'];
}

}
?>