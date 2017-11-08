<?php

//include ('config.inc.php');
session_start();

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
header("Cache-Control: no-cache, must-revalidate" );
header("Pragma: no-cache" );
//header("Content-type: text/x-json");
 
//var_dump($_REQUEST);

if ($_REQUEST['action'] == 'record')	{
	$data = serialize($_REQUEST);
	$db->do_query("INSERT INTO posizione (latitudine, longitudine, quota, testo, sim_id) VALUES (".$_REQUEST['latitudine'].", ".$_REQUEST['longitudine'].", ".$_REQUEST['altitudine'].", '".$_REQUEST['latitudine']."', '".$_REQUEST['sim_id']."')");
}

if ($_REQUEST['action'] == 'track')	{
	$data = $_REQUEST['data'];
	//$punti = $db->do_query("SELECT lat AS latitudine, `long` AS longitudine  FROM contatori");
	//$punti = $db->do_query("SELECT * FROM posizione WHERE ora > SUBTIME(NOW(),'2000:00:00')");
	$punti = $db->do_query("SELECT * FROM posizione WHERE operatore=".$_REQUEST['operatore']." AND DATE(ora) = STR_TO_DATE('$data','%d/%m/%Y')");
	$risposta = json_encode($punti);
	
	echo $risposta;
}


?>