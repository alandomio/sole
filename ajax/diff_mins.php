<?php
include_once '../init.php';

list($jsusages) = request::get(array('jsusages' => NULL));

# PAGINA CONTATORI
$scheda = new nw('meters');
$crud ='ins';
$scheda -> ext_table(array('meterpropertys', 'outputs', 'supplytypes','rfs', 'metertypes'));
$scheda -> descriptors('descriptors', array('K2_ID_USAGE'));
// AGGIUNGO CHECKBOX MOLTI A MOLTI
//$scheda -> add_mm('usages', false);

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

 
// lista misuratori per l'edificio ($id)
$aListMeters = sole::get_meters_by_idbuilding($id);
$select_meters = '';
foreach($aListMeters as $mis){
	$select_meters .= '<option value="'.$mis['ID_METER'].'">'.$mis['CODE_METER'].'</option>'."\n";
}
unset( $aListMeters );

// predispongo i vari select
$select_sum_divisional = '<select name="SUM_DIVISIONAL" id="SUM_DIVISIONAL"><option value="0">'.CHOOSE.'</option>'.$select_meters.'</select>';
unset($select_meters);

/* OK */


$db=new dbio();
$aRet = rs::showfullpersonal($atabelle, $rec, $lable, array(), array('CODE_METER'), array(
'ID_OUTPUT' => "SELECT ID_OUTPUT, OUTPUT from outputs ORDER BY ID_OUTPUT ASC"), $my_vars);	

list($db->a_name, $db->a_val,$db->a_type,$db->a_maxl,$db->a_default,$db->a_not_null,$db->a_lable,$db->a_dec,$db->a_fkey,$db->a_aval,$db->a_addblank,$db->a_comment,$db->a_sql_type, $db->a_js, $db->a_disabled, $aFval) = $aRet;

$_POST = request::adjustPost($_POST);
$rec = arr::magic_quote($rec);
$rec = arr::_trim($rec,array('DESCRIP'));

$db -> a_val = array_merge($db->a_val,$rec);
$db -> dbset();

$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);
$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);

$db -> ID_METERTYPE -> id = 'idmetertype';
$db -> ID_METERTYPE -> addblank = 1;
$db -> ID_METERTYPE -> txtblank = S_CHOOSE.' '.ID_METERTYPE;

$db -> MATRICULA_ID -> id = 'MATRICULA_ID';

$db -> IS_DOUBLE -> lable = '';
// $db -> IS_DOUBLE -> id = 'is_double';
$db -> ID_RF -> id = 'rf';
$db -> ID_RF -> addblank = 1;
$db -> ID_RF -> txtblank = S_CHOOSE.' '.RF;

$db -> ID_OUTPUT -> id = 'ab';
$db -> ID_OUTPUT -> addblank = 1;
$db -> ID_OUTPUT -> txtblank = S_CHOOSE.' output';

$db -> ID_METERPROPERTY -> addblank = 1;
$db -> ID_METERPROPERTY -> txtblank = S_CHOOSE;
$db -> ID_METERPROPERTY -> id = 'idmeterproperty';

$db -> SCALA_MT -> id = 'scalamt';

$db -> D_FROM_MT -> type = 'text';
$db -> D_FROM_MT -> css = 'datepicker';
$db -> D_TO_MT -> type = 'text';
$db -> D_TO_MT -> css = 'datepicker';

$db -> ID_SUPPLYTYPE -> addblank = 1;
$db -> ID_SUPPLYTYPE -> txtblank = S_CHOOSE;
$db -> ID_SUPPLYTYPE -> id = 'idsupplytype';

$db -> ID_DIRECTION -> id = 'iddirection';

$db -> START_2 -> id = 'start_2';
$db -> START_3 -> id = 'start_3';
$db -> END_2 -> id = 'end_2';
$db -> END_3 -> id = 'end_3';

$h_id = '<input type="hidden" id="id_meter" value="'.$id.'" />';

$db -> K2_ID_USAGE -> type = 'radio';

/*
ID_METERTYPE	 METERTYPE_IT / EN	  STYPE		UNIT
1					 Energia elettrica     Eel       kWhe
2					 Energia termica       Ete       kWht
3					 Gas Naturale          Gas       Nm3
4					 Teleriscaldamento     Tel       kWht
5 					 Acqua                 Acq       M3
6					 Acqua calda           Aca       M3
*/
$db -> HMETER -> aval = array('1' => HM_NORMAL, '3' => HM_HOURLY);
$db -> HMETER -> def = 1;
$db -> HMETER -> id = 'hmeter';
$db -> HMETER -> css = 'centocinquanta';
$db -> HMETER -> type = 'select';

