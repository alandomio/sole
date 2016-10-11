<?php
# V.0.1.8
include_once 'init.php';
$user = new autentica($aA1);
$user -> login_standard();

# VARIABILI DI DEFAULT
list($id, $idi) = request::get(array('id' => NULL, 'idi' => NULL));


# ESECUZIONE CODICE
if(array_key_exists('upd',$_GET) || array_key_exists('upd',$_POST)){
	$MYFILE -> add_ack( update_ext_meters($my_vars->aa, $my_vars->ck, $id, $mmv));
}

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
# GENERAZIONE PAGINE

# PAGINA CONTATORI
$scheda = new nw('meters');
$crud = empty($id) ? 'ins' : 'upd';

$scheda -> ext_table(array('meterpropertys', 'outputs', 'supplytypes','rfs'));

// AGGIUNGO CHECKBOX MOLTI A MOLTI
$scheda -> add_mm('usages', $id);

// CONFIGURAZIONE NW

// MOLTI A MOLTI
$scheda -> many_to_many(array('flats_meters' => array(
								'id' 	=> 'ID_FLAT',
								'title' => 'REGISTERNUM',
								'ext'	=> 'flats',
								'where'	=> "ID_METER",
								'lbl'	=> "Contatori",
								'file' => 'meters_flats_ext.php'
						)
					)
				);

$scheda -> many_to_many_tot($id);

// CONFIGURAZIONE FILTRI
$my_vars = new ordinamento(array('rn' => 'REGISTERNUM', 'mp' => 'METERPROPERTY', 'do' => 'IS_DOUBLE', 'ou' => 'OUTPUT', 'sp' => 'SUPPLYTYPE'));

$my_vars->sort_default($scheda->f_id);
$my_vars->tabella = $scheda->table;

// QUERY 
$fil = "";
$fil = is_null($crud) ? "" : $fil;
$fil = $crud == "ins" ? " WHERE 1=0 " : $fil;
$fil = $crud == "upd" ? " WHERE ".$scheda->table.".".$scheda->f_id."='$id' " : $fil; 

//$aCurs=array("icursor"=>$icursor);
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
$db->dbset();

// !! BASTERÀ FARLO UNA VOLTA
$_POST = request::adjustPost($_POST);
//$rec = request::post2arr($sublable);
$rec = arr::magic_quote($rec);
$rec = arr::_trim($rec,array('DESCRIP'));

$db -> a_val = array_merge($db->a_val,$rec);
$db -> dbset();

$val = $backUri;

$href_lista = io::a($scheda->file_l, $val, LISTA, array('title' => LISTA, 'class' => 'puls_back'));

$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);
$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);

$db -> ID_TYPE -> id = 'idtype';
$db -> ID_TYPE -> addblank = 1;
$db -> ID_TYPE -> txtblank = S_CHOOSE.' '.ID_TYPE;
$db -> ID_METERTYPE -> id = 'idmetertype';
$db -> ID_METERTYPE -> addblank = 1;
$db -> ID_METERTYPE -> txtblank = S_CHOOSE.' '.ID_METERTYPE;
$db -> IS_DOUBLE -> lable = '';
$db -> IS_DOUBLE -> id = 'is_double';
$db -> ID_RF -> id = 'rf';
$db -> ID_RF -> addblank = 1;
$db -> ID_RF -> txtblank = S_CHOOSE.' '.RF;
$db -> ID_OUTPUT -> id = 'ab';
$db -> D_FROM_MT -> type = 'text';
$db -> D_FROM_MT -> css = 'datepicker';
$db -> D_TO_MT -> type = 'text';
$db -> D_TO_MT -> css = 'datepicker';

ob_start();
?>

<div id="tabs">
<ul>
    <li><a href="#tabs-1"><?php print METER; ?></a></li>
    <li><a href="#tabs-2"><?php print ADD.' '.MEASURE; ?></a></li>
    <li><a href="#tabs-3"><?php print MEASURES; ?></a></li>
    <li><a href="#tabs-4"><?php print FLATS; ?></a></li>
