<?php
include_once 'init.php';
$user = new autentica($aA5);
$user -> login_standard();

$MYFILE->add_css(JS_MAIN.'chosen/css/chosen.css');

/*
 * richieste json
 * */
if( array_key_exists('action', $_REQUEST)){
	$action = prepare($_REQUEST['action']);
	
	/*
	 * lista misuratori
	 * */
	if($action=='get_meters_list'){
		$id_building=prepare($_REQUEST['id']);
		
		$ret=array('meters'=>array() );
		
		/*
		 * prima i misuratori condivisi
		 * */
		$a = sole::get_meters_by_idbuilding($id_building, $prima_condivisi=true);
		$aDuplicates = arr::duplicate($a, 'CODE_METER');
		$flag_dup = false;
		
		/*
		 * adatta i dati della lista misuratori
		 * */
		$meters=array();
		foreach($a as $k => $row){

			$meters[$k]=array();
			$meters[$k]['css'] = 'view_meter';
			
			if(in_array($row['CODE_METER'], $aDuplicates)){
				$meters[$k]['class'] .= ' alert_duplicate';
				$flag_dup = true;
			}

			$meters[$k]['licss'] = '';
			if( empty($row['D_REMOVE']) || $row['D_REMOVE'] != '0000-00-00'){
				$meters[$k]['licss'] .= ' alert_removed';
	}
		
			$meters[$k]['id']=$row['ID_METER'];
			$meters[$k]['code']=$row['CODE_METER'];
}

		exit( json_encode(array('meters'=>$meters, 'duplicated'=>$flag_dup)));
	}

	elseif($action=='unimeter'){
		exit( json_encode(sole::is_uni_name_meter($_REQUEST['id'], $_REQUEST['name'])));
	}
	
	/*
	 * controlla se è in una formula e se può essere chiuso
	 * */
	elseif ($action == 'formula'){
		$message=''; $success='g';
	
		$id_meter=prepare4sql($_REQUEST['id']);
			
		$q="SELECT CODE_METER, ID_BUILDING, D_REMOVE FROM meters WHERE ID_METER={$id_meter} LIMIT 1";
		$row=rs::rec2arr($q);
			
		
		/*
		 * verifica se il contatore è in una formula
		 */
		$q="SELECT ID_METER AS id, CODE_METER as code FROM meters WHERE meters.ID_BUILDING='{$row['ID_BUILDING']}' AND ( FORMULA LIKE '%{$row['CODE_METER']}%' OR A LIKE '%{$row['CODE_METER']}%' OR B LIKE '%{$row['CODE_METER']}%' ) LIMIT 1";
		$r=rs::rec2arr($q);
		if( ! empty($r['id'])){
			$success='r';
			$message .= str_replace(	array('{code_meter}', '{id_meter}', '{code}', '{id}'),
					array($row['CODE_METER'], $id_meter, $r['code'], $r['id']),
					'contatore {code_meter} (id {id_meter}) trovato nella formula del contatore {code} (id {id})').BR;
		}

		/*
		 * verifica se il contatore è in una formula nze
		 */
		$q="SELECT ID_METER AS id, meters.CODE_METER AS code FROM nzes LEFT JOIN meters USING(ID_METER) WHERE meters.ID_BUILDING='{$row['ID_BUILDING']}' AND ( A_NZE LIKE '%{$row['CODE_METER']}%' OR B_NZE LIKE '%{$row['CODE_METER']}%' ) LIMIT 1";
		$r=rs::rec2arr($q);
		if( ! empty($r['id'])){
			$success='r';
			$message .=
			str_replace(array('{row[CODE_METER]}', '{id_meter}', '{r[code]}', '{r[id]}'), array($row['CODE_METER'], $id_meter, $r['code'], $r['id']), __('contatore {row[CODE_METER]} (id {id_meter}) trovati nella formula nZE del contatore {r[code]} (id {r[id]})') );
		}

		if($success=='g'){
			if( ! empty($row['D_REMOVE']) || $row['D_REMOVE'] != '0000-00-00'){
				$success='y';
				$message=__('Contatore già chiuso');
			}
		}

		exit(json_encode(array('success'=>$success, 'message'=>$message)));
	}	

	elseif ($action == 'close'){
		$id=prepare4sql($_REQUEST['id']);

		$d_remove = dtime::date2db($_REQUEST['D_REMOVE']);

		$end_1 = str_replace(',', '.', $_REQUEST['END_1']);
		$end_2 = str_replace(',', '.', $_REQUEST['END_2']);
		$end_3 = str_replace(',', '.', $_REQUEST['END_3']);
		
		$end_1 = is_numeric($end_1) ? $end_1 : '';
		$end_2 = is_numeric($end_2) ? $end_2 : '';
		$end_3 = is_numeric($end_3) ? $end_3 : '';
		
		if( ! empty($id) && $d_remove){
			$q="UPDATE meters SET D_REMOVE='{$d_remove}', END_1='{$end_1}', END_2='{$end_2}', END_3='{$end_3}' WHERE ID_METER={$id}";
			mysql_query($q);
			$success='g';
			$message=__('Contatore chiuso correttamente');
		} else {
			$success='r';
			$message=__('Data non corretta');
		}

		exit( json_encode(array('success'=>$success, 'message'=>$message)));
	}

	/*
	 * delete
	 * */
	elseif ($action == 'delete'){
		$rs = new ordinamento(array());
		$cnt=0; $total=0; $message=''; $success='g';
		foreach( $rs->ck as $id_meter){
			
			$delete=true;
			$q="SELECT CODE_METER, ID_BUILDING FROM meters WHERE ID_METER={$id_meter} LIMIT 1";
			$row=rs::rec2arr($q);
			
			/*
			 * verifica se il contatore ha misurazioni annuali
			 */			
			$q="SELECT ID_MEASURE AS id FROM measures WHERE ID_METER={$id_meter} AND IS_DEL=0 LIMIT 1";
			$r=rs::rec2arr($q);
			if( ! empty($r['id'])){
				$success='r';
				$delete=false;
				$message .= str_replace(array('{id_meter}', '{code_meter}'), array($id_meter, $row['CODE_METER']), 'contatore con misurazioni annuali: {id_meter} - {code_meter}').BR;
	}

			/*
			 * verifica se il contatore ha misurazioni semestrali
			 */
			if($delete){
				$q="SELECT ID_MEASURE AS id FROM measures12 WHERE ID_METER={$id_meter} LIMIT 1";
				$r=rs::rec2arr($q);
				if( ! empty($r['id'])){
					$success='r';
					$delete=false;
					$message .= $message .= str_replace(array('{id_meter}', '{code_meter}'), array($id_meter, $row['CODE_METER']), 'contatore con misurazioni semestrali: {id_meter} - {code_meter}').BR;
					
					// "contatore con misurazioni semestrali: {$id_meter} - {$row['CODE_METER']}".BR;
				}
			}

			/*
			 * verifica se il contatore è in una formula
			 */
			if($delete){
				$q="SELECT ID_METER AS id, CODE_METER as code FROM meters WHERE meters.ID_BUILDING='{$row['ID_BUILDING']}' AND ( FORMULA LIKE '%{$row['CODE_METER']}%' OR A LIKE '%{$row['CODE_METER']}%' OR B LIKE '%{$row['CODE_METER']}%' ) LIMIT 1";
				$r=rs::rec2arr($q);
				if( ! empty($r['id'])){
					$success='r';
					$delete=false;
					$message .= str_replace(	array('{code_meter}', '{id_meter}', '{code}', '{id}'),
												array($row['CODE_METER'], $id_meter, $r['code'], $r['id']),
												'contatore {code_meter} (id {id_meter}) trovato nella formula del contatore {code} (id {id})').BR;
				}
			}
	
			/*
			 * verifica se il contatore è in una formula nze
			 */
			if($delete){
				$q="SELECT ID_METER AS id, meters.CODE_METER AS code FROM nzes LEFT JOIN meters USING(ID_METER) WHERE meters.ID_BUILDING='{$row['ID_BUILDING']}' AND ( A_NZE LIKE '%{$row['CODE_METER']}%' OR B_NZE LIKE '%{$row['CODE_METER']}%' ) LIMIT 1";
				$r=rs::rec2arr($q);
				if( ! empty($r['id'])){
					$success='r';
					$delete=false;
					$message .= 
					str_replace(array('{row[CODE_METER]}', '{id_meter}', '{r[code]}', '{r[id]}'), array($row['CODE_METER'], $id_meter, $r['code'], $r['id']), __('contatore {row[CODE_METER]} (id {id_meter}) trovati nella formula nZE del contatore {r[code]} (id {r[id]})') );
				}
			}

			if( $delete ){
				if(sole::delete_meter($id_meter)){
					$message .= str_replace('{id_meter}', $id_meter, __('contatore {id_meter} eliminato')).BR; // "contatore {$id_meter} eliminato".BR;
					$cnt++;
				}
			}
			$total++;
		}		
		$message .= str_replace(array('{cnt}', '{total}'), array($cnt, $total), __('ELIMINATI {cnt} contatori su {total}'));
		exit(json_encode(array('success'=>$success, 'message'=>$message)));
	}
	
	/*
	 * crud
	 * */
	elseif ($_REQUEST['action'] == 'crud'){
		
		/*
		 * insert / update
		 * */
		$ID_METER = array_key_exists('ID_METER', $_POST) ? $_POST['ID_METER'] : false;
		
		$message=''; $success='g';
		$rows = rs::inMatrix("SHOW COLUMNS FROM meters");
		$rs = new ordinamento(array());
		
		$fields=array();
		
		/*
		 * costruzione della query su tabella principale
		 * */
		$q="";
		foreach($rows as $row){
			$field = $fields[] = $row['Field'];

			if( array_key_exists($field, $_POST)){
				
				if( $field=='SCALA_MT' && empty($_POST['SCALA_MT'])){
					mysql_query("UPDATE meters SET SCALA_MT=NULL WHERE ID_METER={$ID_METER}");
					continue;
	}
	
				if( $row['Type']=='decimal'){
					$v=str_replace(',', '.', $_POST[$field]);
				}
				elseif($row['Type']=='date'){
					$v=dtime::my2db($_POST[$field]);
				} else {
					$v=$_POST[$field];
				}
				$q.="{$field}='".prepare4sql($v)."', ";
			}
		}
	
		if( ! empty($q)){
			$q=" SET ".substr($q, 0, -2);
	
			if($ID_METER){
				$q="UPDATE meters ".$q." WHERE ID_METER={$ID_METER}";
			} else {
				$q="INSERT INTO meters ".$q;
			}
		}
		
		/*
		 * query principale di inserimento / modifica
		 * */
		$Q=$q;
		
		/*
		 * inserimento multiplo per più appartamenti
		 * */
		$ids_flats=array(); $clone=false;
		if( array_key_exists('clone', $_POST)){
			$clone=true;
			$message .= __('Inserimento contatori diretti in modalità clonazione').BR;
			foreach($rs->ms['flats_meters'] as $id_flat){
				if( ! is_numeric($id_flat)){
					continue;
				}
				$ids_flats[]=$id_flat;
			}	
		} 
		elseif(array_key_exists('flats_meters_uni', $_POST)) {
			
			$message .= __('Contatore diretto').BR;
			
			$ids_flats[]=$_POST['flats_meters_uni'];
		} else {
			/* in questo caso viene inserito il record ma non aggiunto il singolo id flat */
			$message .= __('Contatore condiviso').BR;
			$ids_flats[]='multiselect';
		}
		
		foreach($ids_flats as $ID_FLAT){
			$delete=false;
			
			mysql_query($Q);
			
			$id= $ID_METER ? $ID_METER : mysql_insert_id();			
			if( ! empty($id)){
				
				/*
				 * gestione misurazioni anche mensile
				 * */
				if(array_key_exists('monthly', $_POST)){
					$q="UPDATE meters SET IS_12='1' WHERE ID_METER={$id}";
				} else {
					$q="UPDATE meters SET IS_12='0' WHERE ID_METER={$id}";
				}
				mysql_query($q);
				
				/*
				 * salva i multiselect (utilizzi, appartamenti)
				 * */
				foreach( $rs->ms as $relationtable => $aValues){
					if($relationtable=='nzes_nzeusages'){ /* manca l'ID_NZE, esecuzione dopo l'inserimento */
						continue;
					}
					
					$a_field = explode('_', $relationtable);
					$id_main = 'ID_'.strtoupper(substr($a_field[0], 0, -1));
					$id_ext = 'ID_'.strtoupper(substr($a_field[1], 0, -1));

					/*
					 * elimina i vecchi record
					 * */
					if( array_key_exists('relationtable', $aValues)){
						if($relationtable=='flats_meters'){ /* per la gestione automatica la tabella doveva chiamarsi meters_flats */
							$q="DELETE FROM {$aValues['relationtable']} WHERE $id_ext='$id'";
						} else {
							$q="DELETE FROM {$aValues['relationtable']} WHERE $id_main='$id'";
						}
						mysql_query($q);
					}
					
					foreach($aValues as $kk => $new_id){
						if( is_numeric($new_id )) {
							if($relationtable=='flats_meters'){ /* per la gestione automatica la tabella doveva chiamarsi meters_flats */
								$q="INSERT INTO $relationtable ($id_ext, $id_main) VALUES ('$id', '$new_id')";
							} else {
								$q="INSERT INTO $relationtable ($id_main, $id_ext) VALUES ('$id', '$new_id')";
							}
							mysql_query($q);
						}
					}
				}
				
				
				/*
				 * nei contatori diretti c'è un solo contatore per appartamento,
				 * qui gestiamo il caso di clonazione
				 * */
				
// 				$q="SELECT * FROM flats_meters WHERE ID_METER={$id}";
// 				print_r(rs::inMatrix($q));
				
				/*
				 * altre operazioni di allineamento
				 * */
				if(is_numeric($ID_FLAT)){
					$q="DELETE FROM flats_meters WHERE ID_METER={$id}";
					mysql_query($q);
					
					$q="INSERT INTO flats_meters SET ID_METER={$id}, ID_FLAT={$ID_FLAT}";
					mysql_query($q);
				}
				
				/*
				 * usa bilancio energetico
				 * */
				if(array_key_exists('is_bilancio', $_POST)){
					
					$ID_NZE=false;
					
					/*
					 * pulizia
					 * */
					$_POST['A_NZE']=trim($_POST['A_NZE']);
					$_POST['B_NZE']=trim($_POST['B_NZE']);
					
					if($ID_METER){
						$q="SELECT ID_NZE FROM nzes WHERE ID_METER={$id} LIMIT 1";
						
						$row=rs::rec2arr($q);
						
						if( ! empty( $row['ID_NZE'])){
							$ID_NZE=$row['ID_NZE'];
						}
					}
					
					if($ID_NZE){ /* update */
						if($_POST['ID_OUTPUT_NZE']==2){
							$q="UPDATE nzes SET ID_METER={$id}, ID_BUILDING={$_POST['ID_BUILDING']}, ID_OUTPUT='{$_POST['ID_OUTPUT_NZE']}', A_NZE='{$_POST['A_NZE']}', B_NZE='{$_POST['B_NZE']}', CORRECTION='{$_POST['CORRECTION']}' WHERE ID_NZE={$ID_NZE}";
						} else {
							$q="UPDATE nzes SET ID_METER={$id}, ID_BUILDING={$_POST['ID_BUILDING']}, ID_OUTPUT='{$_POST['ID_OUTPUT_NZE']}', CORRECTION='{$_POST['CORRECTION']}' WHERE ID_NZE={$ID_NZE}";
						}
						mysql_query($q);
						
					} else { /* insert */
						if($_POST['ID_OUTPUT_NZE']==2){
							$q="INSERT INTO nzes SET ID_METER={$id}, ID_BUILDING={$_POST['ID_BUILDING']}, ID_OUTPUT='{$_POST['ID_OUTPUT_NZE']}', A_NZE='{$_POST['A_NZE']}', B_NZE='{$_POST['B_NZE']}', CORRECTION='{$_POST['CORRECTION']}'";
						} else {
							$q="INSERT INTO nzes SET ID_METER={$id}, ID_BUILDING={$_POST['ID_BUILDING']}, ID_OUTPUT='{$_POST['ID_OUTPUT_NZE']}', CORRECTION='{$_POST['CORRECTION']}'";
						}
						//echo $q;
						mysql_query($q);
						$ID_NZE=mysql_insert_id();
					}

					/*
					 * aggiorna molti a molti nzes_nzeusages
					 * */
					mysql_query("DELETE FROM nzes_nzeusages WHERE ID_NZE={$ID_NZE}");
					foreach($rs->ms['nzes_nzeusages'] as $new_id){
						if( is_numeric($new_id )){
							$q="INSERT INTO nzes_nzeusages (ID_NZE, ID_NZEUSAGE) VALUES ({$ID_NZE}, {$new_id})";
							mysql_query($q);
						}
					}
					
				} else {
					
					/*
					 * il contatore non è di bilancio energetico 
					 * */
					sole::remove_nze_info($id);
				}
				
				/*
				 * attribuisce il nome contatore
				 * */
				if(array_key_exists('NAME_METER', $_POST)){

					$meter_name = sole::mk_namemeter($id, $_POST['NAME_METER']);
					mysql_query("UPDATE meters SET CODE_METER='$meter_name' WHERE ID_METER={$id}");
					
					/*
					 * verifica che non ci sia un misuratore con lo stesso codice,
					 * in caso finite le procedure provvede ad eliminarlo
					 * */
					$q="SELECT ID_METER AS id FROM meters WHERE ID_BUILDING={$_POST['ID_BUILDING']} AND CODE_METER LIKE '$meter_name' AND ID_METER <> {$id} LIMIT 1";
					$r=rs::rec2arr($q);
					if( ! empty($r['id'])){
						$success='y';
						$delete=$id;
					}
					if($ID_METER){
						$message .= __('Contatore modificato').': '.$meter_name.' '.BR;
					} else {
						$message .= __('Nuovo contatore inserito').': '.$meter_name.' '.BR;
					}
				}
				
				/*
				 * FOMULA sempre a null su contatori REAL
				 * */
				if(array_key_exists('ID_RF', $_POST) && ! $ID_METER){
					if($_POST['ID_RF'] == 1){
						mysql_query("UPDATE meters SET FORMULA=NULL WHERE ID_METER = {$id}");
					}
				}
				
				/*
				 * A / B sempre a null su contatori non A/B
				 * */
				if(array_key_exists('ID_OUTPUT', $_POST)){
					if($_POST['ID_OUTPUT'] != 2){
						mysql_query("UPDATE meters SET A=NULL, B=NULL WHERE ID_METER = {$id}");
					}
				}
	
				/*
				 * contatori di produzione
				 * */
				$delete_productions = false;
				
				if( $_POST['ID_METERTYPE'] == 1 ){ // energia elettrica
					
					if( $ID_METER && ! empty($_POST['SIZE'])){
						$SIZE = str_replace(',', '.', $_POST['SIZE']);
						if( ! is_numeric($SIZE)){
							$SIZE = 0;
						}
						$qRep = "REPLACE INTO meters_productions (ID_METER, SIZE) VALUES ('$id', '".prepare4sql($SIZE)."' )";
						mysql_query($qRep);
					}
					else if( array_key_exists('IS_DOUBLE', $_POST) && ! empty($_POST['SIZE']) ){
						$SIZE = str_replace(',', '.', $_POST['SIZE']);
						if( ! is_numeric($SIZE)){
							$SIZE = 0;
						}
						$qRep = "REPLACE INTO meters_productions (ID_METER, SIZE) VALUES ('$id', '".prepare4sql($SIZE)."' )";
						mysql_query($qRep);
					}
					elseif( array_key_exists('IS_DOUBLE', $_POST) ){
						$qRep = "REPLACE INTO meters_productions (ID_METER) VALUES ('$id')";
						mysql_query($qRep);
					} else {
						$delete_productions = true;
					}
				}
				
				elseif( $_POST['ID_METERTYPE'] == 5 ){ // acqua
					if (array_key_exists('altre_utenze', $_POST)){
						if( ! empty($_POST['SUM_DIVISIONAL'])){
							$qRep = "REPLACE INTO meters_productions (ID_METER, SUM_DIVISIONAL) VALUES ('$id', '".prepare4sql($_POST['SUM_DIVISIONAL'])."')";
							mysql_query($qRep);
						} else {
							$qRep = "REPLACE INTO meters_productions (ID_METER) VALUES ('$id')";
							mysql_query($qRep);
						}
					} else {
						$delete_productions = true;
					}
				}
				
				elseif( $_POST['ID_METERTYPE'] == 2 ){ // energia termica
					if( ! empty($_POST['THERMAL_TYPE']) && array_key_exists('is_thermal_production', $_POST) && $_POST['is_thermal_production']==1){
				
						$SIZE = str_replace(',', '.', $_POST['SIZE_THERMAL']);
						if(!is_numeric($SIZE)){
							$SIZE = 0;
						}
				
						$tty = $_POST['THERMAL_TYPE'];
						$aReplaceFields = array();
						if($tty == 1){ // solare termico
							$qRep = "REPLACE INTO meters_productions (ID_METER, SIZE, ACS, ETE, THERMAL_TYPE) VALUES ('$id', '".prepare4sql($SIZE)."', '".prepare4sql($_POST['ACS'])."', '".prepare4sql($_POST['ETE'])."', '$tty')";
							mysql_query($qRep);
						}
						elseif($tty == 2){ // generatore
							$qRep = "REPLACE INTO meters_productions (ID_METER, FUEL, THERMAL_TYPE) VALUES ('$id', '".prepare4sql($_POST['FUEL'])."', '$tty')";
							mysql_query($qRep);
						}
					} else {
						$delete_productions = true;
					}
				}
				if($delete_productions && ! $ID_METER){
					mysql_query("DELETE FROM meters_productions WHERE ID_METER='$id'");
}

				/*
				 * aggiorna la tabella di supporto
				 * */
				sole::allinea_utilizzi($id);

			}
			if( $delete ){
				if( $ID_METER){
					$message .= str_replace(array('{meter_name}'), array($meter_name), __('Problema duplicazione codice per il contatore {meter_name}')).BR;
				} else {
					/*
					 * è permessa l'eliminazione per i contatori appena inseriti
					 * */
					sole::delete_meter($delete);
					$success='r';
					$message .= str_replace(array('{meter_name}', '{delete}'), array($meter_name, $delete), __('Contatore {meter_name} (id {delete}) eliminato per duplicazione codice')).BR;
				}
			}
		}
		exit(json_encode(array('success'=>$success, 'message'=>$message)));
	}
	exit;
}

