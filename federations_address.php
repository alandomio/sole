<?php
# V.0.1.8
include_once 'init.php';
list($il, $jsstatis) = request::get(array('il' => NULL,'jsstatis' => NULL));

$user = new autentica($aA2);
$user -> login_standard();
include_once stringa::get_conffile($MYFILE -> filename);

$scheda -> img = false;

$MYFILE -> add_js('
<script type="text/javascript">
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
</script>' ,'code', 'head');

$MYFILE -> add_js('
<script type="text/javascript">
function invia() {
	var str = $("form").serialize();
	//$("#result").text(str);
	$(\'#ajax_wizard\').load(\'federations_address_bk.php\', str, function(){
		addChange();
		geocodifica();
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
</script>' ,'code', 'head');

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

# AGGIUNGO I CAMPI NASCOSTI NELLA SCHEDA PRINCIPALE
$aShowCrud[] = 'LAT_FED';
$aShowCrud[] = 'LNG_FED';

$sublable = arr::arr2constant($aShowCrud, true);

$rec=rs::rec2arr($qrs=$qTotRec." ".$fil);
$db=new dbio();
$aRet = rs::showfull($atabelle, $rec,$lable,$add=array(),$self_join=array(), $my_vars);

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
//$rec = arr::_trim($rec,array('DESCRIP'));


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

$href_new = ''; $href_lista = '';
if($user -> idg == 1){
	$send_nuovo = arr::_unset($backUri, array('id', 'jsstatis'));
	$href_new = io::a($scheda->file_c, array_merge($send_nuovo, array('crud' => 'ins')), L_NUOVO , array('title' => 'Aggiungi nuovo record', 'class' => 'g-button'));

	$href_lista = io::a($scheda->file_l, $val, LISTA, array('title' => LISTA, 'class' => 'g-button'));
}


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

$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);
$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);

$sgmaps = ''; $html['gmaps'] = '';

$coords = DEF_COORDS;
if(!empty($db -> LAT_FED -> val)){
	$coords = $db -> LAT_FED -> val.', '.$db -> LNG_FED -> val;
}

$MYFILE -> add_js('<script type="text/javascript" src="js/maps/maps.js"></script>', 'file', 'head');
$MYFILE -> add_js('<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>', 'file', 'head');

$html['gmaps'] = '<div id="map_canvas"></div>
';

$db -> LAT_FED -> id = 'lat';
$db -> LNG_FED -> id = 'lng';

$db -> ADDRESS_FED -> id = 'indirizzo';
$db -> K0_ID_STATI -> id = 'stato';
$db -> K0_ID_REGIONI -> id = 'regione';
$db -> K0_ID_PROVINCE -> id = 'provincia';
$db -> K0_ID_COMUNI -> id = 'comune';
 
$db -> LAT_FED -> type = 'hidden';
$db -> LNG_FED -> type = 'hidden';

$db -> ADDRESS_FED -> css = 'dtrenta';
include_once HEAD_AR;
print $sub_menu;
if($crud == 'upd'){
}
?>
<table class="dark">
<tr class="bg">
<th colspan="2"><?=$href_lista?> <?=$href_new?> <input id="puls_save" type="button" value="<?=SAVE?>" class="g-button g-button-yellow" /> <div id="result"></div></th>
</tr>
<tr><td valign="top" style="width:250px">
<form id="myForm" name="myForm" method="post" action="<?=$action?>">
<input type="hidden" name="subDo" id="subDo" value="" />
<div id="wizard">
<?php
include_once AJAX.'federations_form.php';
?>
<? $db -> ADDRESS_FED -> get(); ?>
<br />
<? $db -> LAT_FED -> get(); ?> <? $db -> LNG_FED -> get(); ?>
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
ob_start();
?>
<script type="text/javascript">
$(document).ready(function(){
	var lat = $('#lat').val();
	var lng = $('#lng').val();
	var myOptions = {
		zoom: 14,
		center: new google.maps.LatLng( lat, lng),
		mapTypeId: google.maps.MapTypeId.ROADMAP
	};
	map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
	initialize_marker(map);
	$('#indirizzo').typing({
		 stop: function (event, $elem) {
			geocodifica(map);
		 },
		 delay: 500
	});

	$('#puls_save').click(function(){
		$('#subDo').val('Save');
		$('#myForm').submit();
	});
})
</script>
<?php
$gmaps_code = ob_get_clean();

$MYFILE -> add_js('<script type="text/javascript" src="js/jquery/jquery.typing.js"></script>', 'file', 'footer');
$MYFILE -> add_js($gmaps_code, 'code', 'footer');
include_once FOOTER_AR;
?>