</ul>
<div id="tabs-1" class="tabpage">
<button type="button" class="ui-button"  value="Salva"><span class="ui-button-text">Salva</span></button>
<form id="contatore" method="post" enctype="multipart/form-data" action="ajax/json.php?action=put_contatore">
<?=request::hidden($backUri)?>
<table class="list personal">

<tr><td valign="top" width="200"><div class="lbl"><?=ID_TYPE?></div><? $db -> ID_TYPE -> get(); ?></td><td>
<div class="idtype">
<div class="bfloat"><div class="lbl"><?=HMETER?></div><? $db -> HMETER -> get(); ?></div>
</div>
</td></tr>

<tr><td valign="top"><div class="lbl"><?=ID_METERTYPE?></div><? $db -> ID_METERTYPE -> get(); ?></td><td>
<div class="boxinput"><div class="lbl"><?=START_1?></div><? $db -> START_1 -> get(); ?></div>
<div class="hourly_m"><div class="lbl"><?=START_2?></div><? $db -> START_2 -> get(); ?></div>
<div class="hourly_m"><div class="lbl"><?=START_3?></div><? $db -> START_3 -> get(); ?></div>
<div class="boxinput"><div class="lbl"><?=END_1?></div><? $db -> END_1 -> get(); ?></div>
<div class="hourly_m"><div class="lbl"><?=END_2?></div><? $db -> END_2 -> get(); ?></div>
<div class="hourly_m"><div class="lbl"><?=END_3?></div><? $db -> END_3 -> get(); ?></div>
</td></tr>

<tr><td valign="top"><div class="lbl"><?=RF?></div><? $db -> ID_RF -> get(); ?></td><td>
<div class="rf">
<div class="bfloat"><div class="lbl"><?=FORMULA?></div><? $db -> FORMULA -> get(); ?> <?=io::a('#', array(), 'Formula', array())?></div>
</div>
</td></tr>

<tr><td valign="top"><div class="lbl"><?=ID_OUTPUT?></div><? $db -> ID_OUTPUT -> get(); ?></td><td>
<div class="ab">
<div class="bfloat"><div class="lbl"><?=A?></div><? $db -> A -> get(); ?> <?=io::a('#', array(), 'A', array())?></div>
<div class="bfloat"><div class="lbl"><?=B?></div><? $db -> B -> get(); ?> <?=io::a('#', array(), 'B', array())?></div>
</div>
</td></tr>

<tr><td valign="top"><div class="lbl"><?=IS_DOUBLE?></div><? $db -> IS_DOUBLE -> get(); ?></td><td></td></tr>
<tr><td valign="top"><div class="lbl"><?=REGISTERNUM?></div><? $db -> REGISTERNUM -> get(); ?></td><td></td></tr>
<tr><td valign="top"><div class="lbl"><?=MATRICULA_ID?></div><? $db -> MATRICULA_ID -> get(); ?></td><td></td></tr>
<tr><td valign="top"><div class="lbl"><?=ID_METERPROPERTY?></div><? $db -> ID_METERPROPERTY -> get(); ?></td><td></td></tr>
<tr><td valign="top"><div class="lbl"><?=SUPPLYTYPE?></div><? $db -> ID_SUPPLYTYPE -> get(); ?></td><td></td></tr>
<tr><td valign="top"><div class="lbl"><?=SCALA_MT?></div><? $db -> SCALA_MT -> get(); ?></td><td></td></tr>
<tr><td valign="top" colspan="2"><div class="lbl"><?=constant(stringa::tbl2field('usages'))?></div>
<?=$scheda -> mmBox['usages']?></td></tr>
</table>  
</form>
<script type="text/javascript">
$(document).ready(function(){
	var sel = $('#idmetertype');
	var rf = $('#rf');
	var idtype = $('#idtype');
	var ab = $('#ab');
	
	
	if(sel.val() == 2){
		$('.hourly_m').css('display','block');
	}
	if(rf.val() == 2){
		$('.rf').css('display','block');
	}
	if(idtype.val() == 1){ // ID TYPE
		$('.idtype').css('display','block');
	}
	if(ab.val() == 2){ // OUTPUT
		$('.ab').css('display','block');
	}


	sel.change(function() {
		if(sel.val() == 2){ // Electricity hourly meter
			$('.hourly_m').css('display','block');
			
		}
		else{ 
			$('.hourly_m').css('display','none');
		}
	});
	
	$('#is_double').change(function()	{
		if($('#is_double').is(':checked'))
			$('.double_m').css('display','block');
		else
			$('.double_m').css('display','none');
	});
	
	rf.change(function() {
		if(rf.val() == 2){ // REAL / FORMULA
			$('.rf').css('display','block');
		}
		else{
			$('.rf').css('display','none');
		}
	});
	
	idtype.change(function() {
		if(idtype.val() == 1){ // ID TYPE
			$('.idtype').css('display','block');
		}
		else{
			$('.idtype').css('display','none');
		}
	});

	ab.change(function() {
		if(ab.val() == 2){ // OUTPUT
			$('.ab').css('display','block');
		}
		else{
			$('.ab').css('display','none');
		}
	});
	
});
</script>
</div>
<?php
$html['contatori'] = ob_get_clean();

