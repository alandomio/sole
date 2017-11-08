<?php
class html{
function __construct($file){
	if(!is_file($file)){ print 'Pagina non disponibile'; exit; }
	
	$this -> model = $file;
	$this -> aBlocks = array();
	$this -> get_layout();
}

function add_block($file, $tag){
	ob_start();
	include $file;
	$this -> aBlocks[$tag] = ob_get_clean();
}


function get_layout(){
	ob_start();
	include $this -> model;
	$this -> layout = ob_get_clean();
	print $this -> output = $this -> layout;
}

function set_page(){
	$this -> set_tags($this -> aBlocks);
}

function set_tag($tag, $val){
	//echo $tag.BR;
	$this -> output = str_replace('<!-- '.$tag.' -->', $val, $this -> output);
}

function set_tags($a, $n){
	for($i = 1; $i <= $n; $i++){
		foreach($a as $tag =>  $val){
			//echo $tag.BR;
			$this -> set_tag($tag, $val);
		}
	}
}


function clean_comments(){ # STRIPPO TUTTI I COMMENTI TROVATI NELLA PAGINA
	//$this -> output = preg_replace('/\<!--(.*?)--\>/isu', '', $this -> output);
	//$this -> output = preg_replace('/\t\r?\s+/m', '', $this -> output);
	$this -> output = preg_replace("/\r?\n\s+/m", '', $this -> output);
/*	$i = 0;
	$strip = stringa::bfw2($this -> output, '<!--', '-->');
	while($strip && $i < 100){
		//echo $i.' Ocio! '.$strip.BR;
		$this -> output = str_replace('<!--'.$strip.'-->', '', $this -> output);
		$strip = stringa::bfw2($this -> output, '<!--', '-->');
		$i++;
	}*/
}

function output(){
	//$this -> clean_comments();
	print $this -> output;
}

}
?>