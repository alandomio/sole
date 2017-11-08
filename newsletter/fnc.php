<?php
ini_set('display_errors',"1");
// Funzioni newsletter
function post_nwl($a){
	$a['err'] = array_key_exists('err',$a) ? $a['err'] : '';
	$a['ins_mod_titolo'] = array_key_exists('ins_mod_titolo',$a) ? $a['ins_mod_titolo'] : '';
	$a['ins_mod_descrizione'] = array_key_exists('ins_mod_descrizione',$a) ? $a['ins_mod_descrizione'] : '';
	return $a;
}

// Crea un nuovo record senza dati
function nwl_create($redirect){
	$ret=false;
	$q="INSERT INTO nwl (DT_INS) values ('".date("Y-m-d H:i:s")."')";
	if(mysql_query($q)){
		$ret = mysql_insert_id();
	};
	if($redirect==1){
	header(HEADER_TO."nwl_list.php");
	}
	return $ret;
}

// Modifica titolo e descrizione alla newsletter
function nwl_txt($a){
	$aVar=chkText($a['ins_mod_titolo'], 0, 64, true, true, 'titolo');
	$a['titolo']=$aVar[0];
	$a['err'].=$aVar[1];

	$aVar=chkText($a['ins_mod_descrizione'], 0, 4048, true, false, 'descrizione');
	$a['descrizione']=$aVar[0];
	$a['err'].=$aVar[1];
	
/*	if(is_file($_FILES['nw_img']['tmp_name'])){ // INSERIMENTO/SOSTITUZIONE/ELIMINAZIONE IMMAGINE PRINCIPALE
	$aJpgErr = chkjpg($_FILES['nw_img'], (1.5*1048576));
	if(empty($aJpgErr)){ 
		ini_set('memory_limit','120M');
		$imgName = date('YmdHis',time()).".jpg";
		$save_main = move_uploaded_file($_FILES['nw_img']['tmp_name'],IMG_NEWSLETTER_MAIN.$imgName) ? true : false;
		$save_thu = mkSqrjpg(IMG_NEWSLETTER_MAIN.$imgName, IMG_NEWSLETTER_THU.$imgName, 70, 85,0) ? true : false; }
		
		if($save_main && $save_thu) {
			$dele = rs::rec2arr("SELECT * FROM nwl WHERE ID_NWL='".$a['id']."'");
		
			if(is_file(IMG_NEWSLETTER_MAIN.$dele['PATH'])) unlink(IMG_NEWSLETTER_MAIN.$dele['PATH']);
			if(is_file(IMG_NEWSLETTER_THU.$dele['PATH'])) unlink(IMG_NEWSLETTER_THU.$dele['PATH']);
			mysql_query("UPDATE nwl SET PATH = '$imgName' WHERE ID_NWL = '".$a['id']."'");
		}
	}*/
	
	$q="UPDATE nwl SET TITOLO='".prepare4sql($a['titolo'])."', DESCRIZIONE='".prepare4sql($a['descrizione'])."' WHERE ID_NWL = '".$a['id']."'";
	mysql_query($q);
	//header(HEADER_TO."nwl_list.php?ack=".rawurlencode('modifica eseguita'));
}

function nwl_delete($id){
	mysql_query("DELETE FROM nwl_mails WHERE ID_NWL='$id'");
	mysql_query("DELETE FROM nwl_offers WHERE ID_NWL='$id'");
	mysql_query("DELETE FROM nwl WHERE ID_NWL='$id'");
	header(HEADER_TO."nwl_list.php?ack=".rawurlencode('Newsletter eliminata'));
}

function is_newsletter(){
	$ret=false;
	$rs=mysql_query("SELECT * FROM nwl");
	if($tot=mysql_num_rows($rs)>0) $ret=$tot;
	return $ret;
}

