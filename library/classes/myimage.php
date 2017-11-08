<?php
# V.0.1.8
class myimage{
function __construct($path){
	if(empty($path) || !is_file($path)) { $path = ALT_IMG; }
	$this -> path = $path;
	$this -> width = '';
	$this -> height = '';
	$this -> type = '';
	$this -> attr = '';
	$this -> html = '';
	$this -> abs_path = '';	
	$this -> get($this -> path);
	$this -> aInfo = $this -> get($this -> path);
	$this -> alt = '';
	$this -> id = '';
	$this -> css = '';
	$this -> get_html();
}

function get($path){
	$a = array();
	list($a['width'], $a['height'], $a['type'], $a['attr']) = getimagesize($path);
	$this -> width = $a['width'];
	$this -> height = $a['height'];
	$this -> type = $a['type'];
	$this -> attr = $a['attr'];
	$this -> get_html();
	return $a;
}

function get_html(){
	$attr = !empty($this -> attr) ? ' '.$this -> attr : '';
	$alt = !empty($this -> alt) ? ' alt="'.$this -> alt.'"' : '';
	$id = !empty($this -> id) ? ' id="'.$this -> id.'"' : '';
	$css = !empty($this -> css) ? ' class="'.$this -> css.'"' : '';
	$this -> html = '<img src="'.$this -> abs_path.$this -> path.'"'.$attr.$alt.$css.$id.' />';
}

function set_alt($alt){
	$this -> alt = $alt;
	$this -> get_html();
}

function set_attr($w, $h){
	$html_w = ''; $html_h = '';
	$this -> attr = '';
	$this -> width = '';
	$this -> height = '';
	if(!empty($w)) { $this -> width = $w; $html_w = 'width="'.$w.'"'; }
	if(!empty($h)) { $this -> height = $h; $html_h = 'height="'.$h.'"';}
	$this -> attr = $html_w.' '.$html_h;
	$this -> get_html();
}

function set_id($id){
	$this -> id = $id;
	$this -> get_html();
}

function set_css($css){
	$this -> css = $css;
	$this -> get_html();
}

function set_abs_path($s){
	$this -> abs_path = stringa::mk_http($s);
	$this -> get_html();
}

}
?>