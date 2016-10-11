<?php
function capt_print($err, $value) {
	if(!empty($err) && strlen($value)>0) echo addslashes($value);
}

function capt_alert($s) {
	if(isset($s) && strlen($s)>0) echo"<script language=\"javascript\">alert('$s')</script>";
}

	$lista_controlli = array('mNome', 'mTel', 'mEmail', 'mMessaggio', 'secure', 'mPrivacy');

foreach($lista_controlli as $k){
	if($k != 'mMessaggio'){ 
		$_REQUEST[$k] = array_key_exists($k,$_REQUEST) ? trim(strip_tags($_REQUEST[$k])) : "";
	}
	else{ 
		$_REQUEST[$k] = array_key_exists($k,$_REQUEST) ? trim($_REQUEST[$k]) : "";
	}
	$$k = $_REQUEST[$k];
}

//ob_start();
session_start();
$get_message='';

if(isset($_POST['sub'])){
	ob_start();
	include_once BLOCCHI.'anteprima_email.php';
	$preview_article = ob_get_clean(); 

	# FUNCTION #################
	$a = $_REQUEST;
	$mErr=array(); $mAck = array();

	if(strlen($a['mNome'])<1) 					$mErr[] = 'Scrivi il tuo nome.';
	if(!isEmail($a['mEmail'])) 					$mErr[] = 'Scrivi la tua email.';
	if(strlen(strip_tags($a['mMessaggio']))<1) 	$mErr[] = 'Scrivi un messaggio.';
	if(strlen($_SESSION['captcha'])==0) 		$mErr[] = 'Reinserisci il testo dell\'immagine.';
	if($a['secure']!=$_SESSION['captcha']) 		$mErr[] = 'Reinserisci il testo dell\'immagine.';
	if(strlen($a['mPrivacy'])==0)				$mErr[] = 'Accetta l\'informativa sulla privacy.';

	if(empty($mErr)){ # HEADER IN GET
		$dati_mittente = "Dati mittente:".BR.BR;
		$dati_mittente .= strlen($a['mNome'])>0 ? "Nome: ".$a['mNome'].BR : '';
		$dati_mittente .= strlen($a['mEmail'])>0 ? "E-mail: ".$a['mEmail'].BR : '';
		$dati_mittente .= strlen($a['mTel'])>0 ? "Telefono: ".$a['mTel'].BR : '';
		$dati_mittente .= strlen($a['mMessaggio'])>0 ? BR."Messaggio:".BR.$a['mMessaggio'].BR : '';
		
		ob_start();
		include CONTATTI.'layout_richiesta_info.php';
		$body = ob_get_clean();	
		
		###########################################
		$mail = new PHPMailer();
		$mail->AddReplyTo($a['mEmail'],$a['mNome']);
		$mail->SetFrom(MAILTO, $a['mNome']);
		$mail->AddAddress($rec_scheda['CONTATTO_EMAIL'], DOMINIO);
		$mail->Subject = 'Il Mercatino - Richiesta informazioni sul tuo annuncio';
		$mail->MsgHTML($body);
		if(!$mail->Send()) { $mErr[]="MAIL_ERR"; }
		else { $mAck[]="messaggio inviato"; /* all'indirizzo ".$rec_scheda['CONTATTO_EMAIL'];*/ }
	}
	$aAck = array($mAck,$mErr);
	############################
	
	if(!empty($aAck[0])){ foreach($aAck[0] as $k => $aack) $ack[] = $aack;}
	if(!empty($aAck[1])){ foreach($aAck[1] as $k => $aerr) $err[] = $aerr;}
}

ob_start();
?>
<h2 class="<?=$aCssRubriche[$ct]?>">Invia una mail all'inserzionista</h2>
<div class="white">
<form id="richiesta_info" method="post">
<?=request::hidden($_GET)?>
<table>
<tr><td><strong>Nome</strong>*:</td>
<td><input name="mNome" type="text" maxlength="64" value="<? capt_print($err, $mNome); ?>" /></td></tr>
<tr><td><strong>E-mail</strong>*:</td>
<td><input name="mEmail" type="text"  maxlength="64" value="<? capt_print($err, $mEmail); ?>" /></td></tr>
<tr><td>Telefono:</td><td><input name="mTel" type="text" maxlength="64" value="<? capt_print($err, $mTel); ?>" /></td></tr>
<tr><td colspan="2" valign="top"><strong>Messaggio</strong>*:<br />
  <textarea name="mMessaggio" style="width:100%;"><? capt_print($err, $mMessaggio); ?></textarea></td>
</tr>
<tr><td colspan="2" valign="top"><img src="<?=CONTATTI?>captcha.php" alt="Inserisci il codice visualizzato" /><strong>Scrivi ci&ograve; che leggi nell'immagine</strong>*: 
  <input name="secure" type="text" size="5" maxlength="5" /></td></tr>
<tr><td colspan="2"><input type="checkbox" name="mPrivacy" class="checkbox" />
  <strong>accetto l'<a href="privacy.php" target="_blank" title="informativa sulla privacy">informativa</a> sulla privacy</i>*</strong></td>
</tr>
<tr><td colspan="2"><input type="submit" name="sub" class="input_submit floatright" value="Invia" /></td></tr>
</table> 
</form>
</div>
<?php
$html['info_annuncio'] = ob_get_clean();
?>