function nwl_count_contatti($id_nwl){
	$tot = rs::rec2arr("SELECT Count('*') AS TOT FROM nwl_mails WHERE nwl_mails.ID_NWL = '$id_nwl' GROUP BY nwl_mails.ID_NWL");
	$contatti = !empty($tot['TOT']) ? $tot['TOT'] : 0;
	return $contatti;
}

function nwl_count_contatti_attivi($id_nwl){
	$tot = rs::rec2arr("SELECT Count('*') AS TOT FROM nwl_mails WHERE nwl_mails.ID_NWL = '$id_nwl' AND IS_SEND='0' GROUP BY nwl_mails.ID_NWL");
	$contatti = !empty($tot['TOT']) ? $tot['TOT'] : 0;
	return $contatti;
}


function nwl_count_news($id_nwl){
	$tot = rs::rec2arr("SELECT Count('*') AS TOT FROM nwl_offers WHERE nwl_offers.ID_NWL = '$id_nwl' GROUP BY nwl_offers.ID_NWL");
	$news = !empty($tot['TOT']) ? $tot['TOT'] : 0;
	return $news;
}

// paginazione
function nwl_list($icursor, $where, $player){
	$html='<form method="post">
	<input name="new_nwl" type="hidden" value="1">
	<table class="tbl_griglia">
	<tr class="bg"><th>
<input type="submit" value="nuovo" class="button_nuovo" />
</th><th>crea senza testo<input name="new_no_txt" type="checkbox" value="1"></th><th colspan="6"></th></tr>
<tr>
	<th>DATA CREAZIONE</th>
	<th>DATA INVIO</th>
	<th>TITOLO E DESCRIZIONE</th>
	<th>INFO</th>
	<th><div align="center">ANTEPRIMA</div></th>
	<th>MODIFICA</th>
	<th align="right">INVIO</th>
	<th align="right">&nbsp;</th>
</tr>';
	$a=rs::inMatrix("SELECT * FROM nwl $where ORDER BY ID_NWL DESC LIMIT ".($icursor*NWL_OFFSET).",".NWL_OFFSET);
	foreach($a as $row){
		$nwl_contatti=nwl_count_contatti($row['ID_NWL']);
		$nwl_contatti_attivi=nwl_count_contatti_attivi($row['ID_NWL']);
		$nwl_news=nwl_count_news($row['ID_NWL']);
		$titolo = strlen(strip_tags($row['TITOLO']))>0 ? strip_tags($row['TITOLO']) : '----';
		$descrizione = strlen(strip_tags($row['DESCRIZIONE']))>0 ? strcut(strip_tags($row['DESCRIZIONE']),'...',100) : '----';
		$dt_ins=dtime::my2isodts($row['DT_INS']);
		$dt_send=dtime::my2isodts($row['DT_SEND']);

		if(empty($dt_send)){ $is_send=0; }
		else{ $is_send=1; }
		
		if($is_send==1){
			$puls_invia_nwl = '<a href="#" onclick="alert(\'Newsletter già spedita\')" class="puls_mail_text">invia</a>';
		}
		//elseif($nwl_contatti == 0 || $nwl_news ==0){
		elseif($nwl_contatti == 0){
			$puls_invia_nwl = '<a href="#" onclick="alert(\'Inserire annunci e contatti\')" class="puls_mail_text">invia</a>';
		}
		else{
		$puls_invia_nwl = '<a href="nwl_send.php?id='.$row['ID_NWL'].'" onclick="if(confirm(\'Questa newsletter sar&agrave; inviata a '.$nwl_contatti_attivi.' contatti.\nSei sicuro?\')) return true; else return false; " class="puls_mail_text">invia</a>';
		}
		
$html.='
<tr valign="top">
	<td>'.$dt_ins.'</td>
	<td>'.$dt_send.'</td>
	<td><strong>'.$titolo.'</strong><br />
	'.$descrizione.'
	</td>
	<td>contatti: '.$nwl_contatti.'<br />annunci: '.$nwl_news.'</td>
	<td align="center"><a href="'.SYSTEM_PATH.'newsletter.php?id='.$row['ID_NWL'].'" target="_blank" >anteprima</a></td>
	<td>
	<a href="nwl_mod.php?id='.$row['ID_NWL'].'" class="puls_modifica_text">mod.</a>
	<a href="nwl_add.php?id='.$row['ID_NWL'].'" class="puls_news_text">annunci</a></td>
	<td align="right"><a href="nwl_contacts.php?id='.$row['ID_NWL'].'" class="puls_gestcontatti_text">Contatti</a><br />
	'.$puls_invia_nwl.'</td>
	<td align="right" valign="middle"><a href="nwl_list.php?del='.$row['ID_NWL'].'" onclick="if(confirm(\'Procedere con la cancellazione?\')) return true; else return false; " class="puls_cancella_solo"></a></td>
</tr>';	
	}
	if(empty($a)){
$html.='<tr valign="top">
  <td colspan="8"><center>Nessuna newsletter a sistema</center></td>
</tr>';
	}
$html.='<tr><th colspan="8">'.$player.'</th></tr>
</table></form>';
print $html;
}

