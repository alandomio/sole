<?php
include_once 'init.php';
$user = new autentica($aA3);
$user -> login_standard();

include_once stringa::get_conffile($MYFILE -> filename);

$is_swf=0; $is_head_mce=0; $is_multibox=0; $is_js=1;

$aCurs=array("icursor"=>$icursor);
$backUri=array_merge($aCurs,$my_vars->href);

if(array_key_exists('subdel',$_POST)){
$cnt = 0;
	foreach($my_vars->ck as $id_del){
		if(!empty($id_del)){
			$q = "SELECT * FROM files WHERE ID_FILE = '$id_del'";
			$rec = rs::rec2arr($q);
			$file = $rec['PATH'];
			if($rec['TYPE'] == 'i'){ // ELIMINO LE IMMAGINI
				if(is_file($scheda->img_alb_thu.$file)) unlink($scheda->img_alb_thu.$file);
				if(is_file($scheda->img_alb_web.$file)) unlink($scheda->img_alb_web.$file);
				if(is_file($scheda->img_alb_big.$file)) unlink($scheda->img_alb_big.$file);
			}
			elseif($rec['TYPE'] == 'f'){ // ELIMINO I FILE
				if(is_file($scheda->file_atc.$file)) unlink($scheda->file_atc.$file);
			}
			# AGGIORNAMENTO DB
			mysql_query("DELETE FROM files WHERE ID_FILE = '$id_del'");
			mysql_query("DELETE FROM ".$scheda->file_table." WHERE ID_FILE = '$id_del'");
		}
	$cnt++;
	}
	$ack_msg =  $cnt>0 ? $cnt.' '.FILE_DELETED : '';
	$_GET['ack'] = ''; unset($_GET['ack']);
	$_GET['err'] = ''; unset($_GET['err']);
	
	header(HEADER_TO.url::uri($scheda->file_f, array_merge($_GET,array('ack'=>$ack_msg))));
}


if(array_key_exists('subsave',$_POST)){
	if(array_key_exists('is_main',$_POST)){ # RESETTO LA VECCHIA IMMAGINE PRINCIPALE E ASSEGNO LA NUOVA
		$is_m = $_POST['is_main'];
		$is_p = rs::rec2arr("SELECT IS_PRINCIPALE FROM ".$scheda->file_table." WHERE ID_FILE = '$is_m' LIMIT 0,1");
		if(empty($is_p['IS_PRINCIPALE'])){
			mysql_query("UPDATE ".$scheda->file_table." SET IS_PRINCIPALE = '0' WHERE ".$scheda->f_id." = '$id'");
			if(mysql_query("UPDATE ".$scheda->file_table." SET IS_PRINCIPALE = '1' WHERE ID_FILE = '$is_m'")){
				$MYFILE -> add_ack('Aggiornata l\'mmagine principale');
			}
		}
	}

	$cnt = 0;
	foreach($my_vars -> tx as $k => $val){
		$file_rec = rs::rec2arr("SELECT TITLE FROM files WHERE ID_FILE = '$k'");
		if($file_rec['TITLE'] != $val){
			if(mysql_query("UPDATE files SET TITLE = '".prepare4sql($val)."' WHERE ID_FILE = '$k'")) $cnt++;
		}
	}
	if($cnt > 0){ $MYFILE -> add_ack('Aggiornati '.$cnt.' testi'); }
	
	
	# ORDINAMENTO CAMPI
	$cnt = 0; $iord = 1;
	foreach($my_vars -> ba as $k => $val){
		$file_rec = rs::rec2arr("SELECT RANK FROM ".$scheda->file_table." WHERE ID_FILE = '$k'");
		if($file_rec['RANK'] != $iord){
			if(mysql_query("UPDATE ".$scheda -> file_table." SET RANK = '$iord' WHERE ID_FILE = '$k'")) $cnt++;
		}
		$iord++;
	}
	if($cnt > 0){ $MYFILE -> add_ack('Ordine modificato'); }
}


$aFromp=array();
$aFiltro=array();
$qTotRec="SELECT * FROM ".$scheda->table;
if($scheda->ext_table != NULL) {
	$qTotRec.="";
}

$add_file_list = '';
if(in_array('files', $scheda -> files)){ $add_file_list .= '*.doc; *.xls; *.pdf; '; }
if(in_array('images', $scheda -> files)){ $add_file_list .= '*.jpg; *.gif; *.png; ';}
$add_file_list = stringa::togli_ultimi($add_file_list, 2);

ob_start();	# INIZIALIZZO IL CODICE JAVASCRIPT PER IL CARICAMENTO MULTIPLO DI FILE
$swf = new swfupload();
$swf->flash_url = JS_SWF."swfupload.swf";
$swf->upload_url = ABS_PATH.$scheda -> file_u; # METTENDO UN URL ASSOLUTO FUNZIONA ANCHE SU IE
$swf->img_pulsante = IMG_AR."carica.png";
$swf -> aPostp = array("id" => $id);
$swf->file_size_limit = LIMITE_PESO_SWF;
$swf->files_types = $add_file_list;
$swf->file_types_description = 'Scegli i file da caricare';
$swf->file_upload_limit = $swf_limite;
$swf->file_queue_limit = $swf_limite;
//$swf->debug = 'true';
$swf->set_gradient();

$MYFILE -> add_js($swf -> jsc,'code', 'head');
$MYFILE -> add_js($swf -> jsf,'file', 'head');
$MYFILE -> add_css($swf -> css);