/*
 * layout
 * */

$q = "SELECT ID_METERTYPE as value,
METERTYPE_".LANG_DEF." as label
FROM metertypes
ORDER BY METERTYPE_".LANG_DEF." ASC
";
$rows=rs::inMatrix($q);
$hide_metertypes_list = json_encode($rows);

$q = "SELECT ID_METERPROPERTY as value,
METERPROPERTY as label
FROM meterpropertys
ORDER BY METERPROPERTY ASC
";
$rows=rs::inMatrix($q);
$hide_meterpropertys_list = json_encode($rows);

$q = "SELECT ID_OUTPUT as value,
OUTPUT as label
FROM outputs
ORDER BY ID_OUTPUT ASC
";
$rows=rs::inMatrix($q);
$hide_outputs_list = json_encode( $rows );

$q = "SELECT ID_NZEUSAGE as value,
TITLE_".LANG_DEF." as label
FROM nzeusages
ORDER BY ID_NZEUSAGE ASC
";
$rows=rs::inMatrix($q);
$hide_nzeusages_list = json_encode( $rows );

/*
 * select di scelta gruppi / gestori / edifici
 * */
$html['select_building'] = sole::select_fhb('');

$input = new io();
$input->type = 'select';
$input->addblank = true;
$input->aval = rs::id2arr("SELECT ID_UPLOADTYPE, UPLOADTYPE FROM uploadtypes ORDER BY UPLOADTYPE ASC"); 
$input->css = 'duecento';
$input->id = 'uploadtype';
$input->txtblank = S_CHOOSE.' '.strtolower(UPLOADTYPE);
$uploadtype = $input->set('uploadtype');

