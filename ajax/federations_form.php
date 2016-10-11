<?php
$db -> ADDRESS_FED -> css = 'd22';

$db -> ADDRESS_FED -> id = 'indirizzo';
$db -> K0_ID_STATI -> id = 'stato';
$db -> K0_ID_REGIONI -> id = 'regione';
$db -> K0_ID_PROVINCE -> id = 'provincia';
$db -> K0_ID_COMUNI -> id = 'comune';

$val = $backUri; $send_nuovo = arr::_unset($backUri, array('id', 'jsstatis'));
unset($val['jsstatis']);
# STAMPA CONTROLLI HTML
?>
<div id="ajax_wizard">
<?=request::hidden($backUri)?>
<strong><?=LOCALIZATION?></strong><br />
<? $db -> K0_ID_STATI -> get(); ?><br />
<? $db -> K0_ID_REGIONI -> get(); ?><br />
<? $db -> K0_ID_PROVINCE -> get(); ?><br />
<? $db -> K0_ID_COMUNI -> get(); ?><br />
<strong><?=ADDRESS_FED?></strong><br />
</div>