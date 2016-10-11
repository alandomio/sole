<?php
include_once '../init.php';

$d_measure = '';
if(array_key_exists('d_measure', $_GET)){
	$d_measure = dtime::my2db( $_GET['d_measure']);
}

// METERS LIST
$rms = sole::get_real_by_bld($id, $d_measure);
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
	
	// TABELLA INTERNA
	$table = misurazioni::html_tabella_interna($meter, $upload_type, $year);

	$new_line = str_replace('<!-- TABLE_MEASURES -->', $table, $$model_selected);
	$new_line = str_replace('<!-- INFO_METER -->', $info_meter, $new_line);
	
	$TR .= $new_line;
}

if(empty($TR)){
	$msg = new myerr();
	$msg -> add_err(ERR_NO_METERS);
	$TR = $msg -> print_msg(false);
}
print request::hidden($backUri)
?>
<table class="list personal">
<?=$TR?>
</table>