# PAGINA NUOVAMISURAZIONE
$sk_newms = new nw('measures');

$crud = empty($idi) ? 'ins' : 'upd';

$qm = "SELECT * FROM meters WHERE ID_METER = '$id'";
$rm = rs::rec2arr($qm);

$sk_newms -> ext_table(array('uploadtypes'));

// CONFIGURAZIONE NW
$aStripExt = array('ID_METER');
foreach($sk_newms -> ext_table as $k => $v){
	$aStripExt = array_merge($aStripExt, rs::get_fields($k));
	unset($aStripExt[stringa::tbl2id($v)]);
}

$aStripC = array_merge(array('TS'), array(stringa::tbl2id($sk_newms -> table)));

// LISTA CAMPI DI RICERCA / ORDINAMENTO
$aShowList = array('D_MEASURE','IS_CONFIRMED_MS','ANNO_MS','UPLOADTYPE');
$aShowCrud = array_diff($sk_newms -> aFields, $aStripC, $aStripExt);

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
$etichetta = $rec_scheda[$aShowList[0]];

# LIMITE FOTO PER UPLOAD
if(!empty($sk_newms -> files)){
	$totali = $sk_newms -> cnt_files($idi);
	$swf_limite = LIMITE_FOTO-$totali['i'] <= 0 ? 0 : LIMITE_FOTO-$totali['i'];
}

/*$q_ext = "SELECT * FROM flats_meters WHERE ID_METER = '$id'";
$a_ext = rs::inMatrix($q_ext);
$meters_ids = arr::semplifica($a_ext, 'ID_METER');
$tot_ext = count($meters_ids);*/

$fil = "";
$fil = is_null($crud) ? "" : $fil;
$fil = $crud == "ins" ? " WHERE 1=0 " : $fil;
$fil = $crud == "upd" ? " WHERE ".$sk_newms->table.".".$sk_newms->f_id."='$idi' " : $fil; 

//$aCurs=array("icursor"=>$icursor);
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

if(array_key_exists('nw_img_dele',$_GET)){
	$sk_newms->dele_img($idi);
}

# C.R.U.D.
$ERR_CRUD = err::crud($rec);
$_POST = request::adjustPost($_POST);
$rec = request::post2arr($sublable);
$rec = arr::magic_quote($rec);
$rec = arr::_trim($rec,array('DESCRIP'));

$db_ms -> a_val = array_merge($db_ms->a_val,$rec);
$db_ms -> dbset();

$val = $backUri;

$href_lista = io::a($sk_newms->file_l, $val, LISTA, array('title' => LISTA, 'class' => 'puls_back'));
$href_add_file = ($crud == 'upd' && !empty($sk_newms -> files)) ? io::a($sk_newms->file_f, $backUri, ADD_FILE, array('title' => ADD_FILE, 'class' => 'puls_aggiungi')) : '';


$send_nuovo = arr::_unset($backUri, array('idi', 'jsstatis'));
$href_new = io::a($sk_newms->file_c, array_merge($send_nuovo, array('crud' => 'ins')), 'Nuovo' , array('title' => 'Aggiungi nuovo record', 'class' => 'puls_nuovo'));

