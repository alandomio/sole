<?php
# V.0.1.8
include_once 'init.php';
$user = new autentica($aA1);
$user -> login_standard();

# VARIABILI DI DEFAULT
list($id, $idi) = request::get(array('id' => NULL, 'idi' => NULL));

if(array_key_exists('exe', $_POST)){
	$db = mydb::post2db(null, 'measures');
	echo json_encode(array('success' => $db['result'], 'message' => $db['message']));
}



# PAGINA NUOVAMISURAZIONE

$sk_newms = new nw('measures');
$crud = empty($idi) ? 'ins' : 'upd';
$qm = "SELECT * FROM meters WHERE ID_METER = '$id'";
$rm = rs::rec2arr($qm);

$sk_newms -> ext_table(array('uploadtypes'));
$aShowCrud = ($sk_newms -> aFields);

// CONFIGURAZIONE FILTRI
$vars_newms = new ordinamento(array('dm' => 'D_MEASURE', 'ic' => 'IS_CONFIRMED_MS', 'ym' => 'ANNO_MS', 'ut' => 'UPLOADTYPE'));
$vars_newms -> campi_force['text']['HCOMPANY'] = 'flats';
$vars_newms -> campi_force['text']['USER'] = 'flats';

$vars_newms->sort_default($sk_newms->f_id);
$vars_newms->tabella = $sk_newms->table;

# PRE CARICO I DATI DEL RECORD CORRENTE IN MODO DA USARLI DOVE SERVE
$rec_scheda = rs::rec2arr("SELECT * FROM ".$sk_newms->table." WHERE ".$sk_newms->f_id."='$idi' LIMIT 0,1");

$aOrd = array();

# ETICHETTA DA MOSTRARE COME EVIDENZA DEL RECORD
$fil = "";
$fil = is_null($crud) ? "" : $fil;
$fil = $crud == "ins" ? " WHERE 1=0 " : $fil;
$fil = $crud == "upd" ? " WHERE ".$sk_newms->table.".".$sk_newms->f_id."='$idi' " : $fil; 

$aFromp=array();
$aFiltro=array();
$backUri=array_merge($vars_newms -> href, $vars_newms -> hidden);
$backUri['idi'] = $idi;
$backUri['id'] = $id;
$qTotRec="SELECT * FROM ".$sk_newms->table;

$atabelle=$sk_newms->atable;
$lable=rs::sql2lbl($qTotRec);

$sublable = arr::arr2constant($aShowCrud, true);
$rec=rs::rec2arr($qrs=$qTotRec." ".$fil);

$db_ms=new dbio();
$aRet = rs::showfull($atabelle, $rec, $lable, $add=array(),$self_join=array('ID_METER'), $vars_newms);
list($db_ms->a_name, $db_ms->a_val,$db_ms->a_type,$db_ms->a_maxl,$db_ms->a_default,$db_ms->a_not_null,$db_ms->a_lable,$db_ms->a_dec,$db_ms->a_fkey,$db_ms->a_aval,$db_ms->a_addblank,$db_ms->a_comment,$db_ms->a_sql_type, $db_ms->a_js, $db_ms->a_disabled, $aFval) = $aRet;

$db_ms->dbset();

$db_ms -> D_MEASURE -> type = 'text';
$db_ms -> D_MEASURE -> css = 'datepicker';
$db_ms -> ID_METER -> type = 'hidden';
$db_ms -> ID_METER -> val = $id;

ob_start();
?>
<div id="tabs-2" class="tabpage">
<button type="button" class="ui-button ui-state-default ui-corner-all ui-button-text-only"  value="Salva"><span class="ui-button-text">Salva</span></button>
<form id="add_ms" method="post" >
<?php $db_ms -> ID_METER -> get(); ?>
<?=request::hidden($backUri)?>
<table class="list personal">
<tr><td valign="top" width="200"><div class="lbl"><?=D_MEASURE?></div><? $db_ms -> D_MEASURE -> get(); ?></td></tr>
<tr><td valign="top"><div class="lbl"><?=F1?></div><? $db_ms -> F1 -> get(); ?></td></tr>
<tr><td valign="top"><div class="hourly_m"><div class="lbl"><?=F2?></div><? $db_ms -> F2 -> get(); ?></div></td></tr>
<tr><td valign="top"><div class="hourly_m"><div class="lbl"><?=F3?></div><? $db_ms -> F3 -> get(); ?></div></td></tr>
<tr class="double_m"><td valign="top"><div class=""><div class="lbl "><?=O1?></div><? $db_ms -> O1 -> get(); ?></div></td></tr>
<tr class="double_m"><td valign="top" class="hourly_m"><div class=""><div class="lbl"><?=O2?></div><? $db_ms -> O2 -> get(); ?></div></td></tr>
<tr class="double_m"><td valign="top" class="hourly_m"><div class=""><div class="lbl"><?=O3?></div><? $db_ms -> O3 -> get(); ?></div></td></tr>
</table>  
<input type="submit" name="exe" value="salva" />
</form>
</div> 
<?php
$html['nuovamisurazione'] = ob_get_clean();






include_once HEAD_AR;
//print $sub_menu;

print $html['nuovamisurazione'];


include_once FOOTER_AR;
?>