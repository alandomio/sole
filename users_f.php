<?php
# V.0.1.8
include_once 'init.php';
$user = new autentica($aA1);
$user -> login_standard();
include_once 'users_conf.php';

$is_swf=0; $is_head_mce=0; $is_multibox=0; $is_js=1;

$aCurs=array("icursor"=>$icursor);
$backUri=array_merge($aCurs,$my_vars->href);
$backUri['id_rec'] = $id_rec;

$send_vars = $_GET;
$send_vars['aa'] = ''; unset($send_vars['aa']);
$send_vars['ab'] = ''; unset($send_vars['ab']);
$send_vars['da'] = ''; unset($send_vars['da']);
$send_vars['db'] = ''; unset($send_vars['db']);

# IMPOSTA IMMAGINE LOGO DEFAULT
if(array_key_exists('aa',$_GET)){
	mysql_query("UPDATE ".$scheda -> file_table." SET IS_LOGO = '0' WHERE ".$scheda -> f_id." = '".$id_rec."'");
	if(mysql_query("UPDATE ".$scheda -> file_table." SET IS_LOGO = '1' WHERE ".$scheda -> f_id." = '".$id_rec."' AND ID_FILE = '".$_GET['aa']."'")){
		io::headto($MYFILE -> file, array_merge($send_vars, array('ack' => 'Hai cambiato immagine per il logo')));
	}
}

# IMPOSTA IMMAGINE BANNER DEFAULT
if(array_key_exists('ab',$_GET)){
	mysql_query("UPDATE ".$scheda -> file_table." SET IS_BANNER = '0' WHERE ".$scheda -> f_id." = '".$id_rec."'");
	if(mysql_query("UPDATE ".$scheda -> file_table." SET IS_BANNER = '1' WHERE ".$scheda -> f_id." = '".$id_rec."' AND ID_FILE = '".$_GET['ab']."'")){
		io::headto($MYFILE -> file, array_merge($send_vars, array('ack' => 'Hai cambiato immagine per il banner')));
	}
}

# ELIMINA IMMAGINE LOGO DEFAULT
if(array_key_exists('da',$_GET)){
	mysql_query("UPDATE ".$scheda -> file_table." SET IS_LOGO = '0' WHERE ".$scheda -> f_id." = '".$id_rec."'");
	io::headto($MYFILE -> file, array_merge($send_vars, array('ack' => 'Nessun logo impostato')));
}

# IMPOSTA IMMAGINE BANNER DEFAULT
if(array_key_exists('db',$_GET)){
	mysql_query("UPDATE ".$scheda -> file_table." SET IS_BANNER = '0' WHERE ".$scheda -> f_id." = '".$id_rec."'");
	io::headto($MYFILE -> file, array_merge($send_vars, array('ack' => 'Nessun banner impostato')));
}

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
	header(HEADER_TO.url::uri($scheda->file_f,array_merge($backUri,array('ack'=>$ack_msg))));
}

if(array_key_exists('subsave',$_POST)){
	$cnt = 0;
	foreach($my_vars->tx as $k => $val){
		$file_rec = rs::rec2arr("SELECT TITLE FROM files WHERE ID_FILE = '$k'");
		if($file_rec['TITLE']!=$val){
			mysql_query("UPDATE files SET TITLE = '".prepare4sql($val)."' WHERE ID_FILE = '$k'");
			$cnt++;
		}
	}
	header(HEADER_TO.url::uri($scheda->file_f,$backUri));
}

$aFromp=array();
$aFiltro=array();
$qTotRec="SELECT * FROM ".$scheda->table;
if($scheda->ext_table != NULL) {
	$qTotRec.="";
}


ob_start();	# INIZIALIZZO IL CODICE JAVASCRIPT PER IL CARICAMENTO MULTIPLO DI FILE
$swf = new swfupload();
$swf->flash_url = JS_SWF."swfupload.swf";
$swf->upload_url = ABS_PATH.'login/users_u.php'; # METTENDO UN URL ASSOLUTO FUNZIONA ANCHE SU IE
$swf->img_pulsante="../img_layout/carica.png";
$swf->post_params = array("id_rec" => $id_rec);

