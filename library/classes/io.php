<?php
class io{
	var $html="";
	var $type="lable";
	var $name="";
	var $val="";
	var $def="";
	var $aval=array();
	var $maxl="";
	var $dec="";
	var $id="";
	var $js = '';
	var $title="";
	var $tabind="";
	var $css="";
	var $ins="";
	var $src="";
	var $alt="";
	var $dbtable="";
	var $dbkeyfld="";
	var $dbkeyval="";
	var $href="";
	var $target="";
	var $border="";
	var $disabled="";
	var $readonly="";
	var $checked="";
	var $size="";
	var $rows="";
	var $cols="";
	var $addblank=0;
	var $not_null=0;
	var $fkey="";
	var $comment="";
	var $sql_type="";
	var $txtblank="- Scegli -";
	var $ekonull = "";
	var $lable;
	var $skip=0;
	var $placeholder='';

function __construct(){
}
	
function add($var,$str){
	$this->$var=$this->$var.$str;
}
	
function strip($var,$str){
	$this->$var=str_replace($str,"",$this->$var);
}
	
function ekonull(){
	return $this->not_null==1 ? $this->ekonull :  "";
}
	
function css($fld, $style){
	if(!empty($this -> $fld -> css)){
		$this -> $fld -> css .= ' '.$style; 
	}
	else $this -> $fld -> css = $style;
	
	return $this -> $fld -> css;
}
	

function get(){
	if($this->skip==0)
		print(func_num_args()>0 ? self::set(func_get_arg(0)) : self::set());
}

function normalize(){ # ALLINEA LA SINTASSI DI ALCUNI CAMPI PARTICOLARI
	$ret = false; # DIVENTA IL VALORE PER L'INSERT O PER L'UPDATE
	if($this -> type == 'ddmm4y'){
		$val = trim($this -> val);
		if(strlen($val) == 10){ # USO DATA IN CAMPO TESTO UNICO, PER ESEMPIO CON UN DATEPICKER
			$ad = explode('/', $val);
			if(count($ad) == 3){ # HO TRE CAMPI DATA
				//$this -> name.BR;
				$ret = $ad[2].$ad[1].$ad[0];
			}
		}	
	}
	echo 'normalize';
	return $ret;
}

function set(){
	$name=func_num_args()>0 ? func_get_arg(0) : $this->name;
	$this->name=empty($this->name) || func_num_args()>0  ? $name : $this->name;
	$this->$name="";
	if(empty($name)){
		print "Invalid name for IO";
			return false;
		}
		
	if($this->type=="iidde"){
		$this->type = 'text';
	}
		
	if($this->type=='radio'){
		$comtag='name="'.$this->name.'" id="'.$this->id.'" title="'.$this->title.'" tabindex="'.$this->tabind.'"';
		foreach($this->aval as $k=>$v){
			if($k==$this->val) $this->$name.='<input type="'.$this->type.'" '.$comtag.' value="'.$k.'"'.' checked="checked" />'.$v.BR;
			else $this->$name.='<input type="'.$this->type.'" '.self::comtag().' value="'.$k.'"'.' />'.$v.BR;
			$this->tabind++;
		}	
		$this->$name.=self::ekonull(); 
	}
	elseif($this->type=='radio2'){
		$comtag='name="'.$this->name.'" id="'.$this->id.'" title="'.$this->title.'" tabindex="'.$this->tabind.'"';
		foreach($this->aval as $k => $v){
			if($k==$this->val) $this->$name.='<div class="input_radio"><input type="radio" '.$comtag.' value="'.$k.'"'.' checked="checked" />'.$v.'</div>';
			else $this->$name .= '<div class="input_radio"><input type="radio" '.self::comtag().' value="'.$k.'"'.' />'.$v.'</div>';
			$this->tabind++;
		}	
		$this->$name.=self::ekonull(); 
	}
	elseif($this->type=='radio3'){
		$comtag='name="'.$this->name.'" id="'.$this->id.'" title="'.$this->title.'" tabindex="'.$this->tabind.'"';
		foreach($this->aval as $k => $v){
			if($k==$this->val) $this->$name .= $v.' <input type="radio" '.$comtag.' value="'.$k.'"'.' checked="checked" /> ';
			else $this->$name .= $v.' <input type="radio" '.self::comtag().' value="'.$k.'"'.' /> ';
			$this->tabind++;
		}	
		$this->$name.=self::ekonull(); 
		}
		elseif($this->type=="text" || $this->type=="hidden" || $this->type=="submit" || $this->type=="button" || $this->type=="reset" || $this->type=="select" || $this->type=="slable" || $this->type=="textarea" ||  $this->type=="image" ||  $this->type=="a" ||  $this->type=="checkbox" ||  $this->type=="password" || $this->type=="checkbox2"){
		if($this->type=="checkbox" || $this->type=="checkbox2" ){
			if($this->val > 0 && $this -> checked !== false) $this->checked="checked"; # MOD RICKY
			$name = $this->name;
			$this -> type = "hidden";
			$this -> name = $name."_chkhid";
			$this -> $name .= (string)self::ltag().self::ctag().self::rtag(); 
			$this -> type = "checkbox";$this->name=$name;
			$this -> $name .= (string)self::ltag().self::ctag().self::rtag();
			if($this->type=="checkbox"){
				if(!empty($this -> lable)) $this -> $name .= ' '.$this -> lable; 
			}
			$this -> checked = "";
			} else {
			if( $this->type=="checkbox2"){ # NON USARE PIU QUESTA TIPOLOGIA CAMPO, USARE IL $db -> FLD -> lable = false;
				$this->type="checkbox";
				if($this -> checked === false);
					elseif($this->val > 0){
						$this->checked="checked";
					}
				$this->$name.=(string)self::ltag().self::ctag().self::rtag().self::ekonull();
				$this->type="checkbox2";	
			}
			else{ # text, hidden ecc...
			if(substr($this -> name, 0, 2) == 'D_' && $this -> type == 'text'){
				if(!dtime::check($this -> val)){
					$this -> val = dtime::my2iso($this -> val);
				}
			}
			$this->$name.=(string)self::ltag().self::ctag().self::rtag().self::ekonull();
			}
		}
	}
		else if($this->type=="lable"){
		$this->$name.=$this->val;
		}
	else if($this->type=="hiddenlable"){
		$type="hiddenlable";
		$this->type="hidden";
		$this->$name.=(string)self::ltag().self::ctag().self::rtag().$this->txtblank;
		$this->type="hiddenlable";
	}	
	else if($this->type=="ddmm4y"){
	
		$name=$this->name;
		$val=str_replace("-","",$this->val);
		$type=$this->type;
		
		$this->type="text";$this->name=$name."_dd";$this->val=substr($val,6,2)==chr(32).chr(32) || false ?"":substr($val,6,2);$this->size="2";$this->maxl=2;
		$this->$name.=(string)self::ltag().self::ctag().self::rtag();
		$this->$name.="/";
		$this->type="text";$this->name=$name."_mm";$this->val=substr($val,4,2)==chr(32).chr(32)?"":substr($val,4,2);;$this->size="2";$this->maxl=2;
	
		$this->$name.=(string)self::ltag().self::ctag().self::rtag();
		$this->$name.="/";
		$this->type="text";$this->name=$name."_4y";$this->val=substr($val,0,4)==chr(32).chr(32).chr(32).chr(32)?"":substr($val,0,4);$this->size="4";$this->maxl=4;
	
		$this->$name.=(string)self::ltag().self::ctag().self::rtag().self::ekonull();
		$this->type="ddmm4y";$this->name=$name;$this->val=$val;$this->size="";$this->maxl="";
	}
	else if($this->type=="ddmm4yhhiiss"){
	
		$name=$this->name;
		$val=$this->val;
		$val=str_replace("-","",$val);
		$val=str_replace(":","",$val);
		$val=str_replace(" ","",$val);
		$type=$this->type;
		
		$this->type="text";$this->name=$name."_dd";$this->val=substr($val,6,2)==chr(32).chr(32)?"":substr($val,6,2);$this->size="2";$this->maxl=2;
		$this->$name.=(string)self::ltag().self::ctag().self::rtag();
		$this->$name.="/";
		
		$this->type="text";$this->name=$name."_mm";$this->val=substr($val,4,2)==chr(32).chr(32)?"":substr($val,4,2);;$this->size="2";$this->maxl=2;
		$this->$name.=(string)self::ltag().self::ctag().self::rtag();
		$this->$name.="/";
		
		$this->type="text";$this->name=$name."_4y";$this->val=substr($val,0,4)==chr(32).chr(32).chr(32).chr(32)?"":substr($val,0,4);$this->size="4";$this->maxl=4;
		$this->$name.=(string)self::ltag().self::ctag().self::rtag();
		$this->$name.="&nbsp;";
		
		$this->type="text";$this->name=$name."_hh";$this->val=substr($val,8,2)==chr(32).chr(32)?"":substr($val,8,2);$this->size="2";$this->maxl=2;
		$this->$name.=(string)self::ltag().self::ctag().self::rtag();
		$this->$name.=":";
		
		$this->type="text";$this->name=$name."_ii";$this->val=substr($val,10,2)==chr(32).chr(32)?"":substr($val,10,2);$this->size="2";$this->maxl=2;
		$this->$name.=(string)self::ltag().self::ctag().self::rtag();
		$this->$name.=":";
		
		$this->type="text";$this->name=$name."_ss";$this->val=substr($val,12,2)==chr(32).chr(32)?"":substr($val,12,2);$this->size="2";$this->maxl=2;
		$this->$name.=(string)self::ltag().self::ctag().self::rtag().self::ekonull();
		
		$this->type="ddmm4yhhiiss";$this->name=$name;$this->val=$val;$this->size="";$this->maxl="";
	}
	else if($this->type=="hhmm"){
		$name=$this->name;
		$val=$this->val;
		$type=$this->type;
		$this->type="select";$this->name=$name."_hh";$this->val=substr($val,0,2);$this->aval=dtime::ahh();
		$this->$name.=(string)self::ltag().self::ctag().self::rtag();
		$this->$name.=":";
		$this->type="select";$this->name=$name."_ii";$this->val=substr($val,2,2);$this->aval=dtime::aii();
		$this->$name.=(string)self::ltag().self::ctag().self::rtag().self::ekonull();
		$this->type="hhmm";$this->name=$name;$this->val=$val;$this->aval=array();	
		} else {
		print 'Invalid type input';
		return false;
	}
	$this->html.=$this->$name."\n";
	return $this->$name;
}

function comtag(){
		$tmpStr = '';
		$tmpStr .= !empty($this -> name) ? ' name="'.$this -> name.'"' : ''; 
		$tmpStr .= !empty($this -> id) ? ' id="'.$this -> id.'"' : '';
		$tmpStr .= !empty($this -> js) ? ' '.$this -> js : '';
		$tmpStr .= strlen($this -> title) > 0 ? ' title="'.$this -> title.'"' : ''; 
		$tmpStr .= !empty($this -> tabind) ? ' tabindex="'.$this -> tabind.'"' : '';
		$tmpStr .= !empty($this -> css) ? ' class="'.$this -> css.'"' : '';
		$tmpStr .= !empty($this -> src) ? ' src="'.$this -> src.'"' : '';
		$tmpStr .= !empty($this -> alt) ? ' alt="'.$this -> alt.'"' : '';
		$tmpStr .= !empty($this -> href) ? ' href="'.$this -> href.'"' : '';
		$tmpStr .= !empty($this -> target) ? ' target="'.$this -> target.'"' : '';
		$tmpStr .= !empty($this -> border) ? ' border="'.$this -> border.'"' : '';
		$tmpStr .= !empty($this -> disabled) ? ' disabled="'.$this -> disabled.'"' : '';
		$tmpStr .= !empty($this -> readonly) ? ' readonly="'.$this -> readonly.'"' : '';
		$tmpStr .= !empty($this -> checked) ? ' checked="'.$this -> checked.'"' : '';
		$tmpStr .= !empty($this -> size) ? ' size="'.$this -> size.'"' : '';
		$tmpStr .= !empty($this -> cols) ? ' cols="'.$this -> cols.'"' : '';
		$tmpStr .= !empty($this -> rows) ? ' rows="'.$this -> rows.'"' : '';
		$tmpStr .= ! empty($this->placeholder) ? ' placeholder="'.$this->placeholder.'"' : '';
		$tmpStr .= $this -> ins;
		return (string) $tmpStr;
}

function ltag(){
	if($this->type=="text"){
		return (string)'<input type="'.$this->type.'"'.self::comtag().' value="'.$this->val.'"'.' maxlength="'.$this->maxl.'"'.' />';
	}	
	else if($this->type=="hidden" || $this->type=="submit" || $this->type=="button" || $this->type=="reset" || $this->type=="image" || $this->type=="checkbox" || $this->type=="password"){
		return (string)'<input type="'.$this -> type.'"'.self::comtag().' value="'.$this -> val.'"'.' />';
	}
	else if($this->type=="textarea" || $this->type=="select" || $this->type=="a"){
		//print $this->type.self::comtag().BR;
		return (string)'<'.$this->type.self::comtag().'>';
	}
}
	
function ctag(){
		if($this->type=="text" || $this->type=="submit" || $this->type=="button" || $this->type=="reset" || $this->type=="hidden" || $this->type=="image" || $this->type=="checkbox" || $this->type=="password") {
			return (string)'';
		}
		else if($this->type=="textarea" || $this->type=="a"){
			return (string)$this->val;
		}
	else if($this->type=="select"){
		$str="";
		
		$is_selected = false;
		foreach($this->aval as $k => $v){
			if($k==$this->def && strlen($k)==strlen($this->def) && empty($this -> val) && !empty($this -> def)){ # SELECT SU VALORE DI DEFAULT SE VAL � VUOTO
				$str.='<option value="'.$k.'" selected="selected">'.$v.'</option>'."\n";
				$is_selected = true;
			}
			elseif($k==$this->val && strlen($k) == strlen($this->val) && !empty($this->val)){ # SELECT SU VAL		
				$str.='<option value="'.$k.'" selected="selected">'.$v.'</option>'."\n";
				$is_selected = true;
			}
			else{
				$str.='<option value="'.$k.'">'.$v.'</option>'."\n";
			}
		}
		if($this->addblank==1){
			if($is_selected){
				$str = '<option value="">'.$this -> txtblank.'</option>'."\n".$str;
			}
			else{
				$str = '<option value="" selected="selected">'.$this -> txtblank.'</option>'."\n".$str;
			}
		}
			if(count($this->aval)==0 && $this->addblank==0 ){
				$str="";
			}
		return(string) $str;
	}		
	else if($this->type == 'slable'){
		$str = '';
		$is_selected = false;
		foreach($this->aval as $k => $v){
			if($k==$this->def && strlen($k)==strlen($this->def) && empty($this -> val) && !empty($this -> def)){ # SELECT SU VALORE DI DEFAULT SE VAL � VUOTO
				$str = $v;
				$is_selected = true;
			}
			elseif($k==$this->val && strlen($k) == strlen($this->val) && !empty($this->val)){ # SELECT SU VAL		
				$str = $v;
				$is_selected = true;
			}
		}
		return(string) $str;
	}	
}
function rtag(){
	if($this->type=="text" || $this->type=="submit" || $this->type=="button" || $this->type=="reset" || $this->type=="hidden" || $this->type=="image"|| $this->type=="checkbox" || $this->type=="password")
		return (string)'';
	else if($this->type=="textarea" || $this->type=="select" || $this->type=="a" )
		return (string)'</'.$this->type.'>';
}
	
