<?php
# V.0.1.10
$celle='';
foreach($sublable as $k=>$v){
	if(substr($k,0,2)=='D_') $rec[$k] = dtime::my2iso($rec[$k]); // DATA
	if(substr($k,0,3)=='DT_') $rec[$k] = dtime::my2isodt($rec[$k]); // DATA
	elseif(substr($k,0,3)=='IS_') $rec[$k] = !empty($rec[$k]) ? '<div class="icon_check_att"></div>' : '<div class="icon_check_dis"></div>'; // SI NO
	elseif(substr($k,0,3)=='ID_' || $k == $scheda->f_path) $rec[$k]; // IMMAGINE
	else {
		$rec['FULL_'.$k] = $rec[$k];
		$rec[$k] = strcut($rec[$k],'...',20);
	} 
	$celle.='<td>'.$rec[$k].'</td>';
}
$href_mod=io::ahrefcss($scheda->file_c, $val=array_merge($backUriIds, array('crud' => 'upd')),$txt=S_EDIT,$js='',$target="",$title=EDIT, $id='', $css="puls_modifica_text");
$ck_multiplo = $scheda->action_type=='read_write' ? '<input type="checkbox" name="ck'.$rec[$scheda->f_id].'" value="1" class="checkbox" />' : '';
if(array_key_exists('title',$aInfo)){
	$info = strlen($rec['FULL_'.$aInfo['description']])>0 ? '<a href="'.$rec['FULL_'.$aInfo['description']].'" onclick="return false;" class="Tips1" title="'.$rec['FULL_'.$aInfo['title']].'"><img src="'.IMG_AR.'icon_info.gif" /></a>' : '';
} else { $info = ''; }
$lista.='<tr'.$color.' valign="top">'.$celle.'
<td class="comandi_lista">'.$info.$href_mod.$ck_multiplo.'
</td></tr>'."\n";
$cnt++;
?>