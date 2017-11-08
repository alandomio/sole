<?php
class myfile{
function __construct($a){
	$this -> file = FILENAME.'.php';# NOME FILE CON ESTENSIONE
	$this -> filename = FILENAME;	# NOME FILE SENZA ESTENSIONE
	$this -> files_list = array();	# ???
	$this -> id_file = '';
	$this -> add_ajax = false;
	
	$this -> title = '';			# SEO TITLE
	$this -> description = '';		# SEO DESCRIPTION
	$this -> keywords = '';			# SEO KEYWORDS
	
	$this -> system_errors = '';
	
	$this -> aCss = array();		# CSS
	$this -> aJsf_head = array();	# FILES JS HEAD
	$this -> aJsc_head = array();	# CODES JS HEAD
	$this -> aJsf_footer = array();	# FILES JS FOOTER
	$this -> aJsc_footer = array();	# CODES JS FOOTER
	
	$this -> css = '';				# CSS
	$this -> jsf_head ='';			# FILES JS HEAD
	$this -> jsc_head = '';			# CODES JS HEAD
	$this -> jsf_footer = '';		# FILES JS FOOTER
	$this -> jsc_footer = '';		# CODES JS FOOTER

	$this -> js_head = '';			# CODICE JS NELL'HEADER
	$this -> js_footer = '';		# CODICE JS NEL FOOTER
	
	$this -> err = array();			# MESSAGGI DI ERRORE
	$this -> ack = array();			# MESSAGGI DI AVVISO
	
	
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
	// $q = "SELECT * FROM myfiles WHERE FILE_".LANG_DEF." = '".$this -> file."' LIMIT 0, 1";
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
	$this -> css .= '<link href="'.$style.'" rel="stylesheet" type="text/css" />
';
	}
}

function add_js($s, $type, $place){
	if($type == 'file'){
		if($place == 'head'){ $this -> aJsf_head[] = $s;}
		elseif($place == 'footer'){ $this -> aJsf_footer[] = $s; }
	}
	elseif($type == 'code'){
		if($place == 'head'){ $this -> aJsc_head[] = $s;}
		elseif($place == 'footer'){ $this -> aJsc_footer[] = $s; }
	}
	$this -> set_js();
}

function set_js(){
	$this -> jsf_head = '';
	$this -> jsc_head = '';
	$this -> jsf_footer = '';
	$this -> jsc_footer = '';

	foreach($this -> aJsf_head as $k => $v){
		$this -> jsf_head .= $v.'
';
	}
	foreach($this -> aJsf_footer as $k => $v){
		$this -> jsf_footer .= $v.'
';
	}
	foreach($this -> aJsc_head as $k => $v){
		$this -> jsc_head .= $v.'
';
	}
	foreach($this -> aJsc_footer as $k => $v){
		$this -> jsc_footer .= $v.'
';
	}
	$this -> js_head = $this -> jsf_head.$this -> jsc_head;
	$this -> js_footer = $this -> jsf_footer.$this -> jsc_footer;
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

}
?>