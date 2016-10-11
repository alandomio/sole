<?php
include_once 'init.php';
$user = new autentica($aA4);
$user -> login_standard();

$html['select_building'] = sole::select_fhb('');

$MYFILE -> add_js('<script type="text/javascript" src="'.JS_JQUERY.'jquery.caret.1.02.js" ></script>','file', 'head');
ob_start();
?>
<div id="col_left" class="duecentocinquanta">
<?=CH_BUILDING?>
<input id="idg" type="hidden" value="<?=$user -> idg?>" />
<?=$html['select_building']?>
<div id="meters_list">
</div>
</div>
<div id="col_right">
</div><div class="clear"></div>
<div id="dialog_formula">
<div id="box_formula">
<h2>Formula:</h2>
<input class="input-formula" type="text" id="input-formula" value="" />
</div>
<h2><?=METERS?>:</h2>
<div id="meters">
</div>
</div>

<div id="dialog_ab"><input type="hidden" value="" id="status-ab" />
<div id="tabs">
<ul>
<li><a href="#tabs-1">A</a></li>
<li><a href="#tabs-2">B</a></li>
</ul>
<div id="tabs-1">
<input class="input-ab" type="text" id="input-a" value="" />
</div>
<div id="tabs-2">
<input class="input-ab" type="text" id="input-b" value="" />
</div>
</div>
<h2><?=METERS?>:</h2>
<div id="meters_ab">
</div>
</div>

<div id="dialog_meter">
<form id="update_meters" action="<?=AJAX?>json.php?action=crud_contatore" method="post">
<div id="content_meter">
</div>
<!-- <div id="content_valori_iniziali">
</div> -->
<div id="content_flats">
</div>
<div id="content_usages">
</div>
</form>
</div>

<div id="dialog-confirm" class="dn" title="<?=DELETE?>">
<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
<?=ASK_DELETE_RECORD?>
</p>
</div>

<script type="text/javascript" src="<?=JS_MAIN.'meters-ins.js'?>" />
<?php
$html['contatori'] = ob_get_clean();
include_once HEAD_AR;
print $html['contatori'];
include_once FOOTER_AR;
?>