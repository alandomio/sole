<?php
class arr{
	
static function srcArrKey($aVals, $arr, $key){
	$ret = false;
	foreach($aVals as $k => $v){
		foreach($arr as $kk => $vv){
			if(in_array($vv[$key], $aVals)) { 
				$ret = true; 
				break;
			}
		}
	}
	return $ret;
}

static function stampa($a){
	print 'Inizio stampa:'.BR;
	foreach($a as $k => $v){
		if(is_array($v)){ arr::stampa($v); }
		else print $k.' '.$v.BR;
	}
	print '######### fine'.BR;
}

static function merge($arr,$arr2){
	foreach($arr2 as $k=>$v){
		array_key_exists($k,$arr) ? $arr[$k]=$v : NULL;
		}
	return $arr;
	}	
	
 static function  blank2false($arr){
	 	foreach($arr as $k=>$v)
			$arr[$k]=is_string($v) && trim($v)==0 ? false:$v;
		return $arr;} 

 static function strip($arr){
		foreach($arr as $k=>$v){
			if( is_null($v) || (is_bool($v) && $v==false) || (is_string($v) && trim($v)=="") || (is_numeric($v) && ($v)==0))
				unset($arr[$k]);}
		return $arr;}
 static function _empty($arr){
		foreach($arr as $k=>$v){
			if(empty($v)  )
				unset($arr[$k]);}
		return $arr;}	
 static function blank($arr,$skip=array()){
		foreach($arr as $k=>$v){
			if(!in_array($k,$skip)){
				$arr[$k]="";}}
		return $arr;}	
 static function _trim($arr,$skip=array()){
		foreach($arr as $k=>$v){
			if(!in_array($k,$skip)){
				$arr[$k]=trim($v);}}
		return $arr;}
 static function _strip_tags($arr,$skip=array()){
		foreach($arr as $k=>$v){
			if(!in_array($k,$skip))
				$arr[$k]=strip_tags($v);}
		return $arr;}	
 static function _mysql_real_escape_string($arr,$skip=array()){
		foreach($arr as $k=>$v){
			if(!in_array($k,$skip))
				$arr[$k]=mysql_real_escape_string($v);}
		return $arr;}	
		
static function magic_quote($request){
	if(ini_get('magic_quotes_sybase')+get_magic_quotes_gpc()){
		foreach($request as $k=>$v){
			$request[$k]=stripslashes($request[$k]);
				}
	}
	return $request;
}	
		
static function _unset($arr, $unset = array()){
	foreach($unset as $k => $v){
		$arr[$v] = '';
		unset($arr[$v]);
		}
	return $arr;
}	
		
public static function semplifica($arr, $key){
	$ar = array();
	foreach($arr as $k => $v){
		foreach($v as $campo => $valore){
			$ar[$v[$key]][$campo] = $valore;
			}
	}
	return $ar;
}	

public static function key2arr($arr, $key){ # RESTITUISCE UN ARRAY CON I VALORI DEL CAMPO CHIAVE FORNITO
	$ret = array();
	foreach($arr as $k => $v){
		$ret[] = $v[$key];
	}
	return $ret;
}	

public static function arr2constant($arr, $mk_link){
	$aR = array();
	foreach($arr as $k => $v){
		if(defined($v)){
			$aR[$v] = constant($v);
		}
		else{
			$aR[$v] = !$mk_link ? $v : io::a('lablesites_c.php', array('mknew' => $v), $v, array('target' => '_blank'));
		}
	}
	return $aR;
}

public static function unset_vals($arr, $aVals){
	foreach($arr as $k => $v){
		if(in_array($v, $aVals)){
			unset($arr[$k]);
		}
	}
	return $arr;
}

public static function duplicate($a, $k){ # RESTITUISCE UN ARRAY CON LA LISTA DEI VALORI DUPLICATI PER UNA CHIAVE
	$ret = array();
	$aChk = array();
	foreach($a as $chk){
		if(in_array($chk[$k], $aChk)){
			$ret[] = $chk[$k];
		}
		$aChk[] = $chk[$k];
	}
	return $ret;
}

}
?>