	static function checked($k,$k2){
	return $k===$k2 ? "checked" : "";
}


	static function ahrefcss($path="",$val=array(),$text="",$js,$target="",$title="",$id="",$class=""){ # USARE METODO href
	$str="<a href=\"".url::uri($path,$val)."\" ";
	$str.=trim($target)!="" ? "target=\"".$target."\" " : "";
	$str.=trim($js)!=""     ? $js." " 					: "";
	$str.=trim($title)!=""  ? "title=\"".$title."\" "	: "";
	$str.=trim($id)!=""  ? "id=\"".$id."\" "	: "";
	$str.=trim($class)!=""  ? "class=\"".$class."\" "	: "";
	$str=substr($str,0,strlen($str)-1);
	$str.=">";
	$str.=$text;
	return $str.="</a>";
}

	static function href($path="",$val=array(),$text="",$js, $target="",$title="",$id="",$class=""){
	$str="<a href=\"".url::get($path,$val)."\" ";
	$str.=trim($target)!="" ? "target=\"".$target."\" " : "";
	$str.=trim($js)!=""     ? $js." " 					: "";
	$str.=trim($title)!=""  ? "title=\"".$title."\" "	: "";
	$str.=trim($id)!=""  ? "id=\"".$id."\" "	: "";
	$str.=trim($class)!=""  ? "class=\"".$class."\" "	: "";
	$str=substr($str,0,strlen($str)-1);
	$str.=">";
	$str.=$text;
	return $str.="</a>";
}


