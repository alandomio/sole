<?php
# V.0.1.8

function price_func($id){
	$q = "SELECT * FROM funcs WHERE ID_FUNC = '$id'";
	$r = rs::rec2arr($q);
	return array($id, $r['FUNC'], num::formatvaluta($r['PRICE_FUNC']).' &euro;' );
}

function clean_buffer(){
	$level = ob_get_level();
	for($i=0; $i<=$level; $i++){
		ob_get_clean();
	}
}

function catch_buffer(){
	$ret = '';
	$level = ob_get_level();
	for($i=0; $i<=$level; $i++){
		$ret .= ob_get_clean();
	}
	return $ret;
}

function crop_landscape($imgSrc, $imgDest, $quality, $width,$height){
	$success = false;

    list($width_orig, $height_orig, $type) = getimagesize($imgSrc);  
    
    $ratio_orig = $width_orig/$height_orig;
   
    if ($width/$height > $ratio_orig) {
       $new_height = $width/$ratio_orig;
       $new_width = $width;
    } 
	else{
       $new_width = $height*$ratio_orig;
       $new_height = $height;
    }
   
    $x_mid = $new_width/2;  //horizontal middle
    $y_mid = $new_height/2; //vertical middle
   
    $process = imagecreatetruecolor(round($new_width), round($new_height));
   
	# CREAZIONE IMMAGINE
	if($type == 2){ $myImage = imagecreatefromjpeg($imgSrc);}
	elseif($type == 3){	$myImage = imagecreatefrompng($imgSrc);	}
	elseif($type == 1){	$myImage = imagecreatefromgif($imgSrc);	}
   
   
    imagecopyresampled($process, $myImage, 0, 0, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
    $image_out = imagecreatetruecolor($width, $height);
    imagecopyresampled($image_out, $process, 0, 0, ($x_mid-($width/2)), ($y_mid-($height/2)), $width, $height, $width, $height);
    imagedestroy($process);
    imagedestroy($myImage);


	# SALVATAGGIO IMMAGINI
	if($type == 2){ # JPG
		if(imagejpeg($image_out, $imgDest, $quality)) $success = true;
	}
	elseif($type == 3){ # PNG	
		if (imagepng($image_out, $imgDest, 0)) $success = true; # 0 qualità massima, 9 qualità minima
	}
	elseif($type == 1){ # GIF
		if (imagegif($image_out, $imgDest))	$success = true; 
	}

	return $success;
}

function crop_slandscape($source_file, $output_file, $quality, $width, $height){ 
	list($w, $h, $type) = getimagesize($source_file); 
	$prop_w = $width / $w;
	$prop_h = $height / $h;
	
	if($prop_w > $prop_h){	$mode = 'height'; $size = $height;}
	else{$mode = 'width'; $size = $width;}
	
	$img = getimagesize($source_file);
	$success = false; 
	//list($width, $height, $type) = getimagesize($source_file); 
	$src_x = 0; $src_y = 0;
	
	if($mode == 'width'){
	
		//$src_x = 40;
	//	$new_width = intval(($w * $size) / $w); 
	//	$new_height = intval(($h * $size) / $w);
	}
	elseif($mode == 'height'){
		//$src_y = 90;
	//	$new_width = intval(($w * $size) / $h); 
	//	$new_height = intval(($h * $size) / $h);
	}
	else{
	//	$new_width = intval(($w * $size) / max($w, $h)); 
	//	$new_height = intval(($h * $size) / max($w, $h));
	}
	/*
	1 => GIF 2 => JPG 3 => PNG
	imagecopyresampled($image_out, $image_in, 0, 0, $lux, $luy, $new_size, $new_size, $rdx, $rdy); 
	*/
	
	$ratio_orig = $w/$h;
	   
	if ($width/$height > $ratio_orig) {
	   $new_height = $width/$ratio_orig;
	   $new_width = $width;
	} else {
	   $new_width = $height*$ratio_orig;
	   $new_height = $height;
	}
   
	$x_mid = $new_width/2;  //horizontal middle
	$y_mid = $new_height/2; //vertical middle	
	
	
	$src_x = $x_mid-($width/2);
	$src_y = $y_mid-($height/2);
	
	if($type == 2){ # JPG
		if($image_in = imagecreatefromjpeg($source_file)){
			if ($image_out = imagecreatetruecolor($width, $height)){
				imagecopyresampled($image_out, $image_in, 0, 0, $src_x, $src_y, $width, $height, $w, $h); 
				if (imagejpeg($image_out, $output_file, $quality)){ 
					$success = true; 
				} 
				imagedestroy($image_out); 
			} 
			imagedestroy($image_in); 
		}
	}
	elseif($type == 3){ # PNG
		if($image_in = imagecreatefrompng($source_file)){
			if ($image_out = imagecreatetruecolor($width, $new_height)){
				imagecopyresampled($image_out, $image_in, 0, 0, $src_x, $src_y, $width, $height, $w, $h); 
				if (imagepng($image_out, $output_file,0)){ # 0 qualità massima, 9 qualità minima
					$success = true; 
				} 
				imagedestroy($image_out); 
			} 
			imagedestroy($image_in); 
		}
	}
	elseif($type == 1){ # GIF
		if($image_in = imagecreatefromgif($source_file)){
			if ($image_out = imagecreatetruecolor($width, $new_height)){
				imagecopyresampled($image_out, $image_in, 0, 0, $src_x, $src_y, $width, $height, $w, $h); 
				if (imagegif($image_out, $output_file)){ 
					$success = true; 
				} 
				imagedestroy($image_out); 
			} 
			imagedestroy($image_in); 
		}
	}
	return $success; 
}





function crop_img($source_file, $output_file, $quality, $w, $h /*$centertype*/){
	$img = getimagesize($source_file);
	$success = false; 
	if(!isset($centertype))$centertype=0;
	list($width, $height, $type) = $img; 

	if($width > $new_size or $height > $new_size){
		$centerX = $width/2;
		$centerY = $height/2;
		if( $width > $height){
			if($centertype==1){
				$luy = 0;
				$lux = 0;
			}
			elseif($centertype==2){
				$luy = 0;
				$lux = $width-$height;
			}
			else{
				$luy = 0;
				$lux = $centerX-$centerY;
			}
			$rdy =  $height;
			$rdx =  $height;
		}
		else{
			if($centertype==1){
				$lux = 0;
				$luy = 0;
			}
			elseif($centertype==2){
				$lux = 0;
				$luy = $height-$width;
			}
			else{
				$lux = 0;
				$luy = $centerY-$centerX;
			}
			$rdx = $width;
			$rdy = $width;
		}
	}
	/*
	1 => GIF 2 => JPG 3 => PNG
 	*/
	if($type == 2){ # JPG
		if($image_in = imagecreatefromjpeg($source_file)){
			if ($image_out = imagecreatetruecolor($new_size, $new_size)){
				imagecopyresampled($image_out, $image_in, 0, 0, $lux, $luy, $new_size, $new_size, $rdx, $rdy); 
				if (imagejpeg($image_out, $output_file, $quality)){ 
					$success = true; 
				} 
				imagedestroy($image_out); 
			} 
			imagedestroy($image_in); 
		}
	}
	elseif($type == 3){ # PNG
		if($image_in = imagecreatefrompng($source_file)){
			if ($image_out = imagecreatetruecolor($new_size, $new_size)){
				imagecopyresampled($image_out, $image_in, 0, 0, $lux, $luy, $new_size, $new_size, $rdx, $rdy); 
				if (imagepng($image_out, $output_file,0)){ 
					$success = true; 
				} 
				imagedestroy($image_out); 
			} 
			imagedestroy($image_in); 
		}
	}
	elseif($type == 1){ # GIF
		if($image_in = imagecreatefromgif($source_file)){
			if ($image_out = imagecreatetruecolor($new_size, $new_size)){
				imagecopyresampled($image_out, $image_in, 0, 0, $lux, $luy, $new_size, $new_size, $rdx, $rdy); 
				if (imagegif($image_out, $output_file)){ 
					$success = true; 
				} 
				imagedestroy($image_out); 
			} 
			imagedestroy($image_in); 
		}
	}
	return $success; 
}

function resizeImgBox($source_file, $output_file, $quality, $width, $height){ 
	list($w, $h, $type) = getimagesize($source_file); 
	$prop_w = $width / $w;
	$prop_h = $height / $h;
	
	if($prop_w > $prop_h){	$mode = 'height';$size = $height;}
	else{$mode = 'width';$size = $width;}
	
	return resizeImgBySide($source_file, $output_file, $size, $quality, $mode); 
}



function resizeImgBySide($source_file, $output_file, $size, $quality, $mode = 'long, width, height'){ 
	$img = getimagesize($source_file);
	$success = false; 
	list($width, $height, $type) = getimagesize($source_file); 
	
	if($mode == 'width'){
		$new_width = intval(($width * $size) / $width); 
		$new_height = intval(($height * $size) / $width);
	}
	elseif($mode == 'height'){
		$new_width = intval(($width * $size) / $height); 
		$new_height = intval(($height * $size) / $height);
	}
	else{
		$new_width = intval(($width * $size) / max($width, $height)); 
		$new_height = intval(($height * $size) / max($width, $height));
	}
	/*
	1 => GIF 2 => JPG 3 => PNG
	*/
	if($type == 2){ # JPG
		if($image_in = imagecreatefromjpeg($source_file)){
			if ($image_out = imagecreatetruecolor($new_width, $new_height)){
				imagecopyresampled($image_out, $image_in, 0, 0, 0, 0, $new_width, $new_height, $width, $height); 
				if (imagejpeg($image_out, $output_file, $quality)){ 
					$success = true; 
				} 
				imagedestroy($image_out); 
			} 
			imagedestroy($image_in); 
		}
	}
	elseif($type == 3){ # PNG
		if($image_in = imagecreatefrompng($source_file)){
			if ($image_out = imagecreatetruecolor($new_width, $new_height)){
				imagecopyresampled($image_out, $image_in, 0, 0, 0, 0, $new_width, $new_height, $width, $height); 
				if (imagepng($image_out, $output_file,0)){ # 0 qualità massima, 9 qualità minima
					$success = true; 
				} 
				imagedestroy($image_out); 
			} 
			imagedestroy($image_in); 
		}
	}
	elseif($type == 1){ # GIF
		if($image_in = imagecreatefromgif($source_file)){
			if ($image_out = imagecreatetruecolor($new_width, $new_height)){
				imagecopyresampled($image_out, $image_in, 0, 0, 0, 0, $new_width, $new_height, $width, $height); 
				if (imagegif($image_out, $output_file)){ 
					$success = true; 
				} 
				imagedestroy($image_out); 
			} 
			imagedestroy($image_in); 
		}
	}
	return $success; 
}

function img_slider_list($id){
	$s='';
	$q="SELECT
	files.ID_FILE,
	photos_files.ID_PHOTO,
	files.TYPE,
	files.TITLE,
	files.DESCRIP,
	files.PATH
	FROM
	photos_files
	Left Join files ON photos_files.ID_FILE = files.ID_FILE
	WHERE
	photos_files.ID_PHOTO =  '$id' AND
	files.TYPE =  'i'
";
	$aImg = rs::inMatrix($q);
	foreach($aImg as $rec){
		$s.='<a href="'.IMG_ALB_WEB.$rec['PATH'].'" rel="lightbox[galerie]" target="_blank" title="'.$rec['TITLE'].'"><img src="'.IMG_ALB_THU.$rec['PATH'].'" width="84" height="84" /></a>'."\n";
	}
	return $s;
}


function js_alert($s) {
	if(isset($s) && strlen($s)>0) echo"<script language=\"javascript\">alert('$s')</script>";
}

function decimali($s,$d,$arrotonda){
	$zeri = '';
	$ret = $s;
	for($i=1; $i<=$d; $i++){ $zeri.='0'; }
	if(strpos($s,'.')) $pos=strpos($s,'.');
	elseif(strpos($s,',')) $pos=strpos($s,',');
	
	if($arrotonda==1) $ret = ceil($ret).'.'.$zeri;
	else $ret=substr($s,0,$pos+$d+1);
	if($d==0){
		if($pos=strpos($ret,'.')) $ret = substr($ret,0,$pos);
		elseif($pos=strpos($ret,',')) $ret = substr($ret,0,$pos);
	}
	return $ret;
}

function thisFile(){
	$sPath=$_SERVER['PHP_SELF'];
	if(strpos($sPath,"/")!==false){
		$iStart=strrpos($sPath,"/");
		$sPath=substr($sPath,$iStart+1);
	}	
	elseif(strpos($sPath,"\\")!==false){
		$iStart=strrpos($sPath,"\\");
		$sPath=substr($sPath,$iStart+1);
	}
	return $sPath;	  	  	  
}

function ahref($href="stringa",$text="stringa",$arg=array(),$opz=array(),$target="stringa",$title="stringa"){
	$str='<a href="'.$href.'?';
	if(count($arg)+count($opz)==0)
		$str=substr($str,0,strlen($str)-1);
	else{
		foreach($arg as $k=>$v){
			if(!( (is_string($v) && $v=="") || (is_bool($v) &&  $v==false) || is_null($v)))
				$str.=''.$k.'='.rawurlencode($v).'&';}
		foreach($opz as $k=>$v){
			if(!($v=="" || $v==false || is_null($v)))
				$str.=''.$k.'='.rawurlencode($v).'&';}	
		$str=substr($str,0,strlen($str)-1);}
	return $str.='"'.($target=="" ? ' target="'.$target.'"':"" ).($title=="" ? ' title="'.$title.'"':"" ).'>'.$text.'</a>';
}

function dele_files($folder) {
	foreach (glob("$folder/*") as $filename) {
		unlink($filename);
	}
}

function dele_file_img($conx, $id){
	$q="SELECT * FROM foto WHERE ID_FOTO='$id'";
	$rs=dbExec($conx, $q);
	$path=dbResult($rs,'','FILE');
	if(file_exists(IMG_PATH.$path)) unlink(IMG_PATH.$path);
	if(file_exists(ANT_PATH.$path)) unlink(ANT_PATH.$path);
	dbFree($rs);
	$q="DELETE FROM foto WHERE ID_FOTO='$id'";
	dbExec($conx, $q);
}

function mkSqrjpg2($source_file, $output_file, $new_size, $quality,$centertype){ 
		//$ext=sfile::get_ext($_FILES[$file]["name"]);
		//$func=sfile::func($ext);
	$success = false; 
	if(!isset($centertype))$centertype=0;
	list($width, $height) = getimagesize($source_file); 

	if($width > $new_size or $height > $new_size){
		$centerX = $width/2;
		$centerY = $height/2;
		if( $width > $height){
			if($centertype==1){
				$luy = 0;
				$lux = 0;
			}
			elseif($centertype==2){
				$luy = 0;
				$lux = $width-$height;
			}
			else{
				$luy = 0;
				$lux = $centerX-$centerY;
			}
			$rdy =  $height;
			$rdx =  $height;
		}
		else{
			if($centertype==1){
				$lux = 0;
				$luy = 0;
			}
			elseif($centertype==2){
				$lux = 0;
				$luy = $height-$width;
			}
			else{
				$lux = 0;
				$luy = $centerY-$centerX;
			}
			$rdx = $width;
			$rdy = $width;
		}
	}
	//if ($image_in = imagecreatefromjpeg($source_file)){ 
	if ($image_in =imagecreatefromstring(file_get_contents($source_file))){ 
		if ($image_out = imagecreatetruecolor($new_size, $new_size)){
			imagecopyresampled($image_out, $image_in, 0, 0, $lux, $luy, $new_size, $new_size, $rdx, $rdy); 
			if (imagejpeg($image_out, $output_file, $quality)){ 
				$success = true; 
			} 
			imagedestroy($image_out); 
		} 
		imagedestroy($image_in); 
	} 
	return $success; 
}

function mkSqrImg($source_file, $output_file, $new_size, $quality, $centertype){
	$img = getimagesize($source_file);
	$success = false; 
	if(!isset($centertype))$centertype=0;
	list($width, $height, $type) = $img; 

	if($width > $new_size or $height > $new_size){
		$centerX = $width/2;
		$centerY = $height/2;
		if( $width > $height){
			if($centertype==1){
				$luy = 0;
				$lux = 0;
			}
			elseif($centertype==2){
				$luy = 0;
				$lux = $width-$height;
			}
			else{
				$luy = 0;
				$lux = $centerX-$centerY;
			}
			$rdy =  $height;
			$rdx =  $height;
		}
		else{
			if($centertype==1){
				$lux = 0;
				$luy = 0;
			}
			elseif($centertype==2){
				$lux = 0;
				$luy = $height-$width;
			}
			else{
				$lux = 0;
				$luy = $centerY-$centerX;
			}
			$rdx = $width;
			$rdy = $width;
		}
	}
	/*
	1 => GIF 2 => JPG 3 => PNG
 	*/
	if($type == 2){ # JPG
		if($image_in = imagecreatefromjpeg($source_file)){
			if ($image_out = imagecreatetruecolor($new_size, $new_size)){
				imagecopyresampled($image_out, $image_in, 0, 0, $lux, $luy, $new_size, $new_size, $rdx, $rdy); 
				if (imagejpeg($image_out, $output_file, $quality)){ 
					$success = true; 
				} 
				imagedestroy($image_out); 
			} 
			imagedestroy($image_in); 
		}
	}
	elseif($type == 3){ # PNG
		if($image_in = imagecreatefrompng($source_file)){
			if ($image_out = imagecreatetruecolor($new_size, $new_size)){
				imagecopyresampled($image_out, $image_in, 0, 0, $lux, $luy, $new_size, $new_size, $rdx, $rdy); 
				if (imagepng($image_out, $output_file,0)){ 
					$success = true; 
				} 
				imagedestroy($image_out); 
			} 
			imagedestroy($image_in); 
		}
	}
	elseif($type == 1){ # GIF
		if($image_in = imagecreatefromgif($source_file)){
			if ($image_out = imagecreatetruecolor($new_size, $new_size)){
				imagecopyresampled($image_out, $image_in, 0, 0, $lux, $luy, $new_size, $new_size, $rdx, $rdy); 
				if (imagegif($image_out, $output_file)){ 
					$success = true; 
				} 
				imagedestroy($image_out); 
			} 
			imagedestroy($image_in); 
		}
	}
	return $success; 
}

function resizeImg($source_file, $output_file, $size, $quality){ 
	$img = getimagesize($source_file);
	$success = false; 
	list($width, $height, $type) = getimagesize($source_file); 
	$new_width = intval(($width * $size) / max($width, $height)); 
	$new_height = intval(($height * $size) / max($width, $height));
	/*
	1 => GIF 2 => JPG 3 => PNG
	*/
	if($type == 2){ # JPG
		if($image_in = imagecreatefromjpeg($source_file)){
			if ($image_out = imagecreatetruecolor($new_width, $new_height)){
				imagecopyresampled($image_out, $image_in, 0, 0, 0, 0, $new_width, $new_height, $width, $height); 
				if (imagejpeg($image_out, $output_file, $quality)){ 
					$success = true; 
				} 
				imagedestroy($image_out); 
			} 
			imagedestroy($image_in); 
		}
	}
	elseif($type == 3){ # PNG
		if($image_in = imagecreatefrompng($source_file)){
			if ($image_out = imagecreatetruecolor($new_width, $new_height)){
				imagecopyresampled($image_out, $image_in, 0, 0, 0, 0, $new_width, $new_height, $width, $height); 
				if (imagepng($image_out, $output_file,0)){ # 0 qualità massima, 9 qualità minima
					$success = true; 
				} 
				imagedestroy($image_out); 
			} 
			imagedestroy($image_in); 
		}
	}
	elseif($type == 1){ # GIF
		if($image_in = imagecreatefromgif($source_file)){
			if ($image_out = imagecreatetruecolor($new_width, $new_height)){
				imagecopyresampled($image_out, $image_in, 0, 0, 0, 0, $new_width, $new_height, $width, $height); 
				if (imagegif($image_out, $output_file)){ 
					$success = true; 
				} 
				imagedestroy($image_out); 
			} 
			imagedestroy($image_in); 
		}
	}
	return $success; 
}

function mkSqrjpg($source_file, $output_file, $new_size, $quality,$centertype){ 
	$success = false; 
	if(!isset($centertype))$centertype=0;
	list($width, $height) = getimagesize($source_file); 

	if($width > $new_size or $height > $new_size){
		$centerX = $width/2;
		$centerY = $height/2;
		if( $width > $height){
			if($centertype==1){
				$luy = 0;
				$lux = 0;
			}
			elseif($centertype==2){
				$luy = 0;
				$lux = $width-$height;
			}
			else{
				$luy = 0;
				$lux = $centerX-$centerY;
			}
			$rdy =  $height;
			$rdx =  $height;
		}
		else{
			if($centertype==1){
				$lux = 0;
				$luy = 0;
			}
			elseif($centertype==2){
				$lux = 0;
				$luy = $height-$width;
			}
			else{
				$lux = 0;
				$luy = $centerY-$centerX;
			}
			$rdx = $width;
			$rdy = $width;
		}
	}
	if ($image_in = imagecreatefromjpeg($source_file)){ 
		if ($image_out = imagecreatetruecolor($new_size, $new_size)){
			imagecopyresampled($image_out, $image_in, 0, 0, $lux, $luy, $new_size, $new_size, $rdx, $rdy); 
			if (imagejpeg($image_out, $output_file, $quality)){ 
				$success = true; 
			} 
			imagedestroy($image_out); 
		} 
		imagedestroy($image_in); 
	} 
	return $success; 
}

function loadtempo()
	{
	$tempo=microtime();
	$tempo=explode(" ",$tempo);
	$tempo[0]=floatval($tempo[0]);
	$tempo[1]=floatval($tempo[1]);
	return($tempo[0]+$tempo[1]);
	}

function isEmail($sString){
	$sString=trim(strtolower(strip_tags($sString)));
	if(strlen($sString)>0)
		{
		$exp="^([a-z0-9\._-]+)(@[a-z0-9\._-]+)(\.{1}[a-z]{2,6})$";
		if(ereg($exp,$sString)) return true;
		else return false;
		}
	else return false;
}
	
function filter_back(){
	global $fParolachiave;
	global $fAutore;
	global $fOrdinamento;
	global $fAlbum;
	global $fPubblicata;
	
	genera_hid("fParolachiave", $fParolachiave);
	genera_hid("fAutore", $fAutore);
	genera_hid("fOrdinamento", $fOrdinamento);
	genera_hid("fAlbum", $fAlbum);
	genera_hid("fPubblicata", $fPubblicata);	
}
	
function mk_fhidden($aCampi){
	$sReturn="";
	for($i=0; $i<count($aCampi); $i++){
		global $$aCampi[$i];
		if(strlen($$aCampi[$i])>0) {
			$sReturn.="<input name=\"$aCampi[$i]\" type=\"hidden\" value=\"".$$aCampi[$i]."\" />";
		}
	}
	return $sReturn;
}
	
function mk_fhref($aCampi, $sVar)
	{
	$flag=0;
	$sReturn="";
	if(strlen($sVar)>0)
		{
		$sReturn.="?$sVar";
		$flag=1;
		}
	for($i=0; $i<count($aCampi); $i++)
		{
		global $$aCampi[$i];
		if(strlen($$aCampi[$i])>0) 
			{
			if($flag==0)
				{
				$sReturn.="?$aCampi[$i]=".$$aCampi[$i];
				$flag=1;
				}
			else
				{
				$sReturn.="&$aCampi[$i]=".$$aCampi[$i];
				}
			}
		}
	return $sReturn;
	}
		
function cifra($ss, $key)
	{
	$ris="";
	for($i=0; $i<strlen($ss); $i++)
		{
		$kk=ord($key[$i % strlen($key)]);
		$kk=($kk % 256);
		$x=(ord($ss[$i])) % 256;
		$x+=$kk+$i+71;
		$app[$i]=$x;
		}
	$ris=implode(" ",$app);
	return $ris;
	}
		
function decifra($ss, $key)
	{
	$app=explode(" ",$ss);
	$ss="";
	for($i=0;$i<sizeof($app);$i++)
		{
		$kk=ord($key[$i % strlen($key)]);
		$kk=($kk % 256);
		$app[$i]-=(71+$i+$kk);
		$ss.=chr($app[$i]);
		}
	return $ss;
	}
		
function leftfrom($stringa,$sep)
        {
        $pos_sep=strpos($stringa,"$sep");
        $risultato=substr($stringa,0,$pos_sep);
        return $risultato;
        }
function leftfromlast($stringa,$sep)
        {
        $pos_sep=strrpos($stringa,"$sep");
        $risultato=substr($stringa,0,$pos_sep);
        return $risultato;
        }			
function rightfrom($stringa,$sep)
        {
        $pos_sep=strpos($stringa,"$sep");
        $diff=(strlen($stringa)-($pos_sep+1));
        $risultato=substr($stringa,$pos_sep+1,$diff);
        return $risultato;
        }
function rightfromlast($stringa,$sep)
        {
        $pos_sep=strrpos($stringa,"$sep");
        $diff=(strlen($stringa)-($pos_sep+1));
        $risultato=substr($stringa,$pos_sep+1,$diff);
        return $risultato;
        }		
function strbeetween($stringa,$sep1,$sep2)
        {
        $pos_sep1=strpos($stringa,"$sep1");
        $pos_sep2=strpos($stringa,"$sep2");
        $diff=($pos_sep2-($pos_sep1+1));
        $risultato=substr($stringa,$pos_sep1+1,$diff);
        return $risultato;
        }
						
function charclear($sdata)
        {
        $ris="";
        $stringa=strtoupper($sdata);
        for($i=0;$i<strlen($stringa);$i++)
                {
                if(strpos("QWERTYUIOPASDFGHJKLZXCVBNM_0123456789",$stringa[$i])!==false)
                {
                $ris=$ris.$stringa[$i];
                }
                }
        return $ris;
        }
function strrcut($str, $rplstr, $lng)
	{
	if(strlen($str)>$lng)
		{
		$str=strrev($str);
		if(strlen($rplstr)>$lng) $lng=strlen($rplstr);
		$str=substr($str, 0 ,$lng-strlen($rplstr)).$rplstr;
		$str=strrev($str);
		}
	return $str;
	}				

function strcut($str, $rplstr, $lng){
	$str = strip_tags($str);
	if(strlen($str)>$lng){
		if(strlen($rplstr)>$lng) $lng=strlen($rplstr);
      $str=substr($str, 0 ,$lng-strlen($rplstr));
      
     	if(strlen(strrchr($str, '&'))>0 && strlen(strrchr($str, '&'))<8){ # RISOLVO LO SMEZZAMENTO DEI CARATTERI HTML
        $str = leftfromlast($str,'&');
      }
		$str.=$rplstr;
	}
 	return $str;
}


function eko()
   {
   $aFuncGetArgs=func_get_args();
   if(isset($aFuncGetArgs[0]))
      {
	  $sString=$aFuncGetArgs[0];
	  if(ini_get('magic_quotes_sybase')+get_magic_quotes_gpc()) 
	     {
		 $sString=stripslashes($sString);
		 } 
	   if(isset($aFuncGetArgs[1]))
		  {
		  $sFormat=$aFuncGetArgs[1];
		  if( strpos($sFormat,"-t")!==false )
		     {
			 $sString=trim($sString);
			 }
	      if( strpos($sFormat,"-s")!==false )
		     {
			 $sString=strip_tags($sString);
			 } 
		  if($sFormat=="-y")
		     {
			 $sString=trim(strip_tags($sString));
			 }
		  if(isset($aFuncGetArgs[2]))
			 {
			 $wrap_cut_int=$aFuncGetArgs[2];
			 if( strpos($wrap_cut_int,"w")!==false )
			    {
			    $wrap_cut="w";
			    $intWrapCut=intval(rightfrom($wrap_cut_int,$preInt));	 
			    }
		     else //( strpos($wrap_cut_int,"c")!==false )
			    {
			    $wrap_cut="c";
			    $intWrapCut=intval(rightfrom($wrap_cut_int,$preInt));	 
			    }
			 if(isset($aFuncGetArgs[3]))
			    {
		        $sufCutWrap=$aFuncGetArgs[3];
			    }
			 else
			    {
				if($wrap_cut=="w")
				   {
				   $sufWrapCut="\n";
				   }
				else
				   {
				   $sufWrapCut="";
				   }
				} // fine   else(isset($aFuncGetArgs[3]))
			 if($wrap_cut=="w")
			     {
				 $sString=wordwrap($sString,$intWrapCut,$sufWrapCut,1);
				 }
			 else
			     {
				 $sString=strcut($sString,$sufWrapCut,$intWrapCut);
				 }
			 }    // fine  if(isset($aFuncGetArgs[2]))
		  }  // fine  if(isset($aFuncGetArgs[1]))
	   else
	      {
		  //$sString=trim(strip_tags($sString));
		  } // fine else (isset($aFuncGetArgs[1]))
	  echo   $sString;
	  }  // fine if(isset($aFuncGetArgs[0]))
   } // fine function	

function resizeJpg($source_file, $output_file, $size, $quality) 
	{ 
	$success = false; 
	list($width, $height) = getimagesize($source_file); 
	$new_width = intval(($width * $size) / max($width, $height)); 
	$new_height = intval(($height * $size) / max($width, $height)); 
	if ($image_in = imagecreatefromjpeg($source_file)) 
		{ 
		if ($image_out = imagecreatetruecolor($new_width, $new_height)) 
			{
			imagecopyresampled($image_out, $image_in, 0, 0, 0, 0, $new_width, $new_height, $width, $height); 
			if (imagejpeg($image_out, $output_file, $quality)) 
				{ 
				$success = true; 
				} 
			imagedestroy($image_out); 
			} 
		imagedestroy($image_in); 
		} 
	return $success; 
	} 

function resizeSqrjpg($source_file, $output_file, $new_size, $quality) 
	{ 
	$success = false; 
	$size = getimagesize($source_file);
	if( $size[0]>$new_size or $size[1]>$new_size ){
		$centerX = $size[0]/2;
		$centerY = $size[1]/2;
		if( $size[0] > $size[1] ){
		  $luy = 0;
		  $lux = $centerX-$centerY;
		  $rdy = $size[1];
		  $rdx = $size[1];
		}
		else{
		  $lux = 0;
		  $luy = $centerY-$centerX;
		  $rdx = $size[0];
		  $rdy = $size[0];
		}
	}
	if ($image_in = imagecreatefromjpeg($source_file)) 
		{ 
		if ($image_out = imagecreatetruecolor($new_size, $new_size)) 
			{
			imagecopyresampled($image_out, $image_in, 0, 0, $lux, $luy, $new_size, $new_size, $rdx, $rdy); 
			if (imagejpeg($image_out, $output_file, $quality)) 
				{ 
				$success = true; 
				} 
			imagedestroy($image_out); 
			} 
		imagedestroy($image_in); 
		} 
	return $success; 
	} 

function split_odbc_data($value)
	 {
	 if(isset($value))
	     {
		 if( strpos($value,"/")!==false )
			  {
			  $sepData="/";
			  }
		  else if( strpos($value,"-")!==false )
			 {
			 $sepData="-";
			 }
		 else
		     {
			 $sepData=false;
			 }
		 if( strpos($value,":")!==false )
			  {
			  $sepOra=":";
			  }
		  else if( strpos($value,".")!==false )
			 {
			 $sepOra=".";
			 }
		 else
		    {
			$sepOra=false;
			}	 
		if($sepOra==true && $sepData==true)
		   {
		   $hhminss=rightfrom($value," ");
		   $aammgg=leftfrom($value," ");
		   }
		elseif($sepData==true)
		   {
		   $aammgg=$value;
		   $hhminss=NULL;
		   }
		elseif($sepOra==true)
		   {
		   $hhminss=$value;
		   $aammgg=NULL;
		   } 
		else
		   {
		   $hhminss=NULL;
		   $aammgg=NULL;
		   }  
	    if(isset($aammgg))
		   {
			$aa=leftfrom($aammgg, $sepData);
			$mmgg=rightfrom($aammgg, $sepData);
			$mm=leftfrom($mmgg, $sepData);
			$gg=rightfrom($mmgg, $sepData);
			$aValue["gg"]=$gg;
			$aValue["mm"]=$mm; 
			$aValue["aa"]=$aa; 
		   }
		else
		   {
		   	$aValue["gg"]="";
			$aValue["mm"]=""; 
			$aValue["aa"]=""; 
		   }
		if(isset($hhminss))
		   {
		   $hh=leftfrom($hhminss,$sepOra);
		   $minss=rightfrom($hhminss,$sepOra);
		   $min=leftfrom($minss,$sepOra);
		   $ss=rightfrom($minss,$sepOra);
		   $aValue["hh"]=$hh;
		   $aValue["min"]=$min; 
		   $aValue["ss"]=$ss; 
		   }
		else
		   {
		   $aValue["hh"]="";
		   $aValue["min"]=""; 
		   $aValue["ss"]=""; 
		   }      
		 }
	else
	   {
	   $aValue=NULL;
	   }  
	 return $aValue;
	 }	 
function getFileFromPath($sPath)
   {
   if(strpos($sPath,"/")!==false)
      {
	  $iStart=strrpos($sPath,"/");
	  $sPath=substr($sPath,$iStart+1);
	  }
   elseif(strpos($sPath,"\\")!==false)
      {
	  $iStart=strrpos($sPath,"\\");
	  $sPath=substr($sPath,$iStart+1);
	  }
   else
      {
	  $sPath;
	  } 
   return $sPath;	  	  	  
   }

function impQuery($sQuery)
	{
	if(strlen($sQuery)>0)
		{
		for($i=0; $i<strlen($sQuery); $i++)
			{
			$aQuery[$i]=ord($sQuery[$i]);
			}
	$sQuery=implode(" ", $aQuery);
	return $sQuery;
		}	
	}

function expQuery($sQuery)
	{
	if(strlen($sQuery)>0)
		{
		$aQuery=explode(" ", $sQuery);
		for($i=0; $i<count($aQuery); $i++)
			{
			$aQueryOrd[$i]=chr($aQuery[$i]);
			}
	$sQuery=implode("", $aQueryOrd);
	return $sQuery;
		}	
	}

function genera_hid($name,$value)
	{
	?>
	<input type="hidden" name="<? echo $name; ?>" value="<? echo $value; ?>">
	<?
	}
function genera_data($name,$value,$isNullable,$sOption)
	{
	$len_min="";
	$len_max="";
		if(strpos($value,";"))
		   {
		   $iNum=leftfrom($value,";");
		   $aT=getdate();
		   if(rightfrom($value,";")=="mm")
		      {
			  $uTs=mktime($aT["hours"],$aT["minutes"],$aT["seconds"],(($aT["mon"])+$iNum),$aT["mday"],$aT["year"]);
			  }
			elseif(rightfrom($value,";")=="aa")
			   {
			   $uTs=mktime($aT["hours"],$aT["minutes"],$aT["seconds"],$aT["mon"],$aT["mday"],(($aT["year"])+$iNum));
			   }
			else //giorno  
				{
				$uTs=mktime($aT["hours"],$aT["minutes"],$aT["seconds"],$aT["mon"],(($aT["mday"])+$iNum),$aT["year"]);
			   } 
		   $gg=date("d",  date($uTs));
		   $mm=date("m",  date($uTs));
		   $aa=date("Y",  date($uTs));     
		   }

	   elseif(is_array($value))
	      {
          $gg=$value["gg"];
	      $mm=$value["mm"];
	      $aa=$value["aa"];
		  }
	   elseif($value=="time")
	      {
		  $gg=date("d",  time());
		  $mm=date("m",  time());
		  $aa=date("Y",  time());
		  }  
		elseif($value=="null")  
		   {
		  $gg="";
	      $mm="";
	      $aa="";
		   }
		elseif($value)
		   {
		  $value=split_odbc_data($value);
          $gg=$value["gg"];
	      $mm=$value["mm"];
	      $aa=$value["aa"];
		   }
		else 
		   { 
		   $nameApp_gg=$name."_gg";
		   global $$nameApp_gg;
		   $gg=$$nameApp_gg;
		   $nameApp_mm=$name."_mm";
		   global $$nameApp_mm;
		   $mm=$$nameApp_mm;
		   $nameApp_aa=$name."_aa";
		   global $$nameApp_aa;
		   $aa=$$nameApp_aa;
		   }
	   if(  strpos($sOption,"tabindex")!==false   )
	      {
		  $iTabIndex=strbeetween($sOption,"\"","\"");
		  $sOption="";
		  }    
	   if(!isset($sNameDiv) || $sNameDiv==NULL) $sNameDiv="noDIV"; // se il nome del div non specificato si presume che l'elmento non si trova in nessun div
	   $id="";
	   $id=$id."GIORNO".",".$isNullable.",".$sNameDiv.",".$len_min.",".$len_max;
   ?>
   <input type="text"  name="<? echo $name."_gg"; ?>" id="<? echo $id;  ?>" value="<? echo $gg; ?>" <? if(isset($iTabIndex)) { echo "tabindex=\"$iTabIndex\"";}?>
    maxlength="2" size="2" <? echo $sOption; ?>
        onkeypress="return control_num_data(event);">
        /
   <input type="text" name="<? echo $name."_mm"; ?>" value="<? echo $mm; ?>" <? if(isset($iTabIndex)) { echo "tabindex=\"$iTabIndex++\"";}?>
    maxlength="2" title="<? echo $name; ?>"size="2"  <? echo $sOption; ?> onkeypress="return control_num_data(event);">
        /
   <input type="text" name="<? echo $name."_aa"; ?>" value="<? echo $aa; ?>" size="4"   <? if(isset($iTabIndex)) { echo "tabindex=\"$iTabIndex++\"";}?>
    maxlength="4" <? echo $sOption; ?> onkeypress="return control_num_data(event);">
    <? 
	}
function recompDatafromSplit($aData,$sFormat,$sepData,$sepOra,$sepDataOra)
   {
   
   $iData_hh=NULL;
   $iData_min=NULL;
   $iData_ss=NULL;
    if(!isset($aData))
	   {
	   $sRecomposeData="";
	   }
	elseif($aData["aa"]=="0000" && $aData["mm"]=="00" && $aData["gg"]=="00")   
	   {
	   $sRecomposeData="";
	   }
	else
	   {
	   	if(!isset($sepData))    $sepData="/";
		if(!isset($sepOra))     $sepOra=":";
		if(!isset($sepDataOra)) $sepDataOra=chr(32);
	   if(is_array($aData))
	      {	   
	
			if(isset($aData["gg"]))
			   {
			   $iData_gg=str_pad($aData["gg"],2,"0",STR_PAD_LEFT);
			   }
			if(isset($aData["mm"]))
			   {
			   $iData_mm=str_pad($aData["mm"],2,"0",STR_PAD_LEFT);
			   }
		  $iData_aa=$aData["aa"];   
		  if(isset($aData["hh"]))
			   {
			   $iData_hh=str_pad($aData["hh"],2,"0",STR_PAD_LEFT);
			   }   
		if(isset($aData["min"]))
			   {
			   $iData_min=str_pad($aData["min"],2,"0",STR_PAD_LEFT);
			   }   
		if(isset($aData["ss"]))
			   {
			   $iData_ss=str_pad($aData["ss"],2,"0",STR_PAD_LEFT);
			   } 
			}
		else
		   {
		   $aData=split_odbc_data($aData);
		   $iData_aa=$aData["aa"];
		   $iData_mm=$aData["mm"];
		   $iData_gg=$aData["gg"];
		   if(isset($aData["hh"]))  $iData_hh=$aData["hh"];
		   if(isset($aData["hh"]))  $iData_min=$aData["min"];
		   if(isset($aData["hh"]))  $iData_ss=$aData["ss"]; 
		   }	  
				if($sFormat=="ggmmaa") //ggmmaa;
				   {
				   $iData_aa=substr($iData_aa,2);
				   $sRecomposeData=$iData_gg.$sepData.$iData_mm.$sepData.$iData_aa;
				   }
				elseif($sFormat=="hhmin")
				   {
					$sRecomposeData=$iData_hh.$sepOra.$iData_min;
				   }
				elseif($sFormat=="ggmmaaaahhminss")//ggmmaaaa
				   {
				   $sRecomposeData=$iData_gg.$sepData.$iData_mm.$sepData.$iData_aa.$sepDataOra.$iData_hh.$sepOra.$iData_min.$sepOra.$iData_ss;
				   }
				 elseif($sFormat=="ggmmaaaahhmin")//ggmmaaaa
				   {
				   $sRecomposeData=$iData_gg.$sepData.$iData_mm.$sepData.$iData_aa.$sepDataOra.$iData_hh.$sepOra.$iData_min;
				   } 
				  elseif($sFormat=="ggmmaahhmin")//ggmmaaaa
				   {
					$iData_aa=substr($iData_aa,2);
				   $sRecomposeData=$iData_gg.$sepData.$iData_mm.$sepData.$iData_aa.$sepDataOra.$iData_hh.$sepOra.$iData_min;
				   }     
				elseif($sFormat=="ggmmaaaa")//ggmmaaaa
				   {
				   $sRecomposeData=$iData_gg.$sepData.$iData_mm.$sepData.$iData_aa;
				   }   
				elseif($sFormat=="aaaammgg")//aaaammgg
				   {
				   $sRecomposeData=$iData_aa.$sepData.$iData_mm.$sepData.$iData_gg;
				   }
				elseif($sFormat=="hhminggmmaa")//hh min  ggmmaa
				   {
				   $iData_aa=substr($iData_aa,2);
				   $sRecomposeData=$iData_hh.$sepOra.$iData_min.$sepDataOra.$iData_gg.$sepData.$iData_mm.$sepData.$iData_aa;
				   }//ggmmaaaahhminss
		
				elseif($sFormat=="hhminss")//hh min ss 
				   {
				   $sRecomposeData=$iData_hh.$sepOra.$iData_min.$sepOra.$iData_ss;
				   } 
				else //aaaammgg hh min ss
				   {
				   $sRecomposeData=$iData_aa.$sepData.$iData_mm.$sepData.$iData_gg.$sepDataOra.$iData_hh.$sepOra.$iData_min.$sepOra.$iData_ss;
				   } 
		}
	return $sRecomposeData;
	}
function recompData($iData_gg,$iData_mm,$iData_aa,$iData_hh,$iData_min,$iData_ss,$sFormat,$sepData,$sepOra,$sepDataOra)
   {
    if ( (!isset($iData_gg) || $iData_gg==NULL) || (!isset($iData_mm) || $iData_mm==NULL) || (!isset($iData_aa) || $iData_aa==NULL))
	   {
	   $sRecomposeData="";
	   }
	else
	   {
	   if($iData_gg)
	      {
		  $iData_gg=str_pad($iData_gg,2,"0",STR_PAD_LEFT);
		  }
		 if($iData_mm)
	      {
		   $iData_mm=str_pad($iData_mm,2,"0",STR_PAD_LEFT);
		  }  
		/*if($iData_aa)
	      {
		   $iData_gg=str_pad($iData_gg,2,"0",STR_PAD_LEFT);
		  } */
		if($iData_hh)
	      {
		   $iData_hh=str_pad($iData_hh,2,"0",STR_PAD_LEFT);
		  } 
		 if($iData_min)
	      {
		   $iData_min=str_pad($iData_min,2,"0",STR_PAD_LEFT);
		  } 
		 if($iData_ss)
	      {
		   $iData_ss=str_pad($iData_ss,2,"0",STR_PAD_LEFT);
		  }            
		if(!isset($sepData))    $sepData="/";
		if(!isset($sepOra))     $sepOra=":";
		if(!isset($sepDataOra)) $sepDataOra=chr(32);
		if($sFormat=="ggmmaa") //ggmmaa;
		   {
		   $iData_aa=substr($iData_aa,2);
		   $sRecomposeData=$iData_gg.$sepData.$iData_mm.$sepData.$iData_aa;
		   }
		elseif($sFormat=="ggmmaaaa")//ggmmaaaa
		   {
		   $sRecomposeData=$iData_gg.$sepData.$iData_mm.$sepData.$iData_aa;
		   }
		elseif($sFormat=="aaaammgg")//aaaammgg
		   {
		   $sRecomposeData=$iData_aa.$sepData.$iData_mm.$sepData.$iData_gg;
		   }
		elseif($sFormat=="hhminggmmaa")//hh min  ggmmaa
		   {
		   $iData_aa=substr($iData_aa,2);
		   $sRecomposeData=$iData_hh.$sepOra.$iData_min.$sepDataOra.$iData_gg.$sepData.$iData_mm.$sepData.$iData_aa;
		   }
		elseif($sFormat=="hhminssggmmaaaa")//hh min ss ggmmaaaa
		   {
		   $sRecomposeData=$iData_hh.$sepOra.$iData_min.$sepOra.$iData_ss.$sepDataOra.$iData_gg.$sepData.$iData_mm.$sepData.$iData_aa;
		   } 
		else //aaaammgg hh min ss
		   {
		   $sRecomposeData=$iData_aa.$sepData.$iData_mm.$sepData.$iData_gg.$sepDataOra.$iData_hh.$sepOra.$iData_min.$sepOra.$iData_ss;
		   }  
	}	           
	return $sRecomposeData;
	}
function getSqlfromTxtData($iDataFrom,$iDataTo,$sFieldData) 
   {  
	if($iDataFrom!="" || $iDataTo!="")
	   {
	   $i=0;
	   if($iDataFrom!="")
	     {
	      $aSqlData[$i]="  $sFieldData>='".$iDataFrom."'";
		  $i++;
	     }
	   if($iDataTo!="" )	
	     {
	     $aSqlData[$i]=" $sFieldData<='".$iDataTo."'";
	     }    	 	 
	  $sSqlData=" ( ";
	  for($y=0;$y<count($aSqlData);$y++)
	     {
		 if($y==(count($aSqlData)-1))
		    {
			$sWord=$aSqlData[$y];
			$sSqlData=$sSqlData." ".$sWord." ";
			}
		 else
		    {
			$sWord=$aSqlData[$y];
			$sSqlData=$sSqlData." ".$sWord." "."AND"." ";
			}
       }
	   $sSqlData=$sSqlData." ) ";
	   return $sSqlData;  
	   }
	else
	   {
	   return false;
	   }
   }
   
function isset_var($a,$alt)
	{
	for($i=0; $i<count($a); $i++)
		{
		global $$a[$i];
		$svar=$a[$i];
		if(!isset($$a[$i])) 
			{
			$GLOBALS["$svar"] = $alt;
			}
		}
	}
function chkText($string, $lenMin, $lenMax, $isTrim, $isStriptags, $nomeCampo)
	{
	$aErr[1]="";
	if(!isset($string) || $string=="") $aErr[1].="il campo $nomeCampo &egrave; obbligatorio<br />";
	else 
		{
		if($isStriptags==true)
			{
			if(strlen($string)!=strlen(strip_tags($string))) $aErr[1].="nel campo $nomeCampo sono stati inseriti caratteri non ammessi<br />";
			}
		if($isTrim==true)
			{
			$string=trim($string);
			$len=strlen($string);	
			}
		else $len=strlen($string);
		if(isset($lenMin) && $lenMin>0)
			{
			if($len<$lenMin) $aErr[1].="compilare il campo $nomeCampo utilizzando almeno $lenMin caratteri<br />";
			}
		if(isset($lenMax) && $lenMax>0)
			{
			if($len>$lenMax) $aErr[1].="il campo $nomeCampo consente al massimo $lenMax caratteri<br />";
			}
		}
	$aErr[0]=$string;
	return $aErr;
	}
	
function chkJpg($file,$size){
	$err=array();
	$kb=$size/1024;
	if(!$file['tmp_name']) $err[]="invio immagine fallito, sono accettate immagini del peso massimo di $kb KByte";
	else{
		if($file["size"]>$size) $err[]="impossibile salvare l'immagine: sono ammesse solamente immagini pesanti al massimo $kb KByte";
/*		if($file["type"]=="image/jpeg" || $file["type"]=="image/pjpeg"){
			$aDimimg=getimagesize($file['tmp_name']);
			if($aDimimg["channels"]!=3) $err[]="sono accettate solamente immagini RGB"; 			
		}*/
	//else $err[]="sono accettate solamente immagini in formato jpg";
	}
	return $err;
}

function mkPulsantiImg($iTotRec,$rc,$iRows,$iCols,$rsFoto,$action)
	{
	echo "<div id=\"pulsantiera\">";
	if(!isset($rc)) $rc=0;
	$iTotPag=ceil($iTotRec/($iRows*$iCols));
	$lo_dis="<span class=\"pulsantiera_dis\"><</span>";
	$lf_dis="<span class=\"pulsantiera_dis\">prima</span>";
	$lt_dis="<span class=\"pulsantiera_dis\"><<</span>";
	$no_dis="<span class=\"pulsantiera_dis\">></span>";
	$nl_dis="<span class=\"pulsantiera_dis\">ultima</span>";
	$nt_dis="<span class=\"pulsantiera_dis\">>></span>";
	
	if($iTotPag>0 && $rc>0)
		{
		$lo="<a href=\"".$action."?idm=".dbResult($rsFoto,($rc-1),'id_documento')."\" title=\"pagina precedente\"><</a>";
		$lf="<a href=\"".$action."?idm=".dbResult($rsFoto,0,'id_documento')."\" title=\"prima pagina\">prima</a>";
		if(($rc-10)>=0)
			{
			$lt="<a href=\"".$action."?idm=".dbResult($rsFoto,($rc-10),'id_documento')."\" title=\"indietro di 10 pagine\"><<</a>";
			}
		}
	if ($iTotRec>=1) $sCentrale="<span class=\"f_bblue\">".($rc+1)."</span><span class=\"f_blue\"> di $iTotRec</span>";
	if($iTotRec==0)
		{
		$sCentrale="&nbsp;";
		}	
	if ($rc<($iTotRec-1))
		{
		$no="<a href=\"".$action."?idm=".dbResult($rsFoto,($rc+1),'id_documento')."\" title=\"pagina successiva\">></a>";
		$nl="<a href=\"".$action."?idm=".dbResult($rsFoto,($iTotRec-1),'id_documento')."\" title=\"ultima pagina\">ultima</a>";
		if(($iTotRec-$rc)>10)
			{
			$nt="<a href=\"".$action."?idm=".dbResult($rsFoto,($rc+10),'id_documento')."\" title=\"avanti di 10 pagine\">>></a>";
			} 
		}
	?>
	<ul>
	<li><? if(isset($lf)) echo $lf;  else echo $lf_dis; ?></li>
	<li><? if(isset($lt)) echo $lt;  else echo $lt_dis; ?></li>
	<li><? if(isset($lo)) echo $lo;  else echo $lo_dis; ?></li>
	<li><? echo $sCentrale; ?></li>
	<li><? if(isset($no)) echo $no;  else echo $no_dis; ?></li>
	<li><? if(isset($nt)) echo $nt;  else echo $nt_dis; ?></li>
	<li><? if(isset($nl)) echo $nl;  else echo $nl_dis; ?></li>
	</ul>
	</div>
	<div style="width:576px; text-align:center"><? echo prwframe($rsFoto,$rc); ?></div>
	<?
	
	}
function mkPulsantiAlbum($iTotRec,$pg,$iRows,$iCols,$rsFoto,$action)
	{
	if(!isset($pg) || $pg<1) $pg=1;
	$rc=((($pg-1)*(12))+1);
	--$rc;
	$iTotPag=ceil($iTotRec/12);

	$lo_dis="<span class=\"pulsantiera_dis\"><</span>";
	$lf_dis="<span class=\"pulsantiera_dis\">prima</span>";
	$lt_dis="<span class=\"pulsantiera_dis\"><<</span>";
	$no_dis="<span class=\"pulsantiera_dis\">></span>";
	$nl_dis="<span class=\"pulsantiera_dis\">ultima</span>";
	$nt_dis="<span class=\"pulsantiera_dis\">>></span>";
	
	if($iTotPag>0 && $pg>1)
		{
		$lo="<a href=\"".$action."?pg=".($pg-1)."\" title=\"pagina precedente\"><</a>";
		$lf="<a href=\"".$action."?pg=1\" title=\"prima pagina\">prima</a>";
		if(($pg-10)>=1)
			{
			$lt="<a href=\"".$action."?pg=".($pg-10)."\" title=\"indietro di 10 pagine\"><<</a>";
			}
		}
	if($iTotPag>=1) $sCentrale="<span class=\"f_bblue\">$pg</span><span class=\"f_blue\"> di $iTotPag</span>";
	if($iTotRec==0)
		{
		$sCentrale="&nbsp;";
		}	
	if($pg<$iTotPag)
		{
		$no="<a href=\"".$action."?pg=".($pg+1)."\" title=\"pagina successiva\">></a>";
		$nl="<a href=\"".$action."?pg=$iTotPag\" title=\"ultima pagina\">ultima</a>";
		if(($iTotPag-$pg)>=10)
			{
			$nt="<a href=\"".$action."?idm=".($pg+10)."\" title=\"avanti di 10 pagine\">>></a>";
			} 
		}
	?>
	<ul>
	<li><? if(isset($lf)) echo $lf;  else echo $lf_dis; ?></li>
	<li><? if(isset($lt)) echo $lt;  else echo $lt_dis; ?></li>
	<li><? if(isset($lo)) echo $lo;  else echo $lo_dis; ?></li>
	<li><? echo $sCentrale; ?></li>
	<li><? if(isset($no)) echo $no;  else echo $no_dis; ?></li>
	<li><? if(isset($nt)) echo $nt;  else echo $nt_dis; ?></li>
	<li><? if(isset($nl)) echo $nl;  else echo $nl_dis; ?></li>
	</ul>
	<?
	return $rc;
	}
function mkPulsantiRwFiltrati($iTotRec,$rc,$limit,$rsFoto,$action,$aFiltro)
	{
	if(!isset($rc)) $rc=0;
	$iTotPag=ceil($iTotRec/$limit);
	$lo_dis="<span class=\"pulsantiera_dis\"><</span>";
	$lf_dis="<span class=\"pulsantiera_dis\">prima</span>";
	$lt_dis="<span class=\"pulsantiera_dis\"><<</span>";
	$no_dis="<span class=\"pulsantiera_dis\">></span>";
	$nl_dis="<span class=\"pulsantiera_dis\">ultima</span>";
	$nt_dis="<span class=\"pulsantiera_dis\">>></span>";
	
	if($iTotPag>0 && $rc>0)
		{
		$lo="<a href=\"".$action.mk_fhref($aFiltro,'idm='.dbResult($rsFoto,($rc-1),'id_documento'))."\" title=\"pagina precedente\"><</a>";
		$lf="<a href=\"".$action.mk_fhref($aFiltro,'idm='.dbResult($rsFoto,0,'id_documento'))."\" title=\"prima pagina\">prima</a>";
		if(($rc-10)>=0)
			{
			$lt="<a href=\"".$action.mk_fhref($aFiltro,'idm='.dbResult($rsFoto,($rc-10),'id_documento'))."\" title=\"indietro di 10 pagine\"><<</a>";
			}
		}
	if ($iTotRec>=1) $sCentrale="<span class=\"f_bblue\">".($rc+1)."</span><span class=\"f_blue\"> di $iTotRec</span>";
	if($iTotRec==0)
		{
		$sCentrale="&nbsp;";
		}	
	if ($rc<($iTotRec-1))
		{
		$no="<a href=\"".$action.mk_fhref($aFiltro,'idm='.dbResult($rsFoto,($rc+1),'id_documento'))."\" title=\"pagina successiva\">></a>";
		$nl="<a href=\"".$action.mk_fhref($aFiltro,'idm='.dbResult($rsFoto,($iTotRec-1),'id_documento'))."\" title=\"ultima pagina\">ultima</a>";
		if(($iTotRec-$rc)>10)
			{
			$nt="<a href=\"".$action.mk_fhref($aFiltro,'idm='.dbResult($rsFoto,($rc+10),'id_documento'))."\" title=\"avanti di 10 pagine\">>></a>";
			} 
		}
	?>
	<ul>
	<li><? if(isset($lf)) echo $lf;  else echo $lf_dis; ?></li>
	<li><? if(isset($lt)) echo $lt;  else echo $lt_dis; ?></li>
	<li><? if(isset($lo)) echo $lo;  else echo $lo_dis; ?></li>
	<li><? echo $sCentrale; ?></li>
	<li><? if(isset($no)) echo $no;  else echo $no_dis; ?></li>
	<li><? if(isset($nt)) echo $nt;  else echo $nt_dis; ?></li>
	<li><? if(isset($nl)) echo $nl;  else echo $nl_dis; ?></li>
	</ul>
<?
	}


function mkPulsantiAlbumfiltrati($iTotRec,$pg,$limit,$rsFoto,$action,$aFiltro)
	{
	if(!isset($pg) || $pg<1) $pg=1;
	$rc=((($pg-1)*($limit))+1);
	--$rc;
	$iTotPag=ceil($iTotRec/$limit);

	$lo_dis="<span class=\"pulsantiera_dis\"><</span>";
	$lf_dis="<span class=\"pulsantiera_dis\">prima</span>";
	$lt_dis="<span class=\"pulsantiera_dis\"><<</span>";
	$no_dis="<span class=\"pulsantiera_dis\">></span>";
	$nl_dis="<span class=\"pulsantiera_dis\">ultima</span>";
	$nt_dis="<span class=\"pulsantiera_dis\">>></span>";
	
	//echo "ciao".mk_fhref($aFiltro,'pg='.($pg-1));
	if($iTotPag>0 && $pg>1)
		{
		
		// "pg=".($pg-1)
		
		
		$lo="<a href=\"".$action.mk_fhref($aFiltro,'pg='.($pg-1))."\" title=\"pagina precedente\"><</a>";
		$lf="<a href=\"".$action.mk_fhref($aFiltro,'pg=1')."\" title=\"prima pagina\">prima</a>";
		if(($pg-10)>=1)
			{
			$lt="<a href=\"".$action.mk_fhref($aFiltro,'pg='.($pg-10))."\" title=\"indietro di 10 pagine\"><<</a>";
			}
		}
	if($iTotPag>=1) $sCentrale="<span class=\"f_bblue\">$pg</span><span class=\"f_blue\"> di $iTotPag</span>";
	if($iTotRec==0)
		{
		$sCentrale="&nbsp;";
		}	
	if($pg<$iTotPag)
		{
		$no="<a href=\"".$action.mk_fhref($aFiltro,'pg='.($pg+1))."\" title=\"pagina successiva\">></a>";
		$nl="<a href=\"".$action.mk_fhref($aFiltro,'pg='.$iTotPag)."\" title=\"ultima pagina\">ultima</a>";
		if(($iTotPag-$pg)>=10)
			{
			$nt="<a href=\"".$action.mk_fhref($aFiltro,'pg='.($pg+10))."\" title=\"avanti di 10 pagine\">>></a>";
			} 
		}
	?>
	<ul>
	<li><? if(isset($lf)) echo $lf;  else echo $lf_dis; ?></li>
	<li><? if(isset($lt)) echo $lt;  else echo $lt_dis; ?></li>
	<li><? if(isset($lo)) echo $lo;  else echo $lo_dis; ?></li>
	<li><? echo $sCentrale; ?></li>
	<li><? if(isset($no)) echo $no;  else echo $no_dis; ?></li>
	<li><? if(isset($nt)) echo $nt;  else echo $nt_dis; ?></li>
	<li><? if(isset($nl)) echo $nl;  else echo $nl_dis; ?></li>
	</ul>
	<?
	return $rc;
	}
function getImgrew($rs,$id,$aCampi)
	{//(tot_rec, this_rec, path, titolo, descrizione)
	$aImg[0]=mysql_num_rows($rs);
	if($aImg[0]>0)
		{
		if(isset($id))
			{
			for($i=0; $i<$aImg[0]; $i++)
				{
				if(dbResult($rs,$i,'id_documento')==$id)
					{
					$aImg[1]=$i;
					break;
					}
				}
			}
		else $aImg[1]=0;
		for($i=0;$i<count($aCampi); $i++)
			{
			$aImg[$aCampi[$i]]=dbResult($rs,$aImg[1],$aCampi[$i]);
			}
			/*
		$aImg[2]=dbResult($rs,$aImg[1],'webpath');
		$aImg[3]=dbResult($rs,$aImg[1],'titolo_ita');
		$aImg[4]=dbResult($rs,$aImg[1],'descrizione_ita');
		$aImg[5]=dbResult($rs,$i,'id_documento');
		$aImg[6]=dbResult($rs,$aImg[1],'abilitata');
		$aImg['msg_notifica']=dbResult($rs,$aImg[1],'msg_notifica');
		$aImg['username']=dbResult($rs,$aImg[1],'USER');
		$aImg['nome']=dbResult($rs,$aImg[1],'NOME');
		$aImg['cognome']=dbResult($rs,$aImg[1],'COGNOME');
		$aImg['email']=dbResult($rs,$aImg[1],'EMAIL');
		*/
		}
	return $aImg;
	}
function getImgrew_old($rs,$id)
	{//(tot_rec, this_rec, path, titolo, descrizione)
	$aImg[0]=mysql_num_rows($rs);
	if($aImg[0]>0)
		{
		if(isset($id))
			{
			for($i=0; $i<$aImg[0]; $i++)
				{
				if(dbResult($rs,$i,'id_documento')==$id)
					{
					$aImg[1]=$i;
					break;
					}
				}
			}
		else $aImg[1]=0;
		$aImg[2]=dbResult($rs,$aImg[1],'webpath');
		$aImg[3]=dbResult($rs,$aImg[1],'titolo_ita');
		$aImg[4]=dbResult($rs,$aImg[1],'descrizione_ita');
		$aImg[5]=dbResult($rs,$i,'id_documento');
		$aImg[6]=dbResult($rs,$aImg[1],'abilitata');
		$aImg['msg_notifica']=dbResult($rs,$aImg[1],'msg_notifica');
		$aImg['username']=dbResult($rs,$aImg[1],'USER');
		$aImg['nome']=dbResult($rs,$aImg[1],'NOME');
		$aImg['cognome']=dbResult($rs,$aImg[1],'COGNOME');
		$aImg['email']=dbResult($rs,$aImg[1],'EMAIL');
		}
	return $aImg;
	}
function mkQtxt($a,$val,$aCampi)
	{
	if(!isset($a)) $a=array();
	if(strpos($val,"+")!==false )
		{
		$aVal=getArrayFromSearch($val,"+",2);
		if(count($aVal)>0)
			{
			$sSqlSearch=prepareMultiLikefromStringSearch($aCampi,$aVal,"AND","OR");
			array_push($a, $sSqlSearch);
			}
		}
	else
		{	 
		$aVal=getArrayFromSearch($val," ",2);
		if(count($aVal)>0)
			{
			$sSqlSearch=prepareMultiLikefromStringSearch($aCampi,$aVal,"OR","OR");
			array_push($a, $sSqlSearch);
			}
		}
	return $a;
	}

function mkQsimple($a,$val,$default,$campo)
	{
	if(!isset($a)) $a=array();
	if($val!=$default && $val!="")
		{
		$stringa="$campo='$val'";
		array_push($a,$stringa);
		}
	return $a;
	}
	
function mkChkQ($sVar, $lenMin, $lenMax, $sEtichetta, $aQ, $aCampi)
	{
	$aVar=chkText($sVar, $lenMin, $lenMax, true, true, $sEtichetta);
	if(strlen($aVar[1])==0) $aQ=mkQtxt($aQ, $aVar[0], $aCampi);
	return $aQ;
	}

/*
function trim_strip($a)
	{
	for($i=0; $i<count($a); $i++)
		{
		global $$a[$i];
		$svar=$a[$i];
		if(!isset($$a[$i])) 
			{
			$ret=strtoupper(strip_tags($a[$i]));
			$GLOBALS["$svar"] = $ret;
			}
		}
	}
*/
function inviaposta($mittente, $destinatario, $oggetto, $contenuto, $mime_allegato, $path_allegato)
   {
   //---CONSEGNA---
//return "";
$errno="";
$errstr="";

$puntatore_whois =  fsockopen("smtp.montegrisa.org", 25, $errno, $errstr, 50);
//$puntatore_whois =  fsockopen("smtp.thebrainproject.eu", 25, $errno, $errstr, 50);

if (!isset($puntatore_whois))
    {
    return "$errstr ($errno)";
    }
else
    {
    inviadati($puntatore_whois,"helo info_8888");
    inviadati($puntatore_whois, "mail from: $mittente");
    inviadati($puntatore_whois, "rcpt to: $destinatario");
    inviadati($puntatore_whois, "data");
  
	$boundary=md5(microtime().$destinatario.$oggetto);
	if($path_allegato)
	   {
	   $mime_boundary="==Multipart_Boundary_x{$boundary}x";
	   }
	$message="From: $mittente\r\n";
	$message.="To: $destinatario\r\n";
	$message.="Subject: $oggetto\r\n";
	$message.="MIME-Version: 1.0\r\n";
	if($path_allegato)
	   {
	   $message.="Content-Type: multipart/mixed; boundary={$mime_boundary}\r\n\r\n";
	   $message.="--$mime_boundary\r\n";
	   }
	$message.="Content-Type: text/plain; charset=iso-8859-1\r\n";
	$message.="Content-Transfer-Encoding: 7bit\r\n\r\n";
	//$contenuto=chunk_split(base64_encode($contenuto)); 

	$message.="$contenuto\r\n";
	if($path_allegato)
	   {
		$message.="--{$mime_boundary}\r\n";
		if($mime_allegato)
		   {
		   $message.="Content-Type: {$mime_allegato}\r\n";
		   }
		else
		   {
		   $message.="Content-Type: {application/octet-stream}\r\n";
		   }  
		$file_attachment_name=getFileFromPath($path_allegato);  
		$message.="Content-Disposition: attachment;\r\n filename={$file_attachment_name}\r\n";    
		$message.="Content-Transfer-Encoding: base64\r\n\r\n";
		$fp=fopen($path_allegato,'rb');
		$allegato=fread($fp,filesize($path_allegato));
		fclose($fp);
		$allegato=chunk_split(base64_encode($allegato));
		//$allegato=chunk_split(base64_encode(implode("",file($path_allegato)))); 
		$message.="$allegato\r\n";
		$message.="--$mime_boundary--\r\n";
	   }

	inviadati($puntatore_whois,"$message");
  
    inviadati($puntatore_whois, $messaggio."\r\n.\r\n\r\n");
    return "";
    }

   fclose ($puntatore_whois);
   }
   
function inviadati($puntatore_whois,$dati)
	{
	$html_output="";
	$i=0;
	fputs($puntatore_whois, "$dati\r\n");
    }
	
function prwframe($rs,$pnt)
	{
	$limit=mysql_num_rows($rs);
	$start=$pnt-4;
	$finish=$pnt+4;
	$s='<div class="prwframemain">';
	for($i=$start; $i<=$finish; $i++)
		{
		if($i==$pnt) $class="prwframeselected";
		else $class="prwframe";
		if($i>=0 && $i<$limit)
			{
			if($id=dbResult($rs,$i,'id_documento'))
				{
				$antpath=dbResult($rs,$i,'miniantpath');
				//implementare i filtri...
				$s.="<div class=\"$class\"><a href=\"mod_opere.php?idm=$id\"><img src=\"$antpath\" border=\"0\"></a></div>";
				}
			else $s.="<div class=\"$class\"></div>";
			}
		else $s.="<div class=\"$class\"></div>";
		}
	$s.='</div>';
	return $s;
	}

function salvaFileUnicoRaduni_old($id, $file, $tipo, $rec){
	$err='';
	$path=DIR_RADUNI.$rec['PATH']."/docs/";
	$pFlyer = $path.$_FILES[$file]['name'];
	
	if($upld=rs::inMatrix("SELECT * FROM documenti WHERE ID_CARTELLA='$id' AND ID_DOCTIPO='$tipo'")){
		foreach($upld as $del){
			@unlink($path.$del['FILE']);
		}
		mysql_query("DELETE FROM documenti WHERE ID_CARTELLA = '$id' AND ID_DOCTIPO='$tipo'");
	}

	if(move_uploaded_file($_FILES[$file]['tmp_name'], $pFlyer)){
		mysql_query("INSERT INTO documenti (FILE, ID_CARTELLA, ID_DOCTIPO) VALUES ('".$_FILES[$file]['name']."', '$id', '$tipo')");
		$err = "Flyer salvato";
	}
	else{
		$err = "Flyer non salvato";
	}
return $err;
}

function salvaFileUnicoClienti($id, $file, $tipo, $rec){
	$err='';
	$path=PATH_CLIENTI.$rec['PATH']."/banner/";
	if(strpos($tipo,"200")!==false)
		$_FILES[$file]['name']="200_".$_FILES[$file]['name'];
	else
		$_FILES[$file]['name']="100_".$_FILES[$file]['name'];
	$pFlyer = $path.$_FILES[$file]['name'];
	
	if($upld=rs::inMatrix("SELECT * FROM documenti WHERE ID_CARTELLA='$id' AND ID_DOCTIPO='$tipo'")){
		foreach($upld as $del){
			@unlink($path.$del['FILE']);
		}
		mysql_query("DELETE FROM documenti WHERE ID_CARTELLA = '$id' AND ID_DOCTIPO='$tipo'");
	}

	if(move_uploaded_file($_FILES[$file]['tmp_name'], $pFlyer)){
		mysql_query("INSERT INTO documenti (FILE, ID_CARTELLA, ID_DOCTIPO) VALUES ('".$_FILES[$file]['name']."', '$id', '$tipo')");
		$err = "File salvato";
	}
	else{
		$err = "File non salvato";
	}
return $err;
}

function bannerHomePage(){
	$in_home=func_num_args()>0 ? "AND IN_HOME='1'": "";
	$bannerList='';
	$rs=rs::inMatrix("SELECT
	cartella.ID_CARTELLA,
	cartella.CARTELLA,
	cartella.PATH,
	cartella.ID_CLIENTE,
	clienti.RAGIONE,
	clienti.ID_CLIENTIPO,
	documenti.FILE
	FROM
	clienti
	Left Join cartella ON clienti.ID_CLIENTE = cartella.ID_CLIENTE AND cartella.ID_CLIENTE = clienti.ID_CLIENTE
	left Join documenti ON cartella.ID_CARTELLA = documenti.ID_CARTELLA
	WHERE
	clienti.ID_CLIENTIPO <>  'CLUB' AND 
	documenti.ID_DOCTIPO =  'B200X'
	$in_home
	");
	
	
	foreach($rs as $rec){
		$bannerList.='<a href="sponsor.php?id='.$rec['ID_CLIENTE'].'" title="'.$rec['RAGIONE'].'" rel="facebox">
  <img src="'.DIR_CLIENTI.$rec['PATH']."/banner/".$rec['FILE'].'" alt="'.$rec['RAGIONE'].'" width="200" height="65" border="1" />
  </a>';
	}
	return $bannerList;
}
function txt2continua($s){
	$testo=str_replace("--CONTINUA--",'<a href="javascript:collapse2.slideit()" class="add">continua</a><div id="cat">',$s);
	return $testo!=$s ? $testo.='</div><script type="text/javascript">var collapse2=new animatedcollapse("cat", 200, true)</script>' : $s;
}
function bannerCliente($id){
$dir=rs::rec2arr("SELECT
	cartella.ID_CARTELLA,
	cartella.PATH,
	cartella.ID_CLIENTE,
	cartella.ID_CARTIPO,
	documenti.ID_DOCTIPO,
	documenti.FILE
	FROM
	cartella
	Left Join documenti ON cartella.ID_CARTELLA = documenti.ID_CARTELLA
	WHERE
	cartella.ID_CLIENTE =  '$id' AND documenti.ID_DOCTIPO =  'B200X'
	LIMIT 0, 1");

	return $path=url::urlunix(DIR_CLIENTI.$dir['PATH'].'/banner/'.$dir['FILE']);
}

function conta_in_offers($campo, $val){
	$cnt = rs::fld2var("SELECT COUNT(*) AS TOT FROM offers WHERE $campo = '$val'");
	return $cnt['TOT'];
}

?>