$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);
$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);

$db_ms -> D_MEASURE -> type = 'text';
$db_ms -> D_MEASURE -> css = 'datepicker';

if($crud == 'upd'){
	$db_ms -> ID_LABLE_SITE -> type = 'lable';
}

ob_start();
?>
<div id="tabs-2" class="tabpage">
<form method="post" enctype="multipart/form-data" action="<?=$action?>">
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
</form>
</div> 
<?php
$html['nuovamisurazione'] = ob_get_clean();














# LISTAMISURAZIONI
$sk_msl = new nw('measures');
$sk_msl -> offset = 10;

# VARIABILI DI DEFAULT
$qm = "SELECT * FROM meters WHERE ID_METER = '$id'";
$rm = rs::rec2arr($qm);

$sk_msl -> ext_table(array('uploadtypes'));

# CONFIGURAZIONE NW
$aStripExt = array('ID_METER');
foreach($sk_msl -> ext_table as $k => $v){
	$aStripExt = array_merge($aStripExt, rs::get_fields($k));
	unset($aStripExt[stringa::tbl2id($v)]);
}

$aStripC = array_merge(array('TS'), array(stringa::tbl2id($sk_msl -> table)));

# LISTA CAMPI DI RICERCA / ORDINAMENTO
$aShowList = array('D_MEASURE','IS_CONFIRMED_MS','ANNO_MS','UPLOADTYPE');
$aShowCrud = array_diff($sk_msl -> aFields, $aStripC, $aStripExt);

# CONFIGURAZIONE FILTRI
$vars_msl = new ordinamento(array('dm' => 'D_MEASURE', 'ic' => 'IS_CONFIRMED_MS', 'ym' => 'ANNO_MS', 'ut' => 'UPLOADTYPE'));
$vars_msl -> campi_force['text']['HCOMPANY'] = 'flats';
$vars_msl -> campi_force['text']['USER'] = 'flats';

$vars_msl->sort_default($sk_msl->f_id);
$vars_msl->tabella = $sk_msl->table;

# PRE CARICO I DATI DEL RECORD CORRENTE IN MODO DA USARLI DOVE SERVE
$rec_scheda = rs::rec2arr("SELECT * FROM ".$sk_msl->table." WHERE ".$sk_msl->f_id."='$idi' LIMIT 0,1");

$aOrd = array();
# ETICHETTA DA MOSTRARE COME EVIDENZA DEL RECORD
$etichetta = $rec_scheda[$aShowList[0]];


$q_ext = "SELECT * FROM flats_meters WHERE ID_METER = '$id'";
$a_ext = rs::inMatrix($q_ext);
$meters_ids = arr::semplifica($a_ext, 'ID_METER');
$tot_ext = count($meters_ids);

$sub_nav[METERS] = array('meters_c.php');
$sub_nav[ADD.' '.MEASURE] = array('meters_add_measure.php');
$sub_nav[MEASURES] = array('meters_measures.php');
$sub_nav[FLATS.' ('.$tot_ext.')'] = array('meters_flats_ext.php');

if(!empty($sk_msl -> files)) $sub_nav['Allegati'] = array($sk_msl->file_f);

$vars_msl->where('');
$aFromp=array();

$sk_msl -> query_list();
$qTotRec = $sk_msl->query_list;

$sublable = arr::arr2constant($aShowList, false);

$qTotRec .= ' '.$vars_msl->where;


$cursor = new cursor($qTotRec, $sk_msl -> offset);
$cursor -> set_passo(5);
$cursor -> set_mode('full'); # simple normal full


//$player_curs = new player_curs($qTotRec,$sk_msl->offset);

$backUri = array_merge($vars_msl->href);
$backUri['idi'] = $idi;
$backUri['id'] = $id;

$backUriHidden=array_merge($vars_msl->hidden);
$vars_msl->backuri = $backUri;
$vars_msl -> etichette_sort($sublable);

$backUriIds = $backUri;

$rs = rs::inMatrix($qrs=$qTotRec." ".$cursor -> limit);