$db -> FORMULA -> readonly = 'readonly';
$db -> FORMULA -> readonly = 'readonly';
$db -> FORMULA -> id = 'mk-formula';

$db -> A -> readonly = 'readonly';
$db -> A -> title = '0';
$db -> A -> css = 'a_b';
$db -> A -> id = 'a';

$db -> B -> readonly = 'readonly';
$db -> B -> title = '1';
$db -> B -> css = 'a_b';
$db -> B -> id = 'b';

$db -> D_FIRSTVALUE -> type = 'text';
$db -> D_FIRSTVALUE -> css = 'datepicker';
$db -> D_FIRSTVALUE -> id = 'D_FIRSTVALUE';
?>
<div class="action_puls">
<input type="button" id="save_new" class="g-button g-button-yellow" value="<?=SAVE; ?>" />
</div>



<form id="contatore" method="post" action="ajax/json.php?action=crud_contatore">
<?php
print $h_id;
print request::hidden($backUri)
?>
<table class="list personal" id="tab-ins-meter">
<tr><td valign="top"><div class="lbl"><?=MATRICULA_ID?></div><? $db -> MATRICULA_ID -> get(); ?></td><td valign="top"><div class="lbl"><?=NAME_METER?></div><? $db -> NAME_METER -> get(); ?></td></tr>

<tr><td valign="top"><div class="lbl"><?=ID_METERTYPE?></div><? $db -> ID_METERTYPE -> get(); ?></td><td>

<!-- ELETTRICITÀ -->
<div class="hmeter">
<div class="lbl"><?=IS_DOUBLE?></div>
<div id="is_double"><? $db -> IS_DOUBLE -> get(); ?></div>
</div>
<div id="dim_impianto" class="dn"><div class="lbl"></div><input type="text" name="SIZE" value="" /></div>
<div class="hmeter"><div class="lbl"><?=HMETER?></div><? $db -> HMETER -> get(); ?></div>
<div class="shformula"><div class="lbl"><?=D_FIRSTVALUE?></div><? $db -> D_FIRSTVALUE -> get(); ?></div>
<div class="boxinput shformula"><div class="lbl"><?=START_1?></div><? $db -> START_1 -> get(); ?></div>
<div class="hourly_m shformula"><div class="lbl"><?=START_2?></div><? $db -> START_2 -> get(); ?></div>
<div class="hourly_m shformula"><div class="lbl"><?=START_3?></div><? $db -> START_3 -> get(); ?></div>

<!-- ACQUA -->
<div id="watermeter" class="dn">
<div class="lbl"><?=ALTRE_UTENZE?></div>
<input type="checkbox" name="altre_utenze" />
<?=$select_sum_divisional?>
</div>

</td></tr>
<tr id="tr_rf"><td valign="top"><div class="lbl"><?=RF?></div><? $db -> ID_RF -> get(); ?></td><td>
<div class="rf">
<div class="bfloat"><div class="lbl"><?=FORMULA?></div><? $db -> FORMULA -> get(); ?></div>
</div>
</td></tr>
<tr><td valign="top"><div class="lbl"><?=ID_OUTPUT?></div><? $db -> ID_OUTPUT -> get(); ?></td><td>
<div class="ab">
<div class="bfloat"><div class="lbl"><?=A?></div><? $db -> A -> get(); ?></div>
<div class="bfloat"><div class="lbl"><?=B?></div><? $db -> B -> get(); ?></div>
</div>
</td></tr>
<tr class="shformula"><td valign="top"><div class="lbl"><?=ID_METERPROPERTY?></div><? $db -> ID_METERPROPERTY -> get(); ?></td><td></td></tr>
<tr><td valign="top"><div class="lbl"><?=SUPPLYTYPE?></div><? $db -> ID_SUPPLYTYPE -> get(); ?></td><td>
<tr><td valign="top"><div class="lbl"><?=DIRECTION?></div><? $db -> ID_DIRECTION -> get(); ?></td><td>

<div id="flats_list">
</div>

</td></tr>
<tr class="shformula"><td valign="top"><div class="lbl"><?=SCALA_MT?></div><? $db -> SCALA_MT -> get(); ?></td><td></td></tr>
<tr><td valign="top" colspan="2"><div class="lbl"><?=constant(stringa::tbl2field('usages'))?></div>
<? $db -> K2_ID_USAGE -> get(); ?>
<?php
//$scheda -> mmBox['usages']
?></td></tr>
</table>  
</form>