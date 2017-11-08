<?php
include_once 'init.php';

$user = new autentica($aA5);
$user -> login_standard();

# VARIABILI DI DEFAULT
list($icursor, $crud, $PSW_RPT, $PSW_NEW, $PSW_OLD) = request::get(array('icursor' => 0, 'crud' => 'upd',  'PSW_RPT' => NULL, 'PSW_NEW' => NULL, 'PSW_OLD' => NULL));

$tabella = 'users';
$id_rec = $user -> aUser['ID_USER'];

# CONFIGURAZIONE NW
$scheda = new nw('users');
$scheda -> ext_table(array());
$aStripExt = array(); // campi tabelle esterne o interne da rimuovere in tutte le pagine
$aStripList = array_merge($scheda->astrip, $aStripExt, array());
$aStripC = array_merge($scheda->astrip, $aStripExt, array('ID_FILE'));

# CONFIGURAZIONE FILTRI
$my_vars = new ordinamento(array('al'=>'USER'));
$my_vars->campi_force['text']['USER'] = 'users';  //FORZO IL FORMATO DEL CAMPO (FIELDNAME) A SELECT (select) PASSANDO IL NOME TABELLA (tablename) RELATIVO
$my_vars->sort_default($scheda->f_id);
$my_vars->tabella = $scheda->table;

$sub_nav['Scheda'] = array($scheda->file_c);

//$is_swf=0; $is_head_mce=1; $is_multibox=0; $is_js=0;

$fil="";
$fil=is_null($crud) ? "" : $fil;
$fil=$crud=="ins" ? " WHERE 1=0 " : $fil;
$fil=$crud=="upd" ? " WHERE ".$scheda->table.".".$scheda->f_id."='$id_rec' " : $fil;

$aCurs=array('icursor' => $icursor);
$aFromp=array();
$aFiltro=array();

$backUri=array_merge($aCurs, $my_vars->href, $my_vars -> hidden);
$backUri['id_rec'] = $id_rec;

$scheda -> query_list();
 
$qTotRec = "SELECT users.ID_USER,
users.USER,
users.PASSWORD,
users.NAME,
users.SURNAME,
users.ID_GRUPPI,
users.IS_UPLOADER,
users.CODE,
users.TS FROM users";

if($scheda->ext_table != NULL){	$qTotRec.=""; }
$atabelle=$scheda->atable;
$lable=rs::sql2lbl($qTotRec);
$sublable=$lable;
$rec=rs::rec2arr($qrs=$qTotRec." ".$fil);

$db=new dbio();
$aRet = rs::showfull($atabelle, $rec, $lable,$add=array(), $self_join=array(), $my_vars);
list($db->a_name, $db->a_val,$db->a_type,$db->a_maxl,$db->a_default,$db->a_not_null,$db->a_lable,$db->a_dec,$db->a_fkey,$db->a_aval,$db->a_addblank,$db->a_comment,$db->a_sql_type, $db->a_js, $db->a_disabled, $aFval) = $aRet;
$db->dbset();

$input['PSW_RPT'] = new io();
$input['PSW_RPT'] -> type = 'password';
$input['PSW_RPT'] -> maxl = 50;
$input['PSW_RPT'] -> set('PSW_RPT');
$input['PSW_RPT'] -> val = $PSW_RPT;

$input['PSW_NEW'] = new io();
$input['PSW_NEW'] -> type = 'password';
$input['PSW_NEW'] -> maxl = 50;
$input['PSW_NEW'] -> set('PSW_NEW');
$input['PSW_NEW'] -> val = $PSW_NEW;

$input['PSW_OLD'] = new io();
$input['PSW_OLD'] -> type = 'password';
$input['PSW_OLD'] -> maxl = 50;
$input['PSW_OLD'] -> set('PSW_OLD');
$input['PSW_OLD'] -> val = $PSW_OLD;

# C.R.U.D.
$ERR_CRUD=err::crud($rec);
$_POST=request::adjustPost($_POST);
$rec=request::post2arr($sublable);
$rec=arr::magic_quote($rec);
$db->a_val=array_merge($db->a_val,$rec);
$db->dbset();

