<?php
//ob_start();

session_start();


include_once '../init.php';


include_once '../library/classes/rs.php';
include_once '../library/classes/err.php';
include_once '../library/personal/sole.php';
include_once '../library/personal/flat.php';
include_once '../library/personal/outputs.php';


$Excel = new excel();



if($_REQUEST['action'] == 'modello')	{

		$Excel->modello();
		$Excel->write( $Excel -> filename );

}	else if($_REQUEST['action'] == 'carica')	{
		$Excel->importa();
}

	






?>