	static function ahref($path="", $val=array(), $text="", $class=""){
	if(empty($path)) $path = PHP_SELF;
	$str="<a href=\"".url::get($path,$val)."\" ";
	$str.=trim($class)!=""  ? "class=\"".$class."\" "	: "";
	$str=substr($str,0,strlen($str)-1);
	$str.=">";
	$str.=$text;
	return $str.="</a>";
}



	static function img($path="",$val=array(),$alt="",$js,$target="",$title="",$id="",$class=""){
	$str="<img src=\"".url::uri($path,$val)."\" ";
	$str.=trim($target)!="" ? "target=\"".$target."\" " : "";
	$str.=trim($js)!=""     ? $js." " 					: "";
	$str.=trim($title)!=""  ? "title=\"".$title."\" "	: "";
	$str.=trim($alt)!=""  ? "alt=\"".$alt."\" "	: "";
	$str.=trim($id)!=""  ? "id=\"".$id."\" "	: "";
	$str.=trim($class)!=""  ? "class=\"".$class."\" "	: "";
	$str=substr($str,0,strlen($str)-1);
	$str.="/>";
	return $str;
}

	static function dtime($sublable,$db){
	foreach($sublable as $k=>$v){
	$db->$k->val=trim($db->$k->val);
		if($db->$k->type=="ddmm4y"){
			$dtime=new dtime($db->$k->val);
			$db->$k->val= $dtime->date2str("d/m/y");
		}
	}
	return $db;
}	
	
# RICEVE UN ARRAY DI RECOD E GENERA IL SELECT RELATIVO IN BASE AI CAMPI SPECIFICATI
	static function select_from_recordset($rec, $lbl_val, $lbl_lable, $selected, $blank, $aExtra){
	$sExtra = '';
	foreach($aExtra as $marker => $attr){
		$sExtra .= ' '.$marker.'="'.$attr.'" ';
	}
	
	$str = '<select '.$sExtra.'>';
	$str .= empty($blank) ? '' : '<option value="">'.$blank.'</option>';
	foreach($rec as $k => $v){
		$sel = $v[$lbl_val] == $selected ? ' selected="selected"' : '';
		$str .= '<option value = "'.$v[$lbl_val].'"'.$sel.'>'.$v[$lbl_lable].'</option>';
	}
	return $str .= '</select>';
}

function tooltip($title, $description,$txt){
	$title = empty($title) ? '' : $title;
	$description = empty($description) ? '' : $description;
	$txt = empty($txt) ? '' : $txt;
	return '
	<a href="'.$description.'" onclick="return false;" class="Tips1" title="'.$title.'">'.$txt.'</a>';
}

public function td_db($fld, $db){
	ob_start();
	$db->$fld->get();
	$input = ob_get_clean();
	$ret = '<td>'.constant($fld).':</td><td>'.$input.'</td>';
	return $ret;
}

function get_dname($id){
	$ret = rs::rec2arr("SELECT DESCRIPTOR_".LANG_DEF." FROM descriptors WHERE ID_DESCRIPTOR = '$id'");
	return $ret['DESCRIPTOR_'.LANG_DEF];
}

