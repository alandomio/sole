<?php
# V.0.1.9
# inserito l'swf_limit
#
# V.0.1.8
# proprietà files diventa array
#
# V.0.1.7	
# - aggiunta la funzione file_list per la creazione automatica della lista file nel menu di navigazione principale 
# - utilizzo del file nomeTabella_conf.php per centralizzare la configurazione di tutti i singoli file
#
# V.0.1.5
# 
#
# V.0.1.8
# - il campo PATH tabella va chiamato PATH_TABLE in modo da non confondere il nome campo immagine della tabella esterna
# - passo il percorso delle cartelle a filesystem per svincolarmi dalla cartella updl
#
# V.0.1.3
# - il nome tabella è ricavato dal nome file se non specificato nel costruttore
# - nuovi nomi file per facilitare visualizzazione a filesystem: table.php, table_c.php, table_f.php, table_u.php
# - creazione automatica dei campi, sia nella lista che nel crud
# - eliminato messaggio 'ritrasmissione dati nell'upload swf'
# - creazione automatica delle etichette delle schede (SCHEDA e ALLEGATI)
# - creazione automatica di tutti i link di navigazione inerenti alla scheda (lista, elimina ecc...)
#
# V.0.1.2
# SE ESISTE IL FILE DI CARICAMENTO MULTIPLO, SI DEDUCE CHE ESISTA LA GESTIONE MULTIPLA DI FILE

