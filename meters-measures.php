<?php
# V.0.1.8
include_once 'init.php';
$user = new autentica($aA5);
$user -> login_standard();

# VARIABILI DI DEFAULT
list($id) = request::get(array('id' => NULL));


if(!empty($id)){ # CALCOLO IL TITOLO PER QUESTA PAGINA BUILDING > CONTATORE
	$qt ="SELECT
	buildings.BUILDING,
	meters.MATRICULA_ID,
	meters.REGISTERNUM
	FROM
	flats_meters
	Inner Join flats ON flats_meters.ID_FLAT = flats.ID_FLAT
	Inner Join buildings ON flats.ID_BUILDING = buildings.ID_BUILDING
	Inner Join meters ON flats_meters.ID_METER = meters.ID_METER
	WHERE flats_meters.ID_METER = '$id'
	LIMIT 0,1
	";
	$rt = rs::rec2arr($qt);
	
	$title = $rt['BUILDING'];
	if(!empty($rt['MATRICULA_ID']) || !empty($rt['REGISTERNUM'])) $title .= ' &rsaquo; ';
	$flag = false;
	if(!empty($rt['REGISTERNUM'])){
		$title .= $rt['REGISTERNUM'];
		$flag = true;
	}
	if(!empty($rt['MATRICULA_ID'])){
		if($flag) $title .= ' - ';
		$title .= $rt['MATRICULA_ID'];
	}
	if(!empty($title))	$MYFILE -> set_title($title);
}



# PAGINA CONTATORI
$scheda = new nw('meters');
$crud = empty($id) ? 'ins' : 'upd';

$scheda -> ext_table(array('meterpropertys', 'outputs', 'supplytypes','rfs', 'metertypes'));

// AGGIUNGO CHECKBOX MOLTI A MOLTI
$scheda -> add_mm('usages', $id);

// MOLTI A MOLTI
$scheda -> many_to_many(array('flats_meters' => array(
								'id' 	=> 'ID_FLAT',
								'title' => 'REGISTERNUM',
								'ext'	=> 'flats',
								'where'	=> "ID_METER",
								'lbl'	=> "Contatori",
								'file' => 'meters_flats_ext.php'
						)
					)
				);
$scheda -> many_to_many_tot($id);

// CONFIGURAZIONE FILTRI
$my_vars = new ordinamento(array('rn' => 'REGISTERNUM', 'mp' => 'METERPROPERTY', 'do' => 'IS_DOUBLE', 'ou' => 'OUTPUT', 'sp' => 'SUPPLYTYPE'));

$my_vars->sort_default($scheda->f_id);
$my_vars->tabella = $scheda->table;

// QUERY 
$fil = "";
$fil = is_null($crud) ? "" : $fil;
$fil = $crud == "ins" ? " WHERE 1=0 " : $fil;
$fil = $crud == "upd" ? " WHERE ".$scheda->table.".".$scheda->f_id."='$id' " : $fil; 

$aFromp=array();
$aFiltro=array();
$backUri=array_merge($my_vars -> href, $my_vars -> hidden);
$backUri['id'] = $id;
$qTotRec="SELECT * FROM ".$scheda->table;


$atabelle=$scheda->atable;
$lable=rs::sql2lbl($qTotRec);
$rec=rs::rec2arr($qrs=$qTotRec." ".$fil);

$db=new dbio();
$aRet = rs::showfull($atabelle, $rec,$lable,$add=array(),$self_join=array(), $my_vars);

list($db->a_name, $db->a_val,$db->a_type,$db->a_maxl,$db->a_default,$db->a_not_null,$db->a_lable,$db->a_dec,$db->a_fkey,$db->a_aval,$db->a_addblank,$db->a_comment,$db->a_sql_type, $db->a_js, $db->a_disabled, $aFval) = $aRet;
$db->dbset();

// !! BASTERÀ FARLO UNA VOLTA
$_POST = request::adjustPost($_POST);
//$rec = request::post2arr($sublable);
$rec = arr::magic_quote($rec);
$rec = arr::_trim($rec,array('DESCRIP'));

$db -> a_val = array_merge($db->a_val,$rec);
$db -> dbset();

$val = $backUri;

$href_lista = io::a($scheda->file_l, $val, LISTA, array('title' => LISTA, 'class' => 'g-button'));

$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);
$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);

