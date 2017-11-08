<?php
# V.0.1.8
!class_exists("io") ? require_once  (CLASSES."io.php") : NULL;
class dbio extends io
	{
		var $ext_name=NULL;
		var $a_type=array();
		var $a_name=array();
		var $a_val=array();
		var $a_aval=array();
		var $a_maxl=array();
		var $a_id=array();
		var $a_title=array();
		var $a_tabind=array();
		var $a_css=array();
		var $a_ins=array();
		var $a_src=array();
		var $a_alt=array();
		var $a_href=array();
		var $a_target=array();
		var $a_border=array();
		var $a_disabled=array();
		var $a_checked=array();
		var $a_size=array();
		var $a_rows=array();
		var $a_cols=array();
		var $a_addblank=array();
		var $a_not_null=array();
		var $a_dec=array();
		var $a_fkey=array();
		var $a_comment=array();
		var $a_sql_type=array();
		var $a_lable=array();
		var $a_js = array();
		var $a_default = array();
		
		var $primkey=NULL;
	 function __construct(){
		parent::__construct();
		}
		
	function mod($var,$str){
		foreach($this->a_name as $name){
			if(strpos($this->$name->$var,$str)!==false)
				$this->$name->$var=str_replace($str,"",$this->$name->$var);
			else
				$this->$name->$var=$this->$name->$var.$str;}
		}
	function dbset(){
		foreach($this->a_name as $name){
		//	$name=!is_null($this->ext_name) ? $name.$this->ext_name : $name; 
		//	echo $name.BR;
			if(empty($this -> a_disabled)) $this -> a_disabled = array();
			if(empty($this -> a_js)) $this -> a_js = array();
		
			$this->$name = new io();
			$this->$name->name			=$name;
			$this->$name->fkey   		=array_key_exists($name,$this->a_fkey) ?  	$this->a_fkey[$name] : $this->fkey;
			$this->$name->dec			=array_key_exists($name,$this->a_dec)  ?  	$this->a_dec[$name] : $this->dec;
			$this->$name->type			=array_key_exists($name,$this->a_type) ? $this->a_type[$name] : $this->type;
			$this->$name->val			=array_key_exists($name,$this->a_val) ? $this->a_val[$name] : $this->val;
			$this->$name->aval			=array_key_exists($name,$this->a_aval) ? $this->a_aval[$name] : $this->aval;
			$this->$name->addblank		=array_key_exists($name,$this->a_addblank) ? $this->a_addblank[$name] : $this->addblank;
			$this->$name->maxl			=array_key_exists($name,$this->a_maxl) ? $this->a_maxl[$name] : $this->maxl;
			$this->$name->id			=array_key_exists($name,$this->a_id) ? $this->a_id[$name] : $this->id;
			$this->$name->title			=array_key_exists($name,$this->a_title) ? $this->a_title[$name] : $this->title;
			$this->$name->tabind		=array_key_exists($name,$this->a_tabind) ? $this->a_tabind[$name] : $this->tabind;
			$this->$name->css			=array_key_exists($name,$this->a_css) ? $this->a_css[$name] : $this->css;
			$this->$name->ins			=array_key_exists($name,$this->a_ins) ? $this->a_ins[$name] : $this->ins;
			$this->$name->src			=array_key_exists($name,$this->a_src) ? $this->a_src[$name] : $this->src;
			$this->$name->alt			=array_key_exists($name,$this->a_alt) ? $this->a_alt[$name] : $this->alt;
			$this->$name->href			=array_key_exists($name,$this->a_href) ? $this->a_href[$name] : $this->href;
			$this->$name->target		=array_key_exists($name,$this->a_target) ? $this->a_target[$name] : $this->target;
			$this->$name->border		=array_key_exists($name,$this->a_border) ? $this->a_border[$name] : $this->border;
			$this->$name->disabled		=array_key_exists($name,$this->a_disabled) ? $this->a_disabled[$name] : $this->disabled;
			$this->$name->checked		=array_key_exists($name,$this->a_checked) ? $this->a_checked[$name] : $this->checked;
			$this->$name->size			=array_key_exists($name,$this->a_size) ? $this->a_size[$name] : $this->size;
			$this->$name->rows			=array_key_exists($name,$this->a_rows) ? $this->a_rows[$name] : $this->rows;
			$this->$name->cols			=array_key_exists($name,$this->a_cols	) ? $this->a_cols	[$name] : $this->cols	;
			$this->$name->not_null		=array_key_exists($name,$this->a_not_null) ? $this->a_not_null[$name] : $this->not_null;
			$this->$name->comment		=array_key_exists($name,$this->a_comment) ? $this->a_comment[$name] : $this->comment;
			$this->$name->sql_type		=array_key_exists($name,$this->a_sql_type) ? $this->a_sql_type[$name] : $this->sql_type;
			$this->$name->lable			=array_key_exists($name,$this->a_lable) ? $this->a_lable[$name] : $this->lable;
			$this->$name->js			=array_key_exists($name,$this->a_js) ? $this->a_js[$name] : $this->js;
			$this->$name->def			=array_key_exists($name,$this->a_default) ? $this->a_default[$name] : $this->def;
			$this->primkey				=$this->$name->fkey=="PRI" && 	is_null($this->primkey)	   ?  	$this->$name   :   $this->primkey;
			//echo "pri:".$this->$name->fkey."<br>";
			}
		}	
	} #end class	
?>