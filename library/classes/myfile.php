<?php
class myfile{
function __construct($a){
	$this -> file = FILENAME.'.php';# NOME FILE CON ESTENSIONE
	$this -> filename = FILENAME;	# NOME FILE SENZA ESTENSIONE
	$this -> files_list = array();	# ???
	$this -> id_file = '';
	$this -> add_ajax = false;
	
	/*
	 * meta tag
	 * */
	$this->title = '';
	$this->description = '';
	$this->keywords = '';
	
	$this -> system_errors = '';
	
	/*
	 * css
	 * */
	$this->aCss = array();
	$this->css = '';
	
	/*
	 * lista files e codici da includere
	 * */
	$this->js_head = array();
	$this->js_footer = array();
	$this->jsc_head = array();
	$this->jsc_footer = array();
	
	/*
	 * messaggi
	 * */
	$this->err = array();
	$this->ack = array();
	
	
	if(!empty($_REQUEST['err'])){ $this -> err[] = $_REQUEST['err']; }
	if(!empty($_REQUEST['ack'])){ $this -> ack[] = $_REQUEST['ack']; }
	
	if(!empty($a['description'])){
		$this -> set_description($a['description']);
	}
	if(!empty($a['title'])){
		$this -> set_title($a['title']);
	}
	if(!empty($a['keywords'])){
		$this -> set_keywords($a['keywords']);
	}
	$this -> get_files();
}

function get_files(){
	$q = "SELECT * FROM myfiles";
	$r = rs::inMatrix($q);
	$this -> files_list = arr::semplifica($r, 'ID_MYFILE');
}

function set_meta_from_db(){ 
	$q = "SELECT * FROM myfiles WHERE ID_MYFILE = '".$this -> file."' LIMIT 0, 1";
	$r = rs::rec2arr($q);
	
	if(!empty($r['ID_MYFILE'])){
		$this -> set_description($r['DESCRIPTION_'.LANG_DEF]);
		$this -> set_title($r['TITLE_'.LANG_DEF]);		
		$this -> id_file = $r['ID_MYFILE'];
	}
}

function set_mother_class($string){
	$this -> mother_class = ' class="'.$string.'"';
}

function set_description($string){
	$this -> description = '<meta name="description" content="'.$string.'" />';
}

function set_title($string){
	$this -> title = $string;
}

function set_keywords($string){
	$this -> keywords = '<meta name="keywords" content="'.$string.'" />';
}

function add_css($s){
	$this -> aCss[] = $s;
	$this -> set_css();
}

function set_css(){
	$this -> css = '';
	foreach($this -> aCss as $k => $style){
	$this->css .= '<link href="'.$style.'" rel="stylesheet" type="text/css" />'."\n";
	}
}

/*
 * stampa il codice html per l'inclusione dei file
 * */
function print_js($position='head', $type='file'){
	if($type == 'file'){
		
		$a= $position=='head' ? $this->js_head : $this->js_footer;
		ksort($a);
		
		foreach($a as $file){
			if( strpos($file,'<script') !== false){
				print $file."\n";
			} else {
				print '<script type="text/javascript" src="'.$file.'"></script>'."\n";
	}
	}
}
	elseif($type=='code'){
		$a= $position=='head' ? $this->jsc_head : $this->jsc_footer;
		ksort($a);

		foreach($a as $code){
			if( strpos($code,'<script') !== false){
				print $code."\n";
			} else {
				print '<script type="text/javascript">'.$code.'</script>'."\n";
	}
	}
	}
}

function catch_buffer(){
	$this -> system_errors = '';
	$level = ob_get_level();
	for($i=0; $i<=$level; $i++){
		$this -> system_errors .= ob_get_clean();
	}
	$s = trim($this -> system_errors);
	if(!empty($s)){
		$this -> system_errors = '<pre>'.$this -> system_errors.'</pre>';
	}
}

function allinea_file($aLangs){ # USATO PER ALCUNI SITI MULTILINGUA
	if(empty($this -> files_list)){
		$this -> get_files();
	}
	$phps = glob(SYSTEM_PATH.'int/*.php');
	foreach ($phps as $php){
		$a=explode('/',$php);
		$etichetta=$a[count($a)-1];
		foreach($aLangs as $k => $v){
			$dir = SYSTEM_PATH.strtolower($v);
			if(array_key_exists($etichetta, $this -> files_list)){
				$filename = $this -> files_list[$etichetta]['FILE_'.strtoupper($v)];
			}
			else{
				$filename = $etichetta;
			}
			if(!file_exists($dir.'/'.$filename)){ 
				@copy(BLOCCHI.'model_lang.php', $dir.'/'.$filename);
			}
		}
	}
}

function add_err($msg){
	$this -> err[] = $msg;
}

function add_ack($msg){
	$this -> ack[] = $msg;
}

function add_msg($a, $type){
	foreach($a as $k => $msg){
		if(!empty($msg)){
			if($type == 'err'){
				$this -> add_err($msg);
			}
			elseif($type == 'ack'){
				$this -> add_ack($msg);
			}
		}
	}
}

function print_msg($print){
	$listerr = ''; $listack = ''; $flag = false;
	if(!empty($this -> err)){
		foreach(arr::strip($this -> err) as $s){
			$listerr.='<li>'.$s.'</li>'."\n";
		}
		if(strlen($listerr)>0) { $listerr = '
		<div id="alert_msg_ko">
		<ul>'.$listerr.'</ul>
		</div>';
		$flag = true;
		}
	}
	if(!empty($this -> ack)){
		foreach(arr::strip($this -> ack) as $s){
			$listack.='<li>'.$s.'</li>'."\n";
		}
		if(strlen($listack)>0) { $listack = '
		<div id="alert_msg_ok">
		<ul>'.$listack.'</ul>
		</div>'; 
		$flag = true; }
	}	
	
	if($flag==true){
		$ret = '<div id="alert">'.$listerr.$listack.'</div>';
		if($print){ print $ret; }
		return $ret;		
	}
}

/*
 * aggiunge un nuovo gruppo
 * */
function add_js_group($groupname, $files_list, $position, $cache='on', $place='footer'){
	/*
	 * cache: on off debug
	 * */
	$a=array(
			'name'		=>	$groupname,
			'place'		=>	$place,
			'cache'		=>	$cache,
			'files'		=>	$files_list,
			'position'	=>	$position,
			);

	/*
	 * costruisce la cache e aggiunge il gruppo alla lista
	 * */
	$this->add_multi_js($a);
}

/*
 * aggiunge un file js senza metterlo in cache
 * */
function add_js($path_file, $position=false, $place='head', $type="file"){
	if($type=='file'){
		if($place=='head'){
			
			if( empty($position)){
				$position=count($this->js_head)+1000;
			}
			$this->js_head[$position]=$path_file;
			
		} else { /* footer */
			
			if( empty($position)){
				$position=count($this->js_footer)+1000;
			}
			$this->js_footer[$position]=$path_file;
		}
	}
	
	else { /* code */ 
		if($place=='head'){
			if( empty($position)){
				$position=count($this->jsc_head)+1000;
			}
			$this->jsc_head[$position]=$path_file;
			
		} else { /* footer */
			if( empty($position)){
				$position=count($this->jsc_footer)+1000;
			}
			$this->jsc_footer[$position]=$path_file;
		}
	}
	
	if( ! is_numeric($position)){
		echo BR."Insert position for <strong>".stringa::bfw($path_file, 'src="', '"').'</strong>:'.BR.'add_js($path_file, $position_number, "head / footer", "file / code" )'.BR;
	}
}

/*
 * genera i file js da mettere in cache
 * */
function add_multi_js($group){
	global $on_line;

	require_once CLASSES_PATH.'extra/class.JavaScriptPacker.php';

	$script='';

	$js_filename = $group['name'].'.js';
	$cached = JS_MAIN.'cache/'.strtolower(LANG_DEF) . '_' .$js_filename;

	/*
	 * modalità per la gestione della cache:
	 *
	 * cache=on: il file viene scritto solo se non presente a filesystem (usato per i file principali)
	 * cache=off: il file viene sovrascritto ad ogni chiamata (potrebbero esserci problemi su chiamate contemporanee multiutente)
	 * cache=debug: il file viene sempre sovrascritto sul server di sviluppo e mai su quello di produzione, a meno che non esista a filesystem
	 * */

	$generate=true;

	switch ( $group['cache'] ) {
		case 'on':
			$generate=false;
			break;
		case 'off':
			$generate=true;
			break;
		case 'debug':
			if( ! $on_line){
				$generate=true;
			} else {
				$generate=false;
			}
				
			break;
		default:
			$generate=false;
			break;
	}
	
	/*
	 * bypassa la configurazione del gruppo e forza l'aggiornamento del file
	 * */
	if( defined(USE_JSCACHE) && USE_JSCACHE=='false'){
		$generate=true;
	}

	/*
	 * se non esiste a filesystem viene sempre generato
	 * */
	if( ! is_file($cached)){
		$generate=true;
	}
	
	/*
	 * genera il file
	 * */
	if($generate) {
		/*
		 * concatena gli script
		 * */
		foreach($group['files'] as $path){
			$script .= file_get_contents($path)."\n";
		}

		/*
		 * inserisce le traduzioni per il file js
		 * */
		$script=preg_replace_callback('/@@(.+?)@@/', '__', $script);

		/*
		 * salva il file nella cartella di cache
		 * */
		$offuscamento=false;
		if ($offuscamento){
			$packer = new JavaScriptPacker($script, 'None', true, false);
			$script = $packer->pack();
		}
		/* else {
			$packed=$script;
		}*/
		file_put_contents($cached, $script);
	}
	
	if($group['place']=='footer'){
		$this->js_footer[$group['position']]=$cached;
	} else {
		$this->js_head[$group['position']]=$cached;
	}
}

}
?>