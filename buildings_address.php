<?php
include_once 'init.php';
list($il, $jsstatis) = request::get(array('il' => NULL,'jsstatis' => NULL));

$user = new autentica($aA3);
$user -> login_standard();
include_once stringa::get_conffile($MYFILE -> filename);
$scheda -> img = false;

if( array_key_exists('action', $_REQUEST)){
	$action = prepare($_REQUEST['action']);

	if($action=='save_address'){
		$success='r';
		$message=__('salvataggio non riuscito');
		$loc='';
		$add='';
		
		$id = prepare4sql($_POST['ID_BUILDING']);
		
		if( ! empty($id) && is_numeric($id)){
			
			$q="SELECT LOCALITY, ADDRESS_BLD FROM buildings WHERE ID_BUILDING={$id} LIMIT 1";
			$row=rs::rec2arr($q);
			
			$loc=$row['LOCALITY'];
			$add=$row['ADDRESS_BLD'];
			
			$q="UPDATE buildings SET 
				LOCALITY='".prepare4sql($_POST['LOCALITY'])."',
				ADDRESS_BLD='".prepare4sql($_POST['ADDRESS_BLD'])."',
				LAT_BLD='".prepare4sql($_POST['LAT_BLD'])."',
				LNG_BLD='".prepare4sql($_POST['LNG_BLD'])."'
				WHERE
				id_building={$id}";
			
			if(mysql_query($q)){
				$success='g';
				$message=__('indirizzo aggiornato');
			}
		}
		
		exit(json_encode(array('success'=>$success, 'message'=>$message,'loc'=>$loc,'add'=>$add)));
	}
	elseif($action='save_coords'){
		$success='r';
		$message=__('salvataggio non riuscito');
		
		$id = prepare4sql($_REQUEST['id']);
		$lat = prepare4sql($_REQUEST['lat']);
		$lng = prepare4sql($_REQUEST['lng']);
	
		if( ! empty($id) && ! empty($lat) && ! empty($lng)){
			$q="UPDATE buildings SET LAT_BLD='{$lat}', LNG_BLD='{$lng}' WHERE ID_BUILDING='{$id}'";
			if(mysql_query($q)){
				$success='g';
				$message=__('coordinate aggiornate');
			}
		}
		exit(json_encode(array('success'=>$success, 'message'=>$message)));
	}
}

$MYFILE->add_js('https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=places&language='.strtolower(LANG_DEF), 5);

$MYFILE -> add_js('
	$(function() {
		height = $(document).height();
		width = $(document).width();
		$("#map_canvas").height(height-350);
		$("#map_canvas").width(width-320);
	});
	$(window).resize(function() {
		height = $("#content_map").height();
		width = $("#content_map").width();
		$("#map_canvas").css("height", height + "px");
		$("#map_canvas").css("width", width + "px");
	});
', 10,  'head', 'code');

$MYFILE -> add_js('
function invia() {
	var str = $("form").serialize();
	//$("#result").text(str);
	$(\'#ajax_wizard\').load(\'buildings_address_bk.php\', str, function(){
		addChange();
	});
}

function addChange(){
	$(\'select\').change(function() {
		//alert(\'addChange\');
		invia();
	});
}
$(document).ready(function(){
	addChange();
})
', 20,  'head', 'code');