$lista=''; $cnt=0;
foreach($rs as $rec){
	$backUriIds["id"] = $rec[$sk_msl->f_id];
	if(!empty($sk_msl->files)){ // GESTIONE MULTIPLA FILE
		$totali = $sk_msl->cnt_files($rec[$sk_msl->f_id]);
		$rec['ID_FILE'] = '';
		if(in_array('files', $sk_msl->files) && in_array('images', $sk_msl->files)) $rec['ID_FILE'] = io::a($sk_msl->file_f, $backUriIds, $totali['f'].' file - '.$totali['i'].' img', array('class' => 'puls_allegati_text'));
		elseif(in_array('images', $sk_msl->files)) $rec['ID_FILE'] = io::a($sk_msl->file_f, $backUriIds, $totali['i'].' img', array('class' => 'puls_allegati_text'));
	}

	$color = $cnt%2==0 ? '' : ' class="contrast"';
	$href[EDIT]=io::a($sk_msl->file_c, array_merge($backUriIds, array('crud' => 'upd')), EDIT, array('class' => 'puls_modifica_text'));

	$celle='';
	foreach($sublable as $k=>$v){
		if(substr($k,0,2)=='D_') $rec[$k] = dtime::my2iso($rec[$k]); // DATA
		elseif(substr($k,0,3)=='IS_') $rec[$k] = !empty($rec[$k]) ? '<div class="icon_check_att"></div>' : '<div class="icon_check_dis"></div>'; // SI NO
		elseif($k == 'ID_FILE'){ 
			if(in_array('images', $sk_msl -> files)){
				$a_img = $sk_msl -> files_list($rec[$sk_msl -> f_id], 'i', $sk_msl -> img_alb_sqr);
				if(!empty($a_img)){
					$prw_img = $a_img[0]['obj_img'];
					$prw_img -> set_attr(50,50);
					$rec[$k] = io::a($sk_msl->file_f, $backUriIds, $prw_img -> html, array());
				}
			}
		}		
		elseif(substr($k,0,3)=='ID_' || $k == $sk_msl->f_path) $rec[$k]; // IMMAGINE
		elseif($k == 'USER') $rec[$k];
		else $rec[$k] = strcut(trim(strip_tags($rec[$k])),'...',20);
		$celle.='<td>'.$rec[$k].'</td>';
	}
	
	$links = '';
	foreach($href as $k => $link){
		$links .= '<li>'.$link.'</li>';
	}
	if(!empty($links)) $links = '<ul>'.$links.'</ul>';
	$links = '<div id="links">'.$links.'</div>';
	
	
	$lista.='<tr'.$color.' valign="top">'.$celle.'
	<td class="contrast" align="right">'.$links.'
    <input type="checkbox" name="ck'.$rec[$sk_msl->f_id].'" value="1" class="checkbox" /></td></tr>';
	
$cnt++;
}

//$href_new = io::a('meters_add_measure.php', array_merge($backUri,array('crud' => 'ins')), 'Nuova misurazione', array('title' => 'Aggiungi nuovo record', 'class' => 'puls_nuovo'));

$sub_menu = '';
foreach($sub_nav as $k => $arr){
	$href_to='';
	if(in_array(FILENAME.'.php',$arr)) { $sub_menu.='<div class="menu_dis"><span>'.$k.'</span></div>'; }
	/*elseif($crud=='ins') { $sub_menu.='<div class="menu_att_dis">'.$k.'</div>'; }*/
	else {
		$href_to = io::a($arr[0], array_merge($backUri,array('crud'=>'upd')), $k, array('title' => $title=strtolower($k)));
		$sub_menu.='<div class="menu_att"><span>'.$href_to.'</span></div>';
	}
}
$sub_menu = '<div id="menu_bar">'.$sub_menu.'</div>';

$vars_msl->campi_ricerca();

$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);

