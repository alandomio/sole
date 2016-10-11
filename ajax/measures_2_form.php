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
	
	// elimino le righe dei misuratori dismessi
	//if($meter['D_REMOVE'] != '0000-00-00'){
	//	continue;
	//}
	
	$model_selected = 'model_'.$cnt % 2;
	$cnt ++;
	$info_meter = '
	<div id="info'.$meter['ID_METER'].'">
	<div id="message'.$meter['ID_METER'].'" class="msg_ins_meter"></div>
	<ul class="simple">
	<li><strong>'.$meter['CODE_METER'].'</strong></li>
	<li><strong>'.$meter['MATRICULA_ID'].'</strong></li>
	<li>'.$meter['METERTYPE_'.LANG_DEF].'</li>
	<li>'.$meter['UNIT'].'</li>
	</ul>
	</div>
	';
	
	/////////////////////////////
	// inizio generazione tabella
	/////////////////////////////
	
	$nColonne = 7;
	
	$aFasce = array('F1'); $aF = array('F1', 'F2', 'F3');
	if($meter['ID_METERTYPE'] == 1 && $meter['HMETER'] > 1){ // elettrico 3 fasce
		$aFasce = array('F1', 'F2', 'F3');
	}
	
	 $td = ''; $tr = '';
	
	

	// $measures2 = misurazioni::get_last_measures_by_idmeter($meter['ID_METER']);
	
	// fisso il limite minimo dell'anno
	$YEAR = $year-2;
	if( $YEAR < SHOW_FROM_YEAR){
		$YEAR = SHOW_FROM_YEAR;
	}
	
	for( $YEAR; $YEAR<=$year; $YEAR++){

		$measures2 = misurazioni::get_measures_2_by_idmeter($meter['ID_METER'], $YEAR);
		
		// calcolo l'id_uploadtype
		$limit_uploadtype = 2;
		if( $YEAR == date('Y')){
			// se ho scelto l'anno corrente, verifico se mostrare un solo semestre o entrambi
			$month  = intval( date('m') );
			if($month < 7)
				$limit_uploadtype = 1;
			else
				$limit_uploadtype = 2;
		}
		
		for($id_uploadtype = 1; $id_uploadtype <= $limit_uploadtype; $id_uploadtype++){
			$key = $YEAR.$id_uploadtype;
			
			$td = '';
			$td .= mytag::in($YEAR, 'td', array('width' => '5%'));
			$td .= mytag::in($id_uploadtype, 'td', array('width' => '5%'));
			
			// campo data
			
			$write = ( $measures2[$key]['mode'] == 'write' || ! array_key_exists($key, $measures2) ) ? true : false;
			
			if( $write ){
			
				$input = new io();
				$input -> type = 'text';
				$input -> val = array_key_exists($key, $measures2) ? $measures2[$key]['D_MEASURE'] : '';
				$input -> css = 'datepicker ottanta edit_value';
				$td .= mytag::in($input -> set('D_MEASURE'), 'td', array('width' => '20%'));
		
				$itd = 4; // le colonne già stampate
				
				// campi F1, F2, F3
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
				
				// campi F1, F2, F3
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