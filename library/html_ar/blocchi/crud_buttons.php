<?php
# V.0.1.10
$href_nuovo = $crud=='upd' ? io::ahrefcss($scheda->file_c,$val=array_merge($backUri,array('crud'=>'ins','id_rec'=>'')), $txt='nuovo',$js='',$target="",$title='nuovo', $id='', $css="puls_nuovo") : '';

$href_annulla = io::ahrefcss($scheda->file_l,$val=$backUri, $txt=LISTA,$js='',$target="",$title=LISTA, $id='', $css="puls_back");
if($crud=='upd'){
$href_add_file = $scheda->files ? io::ahrefcss($scheda->file_f,$val=$backUri, $txt=ADD_FILE,$js='',$target="",$title=ADD_FILE, $id='', $css="puls_aggiungi") : '';
} else {$href_add_file = ''; }

$sub_menu = '';
foreach($sub_nav as $k => $arr){
	$href_to='';
	if(in_array(FILENAME.'.php',$arr)) { $sub_menu.='<div class="menu_dis"><span>'.$k.'</span></div>'; }
	elseif($crud=='ins') { $sub_menu.='<div class="menu_att_dis">'.$k.'</div>'; }
	else {
		$href_to = io::ahrefcss($arr[0],$val=array_merge($backUri,array('crud'=>'upd')), $txt=$k,$js='',$target="",$title=strtolower($k), $id='', $css="");
		$sub_menu.='<div class="menu_att"><span>'.$href_to.'</span></div>';
	}
}
$sub_menu = '<div id="menu_bar">'.$sub_menu.'</div>';
?>