if(!isset($configura)) $configura = new configura('descriptors');

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
$db=new dbio();
$aRet = rs::showfull($atabelle, $rec,$lable,$add=array(), array('ID_HCOMPANY'), $my_vars);

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
			
			foreach($my_vars -> js as $tbl => $value){
				//$configura -> get_info($tbl, $value);
				$configura -> set_null_fields($scheda -> table, $tbl, $value, $id);
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

if(!empty($_POST['add_sub'])){
	$configura -> set_il($_POST['add_js']);
	$configura -> get_path();
	$configura -> inserisci_valore($_POST['add_val']);
}

$sub_menu = '';
foreach($sub_nav as $k => $arr){
	$href_to='';
	if(in_array(FILENAME.'.php',$arr)) { $sub_menu.='<div class="menu_dis"><span>'.$k.'</span></div>'; }
	elseif($crud=='ins') { $sub_menu.='<div class="menu_att_dis">'.$k.'</div>'; }
	else {
		$href_to = io::a($arr[0], array_merge($backUri,array('crud'=>'upd')), $k, array('title' => $title=strtolower($k)));
		
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

$sgmaps = ''; $html['gmaps'] = '';

$coords = DEF_COORDS;
if(!empty($db -> LAT_BLD -> val)){
	$coords = $db -> LAT_BLD -> val.', '.$db -> LNG_BLD -> val;
}

//$MYFILE->add_js('http://maps.google.com/maps/api/js?sensor=false', 1, 'head', 'file');
$MYFILE->add_js_group('buildings_address', array(
		'js/jquery/jquery.typing.js',
		'js/buildings_address.js',
),
		10, 'on', 'footer');



$html['gmaps'] = '<div id="map_canvas"></div>
';

$db -> LAT_BLD -> id = 'lat';
$db -> LNG_BLD -> id = 'lng';

$db -> ADDRESS_BLD -> id = 'indirizzo';
$db -> K1_ID_STATI -> id = 'stato';
$db -> K1_ID_REGIONI -> id = 'regione';
$db -> K1_ID_PROVINCE -> id = 'provincia';
$db -> K1_ID_COMUNI -> id = 'comune';
 
$db -> LAT_BLD -> type = 'hidden';
$db -> LNG_BLD -> type = 'hidden';

$db -> ADDRESS_BLD -> css = 'dtrenta';

include_once HEAD_AR;
print $sub_menu;
?>
<table class="dark">
<tr class="bg">
<th colspan="2"><?=$href_lista?> <?=$href_new?> <div id="result"></div></th>
</tr>
<tr><td valign="top" style="width:250px">

	<form id="frm-address" method="post" action="buildings_address.php">
		<input type="hidden" name="action" value="save_address"/>
		<input type="hidden" name="ID_BUILDING" value="<?=$id?>"/>

		<div id="wizard" class="hide">
		
			<div class="campo_form"><label for="inputcomune"><?=__('Comune')?>:</label>	
				<input type="text" id="inputcomune" name=comune class="input_form text " style="width:218px;">
			</div>
			
			<div class="campo_form"><label for="inputindirizzo"><?=__('Indirizzo')?>:</label>	
				<input type="text" id="inputindirizzo" name=indirizzo class="input_form text " style="width:218px;">
			</div>
			
			<? $db->LAT_BLD->get(); ?>
			<? $db->LNG_BLD->get(); ?>
				
			<div class="campo_form">
				<input type="button" value="<?=__('Salva')?>" id="save-address" class="g-button g-button-yellow" />
				<input type="button" value="<?=__('Indietro')?>" id="btn-hide-form" class="g-button" />
			</div>
		
		</div>
		
		<div id="saved-address" style="position:relative;">
			<div class="campo_form"><label for="LOCALITY"><?=__('Comune')?>:</label>	
				<input type="text" id="LOCALITY" name="LOCALITY" class="input_form text" readonly="readonly" value="<?=$db->LOCALITY->val?>" style="width:256px; background-color:#fff; border:none">
			</div>
			<div class="campo_form"><label for="ADDRESS_BLD"><?=__('Indirizzo')?>:</label>	
				<input type="text" id="ADDRESS_BLD" name="ADDRESS_BLD" class="input_form text" readonly="readonly" value="<?=$db->ADDRESS_BLD->val?>" style="width:256px; background-color:#fff; border:none">
			</div>
			<input type="button" value="<?=__('Cambia indirizzo')?>" id="change-address" class="g-button" style="width:260px;"/>
</div>
</form>

</td><td id="content_map">
<div id="wizard_right">
<?php
print $html['gmaps'];
?>
</div>
<div class="clear"></div>
</td></tr>
<tr>
<th colspan="2">* campi obbligatori
</th>
</tr>
</table>
<?php
include_once FOOTER_AR;
?>