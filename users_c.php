<?php
include_once 'init.php';
$user = new autentica($aA3);
$user -> login_standard();

include_once 'users_conf.php';

$is_swf=0; $is_head_mce=1; $is_multibox=0; $is_js=0;

$fil="";
$fil=is_null($crud) ? "" : $fil;
$fil=$crud=="ins" ? " WHERE 1=0 " : $fil;
$fil=$crud=="upd" ? " WHERE ".$scheda->table.".".$scheda->f_id."='$id_rec' " : $fil; 

$aCurs=array("icursor"=>$icursor);
$aFromp=array();
$aFiltro=array();
$backUri=array_merge($aCurs, $my_vars->href, $my_vars -> hidden);
//$backUri=array_merge($aCurs,$my_vars->href);
$backUri['id_rec'] = $id_rec;
$qTotRec="SELECT * FROM ".$scheda->table;
if($scheda->ext_table != NULL) {
	$qTotRec.="";
}
$atabelle=$scheda->atable;
$lable=rs::sql2lbl($qTotRec);
$sublable=$lable;
$rec=rs::rec2arr($qrs=$qTotRec." ".$fil);

//$db=new dbio();
//$aRet = rs::showfull($atabelle, $rec, $lable, $add=array(),$self_join = array(), $my_vars);

if($user -> idg == 1){ # ADMIN
	$qIDG = "SELECT ID_GRUPPI, TITLE FROM gruppis WHERE ID_GRUPPI <= 2 AND ID_GRUPPI <> 4";
}
elseif($user->idg == 2){ # GM
	$qIDG = "SELECT ID_GRUPPI, TITLE FROM gruppis WHERE ID_GRUPPI = 3 OR ID_GRUPPI = 4";
}
elseif($user->idg == 3){ # MHMU
	$qIDG = "SELECT ID_GRUPPI, TITLE FROM gruppis WHERE ID_GRUPPI = 4";
}
else{ # TUTTI GLI ALTRI
	print 'Errore: funzione non consentita per l\'utente';
	exit();
}

if($user -> idg == 1){ # ADMIN
	$qHC = "SELECT ID_HCOMPANY, CODE_HC FROM hcompanys ORDER BY CODE_HC";
}
elseif($user->idg == 2){ # GM
	$qHC = "SELECT ID_HCOMPANY, CODE_HC FROM hcompanys
	RIGHT JOIN federations USING(ID_FEDERATION)
	WHERE federations.ID_USER = '".$user -> aUser['ID_USER']."'
	ORDER BY CODE_HC";
}
elseif($user->idg == 3){ # MHMU
	$qHC = "SELECT ID_HCOMPANY, CODE_HC FROM hcompanys WHERE ID_USER = '".$user -> aUser['ID_USER']."'";
}

$db=new dbio();
$aRet = rs::showfullpersonal($atabelle, $rec, $lable, array(), array(), array(
	'ID_GRUPPI' => $qIDG,
	'ID_HCOMPANY' => $qHC
), $my_vars);


list($db->a_name, $db->a_val,$db->a_type,$db->a_maxl,$db->a_default,$db->a_not_null,$db->a_lable,$db->a_dec,$db->a_fkey,$db->a_aval,$db->a_addblank,$db->a_comment,$db->a_sql_type, $db->a_js, $db->a_disabled, $aFval) = $aRet;
$db->dbset();
list($db->a_name, $db->a_val,$db->a_type,$db->a_maxl,$db->a_default,$db->a_not_null,$db->a_lable,$db->a_dec,$db->a_fkey,$db->a_aval,$db->a_addblank,$db->a_comment,$db->a_sql_type, $db->a_js, $db->a_disabled, $aFval) = $aRet;
$db->dbset();

