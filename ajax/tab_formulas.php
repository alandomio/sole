<?php
include_once '../init.php';

$val_formulas = convalida::mk_validation_formulas($_REQUEST['id'], $_REQUEST['upload'],$_REQUEST['year']);

echo $val_formulas['html'];
echo '<div class="fix"></div>';
?>