$db -> ID_METERTYPE -> id = 'idmetertype';
$db -> ID_METERTYPE -> addblank = 1;
$db -> ID_METERTYPE -> txtblank = S_CHOOSE.' '.ID_METERTYPE;
$db -> IS_DOUBLE -> lable = '';
$db -> IS_DOUBLE -> id = 'is_double';
$db -> ID_RF -> id = 'rf';
$db -> ID_RF -> addblank = 1;
$db -> ID_RF -> txtblank = S_CHOOSE.' '.RF;
$db -> ID_OUTPUT -> id = 'ab';
$db -> D_FROM_MT -> type = 'text';
$db -> D_FROM_MT -> css = 'datepicker';
$db -> D_TO_MT -> type = 'text';
$db -> D_TO_MT -> css = 'datepicker';

$h_id = '<input type="hidden" id="id_meter" value="'.$id.'" />';

ob_start();
?>
<div id="tabs">
<ul id="jq_nav">
    <li><a href="#tabs-1"><?php print METER; ?></a></li>
    <li><a href="#tabs-2"><?php print ADD.' '.MEASURE; ?></a></li>
    <li><a href="#tabs-3"><?php print MEASURES; ?></a></li>
    <li><a href="#tabs-4"><?php print FLATS; ?></a></li>
</ul>

<div id="tabs-1" class="tabpage">
<div class="action_puls">
<button type="button" class="ui-button ui-state-default ui-corner-all ui-button-text-only"  value="<? print SAVE; ?>"><span class="ui-button-text"><? print SAVE; ?></span></button>
</div>
<form id="contatore" method="post" enctype="multipart/form-data" action="ajax/json.php?action=crud_contatore">
<?php
print $h_id;
print request::hidden($backUri)
?>
<table class="list personal">

<tr><td valign="top" width="200"><div class="lbl"></div></td><td>
<div class="idtype">
<div class="bfloat"><div class="lbl"><?=HMETER?></div><? $db -> HMETER -> get(); ?></div>
</div>
</td></tr>

<tr><td valign="top"><div class="lbl"><?=ID_METERTYPE?></div><? $db -> ID_METERTYPE -> get(); ?></td><td>
<div class="boxinput"><div class="lbl"><?=START_1?></div><? $db -> START_1 -> get(); ?></div>
<div class="hourly_m"><div class="lbl"><?=START_2?></div><? $db -> START_2 -> get(); ?></div>
<div class="hourly_m"><div class="lbl"><?=START_3?></div><? $db -> START_3 -> get(); ?></div>
<div class="boxinput"><div class="lbl"><?=END_1?></div><? $db -> END_1 -> get(); ?></div>
<div class="hourly_m"><div class="lbl"><?=END_2?></div><? $db -> END_2 -> get(); ?></div>
<div class="hourly_m"><div class="lbl"><?=END_3?></div><? $db -> END_3 -> get(); ?></div>
</td></tr>

<tr><td valign="top"><div class="lbl"><?=RF?></div><? $db -> ID_RF -> get(); ?></td><td>
<div class="rf">
<div class="bfloat"><div class="lbl"><?=FORMULA?></div><? $db -> FORMULA -> get(); ?> <?=io::a('#', array(), 'Formula', array())?></div>
</div>
</td></tr>

<tr><td valign="top"><div class="lbl"><?=ID_OUTPUT?></div><? $db -> ID_OUTPUT -> get(); ?></td><td>
<div class="ab">
<div class="bfloat"><div class="lbl"><?=A?></div><? $db -> A -> get(); ?> <?=io::a('#', array(), 'A', array())?></div>
<div class="bfloat"><div class="lbl"><?=B?></div><? $db -> B -> get(); ?> <?=io::a('#', array(), 'B', array())?></div>
</div>
</td></tr>