function nwl_news_list($icursor, $where, $id, $player){
	$ids='';
	$cnt=1;
	$html='<form method="post" name="frm_add">
<table class="tbl_griglia">
<tr class="bg">
	<th colspan="3"><a href="nwl_list.php" title="indietro" class="puls_back" >indietro</a></th>
    </tr>
<tr>
  <th colspan="3">seleziona / deseleziona tutti<input name="sel" type="checkbox" value="1" onchange="javascript:check_all(\'frm_add\', \'sel\')" class="checkbox" /></th>
  </tr>';
	$a=rs::inMatrix("SELECT * FROM offers $where ORDER BY ID_OFFER DESC LIMIT ".($icursor*NEWS_OFFSET).",".NEWS_OFFSET);
	foreach($a as $row){
	
	$q="SELECT
		files.ID_FILE,
		files.TYPE,
		files.TITLE,
		files.DESCRIP,
		files.PATH,
		offers_files.IS_PLANIMETRIA,
		offers_files.IS_PRINCIPALE
		FROM
		offers_files
		Left Join files ON offers_files.ID_FILE = files.ID_FILE
		WHERE
		offers_files.ID_OFFER =  '".$row['ID_OFFER']."' ORDER BY IS_PRINCIPALE DESC LIMIT 0,1";
	$aImage = rs::rec2arr($q);
	$img = strlen($aImage['PATH']) > 0 ? '<img src="'.IMG_ALB_THU.$aImage['PATH'].'" align="left" />' : '';
	
	$ids.= $ids=='' ? $row['ID_OFFER'] : '|'.$row['ID_OFFER'];
	//$img = strlen($row['PATH']) > 0 ? IMG_MAIN_WEB.$row['PATH'] : THU_JOLLY;
	$checked = is_nwl_news($row['ID_OFFER'], $id) ? 'checked="checked"' : '';
	$html.='
<tr>
  <td><input name="'.$row['ID_OFFER'].'" type="checkbox" value="1" '.$checked.' /></td>
  <td>'.$img.'<strong>'.$row['RIF'].'</strong><br />
  '.strcut(strip_tags($row['DESCRIP']),'[...]',200).'</td>
  </tr>';
	$cnt++;
	}
	if(empty($a)){
$html.='<tr valign="top">
  <td colspan="3"><center>Nessuna news a sistema</center></td>
</tr>';
	}
/*	else{
	$html.='<script type="text/javascript">
var banner = {};
window.addEvent(\'domready\', function(){
banner = new MultiBox(\'preview\', {descClassName: \'multiBoxDesc\', useOverlay: true, showControls: false});
});
</script>';}*/

$html.='
<tr><td colspan="3"><input type="submit" class="button_salva" value="salva"></td></tr>
<tr><th colspan="3">'.$player.'</th></tr>

</table>
<input name="ids" type="hidden" value="'.$ids.'" />
<input name="id" type="hidden" value="'.$id.'" />
</form>';
print $html;
}

