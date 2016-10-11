<?php
# V.0.1.10
include_once 'init.php';
$user = new autentica($aA3);
$user -> login_standard();

# V.0.1.10
$scheda = new nw('flats');
$scheda->offset = 100;
$scheda->swf_limit = 5;

$qm = "SELECT * FROM meters WHERE ID_METER = '$id'";
$rm = rs::rec2arr($qm);

# CONFIGURAZIONE MOLTI A MOLTI
$mmv = array(
	'tbmm' => 'flats_meters',
	'id2d' => 'ID_METER',
	'f_id' => $scheda -> f_id
);

$tbl_main = stringa::id2table($mmv['id2d']);
$list_file = $tbl_main.'.php';

# VARIABILI DI DEFAULT
list($icursor, $tabella, $crud, $id)=request::get(array("icursor" => 0, "tabella" => $scheda -> table, "crud" => NULL, "id" => NULL));

$q_ext = "SELECT * FROM ".$scheda -> table." WHERE ".$scheda -> f_id." = '$id'";
$rec_ext = rs::rec2arr($q_ext);

# CONFIGURAZIONE NW
$scheda->ext_table(array('hcompanys', 'users'));

# VISUALIZZAZIONE LISTA
$strcut = 30;

$aStripC = array();
$aShowList = array('FLAT', 'NETAREA', 'HCOMPANY');
$aShowCrud = array_diff($scheda -> aFields, $aStripC);

# CONFIGURAZIONE FILTRI
$my_vars = new ordinamento(array('ft' => 'FLAT', 'na' => 'NETAREA', 'hc' => 'HCOMPANY'));
//$my_vars -> set_filtro(array('vaiem' => '1'));
//$my_vars->campi_force['text']['EMAIL'] = 'flats';  //FORZO IL FORMATO DEL CAMPO (FIELDNAME_EM) A SELECT (select) PASSANDO IL NOME TABELLA (tablename) RELATIVO

$my_vars -> campi_force['text']['FLAT'] = 'flats';
//$my_vars -> campi_force['text']['USER'] = 'flats';

$my_vars->sort_default($scheda->f_id);
$my_vars->tabella = $scheda->table;

# STILE CAMPI RICERCA
//$my_vars->css['CLIENTS_TYPE'] = 'centocinquanta';

# MENU, TITLE, ETICHETTE ECC...
//$scheda->action_type = 'read';
$rec_scheda = rs::rec2arr("SELECT * FROM ".$scheda->table." WHERE ".$scheda->f_id."='$id'"); 

function update_ext_meters($aa, $ck, $id, $mmv){ # INSERISCE IN BASE AI CHECKBOX SCELTI
	$added = 0;	$removed = 0; $check = array();

	$q="SELECT * FROM ".$mmv['tbmm']." WHERE ".$mmv['id2d']." = '$id'";
	$rec = rs::inMatrix($q);
	foreach($rec as $k => $v){ # SEMPLIFICAZIONE ARRAY
		$check[$v[$mmv['f_id']]] = 1;
	}
	foreach($aa as $k => $id_val){
		if(in_array($id_val, $ck) && !array_key_exists($id_val, $check)){ # INSERT
			$insert = "INSERT INTO ".$mmv['tbmm']." (".$mmv['f_id'].", ".$mmv['id2d'].") VALUES ('$id_val', '$id')";
			if(mysql_query($insert)) { $added++; }
		}
		elseif(!in_array($id_val, $ck) && array_key_exists($id_val,$check)/* && $check[$id_val]['IS_SEND']!=1*/){ # DELETE
			$delete = "DELETE FROM ".$mmv['tbmm']." WHERE ".$mmv['f_id']." = '$id_val' AND ".$mmv['id2d']." = '$id'";
			if(mysql_query($delete)) { $removed++; }
		}
	}
	return "aggiunte $added, rimosse $removed";
}


if(array_key_exists('upd',$_GET) || array_key_exists('upd',$_POST)){
	$MYFILE -> add_ack( update_ext_meters($my_vars->aa, $my_vars->ck, $id, $mmv));
	//$scheda->many_to_many_tot($id);
}

