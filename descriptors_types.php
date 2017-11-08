<?php
include_once 'init.php';

$user = new autentica($aA1);
$user -> login_standard();
include_once 'descriptors_types_conf.php';

$is_swf=0; $is_head_mce=0; $is_multibox=0; $is_js=1;

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
//$fil=is_null($crud) ? "" : $fil;
//$fil=$crud=="ins" ? " WHERE 1=0 " : $fil;
//$fil=$crud=="upd" ? " WHERE users.ID_USER='$id' " : $fil; 
$sublable=arr::_unset($lable,$aStripList);
$sublable=rs::label_frmt($sublable);
$qTotRec.=' '.$my_vars->where;

$cursor = new cursor($qTotRec, $scheda -> offset);
$cursor -> set_passo(5);
$cursor -> set_mode('full'); # simple normal full

//$player_curs=new player_curs($qTotRec,$scheda->offset);
$backUri=array_merge($my_vars->href);
$backUriHidden=array_merge($my_vars->hidden);
$my_vars->backuri = $backUri;
$my_vars->etichette_sort($sublable);
$backUriIds = $backUri;
//$player_curs->vars=$backUri;
//$player_curs->set();
$rs=rs::inMatrix($qrs=$qTotRec." ".$cursor -> limit.$fil);

//print $qrs;

$lista='';
$cnt=0;

foreach($rs as $rec){
	//$scheda->set_upld_dirs(FLD_MAIN.$rec['FOLDER_USR']);
	$backUriIds["id"] = $rec[$scheda->f_id];
	if(!empty($scheda->files)){ // GESTIONE MULTIPLA FILE
		$totali = $scheda->cnt_files($rec[$scheda->f_id]);
		$rec['ID_FILE'] = '';
		if(in_array('files', $scheda->files) && in_array('images', $scheda->files)) $rec['ID_FILE'] = io::a($scheda->file_f, $backUriIds, $totali['f'].' file - '.$totali['i'].' img', array('class' => 'puls_allegati_text'));
		elseif(in_array('images', $scheda->files)) $rec['ID_FILE'] = io::a($scheda->file_f, $backUriIds, $totali['i'].' img', array('class' => 'puls_allegati_text'));
	}
	if($scheda->img){ // IMMAGINE SINGOLA
		$scheda->set_upld_dirs($rec['FOLDER_USR']);
	
		$rec[$scheda->f_path] = is_file($scheda->img_main_web.$rec[$scheda->f_path]) ? io::a($scheda->file_c, array_merge($backUriIds,array('crud' => 'upd', '<img src="'.$scheda->img_main_web.$rec[$scheda->f_path].'" alt="image" width="80" />', array('title' => EDIT)))) : io::a($scheda->file_c, array_merge($backUriIds,array('crud' => 'upd')), '<img src="'.THU_JOLLY.'" alt="no image" width="80" />', array('title' => EDIT));
	}
	$color = $cnt%2==0 ? '' : ' class="contrast"';
	$href_mod=io::a($scheda->file_c, array_merge($backUriIds, array('crud' => 'upd')), EDIT, array('class' => 'href'));

# FORMATTAZIONE PARTICOLARE DI ALCUNI CAMPI ########################################
// if($rec['NOME_CAMPO'] == 'valore') $rec['NOME_CAMPO'] = 'testo, path immagine, ecc... che vuoi stampare'
# FINE CONFIGURAZIONE CAMPI LISTA ##################################################
	$celle='';
	foreach($sublable as $k=>$v){
		if(substr($k,0,2)=='D_') $rec[$k] = dtime::my2iso($rec[$k]); // DATA
		elseif(substr($k,0,3)=='IS_') $rec[$k] = !empty($rec[$k]) ? '<div class="icon_check_att"></div>' : '<div class="icon_check_dis"></div>'; // SI NO
		elseif(substr($k,0,3)=='ID_' || $k == $scheda->f_path) $rec[$k]; // IMMAGINE
		elseif($k == 'USER') $rec[$k];
	//	else $rec[$k] = strcut(trim(strip_tags($rec[$k])),'...',20);
		$celle.='<td>'.ucfirst($rec[$k]).'</td>';
	}
	$input['a'] = new io(); $input['a'] -> type = 'select'; $input['a'] -> addblank = false; $input['a'] -> aval = rs::id2arr("SELECT ID_DESCRIPTOR, DESCRIPTOR_".LANG_DEF." FROM descriptors WHERE ID_DESCRIPTORS_TYPE = '".$rec['ID_DESCRIPTORS_TYPE']."' ORDER BY RANK ASC, DESCRIPTOR_".LANG_DEF." ASC"); $input['a'] -> css = 'trecento'; $input['a'] -> set('a');
	ob_start();
	$input['a'] -> get();
	$prw_sel = ob_get_clean();
	$celle .= '<td>'.$prw_sel.'</td>';
	
	$lista.='<tr'.$color.' valign="top">'.$celle.'
	<td class="contrast" align="right">'.$href_mod.'
    </td></tr>';
	
$cnt++;
}
/*ob_start(); # html per CURSORE
?>
<div id="paginazione"><div id="content_paginazione"><?
for($i=0;$i<7;$i++){?>
	<?=$player_curs->player[$i]; }?>
<div class="clear"></div>
</div></div><? 
$player=ob_get_clean();*/

$href_new = ''; // io::a($scheda->file_c, array_merge($backUri,array('crud' => 'ins')), L_NUOVO, array('title' => 'Aggiungi nuovo record', 'class' => 'g-button'));
$my_vars->campi_ricerca();

$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);

include_once HEAD_AR;
?>
<form method="get" name="list" action="<?=$action?>">
<?=request::hidden($backUriHidden)?>
<table class="list">
<tr class="search"><th colspan="<?=$my_vars->colonne+2?>">&nbsp;</th></tr>
<?=$lista?>
<tr><th colspan="<?=$my_vars->colonne+2?>"><?=$cursor -> player?></th></tr>
<tr><th colspan="<?=$my_vars->colonne+2?>"><?=$cursor -> t_recs?> record | <?=$cursor -> t_curs?> pagine</th></tr>
</table>
</form>
<?php
include_once FOOTER_AR;
?>