	public static function get_dp($id){
	$q = "SELECT * FROM descriptors WHERE ID_DESCRIPTOR = '".$id."'";
	$r = rs::rec2arr($q);
	$ret = $r['DESCRIPTOR_'.LANG_DEF];
	return $ret;
}

public function lable($a, $f, $use_constant, $cut = 0){
		$add_lable = true;
		if(empty($a[$f])) return false;
		$ret = ''; $lbl = '';
		if($use_constant){ //print constant($f).' '.$a[$f].' '.$f.BR;
			if(defined($f)){
				$lbl = '<span>'.constant($f).':</span> ';
			}
			else{
				$lbl = '';
			}
		}
		$sino = defined($f) ? '<span>'.constant($f).'</span> ' : '';

		if(substr($f,0,2)=='D_') $ret = dtime::my2iso($a[$f]); // DATA
		if(substr($f,0,3)=='DT_') $ret = dtime::my2iso($a[$f]); // DATA
		if(substr($f,0,3)=='TS_') $ret = dtime::my2isodt($a[$f]); // DATA
		elseif(substr($f,0,3)=='IS_'){ # SI / NO
			if(!empty($a[$f])){
			 if($use_constant){
			 	$ret = $lbl.'s&igrave;'; $add_lable = false;
			 }
			 else{ $ret = $sino;
			 }
			}
		}
		elseif(substr($f,0,3)=='ID_'){
			$ret = rs::get_ext($f, $a[$f]);
		} 
		elseif(substr($f,3,3)=='ID_'){ # DESCRIPTORS K1_ID_NOMECAMPO
			$q = "SELECT * FROM descriptors WHERE ID_DESCRIPTOR = '".$a[$f]."'";
			$r = rs::rec2arr($q);
			$ret = $r['DESCRIPTOR_'.LANG_DEF];
		}
		elseif(strpos($f, 'PREZZO')!==false){
			$ret = num::ita($a[$f]).' &euro;';
		}
		elseif(strpos($f, 'KM') !== false){
			$ret = num::ita($a[$f]);
		}
		elseif(is_numeric($a[$f])){
			$ret = '<span>'.constant($f).':</span> '.$a[$f];
			$add_lable = false;
		}
		else{
			if(!empty($cut)){
				$ret = $lbl.strcut(trim(strip_tags($a[$f])),'...', $cut);
			}
			else{
				$ret = $lbl.$a[$f];
				$add_lable = false;
			}
		}
		if($use_constant && $add_lable) $ret = $lbl.$ret;
		return $ret;
}

