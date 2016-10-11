<?php
# PAGINA CONTATORI
list($PSW_RPT, $IS_INFORM, $IS_PRIVACYFLAT) = request::get(array('PSW_RPT' => NULL, 'IS_INFORM' => '0', 'IS_PRIVACYFLAT' => '0'));
$scheda = new nw('users');
$crud ='ins';

// CONFIGURAZIONE FILTRI
$my_vars = new ordinamento(array());
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
$qTotRec = "SELECT * FROM ".$scheda->table;

$atabelle=$scheda->atable;
$lable=rs::sql2lbl($qTotRec);
$rec=rs::rec2arr($qrs=$qTotRec." ".$fil);

unset($lable['ID_HCOMPANY']);

$db = new dbio();
$aRet = rs::showfull($atabelle, $rec,$lable,$add=array(),$self_join=array(), $my_vars);

list($db->a_name, $db->a_val,$db->a_type,$db->a_maxl,$db->a_default,$db->a_not_null,$db->a_lable,$db->a_dec,$db->a_fkey,$db->a_aval,$db->a_addblank,$db->a_comment,$db->a_sql_type, $db->a_js, $db->a_disabled, $aFval) = $aRet;

$_POST = request::adjustPost($_POST);
$rec = arr::magic_quote($rec);
$rec = arr::_trim($rec,array('DESCRIP'));

$db -> a_val = array_merge($db->a_val, $rec);
$db -> dbset();

$input['PSW_RPT'] = new io();
$input['PSW_RPT'] -> type = 'password';
$input['PSW_RPT'] -> maxl = 64;
$input['PSW_RPT'] -> set('PSW_RPT');
$input['PSW_RPT'] -> id = 'rpt_password';
$input['PSW_RPT'] -> val = $PSW_RPT;

$input['IS_INFORM'] = new io();
$input['IS_INFORM'] -> type = 'checkbox';
$input['IS_INFORM'] -> set('IS_INFORM');
$input['IS_INFORM'] -> id = 'is_inform';
$input['IS_INFORM'] -> lable = IS_INFORM;

//if($IS_INFORM == 0)
$input['IS_INFORM'] -> val = 1;
$input['IS_INFORM'] -> checked = false;

$input['IS_PRIVACYFLAT'] = new io();
$input['IS_PRIVACYFLAT'] -> type = 'checkbox';
$input['IS_PRIVACYFLAT'] -> set('IS_PRIVACYFLAT');
$input['IS_PRIVACYFLAT'] -> id = 'is_inform';
$input['IS_PRIVACYFLAT'] -> lable = IS_PRIVACYFLAT;
$input['IS_PRIVACYFLAT'] -> val = 1;
$input['IS_PRIVACYFLAT'] -> checked = false;

$db -> PASSWORD -> id = 'password';

$qF = "SELECT flats.*,
		hcompanys.NAME_HC,
		hcompanys.ADDRESS_HC,
		hcompanys.REFERENCE_HC,
		buildings.*
		FROM flats
		Left Join buildings ON flats.ID_BUILDING = buildings.ID_BUILDING
		Left Join hcompanys ON buildings.ID_HCOMPANY = hcompanys.ID_HCOMPANY
		WHERE flats.ACTIVATION_CODE='$code'";
$rF = rs::rec2arr($qF);

$flat_info = CODE_FLAT.': '.$rF['CODE_FLAT'].' - '.CODE_BLD.': '.$rF['CODE_BLD'].' '.$rF['ADDRESS_BLD'];

// variabili stampate nel testo della privacy
$hc_name = $rF['NAME_HC']; 
$hc_address = $rF['ADDRESS_HC']; 
$hc_reference = $rF['REFERENCE_HC']; 

ob_start();
include BLOCCHI_AR.'privacy_'.LANG_DEF.'.php';
$txt = ob_get_clean();
$txt = utf8_encode($txt);
?>
<h3 class="margin_sugiu"><?=$flat_info?></h3>
<table class="list neutra">
<input type="hidden" name="ACTIVATION_CODE" value="<?=$code?>" />
<tr><td valign="top"><div class="lbl"><strong><?=USER?>*</strong></div></td><td valign="top"><? $db -> USER -> get(); ?></td></tr>
<tr><td valign="top"><div class="lbl"><strong><?=PASSWORD?>*</strong></div></td><td valign="top"><? $db -> PASSWORD -> get(); ?></td></tr>
<tr><td valign="top"><div class="lbl"><strong><?=PSW_RPT?>*</strong></div></td><td valign="top"><? $input['PSW_RPT'] -> get();  ?></td></tr>
<tr><td valign="top"><div class="lbl"><strong><?=NAME?>*</strong></div></td><td valign="top"><? $db -> NAME -> get(); ?></td></tr>
<tr><td valign="top"><div class="lbl"><strong><?=SURNAME?>*</strong></div></td><td valign="top"><? $db -> SURNAME -> get(); ?></td></tr>
<tr><td valign="top" colspan="2">Privacy<br /><textarea cols="165" rows="3"><?=$txt?></textarea>
<br /><strong><? $input['IS_PRIVACYFLAT'] -> get(); ?>*</strong>
<br /><? $input['IS_INFORM'] -> get(); ?>
<br /><strong>* <?=NOT_NULL_ERR?></strong>
</td></tr>
</table>  

