<?php
# PAGINA CONTATORI
$scheda = new nw('users');
$crud ='ins';
//$scheda -> ext_table();

// CONFIGURAZIONE FILTRI
$my_vars = new ordinamento(array());

$my_vars->sort_default($scheda->f_id);
$my_vars->tabella = $scheda->table;

// QUERY 
$fil = "";
$fil = is_null($crud) ? "" : $fil;
$fil = $crud == "ins" ? " WHERE 1=0 " : $fil;
$fil = $crud == "upd" ? " WHERE ".$scheda->table.".".$scheda->f_id."='$id' " : $fil; 

$aFromp=array();
$aFiltro=array();
$backUri=array_merge($my_vars -> href, $my_vars -> hidden);
$backUri['id'] = $id;
$qTotRec="SELECT * FROM ".$scheda->table;

$atabelle=$scheda->atable;
$lable=rs::sql2lbl($qTotRec);
$rec=rs::rec2arr($qrs=$qTotRec." ".$fil);

$db=new dbio();
$aRet = rs::showfull($atabelle, $rec,$lable,$add=array(),$self_join=array(), $my_vars);

list($db->a_name, $db->a_val,$db->a_type,$db->a_maxl,$db->a_default,$db->a_not_null,$db->a_lable,$db->a_dec,$db->a_fkey,$db->a_aval,$db->a_addblank,$db->a_comment,$db->a_sql_type, $db->a_js, $db->a_disabled, $aFval) = $aRet;

$_POST = request::adjustPost($_POST);
$rec = arr::magic_quote($rec);
$rec = arr::_trim($rec,array('DESCRIP'));

$db -> a_val = array_merge($db->a_val,$rec);
$db -> dbset();

$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);
$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);

$qHC = "SELECT ID_HCOMPANY FROM buildings WHERE ID_BUILDING = '".$_GET['id_bld']."'";
$rHC = rs::rec2arr($qHC);

$db -> NAME -> id = 'NAME';
$db -> SURNAME -> id = 'SURNAME';

$db -> ID_GRUPPI -> val = 5;
$db -> IS_PRIVACY -> val = 1;
$db -> IS_ABIL -> val = 1;
$db -> ID_HCOMPANY -> val = $rHC['ID_HCOMPANY'];

$db -> ID_GRUPPI -> type = 'hidden';
$db -> IS_PRIVACY -> type = 'hidden';
$db -> IS_ABIL -> type = 'hidden';
$db -> ID_HCOMPANY -> type = 'hidden';

$hidd = $db -> ID_GRUPPI -> set();
$hidd .= $db -> IS_PRIVACY -> set();
$hidd .= $db -> IS_ABIL -> set();
$hidd .= $db -> ID_HCOMPANY -> set();

?>
<?php
print request::hidden($backUri)
?>
<table class="list personal">
<?=$hidd?>
<tr><td valign="top"><div class="lbl"><?=USER?></div></td><td valign="top"><? $db -> USER -> get(); ?></td></tr>
<tr><td valign="top"><div class="lbl"><?=NAME?></div></td><td valign="top"><? $db -> NAME -> get(); ?></td></tr>
<tr><td valign="top"><div class="lbl"><?=SURNAME?></div></td><td valign="top"><? $db -> SURNAME -> get(); ?></td></tr>
<tr><td valign="top"><div class="lbl"><?=IS_UPLOADER?></div></td><td valign="top"><? $db -> IS_UPLOADER -> get(); ?></td></tr>
</table>  