	public static function formatta_campi($aFields, $aStrip, $db, $is_lbl){
	$aInputs = array();
	$aInputs['hidden'] = '';
	$aInputs['lable'] = '';
	$aInputs['text'] = '';
	$aInputs['textarea'] = '';
	$aInputs['select'] = '';
	$aInputs['radio'] = '';
	$aInputs['checkbox'] = '';

	foreach($aFields as $k => $v){
		if($is_lbl){
			$lable = self::lable(array($v => $db -> $v -> val), $v, true, $cut = 0);
			if($lable) $aInputs['lable'] .= $lable.BR;
		}
		else{
			if(!in_array($v, $aStrip)){
				if($db -> $v -> type == 'hidden') {
					ob_start();
					$db -> $v -> get();
					$aInputs['hidden'] .= ob_get_clean()."\n";
				}
				elseif($db -> $v -> type == 'checkbox'){
					ob_start();
					print '<div class="box_checkbox">'."\n";
					//$db -> $v -> get(); print stringa::uppercase_first(constant($v));
					$db -> $v -> get(); // print constant($v);
					print '</div>'."\n";
					$aInputs['checkbox'] .= ob_get_clean();
					
				}
				elseif($db -> $v -> type == 'radio'){
					ob_start();
					print '<div class="blocco">'."\n";
					print '<h4>'.constant($v).':</h4>'."\n";
					$db -> $v -> get();
					print '</div>'."\n";
					$aInputs['radio'] .= ob_get_clean();
				}
				elseif($db -> $v -> type == 'textarea'){
					ob_start();
					print '<div class="box_textarea">'."\n";
					print '<h2>'.constant($v).'</h2>'."\n";
					$db -> $v -> get();
					print '</div>'."\n";
					$aInputs['textarea'] .= ob_get_clean();
				
					/*ob_start();
					print constant($v).BR;
					$db -> $v -> get(); 
					print BR;
					$aInputs['textarea'] .= ob_get_clean();*/
				}
				elseif($db -> $v -> type == 'text' || $db -> $v -> type == 'iidde'){
/*					if($db -> $v -> type == 'iidde'){
						$db -> $v -> css = 'decimal';
					}
*/					ob_start();
					print '<div class="box_text">'."\n";
					print '<h2>'.constant($v).'</h2>'."\n";
					$db -> $v -> get();
					print '</div>'."\n";
					$aInputs['text'] .= ob_get_clean();
				}
				elseif($db -> $v -> type == 'select'){
					ob_start();
					print '<div class="box_select">'."\n";
					print '<h2>'.constant($v).'</h2>'."\n";
					$db -> $v -> get();
					print '</div>'."\n";
					$aInputs['select'] .= ob_get_clean();
				}
				elseif($db -> $v -> type == 'lable'){
					$lable = self::lable(array($v => $db -> $v -> val), $v, true, $cut = 0);
					if($lable) $aInputs['lable'] .= $lable.BR;
				}
			}
		}
	}
	
	if(!empty($aInputs['radio'])) $aInputs['radio'] = '<div class="box_radio">'.$aInputs['radio'].'<div class="fix"></div></div>';
	
	foreach($aInputs as $k => $v){
		if(!empty($aInputs[$k]) && $k != 'radio'){
			$aInputs[$k] .= '<div class="fix"></div>';
		}
	}
	return $aInputs;
}

