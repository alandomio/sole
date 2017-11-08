<?php
	include_once '../init.php';
	include_once('../library/classes/evalmath.class.php');
	$m = new EvalMath;
	$m->suppress_errors = true;
	if ($m->evaluate('y(x) = ' . $_POST['formula'])) {
		echo $m->e($_POST['function']);
	}



?>