ob_start();
?>
<div id="tabs-3" class="tabpage">
<form method="get" name="list" action="<?=$action?>">
<?=request::hidden($backUriHidden)?>
<table class="list">
<tr class="bg">  
<th colspan="<?=$vars_msl->colonne?>">
</th>
<th align="right"><input name="del" type="submit" class="button_elimina floatright" value="elimina" /></th>
</tr>
<tr class="yellow"><td colspan="<?=$vars_msl->colonne+1?>"><div class="table_cell"><strong><?=$rm['REGISTERNUM']?></strong></div></td></tr>

<tr class="sort"><?=$vars_msl->th?><th align="right"><span class="a_sel_tutti">sel. tutti <input type="checkbox" class="checkbox" onchange="javascript:check_all_by_name('list', 'ck')" name="ck" value="1" /></span></th></tr>
<tr class="search"><?=$vars_msl->ricerca?><th align="right"><input id="button" <?=$vars_msl->sortbutton;?> class="button_cerca" type="submit" value="<?=FIND?>" name="button"/></th></tr>
<?=$lista?>
<tr><th colspan="<?=$vars_msl->colonne+1?>"><?=$cursor -> player?></th></tr>
<tr><th colspan="<?=$vars_msl->colonne+1?>"><?=$cursor -> t_recs?> record | <?=$cursor -> t_curs?> pagine</th></tr>
</table>
</form>
</div>
<?php
$html['listamisurazioni'] = ob_get_clean();


























# PAGINA APPARTAMENTI
$sk_app = new nw('flats');
$sk_app -> offset = 100;

$qm = "SELECT * FROM meters WHERE ID_METER = '$id'";
$rm = rs::rec2arr($qm);

# CONFIGURAZIONE MOLTI A MOLTI
$mmv = array(
	'tbmm' => 'flats_meters',
	'id2d' => 'ID_METER',
	'f_id' => $sk_app -> f_id
);

$tbl_main = stringa::id2table($mmv['id2d']);
$list_file = $tbl_main.'.php';


# CONFIGURAZIONE NW
$sk_app->ext_table(array('hcompanys', 'users'));

# VISUALIZZAZIONE LISTA
$aStripC = array();
$aShowList = array('FLAT', 'NETAREA', 'HCOMPANY');
$aShowCrud = array_diff($sk_app -> aFields, $aStripC);

# CONFIGURAZIONE FILTRI
$vars_app = new ordinamento(array('ft' => 'FLAT', 'na' => 'NETAREA', 'hc' => 'HCOMPANY'));
$vars_app -> campi_force['text']['FLAT'] = 'flats';

$vars_app->sort_default($sk_app->f_id);
$vars_app->tabella = $sk_app->table;

$rec_scheda = rs::rec2arr("SELECT * FROM ".$sk_app->table." WHERE ".$sk_app->f_id."='$id'"); 

$sk_app->etichetta = 'Contatori';

$q_ext = "SELECT * FROM ".$mmv['tbmm']." WHERE ".$mmv['id2d']." = '$id'";
$a_ext = rs::inMatrix($q_ext);
$ext_ids = arr::semplifica($a_ext, $sk_app -> f_id);
$tot_ext = count($ext_ids);

$aInfo = array();

$vars_app -> where('');
$aFromp=array();
$sk_app -> short_list(array_merge(array($sk_app -> f_id), $aShowList)); # VELOCIZZA RIDUCENDO I CAMPI DELLA QUERY
$qTotRec = $sk_app->query_list;
$sublable = arr::arr2constant($aShowList, false);
$qTotRec .= ' '.$vars_app->where;

$cursor = new cursor($qTotRec, $sk_app -> offset);
$cursor -> set_passo(5);
$cursor -> set_mode('full'); # simple normal full

$backUri = array_merge($vars_app -> href);
$backUri['id'] = $id;

$backUriHidden=array_merge($vars_app->hidden);
$vars_app->backuri = $backUri;
$vars_app -> etichette_sort($sublable);

$backUriIds = $backUri;

$rs = rs::inMatrix($qrs=$qTotRec." ".$cursor -> limit);

