<?php
include_once '../init.php';
list($id, $year, $read) = request::get(array('id' => NULL, 'year' => NULL, 'read' => NULL));

if(array_key_exists('choose', $_POST)){
	$date = $_POST['date'];
	if(!empty($date)){
		$aD = explode('/', $date);
		$dd = $aD[0];
		$mm = $aD[1];
		$yy = $aD[2];
		io::headto('meters.php', array_merge($_GET, array('dd' => $dd, 'mm' => $mm, 'yy' => $yy)));
	}
}

$qB = "SELECT BUILDING FROM buildings WHERE ID_BUILDING = '$id' LIMIT 0,1";
$rB = rs::rec2arr($qB);
$hB = io::a('index.php', array(), $rB['BUILDING'], array());

if(!empty($year) && !empty($read)){ # SCELTA DELLA DATA
	ob_start();
	print $MYFILE -> system_errors;
	$MYFILE -> print_msg(true);
	?>
	<form id="fmeters" method="post" data-ajax="false">
    <label for="date">Date Input:</label>
    <input type="date" name="date" id="date" value="" data-theme="a" />
	<input type="submit" name="choose" value="<?=NEXT?>" data-theme="a" />
	</form>
	<?php
	$html['LIST_METERS'] = ob_get_clean();
}

include 'head.php';
?>
<div class="content-secondary"> 
<?=$html['LIST_METERS']?>
</div>
<?php
include 'footer.php';
?>