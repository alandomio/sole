<?php
include_once 'init.php';

# AUTENTINCAZIONE PER TUTTE LE PAGINE PUBBLICHE
$user = new autentica($aA5);
$user -> login_no_redirect(false);
// registro la richiesta di accesso al sistema da parte dell'utente
$user -> add_log();

if(!empty($user -> aUser['ID_USER'])){
	$messaggio_benvenuto = PERR_WELLCOME.' '.NOME_SITO;
/* 	if(!empty($user -> aUser['NAME'])){
		$messaggio_benvenuto = $user -> aUser['NAME'].', '.PERR_WELLCOME.' '.NOME_SITO;
	} */
	io::headto('home.php', array('ack' => $messaggio_benvenuto, 'first_login' => 'true'));
}

include_once HEAD_AR;
include AJAX.'login_form.php';
include_once FOOTER_AR;
?>