<?php
include_once 'init.php';
$user = new autentica($aA5);
$user -> login_standard();

$initialize = $body_ini;
//$initialize = '';

$html['flat-info'] = '';


$qFlat = "SELECT
flats.ID_FLAT,
flats.ID_BUILDING,
flats.CODE_FLAT,
flats.NETAREA,
buildings.ID_HCOMPANY,
buildings.CODE_BLD,
buildings.NAME_BLD,
buildings.YEAR_BLD,
buildings.DESCRIP_BLD_".LANG_DEF." AS DESCRIP_BLD,
buildings.ADDRESS_BLD,
hcompanys.NAME_HC,
hcompanys.ADDRESS_HC,
hcompanys.REFERENCE_HC,
hcompanys.DESCRIP_HC_".LANG_DEF." AS DESCRIP_HC,
hcompanys.PATH_HCOMPANY,
federations.PATH_FEDERATION,
federations.FEDERATION,
federations.ADDRESS_FED,
fstatis.DESCRIPTOR_".LANG_DEF." AS K0_ID_STATI,
fregionis.DESCRIPTOR_".LANG_DEF." AS K0_ID_REGIONI,
fprovinces.DESCRIPTOR_".LANG_DEF." AS K0_ID_PROVINCE,
fcomunis.DESCRIPTOR_".LANG_DEF." AS K0_ID_COMUNI,
hstatis.DESCRIPTOR_".LANG_DEF." AS K1_ID_STATI,
hregionis.DESCRIPTOR_".LANG_DEF." AS K1_ID_REGIONI,
hprovinces.DESCRIPTOR_".LANG_DEF." AS K1_ID_PROVINCE,
hcomunis.DESCRIPTOR_".LANG_DEF." AS K1_ID_COMUNI
FROM flats
LEFT JOIN buildings USING(ID_BUILDING)
LEFT JOIN descriptors AS hstatis ON buildings.K1_ID_STATI = hstatis.ID_DESCRIPTOR
LEFT JOIN descriptors AS hregionis ON buildings.K1_ID_REGIONI = hregionis.ID_DESCRIPTOR
LEFT JOIN descriptors AS hprovinces ON buildings.K1_ID_PROVINCE = hprovinces.ID_DESCRIPTOR
LEFT JOIN descriptors AS hcomunis ON buildings.K1_ID_COMUNI = hcomunis.ID_DESCRIPTOR
LEFT JOIN hcompanys USING(ID_HCOMPANY)
LEFT JOIN federations USING(ID_FEDERATION)
LEFT JOIN descriptors AS fstatis ON federations.K0_ID_STATI = fstatis.ID_DESCRIPTOR
LEFT JOIN descriptors AS fregionis ON federations.K0_ID_REGIONI = fregionis.ID_DESCRIPTOR
LEFT JOIN descriptors AS fprovinces ON federations.K0_ID_PROVINCE = fprovinces.ID_DESCRIPTOR
LEFT JOIN descriptors AS fcomunis ON federations.K0_ID_COMUNI = fcomunis.ID_DESCRIPTOR
WHERE
flats.ID_USER = '".$user -> aUser['ID_USER']."'
LIMIT 1
";
$r = rs::rec2arr($qFlat);


/*
    [ID_FLAT] => 1
    [ID_BUILDING] => 1
    [CODE_FLAT] => 001
    [NETAREA] => 53.00
    [ID_HCOMPANY] => 1
    [CODE_BLD] => HC1.001.Step 2
    [NAME_BLD] => Step 2
    [YEAR_BLD] => 2006
    [DESCRIP_BLD] => 
    [ADDRESS_BLD] => Via Garibaldi, 1
    [NAME_HC] => 
    [ADDRESS_HC] => 
    [REFERENCE_HC] => 
    [DESCRIP_HC] => 
    [PATH_HCOMPANY] => 
    [PATH_FEDERATION] => 
    [FEDERATION] => FD1
    [ADDRESS_FED] => Corso Italia, 11 
    [K0_ID_STATI] => Italia
    [K0_ID_REGIONI] => 
    [K0_ID_PROVINCE] => 
    [K0_ID_COMUNI] => 
    [K1_ID_STATI] => Italia
    [K1_ID_REGIONI] => Lombardia
    [K1_ID_PROVINCE] => Brescia
    [K1_ID_COMUNI] => Brescia
*/

/*
BUILDING
    [ID_BUILDING] => 1
    [CODE_BLD] => HC1.001.Step 2
    [NAME_BLD] => Step 2
    [YEAR_BLD] => 2006
    [DESCRIP_BLD] =>     
	
	[ADDRESS_BLD] => Via Garibaldi, 1
    [K1_ID_STATI] => Italia
    [K1_ID_REGIONI] => Lombardia
    [K1_ID_PROVINCE] => Brescia
    [K1_ID_COMUNI] => Brescia

*/

