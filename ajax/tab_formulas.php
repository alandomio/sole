<?php
include_once '../init.php';
/* function get_validation_flat($rec){
	
} */

$val_formulas = convalida::mk_validation_formulas($_REQUEST['id'], $_REQUEST['upload'],$_REQUEST['year']);
//$val_shared = convalida::mk_validation_shared($_REQUEST['id']);

echo $val_formulas['html'];
echo '<div class="fix"></div>';
?>