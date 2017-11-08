<?php
# V.0.1.10
$href_allegati = '';
$backUriIds["id_rec"] = $rec[$scheda->f_id];
$color = $cnt%2==0 ? '' : ' class="contrast"';
if($scheda->files==true){ // GESTIONE MULTIPLA FILE
	$totali = $scheda->cnt_files($rec[$scheda->f_id]);
	if(in_array('files',$scheda->files)){
		$href_allegati.= io::ahrefcss($scheda->file_f, $val=array_merge($backUriIds,array('crud'=>'upd')), $txt=$totali['f'].' file',$js='',$target="",$title="immagini", $id='', $css="puls_allegati_text");
	}
	if(in_array('images',$scheda->files)){
		$href_allegati.= io::ahrefcss($scheda->file_f, $val=array_merge($backUriIds,array('crud'=>'upd')), $txt=$totali['i'].' img',$js='',$target="",$title="files", $id='', $css="puls_allegati_text");
	}
	$rec['ID_FILE'] = $href_allegati;
}
?>