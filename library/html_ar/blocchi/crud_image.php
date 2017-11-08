<?php
# V.0.1.10
if($scheda->img){
	$path = rs::rec2arr("SELECT ".$scheda->f_path." FROM ".$scheda->table." WHERE ".$scheda->f_id." = '$id_rec'");
	print'<tr><td>'.UPD_INS_IMG.'</td><td><input name="nw_img" type="file" /></td></tr>';
	if(is_file($scheda->img_main_web.$path[$scheda->f_path])){
		$href_deleimg = io::ahrefcss($scheda->file_c, $val=array_merge($backUri,array('nw_img_dele' => $id_rec, 'crud'=>'upd')), $txt=DELE_IMG,$js='',$target="",$title=CANCEL, $id='', $css="");
		print"<tr><td>$href_deleimg</td><td>
		<a href=\"".$scheda->img_main_big.$path[$scheda->f_path]."\" target=\"_blank\"><img src=\"".$scheda->img_main_web.$path[$scheda->f_path]."\" /></a>
		</td>
		</tr>";
	}	
}
?>