$swf->file_size_limit = LIMITE_PESO_SWF;
$swf->files_types = "*.jpg; *.gif; *.png";
$swf->file_types_description = 'Scegli le immagini da caricare';
$swf->file_upload_limit = $swf_limite;
$swf->file_queue_limit = $swf_limite;
$swf->set2();
$ajax['swf'] = ob_get_clean();
$ajax['swf'] .= '<script src="'.JS_MAIN.'fnc.js" type="text/javascript"></script>';


/*ob_start();	# INIZIALIZZO IL CODICE JAVASCRIPT PER IL CARICAMENTO MULTIPLO DI FILE
$swf = new swfupload();
$swf->flash_url=JS_SWF."../swfupload.swf";
$swf->upload_url="../login/".$scheda->file_u;
$swf->img_pulsante="../library/img_ar/upload.png";
$swf->post_params=array("id_rec" => $_GET['id_rec']);
$swf->file_size_limit = "1,5";
$swf->files_types = "*.jpg";
$swf->file_types_description = CHOOSE_FILE;
$swf->file_upload_limit = $swf_limite;
$swf->file_queue_limit = $swf_limite;
$swf->set();
$ajax['swf'] = ob_get_clean();*/

$href_annulla = io::ahrefcss($scheda->file_l, $val=$backUri, $txt=CANCEL,$js='',$target="",$title=CANCEL, $id='', $css="g-button");
$href_add_file = $scheda->files ? io::ahrefcss($scheda->file_f,$val=$backUri, $txt=ADD_FILE,$js='',$target="",$title=ADD_FILE, $id='', $css="puls_aggiungi") : '';

$sub_menu = '';
foreach($sub_nav as $k => $arr){
	$href_to='';
	if(in_array(FILENAME.'.php',$arr)) { $sub_menu.='<div class="menu_dis"><span>'.$k.'</span></div>'; }
	elseif($crud=='ins') { $sub_menu.='<div class="menu_att_dis">'.$k.'</div>'; }
	else {
		$href_to = io::ahrefcss($arr[0],$val=array_merge($backUri,array('crud'=>'upd')), $txt=$k,$js='',$target="",$title=strtolower($k), $id='', $css="");
		$sub_menu.='<div class="menu_att"><span>'.$href_to.'</span></div>';
	}
}
$sub_menu = '<div id="menu_bar">'.$sub_menu.'</div>';

include_once HEAD_AR;
print $sub_menu;
$html='';

$q="SELECT
files.ID_FILE,
files.TYPE,
files.TITLE,
files.DESCRIP,
files.PATH,
users_files.IS_LOGO,
users_files.IS_BANNER
FROM
users_files
Left Join files ON users_files.ID_FILE = files.ID_FILE
WHERE
users_files.ID_USER =  '".$id_rec."'
AND files.TYPE='i' ORDER BY users_files.IS_LOGO DESC, users_files.IS_BANNER DESC";

$img_list='';
$rs=rs::inMatrix($q);

$sendVars = $_GET;
$sendVars['err'] = ''; unset($sendVars['err']);
$sendVars['ack'] = ''; unset($sendVars['ack']);