	public static function mk_etichette2($aFields, $aStrip, $arr){ # CREA LE SCHEDE ALL'INTERNO DEL GESTIONALE, SI BASA SUL RECORD IN FORMATO ARRAY
	$ret = array();
	$ret['textarea'] = '';
	$ret['testo'] = '';
	$ret['descriptor'] = '';
	$ret['sino'] = '';
	foreach($aFields as $k => $v){
		//print $db -> $v -> type.$v.BR;
		///print $v.BR;
		if(!in_array($v, $aStrip)){
			$lable = self::lable(array($v => $arr[$v]), $v, false, $cut = 0);
		
			//if($db -> $v -> type == 'hidden') { continue; }
			if(substr($v,3,3)=='ID_'){ # DESCRIPTORS
				if(!empty($lable))$ret['descriptor'] .= '<tr><td width="115"><strong>'.constant($v).':</strong></td><td width="130">'.$lable.'</td></tr>';
			}
			elseif(substr($v,0,3)=='IS_'){ # SINO
				if(!empty($arr[$v])) $ret['sino'] .= '<li>'.constant($v).'</li>';
			}
			elseif(substr($v,0,3)=='ID_'){ # SELECT
				if(!empty($arr[$v])) $ret['testo'] .= $lable.' '.$arr[$v];
			}
			elseif(substr($v,0,8)=='DESCRIP_'){
				if(!empty($arr[$v])) $ret['textarea'] .= $arr[$v];
			}
			else{ # TESTO
				$string = $arr[$v];
				if(strpos($v, 'PREZZO') !== false && !empty($string)) $string = num::ita($string).' &euro;';
				elseif(substr($v,0,2)=='D_') $string = dtime::my2iso($string); // DATA
				elseif(substr($v,0,3)=='DT_') $string = dtime::my2iso($string); // DATA
				elseif(substr($v,0,3)=='TS_') $string = dtime::my2isodt($string); // DATA
				
				if(!empty($arr[$v])) $ret['testo'] .= '<tr><td width="115"><strong>'.constant($v).':</strong></td><td width="130">'.$string.'</td></tr>';
			}
		}
	}
	if(!empty($ret['sino'])) $ret['sino'] = '<ul>'.$ret['sino'].'</ul>';
	if(!empty($ret['descriptor'])) $ret['descriptor'] = "<table id=\"tdlista\">\n".$ret['descriptor'].'<tr><td colspan="2">&nbsp;</td>'.$ret['testo'].'</tr></table>';
	return $ret;
}

	static function a($path="", $val=array(), $txt, $aExtra = array('id' => '', 'class' => '')){
	$str = ''; $sExtra = '';
	foreach($aExtra as $marker => $attr){
		$sExtra .= ' '.$marker.'="'.$attr.'" ';
	}
	$str = "<a href=\"".url::get($path,$val)."\" ".$sExtra;
	$str = substr($str,0,strlen($str)-1);
	$str .= ">".$txt."</a>";
	
	return $str;
}

	public static function headto($file, $aVars){
		if( strpos($file, 'http://') !== false){
			$location = 'Location:'.$file.url::get('', $aVars, '&');
		} else {
			$location = HEADER_TO.url::get($file, $aVars, '&');
		}
		//echo $location;
		header($location);
}

}
?>