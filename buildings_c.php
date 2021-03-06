<?php
include_once 'init.php';
$user = new autentica($aA4);
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

$atabelle=$scheda->atable;
$lable=rs::sql2lbl($qTotRec);

$sublable = arr::arr2constant($aShowCrud, true);
$rec=rs::rec2arr($qrs=$qTotRec." ".$fil);

if($user->idg == 2){ # LISTA HOUSING COMPANIES RELATIVE ALLA FEDERAZIONE DI APPARTENEZA DELL'GM LOGGATO
$qHC = "SELECT
hcompanys.ID_HCOMPANY,
hcompanys.CODE_HC,
federations.ID_USER
FROM
federations
Left Join hcompanys ON hcompanys.ID_FEDERATION = federations.ID_FEDERATION
WHERE federations.ID_USER = '".$user -> aUser['ID_USER']."'
ORDER BY hcompanys.CODE_HC ASC
";
} else {
$qHC = "SELECT
hcompanys.ID_HCOMPANY,
hcompanys.CODE_HC,
federations.ID_USER
FROM
federations
Left Join hcompanys ON hcompanys.ID_FEDERATION = federations.ID_FEDERATION
ORDER BY hcompanys.CODE_HC ASC
";
}

$db=new dbio();
$aRet = rs::showfullpersonal($atabelle, $rec, $lable, array(), array(), array(
	'ID_HCOMPANY' => $qHC
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
	
	/*
	 * verifica se monitortype è stato modificato
	 * */
	$changed_monitortype=false;
	if( ! empty($id)){
		$q="SELECT MONITORTYPE FROM buildings WHERE ID_BUILDING={$id}";
		$row=rs::rec2arr($q);
		if( ! empty($_POST['MONITORTYPE']) && ! empty($row['MONITORTYPE'])){
			if($_POST['MONITORTYPE'] != $row['MONITORTYPE']){
				$changed_monitortype=true;
			}
		}
	}
	
	if(err::allfalse($ERR_CRUD)){
		$err=rs::execdml($crud_op,$tabella,$rec,$aPrime);
		$ERR_CRUD['SYSTEMERR'] = err::sqlcrud(SYSTEMERR);
		
		print $ERR_CRUD['SYSTEMERR'];
		
		$ERR_CRUD = arr::strip($ERR_CRUD);
		$backUri[$msg_krud]="1";
		
		if(err::allfalse($ERR_CRUD)){
			
			/*
			 * operazioni da eseguire in caso di modifica di monitortype,
			 * per esempio l'eliminazione delle precedenti misurazioni
			 * */
			if($changed_monitortype){
				// echo 'Eliminazione misurazioni';
			}
			
			$id = $crud == 'ins' ? mysql_insert_id() : $id;
			if(empty($id)) { $id = $crud == 'ins' ? $rec[$db->primkey->name] : $id; }
		
			$scheda->main_img($id);
			if(array_key_exists("subDo",$_POST)){
				unset($backUri[$msg_krud]);
			}
			
			$idhc = $_REQUEST['ID_HCOMPANY'];
			if(!empty($idhc)){

			}
			
			if($crud=='ins'){ # IN CASO DI INSERIMENTO RICARICO LA PAGINA IN MODALITA' UPDATE
				if(!empty($idhc)){
					$aName = sole::mk_namebuilding($idhc, $_REQUEST['NAME_BLD']);
					$qCode = "UPDATE buildings SET CODE_BLD = '".$aName['fullname']."' WHERE ID_BUILDING = '$id'";
					mysql_query($qCode);
				}
				io::headto($MYFILE -> file, array_merge($backUri, array('crud' => 'upd', 'id' => $id, 'ack' => 'Nuovo record inserito')));
			} else {
				io::headto($MYFILE -> file, array_merge($backUri, array('crud' => 'upd', 'id' => $id, 'ack' => 'Aggiornamento riuscito')));
			}
		}
	}
}
$val = $backUri;
unset($val['jsstatis']);

$href_lista = io::a($scheda->file_l, $val, LISTA, array('title' => LISTA, 'class' => 'g-button'));
$href_add_file = ($crud == 'upd' && !empty($scheda -> files)) ? io::a($scheda->file_f, $backUri, ADD_FILE, array('title' => ADD_FILE, 'class' => 'puls_aggiungi')) : '';


/*if($crud=='upd'){
$href_add_file = $scheda->files ? io::href($scheda->file_f,$val=$backUri, $txt=ADD_FILE,$js='',$target="",$title=ADD_FILE, $id='', $css="puls_aggiungi") : '';
} else {$href_add_file = ''; }*/

$db -> ID_HCOMPANY -> addblank = true;
$db -> ID_HCOMPANY -> txtblank = S_CHOOSE.' Housing Company';

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

if(!$user -> ck_group(array('1','2'))){
	$href_new = '';
}

$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);
$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);