function is_nwl_news($id, $nwl_id){
	$rs=mysql_query("SELECT * FROM nwl_offers WHERE ID_OFFER='$id' AND ID_NWL='$nwl_id'");
	return mysql_num_rows($rs)>0 ? true : false;
}

function is_nwl_contacts($id, $nwl_id){
	$rs=mysql_query("SELECT * FROM nwl_mails WHERE ID_MAIL='$id' AND ID_NWL='$nwl_id'");
	return mysql_num_rows($rs)>0 ? true : false;
}

function is_send($id_nwl, $id_outlet){
	$q="SELECT * FROM nwl_mails WHERE ID_NWL='$id_nwl' AND ID_MAIL='$id_outlet' AND IS_SEND='1'";
	$rs=mysql_query($q);
	return mysql_num_rows($rs)>0 ? true : false;
}

function nwl_add_news($a){
	$aId = explode('|',$a['ids']);
	$added=0;
	$removed=0;
	foreach ($aId as $id){
		if(array_key_exists($id, $a)){
			$rs=mysql_query("SELECT * FROM nwl_offers WHERE ID_NWL='".$a['id']."' AND ID_OFFER='$id'");
			if(mysql_num_rows($rs)==0) {
				mysql_query("INSERT INTO nwl_offers (ID_NWL, ID_OFFER) VALUES ('".$a['id']."' ,'$id')");
				$added++;
			}
		}
		else{
			$rsDel=mysql_query("SELECT * FROM nwl_offers WHERE ID_NWL='".$a['id']."' AND ID_OFFER='$id'");
			if(mysql_num_rows($rsDel)>0) {
				mysql_query("DELETE FROM nwl_offers WHERE ID_NWL='".$a['id']."' AND ID_OFFER='$id'");
				$removed++;
			}
		}
	}
	//header(HEADER_TO."nwl_add.php?id=".$a['id']."&ack=".rawurlencode("Aggiunte $added news e rimosse $removed news"));
	return "Aggiunti $added annunci e rimossi $removed annunci";
}

function nwl_add_contatti($a){
	$aId = explode('|',$a['ids']);
	$added=0;
	$removed=0;
	foreach ($aId as $id){
		if(array_key_exists('riv_'.$id, $a)|| array_key_exists('nwl_'.$id, $a)){
			$rs=mysql_query("SELECT * FROM nwl_mails WHERE ID_NWL='".$a['id']."' AND ID_MAIL='$id'");
			if(mysql_num_rows($rs)==0) {
				if(mysql_query("INSERT INTO nwl_mails (ID_NWL, ID_MAIL) VALUES ('".$a['id']."' ,'$id')")) {$added++;};
			}
		}
		else{
			$rsDel=mysql_query("SELECT * FROM nwl_mails WHERE ID_NWL='".$a['id']."' AND ID_MAIL='$id'");
			if(mysql_num_rows($rsDel)>0) {		
				if(mysql_query("DELETE FROM nwl_mails WHERE ID_NWL='".$a['id']."' AND ID_MAIL='$id'")) {$removed++;};
			}
		}
	}
	return "Aggiunte $added e rimosse $removed email";
}

