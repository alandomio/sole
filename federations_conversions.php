<?php
include_once 'init.php';
$user = new autentica($aA2);
$user -> login_standard();
include_once stringa::get_conffile($MYFILE -> filename);

$qMtypes = "SELECT * FROM metertypes";
$rMtypes = rs::inMatrix($qMtypes);
$rMtypes = arr::semplifica($rMtypes, 'ID_METERTYPE');

$qConvs = "SELECT * FROM federations_conversions WHERE ID_FEDERATION = '$id' ORDER BY ID_METERTYPE ASC";
$rConvs = rs::inMatrix($qConvs);
$rConvs = arr::semplifica($rConvs, 'ID_METERTYPE');

# RIGHE DEL FORM
$aFlds = array();
foreach($rMtypes as $k => $type){
	// acqua non ha bisogno della conversione
	if($type['ID_METERTYPE'] == 5)
		continue;
	
	$aFlds[$type['ID_METERTYPE']] = '<span style="float:right;">1 '.$type['UNIT'].' = </span>'.$type['METERTYPE_'.LANG_DEF].':';
}

$sub_menu = '';
foreach($sub_nav as $k => $arr){
	$href_to='';
	if(in_array(FILENAME.'.php',$arr)) { $sub_menu.='<div class="menu_dis"><span>'.$k.'</span></div>'; }
	elseif($crud=='ins') { $sub_menu.='<div class="menu_att_dis">'.$k.'</div>'; }
	else {
		$href_to = io::a($arr[0], array_merge($_GET,array('crud'=>'upd')), $k, array('title' => $title=strtolower($k)));
		$sub_menu.='<div class="menu_att"><span>'.$href_to.'</span></div>';
	}
}
$sub_menu = '<div id="menu_bar">'.$sub_menu.'</div>';

$backUri = $_GET;
$backUri['id'] = NULL; unset($backUri['id']);
$backUri['crud'] = NULL; unset($backUri['crud']);


$href_lista = '';
if($user -> idg == 1){
	$href_lista = io::a($scheda->file_l, $backUri, LISTA, array('title' => LISTA, 'class' => 'g-button'));
}

include_once HEAD_AR;
print $sub_menu;
?>
<input type="hidden" id="id" value="<?=$id?>">
 <table class="list">    
	<tr class="bg">
	  <th colspan="2"><?=$href_lista?> </th>
	</tr>    
<? if($crud=='upd'){
	print'<tr class="yellow"><td colspan="6"><div class="table_cell"><strong>'.$etichetta.'</strong></div></td></tr>';
  }

foreach($aFlds as $k => $lable){
	$valore = '';
	if(!empty($rConvs[$k]['CONVERSION'])){
		$valore = $rConvs[$k]['CONVERSION'];
	}
	$input = new io();
	$input -> type = 'text'; 
	$input -> val = $valore;
	$input -> id = $k;
	$input -> css = 'conversion';
	?>
<tr>
  <td valign="top" width="160"><?=$lable?>
  </td>
  <td>
 <?=$input -> set('conversion').' kWhEP'?> 
  </td>
</tr><? }?>
<tr>                   
<th colspan="2">* <?=REQUIRED_FIELDS?>
</th>    
</tr>  
</table>
<script type="text/javascript">
 $(document).ready(function(){
	$('.conversion').keypress(function() {
		$(this).css('color' , 'red');
	});
	
	$('.conversion').change(function() {
		
		var idtype = $(this).attr('id');
		var idfederation = $('#id').val();		
		
		var valore = $(this).val()
		valore = String(valore).replace(/\,/g,'.');
		$(this).val(valore);
		
		conversion($(this), idtype, idfederation, valore );

	});
 
function conversion(campo, idtype, idfederation, valore){
		jQuery.getJSON('ajax/json.php?action=conversion&idtype=' + idtype + '&idfederation=' + idfederation + '&valore=' + valore,
		function(data){
			if(data.success){
				campo.css('color','green');
			}
		});
	}	
});
</script>
<?php
include_once FOOTER_AR;
?>