<?php
!class_exists("myfile") ? require_once  (CLASSES_PATH."myfile.php") : NULL;

class myerr extends myfile{
function __construct(){
	$this -> err = array();			# MESSAGGI DI ERRORE
	$this -> ack = array();			# MESSAGGI DI AVVISO
	
	if(!empty($_REQUEST['err'])){ $this -> err[] = $_REQUEST['err']; }
	if(!empty($_REQUEST['ack'])){ $this -> ack[] = $_REQUEST['ack']; }
}

}
?>