function nwl_contatti_list($id){
	$ids='';
	$list_riv='<td>'; $list_knw='<td>'; $list_nwl='<td>';
	
	$aRiv=rs::inMatrix("SELECT * FROM mails");
	foreach($aRiv as $riv){
		$ids.= $ids=='' ? $riv['ID_MAIL'] : '|'.$riv['ID_MAIL'];
		$checked = is_nwl_contacts($riv['ID_MAIL'], $id) ? ' checked="checked"' : '';
		$email_send = is_send($id, $riv['ID_MAIL']) ? '<img src="'.SYSTEM_PATH.'img_ar/send_mail.gif" width="20" height="16" alt="mail inviata" />' : '';
		$list_riv.=$email_send.'<input name="riv_'.$riv['ID_MAIL'].'" type="checkbox"'.$checked.'> '.$riv['EMAIL'].'<br />';
	}
	
/*	$aNwl=rs::inMatrix("SELECT * FROM outlets WHERE (EMAIL IS NOT NULL OR EMAIL <> '') AND IS_HISTORIC='0' AND IS_NEWSLETTER='1' AND IS_BRANCH='0'");
	foreach($aNwl as $nwl){
		$ids.= $ids=='' ? $nwl['ID_MAIL'] : '|'.$nwl['ID_MAIL'];
		$checked = is_nwl_contacts($nwl['ID_MAIL'], $id) ? ' checked="checked"' : '';
		$email_send = is_send($id, $nwl['ID_MAIL']) ? '<img src="'.SYSTEM_PATH.'img_ar/send_mail.gif" width="20" height="16" alt="mail inviata" />' : '';
		$list_nwl.=$email_send.'<input name="nwl_'.$nwl['ID_MAIL'].'" type="checkbox"'.$checked.'> '.$nwl['EMAIL'].'<br />';
	}*/
	
	$list_riv.='</td>'; $list_knw.='</td>'; $list_nwl.='</td>';
	
	$html='<form name="frm" method="post">

<table border="0" class="tabella_ar_utenti">
<tr class="bg"><th>
<a href="nwl_list.php" title="indietro" class="puls_back">indietro</a><input type="submit" value="salva" class="button_salva" /></th></tr>
<tr>
	<th><input type="checkbox" value="1" name="riv" onchange="javascript:check_all_by_name(\'frm\', \'riv\')" />Seleziona tutti</th>
</tr><tr valign="top">';

$html.=$list_riv.$list_nwl;

$html.='</tr>
</table>
<input name="ids" type="hidden" value="'.$ids.'" />
<input name="id" type="hidden" value="'.$id.'" />
</form>';
print $html;
}

function newsletter_mail($id){
//	$where="";
	$html='
<table>';
	$a=rs::inMatrix("SELECT * FROM offers ORDER BY ID_OFFER DESC");
	foreach($a as $row){
	$img = strlen($row['PATH']) > 0 ? ABS_PATH.IMG_MAIN_WEB.$row['PATH'] : ABS_PATH.THU_JOLLY;
	$checked = is_nwl_news($row['ID_OFFER'], $id) ? 'checked="checked"' : '';
	$html.='
<tr>
  <td><img src="'.$img.'" width="53" align="left" /><strong>'.$row['TITOLO'].'</strong><br />
  '.strcut(strip_tags($row['DESCRIZIONE']),'[...]',140).'
  <a href="'.ABS_PATH.'news.php?id_news='.$row['ID_OFFER'].'" target="_blank">vedi sul sito</a></td>
</tr>';
	}
$html.='
</table>
';
print $html;
}

