<?php
# V.0.1.8
include_once 'init.php';
include_once 'users_conf.php';

// Work-around for setting up a session because Flash Player doesn't send the cookies
if (!isset($_FILES["Filedata"]) || !is_uploaded_file($_FILES["Filedata"]["tmp_name"]) || $_FILES["Filedata"]["error"] != 0) {
	echo "C'&egrave; stato un problema con l'invio dell'immagine"; 
	exit(0);
} else {
	echo $id = $_POST['id_rec'];
	
	$limitedext = array("gif","jpg","png","jpeg");
	$ext = strtolower(stringa::rightfromlast($_FILES['Filedata']['name'], '.'));
	
	if(in_array($ext, $limitedext)){ # SALVATAGGIO IMMAGINE		
		$handle =opendir($scheda->img_alb_web);
		$cnt=0;
		while (false !== ($file = readdir($handle))) { 
			if ($file != "." && $file != ".." && $file != "Thumbs.db" && !is_dir($file) ){
				$app = stringa::rightfromlast($file,"_");
				$app = stringa::leftfrom($app,".");
				$cnt = $app != "" && intval($app)>$cnt ? $app : $cnt;
		}}
		$cnt+=1;
		
		$ext=stringa::rightfromlast($_FILES['Filedata']['name'],'.');
		$fileName=str_replace(" ", "_", strtolower(NOME_SITO))."_$cnt".".".$ext;
		
		$pImages = $scheda->img_alb_big.$fileName;
		$pThumbs= $scheda->img_alb_thu.$fileName;
		$pSqrs= $scheda->img_alb_sqr.$fileName;
		$pWeb= $scheda->img_alb_web.$fileName;

		# CREO LE CARTELLE SE NON ESISTONO
		new dirs($scheda->img_alb_big);
		new dirs($scheda->img_alb_thu);
		new dirs($scheda->img_alb_sqr);
		new dirs($scheda->img_alb_web);

		$titolo_file = stringa::leftfromlast($_FILES['Filedata']['name'], '.');
		ini_set('memory_limit','120M');

		$save_main = move_uploaded_file($_FILES['Filedata']['tmp_name'], $pImages) ? true : false;
		$save_web = resizeImg($pImages, $pWeb, $imgs, 90) ? true : false;
		
		//if($scheda -> thumb_format == 'square'){
		//$save_thu = mkSqrImg($pImages, $pThumbs, $thus, 100,0) ? true : false; 
		//}
		//else {
		$save_thu = resizeImg($pImages, $pThumbs, $thus, 100) ? true : false; 
		$save_sqr = mkSqrImg($pImages, $pSqrs, $sqrs, 100,0) ? true : false; 
		
		//}
		if($scheda -> elimina_originale) @unlink($pImages);

		if($save_main && $save_web && $save_thu && $save_sqr) { # CANCELLO I VECCHI FILE SE I NUOVI SONO STATI SALVATI
			mysql_query("INSERT INTO files (TYPE, TITLE, PATH) VALUES ('i','".$titolo_file."', '$fileName')");
			$new_id=mysql_insert_id();
			print $q_ext_table="INSERT INTO ".$scheda->file_table." (ID_FILE, ".$scheda->f_id.") VALUES ('$new_id', '$id')";
			mysql_query($q_ext_table);
			if($scheda -> elimina_originale) @unlink($pImages);
			echo "Immagini salvate";
		}
		
/*		
if((resizeJpg($pImages, $pWeb, $imgs, 90)) && (resizeSqrJpg($pImages, $pThumbs, $thus, 100))) {
	mysql_query("INSERT INTO files (TYPE, TITLE, PATH) VALUES ('i','".$titolo_file."', '$fileName')");
	$new_id=mysql_insert_id();
	$q_ext_table="INSERT INTO ".$scheda->file_table." (ID_FILE, ".$scheda->f_id.") VALUES ('$new_id', '$id')";
	mysql_query($q_ext_table);
	if($scheda -> elimina_originale) @unlink($pImages);
	echo "Immagini salvate";
}
*/
	}
/*	else { // SALVATAGGIO PDF, DOC, XLS
		$handle =opendir($scheda->file_atc);
		$cnt=0;
			while (false !== ($file = readdir($handle))) { 
			if ($file != "." && $file != ".." && $file != "Thumbs.db" && !is_dir($file)){
			$app = stringa::rightfromlast($file,"_");
			$app = stringa::leftfrom($app,".");
			$cnt = $app != "" && intval($app)>$cnt ? $app : $cnt;
			}}
		$cnt+=1;
		$fileName=str_replace(" ", "_", strtolower(NOME_SITO))."_$cnt".".".$ext;
		$pFile = $scheda->file_atc.$fileName;
		move_uploaded_file($_FILES['Filedata']['tmp_name'], $pFile);
		mysql_query("INSERT INTO files (TYPE, TITLE, PATH) VALUES ('f','".$_FILES['Filedata']['name']."', '$fileName')");
		$new_id=mysql_insert_id();
		mysql_query("INSERT INTO ".$scheda->file_table." (ID_FILE, ".$scheda->f_id.", ID_CLIENT) VALUES ('$new_id', '$id','".$rec_scheda['ID_CLIENT']."')");
		echo "File salvati";
	}*/
}

if(!empty($_POST['return'])){ # REDIRECT NEL CASO SUL PC CLIENT NON SIA PRESENTE FLASH
	$backUri = $_POST;
	unset($backUri['return'], $backUri['Filedata']);
	$goto = HEADER_TO.url::uri($_REQUEST['return'], $backUri);
	header($goto);
}

?>