<?php
class mytag{
// function __construct(){
// }

static function in($html, $type, $aExtra = array('id' => '', 'ecc...' => '')){
	$ret = ''; $sExtra = '';
	foreach($aExtra as $marker => $attr){
		$sExtra .= ' '.$marker.'="'.$attr.'"';
	}
	//$sExtra = stringa::togli_ultimo($sExtra);
	
	$ret = "<".$type.$sExtra.">";
	$ret .= $html;
	$ret .= "</".$type.">";
	return $ret;
}

static function tag($html, $type){
	$ret = ''; 
	$ret = "<'.$type.'>";
	$ret .= $html;
	$ret .= "</".$type.">";
	return $ret;
}

}
?>