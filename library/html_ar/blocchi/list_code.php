<?php
# V.0.1.10
//$is_swf=0; $is_head_mce=0; $is_multibox=0; $is_js=1;
if(array_key_exists('del',$_GET) || array_key_exists('del',$_POST)){
	$cnt = 0;
	foreach($my_vars->ck as $id_del){
		if(!empty($id_del) && $scheda->dele_main($id_del)) $cnt++;
	}
	if($cnt>0) $ack[] = "$cnt deleted";
}

$my_vars->where('');
$aCurs=array("icursor"=>$icursor);
$aFromp=array();

$field_list=rs::rec2arr("SELECT * FROM ".$scheda->table." LIMIT 0,1");
$select='';

foreach($field_list as $k => $v){
	$select.= $scheda->table.".$k, ";
}

$select = substr($select, 0, strlen($select)-2);

$scheda->query_list();

$qTotRec = $scheda->query_list;

$aTbl = array($scheda->table, $scheda->file_table);

$lable=rs::sql2lbl($qTotRec);

$fil="";
$fil=is_null($crud) ? "" : $fil;
$fil=$crud=="ins" ? " WHERE 1=0 " : $fil;
//$fil=$crud=="upd" ? " WHERE users.ID_USER='$id_rec' " : $fil; 
$sublable=arr::_unset($lable,$aStripList);
$sublable=rs::label_frmt($sublable);
$qTotRec.=' '.$my_vars->where;
$player_curs=new player_curs($qTotRec,$scheda->offset);
$backUri=array_merge($player_curs->aCurs,$my_vars->href);
$backUriHidden=array_merge($player_curs->aCurs,$my_vars->hidden);
$my_vars->backuri = $backUri;
$my_vars->etichette_sort($sublable);
$backUriIds = $backUri;
$player_curs->vars=$backUri;
$player_curs->set();
$rs=rs::inMatrix($qrs=$qTotRec." ".$player_curs->limit_sql.$fil);
//print $qrs;
$lista='';
$cnt=0;

?>