if(array_key_exists('resetpassword', $_GET)){
	$uRec = rs::rec2arr("SELECT * FROM users WHERE ID_USER = '{$_REQUEST['id_rec']}'");

	############
	$new_password = stringa::random_alfanum(7);
	$upd_psw = md5($new_password);
	
	$send_message = "";
	$send_message .= '<strong>'.NEW_PASSWORD_SENDED.' '.NOME_SITO.'</strong>'.BR.BR;
	$send_message .= 'Username: '.$uRec['USER'].BR;
	$send_message .= 'Password: '.$new_password.BR.BR;
	$send_message .= '<a href="http://www.sole-project.com" target="_blank" title="Sole Project">www.sole-project.com</a>'.BR;

	# INVIO LA MAIL
	ob_start();
	include CONTATTI.'layout_new_user.php';
	$body = ob_get_clean();	
	###########################################
	$err = ''; $ack = '';
	$mail = new PHPMailer();
	//$mail->IsSMTP();
	//$mail->Host = "mail.rigosalotti.it";
	//$mail->SMTPDebug = 1;
	$mail->AddReplyTo('noreply@sole-project.com', NOME_SITO);
	$mail->SetFrom('noreply@sole-project.com', NOME_SITO);
	$mail->AddAddress($uRec['USER'], $uRec['NAME'].' '.$uRec['SURNAME']);
	$mail->Subject = NEW_PASSWORD_SENDED;
	$mail->MsgHTML($body);
	if($mail -> Send()){
		$ack = MAIL_SENDED;
		
		// aggiorno la password in db
		mysql_query("UPDATE users SET PASSWORD = '$upd_psw' WHERE id_user = '{$_REQUEST['id_rec']}'");
		
	} else {
		$err = ERR_SEND_MAIL;
	}
	//echo $body;

	io::headto($MYFILE -> file, array_merge($backUri, array('crud'=>'upd', 'ack' => $ack, 'err' => $err)));
}

if(array_key_exists('nw_img_dele',$_GET)){
	$scheda->dele_img($id_rec);
}

# C.R.U.D.
$ERR_CRUD=err::crud($rec);
$_POST=request::adjustPost($_POST);
$rec=request::post2arr($sublable);
$rec=arr::magic_quote($rec);
$rec=arr::_trim($rec,array('DESCRIP'));

$db->a_val=array_merge($db->a_val,$rec);
$db->dbset();

