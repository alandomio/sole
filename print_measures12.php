<?php
include_once 'init.php';
$user = new autentica($aA5);
$user -> login_standard();

$id_building = $_REQUEST['id'];
$year = $_REQUEST['year'];

if(!empty($id_building)){
	$rBuilding = sole::get_building_info($id_building);
	$name_building = $rBuilding['CODE_BLD'];
	unset($rBuilding);
}

// i mesi da visualizzare per l'anno scelto
$limit_uploadtype = 12;
if( $year == date('Y')){
	$limit_uploadtype = intval( date('m') );
}

// METERS LIST
$rms = sole::get_real_12_by_id_bld( $id_building, 'mostra_prima_i_condivisi' );
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

	$nColonne = 6;

	$aFasce = array('F1'); $aF = array('F1', 'F2', 'F3');
	if($meter['ID_METERTYPE'] == 1 && $meter['HMETER'] > 1){ // elettrico 3 fasce
		$aFasce = array('F1', 'F2', 'F3');
	}

	$td = ''; $tr = '';

	// le misurazioni mensili inserite
	$measures12 = misurazioni::get_measures_12_by_idmeter($meter['ID_METER'], $year);

	for($id_uploadtype = 1; $id_uploadtype <= $limit_uploadtype; $id_uploadtype++){
		$key = $year.$id_uploadtype;

		$td = '';
		$td .= mytag::in($year, 'td', array('width' => '16.6%'));
		$td .= mytag::in($id_uploadtype, 'td', array('width' => '16.6%'));
		
		$d_measure= array_key_exists($key, $measures12) ? dtime::my2iso($measures12[$key]['D_MEASURE']) : '';
		$td .= mytag::in( $d_measure, 'td', array('width' => '16.6%'));

		$itd = 4; // le colonne già stampate
		foreach($aFasce as $kk => $field){
			
			$fn = ( array_key_exists($key, $measures12) && $measures12[$key][$field]>0 ) ? number_format ( $measures12[$key][$field], 3, ',' , ' ' ) : '';
			// $fn = array_key_exists($key, $measures12) ? $measures12[$key][$field] : '';
			$td .= mytag::in( $fn, 'td', array('width' => '16.6%'));
			$itd ++;
		}

		while($itd <= $nColonne){ // celle riempitive
			$td .= mytag::in( '&nbsp;', 'td', array('width' => '16.6%'));
			$itd ++;
		}
		$tr .= mytag::in($td, 'tr', array('class' => $key));
	}

	$table = mytag::in($tr, 'table', array('class' => 'neutra', 'id' => $meter['ID_METER']));

	$new_line = str_replace('<!-- TABLE_MEASURES -->', $table, $$model_selected);
	$new_line = str_replace('<!-- INFO_METER -->', $info_meter, $new_line);

	$TR .= $new_line;

	/////////////////////////////
	// fine generazione tabella
	/////////////////////////////
}

if(empty($TR)){
	$msg = new myerr();
	$msg -> add_err(ERR_NO_METERS);
	$TR = '<br />'.$msg -> print_msg(false);
}

$title_page = STAMPA_MISURAZIONI_MENSILI.' '.strtolower(ID_BUILDING).' '.$name_building;

include_once HTML_AR.'head_print.php';
?>
<table class="list personal">
<tr><th width="120"></th><th>

<table class="neutra"><tr>
<th width="16.6%"><?=ANNO?></th>
<th width="16.6%"><?=MONTH?></th>
<th width="16.6%"><?=DATE?></th>
<th width="16.6%">F1</th>
<th width="16.6%">F2</th>
<th width="16.6%">F3</th>
</tr></table>

</th></tr>
</table>
<table class="list personal">
<?=$TR?>
</table>
<?
include_once FOOTER_AR;
?>