<?php
include_once 'init.php';
$user = new autentica($aA3);
$user -> login_standard();
include_once stringa::get_conffile($MYFILE -> filename);

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

$atabelle=$scheda->atable;
$lable=rs::sql2lbl($qTotRec);
$sublable=$lable;

$rec=rs::rec2arr($qrs=$qTotRec." ".$fil);

$db = new dbio();

# SOLO GLI EDIFICI DELLA FEDERAZIONE DI APPARTENENZA
$q_building = "SELECT ID_BUILDING, CODE_BLD from buildings ORDER BY CODE_BLD ASC";
if($user -> idg != 1){
	$q_building = "SELECT ID_BUILDING, CODE_BLD FROM buildings
	LEFT JOIN hcompanys USING(ID_HCOMPANY)
	WHERE hcompanys.ID_FEDERATION = '{$user -> aUser['ID_FEDERATION']}'
	ORDER BY CODE_BLD ASC";
}



$aRet = rs::showfullpersonal($atabelle, $rec, $lable, array(), array(), array(
	'ID_BUILDING' => $q_building
), $my_vars);

list($db->a_name, $db->a_val,$db->a_type,$db->a_maxl,$db->a_default,$db->a_not_null,$db->a_lable,$db->a_dec,$db->a_fkey,$db->a_aval,$db->a_addblank,$db->a_comment,$db->a_sql_type, $db->a_js, $db->a_disabled, $aFval) = $aRet;
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

	if(array_key_exists('IS_PRIVACYFLAT', $_POST) && $_POST['IS_PRIVACYFLAT'] == '1'){
		if(empty($_POST['NAME_USER'])){
			$ERR_CRUD['NAME_USER'] = NOT_NULL_ERR;
			$db -> NAME_USER -> css = 'alert_red';
		}
		if(empty($_POST['SURNAME_USER'])){
			$ERR_CRUD['SURNAME_USER'] = NOT_NULL_ERR;
			$db -> SURNAME_USER -> css = 'alert_red';
		}
	
	}
	
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
			
				$code_flat = sole::mk_nameflat($_REQUEST['ID_BUILDING']);
				
				$code_activation = stringa::random_alfanum(5);
				
				$qCodeFlat = "UPDATE flats SET CODE_FLAT = '$code_flat', ACTIVATION_CODE = '$code_activation' WHERE ID_FLAT = '$id'";
				mysql_query($qCodeFlat);
			
				io::headto($MYFILE -> file, array_merge($backUri, array('crud' => 'upd', 'id' => $id, 'ack' => 'Nuovo record inserito', 'def_build' => $_REQUEST['ID_BUILDING'])));
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

$href_new = io::a($scheda->file_c, array_merge($send_nuovo, array('crud' => 'ins', 'def_build' => $def_build)), L_NUOVO , array('title' => 'Aggiungi nuovo record', 'class' => 'g-button'));

$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);
$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);

$db -> NETAREA -> css = 'decimal';

# ESEMPI:
# $db -> NAME_FIELD -> addblank = true;
# $db -> NAME_FIELD -> txtblank = '- Scegli';
$db -> IS_PRIVACYFLAT -> css = 'checkme';
$db -> IS_INFORM -> lable = '';

$db -> NAME_USER -> id = 'NAME_USER';
$db -> SURNAME_USER -> id = 'SURNAME_USER';
/* $db -> IS_INFORM -> id = 'IS_INFORM';
$db -> IS_PRIVACYFLAT -> id = 'IS_PRIVACYFLAT';
 */

$def_building = $db -> ID_BUILDING -> val;
if(!empty($def_build) && $crud == "ins"){
	$def_building = $def_build;
}
 
$rec_blds = sole::building_user(false);
$sel_bld = io::select_from_recordset($rec_blds, 'ID_BUILDING', 'CODE_BLD', $def_building, '- '.CHOOSE, array('name' => 'ID_BUILDING', 'id' => 'sel_bld'));

include_once HEAD_AR;
print $sub_menu;
# CONFIGURAZIONE 2 DI 2 ##################################################
if($crud == 'upd'){
	//$db -> ID_BUILDING -> type = 'slable';
}

$show_hide = '';
if($user -> idg == 3){
	$href_new = '';
	$show_hide = ' class="dn"';
	
	$db -> NETAREA -> type = 'lable';
}
$db -> CODE_FLAT -> type = 'lable';

# FINE CONFIGURAZIONE 2 DI 2 ############################################
?>
  <form id="frm_flat" method="post" action="<?=$action?>">
  <input id="def_user" type="hidden" value="<?=$db -> ID_USER -> val?>" />
  
  <?=request::hidden($backUri)?>
    <table class="list">    
      <tr class="bg">      
        <th width="30%"><?=$href_lista?> <?=$href_new?></th>
        <th>&nbsp;</th>
      </tr>    
	<? if($crud=='upd'){
		print'<tr class="yellow"><td colspan="6"><div class="table_cell"><strong>'.$etichetta.'</strong></div></td></tr>';
	  }
?>

<tr<?=$show_hide?>>
  <td valign="top"><div class="table_cell"><strong><?=ID_BUILDING?>*</strong></div></td>
  <td><div class="table_cell"><?=$sel_bld?></div></td>
</tr>

<tr class="dn sh">
  <td valign="top"><div class="table_cell"><strong><?=NETAREA?>*</strong></div></td>
  <td><div class="table_cell"><? $db -> NETAREA -> get(); ?></div></td>
</tr>

<tr class="dn sh">
  <td valign="top"><div class="table_cell"><?=PUBLISHING_RULES?></div></td>
  <td>
  
  <div class="table_cell"><? $db -> IS_PRIVACYFLAT -> get(); ?></div>
  <div id="extra" class="table_cell dn" style="float:left; clear:both;">
  
<div class="fieldcontain">
<label for="<?=$db -> NAME_USER -> id;?>"><strong><?=NAME_USER?>*</strong><br /><? $db -> NAME_USER -> get(); ?></label><br />
<label for="<?=$db -> SURNAME_USER -> id;?>"><strong><?=SURNAME_USER?>*</strong><br /><? $db -> SURNAME_USER -> get(); ?></label><br />

<label for="<?=$db -> IS_INFORM -> name;?>">
<? $db -> IS_INFORM -> get(); ?>
<?=IS_INFORM?></label>
</div>  

</div>
</td>
</tr>

<tr class="dn sh">
<td>&nbsp;</td>
<td>
<input type="hidden" name="subDo" value="salva" />
<input id="subDo" type="button" value="<?=SAVE?>" class="g-button g-button-yellow" />
</td>
</tr>

<tr>                   
<th colspan="6">* <?=REQUIRED_FIELDS?>
</th>    
</tr>  
</table>
</form>

<script type="text/javascript" src="<?=JS_MAIN.'flats-users.js'?>" /></script>
<?php
include_once FOOTER_AR;
?>