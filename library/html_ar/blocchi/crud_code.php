<?php
# V.0.1.10
$is_swf=0; $is_head_mce=1; $is_multibox=0; $is_js=0;

$fil="";
$fil=is_null($crud) ? "" : $fil;
$fil=$crud=="ins" ? " WHERE 1=0 " : $fil;
$fil=$crud=="upd" ? " WHERE ".$scheda->table.".".$scheda->f_id."='$id_rec' " : $fil; 
//$title_pag.=$crud=="ins" ? "Inserimento ".$scheda->etichetta : "Modifica ".$scheda->etichetta;
$aCurs=array("icursor"=>$icursor);
$aFromp=array();
$aFiltro=array();
$backUri=array_merge($aCurs,$my_vars->href);
$backUri['id_rec'] = $id_rec;

$scheda->query_list();
$qTotRec = $scheda->query_list;

//$qTotRec="SELECT * FROM ".$scheda->table;
if($scheda->ext_table != NULL)
{
	$qTotRec.="";
}

$atabelle=$scheda->atable;

$lable=rs::sql2lbl($qTotRec);
$sublable=$lable;

$rec=rs::rec2arr($qrs=$qTotRec." ".$fil);
//print $qrs;
$db=new dbio();
list($db->a_name,$db->a_val,$db->a_type,$db->a_maxl,$db->a_default,$db->a_not_null,$db->a_lable,$db->a_dec,$db->a_fkey,
$db->a_aval,$db->a_addblank,$db->a_comment,$db->a_sql_type)=rs::showfull3($atabelle,$rec,$lable,$add=array(),$self_join=array());
$db->dbset();

if(array_key_exists('nw_img_dele',$_GET)){
	$scheda->dele_img($id_rec);
}

# C.R.U.D.
if(array_key_exists("subDo",$_POST) || array_key_exists("subBack",$_POST)){
	$ERR_CRUD=err::crud($rec);
	$_POST=request::adjustPost($_POST);
	$rec=request::post2arr($sublable);
	$rec=arr::magic_quote($rec);
	$db->a_val=array_merge($db->a_val,$rec);
	$db->dbset();
}
?>