<tr><td valign="top"><div class="lbl"><?=IS_DOUBLE?></div><? $db -> IS_DOUBLE -> get(); ?></td><td></td></tr>
<tr><td valign="top"><div class="lbl"><?=REGISTERNUM?></div><? $db -> REGISTERNUM -> get(); ?></td><td></td></tr>
<tr><td valign="top"><div class="lbl"><?=MATRICULA_ID?></div><? $db -> MATRICULA_ID -> get(); ?></td><td></td></tr>
<tr><td valign="top"><div class="lbl"><?=ID_METERPROPERTY?></div><? $db -> ID_METERPROPERTY -> get(); ?></td><td></td></tr>
<tr><td valign="top"><div class="lbl"><?=SUPPLYTYPE?></div><? $db -> ID_SUPPLYTYPE -> get(); ?></td><td></td></tr>
<tr><td valign="top"><div class="lbl"><?=SCALA_MT?></div><? $db -> SCALA_MT -> get(); ?></td><td></td></tr>
<tr><td valign="top" colspan="2"><div class="lbl"><?=constant(stringa::tbl2field('usages'))?></div>
<?=$scheda -> mmBox['usages']?></td></tr>
</table>  
</form>
<script type="text/javascript">
$(document).ready(function(){
	var sel = $('#idmetertype');
	var rf = $('#rf');
	var idtype = $('#idtype');
	var ab = $('#ab');
	
	
	if(sel.val() == 2){
		$('.hourly_m').css('display','block');
	}
	if(rf.val() == 2){
		$('.rf').css('display','block');
	}
//	if(idtype.val() == 1){ // ID TYPE
//		$('.idtype').css('display','block');
//	}
	if(ab.val() == 2){ // OUTPUT
		$('.ab').css('display','block');
	}


	sel.change(function() {
		if(sel.val() == 2){ // Electricity hourly meter
			$('.hourly_m').css('display','block');
			
		}
		else{ 
			$('.hourly_m').css('display','none');
		}
	});
	
	$('#is_double').change(function()	{
		if($('#is_double').is(':checked'))
			$('.double_m').css('display','block');
		else
			$('.double_m').css('display','none');
	});
	
	rf.change(function() {
		if(rf.val() == 2){ // REAL / FORMULA
			$('.rf').css('display','block');
		}
		else{
			$('.rf').css('display','none');
		}
	});
	
	idtype.change(function() {
//		if(idtype.val() == 1){ // ID TYPE
//			$('.idtype').css('display','block');
//		}
//		else{
//			$('.idtype').css('display','none');
//		}
	});

	ab.change(function() {
		if(ab.val() == 2){ // OUTPUT
			$('.ab').css('display','block');
		}
		else{
			$('.ab').css('display','none');
		}
	});
	
});
</script>
</div>
<?php
$html['contatori'] = ob_get_clean();

# PAGINA NUOVAMISURAZIONE
$sk_newms = new nw('measures');

$qm = "SELECT * FROM meters WHERE ID_METER = '$id'";
$rm = rs::rec2arr($qm);

$sk_newms -> ext_table(array('uploadtypes'));
$aShowCrud = ($sk_newms -> aFields);

// CONFIGURAZIONE FILTRI
$vars_newms = new ordinamento(array('dm' => 'D_MEASURE', 'ic' => 'IS_CONFIRMED_MS', 'ym' => 'ANNO_MS', 'ut' => 'UPLOADTYPE'));
$vars_newms->tabella = $sk_newms->table;

$fil = " WHERE 1=0 ";

$aFromp=array();
$aFiltro=array();
$backUri=array_merge($vars_newms -> href, $vars_newms -> hidden);

$qTotRec="SELECT * FROM ".$sk_newms -> table;
$atabelle = $sk_newms -> atable;
$lable = rs::sql2lbl($qTotRec);

$sublable = arr::arr2constant($aShowCrud, true);
$rec=rs::rec2arr($qrs=$qTotRec." ".$fil);

$db_ms=new dbio();
$aRet = rs::showfull($atabelle, $rec, $lable, array(), array('ID_METER', 'D_MEASURE'), $vars_newms);
list($db_ms->a_name, $db_ms->a_val,$db_ms->a_type,$db_ms->a_maxl,$db_ms->a_default,$db_ms->a_not_null,$db_ms->a_lable,$db_ms->a_dec,$db_ms->a_fkey,$db_ms->a_aval,$db_ms->a_addblank,$db_ms->a_comment,$db_ms->a_sql_type, $db_ms->a_js, $db_ms->a_disabled, $aFval) = $aRet;

$db_ms->dbset();

$db_ms -> D_MEASURE -> type = 'text';
$db_ms -> D_MEASURE -> css = 'datepicker';
$db_ms -> ID_METER -> type = 'hidden';
$db_ms -> ID_METER -> val = $id;