class nw{
public function __construct($table){
	$this->table = !empty($table) ? strtolower($table) : strtolower(stringa::leftfrom(FILENAME,'_'));
	$this->f_tb = strtoupper(substr($this->table,0,strlen($this->table)-1));
	$this->etichetta = strtolower($this->f_tb);
	$this->f_id = 'ID_'.$this->f_tb;
	$this->f_path = 'PATH_'.$this->f_tb;
	$this->file_table_id = 'ID_FILE';
	$this->ext_table = array();
	$this->file_table = $this->table.'_files';
	$this->atable = array($this->table);
	$this->astrip = array($this->f_id);
	$this->file_list = array();
	$this -> aFields = rs::get_fields($this -> table);
	
	$this -> table_dpt = '';
	$this -> aId_dpt = array();
	
	$this->swf_limit = 50;
	$this->many_to_many = array();
	
	$this->query_list = '';
	$this->left_join = '';
	$this->offset = 30;
	$this->files = array(); # $this->files = array('files','images'); // is_file($this->table.'_f.php') ? true : false; # 1
	$this->cnt_file = 0;
	$this->cnt_img = 0;
	$this->cnt_tot = 0;
	$check = rs::rec2arr("SELECT * FROM ".$this->table." LIMIT 0,1"); // ELENCA I CAMPI DELLA TABELLA
	$this->img = array_key_exists($this->f_path, $check) ? true : false;
	$this->thumb_size = 70;
	$this->thumb_format = 'square';
	$this -> elimina_originale = false;
	$this->action_type = 'read_write';
	$this -> mmBox = array();
	$this -> mmLable = false;
	
	$this->file_l = $this->table.'.php';	// lista
	$this->file_c = $this->table.'_c.php';	// crud
	$this->file_f = $this->table.'_f.php';	// file
	$this->file_u = $this->table.'_u.php';	// upload swf
	
	$this->personal_folders = false;
	$this -> full_path = '';
	$this->main_path = UPLD_MAIN;
	$this->file_atc = PATH_FILE;
	$this->img_main_big = IMG_MAIN_BIG;
	$this->img_main_web = IMG_MAIN_WEB;
	$this->img_alb_thu = IMG_ALB_THU;
	$this->img_alb_web = IMG_ALB_WEB;
	$this->img_alb_big = IMG_ALB_BIG;
	$this->img_alb_sqr = IMG_ALB_SQR;
	$this->img_alb_lnd = IMG_ALB_LND;
	
	$this->folder_list = array('file_atc'=>'atc', 'img_main_web'=>'m_web', 'img_main_big'=>'m_big', 'img_alb_thu'=>'thu', 'img_alb_web'=>'web','img_alb_big'=>'big','img_alb_sqr'=>'sqr','img_alb_lnd'=>'lnd');
}

function add_mm($tbl_name, $id){
	# RICAVO I NOMI TABELLA E CAMPI
	//$this -> mm[$tbl_name]['table'] = 
	$this -> mmBox[$tbl_name] = '';
	
	$tb = $this -> table.'_'.$tbl_name;
	$fn = stringa::tbl2field($tbl_name);
	$ie = stringa::tbl2id($tbl_name);
	
	$qCheck = "SELECT * FROM $tb WHERE ".$this -> f_id." = '$id'";
	$rCheck = rs::inMatrix($qCheck);
	$rCheck = arr::semplifica($rCheck, $ie);
	
	$qList = "SELECT * FROM	descriptors	WHERE
	ID_DESCRIPTORS_TYPE =  '$fn'
	ORDER BY RANK ASC, DESCRIPTOR_".LANG_DEF." ASC";
	$rList = rs::inMatrix($qList);

	foreach($rList as $k => $v){
		$in[$tbl_name] = new io();
		$in[$tbl_name] -> type = 'checkbox'; 
		$in[$tbl_name] -> lable = '<div class="lable">'.$v['DESCRIPTOR_'.LANG_DEF].'</div>'; 
		if(array_key_exists($v['ID_DESCRIPTOR'], $rCheck)) $in[$tbl_name] -> val = 1; 
		$this -> mmBox[$tbl_name] .= '<div class="cb">'.$in[$tbl_name] -> set('mm'.$tbl_name.'_'.$v['ID_DESCRIPTOR']).'</div>'; # Ottengo i checkbox
	}
	if(!empty($this -> mmBox[$tbl_name])){
		if($this -> mmLable)$this -> mmBox[$tbl_name] = '<div class="mmBox"><p>'.stringa::get_constant(stringa::tbl2field($tbl_name), true).'</p>'.$this -> mmBox[$tbl_name].'</div>';
		else $this -> mmBox[$tbl_name] = '<div class="mmBox">'.$this -> mmBox[$tbl_name].'</div>';
	}
}

function mmSyncro($id_rec, $a){
	//	$this -> mm[$tbl][$tid]['hid'] = 1;
	//	$this -> mm[$tbl][$tid]['id'] = 1;
	foreach($a as $tbl => $ids){
		foreach($ids as $id => $type){
			$t_ext = $this -> table.'_'.$tbl;;
			$i_ext = stringa::tbl2id($tbl);
			if(array_key_exists('id', $type)){ # CHECK + INSERT
				$check = "SELECT * FROM ".$t_ext." WHERE ".$this -> f_id." = '$id_rec' AND $i_ext = '$id'";
				$q = "INSERT INTO ".$t_ext." (".$this -> f_id.", $i_ext) VALUES ('$id_rec', '$id')";
				dbChkQuery($check,$q);
			}
			else{ # DELETE
				$q = "DELETE FROM ".$t_ext." WHERE ".$this -> f_id." = '$id_rec' AND $i_ext = '$id'";		
				mysql_query($q);
			}
		}	
	# RICREO I VALORI DEI CHECKBOX
		self::add_mm($tbl, $id_rec);
	}
	
	
}

public function unset_astrip($campo){
	foreach($this -> astrip as $k => $fld){
		if($fld == $campo){
			unset($this -> astrip[$k]);
		}
	}
}

public function action_type($val){
	$this->action_type = $val;
}

# MENU

public function file_list($table){
	return array($table, $table.'_c',$table.'_f',$table.'_u');
}

# DATABASE
public function ext_table($a){
	foreach($a as $table){ # chiave = exttable, valore = id tabella
		$this -> ext_table[$table] = stringa::tbl2id($table); // 'ID_'.strtoupper(substr($table,0,strlen($table)-1));
		$this -> atable[] = $table;
		$this -> astrip[] = $this -> ext_table[$table]; # STRIP ID
		$this -> aFields = array_merge($this -> aFields, rs::get_fields($table));
		$this -> aFields = arr::unset_vals($this -> aFields, array(stringa::tbl2field($table))); # STRIP CAMPO PRINCIPALE 
		//arr::stampa($this -> aFields);
	}
}

public function ext_table_to_many($a){
	foreach($a as $table => $id){ # chiave = exttable, valore = id tabella
		$this->ext_table[$table] = $id;
		$this->atable[] = $table;
		$this->astrip[] = $this->ext_table[$table]; # strippo la chiave primaria
	}
}


function descriptors($table, $aIds){
	$this -> table_dpt = $table;
	$this -> aId_dpt = $aIds;
}

public function query_list(){
	$field_list=rs::rec2arr("SELECT * FROM ".$this->table." LIMIT 0,1");
	$select='';
	foreach($field_list as $k => $v){
		$select.= $this->table.".$k,\n";
	}
	if(!empty($this->ext_table)){
		foreach($this->ext_table as $ext_table => $ext_id){
			$field_list=rs::rec2arr("SELECT * FROM ".$ext_table." LIMIT 0,1");
			foreach($field_list as $k => $v){
				$as = ($k == $this->f_id) ? ' as EXT_'.$ext_id : '';
				$select.= $ext_table.".$k$as,\n";
			}
		$this->left_join.= "\n".'Left Join '.$ext_table.' ON '.$this->table.'.'.$ext_id.' = '.$ext_table.'.'.$ext_id;
		}
	}
	if(!empty($this -> table_dpt)){
		$id_dpt = 'ID_'.strtoupper(substr($this -> table_dpt, 0, strlen($this -> table_dpt)-1));
/*		foreach($this -> aId_dpt as $k => $v){
			$this->left_join.= ' Left Join '.$this -> table_dpt.' ON '.$this->table.'.'.$v.' = '.$this -> table_dpt.'.'.$id_dpt;
		}
*/	}
		
	$select = substr($select, 0, strlen($select)-2);
	$this->query_list = "SELECT $select FROM ".$this->table.$this->left_join;
}

public function short_list($a){ # PERMETTE DI USARE QUERY CON MENO CAMPI E QUINDI PIù LEGGERE
	$field_list=rs::rec2arr("SELECT * FROM ".$this->table." LIMIT 0,1");
	$select='';
	foreach($field_list as $k => $v){
		if(in_array($k, $a))
		$select.= $this->table.".$k,\n";
	}
	if(!empty($this->ext_table)){
		foreach($this->ext_table as $ext_table => $ext_id){
			$field_list=rs::rec2arr("SELECT * FROM ".$ext_table." LIMIT 0,1");
			foreach($field_list as $k => $v){
				$as = ($k == $this->f_id) ? ' as EXT_'.$ext_id : '';
				$select.= $ext_table.".$k$as,\n";
			}
		$this->left_join.= "\n".'Left Join '.$ext_table.' ON '.$this->table.'.'.$ext_id.' = '.$ext_table.'.'.$ext_id;
		}
	}
	if(!empty($this -> table_dpt)){
		$id_dpt = 'ID_'.strtoupper(substr($this -> table_dpt, 0, strlen($this -> table_dpt)-1));
	}
		
	$select = substr($select, 0, strlen($select)-2);
	$this->query_list = "SELECT $select FROM ".$this->table.$this->left_join;
}
public function many_to_many($a){
	foreach($a as $k => $v){
		foreach($v as $k1 => $v1){ # RIPORTO I VALORI DELL'ARRAY ALL'INTERNO DELL'OGGETTO
			$this->many_to_many[$k][$k1] = $v1;
		}
		//$this->many_to_many[$k]['file'] = $k.'_ext.php';# STRUTTURA FILE
	}
}

public function many_to_many_tot(){
	foreach($this->many_to_many as $k => $v){ # ESTRAPOLO I DATI DI INTERESSE
	$where = !empty($v['flag']) ? " WHERE ".$v['flag']." = '1' ": '';
	$q = "SELECT COUNT(".$this->f_id.") as TOT FROM $k";		
	$exec = rs::rec2arr($q);
	$this->many_to_many[$k]['tot'] = $exec['TOT']; # RECORD TOTALI
	
	$q = "SELECT COUNT(".$this->f_id.") as TOT FROM $k $where";
	$exec = rs::rec2arr($q);
	$this->many_to_many[$k]['sub'] = $exec['TOT']; # RECORD SUBTOTALI
	
	$where = !empty($v['ext_flag']) ? " WHERE ".$v['ext_flag']." = '1' ": '';
	$q = "SELECT COUNT(".$v['id'].") as TOT FROM ".$v['ext']." $where";
	$exec = rs::rec2arr($q);
	$this->many_to_many[$k]['tot_ext'] = $exec['TOT']; # TOTALI RECORD TABELLA ESTERNA
	}
}

public function update_ext($aa, $ck, $id){
	$added = 0;	$removed = 0; $check = array();
	foreach($this->ext_table as $k => $v){ # GESTIAMO UNA SOLA TABELLA ESTERNA
		$ext_table = $k;
	}
	//mysql_query("DELETE FROM $ext_table WHERE ".$this->many_to_many[$ext_table]['id']." = '0'"); # ELIMINAZIONE RECORD ORFANI E/O DERIVANTI DA ERRORI
	$q="SELECT * FROM $ext_table WHERE ".$this->many_to_many[$ext_table]['id']." = '$id'";
	$rec = rs::inMatrix($q);
	foreach($rec as $k => $v){ # SEMPLIFICAZIONE ARRAY
		$check[$v[$this->f_id]] = $v[$this->many_to_many[$ext_table]['flag']];
	}
	
	foreach($aa as $k => $id_val){
		if(in_array($id_val,$ck) && !array_key_exists($id_val, $check)){ /*print $id_val.'<br />';*/ # INSERT
			if(mysql_query("INSERT INTO $ext_table (".$this->many_to_many[$ext_table]['id'].", ".$this->f_id.") VALUES ('$id', '$id_val')")) {
			$added++;
			}
		}

		elseif(!in_array($id_val, $ck) && array_key_exists($id_val,$check) && $check[$id_val][$this->many_to_many[$ext_table]['flag']]!=1){ # DELETE
			if(mysql_query("DELETE FROM $ext_table WHERE ".$this->many_to_many[$ext_table]['id']." = '$id' AND ".$this->f_id." = '".$id_val."'")) {
				$removed++;
			}
		}
	}
	return "aggiunte $added, rimosse $removed";
}

function allinea_dizionario($id, $rs){ 
	$qDel = "DELETE FROM dizionarios WHERE ID_ARTICLE = '$id'";
	mysql_query($qDel);
	$aParole = array();
	foreach($rs as $k => $v){
		//print $k.' '.$v.BR;
		$str = io::lable($rs, $k, false, 0);
		$a = stringa::prepara_dizionario($str);
		
		foreach($a as $kk => $parola){
			if(strlen($parola)>2){
				$qIns = "INSERT INTO dizionarios (PAROLA_CHIAVE, CAMPO_PROVENIENZA, ID_ARTICLE) VALUES ('".prepare4sql($parola)."', '".$k."', '".$id."')";
				mysql_query($qIns);
			}
		}
	}
}

# OPERAZIONI CON I FILE
public function files($val){ # true o false
	$this->files = $val; 
}

public function set_main_path($path){
	$this->main_path = $path;
	if(substr($this->main_path,strlen($this->main_path)-1,strlen($this->main_path))!='/') $this->main_path.='/';
}

public function set_upld_dirs($path){
	if(substr($path,strlen($path)-1,strlen($path))!='/') $path.='/';
	foreach($this->folder_list as $k => $v){
		$this->$k = $this->main_path.$path.$v.'/';
		//print $k.' '.$this -> $k.BR;
	}
}

function dele_main($id){
	if($this->personal_folders == true){
		$rec=rs::rec2arr("SELECT * FROM ".$this->table." WHERE ".$this->f_id."='$id'");
		$this->set_upld_dirs($rec['FOLDER_'.$this -> f_tb]);
	}
	$this->dele_img($id); # CANCELLA FILE SINGOLO
	if($this->files) $this->dele_files($id); # CANCELLA ALBUM E ALLEGATI

	if($this->personal_folders == true){
		$this->dele_personal_dirs($rec['FOLDER_'.$this -> f_tb]);
	}
	if(mysql_query("DELETE FROM ".$this->table." WHERE ".$this->f_id."='$id'")) return true;
	else return false;
}


function elimina_allegati($id){
	if($this->personal_folders == true){
		$rec=rs::rec2arr("SELECT * FROM ".$this->table." WHERE ".$this->f_id."='$id'");
		$this->set_upld_dirs($rec['FOLDER_'.$this -> f_tb]);
	}
	$this->dele_img($id); # CANCELLA FILE SINGOLO
	if($this->files) $this->dele_files($id); # CANCELLA ALBUM E ALLEGATI

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

function delete_files_and_record($id, $folder){
	if($this->personal_folders == true){
		$this->set_upld_dirs($folder);
	}
	$this->dele_img($id); # CANCELLA FILE SINGOLO
	if($this->files) $this->dele_files($id); # CANCELLA ALBUM E ALLEGATI
	if(mysql_query("DELETE FROM ".$this->table." WHERE ".$this->f_id."='$id'")) return true;
	else return false;
}


function dele_record($id){ # CANCELLAZIONE RECORD SEMPLICE
	if(mysql_query("DELETE FROM ".$this->table." WHERE ".$this->f_id."='$id'")) return 1;
	else return false;
}


/*function dele_personal_dirs($dir){
	foreach($this->folder_list as $k => $v){
		if(is_dir($this->main_path.$dir.'/'.$v)) rmdir($this->main_path.$dir.'/'.$v);
	}
	if(is_dir($this->main_path.$dir) && strlen($dir)>0) rmdir($this->main_path.$dir);
}*/

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

/*function dele_files($id){ # CANCELLA ALBUM E ALLEGATI RIDONDO LA PRESENZA DEL NOME CARTELLA ANCHE IN files
	$cnt_dele_files = 0;
	
	$rec=rs::rec2arr("SELECT * FROM ".$this->table." WHERE ".$this->f_id."='$id'");
	
	$qAll = "SELECT files.ID_FILE, files.FOLDER_FILE, files.TYPE, files.TITLE, files.DESCRIP, files.PATH, ".$this->file_table.".".$this->f_id." FROM ".$this->file_table." Left Join files ON ".$this->file_table.".ID_FILE = files.ID_FILE WHERE ".$this->file_table.".".$this->f_id." = '$id'";
	
	$aFiles = rs::inMatrix($qAll);

	foreach($aFiles as $file){
		foreach($this->folder_list as $k => $v){
			if($k=='img_main_big' || $k=='img_main_web') continue;
			
			//echo $this->$k.$file['PATH'].BR;
			if(is_file($this->$k.$file['PATH'])){
				if(unlink($this->$k.$file['PATH'])){
					$q_mul = "DELETE FROM ".$this->file_table." WHERE ID_FILE = '".$file['ID_FILE']."'";
					$q_fil = "DELETE FROM files WHERE ID_FILE = '".$file['ID_FILE']."'";
					if(mysql_query($q_mul) && mysql_query($q_fil)) $cnt_dele_files++;
				}
			}
		}
	}
	return $cnt_dele_files;
}
*/
function dele_files($id){ # CANCELLA ALBUM E ALLEGATI RIDONDO LA PRESENZA DEL NOME CARTELLA ANCHE IN files
	$cnt_dele_files = 0;
	$rec=rs::rec2arr("SELECT * FROM ".$this->table." WHERE ".$this->f_id."='$id'");
	$aFiles = rs::inMatrix("SELECT files.ID_FILE, files.FOLDER_FILE, files.TYPE, files.TITLE, files.DESCRIP, files.PATH, ".$this->file_table.".".$this->f_id." FROM ".$this->file_table." Left Join files ON ".$this->file_table.".ID_FILE = files.ID_FILE WHERE ".$this->file_table.".".$this->f_id." = '$id'");

	foreach($aFiles as $file){
		foreach($this->folder_list as $k => $v){
			if($k=='img_main_big' || $k=='img_main_web') continue;
			if(is_file($this->$k.$file['PATH'])){
				if(unlink($this->$k.$file['PATH'])){
					$q_mul = "DELETE FROM ".$this->file_table." WHERE ID_FILE = '".$file['ID_FILE']."'";
					$q_fil = "DELETE FROM files WHERE ID_FILE = '".$file['ID_FILE']."'";
					if(mysql_query($q_mul) && mysql_query($q_fil)) $cnt_dele_files++;
				}
			}
		}
	}
	return $cnt_dele_files;
}

function dele_record_e_file($id){
	$ack_fil = 0; $ack_rec = 0;
	$ack_fil = $this -> dele_files($id);
	$ack_rec = $this -> dele_record($id);
	return array($ack_fil, $ack_rec);
}

function cnt_files($id){
	$cnt_file=rs::rec2arr("SELECT COUNT(files.ID_FILE) AS TOT, files.TYPE, ".$this->file_table.".".$this->f_id." FROM ".$this->file_table." Left Join files ON ".$this->file_table.".ID_FILE = files.ID_FILE WHERE ".$this->file_table.".".$this->f_id." = '$id' AND files.TYPE = 'f' GROUP BY files.TYPE");
	$aCntFile['f'] = !empty($cnt_file['TOT']) ? $cnt_file['TOT'] : '0';
	$cnt_img=rs::rec2arr("SELECT COUNT(files.ID_FILE) AS TOT, files.TYPE, ".$this->file_table.".".$this->f_id." FROM ".$this->file_table." Left Join files ON ".$this->file_table.".ID_FILE = files.ID_FILE WHERE ".$this->file_table.".".$this->f_id." = '$id' AND files.TYPE = 'i' GROUP BY files.TYPE");
	$aCntFile['i'] = !empty($cnt_img['TOT']) ? $cnt_img['TOT'] : '0';
	$aCntFile['t'] = $aCntFile['f']+$aCntFile['i'];
	return $aCntFile;
}

function files_list($id, $type, $folder){
	if(substr($folder, strlen($folder)-1, strlen($folder)) == '/'){ $folder = stringa::togli_ultimo($folder); }
	
	$altimg = new myimage('');
	$altimg -> set_alt('No pic');
	
	# DEFAULT
	$aRet = array();
	$aRet[0]['path'] = '';
	$aRet[0]['alt'] = 'No pic';
	$aRet[0]['obj_img'] = $altimg;
	$aRet[0]['img'] = $altimg -> html;

	$q = "SELECT
	".$this->file_table.".".$this->f_id.",
	".$this->file_table.".IS_PRINCIPALE,
	files.PATH,
	files.TITLE,
	files.TYPE
	FROM
	".$this->file_table."
	Left Join files ON ".$this->file_table.".ID_FILE = files.ID_FILE
	WHERE files.TYPE = '$type' AND
	".$this->file_table.".".$this -> f_id."='$id'
	ORDER BY ".$this->file_table.".IS_PRINCIPALE DESC
	";
	$r = rs::inMatrix($q);
	$cnt = 0;
	if(!empty($r)){
		foreach($r as $k => $v){
			$path_img = $folder.'/'.$v['PATH'];
			if($type == 'i'){
				$img = new myimage($path_img);
				$img -> set_alt($v['TITLE']);
				$aRet[$cnt]['obj_img'] = $img;
				$aRet[$cnt]['img'] = $img -> html;
			}
			$aRet[$cnt]['path'] = $path_img;
			$aRet[$cnt]['filename'] = $v['PATH'];
			$aRet[$cnt]['alt'] = $v['TITLE'];
			$cnt ++;
		}
	}
	return $aRet;
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
					elseif($this->thumb_format=='width'){
						//$save_thu = resizeImg($this->img_main_big.$imgName, $this->img_main_web.$imgName, $this->thumb_size, 85) ? true : false;
						$save_thu = resizeImgBySide($this->img_main_big.$imgName, $this->img_main_web.$imgName, $this->thumb_size, 85, $mode = 'width') ? true : false;
					}
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

}
?>