<?php
# V.0.1.8
include_once 'init.php';
$user = new autentica($aA2);
$user -> login_standard();


require_once('/library/fpdf/fpdf.php');
require_once('/library/fpdf/fpdi.php');
		
		
?>