<?php
# V.0.1.10
$doc_list=''; $documenti = ''; $immagini = ''; $lbl_int = ''; $img_list = '';
$rs=rs::inMatrix($q." AND files.TYPE='f'"); # LISTA FILE
foreach($rs as $rec){
$filesize = filesize($scheda->file_atc.$rec['PATH'])/1024;
$doc_list.='<li><input type="checkbox" name="ck'.$rec['ID_FILE'].'" value="1" class="checkbox" />';
$doc_list.='<input name="tx'.$rec['ID_FILE'].'" type="text" maxlenght="32" value="'.$rec['TITLE'].'" />';
$doc_list.='<a href="'.$scheda->file_atc.$rec['PATH'].'" class="puls_modifica_text" target="_blank">'.OPEN.' ('.strtolower(strrcut($rec['PATH'],'',3)).' '.num::format($filesize,0,'','').' Kb)</a></li>';
}
$doc_list = strlen($doc_list)>0 ? '<ul id="elenco_documenti">'.$doc_list.'</ul>' : '';

$rs=rs::inMatrix($q." AND files.TYPE='i'"); # LISTA IMMAGINI
foreach($rs as $rec){
	$img_list.='
	<div class="img_gallery_riga_scheda">
	<input type="checkbox" name="ck'.$rec['ID_FILE'].'" value="1" class="checkbox" />
	<a href="'.$scheda->img_alb_web.$rec['PATH'].'" alt="'.$rec['TITLE'].'" target="_blank"><img src="'.$scheda->img_alb_thu.$rec['PATH'].'" alt="'.$rec['TITLE'].'" width="60"></a>
	<span><input name="tx'.$rec['ID_FILE'].'" type="text" maxlenght="32" value="'.$rec['TITLE'].'" />
	</span></div>';
}
//$img_list = strlen($img_list)>0 ? '<h2>'.GEST_IMG.'</h2>'.$img_list : '';

$href_lista = io::ahrefcss($scheda->file_l,$val=$backUri, $txt=LISTA,$js='',$target="",$title=LISTA, $id='', $css="puls_back");
$href_modifica = io::ahrefcss($scheda->file_c,$val=array_merge($backUri,array('crud' => 'upd')), $txt=EDIT,$js='',$target="",$title=EDIT, $id='', $css="puls_back");

$colspan = (in_array('files', $scheda->files) && !in_array('images', $scheda->files)) || (!in_array('files', $scheda->files) && in_array('images', $scheda->files)) ? ' colspan="2"' : '';

if(in_array('files', $scheda->files)){
$documenti = '<td valign="top"'.$colspan.'><h2>Documenti</h2>
	<div class="img_sel_scheda">'.$doc_list.'
	</div></td>';
}

if(in_array('images', $scheda->files)){
	$immagini = '<td valign="top"'.$colspan.'><h2>Immagini</h2>
  <div class="_overflow">
	'.$img_list.'   
    <div class="clear"></div>
  </div></td>';
}

if($crud=='upd'){ 
	  $lbl_int ='<tr class="celeste">      
<td colspan="2">'.$etichetta.'</td>    
</tr>
';}
?>
<div id="left_prodotti">
<form method="post" name="list">
<input type="hidden" name="icursor" value="0">
<table>
<tr valign="bottom" class="bg" >
    <th valign="middle"><?=$href_lista?></th>
    <th valign="middle"><span class="float_right">
    <input type="submit" name="subsave" class="button_salva" value="salva" />
    <input name="subdel" type="submit" value="elimina" class="button_elimina" />
    <input type="checkbox" class="checkbox" onchange="javascript:check_all_by_name('list', 'ck')" id="sel_tutti" name="ck" value="1" />
    <span class="a_sel_tutti"><?=SEL_ALL?></span></span></th>
</tr>
<?=$lbl_int?>
<tr class="colour">
<?=$documenti?>
<?=$immagini?>
</tr>
</table>
<?php
if($scheda->swf_limit<=0){
	print '<p>Hai raggiunto il limite massimo di file inseribili</p>';
}elseif(isset($noSwf) && $noSwf==1){ print '';
}else{ ?>
</form>
<table>
<tr class="tab_noover">
<td valign="top"><h2 style=" margin:0 0 0 10px;">Aggiungi allegati alla scheda</h2>
<?php
include JS_SWF.'form.php';
?>
</td><td valign="top">&nbsp;</td>
</tr>
</table></form>
<?php } ?>
</div>