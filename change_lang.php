<?php
include_once 'init.php';
// cambio la variabile della lingua
$_SESSION['lang'] =  array_key_exists('lang',$_GET) ? $_GET['lang'] : 'it';
// faccio redirect sulla precedente pagina visualizzata
io::headto(array_key_exists('return',$_GET) ? $_GET['return'] : '' , array() );
?>