<?php
class request{
static function get($arr=array()){ $arr2=array();
	foreach($arr as $k => $v){
		/*
		 * gestisce le variabili in ordine di importanza (POST, GET, REQUEST, default)
		 * */
		if(array_key_exists($k, $_POST)){ $_REQUEST[$k] = $_POST[$k]; }
		elseif(array_key_exists($k, $_GET)){ $_REQUEST[$k] = $_GET[$k]; }
		elseif(!array_key_exists($k, $_REQUEST)){ $_REQUEST[$k] = $v; }
		$arr2[] = $_REQUEST[$k];
	}
	return $arr2;
}
	
public static function gKey($k){
	$ret = false;
	if(array_key_exists($k, $_GET)){
		$ret = true;
	}
	return $ret;
}
	
static function defArr($vars=array(),$aDef){
		$vars=!is_array($vars) ? $vars=array($vars) : $vars;
		$ret=array();
		foreach($vars as $var){
			foreach($_REQUEST as $k=>$v){
				if(strpos($k,$var)!==false){
					$ret[$k]=$v;}}}
		$ret=empty($ret) ? $aDef : $ret;
		return $ret;
		}
		
	
static function give($vars=array()){
		$vars=!is_array($vars) ? $vars=array($vars) : $vars;
		$ret=array();
		foreach($vars as $var){
			foreach($_REQUEST as $k=>$v){
				if(strpos($k,$var)!==false){
					$ret[$k]=$v;}}}
		return $ret;
		}
		
static function hidden($arr=array()){
		$str="";
		foreach($arr as $k => $v)
			$str.='<input type="hidden" name="'.$k.'" value="'.$v.'" />'."\n";
		$str=substr($str,0,strlen($str)-1);
		return $str;
	}
		
static function &post2arr($rec=array()){
		$res=array();
		foreach($rec as $k=>$v) {
			if(array_key_exists($k,$_POST) ){
					$res[$k]=$_POST[$k]; }
				}
		return $res;}
	
static function toupper($request=array(),$arrup=array()){
		foreach($request as $k=>$v){
			if(in_array($k,$arrup)){
				$request[$k]=strtoupper($v);}	
			}
		
		return $request;
		}
		
static function adjustPost($request){
		foreach($request as $k=>$v){
			if( strpos($k,"_chkhid")!== false ){ 
				$k2=stringa::leftfrom($k,"_chkhid");
				if(!array_key_exists($k2,$request))
				$request[$k2]=0;
				else
				$request[$k2]=1;
				unset($request[$k2.'_chkhid']);
			}
			if(strpos($k,"_iie")!==false ){
				$k2=leftfrom($k,"_iie");
				$float=(string)trim($request[$k2.'_iie'].".".$request[$k2.'_dde']);
				$zero=(string)".".chr(48);
				$zero2=(string)".".chr(48).chr(48);
				if($float=="." ){
					$request[$k2]="";}
				else {
					$request[$k2.'_iie']=strlen($request[$k2.'_iie'])==0  ? "0" : $request[$k2.'_iie'];
					$request[$k2]=$request[$k2.'_iie'].".".str_pad($request[$k2.'_dde'],2,"0",STR_PAD_RIGHT);}
				unset($request[$k2.'_iie'],$request[$k2.'_dde']);
			}
			if(strpos($k,"_4y")!==false ){
				$k2=stringa::leftfrom($k,"_4y");
				if(!array_key_exists($k2."_ii",$request)){
					$request[$k2]=str_pad($request[$k2.'_4y'],4,chr(32),STR_PAD_LEFT).str_pad($request[$k2.'_mm'],2,chr(32),STR_PAD_LEFT).
								  str_pad($request[$k2.'_dd'],2,chr(32),STR_PAD_LEFT);
					$request[$k2]=$request[$k2]==chr(32).chr(32).chr(32).chr(32).chr(32).chr(32).chr(32).chr(32) ?"" :$request[$k2];
					unset($request[$k2.'_4y'],$request[$k2.'_mm'],$request[$k2.'_dd']);}
				else{
					$request[$k2]=str_pad($request[$k2.'_4y'],4,chr(32),STR_PAD_LEFT).str_pad($request[$k2.'_mm'],2,chr(32),STR_PAD_LEFT).
								  str_pad($request[$k2.'_dd'],2,chr(32),STR_PAD_LEFT).
								  str_pad($request[$k2.'_hh'],2,chr(32),STR_PAD_LEFT).str_pad($request[$k2.'_ii'],2,chr(32),STR_PAD_LEFT).
								  str_pad($request[$k2.'_ss'],2,chr(32),STR_PAD_LEFT);
					$request[$k2]=$request[$k2]==chr(32).chr(32).chr(32).chr(32).chr(32).chr(32).chr(32).chr(32).chr(32).chr(32).chr(32).chr(32).chr(32).chr(32)
							      ?"" :$request[$k2];
					unset($request[$k2.'_4y'],$request[$k2.'_mm'],$request[$k2.'_dd'],$request[$k2.'_hh'],$request[$k2.'_ii'],$request[$k2.'_ss']);
					}
				}
			}
		return  $request;
}
}
?>