if(array_key_exists("subDo", $_POST) || array_key_exists("subDo_x", $_POST) || array_key_exists("subBack", $_POST)){
	$aPrime = array($db -> primkey->name => $db->primkey->val)  ;
	$crud_op=$crud=="ins" ? "INSERT" : "UPDATE";
	$msg_krud=$crud=="ins" ? "REC_INS_BCKH" : "REC_MOD_BCKH";
	$ctrl = array("null_ctrl", "syntax_ctrl", "max_ctrl", "min_ctrl", "uni_ctrl");
	
	foreach ($rec as $fld=>$val){
	$db->$fld->dbtable=$tabella;
	$db->$fld->dbkeyfld=$db->primkey->name;
	$db->$fld->dbkeyval=$db->primkey->val;
		foreach($ctrl as $func){
			$ERR_CRUD[$fld] = $ERR_CRUD[$fld] == false ? rs::$func($db->$fld) : $ERR_CRUD[$fld];
			if(!empty($ERR_CRUD[$fld])) $db -> $fld -> css = 'alert_red';
		}
	}
	# ULTERIORI CONTROLLI
	if(md5($_POST['PSW_OLD']) == $user -> aUser['PASSWORD']){  # I CONTROLLO CORRISPONDENZA CON LA VECCHIA PASSWORD
		if(strlen($_POST['PSW_NEW'])<6){
			$ERR_CRUD['PASSWORD'] = MINL_ERR.': 6';
			$MYFILE -> add_err( MINL_ERR.': 6' );
			$input['PSW_NEW'] -> css = 'alert_red';

		}
		if($_POST['PSW_NEW'] != $_POST['PSW_RPT']){
			$ERR_CRUD['PASSWORD'] = ERR_RPT_PASSWORD; 
			$MYFILE -> add_err(ERR_RPT_PASSWORD);
			$input['PSW_NEW'] -> css = 'alert_red';
			$input['PSW_RPT'] -> css = 'alert_red';
		}
	}
	else{
		$ERR_CRUD['PASSWORD'] = OLD_PASSWORD_WRONG; 
		$MYFILE -> add_err(OLD_PASSWORD_WRONG);
		$input['PSW_OLD'] -> css = 'alert_red';
	}
	
	if(err::allfalse($ERR_CRUD)){
		$err=rs::execdml($crud_op,$tabella,$rec,$aPrime);
		//$ERR_CRUD['SYSTEMERR']=err::sqlcrud(SYSTEMERR);
		$ERR_CRUD=arr::strip($ERR_CRUD);
		$backUri[$msg_krud]="1";
		print_r($ERR_CRUD);
		if(err::allfalse($ERR_CRUD)){
			//$id_rec = $crud == 'ins' ? mysql_insert_id() : $id_rec;
			//$scheda->main_img($id_rec);
			$psw = md5($_POST['PSW_NEW']);
			$q = "UPDATE ".$scheda->table." SET PASSWORD = '".$psw."' WHERE ".$scheda->f_id." = '".$id_rec."'";
			if(mysql_query($q)){
				$MYFILE -> add_ack( PASSWORD_SAVED );
				# REFRESH DEI COOKIE
				$user -> password = $psw;
				$user -> set_normal_cookie(false);
				io::headto($MYFILE -> file, array('ack' => PASSWORD_SAVED ));
			}
			if(array_key_exists("subDo",$_POST)){
				unset($backUri[$msg_krud]);
			}
		}
	}
}

include BLOCCHI_AR.'crud_buttons.php';

ob_start();
?>
<form method="post" id="form_registrazione">
<?=request::hidden($backUri)?>
<table class="list">
<tr class="bg"><th colspan="2">&nbsp;</th></tr>
<tr class="yellow"><td colspan="2"><div class="table_cell"><strong><?=$user -> aUser['USER']?></strong></div></td></tr>
<tr><td><div class="table_cell"><?=INSERT_PASSWORD?>*:</div></td><td><? $input['PSW_OLD'] -> get(); ?></td></tr>
<tr><td><div class="table_cell"><?=INSERT_NEW_PASSWORD?>*:</div></td><td><? $input['PSW_NEW'] -> get(); ?></td></tr>
<tr><td><div class="table_cell"><?=REPEAT_PASSWORD?>*:</div></td><td><? $input['PSW_RPT'] -> get(); ?></td></tr>
<tr><th colspan="2">
<input name="subDo" type="submit" value="<?=SAVE?>" class="g-button g-button-yellow" /> * <?=REQUIRED_FIELDS?></th></tr>
</table>
</form>
<?php
$html['passwords_c'] = ob_get_clean();

include_once HEAD_AR;
print $html['passwords_c'];
include_once FOOTER_AR;
?>