include_once HEAD_AR;
?>
<!-- valori hidden per select options -->
<span id="hide_metertypes_list" class="hide"><?=$hide_metertypes_list?></span>
<span id="hide_meterpropertys_list" class="hide"><?=$hide_meterpropertys_list?></span>
<span id="hide_outputs_list" class="hide"><?=$hide_outputs_list?></span>
<span id="hide_nzeusages_list" class="hide"><?=$hide_nzeusages_list?></span>

<div id="container_convalida">
	<div id="col_left" class="duecentocinquanta" style="min-height:10px;">
	
	<div id="box-col-left">
	<p><?=__('Scegli edificio')?>:</p>
	<?=$html['select_building']?>
		<div style="position:relative;">
		<input type="button" id="btn-show-meters" class="g-button g-button-yellow" value="<?=__('Mostra contatori')?>" style="width:200px;" />
			<div id="list-meters">
				<hr class="hr" />
				<input type="text" id="kw" name="kw" class="search" value="" />
				<form id="frm-delete">
					<ul id="meters-list">
					</ul>
				</form>
			</div>
		</div>
	</div>
	
	</div>
	<div id="col_right" style="margin-left:0">
	
	<div class="fixed_box">
		<div id="table-menu" >
			<div id="buttons">
			<a href="#" rel="save" title="<?=__('Salva')?>"><img src="images/icon-save.png" alt="<?=__('Salva')?>" /></a>
			<a href="#" rel="table_small" title="Small table"><img src="images/icon-table.png" alt="Open table small" /></a>
			<a href="#" rel="table_full" title="Full table"><img src="images/icon-table-orange.png" alt="Open table full" /></a>
			<a href="#" rel="delete" title="<?=__('Elimina')?>"><img src="images/icon-trash.gif" alt="<?=__('Elimina')?>" /></a>
			</div>
		</div>
	</div>
	
	<div class="content" id="ins_meter">
	<!-- form inserimento contatori -->
	<form id="frm-crud" action="meters.php?action=crud" method="post">
	
	<!-- colonna di sinistra -->
	<div class="left-col" id="meter_info"></div>
	
	<!-- colonna di destra -->
	<div class="right-col">
	
		<div class="box">
			<div id="type_box"></div>
			<div id="subtype_box">
				
				<!-- ACQUA -->
				<div id="watermeter" class="special_type">
				</div>
						
				<!-- ENERGIA TERMICA -->
				<div id="thermal" class="special_type">
				</div>
						
				<!-- ENERGIA ELETTRICA -->
				<div id="electric" class="special_type">
				</div>
				
				<!-- CAMPI COMUNI -->
				<div id="common_type">
				</div>
				
			</div>
		<div class="clear"></div>
		</div>
		<div id="rf_box" class="box relative"></div>
		<div id="ab_box" class="box relative"></div>
		<div id="nze_box" class="box relative" style="min-height:136px;"></div>
		<div id="ds_box" class="box"></div>
	</div>
	</form>
	
	<div class="clear"></div>
	</div>

	</div>