$scheda->etichetta = 'Contatori';
$etichetta = $rec_ext['FLAT'];

$q_ext = "SELECT * FROM ".$mmv['tbmm']." WHERE ".$mmv['id2d']." = '$id'";
$a_ext = rs::inMatrix($q_ext);
$ext_ids = arr::semplifica($a_ext, $scheda -> f_id);
$tot_ext = count($ext_ids);

$aInfo = array();

$sub_nav[METER] = array('meters_c.php');
$sub_nav[ADD.' '.MEASURE] = array('meters_add_measure.php');
$sub_nav[MEASURES] = array('meters_measures.php');
$sub_nav[FLATS.' ('.$tot_ext.')'] = array('meters_flats_ext.php');
/*
$sub_nav['Contatori'] = array('meters_c.php');
$sub_nav['Aggiungi Misurazione'] = array('meters_add_measure.php');
$sub_nav['Tutte le misurazioni'] = array('meters_measures.php');
$sub_nav['Appartamenti ('.$tot_ext.')'] = array('meters_flats_ext.php');
*/

$is_swf=0; $is_head_mce=0; $is_multibox=0; $is_js=1;
if(array_key_exists('del',$_GET) || array_key_exists('del',$_POST)){
	$cnt = 0;
	foreach($my_vars->ck as $id_del){
		if(!empty($id_del) && $scheda->dele_main($id_del)) $cnt++;
	}
	if($cnt>0) $ack[] = "$cnt deleted";
}

$my_vars->where('');
$aFromp=array();
$scheda -> short_list(array_merge(array($scheda -> f_id), $aShowList)); # VELOCIZZA RIDUCENDO I CAMPI DELLA QUERY
$qTotRec = $scheda->query_list;
$sublable = arr::arr2constant($aShowList, false);
$qTotRec .= ' '.$my_vars->where;

$cursor = new cursor($qTotRec, $scheda -> offset);
$cursor -> set_passo(5);
$cursor -> set_mode('full'); # simple normal full

//$player_curs = new player_curs($qTotRec,$scheda->offset);
$backUri = array_merge($my_vars -> href);
$backUri['id'] = $id;

$backUriHidden=array_merge($my_vars->hidden);
$my_vars->backuri = $backUri;
$my_vars -> etichette_sort($sublable);

$backUriIds = $backUri;
//$player_curs -> vars = $backUri;
//$player_curs -> set();

if(request::gKey('add_search')){ # INSERISCE TUTTI I RECORD DI UNA RICERCA
	// ini_set('memory_limit', '64M'); # DA USARE IN CASO DI PROBLEMI CON MIGLIAIA DI RECORD
	$aRet = array( 'err' => 'Non ci sono record da aggiungere');
	$rTot = rs::inMatrix($qTotRec);
	$rTot = arr::semplifica($rTot, $mmv['f_id']);
	
	$rList = rs::inMatrix("SELECT * FROM ".$mmv['tbmm']." WHERE ".$mmv['id2d']." = '$id'");
	$rList = arr::semplifica($rList, $mmv['f_id']);

	# OTTENGO TUTTI I RECORD NON IN TABELLA PRINCIPALE
	$aUpd = array_diff_assoc($rTot, $rList);

	# INSERISCO TUTTI I RECORD ESTERNI IN TABELLA PRINCIPALE
	$insValues = ''; $cnt = 0;
	foreach($aUpd as $idIns => $x){ # CREO STRINGA DI INSERT IN MODO DA USARE UNA SOLA QUERY PER INSERIRE TUTTI I RECORD
		$insValues .= "($id, $idIns),";
		$cnt ++;
	}
	if(!empty($insValues)){
		$insValues = stringa::togli_ultimo($insValues);
		
		$qIns = "INSERT INTO ".$mmv['tbmm']." (".$mmv['id2d'].", ".$scheda -> f_id.") VALUES $insValues";
		if(mysql_query($qIns)) { $aRet = array('ack' => $cnt.' record aggiunti'); unset($aRet['err']); }
	}
	
	$_GET['err'] = ''; unset($_GET['err']);
	$_GET['ack'] = ''; unset($_GET['ack']);
	
	$aUri = array_merge($backUri, $_GET, $aRet);
	unset($aUri['add_search']);
	io::headto($MYFILE -> file, $aUri);
}	