ob_start();
?>
<div id="tabs-2" class="tabpage">
<div class="action_puls">
<button type="button" class="ui-button ui-state-default ui-corner-all ui-button-text-only"  value="<? print SAVE; ?>"><span class="ui-button-text"><? print SAVE; ?></span></button>
</div>
<form id="add_ms" method="post" action="ajax/json.php?action=add_measure">
<?php $db_ms -> ID_METER -> get(); ?>
<?=request::hidden($backUri)?>
<table class="list personal">
<tr><td valign="top" width="200"><div class="lbl"><?=D_MEASURE?></div><? $db_ms -> D_MEASURE -> get(); ?></td></tr>
<tr><td valign="top"><div class="lbl"><?=F1?></div><? $db_ms -> F1 -> get(); ?></td></tr>
<tr><td valign="top"><div class="hourly_m"><div class="lbl"><?=F2?></div><? $db_ms -> F2 -> get(); ?></div></td></tr>
<tr><td valign="top"><div class="hourly_m"><div class="lbl"><?=F3?></div><? $db_ms -> F3 -> get(); ?></div></td></tr>
<tr class="double_m"><td valign="top"><div class=""><div class="lbl "><?=O1?></div><? $db_ms -> O1 -> get(); ?></div></td></tr>
<tr class="double_m"><td valign="top" class="hourly_m"><div class=""><div class="lbl"><?=O2?></div><? $db_ms -> O2 -> get(); ?></div></td></tr>
<tr class="double_m"><td valign="top" class="hourly_m"><div class=""><div class="lbl"><?=O3?></div><? $db_ms -> O3 -> get(); ?></div></td></tr>
</table>  
</form>
</div> 
<?php
$html['nuovamisurazione'] = ob_get_clean();

# PAGINA LISTA MISURAZIONI
$measures = blocks::lista_misurazioni($id, false);
$html['listamisurazioni'] = $measures['list'];

# PAGINA APPARTAMENTI


//$rs = sole::get_flats_by_idbuilding($id_building);

$rs = array();
$lista=''; $cnt=0;
foreach($rs as $rec){ # CREO LA LISTA
	$celle='';
	foreach($sublable as $k=>$v){
		$color = $cnt%2==0 ? '' : ' class="contrast"';
		if(substr($k,0,2)=='D_') $rec[$k] = dtime::my2iso($rec[$k]); // DATA
		if(substr($k,0,3)=='DT_') $rec[$k] = dtime::my2isodt($rec[$k]); // DATA
		elseif(substr($k,0,3)=='IS_') $rec[$k] = !empty($rec[$k]) ? '<div class="icon_check_att"></div>' : '<div class="icon_check_dis"></div>'; // SI NO
		elseif(substr($k,0,3)=='ID_' || $k == $sk_app->f_path) $rec[$k]; // IMMAGINE
		elseif($k == 'EMAIL'){ $rec[$k]; /* NON FACCIO NIENTE */ }
		else {
			$rec['FULL_'.$k] = $rec[$k];
			$rec[$k] = strcut($rec[$k],'...',30);
		} 
		$celle.='<td>'.$rec[$k].'</td>';
	}
	if(array_key_exists($rec[$sk_app -> f_id], $ext_ids)) {
		$ck_multiplo =  '<input type="checkbox" id="ck'.$rec[$sk_app->f_id].'" id="ck'.$rec[$sk_app->f_id].'" name="ck'.$rec[$sk_app->f_id].'" onclick="update('.$rec[$sk_app->f_id].', '.$id.');" value="1" class="checkbox" checked="checked" />';
	}
	else $ck_multiplo = '<input type="checkbox" id="ck'.$rec[$sk_app->f_id].'" name="ck'.$rec[$sk_app->f_id].'" onclick="update('.$rec[$sk_app->f_id].', '.$id.');"  value="1" class="checkbox"  />';
	
	$href_mod = '';

	if(array_key_exists('title',$aInfo)){
		$info = strlen($rec['FULL_'.$aInfo['description']])>0 ? '<a href="'.$rec['FULL_'.$aInfo['description']].'" onclick="return false;" class="Tips1" title="'.$rec['FULL_'.$aInfo['title']].'"><img src="'.IMG_AR.'icon_info.gif" /></a>' : '';
	} else { $info = ''; }
	$lista.='<tr'.$color.' valign="top">'.$celle.'
	<td class="comandi_lista">'.$info.$href_mod.$ck_multiplo.'
	</td></tr>'."\n";
	$cnt++;
}

$vars_back = $_GET;
$vars_back['id'] = ''; unset($vars_back['id']);
$vars_back['ic'] = ''; unset($vars_back['ic']);
$vars_back['crud'] = ''; unset($vars_back['crud']);
$vars_back['err'] = ''; unset($vars_back['err']);
$vars_back['ack'] = ''; unset($vars_back['ack']);

