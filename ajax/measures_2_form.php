<?php
include_once '../init.php';

/*
inizializzati in json.php
$id_building
$year
$id_uploadtype
*/

// i mesi da visualizzare per l'anno scelto
$d_measure = false;
if(array_key_exists('d_measure', $_GET)){
	$d_measure = dtime::my2db( $_GET['d_measure']);
}

// METERS LIST
$rms = sole::get_real_2_by_id_bld( $id_building, 'mostra_prima_i_condivisi', $d_measure );

/*
 * tipo monitoaggio
 * */
$q="SELECT MONITORTYPE as value FROM buildings WHERE ID_BUILDING={$id_building} LIMIT 1";
$monitortype=rs::rec2arr($q);

$tr = '';
$TR = '';

$model_0 = '
<tr>
	<td valign="top" width="120" class="bright"><!-- INFO_METER --></td>
	<td valign="bottom" style="padding:0;"><!-- TABLE_MEASURES --></td>
</tr>';

$model_1 = '
<tr class="light-grey">
	<td valign="top" width="120" class="bright"><!-- INFO_METER --></td>
	<td valign="bottom" style="padding:0;"><!-- TABLE_MEASURES --></td>
</tr>';

$cnt = 0;
// riga principale contatore
foreach($rms as $k => $meter){
	$dstart='';
	$dremove='';
	if( ! empty($meter['D_REMOVE']) && $meter['D_REMOVE']!='0000-00-00'){
		$dremove=dtime::my2iso($meter['D_REMOVE']);
		$dremove='<li style="line-height:20px"><img src="images/icon-meter-remove.png" alt="'.__('Misuratore dismesso il').'" title="'.__('Misuratore dismesso il').'" style="vertical-align:middle;"> <span style="vertical-align:middle;">'.$dremove.'</span></li>';
	}
	
	if( ! empty($meter['D_FIRSTVALUE']) && $meter['D_FIRSTVALUE']!='0000-00-00'){
		$dstart=dtime::my2iso($meter['D_FIRSTVALUE']);
		$dstart='<li style="line-height:20px"><img src="images/icon-meter-add.png" alt="'.__('Misuratore aggiunto il').'" title="'.__('Misuratore aggiunto il').'" style="vertical-align:middle;"> <span style="vertical-align:middle;">'.$dstart.'</span></li>';
	}
	
	$model_selected = 'model_'.$cnt % 2;
	$cnt ++;
	$info_meter = '
	<div id="info'.$meter['ID_METER'].'">
	<div id="message'.$meter['ID_METER'].'" class="msg_ins_meter"></div>
	<ul class="simple">
	<li><strong>'.$meter['CODE_METER'].'</strong></li>
		<li>'.$meter['MATRICULA_ID'].'</li>
		<li>'.$meter['METERTYPE_'.LANG_DEF].' '.$meter['UNIT'].'</li>
		'.$dstart.'
		'.$dremove.'
	</ul>
	</div>
	';
	
	/*
	 * inizio generazione tabella
	 * */
	$nColonne = 7;
	
	$aFasce = array('F1'); $aF = array('F1', 'F2', 'F3');
	if($meter['ID_METERTYPE'] == 1 && $meter['HMETER'] > 1){ // elettrico 3 fasce
		$aFasce = array('F1', 'F2', 'F3');
	}
	
	 $td = ''; $tr = '';
	
	// $measures2 = misurazioni::get_last_measures_by_idmeter($meter['ID_METER']);
	// fissa il limite minimo dell'anno
	
	$YEAR = $year-2;
	if( $YEAR < SHOW_FROM_YEAR){
		$YEAR = SHOW_FROM_YEAR;
	}
	
	for( $YEAR; $YEAR<=$year; $YEAR++){

		/*
		 * in caso di misurazioni fuori dal periodo di validità del contatore,
		 * non viene visualizzato il controllo
		 * */
		if( ! empty($meter['D_REMOVE']) && $meter['D_REMOVE']!='0000-00-00'){
			$y=substr($meter['D_REMOVE'], 0, 4);
			if($y<$YEAR){
				// echo $y.' '.$YEAR.BR.$meter['CODE_METER'].' '.$meter['ID_METER'].BR;
				continue;
			}
			
		}
		if( ! empty($meter['D_FIRSTVALUE']) && $meter['D_FIRSTVALUE']!='0000-00-00'){
			$y=substr($meter['D_FIRSTVALUE'], 0, 4);
			if($y>$YEAR){
				// echo $y.' '.$YEAR.BR.$meter['CODE_METER'].' '.$meter['ID_METER'].BR;
				continue;
			}
		}
		

		$measures2 = misurazioni::get_measures_2_by_idmeter($meter['ID_METER'], $YEAR);
		
		// calcola l'id_uploadtype
		$limit_uploadtype = 2;
		if( $YEAR == date('Y')){
			// se ho scelto l'anno corrente, verifico se mostrare un solo semestre o entrambi
			$month  = intval( date('m') );
			if($month < 7)
				$limit_uploadtype = 1;
			else
				$limit_uploadtype = 2;
		}
		
		/*
		 * controlla se l'edificio è configurato per una sola misurazione annuale
		 * */
		if($monitortype['value']==12){
			$limit_uploadtype = 1;
		}
		
		for($id_uploadtype = 1; $id_uploadtype <= $limit_uploadtype; $id_uploadtype++){
			$key = $YEAR.$id_uploadtype;
			
			$td = '';
			$td .= mytag::in($YEAR, 'td', array('width' => '5%'));
			$td .= mytag::in($id_uploadtype, 'td', array('width' => '5%'));
			
			/*
			 * campo data
			 * */
			if( ! array_key_exists($key, $measures2)){
				$write = true;
			} else {
				$write = ($measures2[$key]['mode'] == 'write') ? true : false;
			}
			
			if( $write ){
			
				$input = new io();
				$input -> type = 'text';
				$input -> val = array_key_exists($key, $measures2) ? $measures2[$key]['D_MEASURE'] : '';
				$input -> css = 'datepicker ottanta edit_value';
				$td .= mytag::in($input -> set('D_MEASURE'), 'td', array('width' => '20%'));
		
				$itd = 4; // le colonne già stampate
				
				foreach($aFasce as $kk => $field){
					$input = new io();
					$input -> type = 'text';
					$input -> val = ( array_key_exists($key, $measures2) && $measures2[$key][$field] > 0  ) ? $measures2[$key][$field] : '';
					$input -> css = 'ottanta edit_value';
					
					$td .= mytag::in($input -> set($field), 'td', array('width' => '20%'));
					$itd ++;
				}
			} else {
				$value = array_key_exists($key, $measures2) ? dtime::db2my($measures2[$key]['D_MEASURE']) : '';
				$td .= mytag::in($value, 'td', array('width' => '20%'));
				$itd = 4; // le colonne già stampate
				
				/*
				 * campi F1, F2, F3
				 * */
				foreach($aFasce as $kk => $field){
					$value = array_key_exists($key, $measures2) ? $measures2[$key][$field] : '';
				
					$td .= mytag::in($value, 'td', array('width' => '20%'));
					$itd ++;
				}
			}

			while($itd <= $nColonne){ // celle riempitive
			
				if($itd == $nColonne && $write){
					$td_riempitivo = '<input type="checkbox" value="1" class="dele_measures" />';
					$percent = '10%';
				} else {
					$td_riempitivo = '&nbsp;';
					$percent = '20%';				
				}
				
				$td .= mytag::in($td_riempitivo, 'td', array('width' => $percent, 'align' => 'right'));
				$itd ++;
			}
			$tr .= mytag::in($td, 'tr', array('class' => $key));
		}
	}
	
	$table = mytag::in($tr, 'table', array('class' => 'neutra', 'id' => $meter['ID_METER']));
	
	$new_line = str_replace('<!-- TABLE_MEASURES -->', $table, $$model_selected);
	$new_line = str_replace('<!-- INFO_METER -->', $info_meter, $new_line);
	
	$TR .= $new_line;
	
	/*
	 <table id="1456" (id_meter)>
	<tr class="20114" (anno mese)><td>...</td></tr>
	</table>
	*/
	
	/////////////////////////////
	// fine generazione tabella
	/////////////////////////////
}

if(empty($TR)){
	$msg = new myerr();
	$msg -> add_err(ERR_NO_METERS);
	$TR = '<br />'.$msg -> print_msg(false);
}
print request::hidden($backUri)
?>
<table class="list personal" style="width:800px;">
<?=$TR?>
</table>