$lista=''; $cnt=0;
foreach($rs as $rec){ # CREO LA LISTA
	$celle='';
	foreach($sublable as $k=>$v){
		$color = $cnt%2==0 ? '' : ' class="contrast"';
		if(substr($k,0,2)=='D_') $rec[$k] = dtime::my2iso($rec[$k]); // DATA
		if(substr($k,0,3)=='DT_') $rec[$k] = dtime::my2isodt($rec[$k]); // DATA
		elseif(substr($k,0,3)=='IS_') $rec[$k] = !empty($rec[$k]) ? '<div class="icon_check_att"></div>' : '<div class="icon_check_dis"></div>'; // SI NO
		elseif(substr($k,0,3)=='ID_' || $k == $sk_app->f_path) $rec[$k]; // IMMAGINE
		elseif($k == 'EMAIL'){ $rec[$k]; /* NON FACCIO NIENTE */ }
		else {
			$rec['FULL_'.$k] = $rec[$k];
			$rec[$k] = strcut($rec[$k],'...',30);
		} 
		$celle.='<td>'.$rec[$k].'</td>';
	}
	if(array_key_exists($rec[$sk_app -> f_id], $ext_ids)) {
		$ck_multiplo =  '<input type="checkbox" id="ck'.$rec[$sk_app->f_id].'" name="ck'.$rec[$sk_app->f_id].'" onclick="update('.$rec[$sk_app->f_id].', '.$id.');" value="1" class="checkbox" checked="checked" />';
	}
	else $ck_multiplo = '<input type="checkbox" name="ck'.$rec[$sk_app->f_id].'" onclick="update('.$rec[$sk_app->f_id].', '.$id.');"  value="1" class="checkbox"  />';
	
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

$puls_aggiorna = '<input name="upd" type="submit" value="aggiorna" class="button_aggiorna floatright">';

$href_new = io::ahrefcss($sk_app->file_c, $val=array_merge($backUri,array('crud' => 'ins')), $txt=ADD_NEW,$js='',$target="",$title=ADD_NEW, $id='', $css="puls_nuovo");
$sel_all = 'sel. tutti <input type="checkbox" class="checkbox" onchange="javascript:check_all_by_name(\'list\', \'ck\')" name="ck" value="1" />';
$puls_elimina = '<input name="del" type="submit" class="button_elimina" value="elimina" />';
if($sk_app->action_type=='read') { $href_new = ''; $sel_all=''; $puls_elimina='';}

$vars_app->campi_ricerca();

ob_start();
?>
<div id="tabs-4" class="tabpage">
<form method="post" name="list">
<?=request::hidden($backUriHidden)?>
<table class="list">
<tr class="sort"><?=$vars_app->th?><th align="right"><span class="a_sel_tutti"><?=$sel_all?></span></th></tr>
<tr><?=$vars_app->ricerca?><th align="right"><input id="button" <?=$vars_app->sortbutton;?> class="button_cerca" type="submit" value="<?=FIND?>" name="button"/></th></tr>
<?=$lista?>
<tr><th colspan="<?=$vars_app->colonne+1?>"><?=$cursor -> player?></th></tr>
<tr><th colspan="<?=$vars_app->colonne+1?>"><?=$cursor -> t_recs?> record | <?=$cursor -> t_curs?> pagine</th></tr>
</table>
</form>
</div>


</div>
<script>
	$(function() {
		$( "#tabs" ).tabs({selected: 0	});
	});
	
	$(document).ready(function()	{
		$('#tabs-1 button').click(function() {
			$("#contatore").ajaxSubmit(function(data){
						if(data.success)
							jAlert('ok');
						else	{
							jAlert('ko');
							$('#message').html(data.message);
						}
							
					});
		});
	});
	
	function update(appartamento, contatore) {
		valore = $('#ck' + appartamento).is(":checked");
		jQuery.getJSON("ajax/json.php?action=put_contatore_appartamento&id_contatore="+contatore+'&id_appartamento=' + appartamento + '&valore=' + valore,
				function(data){	
					
				});
	}
	
</script>
<?php
$html['appartamenti'] = ob_get_clean();


include_once HEAD_AR;
//print $sub_menu;

print $html['contatori'];
print $html['nuovamisurazione'];
print $html['listamisurazioni'];
print $html['appartamenti'];


include_once FOOTER_AR;
?>