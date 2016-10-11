<?php
# V.0.1.10
$href_new = io::ahrefcss($scheda->file_c, $val=array_merge($backUri,array('crud' => 'ins')), $txt=L_NUOVO,$js='',$target="",$title=L_NUOVO, $id='', $css="puls_nuovo");
$sel_all = 'sel. tutti <input type="checkbox" class="checkbox" onchange="javascript:check_all_by_name(\'list\', \'ck\')" name="ck" value="1" />';
$puls_elimina = '<input name="del" type="submit" class="button_elimina" value="elimina" />';
if($scheda->action_type=='read') { $href_new = ''; $sel_all=''; $puls_elimina='';}
//$title_pag.="Lista ".$scheda->etichetta;
$my_vars->campi_ricerca();
?>