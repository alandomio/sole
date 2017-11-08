<?php
# V.0.1.8
include_once 'init.php';
$user = new autentica($aA1);
$user -> login_standard();
include_once 'descriptors_types_conf.php';

$fil="";
$fil=is_null($crud) ? "" : $fil;
$fil=$crud=="ins" ? " WHERE 1=0 " : $fil;
$fil=$crud=="upd" ? " WHERE ".$scheda->table.".".$scheda->f_id."='$id' " : $fil; 

//$title_pag.=$crud=="ins" ? "Inserimento ".$scheda->etichetta : "Modifica ".$scheda->etichetta;

$aCurs=array("icursor"=>$icursor);
$aFromp=array();
$aFiltro=array();
$backUri=array_merge($aCurs, $my_vars->href, $my_vars -> hidden);
$backUri['id'] = $id;
$qTotRec="SELECT * FROM ".$scheda->table;
if($scheda->ext_table != NULL) {
	$qTotRec.="";
}
$atabelle=$scheda->atable;
$lable=rs::sql2lbl($qTotRec);
$sublable=$lable;
$rec=rs::rec2arr($qrs=$qTotRec." ".$fil);

if(array_key_exists('nw_img_dele',$_GET)){
	$scheda->dele_img($id);
}

# C.R.U.D.

if(array_key_exists("ins",$_POST)){ # INSERIMENTO NUOVA VOCE
	# COSTRUISCO L'INSERT
	$sFlds = ''; $sVals = '';
	foreach($my_vars -> tx as $campo => $valore){
		$s = prepare4sql($valore);
		$sFlds .= " $campo,";
		$sVals .= " '$s',";
		$sAcks .= " &quot;$s&quot;,";
	}
	$qIns = "INSERT INTO descriptors ($sFlds ID_DESCRIPTORS_TYPE, IS_DESCRIPTOR) VALUES ($sVals '$id', '1')";
	
	# REDIRECT DOPO L'INSERIMENTO
	if(mysql_query($qIns)){ 
		io::headto(FILENAME.'.php', array_merge($_GET, array('ack' => "Aggiunto ".stringa::togli_ultimo($sAcks))));
	}
	else $MYFILE -> err[] = "Inserimento fallito";
}

if(array_key_exists("subDo",$_POST) || array_key_exists("subBack",$_POST)){
	$c1 = 0; $c2 = 0;
	

if(array_key_exists('ordina', $_GET) || array_key_exists('ordina', $_POST)){ # ORDIMENTO ELEMENTI
	$rList = rs::id2arr("SELECT ID_STRUCTURE, RANK FROM structures"); # LISTA ID => RANK
	$cnt = 0; $iord = 1;
	foreach($my_vars -> ba as $k => $val){
		if($rList[$k] != $iord){
			if(mysql_query("UPDATE structures SET RANK = '$iord' WHERE ID_STRUCTURE = '$k'")) $cnt++;
		}
		$iord++;
	}
	if($cnt > 0){ $MYFILE -> add_ack('Ordine modificato'); }
}

	
	
	foreach($my_vars -> ln as $k => $v){ # UPDATE NOMI DESCRITTORI MULTILINGUA
		$qUp = "UPDATE descriptors SET DESCRIPTOR_".$v['lang']." = '".prepare4sql($v['value'])."' WHERE ID_DESCRIPTOR = '".$v['id']."'";
		if(mysql_query($qUp)) $c1++;
	}
	
	$ord = 1;
	foreach($my_vars -> bb as $k => $v){ # UPDATE RANK DESCRITTORI
/*		$v = stringa::charclear($v, "0123456789");
		if($v < 0 || $v == ''){
			$err[] = $v.': usare un numero maggiore o uguale a zero';
			continue;
		}*/
		$qUp = "UPDATE descriptors SET RANK = '$ord' WHERE ID_DESCRIPTOR = '$k'";
		if(mysql_query($qUp)) $c2++;
		$ord ++;
	}
	if(!empty($c1)){ $MYFILE -> ack[] = 'Aggiornati '.$c1.' nomi di descrittori.'; }
	if(!empty($c2)){ $MYFILE -> ack[] = 'Aggiornati '.$c2.' ordinamenti di descrittori.'; }

}
$val = $backUri;
$href_annulla = io::href($scheda->file_l, $val, 'Indietro', $js='',$target="",$title=LISTA, '', $css="g-button");
if($crud=='upd'){
$href_add_file = $scheda->files ? io::href($scheda->file_f,$val=$backUri, $txt=ADD_FILE,$js='',$target="",$title=ADD_FILE, '', $css="puls_aggiungi") : '';
} else {$href_add_file = ''; }


