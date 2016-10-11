<?php
include_once '../init.php';

$val = convalida::mk_validation($_REQUEST['id'],$_REQUEST['upload'],$_REQUEST['year']);
$periodo = convalida::lunghezza_periodo($_REQUEST['id'],$_REQUEST['upload']);
$val_shared = convalida::mk_validation_shared($_REQUEST['id'],$_REQUEST['upload'],$_REQUEST['year']);
//$val_shared = convalida::mk_validation_shared($_REQUEST['id'],$_REQUEST['upload'],$_REQUEST['year']);

echo '<span style="display:none;" id="periodlength">'.$periodo.'</span>';
echo $val['html'];
echo '<div class="fix"></div>';
echo $val_shared['html'];
echo '<div class="fix"></div>';
?>