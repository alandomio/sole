<?php
class newsletter{
function __construct($id){
	$this -> tbl = new table('newsletters');
	$this -> id = $id;
	$this -> get();
	
	$this -> txtHead = 'Newsletter del '.date("d/m/Y", time());
	$this -> output = '';
}

function add_tbl($tbl, $a){ # NOME TABELLA E CAMPI EVENTUALI
	
}

function get(){
	$q = "SELECT * FROM ".$this -> tbl -> tbl." WHERE ".$this -> tbl -> nid." = '".$this -> id."'";
	$this -> data =  rs::rec2arr($q);
}

public function get_layout($layout){
	ob_start();
	include $layout;
	$this -> layout = ob_get_clean();
	$this -> output = $this -> layout;
}

function set_date($s){
	$this -> output = str_replace('<!-- DATE -->', $s, $this -> output);
}
function set_domain($s){
	$this -> output = str_replace('<!-- DOMAIN -->', $s, $this -> output);
}
function set_title($s){
	$this -> output = str_replace('<!-- TITLE -->', $s, $this -> output);
}
function set_description($s){
	$this -> output = str_replace('<!-- DESCRIPTION -->', $s, $this -> output);
}

function get_html($layout){
	$this -> get_layout($layout);
	
	$this -> set_date($this -> txtHead);
	$this -> set_domain('www'.DOMINIO);
	$this -> set_title($this -> data['TITLE_NEWSLETTER']);
	$this -> set_description($this -> data['DESCRIP_NEWSLETTER']);
	
	if(!empty($this -> data['PATH_NEWSLETTER'])){
		$nImg = $this -> data['PATH_NEWSLETTER'];
		$pImg = ABS_PATH_THU.$nImg;
		if(is_file(IMG_MAIN_WEB.$nImg)){ 
			$tg_img = '<img src="'.$pImg.'" alt="'.stringa::strcut($this -> data['DESCRIP_NEWSLETTER'], '', 160).'" />';
			$image = '<tr><td colspan="2" background="'.ABS_NEWSLETTER.'bg.png" style="background:url('.ABS_NEWSLETTER.'bg.png); padding:10px 70px 10px 70px;"><center>'.$tg_img.'</center></td></tr>';
			$this -> output = str_replace('<!-- IMAGE -->', $image, $this -> output);
		}
	}
}




}
?>