$sub_menu = '';
foreach($sub_nav as $k => $arr){
	$href_to='';
	if(in_array(FILENAME.'.php',$arr)) { $sub_menu.='<div class="menu_dis"><span>'.$k.'</span></div>'; }
	elseif($crud=='ins') { $sub_menu.='<div class="menu_att_dis">'.$k.'</div>'; }
	else {
		$href_to = io::href($arr[0],$val=array_merge($backUri,array('crud'=>'upd')), $txt=$k,$js='',$target="",$title=strtolower($k), '', $css="");
		$sub_menu.='<div class="menu_att"><span>'.$href_to.'</span></div>';
	}
}
$sub_menu = '<div id="menu_bar">'.$sub_menu.'</div>';

# COSTRUISCO I CAMPI TEXT
$txtinserts = '';
foreach($lang -> langs as $k => $lng){
	// '.constant($lng).':
	$txtinserts .= '<div class="box_input"><span>'.constant($lng).':</span>'.BR.'<input type="text" name="txDESCRIPTOR_'.$lng.'" class="centocinquanta char_piccolo" maxlength="255" /></div>';
}

$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);

$insTabella = '
<table class="list">
<tr class="bg"><th>Inserimento nuova sottocategoria</th></tr>
<tr><td>
  <form method="post" action="'.$action.'">
  <input type="hidden" value="'.$id.'" />
  '.$txtinserts.'
  <input name="ins" type="submit" value="Aggiungi nuova" class="g-button" title="Aggiungi" style="margin:20px 0 0 20px;" />
  </form>
 </td></tr>
</table>';

$send_nuovo = arr::_unset($backUri, array('id', 'jsstatis'));
$href_new = io::a($scheda->file_c, array_merge($send_nuovo, array('crud' => 'ins')), L_NUOVO , array('title' => 'Aggiungi nuovo record', 'class' => 'g-button'));

$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);
$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);

include_once HEAD_AR;
//print $sub_menu;
# CONFIGURAZIONE 2 DI 2 ##################################################

# HTML RECORD
$qE = "SELECT * FROM descriptors WHERE ID_DESCRIPTORS_TYPE = '".$rec_scheda['ID_DESCRIPTORS_TYPE']."' ORDER BY RANK ASC, DESCRIPTOR_".LANG_DEF." ASC";
$rE = rs::inMatrix($qE);
$ntml['records'] = '';
foreach($rE as $k => $v){
	# CREO GLI INPUT
	$inputs = '';
foreach($lang -> langs as $n => $lng){
		// constant($lng)
		$inputs .= '<div class="box_input"><span>'.constant($lng).':</span>'.BR.'<input type="text" name="ln'.$lng.$v['ID_DESCRIPTOR'].'" class="centocinquanta char_piccolo" maxlength="255" value="'.$v['DESCRIPTOR_'.$lng].'" /></div>';
	}
	$nmr = 'bb'.$v['ID_DESCRIPTOR'];
	$input[$nmr] = new io(); $input[$nmr] -> type = 'hidden'; $input[$nmr] -> maxl = 64; $input[$nmr] -> val = $v['RANK']; $input[$nmr] -> set($nmr);
	ob_start(); $input[$nmr] -> get(); $input_r = ob_get_clean();
	
	# CREO L'HTML
	$ntml['records'] .= '<tr><td>'.$inputs.'</td><td>'.$input_r.'</td></tr>';	

}

# FINE CONFIGURAZIONE 2 DI 2 #############################################

print $insTabella;
?>
  <form method="post" enctype="multipart/form-data" action="<?=$action?>">
  <?=request::hidden($backUri)?>
    <table id="sortable" class="list" >    
      <tr class="bg">      
        <th><?=$href_annulla?> <?//$href_new?></th>
        <th><input name="subDo" type="submit" value="<?=SAVE?>" class="g-button g-button-yellow" /></th>
      </tr>    
	<? if($crud=='upd'){
		print'<tr class="yellow"><td colspan="6"><strong>'.ucfirst($rec_scheda['DESCRIPTORS_TYPE']).'</strong></td></tr>';
	  }
	  ?>
    <tbody class="sorttr">
    <?=$ntml['records']?>
    </tbody>
    <tr>                   
    <th colspan="6"><input name="subDo" type="submit" value="<?=SAVE?>" class="g-button g-button-yellow" />* campi obbligatori
    </th>
    </tr>
    <tr><th colspan="6"><?=count($rE)?> record</th></tr>
  
    </table>
  </form>
<?php
include_once FOOTER_AR;
alert::crud();
?>