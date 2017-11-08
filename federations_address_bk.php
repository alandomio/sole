<?php
# V.0.1.8
include_once 'init.php';
$user = new autentica($aA2);
$user -> login_standard();
include_once 'federations_conf.php';

if(!isset($configura)) $configura = new configura('descriptors');

$fil = "";
$fil = is_null($crud) ? "" : $fil;
$fil = $crud == "ins" ? " WHERE 1=0 " : $fil;
$fil = $crud == "upd" ? " WHERE ".$scheda->table.".".$scheda->f_id."='$id' " : $fil; 

$aCurs=array("icursor"=>$icursor);
$aFromp=array();
$aFiltro=array();
$backUri=array_merge($aCurs, $my_vars -> href, $my_vars -> hidden);
$backUri['id'] = $id;
$qTotRec="SELECT * FROM ".$scheda->table;

$atabelle=$scheda->atable;
$lable=rs::sql2lbl($qTotRec);
//$sublable=$lable;

$sublable = arr::arr2constant($aShowCrud, true);

$rec=rs::rec2arr($qrs=$qTotRec." ".$fil);

$db=new dbio();
$aRet = rs::showfull($atabelle, $rec,$lable,$add=array(),$self_join=array(), $my_vars);
list($db->a_name, $db->a_val,$db->a_type,$db->a_maxl,$db->a_default,$db->a_not_null,$db->a_lable,$db->a_dec,$db->a_fkey,$db->a_aval,$db->a_addblank,$db->a_comment,$db->a_sql_type, $db->a_js, $db->a_disabled, $aFval) = $aRet;
$db->dbset();

if(array_key_exists('nw_img_dele',$_GET)){
	$scheda->dele_img($id);
}

# C.R.U.D.
$ERR_CRUD = err::crud($rec);
$_POST = request::adjustPost($_POST);
$rec = request::post2arr($sublable);
$rec = arr::magic_quote($rec);
$rec = arr::_trim($rec,array('DESCRIP'));
$db -> a_val = array_merge($db->a_val,$rec);
$db -> dbset();
include_once AJAX.'federations_form.php';
?>