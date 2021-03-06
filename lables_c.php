<?php
# V.0.1.8
include_once 'init.php';
$user = new autentica($aA1);
$user -> login_standard();
include_once stringa::get_conffile($MYFILE -> filename);

if(!empty($_GET['mknew'])){
	$newid = $_GET['mknew'];
	$qc = "SELECT * FROM lablesites WHERE ID_LABLESITE = '$newid'";
	$rc = rs::rec2arr($qc);
	if(empty($rc['ID_LABLESITE'])){
		$q = "INSERT INTO lablesites (ID_LABLESITE) VALUES ('$newid')";
		mysql_query($q);
	}
	io::headto($MYFILE -> file, array('crud' => 'upd', 'id' => $newid));
}

$fil = "";
$fil = is_null($crud) ? "" : $fil;
$fil = $crud == "ins" ? " WHERE 1=0 " : $fil;
$fil = $crud == "upd" ? " WHERE ".$scheda->table.".".$scheda->f_id."='$id' " : $fil; 

$aCurs=array("icursor"=>$icursor);
$aFromp=array();
$aFiltro=array();
$backUri=array_merge($aCurs, $my_vars -> href, $my_vars -> hidden);
$backUri['id'] = $id;
$qTotRec="SELECT * FROM ".$scheda->table;

/*if($scheda->ext_table != NULL) {
	$qTotRec.="";
}*/


$atabelle=$scheda->atable;
$lable=rs::sql2lbl($qTotRec);
//$sublable=$lable;

$sublable = arr::arr2constant($aShowCrud, true);


$rec=rs::rec2arr($qrs=$qTotRec." ".$fil);

$db=new dbio();
$aRet = rs::showfull($atabelle, $rec,$lable,$add=array(),$self_join=array(), $my_vars);

list($db->a_name, $db->a_val,$db->a_type,$db->a_maxl,$db->a_default,$db->a_not_null,$db->a_lable,$db->a_dec,$db->a_fkey,$db->a_aval,$db->a_addblank,$db->a_comment,$db->a_sql_type, $db->a_js, $db->a_disabled, $aFval) = $aRet;
$db -> RAGIONESOCIALE_USR -> not_null = 0;
$db->dbset();

if(array_key_exists('nw_img_dele',$_GET)){
	$scheda->dele_img($id);
}

# C.R.U.D.
$ERR_CRUD = err::crud($rec);
$_POST = request::adjustPost($_POST);
$rec = request::post2arr($sublable);
$rec = arr::magic_quote($rec);
$rec = arr::_trim($rec,array('DESCRIP'));
$db -> a_val = array_merge($db->a_val,$rec);
$db -> dbset();

if(array_key_exists("subDo",$_POST) || array_key_exists("subBack",$_POST)){
	$aPrime = array($db->primkey->name=>$db->primkey->val)  ;
	$crud_op=$crud=="ins" ? "INSERT" : "UPDATE";
	$msg_krud=$crud=="ins" ? "REC_INS_BCKH" : "REC_MOD_BCKH";
	$ctrl=array("null_ctrl","syntax_ctrl","max_ctrl","min_ctrl","uni_ctrl");
		
	foreach ($rec as $fld=>$val){
		$db->$fld->dbtable=$tabella;
		$db->$fld->dbkeyfld=$db->primkey->name;
		$db->$fld->dbkeyval=$db->primkey->val;
		foreach($ctrl as $func){
			$ERR_CRUD[$fld] = empty($ERR_CRUD[$fld]) ? rs::$func($db->$fld) : $ERR_CRUD[$fld];
			if(!empty($ERR_CRUD[$fld])){
				$db -> $fld -> css = 'alert_red';
			}
		}
	}
	
	$MYFILE -> add_msg($ERR_CRUD, 'err');
	
	if(err::allfalse($ERR_CRUD)){
		$err=rs::execdml($crud_op,$tabella,$rec,$aPrime);
		$ERR_CRUD['SYSTEMERR'] = err::sqlcrud(SYSTEMERR);
		
		print $ERR_CRUD['SYSTEMERR'];
		
		$ERR_CRUD = arr::strip($ERR_CRUD);
		$backUri[$msg_krud]="1";
		
		if(err::allfalse($ERR_CRUD)){
			$id = $crud == 'ins' ? mysql_insert_id() : $id;
			if(empty($id)) { $id = $crud == 'ins' ? $rec[$db->primkey->name] : $id; }
			if($crud=='ins'){
				# EXTRA INSERIMENTO (md5 password, creazione cartelle ecc...)
			}
			$scheda->main_img($id);
			if(array_key_exists("subDo",$_POST)){
				unset($backUri[$msg_krud]);
			}
			if($crud=='ins'){ # IN CASO DI INSERIMENTO RICARICO LA PAGINA IN MODALITA' UPDATE
				io::headto($MYFILE -> file, array_merge($backUri, array('crud' => 'upd', 'id' => $id, 'ack' => 'Nuovo record inserito')));
			}
			$MYFILE -> add_ack('Aggiornamento riuscito');
		}
	}
}
$val = $backUri;
unset($val['jsstatis']);

