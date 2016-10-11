<?php
include_once 'init.php';
$user = new autentica($aA5);
$user -> login_standard();

# VARIABILI DI DEFAULT 
list($id) = request::get(array('id' => NULL));

if($user -> idg == 5){
	$qHhu = "SELECT 
	flats.ID_FLAT,
	flats.CODE_FLAT,
	flats.ID_BUILDING,
	hcompanys.ID_HCOMPANY,
	hcompanys.ID_FEDERATION
	FROM
	flats
	LEFT JOIN buildings USING(ID_BUILDING)
	LEFT JOIN hcompanys USING(ID_HCOMPANY)
	WHERE 
	flats.ID_USER = '".$user -> aUser['ID_USER']."'
	LIMIT 1
	";
	
	$rHhu = rs::rec2arr($qHhu);
	$html['select_building'] = '
	<input type="hidden" id="id_user" value="'.$user -> aUser['ID_USER'].'" /> 
	<input type="hidden" id="federations1" name="federations1" value="'.$rHhu['ID_FEDERATION'].'" />
	<input type="hidden" id="hcompanys1" name="hcompanys1" value="'.$rHhu['ID_HCOMPANY'].'" />
	<input type="hidden" id="buildings1" name="buildings1" value="'.$rHhu['ID_BUILDING'].'" />
	<input type="hidden" id="flats1" name="flats1" value="'.$rHhu['ID_FLAT'].'" />
	<strong>'.CODE_FLAT.': '.$rHhu['CODE_FLAT'].'</strong>
	';
} else {
	$html['select_building'] = sole::select_fhb('1');
}


$MYFILE -> add_js('<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>', 'file', 'head');
$MYFILE -> add_js('<script type="text/javascript" src="'.JS_MAIN.'outputs.js"></script>', 'file', 'head');

$initialize = $body_ini;
$input['mode2'] = new io();
$input['mode2'] -> type = 'select'; 
$input['mode2'] -> addblank = true;

$input['mode2'] -> aval = array(
	'NPVFULL' => LBL_NPVFULL,
	'NPVM2' => LBL_NPVM2,
	'NPVFULLEP' => LBL_NPVFULLEP,
	'NPVM2EP' => LBL_NPVM2EP,	
	'F1' => LBL_F1,
	'PRODUCTION' => LBL_PRODINDEX
);

$input['mode2'] -> css = 'trecento'; 
$input['mode2'] -> id = 'mode2'; 
$input['mode2'] -> txtblank = '- '.CHOOSE.' output'; 
$input['mode2'] -> set('mode');


$input['mode12'] = new io();
$input['mode12'] -> type = 'select'; 
$input['mode12'] -> addblank = true;

$input['mode12'] -> aval = array(
	'NPVFULL' => LBL_NPVFULL,
	'NPVM2' => LBL_NPVM2,
	'NPVFULLEP' => LBL_NPVFULLEP,
	'NPVM2EP' => LBL_NPVM2EP
);

$input['mode12'] -> css = 'trecento'; 
$input['mode12'] -> id = 'mode12'; 
$input['mode12'] -> txtblank = '- '.CHOOSE.' output'; 
$input['mode12'] -> set('mode');

$year = sole::select_year('year');
ob_start();
?>
<form id="myForm" method="post">
<table width="100%" class="clear">
<tr><td width="240" valign="top">
<div id="wizard">
<span><?= $user -> idg != 5 ? CHOOSE.' '.strtolower(ID_BUILDING).':' : ''; ?></span>
<?=$html['select_building']?>
<span><?=CHOOSE.' '.strtolower(YEAR)?>:</span>
<?=$year?>
<span><?=CHOOSE.' '.mb_strtolower(PERIODICITA,'UTF-8')?>:</span>
<select id="month_mode">
<option><?=CHOOSE.' '.PERIODICITA?></option>
<option value="12"><?=MENSILE?></option>
<option value="2"><?=SEMESTRALE?></option>
</select>

<div id="periodicity_2" class="dn">
<span><?=CHOOSE?> output:</span>
<? $input['mode2'] -> get(); ?>
</div>

<div id="periodicity_12" class="dn">
<span><?=CHOOSE?> output:</span>
<? $input['mode12'] -> get(); ?>
</div>

<br />
<input type="button" id="show_outputs" value="<?=NEXT?>" class="g-button g-button-yellow" />
</div>
</td>
<td id="content_map">
<div id="map_canvas"></div>
</td>
</table>
</form>

<?php
$html['map'] = ob_get_clean();
include_once HEAD_AR;
?>
<div id="tabs">
<ul>    
<li><a href="#tabs-2"><?=OUTPUT_PARAMETERS?></a></li>
<li><a href="#tabs-1"><?='Outputs'?></a></li>
</ul>
<div id="tabs-1" class="tabpage">

</div>
<div id="tabs-2" class="tabpage">
<?php
print $html['map'];
?>
</div>
</div>

<div id="dialog-confirm" class="dn" title="<?=LBL_MESSAGE?>">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
<?=COMPLETE_FIELDS?>
</p>
</div>

<script>
$(function() {
	$( "#tabs" ).tabs({selected: 0 });
});
</script>
<?php
include_once FOOTER_AR;
?>