if(array_key_exists("subDo",$_POST) || array_key_exists("subBack",$_POST)){

	if($crud == 'ins'){
		$rec['PASSWORD'] = md5($rec['PASSWORD']);
	}

	$aPrime = array($db->primkey->name=>$db->primkey->val);
	$crud_op=$crud=="ins" ? "INSERT" : "UPDATE";
	$msg_krud=$crud=="ins" ? "REC_INS_BCKH" : "REC_MOD_BCKH";
	$ctrl=array("null_ctrl","syntax_ctrl","max_ctrl","min_ctrl","uni_ctrl");
		
	foreach ($rec as $fld=>$val){
		$db->$fld->dbtable=$tabella;
		$db->$fld->dbkeyfld=$db->primkey->name;
		$db->$fld->dbkeyval=$db->primkey->val;
		//foreach($ctrl as $func){$ERR_CRUD[$fld]=$ERR_CRUD[$fld]==false ? rs::$func($db->$fld) : $ERR_CRUD[$fld]; }
		foreach($ctrl as $func){
			$ERR_CRUD[$fld] = empty($ERR_CRUD[$fld]) ? rs::$func($db->$fld) : $ERR_CRUD[$fld];
			if(!empty($ERR_CRUD[$fld])){
				$MYFILE -> add_err($ERR_CRUD[$fld]);
				$db -> $fld -> css = 'alert_red';
			}
		}
	}

	$MYFILE -> add_msg($ERR_CRUD, 'err');

	if(err::allfalse($ERR_CRUD)){
		$err=rs::execdml($crud_op,$tabella,$rec,$aPrime);
		$ERR_CRUD['SYSTEMERR']=err::sqlcrud(SYSTEMERR);
		$ERR_CRUD=arr::strip($ERR_CRUD);
		$backUri[$msg_krud]="1";
		
		if(err::allfalse($ERR_CRUD)){
			$id_rec = $crud == 'ins' ? mysql_insert_id() : $id_rec;
			
			# ALLINEAMENTO UPLOADUSER
			if($rec['ID_GRUPPI'] < '5'){ # TUTTI TRANNE L'HHU
				$qUpl = "UPDATE users SET IS_UPLOADER = '1' WHERE ID_USER = '$id_rec'";
				mysql_query($qUpl);
			}				
			
/* 			if($rec['ID_GRUPPI'] == 4){ # HMU
				
			
				$qUpl = "UPDATE users SET ID_HCOMPANY = '' WHERE ID_USER = '$id_rec'";
				mysql_query($qUpl);
			}	 */		
			
			if(($user -> idg == 2 || $user -> idg == 3)  && ($_POST['ID_GRUPPI'] == 3 || $_POST['ID_GRUPPI'] == 4 )){
				mysql_query("REPLACE INTO users_federations SET ID_USER = '$id_rec', ID_FEDERATION = '".$user -> aUser['ID_FEDERATION']."'");
			}
			
/* 		if($crud=='ins'){
				$new_folder = md5($_POST['USER']);
				$cartella = new dirs(FLD_MAIN.$new_folder);
				$cartella -> mk_upld();

				$psw = md5($_POST['PASSWORD']);
				$code = md5($_POST['USER'].date('YmdHis',time()));
				$q = "UPDATE ".$scheda->table." SET FOLDER_USR = '".$cartella -> folder."', CODE = '".$code."', PASSWORD = '".$psw."' WHERE ".$scheda -> f_id." = '".$id_rec."'";
				mysql_query($q); 
			} 
			$scheda -> main_img($id_rec);
			*/
			
			if(array_key_exists("subDo",$_POST)){
				unset($backUri[$msg_krud]);
			}
			if($crud=='ins'){ # IN CASO DI INSERIMENTO RICARICO LA PAGINA IN MODALITA' UPDATE
				io::headto($MYFILE -> file, array_merge($backUri, array('crud'=>'upd','id_rec'=>$id_rec, 'ack' => 'Nuovo record inserito')));
			}
			$ack[] = 'Aggiornamento riuscito per: '.$db -> USER -> val;
		}
	}
}
$val = $backUri;
unset($val['jsstatis']);

$href_annulla = io::href($scheda->file_l, $val, LISTA, $js='',$target="", LISTA, $id='', $css="g-button");

$sub_menu = '';
foreach($sub_nav as $k => $arr){
	$href_to='';
	if(in_array(FILENAME.'.php',$arr)) { $sub_menu.='<div class="menu_dis"><span>'.$k.'</span></div>'; }
	elseif($crud=='ins') { $sub_menu.='<div class="menu_att_dis">'.$k.'</div>'; }
	else {
		$href_to = io::href($arr[0],$val=array_merge($backUri,array('crud'=>'upd')), $txt=$k,$js='',$target="",$title=strtolower($k), $id='', $css="");
		$sub_menu.='<div class="menu_att"><span>'.$href_to.'</span></div>';
	}
}

$sub_menu = '<div id="menu_bar">'.$sub_menu.'</div>';
$send_nuovo = arr::_unset($backUri, array('id_rec', 'jsstatis'));

/*
 * pulsante nuovo utente solo per HMU
 * */
if($user->idg != 4){
	$href_new = '';
} else {
	$href_new = io::a($scheda->file_c, array_merge($backUri,array('crud' => 'ins')), L_NUOVO, array('class' => 'g-button'));
}


include_once HEAD_AR;
print $sub_menu;
# CONFIGURAZIONE 2 DI 2 #################################################

$db -> ID_CLIENTS_TYPE -> addblank = 1;
$db -> K0_ID_COMUNI -> addblank = 1;
$db -> K0_ID_LOCALITA -> addblank = 1;