$puls_aggiorna = '<input name="upd" type="submit" value="aggiorna" class="button_aggiorna floatright">';

//$href_new = io::ahrefcss($sk_app->file_c, $val=array_merge($backUri,array('crud' => 'ins')), $txt=L_NUOVO,$js='',$target="",$title=L_NUOVO, $id='', $css="g-button");
//$sel_all = '<?=S_SELALL?> <input type="checkbox" class="checkbox" onchange="javascript:check_all_by_name(\'list\', \'ck\')" name="ck" value="1" />';
//$puls_elimina = '<input name="del" type="submit" class="button_elimina" value="<?=DELETE_SELECTED?>" />';
//if($sk_app->action_type=='read') { $href_new = ''; $sel_all=''; $puls_elimina='';}
//$vars_app->campi_ricerca();

$a_lista_edifici = sole::building_user($user -> aUser['ID_USER']); # SOLO GLI EDIFICI CORRELATI ALL'UTENTE
foreach($a_lista_edifici as $kb => $bld){
	$a_vals[$bld['ID_BUILDING']] = $bld['CODE_BLD'];
}

$input['buildings1'] = new io();
$input['buildings1'] -> type = 'select'; 
$input['buildings1'] -> addblank = true; 
$input['buildings1'] -> aval = $a_vals; 
$input['buildings1'] -> css = 'trecento'; 
$input['buildings1'] -> id = 'buildings'; 
$input['buildings1'] -> txtblank = '- Scegli edificio'; 
$input['buildings1'] -> set('buildings');

ob_start();

?>
<table>
<tr><td valign="top" style="width:250px; border-right:1px solid #000;">
<form id="myForm" method="post">
<div id="wizard">
<div id="wizard1">
<span><?=CH_BUILDING?>:</span>
<? $input['buildings1'] -> get(); ?>
</div>
</div>
</form>
</td><td >

<table class="list" style="border:none;">
<?=$lista?>
</table>
<div class="clear"></div>
</td></tr>
<tr>
<th colspan="2">* campi obbligatori
</th>
</tr>
</table>


</div>
<script>
	$(function() {
		$( "#tabs" ).tabs({selected: 0	});
	});
	
	$(document).ready(function(){
		if($('#id_meter').val() > 0){
			$('#jq_nav').css('display','block');
		}
		$('#tabs-1 button').click(function() {
			$("#contatore").ajaxSubmit(function(data){
						if(data.success){
							$('#message').html(data.message);
							$('#jq_nav').css('display','block');
							if(data.mode == 'insert'){
								var url = window.location + '&id=' + data.id
								document.location.href = url;
							}
						}
						else	{
							$('#message').html(data.message);
						}
						$('#message').delay(100).slideDown(700);
						$('#message').delay(5000).slideUp(700);
					});
		});
	});
	
	$(document).ready(function()	{
		$('#tabs-2 button').click(function() {
			$("#add_ms").ajaxSubmit(function(data){
						if(data.success){
							$('#message').html(data.message);
							var id_contatore = $('#id_meter').val();
							jQuery.getJSON("ajax/json.php?action=get_measures_list&id_contatore="+id_contatore,
							function(data){	
								$('#tabs-3').html(data.list);
							});
							
							
							$( "#tabs" ).tabs( "option", "selected", 2 );
						}
						else{
							$('#message').html(data.message);
						}
						$('#message').delay(100).slideDown(700);
						$('#message').delay(5000).slideUp(700);
					});
		});
	});	
	
	function update(appartamento, contatore) {
		valore = $('#ck' + appartamento).is(":checked");
		jQuery.getJSON("ajax/json.php?action=put_contatore_appartamento&id_contatore="+contatore+'&id_appartamento=' + appartamento + '&valore=' + valore,
				function(data){	
					
				});
	}
	
	function dele_measure(id) {
		jQuery.getJSON("ajax/json.php?action=dele_measure&id_measure=" + id,
			function(data){	
			if(data.success){
				$('#line' + id).toggle();
			}
			$('#message').html(data.message);
		});
	}

</script>
<?php
$html['appartamenti'] = ob_get_clean();




include_once HEAD_AR;

print $html['contatori'];
print $html['nuovamisurazione'];
print '<div id="tabs-3" class="tabpage">'.$html['listamisurazioni'].'</div>';
print '<div id="tabs-4" class="tabpage">'.$html['appartamenti'].'</div>';

include_once FOOTER_AR;
?>