/*
 * personalizzazione select MONITORTYPE
 * */
$db->MONITORTYPE->type =	'select';
$db->MONITORTYPE->aval =	array(6=>SEMESTRALE, 12=>ANNUALE);

/*
 * policies di gruppo
 * */
if($user->idg > 2){
	$db->MONITORTYPE->type = 'slable';
}
elseif($user->idg == 4){
	$db -> ID_HCOMPANY -> type = 'slable';
	$db -> NAME_BLD -> type = 'lable';
	$db -> YEAR_BLD -> type = 'lable';
	
	$db->DESCRIP_BLD_IT->type = 'lable';
	$db->MONITORINFO_IT->type = 'lable';
	$db->DESCRIP_BLD_EN->type = 'lable';
	$db->MONITORINFO_EN->type = 'lable';
} else {
	$db->DESCRIP_BLD_IT->css = 'text_big editor';
	$db->MONITORINFO_IT->css = 'text_big editor';
	$db->DESCRIP_BLD_EN->css = 'text_big editor';
	$db->MONITORINFO_EN->css = 'text_big editor';
}
$db -> IS_HIDE -> lable = '';
$db->IS_POWERHOUSE->lable = '';

include_once HEAD_AR;
print $sub_menu;
# CONFIGURAZIONE 2 DI 2 ##################################################
if($crud == 'upd'){
}
$db -> CODE_BLD -> type = 'lable';

$input_save = '<input name="subDo" type="submit" value="'.SAVE.'" class="g-button g-button-yellow" />';
if($user -> idg == 4){
	$input_save = '';
}

# FINE CONFIGURAZIONE 2 DI 2 #############################################
?>
  <form method="post" enctype="multipart/form-data" action="<?=$action?>">
  <?=request::hidden($backUri)?>
    <table class="list">    
      <tr class="bg">      
         <th colspan="6"><?=$href_lista?> <?=$href_new?> <?=$input_save?> 
         <input type="button" value="<?=__('Duplica edificio')?>" id="clone-building" rel="<?=$id?>" class="g-button" />
         </th>
      </tr>    
	<? if($crud=='upd'){
		print'<tr class="yellow"><td colspan="6"><div class="table_cell"><strong>'.$etichetta.'</strong></div></td></tr>';
	  }

foreach($sublable as $k=>$v){
	if($user -> idg == 4){
		if($k == 'IS_HIDE') continue;
	}
	if(!in_array($k,$aStripC) && ($k != $scheda->f_path)){
	?>
<tr>
  <td valign="top"><div class="table_cell"><?=$v?></div></td>
  <td><div class="table_cell"><? $db->$k->get(); ?></div></td>
</tr><? }}?>

<?php
  
if($scheda -> img){ # STAMPO CONTROLLI IMMAGINE
	$path = rs::rec2arr("SELECT ".$scheda->f_path." FROM ".$scheda->table." WHERE ".$scheda->f_id." = '$id'");
	print'<tr><td><div class="table_cell">'.UPD_INS_IMG.'</div></td><td colspan=\"5\"><input name="nw_img" type="file" /></td></tr>';
	if(is_file($scheda->img_main_web.$path[$scheda->f_path])){
		$href_deleimg = io::a($scheda->file_c, array_merge($backUri,array('nw_img_dele' => $id, 'crud'=>'upd')), DELE_IMG, array('title' => CANCEL, 'class' => 'button_elimina'));
	
		print"<tr><td><div class=\"table_cell\">$href_deleimg</div></td><td colspan=\"5\">
		<div class=\"table_cell\"><a href=\"".$scheda->img_main_big.$path[$scheda->f_path]."\" target=\"_blank\"><img src=\"".$scheda->img_main_web.$path[$scheda->f_path]."\" /></a></div>
		</td>
		</tr>";
	}	
}
      	?>      
    <tr>                   
    <th colspan="6">* <?=__('Campi obbligatori')?>
    </th>    
    </tr>  
    </table>
  </form>
<?php

$MYFILE->add_js_group('buildings_c',array('js/buildings_c.js'), 'debug', 100, 'footer');

include_once FOOTER_AR;
?>