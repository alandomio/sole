<?php
# V.0.1.10
class files{
public function __construct(){

	$this->main_path = UPLD_MAIN;
	$this->file_atc = PATH_FILE;
	$this->img_main_big = IMG_MAIN_BIG;
	$this->img_main_web = IMG_MAIN_WEB;
	$this->img_alb_thu = IMG_ALB_THU;
	$this->img_alb_web = IMG_ALB_WEB;
	$this->img_alb_big = IMG_ALB_BIG;
	
	$this->folder_list = array('file_atc'=>'atc', 'img_main_web'=>'m_web', 'img_main_big'=>'m_big', 'img_alb_thu'=>'thu', 'img_alb_web'=>'web','img_alb_big'=>'big');
}

public function set_main_path($path){
	$this->main_path = $path;
	if(substr($this->main_path,strlen($this->main_path)-1,strlen($this->main_path))!='/') $this->main_path.='/';
}

public function set_dirs($path){
	if(substr($path,strlen($path)-1,strlen($path))!='/') $path.='/';
	foreach($this->folder_list as $k => $v){
		$this->$k = $this->main_path.$path.$v.'/';
	}
}
/*
function dele_main($id){
	if($this->personal_folders == true){
		$rec=rs::rec2arr("SELECT * FROM ".$this->table." WHERE ".$this->f_id."='$id'");
		$this->set_upld_dirs($rec['FOLDER']);
	}
	$this->dele_img($id); # CANCELLA FILE SINGOLO
	if($this->files) $this->dele_files($id); # CANCELLA ALBUM E ALLEGATI

	if($this->personal_folders == true){
		$this->dele_personal_dirs($rec['FOLDER']);
	}
	if(mysql_query("DELETE FROM ".$this->table." WHERE ".$this->f_id."='$id'")) return true;
	else return false;
}

function dele_main_folder($id, $folder){
	if($this->personal_folders == true){
		$this->set_upld_dirs($folder);
	}
	$this->dele_img($id); # CANCELLA FILE SINGOLO
	if($this->files) $this->dele_files($id); # CANCELLA ALBUM E ALLEGATI

	if($this->personal_folders == true){
		$this->dele_personal_dirs($folder);
	}
	if(mysql_query("DELETE FROM ".$this->table." WHERE ".$this->f_id."='$id'")) return true;
	else return false;
}


function dele_personal_dirs($dir){
	foreach($this->folder_list as $k => $v){
		if(is_dir($this->main_path.$dir.'/'.$v)) rmdir($this->main_path.$dir.'/'.$v);
	}
	if(is_dir($this->main_path.$dir) && strlen($dir)>0) rmdir($this->main_path.$dir);
}

function dele_img($id){ # CANCELLA FILE SINGOLO
	if($this->img){
		$rec=rs::rec2arr("SELECT * FROM ".$this->table." WHERE ".$this->f_id."='$id'");
		$file=$rec[$this->f_path];
		if(is_file($this->img_main_web.$file)) unlink($this->img_main_web.$file);
		if(is_file($this->img_main_big.$file)) unlink($this->img_main_big.$file);
		mysql_query("UPDATE ".$this->table." SET ".$this->f_path."=NULL WHERE ".$this->f_id."='$id'");
	}
}


function dele_files($id){ # CANCELLA ALBUM E ALLEGATI
	$rec=rs::rec2arr("SELECT * FROM ".$this->table." WHERE ".$this->f_id."='$id'");
$aFiles = rs::inMatrix("SELECT files.ID_FILE, files.TYPE, files.TITLE, files.DESCRIP, files.PATH, ".$this->file_table.".".$this->f_id." FROM ".$this->file_table." Left Join files ON ".$this->file_table.".ID_FILE = files.ID_FILE WHERE ".$this->file_table.".".$this->f_id." = '$id'");

	foreach($aFiles as $file){
		foreach($this->folder_list as $k => $v){
			if($k=='img_main_big' || $k=='img_main_web') continue;
			if(is_file($this->$k.$file['PATH'])) unlink($this->$k.$file['PATH']);
		}
		mysql_query("DELETE FROM ".$this->file_table." WHERE ID_FILE = '".$file['ID_FILE']."'");
		mysql_query("DELETE FROM files WHERE ID_FILE = '".$file['ID_FILE']."'");
	}
}

function cnt_files($id){
	$cnt_file=rs::rec2arr("SELECT COUNT(files.ID_FILE) AS TOT, files.TYPE, ".$this->file_table.".".$this->f_id." FROM ".$this->file_table." Left Join files ON ".$this->file_table.".ID_FILE = files.ID_FILE WHERE ".$this->file_table.".".$this->f_id." = '$id' AND files.TYPE = 'f' GROUP BY files.TYPE");
	$aCntFile['f'] = !empty($cnt_file['TOT']) ? $cnt_file['TOT'] : '0';
	$cnt_img=rs::rec2arr("SELECT COUNT(files.ID_FILE) AS TOT, files.TYPE, ".$this->file_table.".".$this->f_id." FROM ".$this->file_table." Left Join files ON ".$this->file_table.".ID_FILE = files.ID_FILE WHERE ".$this->file_table.".".$this->f_id." = '$id' AND files.TYPE = 'i' GROUP BY files.TYPE");
	$aCntFile['i'] = !empty($cnt_img['TOT']) ? $cnt_img['TOT'] : '0';
	$aCntFile['t'] = $aCntFile['f']+$aCntFile['i'];
	return $aCntFile;
}

function main_img($id){
	if($this->img){
		if(is_file($_FILES['nw_img']['tmp_name'])){ // INSERIMENTO/SOSTITUZIONE/ELIMINAZIONE IMMAGINE PRINCIPALE
			$aJpgErr = chkjpg($_FILES['nw_img'], (1.5*1048576));
			if(empty($aJpgErr)){
				# estensione
				$limitedext = array("gif","jpg","png","jpeg");
				$ext=strtolower(stringa::rightfromlast($_FILES['nw_img']['name'],'.'));
				if(in_array($ext, $limitedext)){
					ini_set('memory_limit','120M');
					$imgName = date('YmdHis',time()).".$ext";
					$save_main = move_uploaded_file($_FILES['nw_img']['tmp_name'],$this->img_main_big.$imgName) ? true : false;
					if($this->thumb_format=='square'){
					$save_thu = mkSqrImg($this->img_main_big.$imgName, $this->img_main_web.$imgName, $this->thumb_size, 85,0) ? true : false; }
					else {
					$save_thu = resizeImg($this->img_main_big.$imgName, $this->img_main_web.$imgName, $this->thumb_size, 85) ? true : false; }
					
					if($save_main && $save_thu) { # CANCELLO I VECCHI FILE SE I NUOVI SONO STATI SALVATI
						$dele = rs::rec2arr("SELECT * FROM ".$this->table." WHERE ".$this->f_id."='$id'");
						if(is_file($this->img_main_web.$dele[$this->f_path])) unlink($this->img_main_web.$dele[$this->f_path]);
						if(is_file($this->img_main_big.$dele[$this->f_path])) unlink($this->img_main_big.$dele[$this->f_path]);
						mysql_query("UPDATE ".$this->table." SET ".$this->f_path." = '$imgName' WHERE ".$this->f_id." = '$id'");
					}
					else{ # ELIMINO I POSSIBILI FILE SALVATI A FILESYSTEM
						if(is_file($this->img_main_big.$imgName)) unlink($this->img_main_big.$imgName);
						if(is_file($this->img_main_web.$imgName)) unlink($this->img_main_web.$imgName);
					}
				}
			}
		}
	}
}

*/

}
?>