function nwl_avviso_html($id){
	$scheda=rs::rec2arr("SELECT offers.ID_OFFER, offers.RIF, offers.ADDRESS, offers.CIVIC, offers.PRICE, offers.MQ, offers.IS_WEB, offers.IS_HOME, offers.IS_ELEVATOR, offers.DESCRIP, offers.N_ROOMS, offers.N_BATHS, offers.AMMINISTRATORE, offers.PROPRIETARIO, offers.INQUILINO, offers.NOTE_MAIN, offers.D_INCARICO, offers.D_SCADENZA, offers.ID_CATEG, offers.ID_CITY, offers.ID_CONTRACT, offers.ID_ZONE, offers.ID_TYPE, offers.DT_INS, types.ID_TYPE, types.TYPE, types.IS_DEFAULT, categs.ID_CATEG, categs.CATEG, categs.IS_DEFAULT, contracts.ID_CONTRACT, contracts.CONTRACT, contracts.IS_DEFAULT, citys.ID_CITY, citys.CITY, citys.IS_DEFAULT, zones.ID_ZONE, zones.ZONE, zones.IS_DEFAULT FROM offers Left Join types ON offers.ID_TYPE = types.ID_TYPE Left Join categs ON offers.ID_CATEG = categs.ID_CATEG Left Join contracts ON offers.ID_CONTRACT = contracts.ID_CONTRACT Left Join citys ON offers.ID_CITY = citys.ID_CITY Left Join zones ON offers.ID_ZONE = zones.ID_ZONE WHERE ID_OFFER = '$id' LIMIT 0, 1");
	# GESTISCO I LINK DELLE OFFERTE	
	if($scheda['ID_TYPE']<5 && $scheda['ID_CONTRACT'] == 2) { $file = ABS_PATH.'dettaglio_appartamenti_in_vendita_a_trieste.php'; $m=0; }# VENDITA
	if($scheda['ID_TYPE']<5 && $scheda['ID_CONTRACT'] == 1) { $file = ABS_PATH.'dettaglio_appartamenti_affitto_trieste.php'; $m=1;}# AFFITTO
	if($scheda['ID_TYPE']==5 && $scheda['ID_CONTRACT'] == 2){ $file = ABS_PATH.'dettaglio_casa_in_vendita_a_trieste.php'; $m=2;}# VENDITA
	if($scheda['ID_TYPE']==5 && $scheda['ID_CONTRACT'] == 1){ $file = ABS_PATH.'dettaglio_casa_affitto_trieste.php'; $m=3;}# VENDITA
	# VARIABILI FILTRO
	$get_list = array('slty'=>$scheda['ID_TYPE'],'vaiw'=>'1','slct'=>$scheda['ID_CONTRACT'],'id_rec'=>$scheda['ID_OFFER'],'m'=>$m);
	# HTML SCHEDA
	$rif = (!empty($scheda['RIF'])) ? ' rif. '.$scheda['RIF'] : '';
	$titolo = ucfirst($scheda['TYPE'].' in '.$scheda['CONTRACT'].$rif);
	$html_title = ucfirst($scheda['CONTRACT'].' di '.$scheda['TYPE'].' a Trieste, '.$rif);
	$prezzo = (!empty($scheda['PRICE'])) ? ' '.num::formatnum($scheda['PRICE'],0).' &euro;' : 'Trattativa riservata';
	$descrizione = strcut(strip_tags($scheda['DESCRIP']),'...',150);
	# IMMAGINE PRINCIPALE
	$q="SELECT
	files.ID_FILE,
	files.TYPE,
	files.TITLE,
	files.DESCRIP,
	files.PATH,
	offers_files.IS_PLANIMETRIA,
	offers_files.IS_PRINCIPALE
	FROM
	offers_files
	Left Join files ON offers_files.ID_FILE = files.ID_FILE
	WHERE
	offers_files.ID_OFFER = '$id' ORDER BY IS_PRINCIPALE DESC LIMIT 0,1";
	$aImage = rs::rec2arr($q);
	$href_image = io::ahrefcss($file, $val=$get_list, $txt='<img src="'.ABS_ALB_THU.$aImage['PATH'].'" alt="'.$titolo.'" style="border:1px solid #FF9900;" />',$js='',$target="_blank",$title=$titolo, $id='', $css="");
	$href_title = io::ahrefcss($file, $val=$get_list, $txt=$html_title,$js='',$target="_blank",$title=$html_title, $id='', $css="");

	$img = strlen($aImage['PATH'])>0 ? '<td width="74" valign="top">'.$href_image.'</td>' : '';
	ob_start();
	?>
<table width="100%" border="0" cellpadding="5">
<tr>
<?=$img?>
<td valign="top"><font size="2" face="sans-serif">
<h2 style="font-size:12px; margin:0 0 3px 0;"><div style="float:right"><font color="#FF9900"><?=$prezzo?></font></div><font color="#044dff"><?=$href_title?></font></h2>
<?=$descrizione?></font></td>
</tr>
</table>
<?php
	return ob_get_clean();
}
?>