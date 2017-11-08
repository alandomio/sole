<?php
include_once 'init.php';
$user = new autentica($aA5);
$user -> login_standard();

$html['select_building'] = sole::select_fhb('');

$input = new io();
$input -> type = 'select'; 
$input -> addblank = true; 
$input -> aval = rs::id2arr("SELECT ID_UPLOADTYPE, UPLOADTYPE FROM uploadtypes ORDER BY UPLOADTYPE ASC"); 
$input -> css = 'duecento'; 
$input -> id = 'upload_type'; 
$input -> txtblank = S_CHOOSE.' '.strtolower(UPLOADTYPE); 
$upload_type = $input -> set('upload_type');

$year = sole::select_year('year');

ob_start();
?>
<div id="col_left" class="duecentocinquanta">
<p><?=CH_BUILDING?>:</p>
<?=$html['select_building']?>
<p><?=DATE?>:</p>
<input id="choose_date" type="text" maxlength="10" name="d_measure" class="datepicker duecento" value="<?=date('d/m/Y')?>" />
<p><?=UPLOADTYPE?>:</p>
<?=$upload_type?>
<p><?=ANNO?>:</p>
<?=$year?>
<br />
<br />
<input type="button" class="g-button g-button-yellow" value="<?=STAMPA_MISURAZIONI?>" id="print-measures" />
<div id="info_meter">
</div>
</div>
<div id="col_right">
</div><div class="clear"></div>
<script type="text/javascript" src="<?=JS_MAIN.'measures-ins.js'?>" /></script>
<?php
$html['measures'] = ob_get_clean();
include_once HEAD_AR;
print $html['measures'];
include_once FOOTER_AR;
?>