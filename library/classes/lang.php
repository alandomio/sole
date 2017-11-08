<?php
class lang{
public function __construct($a){
	$this->langs = $a;
	$this->def = $this->get_from_session($this->langs);
	$this->table('lables');
	$this->set_constants(); # inizializza costanti di base per la lingua
	define('LANG_DEF', $this -> def);
}
function table($s){
	$this->table = $s;
	$this->f_id = 'ID_'.strtoupper(substr($s,0,strlen($s)-1));
}

function get_from_url($aLang){
	$ret = $aLang[0]; // DEFAULT LANGUAGE
	foreach($aLang as $lang){
		if(strpos(PHP_SELF,strtolower('/'.$lang.'/'))){	$ret = $lang; }
	}
	return $ret;
}

function get_from_session($aLang){
	if(array_key_exists('lang',$_SESSION) && !empty($_SESSION['lang'])){
		return strtoupper($_SESSION['lang']);
	} else {
		return $aLang[0];
	}
}

function fld($s){
	return $s.'_'.LANG_DEF;
}

function set_constants(){
	# tabella principale
	$q = "SELECT ".$this->f_id.", ".$this->def." FROM ".$this->table;
	$rs=rs::inMatrix($q);
	foreach($rs as $n => $rec){
		$var = 	strlen(trim($rec[$this->def]))>0 ? $rec[$this->def] : '<font color="red"><em>'.$rec[$this->def].'</em></font>';
		//define(strtoupper($rec['ID_LABLE']),stringa::uppercase_first($var));
		define(strtoupper($rec['ID_LABLE']), $var);
	}
	# tabella dedicata sito
	$rs=rs::inMatrix("SELECT ID_LABLESITE, ".$this->def." FROM lablesites");
	foreach($rs as $n => $rec){
		$var = 	strlen(trim($rec[$this->def]))>0 ? $rec[$this->def] : '<font color="red"><em>'.$rec[$this->def].'</em></font>';
	//	define(strtoupper($rec['ID_LABLE_SITE']), stringa::uppercase_first($var));
		define(strtoupper($rec['ID_LABLESITE']), $var);
	}
}


}
?>