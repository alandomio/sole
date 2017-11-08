<?php
# V.0.1.10
if($scheda->img){ // IMMAGINE SINGOLA
//$scheda->set_upld_dirs(YT_SPACE.$rec['FOLDER'].'/');
$rec[$scheda->f_path] = is_file($scheda->img_main_web.$rec[$scheda->f_path]) ? io::ahrefcss($scheda->file_c, $val=array_merge($backUriIds,array('crud' => 'upd')), $txt='<img src="'.$scheda->img_main_web.$rec[$scheda->f_path].'" alt="image" />',$js='',$target="",$title=EDIT, $id='', $css="") : io::ahrefcss($scheda->file_c, $val=array_merge($backUriIds,array('crud' => 'upd')), $txt='<img src="'.THU_JOLLY.'" alt="no image" />',$js='',$target="",$title=EDIT, $id='', $css="");		
}
?>