if(request::gKey('rem_search')){ # RIMUOVE TUTTI I RECORD DI UNA RICERCA
	// ini_set('memory_limit', '64M'); # DA USARE IN CASO DI PROBLEMI CON MIGLIAIA DI RECORD
	$aRet = array( 'err' => 'Non ci sono record da eliminare');
	$rTot = rs::inMatrix($qTotRec);
	$rTot = arr::semplifica($rTot, $mmv['f_id']);
	
	$rList = rs::inMatrix("SELECT * FROM ".$mmv['tbmm']." WHERE ".$mmv['id2d']." = '$id'");
	$rList = arr::semplifica($rList, $mmv['f_id']);
	
	//arr::stampa($rList);
	
	# DELETE DEI RECORD ESTERNI IN TABELLA PRINCIPALE
	$delValues = ''; $cnt = 0;
	foreach($rTot as $idDel => $x){ # CREO WHERE DELETE IN MODO DA USARE UNA SOLA QUERY PER ELIMINARE TUTTI I RECORD
		if(array_key_exists($idDel,$rList)){ # CONTROLLO CHE IL RECORD ESISTA NELLA TABELLA MOLTI A MOLTI
			$delValues .= $mmv['f_id']." = '$idDel' || ";
			$cnt ++;
		}
	}
	if(!empty($delValues)){
		$delValues = '('.stringa::togli_ultimi($delValues, 4).')';
		$qDel = "DELETE FROM ".$mmv['tbmm']." WHERE $delValues AND ".$mmv['id2d']." = '$id'";
		if(mysql_query($qDel)) { $aRet = array('ack' => $cnt.' record rimossi'); unset($aRet['err']); }
	}
	
	$_GET['err'] = ''; unset($_GET['err']);
	$_GET['ack'] = ''; unset($_GET['ack']);
	
	$aUri = array_merge($backUri, $_GET, $aRet);
	unset($aUri['rem_search']);
	io::headto($MYFILE -> file, $aUri);
}	

$rs = rs::inMatrix($qrs=$qTotRec." ".$cursor -> limit);

$lista=''; $cnt=0;

foreach($rs as $rec){
	$celle='';
	foreach($sublable as $k=>$v){
		$color = $cnt%2==0 ? '' : ' class="contrast"';
	
		if(substr($k,0,2)=='D_') $rec[$k] = dtime::my2iso($rec[$k]); // DATA
		if(substr($k,0,3)=='DT_') $rec[$k] = dtime::my2isodt($rec[$k]); // DATA
		elseif(substr($k,0,3)=='IS_') $rec[$k] = !empty($rec[$k]) ? '<div class="icon_check_att"></div>' : '<div class="icon_check_dis"></div>'; // SI NO
		elseif(substr($k,0,3)=='ID_' || $k == $scheda->f_path) $rec[$k]; // IMMAGINE
		elseif($k == 'EMAIL'){ $rec[$k]; /* NON FACCIO NIENTE */ }
		else {
			$rec['FULL_'.$k] = $rec[$k];
			$rec[$k] = strcut($rec[$k],'...',$strcut);
		} 
		$celle.='<td>'.$rec[$k].'</td>';
	}
	
		if(array_key_exists($rec[$scheda -> f_id], $ext_ids)) {

		$ck_multiplo =  '<input type="checkbox" name="ck'.$rec[$scheda->f_id].'" value="1" class="checkbox" checked="checked" />
		<input type="hidden" value="1" name="aa'.$rec[$scheda->f_id].'">';
	}
	else $ck_multiplo = '<input type="checkbox" name="ck'.$rec[$scheda->f_id].'" value="1" class="checkbox"  />
		<input type="hidden" value="1" name="aa'.$rec[$scheda->f_id].'">';
	
	$href_mod = '';

	if(array_key_exists('title',$aInfo)){
		$info = strlen($rec['FULL_'.$aInfo['description']])>0 ? '<a href="'.$rec['FULL_'.$aInfo['description']].'" onclick="return false;" class="Tips1" title="'.$rec['FULL_'.$aInfo['title']].'"><img src="'.IMG_AR.'icon_info.gif" /></a>' : '';
	} else { $info = ''; }
	$lista.='<tr'.$color.' valign="top">'.$celle.'
	<td class="comandi_lista">'.$info.$href_mod.$ck_multiplo.'
	</td></tr>'."\n";
	$cnt++;
}