foreach($rs as $rec){
	$aLogo = io::href($MYFILE -> file, array_merge($sendVars, array('aa' => $rec['ID_FILE'])), '<img src="'.IMG_LAYOUT.'freccia_left.gif" class="arrow_left" alt="Seleziona"> Imposta come logo', $js, $target="", $title="Imposta come immagine del logo aziendale",$id="",$class="");
	$aBanner = io::href($MYFILE -> file, array_merge($sendVars, array('ab' => $rec['ID_FILE'])), '<img src="'.IMG_LAYOUT.'freccia_left.gif" class="arrow_left" alt="Seleziona"> Imposta come banner', $js, $target="", $title="Imposta come immagine del banner",$id="",$class="");
	
	$aLogo = $rec['IS_LOGO'] == '1' ? '<span class="is_current">Logo corrente '.io::href($MYFILE -> file, array_merge($sendVars, array('da' => $rec['ID_FILE'])), '[rimuovi]', $js, $target="", $title="Rimuovi logo",$id="",$class="").'</span>' : $aLogo;
	$aBanner = $rec['IS_BANNER'] == '1' ? '<span class="is_current">Banner corrente  '.io::href($MYFILE -> file,  array_merge($sendVars, array('db' => $rec['ID_FILE'])), '[rimuovi]', $js, $target="", $title="Rimuovi banner",$id="",$class="").'</span>' : $aBanner; 

	
	$img_list .= '
	<div class="img_gallery_riga_scheda">
	<input type="checkbox" name="ck'.$rec['ID_FILE'].'" value="1" class="checkbox" />
	<a href="'.$scheda->img_alb_web.$rec['PATH'].'" alt="'.$rec['TITLE'].'" target="_blank"><img src="'.$scheda->img_alb_sqr.$rec['PATH'].'" width="60" alt="'.$rec['TITLE'].'" /></a>
	<span>
	'.$aLogo.'<br />
	'.$aBanner.'
	</span></div>';
	
}
//$img_list = strlen($img_list)>0 ? '<h2>'.GEST_IMG.'</h2>'.$img_list : '';

$href_lista = io::ahrefcss($scheda->file_l,$val=$backUri, $txt=LISTA,$js='',$target="",$title=LISTA, $id='', $css="g-button");
$href_modifica = io::ahrefcss($scheda->file_c,$val=array_merge($backUri,array('crud' => 'upd')), $txt=EDIT,$js='',$target="",$title=EDIT, $id='', $css="g-button");
//$swf->test();
?>

<div id="left_prodotti">
  <form method="post" name="list">
    <input type="hidden" name="icursor" value="0">
    <table>
      <tr valign="bottom" class="bg" >
        <th valign="middle"><?=$href_lista?></th>
        <th valign="middle"><span class="float_right">
          <input name="subdel" type="submit" value="<?=DELETE_SELECTED?>" class="button_elimina">
          </span></th>
      </tr>
      <? if($crud=='upd'){
	  print'
      <tr class="yellow">      
        <td colspan="2"><strong>'.$etichetta.'</strong></td>    
      </tr>';}
	  ?>
      <tr>
        <th> <input type="checkbox" class="checkbox" onchange="javascript:check_all_by_name('list', 'ck')" id="sel_tutti" name="ck" value="1" />
          <span class="a_sel_tutti">
          <?=SEL_ALL?>
          </span></th>
        <th>&nbsp;</th>
      </tr>
      <tr class="colour">
        <td valign="top"<?php print in_array('files', $scheda->files) ? '' : ' colspan="2"';?>><h2>
            <?=GEST_IMG?>
          </h2>
          <div class="overflow">
            <?=$img_list?>
            <div class="clear"></div>
          </div>
          <div class="float_right" style=" padding:0 5px 0 0;">
            <input type="submit" name="subsave" class="button_salva" value="salva modifiche" />
          </div></td>
        <?php
if(in_array('files', $scheda->files)){
print '<td valign="top"><h2>'.GEST_DOC.'</h2>
	<div class="img_sel_scheda">'.$doc_list.'
	</div></td>';
}
?>
      </tr>
    </table>
    <?php
if($swf_limite<=0){
	print '<p>Hai raggiunto il limite massimo di immagini inseribili</p>';
}else{ ?>
  </form>
  <table>
    <tr class="tab_noover">
      <td valign="top"><h2 style=" margin:0 0 0 10px;">Aggiungi allegati alla scheda</h2>
        <?php
include JS_SWF.'form.php';
?>
      </td>
      <td valign="top">&nbsp;</td>
    </tr>
  </table>
  </form>
  <?php } ?>
</div>
<?php
include_once FOOTER_AR;
alert::crud();
?>
