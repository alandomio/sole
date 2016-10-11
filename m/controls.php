<?php
include_once '../init.php';
list($id, $year, $read) = request::get(array('id' => NULL, 'year' => NULL, 'read' => NULL));

if(array_key_exists('choose', $_POST)){


	if(!empty($_POST['year'])&&!empty($_POST['read'])){
		io::headto('choose-data.php', array('year' => $_POST['year'], 'read' => $_POST['read'], 'id' => $id));
	}
}


$qB = "SELECT CODE_BLD FROM buildings WHERE ID_BUILDING = '$id' LIMIT 0,1";
$rB = rs::rec2arr($qB);
$hB = io::a('index.php', array(), $rB['CODE_BLD'], array());

$html['options_year'] = '';

$y = date('Y', time());
for($i = $y; $i >= ($y - 1); $i--){
	$html['options_year'] .= '<option value="'.$i.'">'.$i.'</option>';

}

# FORM ANNO E NUMERO LETTURA
ob_start();
print $MYFILE -> system_errors;
$MYFILE -> print_msg(true);
?>
<form action="controls.php" id="fmeters" method="post" data-ajax="false">
<input type="hidden" name="id" value="<?=$id?>" />
<div data-role="fieldcontain">
	<label for="year-1" class="select"><?=ANNO?>:</label>
	<select name="year" id="year-1">
	<?=$html['options_year']?>	
	</select>
</div>    
	
<div data-role="controlgroup">
	<legend><?=ID_UPLOADTYPE?>:</legend>
	<input type="radio" name="read" id="read-1" value="1" checked="checked" />
	<label for="read-1">1</label>
	
	<input type="radio" name="read" id="read-2" value="2"  />
	<label for="read-2">2</label>
</div>

<input type="submit" name="choose" value="<?=NEXT?>" data-theme="a" />
</form>
<?php
$html['LIST_METERS'] = ob_get_clean();


include 'head.php';
?>
<div class="content-secondary"> 
<?=$html['LIST_METERS']?>
</div>
<?php
include 'footer.php';
?>