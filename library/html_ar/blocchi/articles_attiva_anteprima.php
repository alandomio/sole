<?php
$html['licontatti'] = '';
foreach($aContatti as $k => $fld){
	$tmp_contact = io::lable(array($fld => $rec[$fld]), $fld, true, $cut = 0);
	
	if(!empty($tmp_contact)) $html['licontatti'] .= '<li style="background:none; margin:0; padding:0;">'.$tmp_contact.'</li>'."\n";
}
if(!empty($html['licontatti'])) $html['licontatti'] = '<ul style="margin:20px 0 0 0; padding:0; list-style:none;">'.$html['licontatti'].'</ul>';

$qUsr = "SELECT * FROM users WHERE ID_USER = '".$rec['ID_USER']."'";
$rUsr = rs::rec2arr($qUsr);

$path_img = FLD_MAIN.$rUsr['FOLDER_USR'].'/m_web/'.$rUsr['PATH_USER'];
	//print $path_img;

$html['img_usr'] = '';
if(!empty($rUsr['PATH_USER'])){
	if(is_file($path_img)){
		$html['img_usr'] = "<img src=\"".$path_img."\" style=\"padding:1px; border:solid 1px #ccc; margin-right:4px; \" />";
	}	
}

$html['scheda_contatto'] = '<h2 class="'.$aCssRubriche[$categoria].'">Contatti</h2>
<div style=" background-color:#fff; margin:0 0 0 0; padding:10px 20px 10px 20px; text-align:justify;">
'.$html['img_usr'].'
'.$html['licontatti'].'
</div>';

$qFiles="SELECT
files.ID_FILE,
files.TYPE,
files.TITLE,
files.DESCRIP,
files.PATH
FROM
".$scheda->file_table."
Left Join files ON ".$scheda->file_table.".ID_FILE = files.ID_FILE
WHERE
".$scheda->file_table.".".$scheda->f_id." =  '".$rec['ID_ARTICLE']."'";

$doc_list='';
$img_list = '';

$rFiles=rs::inMatrix($qFiles." AND files.TYPE='i'"); # LISTA IMMAGINI

$html['js_imgs'] = ''; $html['html_gallery'] = ''; $html['descrizione'] = ''; $cnt_gal = 0;

$html['thumbs'] = '';
foreach($rFiles as $k => $v){
	$path = $scheda->img_alb_thu.$v['PATH'];
	if(is_file($path)){
	$im = new myimage($path);
		if($v['TYPE'] == 'i'){
			$html['thumbs'] .= '<img src="'.$scheda->img_alb_thu.$v['PATH'].'" alt="'.$v['TITLE'].'" '.$im -> attr.' />';
		}
	}
}
if(!empty($html['thumbs'])) $html['thumbs'] = '<div class="slideshow">'.$html['thumbs'].'</div><div class="fix"></div>';

$href['successivo'] = io::ahrefcss('annuncio_step_5.php',array_merge($backUri, array('crud'=>'upd')),'Conferma la pubblicazione', ON_CLICK_PUBBLICA, $target="",'Conferma la pubblicazione',$id="",$class="input_puls_generico");

if(!empty($db -> ID_STEP -> val) && $db -> ID_STEP -> val > 4){ $db -> ID_STEP -> val; /* NON FACCIO NULLA */  }
else $db -> ID_STEP -> val = 4;

$aStrip = array('FOLDER_ARTICLE','ID_ARTICLE', 'ID_USER', 'ID_STEP', 'ID_DESCRIPTORS_CATEG', 'VARS_ARTICLE');
foreach($aFlds as $k => $v){
	if(strpos($v, 'K3_ID') !== false){
		$aStrip[] = $v;
	}
}


$aEtichette = io::mk_etichette2($aFlds, array_merge($aContatti, $aDatitecnici, $aStrip, $aNascondi), $rec); # SOLO LE ETICHETTE DA VISUALIZZARE
$html['riepilogo'] = '';
foreach($aIntestazioni[$categoria] as $k => $v){
	$tmp = io::lable($rec, $v, false, $cut = 30);
	if(!empty($tmp)) $html['riepilogo'] .= $tmp.' - ';
}

$html['riepilogo'] = stringa::togli_ultimi($html['riepilogo'], 3);
$html['datitecnici'] = '';
foreach($aDatitecnici as $t => $f){
	$tmp2 = io::lable($rec, $f, true, $cut = 0);
	if(!empty($tmp2)) $html['datitecnici'] .= $tmp2.' - ';
}
$html['datitecnici'] = stringa::togli_ultimi($html['datitecnici'], 3);
?>
<table id="vis_annuncio">
<tr>
<td colspan="2" valign="top" class="datitecnici"><?=$html['datitecnici']?></td>
</tr>
<tr>
<th colspan="2" valign="top"><?=$html['riepilogo']?></th>
</tr>
<tr>
<td valign="top" style="padding:1px 7px 10px 4px; width:350px;">
<?=$html['thumbs']?>
<?=$html['scheda_contatto']?>
</td>
<td valign="top"><h2 class="<?=$aCssRubriche[$categoria]?>">Descrizione</h2>
<table>
<tr>
<td valign="top">
<?=$aEtichette['descriptor']?>
</td>
<td rowspan="2" valign="top" class="riga">
<?=$aEtichette['sino']?>
</td>
</tr>
<tr>
<td colspan="3" valign="top"><?=$aEtichette['textarea']?></td>
</tr>
</table>
</td>
</tr>
<tr>
<td valign="top">
</td>
<td valign="top"><?//$href['successivo']?></td>
</tr>
</table>