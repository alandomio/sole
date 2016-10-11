<?php
class outputs{
var $keys = array();
var $aOccupied = array();
var $media = array(); 
var $id_flat_user = false;
var $debug = false;

function __construct($id_building, $year = 0, $mode = ''){
	$this -> id_user = func_num_args()>3 ? func_get_arg(3) : false;
	if(!empty($this -> id_user)){
		$this -> get_id_flat_by_id_user($this -> id_user);
	}

	$this -> set_building($id_building);
	$this -> set_year($year);
	$this -> set_mode($mode);
	
	if(!empty($year)){
		$this -> set_schema();
		$this -> set_vals();
		$this -> set_table();
	}
}

function debug(){
	$this -> debug = true;
	$this -> set_vals();
	$this -> set_table();
}

function get_id_flat_by_id_user($id_user){
	$qF = "SELECT ID_FLAT FROM flats WHERE ID_USER = '".$id_user."' LIMIT 1";
	$rF = rs::rec2arr($qF);
	$this -> id_flat_user = $rF['ID_FLAT'];
	return $this -> id_flat_user;
}

function set_building($id){
	$this -> id_building = $id;
	$this -> info_building = rs::rec2arr("SELECT * FROM buildings WHERE ID_BUILDING = '$id' LIMIT 0,1");
	$this -> get_flats();
}

function get_flats(){
	$this -> a_flat_list = sole::get_flats_by_idbuilding($this -> id_building);
	$this -> n_flats = count($this -> a_flat_list);
	$this -> tot_netarea = 0;
	foreach($this -> a_flat_list as $v){
		$this -> tot_netarea += $v['NETAREA'];
	}
}

function set_year($year){
	$this -> year = $year;
}

function set_mode($mode){
	# NPVFULL
	# NPVM2
	# NPVFULLEP
	# NPVM2EP
	# F1
	//$this -> a_modes = array('NPVFULL' => '&sect;NPV/full', 'NPVM2' => '&sect;NPV/m2', 'NPVFULLEP' => '&sect;NPV/full (EP)', 'NPVM2EP' => '&sect;NPV/m2 (EP)', 'F1' => '% F1'); 

	$this -> a_modes = array(
	'NPVFULL' => LBL_NPVFULL,
	'F1' => LBL_F1,
	'NPVM2' => LBL_NPVM2,
	'NPVFULLEP' => LBL_NPVFULLEP,
	'NPVM2EP' => LBL_NPVM2EP
);
	
	
	$this -> mode = $mode;
	$this -> lbl_mode = '';
	if(array_key_exists($mode, $this -> a_modes)){
		$this -> lbl_mode = $this -> a_modes[$mode];
	}
	
	$this -> is_media = false; $this -> clear_empty = false;
	if($this -> mode == 'NPVM2' || $this -> mode == 'NPVM2EP'){
		$this -> is_media = true;
	}
	elseif($this -> mode == 'F1'){
		$this -> clear_empty = true;
	}
}

function add_to_schema($type, $usage){
	if(!empty($usage)){
		if(!array_key_exists($type, $this -> schema)){ # CREO IL RAMO PER TIPOLOGIA
			$this -> schema[$type] = array();
		}
		if(!in_array($usage, $this -> schema[$type])){ # INSERISCO UL NUOVO UTILIZZO
			$this -> schema[$type][] = $usage;
		}
	}
}

function set_schema(){ # CREA LO SCHEMA RELATIVO A TIPO CONTATORE -> UTILIZZI	
	$this -> schema = array();
	$q = "SELECT meters.*,
	metertypes.*
	FROM
	meters
	Inner Join flats_meters ON meters.ID_METER = flats_meters.ID_METER
	Inner Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
	Inner Join metertypes ON meters.ID_METERTYPE = metertypes.ID_METERTYPE
	WHERE flats.ID_BUILDING = '".$this -> id_building."' AND
	meters.IS_DEL <> '1' AND
	meters.ID_OUTPUT <> '4'
	GROUP BY meters.ID_METER
	ORDER BY meters.CODE_METER ASC
	";
	$r = rs::inMatrix($q);

	foreach($r as $k => $v){ # VERIFICO CHE LA TIPOLOGIA SIA CONGRUA CON IL TIPO DI OUTPUT A/B
	
		if($v['ID_OUTPUT'] == 2){ # ID_OUTPUT = A/B
			$q_new_type = "SELECT meters.*,
			metertypes.*
			FROM
			meters
			Inner Join flats_meters ON meters.ID_METER = flats_meters.ID_METER
			Inner Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
			Inner Join metertypes ON meters.ID_METERTYPE = metertypes.ID_METERTYPE
			WHERE flats.ID_BUILDING = '".$this -> id_building."' AND
			meters.CODE_METER = '".$v['A']."'
			LIMIT 0,1
			";

			$r_new_type = rs::rec2arr($q_new_type);

			# AGGIUNGO L'UTILIZZO DEL CONTATORE PRINCIPALE SULLA TIPOLOGIA EREDITATA
			self::add_to_schema($r_new_type['ID_METERTYPE'], $v['K2_ID_USAGE']);
			
			//echo BR.'Id Meter '.$r_new_type['ID_METER'].' Metertype: '.$r_new_type['ID_METERTYPE'].' Usage: '.$r_new_type['K2_ID_USAGE'].BR.BR;
		}
		else{
			self::add_to_schema($v['ID_METERTYPE'], $v['K2_ID_USAGE']);
		}
	}
	
	# ELIMINO LE VOCI DI INTESTAZIONE QUANDO NON HO UTILIZZI ASSOCIATI
	$this -> clear_schema_types();
}

function clear_celle($id_type){ # ELIMINA TUTTI I VALORI DELLE CELLE RELATIVE AD UN TIPO DI CONTATORE
	foreach($this -> vals as $k => $v){
		$type = stringa::leftfrom($k, '.');
		//echo $type.' '.$k.BR;
		if($type == $id_type){
			unset($this -> vals[$k]);
		}
	}
}

function clear_schema(){ # ELIMINA LE COLONNE SE ESTATE + INVERNO HANNO TUTTI VALORI A ZERO
	$ee=''; $aa = '';
	if($this -> mode == 'NPVM2EP' || $this -> mode == 'NPVFULLEP'){ # ELIMINA I VALORI ACQUA SE EP
		unset($this -> schema[5]);
		self::clear_celle(5);
	}
	
	// mostro tutte le colonne anche se con valori a zero
	if(!$this -> clear_empty){ return false; }
	#####################################################
	$a_mantieni = array();
	$a_elimina = array();
	foreach($this -> vals as $k => $v){
		if(!is_array($v)){
			
			$id_upload_type = stringa::rightfromlast($k, '.');
			$key = stringa::leftfromlast($k,'.');
			$a_elimina[$key] = 'Elimina';
			$ee .= ' '.$key;
			
			if(!array_key_exists($key, $a_mantieni) && !empty($v)){ 
				$a_mantieni[$key] = 'Ok, stampa colonne!';
				$aa .= ' '.$key;
			}
			
		}
	}
	
	foreach($a_elimina as $k => $v){
		
		if(!array_key_exists($k, $a_mantieni)){ # ELIMINA UTILIZZO PER LA TIPOLOGIA
			
			$k_usage = stringa::rightfromlast($k,'.');
			$k_type = stringa::leftfromlast($k,'.');
			unset($this -> schema[$k_type][$k_usage]);
			unset($this -> vals[$k.'.1']);
			unset($this -> vals[$k.'.2']);
		}
	}
	
	$this -> clear_schema_types();
}

function clear_schema_types(){
	foreach($this -> schema as $k => $v){
		if(count($v)<1){
			unset($this -> schema[$k]);
		}
	}
}

// calcola i valori da utilizzare per le medie per i due periodi estate / inverno
function get_media_values(){
	$this -> media['tot_flat'][1] = 0;
	$this -> media['tot_flat'][2] = 0;	
	$this -> media['tot_netarea'][1] = 0;
	$this -> media['tot_netarea'][2] = 0;

	foreach($this -> a_flat_list as $k => $flat){
		$id_flat = $flat['ID_FLAT'];
		$a = $this -> aOccupied[$id_flat][1] = sole::is_occupied($id_flat, 1, $this -> year); // winter
		$b = $this -> aOccupied[$id_flat][2] = sole::is_occupied($id_flat, 2, $this -> year); // summer
		
		if($a){
			$this -> media['tot_flat'][1] ++;
			$this -> media['tot_netarea'][1] += $flat['NETAREA'];
		}
		
		if($b){
			$this -> media['tot_flat'][2] ++;
			$this -> media['tot_netarea'][2] += $flat['NETAREA'];
		}
	}
	//echo ' 1: '.$this -> media['tot_flat'][1] .' '.$this -> media['tot_netarea'][1];
	//echo ' 2: '.$this -> media['tot_flat'][2] .' '.$this -> media['tot_netarea'][2];
}

function set_vals(){ # PER OGNI CELLA VADO A CALCOLARE IL VALORE DELL'OUTPUT
	# array usati per stampare i totali
	$this -> vals = array();
	$this -> alerts = array();
	$this -> nd = array();
	$this -> media_n_flats = array();
	$this -> media_count = array();
	
	$a_uploadtypes = array(1, 2);
	
	$this -> get_media_values();
	
	
	foreach($this -> a_flat_list as $k => $flat){ # ID_FLAT, CODE_FLAT ...
		$id_flat = $flat['ID_FLAT'];
		
		foreach($this -> schema as $id_metertype => $a_usages){
			foreach($a_usages as $n_usage => $usage){
				$sql_usages = " AND meters.K2_ID_USAGE = '$usage' ";
				foreach($a_uploadtypes as $kk => $id_uploadtype){
					$q = "SELECT 
					measures.ID_MEASURE,
					flats_meters.ID_FLAT,
					meters.ID_OUTPUT,
					descriptors.DESCRIPTOR_".LANG_DEF." AS ETICHETTA
					FROM measures
					LEFT JOIN meters USING(ID_METER)
					LEFT JOIN flats_meters USING(ID_METER)
					LEFT JOIN descriptors ON meters.K2_ID_USAGE = descriptors.ID_DESCRIPTOR
					LEFT JOIN meters AS meters2 ON meters.A = meters2.CODE_METER
					WHERE
					flats_meters.ID_FLAT = '".$flat['ID_FLAT']."' AND
					measures.ID_UPLOADTYPE = '$id_uploadtype' AND
					measures.ANNO_MS = '".$this -> year."' AND
					meters.ID_OUTPUT <> '4' AND
					(meters.ID_METERTYPE = '$id_metertype' OR meters2.ID_METERTYPE = '$id_metertype')".
					$sql_usages."
					GROUP BY measures.ID_MEASURE
					";
					
					//echo BR;	
					$r = rs::inMatrix($q);
					
					$output = 0;
					$overallstatus = 'nd'; 
					//$overallstatus = 'valid'; 
					foreach($r as $measure){ # RICAVO L'OUTPUT
						$dati = misurazioni::get_output($measure['ID_MEASURE'], $id_flat, $this -> mode);

						$status = $dati['status'];
						$output += $dati['value'];
						// echo $overallstatus.' '.$status.' '.$output.BR;
						
						if($status == 'nd'){
							$overallstatus = 'nd';
						}
						elseif($status == 'wrong'){
							if($status != 'nd')
								$overallstatus = 'wrong';
						} else {
							$overallstatus = 'valid';
						}
					}
					
					$new_key = $id_metertype.'.'.$n_usage.'.'.$id_uploadtype;
					$new_key_media = $new_key.'_media';
					$new_key_netarea = $new_key.'_netarea';
					
					$this -> keys[$new_key] = 1;
					$this -> vals[$id_flat][$new_key] = $output . '|' . $overallstatus; # VALORI DI OGNI CELLA
					$this -> vals[$id_flat][$new_key_media] = $output * $flat['NETAREA']; # VALORI DI OGNI CELLA
					
					// Creo i valori di default
					if(!array_key_exists($new_key, $this -> vals)){
						$this -> vals[$new_key] = '0|' . $overallstatus;
						$this -> vals[$new_key_media] = 0;
						$this -> vals[$new_key_netarea] = 0;
						$this -> alerts[$new_key] = false;
						$this -> nd[$new_key] = true;
						$this -> media_n_flats[$new_key] = 0;
						$this -> media_count[$new_key] = 0;
					}
					
					if($overallstatus == 'valid'){ # NETAREA PER LE MEDIE
						//echo 'nonvaben'.BR;
						$this -> nd[$new_key] = false;
						$this -> vals[$new_key_netarea] += $flat['NETAREA'];
						
						// $u_type = substr($new_key, -1);
						
						$this -> media_n_flats[$new_key] ++;
						$this -> media_count[$new_key] += $output;
						
					} elseif($overallstatus == 'nd') { // scrive in rosso il valore totale
						$this -> alerts[$new_key] = true;
						
					} elseif($overallstatus == 'wrong'){
						$this -> nd[$new_key] = false;
						$this -> alerts[$new_key] = true;
					} 
					
					# NPV CALCOLATI SU TUTTI I VALORI
					# SE ANCHE UNO SOLO DEI VALORI è WRONG O ND, LO SCRIVO IN ROSSO
					$this -> vals[$new_key] += $output; # VALORI TOTALI SUDDIVISI PER APPARTAMENTO, USO, INVIO
				
					# MEDIE CALCOLATE SOLO SUI VALORI VALIDI
					# (($output * $flat['NETAREA'])totale_validi) / totale_mq_validi
					
					// output * netarea per il calcolo della media pesata
					if($overallstatus == 'valid' || $overallstatus == 'wrong'){ 
						// echo $output." * ".$flat['NETAREA'].BR;
						$this -> vals[$new_key_media] += $output * $flat['NETAREA'];
					}
					//elseif($overallstatus == 'wrong') echo $output .'*'. $flat['NETAREA'].BR;
				}
			}
		}
	}
	$this -> clear_schema();
}

function prepare_value($id, $my_key){ # FORMATTA I VALORI DA STAMPARE NELLE CELLE
	$perc = $this -> mode == 'F1' ? '%' : '';
	$aVals = explode('|', $this -> vals[$id][$my_key]);

	$valore = is_numeric($aVals[0]) ? $aVals[0] : 0;
	$stato = array_key_exists(1, $aVals) ? $aVals[1] : 'Blank!';
	
	if($stato == 'nd'){
		if($this -> debug){
			$valore = '[Nd] '.$valore;
		} else {
			$valore = '[Nd]';
		}
	}
	elseif($stato == 'wrong'){
		$valore = '<span class="green-output">'.num::format($valore, 2, DEC_SEP, THO_SEP).$perc.'</span>';
	} 
 	elseif($this -> mode == 'F1'){
		$valore = ($valore > 0) ? num::format($valore, 2, DEC_SEP, THO_SEP).$perc : '';
	
	} else {
		
		$valore = num::format($valore, 2, DEC_SEP, THO_SEP).$perc;
	}
	return $valore;
}

function set_table(){
	$this -> th1 = '<th colspan="2">'.$this -> lbl_mode.'</th>';
	$this -> th2 = '<th colspan="2"></th>';
	$this -> th3 = '<th width="100">'.CODE_FLAT.'</th><th width="100">'.NETAREA.'</th>';
	$this -> footer = '<td></td><td align="right"><strong>Total</strong></td>';
	$this -> footer_media = '<td align="right" colspan="2"><strong>Media</strong><br /> '.$this -> n_flats.' appartamenti, '.$this -> tot_netarea.'m2</td>';

	$q_metertypes = "SELECT * FROM metertypes";
	$r_metertypes = rs::inMatrix($q_metertypes);
	$r_metertypes = arr::semplifica($r_metertypes, 'ID_METERTYPE');

	$this -> tr = '';
	$a_uploadtypes = array(1, 2);		
	
	$i = 0;
	foreach($this -> keys as $my_key => $var){
		$a_upload_css = $i % 2 == 0 ? array('class' => 'winter') : array('class' => 'summer');
		
		// echo $my_key.' '.$i%2 .' '.$a_upload_css.BR;
		if(array_key_exists($my_key, $this -> vals)){
			# RIGA PER TOTALI
			if($this -> nd[$my_key]){
				$val_td = '[Nd]';
			}
			elseif($this -> alerts[$my_key]){
				$val_td = '<span class="green-output">'.num::format($this -> vals[$my_key], 2, DEC_SEP, THO_SEP).'</span>';
			} else {
				$val_td = num::format($this -> vals[$my_key], 2, DEC_SEP, THO_SEP);
			}
			
			$this -> footer .= mytag::in( '<strong>'.$val_td.'</strong>' , 'td', $a_upload_css); 
			
			# RIGA PER MEDIE
			// num::format(( $this -> vals[$my_key.'_media'] / $this -> tot_netarea;
			
			$val_netarea = num::format( $this -> vals[$my_key.'_netarea'], 2, DEC_SEP, THO_SEP);
			if($this -> nd[$my_key]){
				$val_td = '[Nd]';
				if($this -> debug){
					// $val_td .= 'debug mode';
				}
			} else {
				if($this -> vals[$my_key.'_netarea'] == 0) $this -> vals[$my_key.'_netarea'] = 1;
				//$media_pesata = num::format( $this -> vals[$my_key.'_media'] / $this -> vals[$my_key.'_netarea'], 2, DEC_SEP, THO_SEP);
				
				$id_upload = substr($my_key, -1);
				$netarea_for_media = $this -> media['tot_netarea'][$id_upload];
				// $media_pesata = num::format( $this -> vals[$my_key.'_media'] / $netarea_for_media, 2, DEC_SEP, THO_SEP).' '.$this -> vals[$my_key.'_media'].'/'.$netarea_for_media;
				$media_pesata = num::format( $this -> vals[$my_key.'_media'] / $netarea_for_media, 2, DEC_SEP, THO_SEP);
				
				// echo $this -> vals[$my_key.'_media'].'/'.$netarea_for_media.'='.$media_pesata.BR;
				
				if(!empty($this -> media_n_flats[$my_key])){
				
					$num_flats_for_media = $this -> media['tot_flat'][$id_upload];
					//$media_aritmetica = num::format( $this -> media_count[$my_key] / $this -> media_n_flats[$my_key], 2, DEC_SEP, THO_SEP);
					//$media_aritmetica = num::format( $this -> media_count[$my_key] / $this -> media_n_flats[$my_key], 2, DEC_SEP, THO_SEP);
				}
				$val_td = $media_pesata;
			}
			$this -> footer_media .= mytag::in( '<strong>'.$val_td.'</strong>' , 'td', $a_upload_css); 
			$i ++;
		}
	}
	// 171.92
	foreach($this -> a_flat_list as $k => $flat){ # ID_FLAT, CODE_FLAT ...
		$this -> td = '';
		// controllo se l'appartamento è occupato nei due periodi
/* 		$a = $this -> aOccupied[$flat['ID_FLAT']][1] = sole::is_occupied($flat['ID_FLAT'], 1, $this -> year);
		$b = $this -> aOccupied[$flat['ID_FLAT']][2] = sole::is_occupied($flat['ID_FLAT'], 2, $this -> year);
		// echo $a.' : '.$b.BR;
 */		
		if($this -> id_flat_user > 0 && $this -> id_flat_user == $flat['ID_FLAT']){
			$this -> td .= mytag::in($flat['CODE_FLAT'], 'td', array()); 
			$this -> td .= mytag::in($flat['NETAREA'], 'td', array()); 
		} elseif($this -> id_flat_user > 0) { # do nothing

		} else {
			$this -> td .= mytag::in($flat['CODE_FLAT'], 'td', array()); 
			$this -> td .= mytag::in($flat['NETAREA'] , 'td', array()); 
		}

		$perc = $this -> mode == 'F1' ? '%' : '';
		foreach($this -> schema as $id_metertype => $a_usages){
			foreach($a_usages as $n_usage => $usages){
				$lbl_usages = io::get_dp($usages);

				foreach($a_uploadtypes as $kk => $id_uploadtype){ # W / S
					if($this -> id_flat_user > 0 && $this -> id_flat_user == $flat['ID_FLAT']){
						$a_upload_css = $id_uploadtype == 1 ? array('class' => 'winter') : array('class' => 'summer');
						$my_key = $id_metertype.'.'.$n_usage.'.'.$id_uploadtype;
						$valore = $this -> prepare_value($flat['ID_FLAT'], $my_key);
						$this -> td .= mytag::in($valore, 'td', $a_upload_css); 
					} elseif($this -> id_flat_user > 0) { # do nothing
						
					
					} else {
						$a_upload_css = $id_uploadtype == 1 ? array('class' => 'winter') : array('class' => 'summer');
						$my_key = $id_metertype.'.'.$n_usage.'.'.$id_uploadtype;
						$valore = $this -> prepare_value($flat['ID_FLAT'], $my_key);
						$this -> td .= mytag::in($valore, 'td', $a_upload_css); 
					}
				}
			}
		}
		$this -> tr .= mytag::in($this -> td, 'tr', array());
	}
	
	foreach($this -> schema as $id_metertype => $a_usages){
		$lbl_unit = ' ['.$r_metertypes[$id_metertype]['UNIT'].']';
		if($this -> mode == 'NPVM2EP' || $this -> mode == 'NPVFULLEP'){ # ELIMINA I VALORI ACQUA SE EP
			$lbl_unit = ' [kWh EP]';
		}
	
		$this -> th1 .= mytag::in($r_metertypes[$id_metertype]['METERTYPE_'.LANG_DEF].$lbl_unit, 'th', array('colspan' => count($a_usages) * 2, 'class' => 'bordato'));
	
		foreach($a_usages as $n_usage => $usage){
			$lbl_usages = io::get_dp($usage);

			$this -> th2 .= mytag::in($lbl_usages, 'th', array('colspan' => '2', 'class' => 'bordato'));
			
			foreach($a_uploadtypes as $kk => $id_uploadtype){ # W / S
				$this -> th3 .= mytag::in($id_uploadtype == 1 ? SBL_WINTER : SBL_SUMMER, 'th', array('class' => 'bordato', 'width' => '100'));

				$a_upload_css = $id_uploadtype == 1 ? array('class' => 'winter') : array('class' => 'summer');
				$my_key = $id_metertype.'.'.$n_usage.'.'.$id_uploadtype;
				$this -> td .= mytag::in(num::format($this -> vals[$my_key]['output'], 2, DEC_SEP, THO_SEP), 'td', $a_upload_css); 
			}
		}
	}
	
	$this -> th1 = mytag::in($this -> th1, 'tr', array());
	$this -> th2 = mytag::in($this -> th2, 'tr', array());
	$this -> th3 = mytag::in($this -> th3, 'tr', array());
	$this -> footer = mytag::in($this -> footer, 'tr', array());
	$this -> footer_media = mytag::in($this -> footer_media, 'tr', array());

	if($this -> is_media){
		$this -> table = mytag::in($this -> th1.$this -> th2.$this -> th3.$this -> tr.$this -> footer_media, 'table', array('class' => 'list'));
	}
	elseif($this -> mode == 'F1'){
		$this -> table = mytag::in($this -> th1.$this -> th2.$this -> th3.$this -> tr, 'table', array('class' => 'list'));
	}
	else{
		$this -> table = mytag::in($this -> th1.$this -> th2.$this -> th3.$this -> tr.$this -> footer, 'table', array('class' => 'list'));
	}
}

function print_table(){
	print $this -> table;
}

function get_meters_by_idflat($id){
	return sole::get_meters_by_idflat($id);
}

}
?>