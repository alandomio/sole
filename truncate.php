<?php
# V.0.1.8
include_once 'init.php';
$user = new autentica($aA1);
$user -> login_standard();
include_once HEAD_AR;

$aSql = array(
	"DELETE FROM users WHERE USER <> 'admin@sole.it'",
	"TRUNCATE consumptions",
	"TRUNCATE buildings_files",
	"TRUNCATE buildings_users",
	"TRUNCATE federations_conversions",
	"TRUNCATE files",
	"TRUNCATE flats",
	"TRUNCATE flats_meters",
	"TRUNCATE measures",
	"TRUNCATE meters",
	"TRUNCATE  msoutputs",
	"TRUNCATE  buildings",
	"TRUNCATE  hcompanys",
	"TRUNCATE  federations"
	);


foreach($aSql as $k => $q){
	print $q;
	if(mysql_query($q)) print ' Eseguita';
	else print ' Fallita';
	print BR;
}
	
include_once FOOTER_AR;
?>