$href_lista = io::a($scheda->file_l, $val, LISTA, array('title' => LISTA, 'class' => 'g-button'));
$href_add_file = ($crud == 'upd' && !empty($scheda -> files)) ? io::a($scheda->file_f, $backUri, ADD_FILE, array('title' => ADD_FILE, 'class' => 'puls_aggiungi')) : '';

$sub_menu = '';
foreach($sub_nav as $k => $arr){
	$href_to='';
	if(in_array(FILENAME.'.php',$arr)) { $sub_menu.='<div class="menu_dis"><span>'.$k.'</span></div>'; }
	elseif($crud=='ins') { $sub_menu.='<div class="menu_att_dis">'.$k.'</div>'; }
	else {
		$href_to = io::a($arr[0], array_merge($backUri,array('crud'=>'upd')), $k, array('title' => $title=strtolower($k)));
		
		//$href_to = io::href($arr[0],$val=array_merge($backUri,array('crud'=>'upd')), $txt=$k,$js='',$target="",$title=strtolower($k), $id='', $css="");
		$sub_menu.='<div class="menu_att"><span>'.$href_to.'</span></div>';
	}
}
$sub_menu = '<div id="menu_bar">'.$sub_menu.'</div>';

$send_nuovo = arr::_unset($backUri, array('id', 'jsstatis'));
$href_new = io::a($scheda->file_c, array_merge($send_nuovo, array('crud' => 'ins')), L_NUOVO , array('title' => 'Aggiungi nuovo record', 'class' => 'g-button'));

$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);
$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);

$db -> INF_ART -> css = 'decimal';
$db -> BAS_ART -> css = 'decimal';
$db -> BAL_ART -> css = 'decimal';
$db -> PREZZO_ART -> css = 'decimal';

include_once HEAD_AR;
print $sub_menu;
# CONFIGURAZIONE 2 DI 2 ##################################################
if($crud == 'upd'){
	$db -> ID_LABLE_SITE -> type = 'lable';
}

# FINE CONFIGURAZIONE 2 DI 2 #############################################
?>
  <form method="post" enctype="multipart/form-data" action="<?=$action?>">
  <?=request::hidden($backUri)?>
    <table class="list">    
      <tr class="bg">      
        <th colspan="6"><?=$href_lista?> <?=$href_new?> <input name="subDo" type="submit" value="<?=SAVE?>" class="g-button g-button-yellow" /></th>
      </tr>    
	<? if($crud=='upd'){
		print'<tr class="yellow"><td colspan="6"><strong>'.$etichetta.'</strong></td></tr>';
	  }

foreach($sublable as $k=>$v){
	if(!in_array($k,$aStripC) && ($k != $scheda->f_path)){
	?>
<tr>
  <td valign="top"><?=$v?></td>
  <td><? $db->$k->get(); ?></td>
</tr><? }}?>
    
    <tr>                   
    <th colspan="6">* campi obbligatori
    </th>    
    </tr>  
    </table>
  </form>
<?php
include_once FOOTER_AR;
?>