$vars_back = $_GET;
$vars_back['id'] = ''; unset($vars_back['id']);
$vars_back['ic'] = ''; unset($vars_back['ic']);
$vars_back['crud'] = ''; unset($vars_back['crud']);
$vars_back['err'] = ''; unset($vars_back['err']);
$vars_back['ack'] = ''; unset($vars_back['ack']);

$href_lista = io::ahrefcss( $list_file, $vars_back, LISTA, '', '', LISTA, '', "g-button");
$href_add_search = io::a($MYFILE -> file, array_merge($backUri, $vars_back, array('id' => $id, 'add_search' => '1')), ADD_ALL_REC, array('class' => 'g-button'));
$href_rem_search = io::a($MYFILE -> file, array_merge($backUri, $vars_back, array('id' => $id, 'rem_search' => '1')), REM_ALL_REC, array('class' => 'puls_elimina'));

$puls_aggiorna = '<input name="upd" type="submit" value="aggiorna" class="button_aggiorna floatright">';

$sub_menu = '';
foreach($sub_nav as $k => $arr){
	$href_to='';
	if(in_array(FILENAME.'.php',$arr)) { $sub_menu.='<div class="menu_dis"><span>'.$k.'</span></div>'; }
	elseif($crud=='ins') { $sub_menu.='<div class="menu_att_dis">'.$k.'</div>'; }
	else {
		$addCrud = substr($k,0,8)=='Contatti' ? array() : array('crud'=>'upd');
	//	$backUri['id']=$id;
		//$backUri['vaiu'] = $vaiu;
		$href_to = io::ahrefcss($arr[0],$val=array_merge($backUri,$addCrud), $txt=$k,$js='',$target="",$title=strtolower($k), $id='', $css="");
		$sub_menu.='<div class="menu_att"><span>'.$href_to.'</span></div>';
	}
}
$sub_menu = '<div id="menu_bar">'.$sub_menu.'</div>';

include BLOCCHI_AR.'cursor.php';
include BLOCCHI_AR.'list_buttons.php';
include_once HEAD_AR;
print $sub_menu;
?>
<form method="post" name="list">
<?=request::hidden($backUriHidden)?>
<table class="list">
<tr class="bg">  
<th colspan="2"><?=$href_lista?> <?=$href_add_search?> <?=$href_rem_search?>
</th>
<th colspan="<?=$my_vars->colonne-1?>"><?=$puls_aggiorna?></th>
</tr>
<tr class="yellow"><td colspan="<?=$my_vars->colonne+1?>"><strong><?=$rm['REGISTERNUM']?></strong></td></tr>
<tr class="sort"><?=$my_vars->th?><th align="right"><span class="a_sel_tutti"><?=$sel_all?></span></th></tr>
<tr><?=$my_vars->ricerca?><th align="right"><input id="button" <?=$my_vars->sortbutton;?> class="g-button" type="submit" value="<?=FIND?>" name="button"/></th></tr>
<?=$lista?>
<tr><th colspan="<?=$my_vars->colonne+1?>"><?=$cursor -> player?></th></tr>
<tr><th colspan="<?=$my_vars->colonne+1?>"><?=$cursor -> t_recs?> record | <?=$cursor -> t_curs?> pagine</th></tr>
</table>
</form>
<?php
include_once FOOTER_AR;
?>