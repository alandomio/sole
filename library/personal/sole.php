<?php
class sole{

static function get_users_from_hc($id_hc, $id_group){ # RESTITUISCE GLI UTENTI COLLEGATI AD UN APPARTAMENTO DELLA CORRISPONDENTE HC
	$AND = '';
	if(!empty($id_group)){
		$AND = " AND users.ID_GRUPPI = '$id_group' ";
	}
	$q = "SELECT * FROM users
	WHERE users.ID_HCOMPANY = '$id_hc'
	$AND";
	return rs::inMatrix($q);
}

static function get_buildings_from_hc($id_hc){
	$q = "SELECT * FROM buildings WHERE ID_HCOMPANY = '$id_hc' ORDER BY CODE_BLD ASC";
	return rs::inMatrix($q);
}

static function get_building_coords_info($id=0, $mode='building', $powerhouse=0){

	$lj='';
	if($mode=='building'){
		$where = "ID_BUILDING={$id}";
	}
	elseif($mode=='hcompany'){
		$where = "ID_HCOMPANY={$id}";
	}
	elseif($mode=='federation'){
		$lj="LEFT JOIN hcompanys USING(ID_HCOMPANY)";
		$where="hcompanys.ID_FEDERATION={$id}";
	}
	
	$extra_where='';
	if( ! empty($powerhouse)){
		$extra_where = "buildings.IS_POWERHOUSE=1 AND ";
	}

	$q="SELECT
	ID_BUILDING AS id,
	NAME_BLD AS name,
	LAT_BLD AS lat,
	LNG_BLD AS lng FROM buildings
	{$lj}
	WHERE
	{$extra_where}
	{$where} AND
	LAT_BLD IS NOT NULL
	";
	
	return rs::inMatrix($q);
}


static function get_flats_num($id_building)	{
	$sql = "SELECT COUNT(ID_FLAT) AS flats FROM flats WHERE ID_BUILDING=$id_building GROUP BY ID_BUILDING";
	$dati = rs::rec2arr($sql);
	return $dati['flats'];
}

static function building_user($id){ # RESTITUISCE TUTTI I RECORD DEGLI EDIFICI LEGATI ALL'ID UTENTE INDICATO
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
	elseif($rU['ID_GRUPPI'] == '2'){ # GM > federazione > housing companies > buildings
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
	elseif($rU['ID_GRUPPI'] == '3'){ # MHMU
		$qB = "SELECT buildings.* FROM buildings
		Inner Join hcompanys ON buildings.ID_HCOMPANY = hcompanys.ID_HCOMPANY
		WHERE hcompanys.ID_USER = '$id'";
	}
	if($rU['ID_GRUPPI'] == '4' || $rU['ID_GRUPPI'] == '5'){ # HMU HHU tabella molti a molti buildings_users
		$qB = "SELECT buildings.*
		FROM buildings_users
		Inner Join buildings ON buildings_users.ID_BUILDING = buildings.ID_BUILDING
		WHERE buildings_users.ID_USER = '$id'";
	}
	return rs::inMatrix($qB);
}

static function get_meters_sinottica_by_idbuilding($id_building, $mode='full'){
	/*
	 * carica le etichette per gli output 
	 * */
	$q="SELECT * FROM outputs";
	$rows=rs::inMatrix($q);

	$outputs=array();
	foreach($rows as $row){
		$outputs[$row['ID_OUTPUT']]=$row['OUTPUT'];
	}
	
	if($mode=='full'){
	$q = "SELECT
	meters.ID_METER,
	meters.CODE_METER,
	meters.MATRICULA_ID,
	meters.REGISTERNUM,
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
	LEFT JOIN meterpropertys USING(ID_METERPROPERTY)
	LEFT JOIN supplytypes USING(ID_SUPPLYTYPE)
	LEFT JOIN rfs USING(ID_RF)
	LEFT JOIN outputs USING(ID_OUTPUT)
	WHERE flats.ID_BUILDING = '$id_building' AND
	meters.IS_DEL <> '1'
	GROUP BY meters.ID_METER
	ORDER BY meters.CODE_METER ASC
	";
	} else {
		$q = "SELECT
		meters.ID_METER,
		meters.CODE_METER,
		meters.MATRICULA_ID,
		meterpropertys.METERPROPERTY,
		supplytypes.SUPPLYTYPE,
		rfs.RF,
		meters.FORMULA,
		outputs.OUTPUT,
		meters.A,
		meters.B
		FROM
		meters
		Inner Join flats_meters ON meters.ID_METER = flats_meters.ID_METER
		Inner Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
		Inner Join metertypes ON meters.ID_METERTYPE = metertypes.ID_METERTYPE
		LEFT JOIN meterpropertys USING(ID_METERPROPERTY)
		LEFT JOIN supplytypes USING(ID_SUPPLYTYPE)
		LEFT JOIN rfs USING(ID_RF)
		LEFT JOIN outputs USING(ID_OUTPUT)
		WHERE flats.ID_BUILDING='$id_building' AND
		meters.IS_DEL <> '1'
		GROUP BY meters.ID_METER
		ORDER BY meters.CODE_METER ASC
		";	
	}
	$r = rs::inMatrix($q);
	
	$ret=array();
	foreach($r as $k => $v){
		/*
		 * crea la lista di appartamenti
		 * */
		$q = "	SELECT 
				flats.CODE_FLAT
				FROM flats_meters
				Left Join flats USING(ID_FLAT)
				WHERE flats_meters.ID_METER={$v['ID_METER']}";
		
		$rows=rs::inMatrix($q);

		$r[$k]['FLATS'] = '';
		foreach($rows as $row){
			$r[$k]['FLATS'] .= $row['CODE_FLAT'].', '; //.'('.$row['NETAREA'].'mq) ';
		}
		$r[$k]['FLATS'] = substr($r[$k]['FLATS'], 0, -2);
		
		/*
		 * crea la lista di utilizzi
		 * */
		$q="SELECT 
			usages.USAGE_".LANG_DEF." AS value FROM
			meters_usages
			LEFT JOIN usages USING(ID_USAGE)
			WHERE 
			meters_usages.ID_METER={$v['ID_METER']}";
		
		$rows=rs::inMatrix($q);
		
		$r[$k]['USAGES']='';
		foreach($rows as $row){
			$r[$k]['USAGES'] .= $row['value'].', ';
		}
		if( ! empty($r[$k]['USAGES'])){
			$r[$k]['USAGES'] = substr($r[$k]['USAGES'], 0, -2);
		}
		
		/*
		 * utilizzi nze
		 * */
		$q="SELECT ID_NZE, ID_OUTPUT, A_NZE, B_NZE FROM nzes WHERE ID_METER={$v['ID_METER']} LIMIT 1";
		$row=rs::rec2arr($q);
		
		if( ! empty($row['ID_NZE'])){
			$r[$k]['ID_NZE']='<div class="flexi-chk"><input type="checkbox" disabled="true" style="margin:0 auto;" checked="checked"></div>';
			$r[$k]['ID_OUTPUT']=$outputs[$row['ID_OUTPUT']];
			$r[$k]['A_NZE']=$row['A_NZE'];
			$r[$k]['B_NZE']=$row['B_NZE'];
		} else {
			$r[$k]['ID_NZE']='<div class="flexi-chk"><input type="checkbox" disabled="true" style="margin:0 auto;"></div>';
			$r[$k]['ID_OUTPUT']='';
			$r[$k]['A_NZE']='';
			$r[$k]['B_NZE']='';
		}

		unset($r[$k]['ID_METER']);
		
		/*
		 * accoda il nuovo record con le colonne ordinate
		 * */
		$ret[$k]=array_merge(array('PROGRESSIVO'=>$k+1), $r[$k]);
		unset($r[$k]);
	}
	return $ret;
}

/*
 * restituisce tutti i misuratori di un'edificio, anche quelli non più usati
 * */
static function get_meters_by_idbuilding($id, $prima_condivisi=false){
	
	$order = $prima_condivisi ? " meters.ID_SUPPLYTYPE DESC, " : '';
	
	$q = "SELECT
	flats.*,
	meters.*,
	metertypes.*
	FROM
	meters
	Inner Join flats_meters ON meters.ID_METER = flats_meters.ID_METER
	Inner Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
	Inner Join metertypes ON meters.ID_METERTYPE = metertypes.ID_METERTYPE
	WHERE flats.ID_BUILDING = {$id} AND
	meters.IS_DEL <> '1'
	GROUP BY meters.ID_METER
	ORDER BY {$order} meters.CODE_METER ASC
	";
	$r = rs::inMatrix($q);
	return $r;	
}

static function get_meters_production_by_idbuilding($id, $metertype=false){
	$wheremetertype = $metertype ? "meters.ID_METERTYPE=" . $metertype . " AND" : '';
	
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
	$wheremetertype
	meters.IS_DEL <> '1'
	GROUP BY meters.ID_METER
	ORDER BY meters.CODE_METER ASC
	";
	$r = rs::inMatrix($q);
	return $r;	
}

static function get_meters_formula_by_idbuilding($id){
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

/*
 * lista dei misuratori di un appartamento
 * */
static function get_meters_by_idflat($id){
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

static function get_meters_all_by_idflat($id){
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

static function get_real_by_bld($id){ // LISTA DEI MISURATORI REAL PER EDIFICIO
	
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

static function get_real_2_by_id_bld($id, $prima_condivisi, $data_riferimento=false ){ // LISTA DEI MISURATORI REAL PER EDIFICIO
	
	/*
	 * personalizzazione query
	 * */
	$order_condivisi = $prima_condivisi ? " meters.ID_SUPPLYTYPE DESC, " : '';
	
	/*
	 * contatore dismesso o non ancora attivato
	 * */
	$add_q_dismesso = '';

	if($data_riferimento){
		$d_dismiss=dtime::my2db($data_riferimento);
		$add_q_dismesso = "	AND (meters.D_REMOVE >= '".$d_dismiss."' OR meters.D_REMOVE='0000-00-00')
							AND (meters.D_FIRSTVALUE <= '".$d_dismiss."' OR meters.D_FIRSTVALUE='0000-00-00')";
	}
	
	/*
	 * contatore inserito ma non ancora attivato
	 * */
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

static function get_real_12_by_id_bld($id, $prima_condivisi=false){ // misuratori real mensili per l'edificio
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



static function get_usages_by_idflat($id){ # TUTTI GLI UTILIZZI ATTRIBUITI TRAMITE I CONTATORI AD UN EDIFICIO
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

static function get_flats_by_idbuilding($id_building){
	return rs::inMatrix("SELECT * FROM flats WHERE ID_BUILDING={$id_building} ORDER BY CODE_FLAT ASC");
}

static function get_idbuilding_by_idflat($id_flat){
	$q = "SELECT ID_BUILDING FROM flats WHERE ID_FLAT = '$id_flat'";
	$r = rs::rec2arr($q);
	return $r['ID_BUILDING'];
}


static function get_avg_npvm2($id_building, $usage, $anno, $uploadtype)	{
	
	//echo $anno . ' ' . $uploadtype.BR.BR;
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
		
		$sql = "SELECT DISTINCT measures.ID_MEASURE, meters.ID_METERTYPE FROM flats_meters
						LEFT JOIN meters USING(ID_METER)
						LEFT JOIN measures USING(ID_METER)
						LEFT JOIN (SELECT DISTINCT ID_METER,
								GROUP_CONCAT(DISTINCT meters_usages.ID_USAGE ORDER BY meters_usages.ID_USAGE ASC SEPARATOR ',') AS USAGES
							FROM meters
								LEFT JOIN meters_usages USING (ID_METER)
								LEFT JOIN nzes USING (ID_METER)
								LEFT JOIN flats_meters	USING ( ID_METER )
								WHERE flats_meters.ID_FLAT=" . $flat['ID_FLAT'] .
											" GROUP BY ID_METER) AS aggr_usages USING(ID_METER)
											WHERE aggr_usages.USAGES='$usage' " .
			
											" AND measures.ANNO_MS=" . $anno .
											" AND measures.ID_UPLOADTYPE=" . $uploadtype;
		//echo $sql;
		$meters = rs::inMatrix($sql);
		
		$overallstatus = 'valid';
		$consumptions = 0;
		foreach($meters as $meter)	{

			$dati = misurazioni::get_output($meter['ID_MEASURE'], $flat['ID_FLAT'], 'NPVM2');
			//if($meter['ID_METER']==781)
				//var_dump($meter['ID_MEASURE']);
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
			$area = $dat['area'];

		}
		
		//var_dump($flat);
		if($overallstatus=='valid' && $consumptions > 0)	{
			//var_dump($consumptions);
// 			var_dump($overallstatus);
			$consumo += $consumptions  * $flat['NETAREA'];
			$tot_area += $flat['NETAREA'];
			//$N_flat++;
		}
	}
		
	if($tot_area > 0)
		$media = $consumo / $tot_area;
	else
		$media = 0;
	//echo $media.'<br>';	
	return $media;

}


static function get_metertypes_by_idflats ($flat1, $flat2) {
	$sql = "SELECT metertypes.ID_METERTYPE, metertypes.METERTYPE_".LANG_DEF." FROM meters
					LEFT JOIN flats_meters	USING ( ID_METER ) 
					LEFT JOIN metertypes USING ( ID_METERTYPE ) 
					WHERE (flats_meters.ID_FLAT=$flat1 OR flats_meters.ID_FLAT=$flat2)
					GROUP BY metertypes.ID_METERTYPE
					";
	$r = rs::inMatrix($sql);
	return $r;
	}
	
static function get_metertypes_by_idbuilding ($b1, $b2=0, $output_meter=false) {
	
	if($output_meter)	{
		$output = " AND ID_OUTPUT < 4";
		$aoutput = " AND ameter.ID_OUTPUT < 4";
	}
		

	
	$sql = "SELECT DISTINCT
					COALESCE(ameter.ID_METERTYPE, meters.ID_METERTYPE) AS ID_METERTYPE,
					COALESCE(ametertypes.METERTYPE_".LANG_DEF.", metertypes.METERTYPE_".LANG_DEF.") AS METERTYPE_".LANG_DEF."
					FROM meters
					LEFT JOIN meters AS ameter ON ameter.CODE_METER=meters.A AND ameter.ID_BUILDING=meters.ID_BUILDING
					LEFT JOIN metertypes ON meters.ID_METERTYPE=metertypes.ID_METERTYPE
					LEFT JOIN metertypes AS ametertypes ON ameter.ID_METERTYPE=ametertypes.ID_METERTYPE
					LEFT JOIN meters_usages ON meters_usages.ID_METER=meters.ID_METER
					LEFT JOIN usages USING (ID_USAGE)
					LEFT JOIN flats_meters	ON flats_meters.ID_METER=meters.ID_METER
					LEFT JOIN flats USING (ID_FLAT)
					WHERE ((meters.ID_OUTPUT=2 $where_ametertype) OR ((meters.ID_OUTPUT=1 OR meters.ID_OUTPUT=3) $where_metertype)) AND ID_USAGE IS NOT NULL AND flats.ID_BUILDING=$b1
					GROUP BY meters.ID_METERTYPE, ameter.ID_METERTYPE  ORDER BY meters.ID_METERTYPE ASC
					";
	
	
	//echo $sql;
	$r = rs::inMatrix($sql);
	return $r;
	}
	
static function get_usages_by_idflats ($flat1, $metertype) {
	$id_building = sole::get_idbuilding_by_idflat($flat1);
	$output = new outputs($id_building);
	$output->set_schema();
	$usages = $output->schema[$metertype];
	
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
static function get_directusages_by_idflats ($flat1, $metertype) {

	$usages = sole::get_usage_list_by_idflats ($flat1, $metertype);
	$usages = sole::get_usage_list_by_idflats($flat1, $metertype, 't', true);
	
	return $usages;
}


static function get_direct_hourly_meters($flat)	{
	$sql = "SELECT * FROM meters
						LEFT JOIN flats_meters USING(ID_METER)
						LEFT JOIN metertypes USING(ID_METERTYPE)
					WHERE ID_FLAT=$flat AND ID_SUPPLYTYPE=1 AND HMETER>1";
					
			$r = rs::inMatrix($sql);
	return $r;
		
		}
	

static function get_usage_list_by_idbuilding($id_building, $metertype=false, $bilancio='t' ) {
	
	if($metertype > 0)	{
		$where_metertype = "AND meters.ID_METERTYPE=$metertype";
		$where_ametertype = "AND ameter.ID_METERTYPE=$metertype";
}

	else	{
		$where_metertype = '';
		$where_ametertype = '';
	}


	if($bilancio=='b')
		$sql = "SELECT DISTINCT
				GROUP_CONCAT(DISTINCT nzeusages.ID_NZEUSAGE ORDER BY nzeusages.ID_NZEUSAGE ASC SEPARATOR ',') AS ID_USAGE,
				GROUP_CONCAT(DISTINCT nzeusages.TITLE_".LANG_DEF." ORDER BY nzeusages.ID_NZEUSAGE	 ASC SEPARATOR ',') AS description,
				COALESCE(ameter.ID_METERTYPE, meters.ID_METERTYPE) AS ID_METERTYPE
				FROM  meters
				LEFT JOIN meters_usages USING (ID_METER)
				LEFT JOIN usages USING (ID_USAGE)
				LEFT JOIN nzes USING (ID_METER)
				LEFT JOIN nzes_nzeusages USING(ID_NZE)
				LEFT JOIN nzeusages USING(ID_NZEUSAGE)
						LEFT JOIN flats_meters USING(ID_METER)
				
				LEFT JOIN flats USING (ID_FLAT)
						LEFT JOIN metertypes USING(ID_METERTYPE)
				LEFT JOIN meters AS ameter ON ameter.CODE_METER=nzes.A_NZE AND ameter.ID_BUILDING=meters.ID_BUILDING
				WHERE ((nzes.ID_OUTPUT=2 $where_ametertype) OR ((nzes.ID_OUTPUT=1 OR nzes.ID_OUTPUT=3) $where_metertype)) AND nzes.ID_NZE IS NOT NULL AND flats.ID_BUILDING=$id_building 
				GROUP BY meters.ID_METER ORDER BY ID_METERTYPE ASC
	";
	else
		$sql = "SELECT DISTINCT
				GROUP_CONCAT(DISTINCT meters_usages.ID_USAGE ORDER BY meters_usages.ID_USAGE ASC SEPARATOR ',') AS ID_USAGE,
				GROUP_CONCAT(DISTINCT usages.USAGE_".LANG_DEF." ORDER BY meters_usages.ID_USAGE ASC SEPARATOR ',') AS description,
				COALESCE(ameter.ID_METERTYPE, meters.ID_METERTYPE) AS ID_METERTYPE
				FROM meters
				LEFT JOIN meters AS ameter ON ameter.CODE_METER=meters.A AND ameter.ID_BUILDING=meters.ID_BUILDING
				LEFT JOIN meters_usages ON meters_usages.ID_METER=meters.ID_METER
				LEFT JOIN usages USING (ID_USAGE)
				LEFT JOIN flats_meters	ON flats_meters.ID_METER=meters.ID_METER
				LEFT JOIN flats USING (ID_FLAT)
				WHERE ((meters.ID_OUTPUT=2 $where_ametertype) OR ((meters.ID_OUTPUT=1 OR meters.ID_OUTPUT=3) $where_metertype)) AND ID_USAGE IS NOT NULL AND flats.ID_BUILDING=$id_building 
				GROUP BY meters.ID_METER ORDER BY meters.ID_METERTYPE ASC
	";
					
	//echo $sql;
	$r = rs::inMatrix($sql);
	//var_dump($r);
	return $r;
	
}


static function get_usage_list_by_idflats($flat, $metertype=false, $bilancio='t', $direct=false ) {
	if($metertype > 0)	{
		$where_metertype = "AND meters.ID_METERTYPE=$metertype";
		$where_ametertype = "AND ameter.ID_METERTYPE=$metertype";
	}

	else	{
		$where_metertype = '';
		$where_ametertype = '';
	}
	
	if($direct) 
		$where_direct = "meters.ID_SUPPLYTYPE=1 AND ";
	


	if($bilancio=='b')
		$sql = "SELECT DISTINCT
				GROUP_CONCAT(DISTINCT nzeusages.ID_NZEUSAGE ORDER BY nzeusages.ID_NZEUSAGE ASC SEPARATOR ',') AS ID_USAGE,
				GROUP_CONCAT(DISTINCT nzeusages.TITLE_".LANG_DEF." ORDER BY nzeusages.ID_NZEUSAGE	 ASC SEPARATOR ',') AS description,
				COALESCE(ameter.ID_METERTYPE, meters.ID_METERTYPE) AS ID_METERTYPE
				FROM  meters
				LEFT JOIN meters_usages USING (ID_METER)
				LEFT JOIN usages USING (ID_USAGE)
				LEFT JOIN nzes USING (ID_METER)
				LEFT JOIN nzes_nzeusages USING(ID_NZE)
				LEFT JOIN nzeusages USING(ID_NZEUSAGE)
				LEFT JOIN flats_meters	USING ( ID_METER )

				LEFT JOIN flats USING (ID_FLAT)
				LEFT JOIN metertypes USING ( ID_METERTYPE )
				LEFT JOIN meters AS ameter ON ameter.CODE_METER=nzes.A_NZE AND ameter.ID_BUILDING=meters.ID_BUILDING
				WHERE ((nzes.ID_OUTPUT=2 $where_ametertype) OR ((nzes.ID_OUTPUT=1 OR nzes.ID_OUTPUT=3) $where_metertype)) AND nzes.ID_NZE IS NOT NULL AND flats_meters.ID_FLAT=$flat
				GROUP BY meters.ID_METER ORDER BY ID_METERTYPE ASC
				";
			else
				$sql = "SELECT DISTINCT
				GROUP_CONCAT(DISTINCT meters_usages.ID_USAGE ORDER BY meters_usages.ID_USAGE ASC SEPARATOR ',') AS ID_USAGE,
				GROUP_CONCAT(DISTINCT usages.USAGE_".LANG_DEF." ORDER BY meters_usages.ID_USAGE ASC SEPARATOR ',') AS description,
				COALESCE(ameter.ID_METERTYPE, meters.ID_METERTYPE) AS ID_METERTYPE
				FROM meters
				LEFT JOIN meters AS ameter ON ameter.CODE_METER=meters.A AND ameter.ID_BUILDING=meters.ID_BUILDING
				LEFT JOIN meters_usages ON meters_usages.ID_METER=meters.ID_METER
				LEFT JOIN usages USING (ID_USAGE)
				LEFT JOIN flats_meters	ON flats_meters.ID_METER=meters.ID_METER
				LEFT JOIN flats USING (ID_FLAT)
				WHERE $where_direct ((meters.ID_OUTPUT=2 $where_ametertype) OR ((meters.ID_OUTPUT=1 OR meters.ID_OUTPUT=3) $where_metertype)) AND ID_USAGE IS NOT NULL AND flats_meters.ID_FLAT=$flat
				GROUP BY meters.ID_METER ORDER BY meters.ID_METERTYPE ASC
				";

				//echo $sql;
				$r = rs::inMatrix($sql);
	return $r;

}


static function get_usages_by_idbuilding($id_building, $metertype=false, $bilancio='t' ) {
	if($metertype)
		$where_metertype = "AND metertypes.ID_METERTYPE=$metertype";
	else
		$where_metertype = '';
		
	if($bilancio=='b')
		$where_bilancio = " AND nzes.ID_METER IS NOT NULL";
		else
		$where_bilancio = '';
	
		$sql = "SELECT meters.K2_ID_USAGE AS ID_USAGE, descriptors.DESCRIPTOR_".LANG_DEF." AS description, meters.*
		FROM meters
		LEFT JOIN nzes USING (ID_METER)
					LEFT JOIN flats_meters	USING ( ID_METER ) 
					LEFT JOIN flats USING (ID_FLAT)
					LEFT JOIN metertypes USING ( ID_METERTYPE ) 
					LEFT JOIN descriptors ON descriptors.ID_DESCRIPTOR=meters.K2_ID_USAGE 
		WHERE flats.ID_BUILDING=$id_building $where_metertype AND meters.K2_ID_USAGE IS NOT NULL $where_bilancio
					GROUP BY meters.K2_ID_USAGE
					";
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
				$ret_usages[$i]['metertype'] = $metertype;
				$ret_usages[$i]['METERTYPE_'.LANG_DEF] = $metertype;
				$ret_usages[$i]['ID_USAGE'] = $v;
				$ret_usages[$i]['description'] = io::get_dp($v);
				$i++;
			}
	
	return $ret_usages;
}

# TABELLE VARIE
static function table_mk_flatslist($rs, $flds, $dos){ # $dos: DIRECT OR SHARED, 1 => DIRECT, 2 => SHARED
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

static function table_mk_lista($rs, $flds){
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

static function select_fhb($des){ # SELECT MULTI SCELTA FEDERAZIONI, HOUSING COMPANIES, BUILDINGS
	global $user;
	$add_flats = func_num_args()>1 ? func_get_arg(1) : false;
	
	### PREDISPONGO LE QUERIES
	$q_hcompany = "SELECT ID_HCOMPANY, CODE_HC FROM hcompanys ORDER BY CODE_HC ASC";
	$q_building = "SELECT ID_BUILDING, CODE_BLD FROM buildings ORDER BY CODE_BLD ASC";
	$q_flat = "SELECT ID_FLAT, CODE_FLAT FROM flats ORDER BY CODE_FLAT ASC";
	
	if($user->idg == 2){ # GM
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
	elseif($user->idg == 3){ # MHMU
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
	elseif($user->idg == 4){ # HMU
		$q_building = "SELECT ID_BUILDING, CODE_BLD FROM buildings 
		WHERE ID_HCOMPANY = '".$user -> aUser['ID_HCOMPANY']."'
		ORDER BY CODE_BLD ASC";
		;
		
		$q_flat = "SELECT ID_FLAT, CODE_FLAT FROM flats 
		Left Join buildings USING(ID_BUILDING)
		WHERE buildings.ID_HCOMPANY = '".$user -> aUser['ID_HCOMPANY']."'
		ORDER BY CODE_FLAT ASC";
	} 
	elseif($user->idg == 5){ # HMU
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
		// $input['buildings'.$des]->val = 10;
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
		$input['flats'.$des]->css = 'duecento graphparm'; 
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

static function mk_namebuilding($id_housingcompany, $text){
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

static function mk_nameflat($id_building){
	$ret = '000';
	
	$qcb = "SELECT COUNT(*) AS N_FLATS FROM flats WHERE ID_BUILDING = '$id_building'";
	$rcb = rs::rec2arr($qcb);
	
	$n = $rcb['N_FLATS'];
	if(empty($n)) $n = 1;
	
	$yyy = stringa::zero_fill($n, 3);
	$ret = $yyy;
	
	return $ret;
}

static function mk_namemeter($id_meter, $text){
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
	
	/*
	 * nome per contatori diretti
	 * */
	if($rmt['ID_SUPPLYTYPE'] == 1){
		
		$q="SELECT flats.CODE_FLAT, flats.ID_FLAT
		FROM flats_meters
		LEFT JOIN flats USING(ID_FLAT)
		WHERE flats_meters.ID_METER={$id_meter} LIMIT 1";
		$row=rs::rec2arr($q);
		
		$xxx = strtoupper($rmt['STYPE']);		
		$st = 'D';
		$yyy = $row['CODE_FLAT'];
	}
	
	/*
	 * nome per i contatori condivisi
	 * */
	elseif($rmt['ID_SUPPLYTYPE'] == 2){
		$st = 'C';
		$xxx = strtoupper($rmt['STYPE']);		
	}
	
	/*
	 * aggiunge un separatore
	 * */
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

static function select_year($id){
	$aOpts = array(); 

	$y = date('Y');
	for($i = $y; $i >= SHOW_FROM_YEAR; $i--){
		$aOpts[$i] =$i;
	}

	$input = new io();
	$input -> type = 'select'; 
	$input -> addblank = true; 
	$input->val = 2012;
	$input -> aval = $aOpts; //rs::id2arr("SELECT ID_UPLOADTYPE, UPLOADTYPE FROM uploadtypes ORDER BY UPLOADTYPE ASC"); 
	$input -> css = 'duecento'; 
	$input -> id = $id; 

	//$input->txtblank = S_CHOOSE.' '.strtolower(ANNO); 
	return $input -> set($id);
}

static function select_months($name){
	if(LANG_DEF=='IT')
		$mesi = array(1=>'Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre','Dicembre');
	else
		$mesi = array(1=>'January', 'Febraury', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November','December');
		
	$input = new io();
	$input -> type = 'select'; 
	$input -> addblank = true; 
	$input -> aval = $mesi; 
	$input -> id = $name; 
	$input -> txtblank = S_CHOOSE; 
	return $input -> set($name);
}

static function select_uploadtype($name){

	$input = new io();
	$input -> type = 'select'; 
	$input -> addblank = true; 
	$input -> aval = array('1' => '1', '2' => '2');
	$input -> id = $name; 
	$input -> txtblank = S_CHOOSE; 
	return $input -> set($name);
}

static function is_multi($id_meter){
	$ret = false;
	$qMeter = "SELECT ID_METERTYPE, HMETER FROM meters WHERE ID_METER = '$id_meter'";
	$rMeter = rs::rec2arr($qMeter);

	if($rMeter['ID_METERTYPE'] == 1 && $rMeter['HMETER'] > 1){ # CONTATORE ELETTRICO MULTIORARIO
		$ret = true;
	}
	return $ret;
}

static function get_last_measures($id_meter, $n){
	# ULTIMA E PENULTIMA MISURAZIONE
	$q = "SELECT measures.*, 
	meters.ID_METERTYPE,
	meters.HMETER,
	meters.MATRICULA_ID, 
	meters.REGISTERNUM,	
	TO_DAYS(D_MEASURE) AS GIORNI
	FROM msoutputs
	LEFT JOIN measures ON measures.ID_MEASURE=msoutputs.ID_MEASURE
	LEFT JOIN meters ON measures.ID_METER = meters.ID_METER
	WHERE measures.ID_METER =  '".$id_meter."'
	AND measures.IS_DEL = '0'
	ORDER BY measures.ANNO_MS DESC,
	measures.ID_UPLOADTYPE DESC
	LIMIT 0 , $n";
	
	$r = rs::inMatrix($q);
	return $r;
}

static function get_flats_by_idmeter($idmeter, $anno=null, $uploadtype=null){
	if($anno!=null & $uploadtype!=null)
		$q = "SELECT flats.*,
		flats_meters.*, OCCUPANCY
		FROM flats_meters
		Left Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
		LEFT JOIN occupancys ON flats.ID_FLAT=occupancys.ID_FLAT AND occupancys.ANNO_MS=$anno AND occupancys.ID_UPLOADTYPE=$uploadtype
		WHERE flats_meters.ID_METER = '$idmeter';
		";
	else
	$q = "SELECT flats.*,
	flats_meters.*
	FROM flats_meters
	Left Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
	WHERE flats_meters.ID_METER = '$idmeter';
	";
	return rs::inMatrix($q);
}

static function get_unit_by_metertype($metertype)	{
	$q = "SELECT UNIT FROM metertypes WHERE ID_METERTYPE = '$metertype'";
	$r = rs::rec2arr($q);
	return $r['UNIT'];

}

static function get_metertype_description($metertype)	{
	$q = "SELECT METERTYPE_".LANG_DEF." FROM metertypes WHERE ID_METERTYPE = '$metertype'";
	$r = rs::rec2arr($q);
	return $r['METERTYPE_'.LANG_DEF];
}

static function get_multicheck_flats_list_by_id_building($id_building, $mode, $id_meter){
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

static function get_multicheck_usages_list_by_id_meter($id_meter){
	$scheda = new nw('meters');
	$scheda -> ext_table(array('supplytypes'));
	$scheda -> add_mm('usages', $id_meter);
	return '<div>'.CHOOSE.' '.strtolower(USAGE).'</div>'.$scheda -> mmBox['usages'];
}

static function get_flat_info($id_flat){
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

static function get_id_building_from_id_meter($id_meter){
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

static function get_building_info($id_building){
	$ret = array();
	
	$q = "
	SELECT 
	buildings.*,
	hcompanys.*,
	federations.FEDERATION
	FROM buildings
	Left Join hcompanys ON buildings.ID_HCOMPANY = hcompanys.ID_HCOMPANY
	Left Join federations ON federations.ID_FEDERATION = hcompanys.ID_FEDERATION
	WHERE 
	buildings.ID_BUILDING = '$id_building'
	LIMIT 0,1
	";
	
	$ret = rs::rec2arr($q);
	return $ret;
}


static function is_building_monolocale($id_building)	{
	$sql = "SELECT COUNT(ID_FLAT) AS flatsnum FROM flats WHERE ID_BUILDING=$id_building ";
	$ret = rs::rec2arr($sql);
	return ($ret['flatsnum']==1);
	
}

static function get_meter_output($meter_code, $year, $period) {
	$sql = "SELECT meters.*, msoutputs 
			FROM meters
			LEFT JOIN measures USING(ID_METER)
			LEFT JOIN msoutput USING(ID_MEASURE)
			WHERE meters.CODE_METER='$meter_code' AND measures.ANNO_MS=$year AND measures.ID_UPLOADTYPE=$period";
}

static function delete_meter($id){
	$ret = false;
	
	$q = "DELETE FROM meters WHERE ID_METER = $id";
	if(mysql_query($q)){
		$ret = true;
	}
	
	$row=rs::rec2arr("SELECT ID_NZE AS id FROM nzes WHERE ID_METER={$id} LIMIT 1");
	$q="DELETE FROM nzes_nzeusages WHERE ID_NZE={$row['id']}";
	mysql_query($q);
	
	$q="DELETE FROM flats_meters WHERE ID_METER={$id}";
	mysql_query($q);
	
	$q="DELETE FROM nzes WHERE ID_METER={$id}";
	mysql_query($q);
	
	$q="DELETE FROM consumptions WHERE ID_METER={$id}";
	mysql_query($q);
	
	$q="DELETE FROM meters_productions WHERE ID_METER={$id}";
	mysql_query($q);
	
	$q="DELETE FROM meters_usages WHERE ID_METER={$id}";
	mysql_query($q);
	
	$q="DELETE FROM meters_usages WHERE ID_METER={$id}";
	mysql_query($q);
	
	$q="DELETE FROM metersusages_aggr WHERE ID_METER={$id}";
	mysql_query($q);
	
	return $ret;
}

static function get_metertype_by_codename($code_name, $id_flat){
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

static function get_idmeter_by_codename($code_name, $id_building)	{
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

static function get_metertype_by_idmeter($id_meter){
	$q = "SELECT meters.ID_METERTYPE	FROM meters
	WHERE meters.ID_METER = '$id_meter'
	LIMIT 1
	";
	
	$r = rs::rec2arr($q);
	return $r['ID_METERTYPE'];
}


static function get_conversions_from_id_flat($id_flat){
	$ret = array('1' => 1, '2' => 1, '3' => 1, '4' => '1', '5' => 1); 
  	$q = "
	SELECT 
	federations_conversions.ID_METERTYPE,
	federations_conversions.EP AS CONVERSION
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

static function is_occupied($id_flat, $id_uploadtype, $anno){
	$q = "SELECT * FROM occupancys WHERE ID_FLAT = '$id_flat' AND ID_UPLOADTYPE = '$id_uploadtype' AND ANNO_MS = '$anno' LIMIT 1";
	$r = rs::rec2arr($q);
	if($r['IS_OCCUPIED'] == 1 || $r['OCCUPANCY'] >= 20 ) 
		$ret = true;
	else 
		$ret = false;
	
	return $ret;
}


static function get_flat_by_usercode($code)	{
	$q = "SELECT ID_FLAT FROM flats LEFT JOIN users USING (ID_USER) WHERE CODE='$code'";
	$r = rs::rec2arr($q);
	
	return $r['ID_FLAT'];
}

static function get_flat_by_userid($id)	{
	$q = "SELECT ID_FLAT FROM flats  WHERE ID_USER=$id";
	$r = rs::rec2arr($q);

	return $r['ID_FLAT'];
}

static function get_coords_buildings(){
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

		google.maps.event.addListener(marker, \'click\', (static function(marker, i) {
		  return static function() {
			 infowindow.setContent(locations[i][0]);
			 infowindow.open(map, marker);
		  }
		})(marker, i));
		}
		</script>
		';
}

// controlla se tutte le misurazioni di un invio sono state convalidate
static function check_if_all_validated($aMeters, $upload_type, $year){
	if(empty($aMeters))
		$all_validated = false;
	else
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


static function deduci_fondoscala($number){
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

static function get_federation_by_id_building($id_building){
	$id_federation=false;
	if( ! empty($id_building)){
	$qFederation = "SELECT ID_FEDERATION FROM hcompanys
	LEFT JOIN buildings USING (ID_HCOMPANY)
		WHERE buildings.ID_BUILDING={$id_building} LIMIT 1";
	$rFederation = rs::rec2arr($qFederation);
		$id_federation=$rFederation['ID_FEDERATION'];
	}
	return $id_federation;
}

/*
 * restituisce gli utilizzi assegnati ad una federazione in base al mode indicato
 * */
static function get_usages($mode='building, federation', $id){
	if($mode=='building'){ /* ricava l'id federazione */
		$id=sole::get_federation_by_id_building($id);
	}
	$q="SELECT ID_USAGE AS id, USAGE_".LANG_DEF." AS value FROM usages
		WHERE ID_FEDERATION={$id}
		ORDER BY ID_USAGE";
	$rows=rs::inMatrix($q);
	return($rows);
}

/*
 * carica la lista di valori per un descrittore
 * */
static function get_descriptors($type, $id_self=false, $alias=false){
	
	if($alias){
		$id=$alias[0];
		$value=$alias[1];
	} else {
		$id='id';
		$value='value';
	}
	
	$extrawhere='';
	if($id_self){
		$extrawhere = " AND ID_SELF={$id_self}";
	}
	
	$q="SELECT ID_DESCRIPTOR AS {$id}, DESCRIPTOR_".LANG_DEF." AS {$value} FROM descriptors
		WHERE ID_DESCRIPTORS_TYPE='{$type}'{$extrawhere}";
	
	return rs::inMatrix($q);
}

/*
 * restituisce i contatori associati ad una federazione
 * */
static function get_meters_by_federation($id_federation, $a_fields=array()){
	
	$fields='ID_METER';
	if( ! empty($a_fields)){
		$fields='';
		foreach($a_fields as $field){
			$fields .= $field.', ';
		}
		if( ! empty($fields)){
			$fields = substr($fields, 0, -2);
		}
	}
	
	$q="SELECT {$fields}
	FROM meters
	LEFT JOIN buildings USING(ID_BUILDING)
	LEFT JOIN hcompanys USING(ID_HCOMPANY)
	WHERE hcompanys.ID_FEDERATION={$id_federation}
	";
	return rs::inMatrix($q);
}

/*
 * allinea la tabella d'appoggio 
 * */
static function allinea_utilizzi($id_meter){
	$q="SELECT * FROM meters_usages WHERE ID_METER={$id_meter} ORDER BY ID_USAGE ASC";
	$rows=rs::inMatrix($q);
	$list='';
	foreach($rows as $row){
		$list .= $row['ID_USAGE'].',';
	}
	
	$q="DELETE FROM metersusages_aggr WHERE ID_METER={$id_meter}";
	mysql_query($q);
	
	if( ! empty($list)){
		$list=substr($list, 0, -1);
		$q="INSERT INTO metersusages_aggr SET ID_METER={$id_meter}, STRING_USAGE='{$list}'";
		mysql_query($q);
	}
}

/*
 * elimina i valori nze associati ad un contatore
 * */
static function remove_nze_info($id_meter){
	$q="SELECT ID_NZE AS id FROM nzes WHERE ID_METER='{$id_meter}' LIMIT 1";
	$row=rs::rec2arr($q);
	
	if( ! empty($row['id'])){
		$q="DELETE FROM nzes_nzeusages WHERE ID_NZE={$row['id']}";
		mysql_query($q);
		
		$q="DELETE FROM nzes WHERE ID_NZE={$row['id']}";
		mysql_query($q);
	}
}

/*
 * controlla se esiste un altro contatore con lo stesso nome
 * */
static function is_uni_name_meter($id_meter, $name){
	$q="SELECT ID_BUILDING FROM meters WHERE ID_METER={$id_meter}";
	$row=rs::rec2arr($q);
	
	$meter_name = sole::mk_namemeter($id_meter, $name);
	$q="SELECT ID_METER AS id FROM meters WHERE ID_BUILDING={$row['ID_BUILDING']} AND CODE_METER LIKE '$meter_name' AND ID_METER <> {$id_meter} LIMIT 1";
	$r=rs::rec2arr($q);
	
	return empty($r['id']);

}

/*
 * genera la pagina html dell'edificio 
 * */
static function building_chart($id_building){
	$row = sole::get_building_info($id_building);
	$image_gallery='';
	
	/*
	 * applica il logo per la powerhouse europe
	 * */
	$img_powerhouse='';
	if($row['IS_POWERHOUSE']==1){
		$img_powerhouse='<img src="images/powerhouse-logo.jpg" alt="Powerhouse" style="width:120px; height:60px;"/>';
		if( ! empty($row['URL'])){
			$url=stringa::mk_http($row['URL']);
			$img_powerhouse=io::a($url, $val=array(), $img_powerhouse, array('target' => '_blank'));
		} else {
			$img_powerhouse=io::a('http://www.powerhouseeurope.eu/', $val=array(), $img_powerhouse, array('target' => '_blank'));
		}
	}
	
	/*
	 * carica le foto
	 * */
	$q = "SELECT
	files.PATH,
	files.TITLE,
	files.TYPE
	FROM
	files
	Left Join buildings_files USING(ID_FILE)
	WHERE
	buildings_files.ID_BUILDING={$id_building} AND
	files.TYPE = 'i'
	ORDER BY buildings_files.RANK ASC
	";
	$rows=rs::inMatrix($q);
	
	foreach($rows as $image){
	$image_gallery .= '<li><a href="upld/img/web/'.$image['PATH'].'" rel="prettyPhoto[gallery]"><img src="upld/img/sqr/'.$image['PATH'].'" width="80" height="80" alt="'.$image['TITLE'].'" /></a></li>';
	}
	
	/*
	 * carica le tabella di conversione per edificio e federazione
	 * */
	$convs=array('EP'=>'ba', 'CO2'=>'bb', 'EURO'=>'bc');
	
	$q="SELECT * FROM buildings_conversions WHERE ID_BUILDING={$id_building}";
	$rows=rs::inMatrix($q);
	$aValues=array();
	foreach($rows as $r){
		foreach($convs as $k=>$tmp){
			$aValues[$r['ID_METERTYPE']][$k]=$r[$k];
		}
	}
	
	/*
	 * ricava i valori di default legati alla federazione
	 * */
	$id_federation=sole::get_federation_by_id_building($id_building);
	$q="SELECT * FROM federations_conversions WHERE ID_FEDERATION={$id_federation}";
	$rows=rs::inMatrix($q);
	$aDefaults=array();
	foreach($rows as $r){
		foreach($convs as $k=>$tmp){
			$aDefaults[$r['ID_METERTYPE']][$k]=$r[$k];
		}
	}
	
		/*
		 * ricava la lista dei tipi di energia
		 * */
		$q = "SELECT * FROM metertypes";
		$rMtypes = rs::inMatrix($q);
			$rMtypes = arr::semplifica($rMtypes, 'ID_METERTYPE');
	
		/*
		 * costruzione del corpo tabella
		 * */
		ob_start();
		$colspan=count($convs)+2;
	
		$energy = array();
		foreach($rMtypes as $k => $type){
		// acqua non ha bisogno della conversione
			if($type['ID_METERTYPE'] == 5){
				continue;
			}
		$energy[$type['ID_METERTYPE']] = '<span style="float:right;">1 '.$type['UNIT'].' = </span>'.$type['METERTYPE_'.LANG_DEF].':';
		}
	
		$th='<th width="260"></th>';
		foreach($convs as $conv=>$field_prefix){
	
			switch ($conv){
			case 'EP':
		$tmp=__('Energia Primaria');
					break;
		case 'CO2':
		$tmp=__('CO2 Equivalente');
			break;
			case 'EURO':
				$tmp=__('Costo');
				break;
			}
						$th.='<th width="300">'.$tmp.'</th>';
			}
			$th.='<th></th>';
	
			$a_suffix=array(	'EP'=>	' kWhEP',
			'CO2'=>	' kg CO<sub>2</sub>-eq',
			'EURO'=>' &euro;'
			);
	
		$i=0;
		foreach($energy as $id_energy => $label){
		$class = $i%2==1 ? ' class="contrast"' : '';
		?>
			<tr<?=$class?>>
				<td valign="top"><?=$label?></td>
				<?
				foreach($convs as $conv=>$field_prefix){ 
					
					$value='';
					if( array_key_exists($id_energy, $aValues) && array_key_exists($conv, $aValues[$id_energy])){
						if( $aValues[$id_energy][$conv]>=0){
							$value=$aValues[$id_energy][$conv];
						}
					}
					
					$placeholder='';
					if( array_key_exists($id_energy, $aDefaults) && array_key_exists($conv, $aDefaults[$id_energy])){
						if( $aDefaults[$id_energy][$conv]>=0){
							$placeholder=$aDefaults[$id_energy][$conv];
						}
					}
		
					$field_name=$field_prefix.$id_energy;
		
					if( empty($value)){
						$value=$placeholder;
					}
					
					if($value==''){
						$value='[nd]';
					}
					
				?>
				<td><?=$value?> <?=$a_suffix[$conv]?></td>
				<?
				}		
				?>
				<td></td>
			</tr>
			<?
			$i++;
		}
		$tr = ob_get_clean();
		ob_start();
		?>
		<div id="contenitore_titolo_scheda">
			<div style="position:absolute; top:18px; left:24px;"><?=$img_powerhouse?></div>
			<div id="titolo_scheda">
				<h2><?=$row['NAME_BLD']?></h2>
				<div><?=$row['FEDERATION']?> | <a href="hcompany-chart.php?id=<?=$row['ID_HCOMPANY']?>"><?=$row['NAME_HC']?></a></div>
			</div>
		</div>
		<div style="background-color: #ffffff; padding:90px 12px 12px;">
			<h4><?=__('Info monitoraggio')?></h4>
			<p>
				<?=empty($row['MONITORINFO_'.LANG_DEF]) ? '---' : $row['MONITORINFO_'.LANG_DEF] ?>
			</p>
			<h4><?=__('Descrizione generale')?></h4>
			<p>
				<?=empty($row['DESCRIP_BLD_'.LANG_DEF]) ? '---' : $row['DESCRIP_BLD_'.LANG_DEF] ?>
			</p>

			<h4><?=__('Tabella di conversione')?></h4>
			<div style="margin:12px 0;">
				<table class="list">
					<tr class="base"><?=$th?></tr>
					<?=$tr?>				
				</table>
			</div>
			
			<? if( ! empty($image_gallery)){?>
			<h4><?=__('Galleria immagini')?></h4>
			<div>
				<ul id="building-images">
				<?=$image_gallery?>
				</ul>
			</div>
			<?}?>
		</div>
	<?
	return ob_get_clean();
}

	static function get_conversion_units($id_building)	{
		$sql = "SELECT federations_conversions.ID_METERTYPE, COALESCE(buildings_conversions.EP, federations_conversions.EP) AS p,
										COALESCE(buildings_conversions.CO2, federations_conversions.CO2) AS c,
										COALESCE(buildings_conversions.EURO, federations_conversions.EURO) AS e,
										1 AS f
					  FROM federations_conversions
						LEFT JOIN hcompanys USING(ID_FEDERATION)
						LEFT JOIN buildings USING(ID_HCOMPANY)
						LEFT JOIN buildings_conversions USING(ID_BUILDING)
				
				
						WHERE buildings.ID_BUILDING=" . $id_building;
			
		//echo $sql;
	
		$data = rs::inMatrix($sql);
		//var_dump($data);
		foreach($data as $k=>$v)	{
			$conversion_unit[$v['ID_METERTYPE']] = $v;
		}
	
		return $conversion_unit;

}

}
?>