$db -> ID_HCOMPANY -> addblank = 1;

$db -> ID_SELF_USER -> addblank = 1;

$db -> TS -> val = dtime::my2isodt($db -> TS -> val);
$db -> TS -> type = "lable";
$db -> FOLDER_USR -> type = "lable";

$lnk_chg_psw = '';
if($crud == 'upd') { 
	$db -> PASSWORD -> val = $lnk_chg_psw; //'*********';
	$lnk_chg_psw = io::ahref('', array_merge($backUri, array('crud' => $crud, 'resetpassword' => '1')), SEND_NEW_PASSWORD, 'g-button g-button-red');
	$db -> PASSWORD -> val = $lnk_chg_psw;
	$db -> PASSWORD -> type = 'lable';
}

# CONFIGURAZIONE CAMPI
$db -> IS_UPLOADER -> lable = '';
$db -> ID_GRUPPI -> css = 'dtrenta';
$db -> USER -> css = 'dtrenta';
$db -> PASSWORD -> css = 'dtrenta';
$db -> NAME -> css = 'dtrenta';
$db -> SURNAME -> css = 'dtrenta';

$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);

$tr_gruppi = '<tr><td><div class="table_cell"><strong>'.GRUPPI.'*</strong></div></td><td colspan="4"><div class="table_cell">'.$db -> ID_GRUPPI -> set().'</div><td></tr>';

$tr_is_uploader = '';
if($db -> ID_GRUPPI -> val == '5' && $crud == 'upd'){ # CHECKBOX IS_UPLOADER SE SONO UTENTI HHU
	$tr_is_uploader = '<tr>
<td><div class="table_cell">'.IS_UPLOADER.':</div></td><td colspan="5"><div class="table_cell">'.$db -> IS_UPLOADER -> set().'</div></td>
</tr>';
	$db -> ID_GRUPPI -> type = 'hidden';
	
	$tr_gruppi = '<tr><td><div class="table_cell"><strong>'.GRUPPI.':</strong></div></td><td colspan="4"><div class="table_cell"><strong>HHU</strong>'.$db -> ID_GRUPPI -> set().'</div><td></tr>';
}

# FINE CONFIGURAZIONE 2 DI 2 #############################################
?>
  <form method="post" enctype="multipart/form-data" action="<?=$action?>">
  <?=request::hidden($backUri)?>
    <table class="list">    
		<tr class="bg">      
		<th colspan="6"><?=$href_annulla?> <?=$href_new?> <input name="subDo" type="submit" value="<?=SAVE?>" class="g-button g-button-yellow" /></th>
		</tr>    
	<? if($crud=='upd'){
		print'<tr class="yellow"><td colspan="6"><div class="table_cell"><strong>'.$etichetta.'</strong></div></td></tr>';
	  }

?>
<?=$tr_gruppi?>
<tr><td><div class="table_cell"><strong><?=USER?>*:</strong></div></td><td colspan="5"><div class="table_cell"><? $db -> USER -> get(); ?></div></td></tr>

<tr><td><div class="table_cell"><?=ID_HCOMPANY?> (HMU user):</div></td><td colspan="5"><div class="table_cell"><? $db->ID_HCOMPANY->get(); ?></div></td></tr>

<tr>
<td><div class="table_cell"><strong>Password*:</strong></div></td>
<td colspan="5"><div class="table_cell"><? $db -> PASSWORD -> get(); ?></div></td></tr>
<tr><td><div class="table_cell"><?=NAME?>:</div></td><td colspan="5"><div class="table_cell"><? $db -> NAME -> get(); ?></div></td></tr>
<tr><td><div class="table_cell"><?=SURNAME?>:</div></td><td colspan="5"><div class="table_cell"><? $db -> SURNAME -> get(); ?></div></td></tr>
<?=$tr_is_uploader?>
<tr><th colspan="6">* campi obbligatori</th></tr>  
</table>
</form>
<?php
include_once FOOTER_AR;
?>