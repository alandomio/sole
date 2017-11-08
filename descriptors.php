<?php
include_once 'init.php';
$user = new autentica($aA1);
$user -> login_standard();

$html['h1_int'] = 'Gestione descrittori';
$scheda = new configura('descriptors');
$MYFILE -> add_msg($scheda -> ack, 'ack'); 
$MYFILE -> add_msg($scheda -> err, 'err');

include_once HEAD_AR;
$scheda -> get();
include_once FOOTER_AR;
?>