</div>
<div class="clear"></div>

<div id="dialog-confirm">
	<h3><?=__('Attenzione, sono state riscontrate alcune difformità')?>:</h3><br />
	<div id="dialog-confirm-message">
	</div>
</div>

<div id="dialog-close" class="hide">
	<form id="frm-close">
		<div class="campo_form">
			<label for="D_REMOVE"><?=__('Data chiusura')?>:</label>	
			<input type="text" style="width:218px;" value="" class="input_form text datepicker" name="D_REMOVE" id="D_REMOVE">
		</div>
		<div class="campo_form">
			<label for="END_1"><?=__('Valore finale')?> 1:</label>	
			<input type="text" style="width:218px;" value="" class="input_form text" name="END_1" id="END_1">
		</div>
		<div id="content_END_2" class="campo_form">
			<label for="END_2"><?=__('Valore finale')?> 2:</label>	
			<input type="text" style="width:218px;" value="" class="input_form text" name="END_2" id="END_2">
		</div>
		<div id="content_END_3" class="campo_form">
			<label for="END_3"><?=__('Valore finale')?> 3:</label>	
			<input type="text" style="width:218px;" value="" class="input_form text" name="END_3" id="END_3">
		</div>
</form>
</div>

<!-- dialog formule -->
<div id="dialog-formula" class="hide">
	<div class="campo_form" >
		<label for="input-formula"><?=__('Formula')?>:</label>	
		<input type="text" id="input-formula" name="input-formula" class="input_form text input-formula" style="width:860px" />
	</div>
	<div class="clear"></div>
	<div id="formula-meters-list" style="border-bottom: 3px solid #666666; height: 320px; overflow-y: auto;">
		<ul>
		</ul>
	</div>
</div>

<!-- dialog modifica contatore -->
<div id="dialog-meter" class="hide" style="overflow:hidden;">

</div>

<?php
$MYFILE->add_js_group('meters', array(
	JS_MAIN.'chosen/chosen.jquery.min.js',
	JS_MAIN.'jquery/jquery.livefilter.min.js',
	JS_JQUERY.'jquery.caret.1.02.js',
	JS_MAIN.'meters.js',
),
	10, 'on', 'footer');

include_once FOOTER_AR;
?>