$href_annulla = io::ahrefcss($scheda->file_l, $val=$backUri, $txt=CANCEL,$js='',$target="",$title=CANCEL, '', $css="g-button");
$href_add_file = $scheda->files ? io::ahrefcss($scheda->file_f,$val=$backUri, $txt=ADD_FILE,$js='',$target="",$title=ADD_FILE, '', $css="puls_aggiungi") : '';

$sub_menu = '';
foreach($sub_nav as $k => $arr){
	$href_to='';
	if(in_array(FILENAME.'.php',$arr)) { $sub_menu.='<div class="menu_dis"><span>'.$k.'</span></div>'; }
	elseif($crud=='ins') { $sub_menu.='<div class="menu_att_dis">'.$k.'</div>'; }
	else {	
		$backUri['id'] =$id;
		$href_to = io::a($arr[0], array_merge($backUri,array('crud'=>'upd')),  $k, array('title' => $title=strtolower($k)));
		$sub_menu.='<div class="menu_att"><span>'.$href_to.'</span></div>';
	}
}
$sub_menu = '<div id="menu_bar">'.$sub_menu.'</div>';

$html='';
$q = "SELECT files.*,
".$scheda->file_table.".*
FROM
".$scheda->file_table."
Left Join files ON ".$scheda->file_table.".ID_FILE = files.ID_FILE
WHERE
".$scheda->file_table.".".$scheda->f_id." = '$id'
";

$doc_list='';
$rs=rs::inMatrix($q." AND files.TYPE='f'"); # LISTA FILE

foreach($rs as $rec){
$filesize = filesize($scheda->file_atc.$rec['PATH'])/1024;
$doc_list.='<li><input type="checkbox" name="ck'.$rec['ID_FILE'].'" value="1" class="checkbox" />';
$doc_list.='<input name="tx'.$rec['ID_FILE'].'" type="text" maxlenght="32" value="'.$rec['TITLE'].'" />';
$doc_list.='<a href="'.$scheda->file_atc.$rec['PATH'].'" class="href" target="_blank">'.OPEN.' ('.strtolower(strrcut($rec['PATH'],'',3)).' '.num::format($filesize,0,'','').' Kb)</a></li>';
}
$doc_list = strlen($doc_list)>0 ? '<ul id="elenco_documenti">'.$doc_list.'</ul>' : '';

$img_list = ''; $sort_list = '';

$rs=rs::inMatrix($q." AND files.TYPE='i' ORDER BY ".$scheda->file_table.".RANK ASC"); # LISTA IMMAGINI
foreach($rs as $rec){

	$ckprinc = $rec['IS_PRINCIPALE'] == '1' ? ' checked="checked"' : ''; 
	$input['is_principale'] = '<input type="radio" name="is_main" value="'.$rec['ID_FILE'].'"'.$ckprinc.'> Principale';

	$img_list.='<div class="img_gallery_riga_scheda">
	<input type="hidden" name="ba'.$rec['ID_FILE'].'" value="1" />
	<input type="checkbox" name="ck'.$rec['ID_FILE'].'" value="1" class="checkbox" />
	<a href="'.$scheda->img_alb_web.$rec['PATH'].'" alt="'.$rec['TITLE'].'" target="_blank"><img src="'.$scheda->img_alb_sqr.$rec['PATH'].'" alt="'.$rec['TITLE'].'" width="60"></a>
	<span>
	Alt image: <input name="tx'.$rec['ID_FILE'].'" type="text" maxlenght="32" value="'.$rec['TITLE'].'" />
	<br />'.$input['is_principale'].'
	</span>
	</div>';
}

if(!empty($img_list)){
	$img_list = '
	<div id="lista_img">
	<h2>Modifica informazioni immagini, trascina per ordinarle</h2>
	'.$img_list.'
	</div>
	';
}

include_once HEAD_AR;
print $sub_menu;
$href_lista = io::a($scheda->file_l, $backUri, LISTA, array('class' => 'g-button'));
?>
<form method="post" name="list" id="dd-form">
<input type="hidden" name="sort_order" id="sort_order" value="" />
<table class="list">
<tr class="bg" >
<th><?=$href_lista?> 
<input type="checkbox" class="checkbox" onchange="javascript:check_all_by_name('list', 'ck')" id="sel_tutti" name="ck" value="1" /><?=SEL_ALL?> <input name="subdel" type="submit" value="<?=DELETE_SELECTED?>" class="g-button g-button-red" /> <input type="submit" name="subsave" class="g-button g-button-yellow" value="<?=SAVE?>" />
</th>
</tr>
<?php # STAMPA TITOLO INTESTAZIONE
print $crud == 'upd' ? '<tr class="yellow"><td colspan="2"><div class="table_cell"><strong>'.$etichetta.'</strong></div></td></tr>' : '';
?>
<tr class="colour">
  <td valign="top"<?php print in_array('files', $scheda->files) ? ' class="c70"' : ' colspan="2" class="c100"';?>>
  <div class="overflow">
    <?=$img_list?>    
    <div class="clear"></div>
  </div>
<div class="float_right" style=" padding:0 5px 0 0;"></div>
</td>
<?php
if(in_array('files', $scheda->files)){
print '<td valign="top" ><h2>'.GEST_DOC.'</h2>
	<div class="img_sel_scheda">'.$doc_list.'
	</div></td>';
}
?>
</tr>

</table>
<?php
if($scheda->swf_limit<=0){
	print '<p>Hai raggiunto il limite massimo di immagini inseribili</p>
	</form>';
}else{ ?>
<table>
<tr class="tab_noover">
<td valign="top">
<?php
include JS_SWF.'form.php';
?>
</td>
  <td valign="top">&nbsp;</td>
</tr>
</table>
</form>
<?php 
}
include_once FOOTER_AR;
?>