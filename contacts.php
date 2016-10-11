<?php
include_once 'init.php';
$user = new autentica($aA1);
$user -> login_standard();

$MYFILE -> add_err('Under costruction');

include_once HEAD_AR;
?>

<?php
include_once FOOTER_AR;
?>