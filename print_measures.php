<?php
include_once 'init.php';
$user = new autentica($aA5);
$user -> login_standard();

//http://sole.infonair.com/ajax/json.php?action=form_insert_measures&id=7&upload_type=1&year=2009

$id_building = $_REQUEST['id'];
$upload_type = $_REQUEST['upload_type'];
$year = $_REQUEST['year'];

/* $id_building = 7;
$upload_type = 1;
$year = 2009; */
$name_building = '';
if(!empty($id_building)){

	$rBuilding = sole::get_building_info($id_building);
	$name_building = $rBuilding['CODE_BLD'];
	unset($rBuilding);

}

# METERS LIST
$rms = sole::get_real_by_bld($id_building);
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
	
	### TABELLA INTERNA
	$table = misurazioni::print_measures_table($meter, $upload_type, $year);

	### / TABELLA INTERNA
	$new_line = str_replace('<!-- TABLE_MEASURES -->', $table, $$model_selected);
	$new_line = str_replace('<!-- INFO_METER -->', $info_meter, $new_line);
	
	$TR .= $new_line;
}

if(empty($TR)){
	$msg = new myerr();
	$msg -> add_err(ERR_NO_METERS);
	$TR = $msg -> print_msg(false);
}
// print request::hidden($backUri)

$title_page = STAMPA_MISURAZIONI.' '.strtolower(ID_BUILDING).' '.$name_building;

include_once HTML_AR.'head_print.php';
?>
<table class="list personal">
<?=$TR?>
</table>
<?
include_once FOOTER_AR;
?>