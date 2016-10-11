<?php
# V.0.1.8
class myhtml{
function __construct(){
}

function mk_accordion($aId, $num, $file){ #$t, $id_livello
	$ret = ''; $cnt = 0; 
	$nlvl = $num+1;
	foreach($aId as $k => $id){
		$q = "SELECT * FROM descriptors WHERE ID_DESCRIPTOR = '$id'";
		$r = rs::rec2arr($q);
		$ret .= '<dt class="accordion_toggler_'.$num.'"><span>'.$r['DESCRIPTOR'].'</span></dt>
<dd class="accordion_content_'.$num.'">';
		$qSelf = "SELECT * FROM descriptors WHERE ID_DESCRIPTOR_SELF = '".$r['ID_DESCRIPTOR']."'";
		$rSelf = rs::inMatrix($qSelf);
		$lvl = '';
		foreach($rSelf as $kk => $r){
			$lvl .= '<li>'.io::href($file, array('crud' => 'ins', 'ct' => $r['ID_DESCRIPTORS_CATEG'], 'il' => $r['ID_DESCRIPTOR']), $r['DESCRIPTOR'], '').'</li>';
			$cnt++;
		}
		if(!empty($lvl)){
			$lvl = '<ul>'.$lvl.'</ul>';
			$ret .= $lvl.'</dd>';
		}
	}
	if(!empty($lvl)) $lvl = '<dl>'.$lvl.'</dl>';
	if(!empty($ret)){
		$ret = '<dl class="accordion">'.$ret.'</dl>';
	}
	return $ret;
}

function lista_semplice($aId, $file){
	$ret = '';
	foreach($aId as $k => $id){
		$ret .= '<li>'.self::mk_link($id, $file).'</li>';
	}
	if(!empty($ret)) $ret = '<ul>'.$ret.'</ul>';
	return $ret;
}

function mk_link($id, $file){
	$lnk = '';
	$q = "SELECT * FROM descriptors WHERE ID_DESCRIPTOR = '$id'";
	$r = rs::rec2arr($q);
	return $lnk = io::href($file, array('crud' => 'ins', 'ct' => $r['ID_DESCRIPTORS_CATEG'], 'il' => $r['ID_DESCRIPTOR']), $r['DESCRIPTOR_IT'], '');
}

function conta_articoli($ct, $id_dpt){
	$aSF = array('2' => 'K1_ID_CASA1', '3' => 'K0_ID_VE1', '4' => 'K0_ID_LV1', '5' => 'K0_ID_CAT2', '6' => 'K0_ID_NA1');
	$q = "SELECT COUNT(*) AS TOTALE FROM articles WHERE ID_DESCRIPTORS_CATEG = '$ct' AND ID_STEP = '7' AND ".$aSF[$ct]." = '$id_dpt'";
	$r = rs::rec2arr($q);
	return $r['TOTALE'];
}

function mk_accordion_src($aId, $num, $file){ #$t, $id_livello
	$ret = ''; $cnt = 0; 
	$nlvl = $num+1;
	foreach($aId as $k => $id){
		# ID_DESCRIPTORS_CATEG
		$q = "SELECT * FROM descriptors WHERE ID_DESCRIPTOR = '$id'";
		$r = rs::rec2arr($q);
		
		$tmp_ret = '<dt class="accordion_toggler_'.$num.'"><span>'.$r['DESCRIPTOR'].' (<SOST>)</span></dt>
<dd class="accordion_content_'.$num.'">';
		$qSelf = "SELECT * FROM descriptors WHERE ID_DESCRIPTOR_SELF = '".$r['ID_DESCRIPTOR']."'";
		$rSelf = rs::inMatrix($qSelf);
		$lvl = '';
		$cnt_tot_subcat = 0;
		foreach($rSelf as $kk => $r){
	//		$qCnt = "SELECT * FROM articles WHERE ID_DESCRIPTORS_CATEG = '".$r['ID_DESCRIPTORS_CATEG']."'"
			$num_art = self::conta_articoli($r['ID_DESCRIPTORS_CATEG'],$r['ID_DESCRIPTOR']);
			$cnt_tot_subcat += $num_art;
			if($num_art == 0) continue;
			$lvl .= '<li>'.io::href($file, array('ct' => $r['ID_DESCRIPTORS_CATEG'], 'il' => $r['ID_DESCRIPTOR']), $r['DESCRIPTOR'].' ('.$num_art.')', '').'</li>';
			$cnt++;
		}
		if($cnt_tot_subcat > 0){ # AGGIUNGO IL TOTALE ARTICOLI PER RUBRICA 
			$tmp_ret = str_replace('<SOST>', $cnt_tot_subcat, $tmp_ret);
			$ret .= $tmp_ret; 
		}
		if(!empty($lvl)){
			$lvl = '<ul>'.$lvl.'</ul>';
			$ret .= $lvl.'</dd>';
		}
	}
	if(!empty($lvl)) $lvl = '<dl>'.$lvl.'</dl>';
	if(!empty($ret)){
		$ret = '<dl class="accordion">'.$ret.'</dl>';
	}
	return $ret;
}

function lista_semplice_src($aId, $file){
	$ret = '';
	foreach($aId as $k => $id){
		$lnk = self::mk_link_src($id, $file);
		if(!empty($lnk)) $ret .= '<li>'.$lnk.'</li>';
	}
	if(!empty($ret)) $ret = '<ul>'.$ret.'</ul>';
	return $ret;
}

function mk_link_src($id, $file){
	$lnk = '';
	$q = "SELECT * FROM descriptors WHERE ID_DESCRIPTOR = '$id'";
	$r = rs::rec2arr($q);
	$num_art = self::conta_articoli($r['ID_DESCRIPTORS_CATEG'],$r['ID_DESCRIPTOR']);
	if($num_art > 0)$lnk = io::href($file, array('ct' => $r['ID_DESCRIPTORS_CATEG'], 'il' => $r['ID_DESCRIPTOR']), $r['DESCRIPTOR'].' ('.$num_art.')', '');
	return $lnk;
}

function get_logo($id_user){
	$ret = array('img' => '');
	$q = "SELECT
users.FOLDER_USR,
users.RAGIONESOCIALE_USR,
users.WEBSITE_USR,
users_files.IS_LOGO,
files.ID_FILE,
files.PATH,
files.TYPE
FROM
users
Left Join users_files ON users.ID_USER = users_files.ID_USER
Left Join files ON users_files.ID_FILE = files.ID_FILE
WHERE users_files.IS_LOGO = '1' AND files.TYPE = 'i' AND users.ID_USER = '$id_user'
LIMIT 0,1";
	$r = rs::rec2arr($q);
	
	if(!empty($r['PATH']) && is_file($src_path = FLD_MAIN.$r['FOLDER_USR'].'/web/'.$r['PATH'])){
		$ext = '.'.stringa::get_extension($r['PATH']);
		$nlogo = 'logo-'.stringa::filename($r['RAGIONESOCIALE_USR']).'-'.$r['ID_FILE'].$ext;
	//	print $nlogo;
		if(!is_file(FLD_LOGHI.$nlogo)){
			resizeImgBox($src_path, FLD_LOGHI.$nlogo, 85, 200, 80);
		}
	//	$f_path = FLD_MAIN.$r['PATH_USER'].'/'.$r['PATH'];
		$logo = new myimage(FLD_LOGHI.$nlogo);
		$logo -> set_alt($r['RAGIONESOCIALE_USR']);
		$logo -> set_css('logo_img');
		$ret['img'] = $logo -> html;
	//	$aLogo['href'] = io::href($path="",$val=array(),$text="",$js, $target="",$title="",$id="",$class="")
	}
	return $ret;
}


function get_banner($id_user){
	$ret = array('img' => '');
	$q = "SELECT
users.FOLDER_USR,
users.RAGIONESOCIALE_USR,
users.WEBSITE_USR,
users_files.IS_BANNER,
files.ID_FILE,
files.PATH,
files.TYPE
FROM
users
Left Join users_files ON users.ID_USER = users_files.ID_USER
Left Join files ON users_files.ID_FILE = files.ID_FILE
WHERE users_files.IS_BANNER = '1' AND files.TYPE = 'i' AND users.ID_USER = '$id_user'
LIMIT 0,1";
	$r = rs::rec2arr($q);
	
	if(!empty($r['PATH']) && is_file($src_path = FLD_MAIN.$r['FOLDER_USR'].'/web/'.$r['PATH'])){
		$ext = '.'.stringa::get_extension($r['PATH']);
		$nlogo = 'banner-'.stringa::filename($r['RAGIONESOCIALE_USR']).'-'.$r['ID_FILE'].$ext;
	//	print $nlogo;
		if(!is_file(FLD_LOGHI.$nlogo)){
			resizeImgBox($src_path, FLD_BANNER.$nlogo, 85, 195, 75);
		}
	//	$f_path = FLD_MAIN.$r['PATH_USER'].'/'.$r['PATH'];
		$logo = new myimage(FLD_BANNER.$nlogo);
		$logo -> set_alt($r['RAGIONESOCIALE_USR']);
		$ret['img'] = $logo -> html;
	//	$aLogo['href'] = io::href($path="",$val=array(),$text="",$js, $target="",$title="",$id="",$class="")
	}
	return $ret;
}

function get_banner_head(){
	$ret = array('img' => '', 'href' => '');
	$q = "SELECT
	users.ID_USER,
	users.FOLDER_USR,
	users.RAGIONESOCIALE_USR,
	users.WEBSITE_USR,
	users.ID_GRUPPI,
	users.IS_BANNER_FRAME,
	users.IS_BANNER_FRAME_EXT,
	users.D_SCAD_BANNER_FRAME,
	users_files.IS_BANNER,
	files.ID_FILE,
	files.PATH,
	files.TYPE
	FROM
	users
	Left Join users_files ON users.ID_USER = users_files.ID_USER
	Left Join files ON users_files.ID_FILE = files.ID_FILE
	WHERE
	(IS_BANNER_FRAME = '1' OR IS_BANNER_FRAME_EXT = '1') AND
	D_SCAD_BANNER_FRAME > CURDATE() AND
	users_files.IS_BANNER = '1' AND
	files.TYPE = 'i'
	ORDER BY RAND()
	LIMIT 0,1";
	
	$r = rs::rec2arr($q);
	if(!empty($r['PATH']) && is_file($src_path = FLD_MAIN.$r['FOLDER_USR'].'/web/'.$r['PATH'])){
		$ext = '.'.stringa::get_extension($r['PATH']);
		$fname = 'banner-'.stringa::filename($r['RAGIONESOCIALE_USR']).'-'.$r['ID_FILE'].$ext;
		if(!is_file(FLD_BANNER.$fname)){
			resizeImgBox($src_path, FLD_BANNER.$fname, 85, 195, 75);
		}
		$image = new myimage(FLD_BANNER.$fname);
		$image -> set_alt($r['RAGIONESOCIALE_USR']);
		$ret['img'] = $image -> html;
		if($r['IS_BANNER_FRAME_EXT'] == '1'){
			$ret['href'] = io::href(stringa::mk_http($r['WEBSITE_USR']), array(), $ret['img'], '', $target="_blank", 'Vai al sito di '.$r['RAGIONESOCIALE_USR'], $id="",$class="");
		}
		else{
			$ret['href'] = io::href('azienda.php', array('iu' => $r['ID_USER']), $ret['img'], '', $target="", 'Vai alla pagina di '.$r['RAGIONESOCIALE_USR'], $id="",$class="");
		}
	}
	return $ret;
}


function get_annunci_in_carrello($id_cart, $id_art){
	$qGet = "SELECT
	funcs.FUNC,
	funcs.PRICE_FUNC
	FROM
	carts_prods
	Left Join prods ON carts_prods.ID_PROD = prods.ID_PROD
	Left Join funcs ON prods.ID_FUNC = funcs.ID_FUNC
	WHERE
	carts_prods.ID_CART =  '$id_cart' AND
	prods.ID_ARTICLE =  '$id_art'";

	$rGet = rs::inMatrix($qGet);
	$ret = '';
	$tmp = '';
	foreach($rGet as $k => $v){
		$tmp .= mytag::in($v['FUNC'].' '.$v['PRICE_FUNC'].'&euro;', 'li', array());
	}
	if(!empty($tmp)) $ret = mytag::in('<lh>In carrello:</lh>'.$tmp, 'ul', array());
	
	return $ret;
}


}
?>