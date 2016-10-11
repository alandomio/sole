<?php
# V.0.1.8
include_once 'init.php';
$user = new autentica($aA1);
$user -> login_standard();

$scheda = new nw('meters');
$crud = empty($id) ? 'ins' : 'upd';

$scheda -> ext_table(array('meterpropertys', 'outputs', 'supplytypes','rfs'));

// AGGIUNGO CHECKBOX MOLTI A MOLTI
$scheda -> add_mm('usages', $id);

// CONFIGURAZIONE NW

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

//$aCurs=array("icursor"=>$icursor);
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

$_POST = request::adjustPost($_POST);
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

$db -> ID_TYPE -> id = 'idtype';
$db -> ID_TYPE -> addblank = 1;
$db -> ID_TYPE -> txtblank = S_CHOOSE.' '.ID_TYPE;
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

ob_start();
?>
<div class="action_puls">
<button type="button" class="ui-button ui-state-default ui-corner-all ui-button-text-only"  value="<? print SAVE; ?>"><span class="ui-button-text"><? print SAVE; ?></span></button>
</div>
<form id="contatore" method="post" enctype="multipart/form-data" action="ajax/json.php?action=crud_contatore">
<?php
print $h_id;
print request::hidden($backUri)
?>
<table class="list personal">

<tr><td valign="top" width="200"><div class="lbl"><?=ID_TYPE?></div><? $db -> ID_TYPE -> get(); ?></td><td>
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
	if(idtype.val() == 1){ // ID TYPE
		$('.idtype').css('display','block');
	}
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
		if(idtype.val() == 1){ // ID TYPE
			$('.idtype').css('display','block');
		}
		else{
			$('.idtype').css('display','none');
		}
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
<?php
$html['contatori'] = ob_get_clean();

# PAGINA HTML
include_once HEAD_AR;

print $html['contatori'];

include_once FOOTER_AR;
?>