$qImgBuild = "
SELECT
buildings_files.*,
files.*
FROM files
LEFT JOIN buildings_files USING(ID_FILE)
WHERE
buildings_files.ID_BUILDING = '{$r['ID_BUILDING']}' AND
files.TYPE = 'i'
ORDER BY
buildings_files.IS_PRINCIPALE DESC,
buildings_files.RANK ASC
";

$rImgBuild = rs::inMatrix($qImgBuild);
$imgBuilding = '';
foreach($rImgBuild as $k => $img){
	$web = IMG_ALB_WEB.$img['PATH'];
	$path = IMG_ALB_SQR.$img['PATH'];
	if(is_file($path)){
		
		$imgBuilding .= '
		<a href="'.$web.'" title="'.$img['title'].'">
		<img src="'.$path.'" title="'.$img['title'].'">
		</a>
		';
		
	}
}

$slide = '';
if(!empty($imgBuilding)){

$slide = '
	<h4>'.IMAGES_BUILDING.'</h4>
	<div id="galleria">
	</div>
	<div id="source" style="display:none;">
	'.$imgBuilding.'	
	</div>

	<script>
		Galleria.loadTheme(\'js/galleria/themes/classic/galleria.classic.min.js\');
		$("#galleria").galleria({
			dataSource: "#source",
			keepSource: true,
			lightbox: false,
			image_pan: true,
			imagePanSmoothness: 1,
			width: 600,
			height: 680
		});
	</script>';

}



$html['building'] = '
<div style="float:left;">
'.$slide.'
</div>
<div id="building_info" style="float:left; margin:0 20px;">
<h2>'.$r['CODE_BLD'].' '.$r['YEAR_BLD'].'</h2>
'.$r['DESCRIP_BLD'].'
'.$r['ADDRESS_BLD'].' '.$r['K1_ID_COMUNI'].'('.$r['K1_ID_PROVINCE'].')
</div>

';


/*
FEDERAZIONE
Logo
Nome
Indirizzo
*/

$logo = '';
if(!empty($r['PATH_FEDERATION'])){
	$logo = '<img src="'.IMG_MAIN_WEB.$r['PATH_FEDERATION'].'" />';
}
$a = array('FEDERATION', 'ADDRESS_FED', 'K0_ID_STATI', 'K0_ID_REGIONI', 'K0_ID_PROVINCE', 'K0_ID_COMUNI' );

$html['federation'] = $logo.'<ul id="federation-data">';
foreach($a as $k => $f){
	if(!empty($r[$f])){
		$html['federation'] .= '<li><strong>'.constant($f).':</strong> '.$r[$f].'</li>'."\n";
	}
}
$html['federation'] .= '</ul>';

$logo = '';
if(!empty($r['PATH_HCOMPANY'])){
	$logo = '<img src="'.IMG_MAIN_WEB.$r['PATH_HCOMPANY'].'" />';
}

$a = array('NAME_HC', 'ADDRESS_HC', 'REFERENCE_HC', 'DESCRIP_HC' );
$html['hcompany'] = '<ul>';
foreach($a as $k => $f){
	if(!empty($r[$f])){
		$html['hcompany'] .= '<li><strong>'.constant($f).':</strong> '.$r[$f].'</li>'."\n";
	}
}
$html['hcompany'] .= '</ul>'.$logo;


$html['flat'] = '';
$a = array('CODE_FLAT', 'NETAREA');
$html['flat'] = '<ul id="flats-data">';
foreach($a as $k => $f){
	if(!empty($r[$f])){
		$html['flat'] .= '<li><strong>'.constant($f).':</strong> '.$r[$f].'</li>'."\n";
	}
}
$html['flat'] .= '</ul>';

$MYFILE -> add_js('<script type="text/javascript" src="js/galleria/galleria-1.2.6.min.js"></script>', 'file', 'head');

include_once HEAD_AR;
?>
<div id="col-float-right">
<h2><?=DATA_FLAT?></h2>
<?=$html['flat']?>
</div>


<div id="col-left">

<div id="left-login">
<h2><?=DATA_FEDERATION?></h2>
<?=$html['federation']?>

<div id="hcompany-data">
<h2><?=DATA_HCOMPANY?></h2>
<?=$html['hcompany']?>
</div>


</div>

</div>
<div id="col-main">


<?=$html['building']?>
</div>



<div class="clear"></div>
<?php
include_once FOOTER_AR;
?>