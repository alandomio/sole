<?php
# V.0.1.8
class arr{
function intersect($args,$arr){
	$narr=array();
	foreach($args as $arg){
		$narr[$arg]=$arr[$arg];}
	return $narr;
	}
function controlla_esistenza_valore($aVals, $arr){
	$ret = false;
	
	foreach($aVals as $k => $v){
		if(in_array($v, $arr)) { 
			$ret = true; 
			break;
		}
	}
	return $ret;
}

function srcArrKey($aVals, $arr, $key){
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

function stampa($a){
	print 'Inizio stampa:'.BR;
	foreach($a as $k => $v){
		if(is_array($v)){ arr::stampa($v); }
		else print $k.' '.$v.BR;
	}
	print '######### fine'.BR;
}

function eko($a){
	print '<pre>';
	print_r($a);
	print '</pre>';
}
	function kfil($arr,$str){
		foreach($arr as $k=>$v){
			if(strpos($k,$str)!==false  && stringa::rightfromlast($k,$str)=="" ){
				$nk=stringa::leftfrom($k,$str);
				unset($arr[$k]);
				$arr[$nk]=$v;
			}}
		return ($arr);
		}	
	function wizard($str,$arr){
		$narr=array();
		foreach($arr as $k=>$v){
			$narr[$str.$k]=$v;			}
		$arr2=func_num_args()>2 ?  func_get_arg(2) :  array();
		return array_merge($narr,$arr2);
		}
	function multifil($arr,$str){
		$sk="";
		$narr=array();
		foreach($arr as $k=>$v){
			if(stringa::rightfromlast($k,$str)!="" && strpos($k,$str)!==false){
			$sk=stringa::leftfrom($k,$str);
			$narr[]=$v;}}
		return array($sk,$narr);
		}
	function onefil($arr,$str){
		$sk="";
		$narr=array();
		foreach($arr as $k=>$v){
			if(stringa::rightfromlast($k,$str)=="" && strpos($k,$str)!==false){
			$sk=stringa::leftfrom($k,$str);
			$narr[$sk]=$v;}}
		return $narr;
		}	
	function r($arr){
		$i=0;
		foreach($arr as $k=>$v){ echo "$i. $k=>$v<br>";$i++;}
		}
	function merge($arr,$arr2){
	foreach($arr2 as $k=>$v){
		array_key_exists($k,$arr) ? $arr[$k]=$v : NULL;
		}
	return $arr;
	}	
	function cutkey($arr,$str){
	foreach($arr as $k=>$v){
		$k2=str_replace($str,"",$k);
		$arr[$k2]=$v;
		unset($arr[$k]);
		}
	return $arr;
	}	
	function lable($name_fields,$fldlable){
	$lable=array_flip($name_fields);
	$lable=array_merge($lable,$fldlable);
	return arr::_empty($lable);}
	
	function sub($arr,$key){
		$ret=array();
	 	foreach($arr as $subarr)
			$ret[]=$subarr[$key];	
		return $ret;} 
	 function  blank2false($arr){
	 	foreach($arr as $k=>$v)
			$arr[$k]=is_string($v) && trim($v)==0 ? false:$v;
		return $arr;} 

	 function strip($arr){
		foreach($arr as $k=>$v){
			if( is_null($v) || (is_bool($v) && $v==false) || (is_string($v) && trim($v)=="") || (is_numeric($v) && ($v)==0))
				unset($arr[$k]);}
		return $arr;}
	 function _empty($arr){
		foreach($arr as $k=>$v){
			if(empty($v)  )
				unset($arr[$k]);}
		return $arr;}	
	function fill($arr,$write,$skip=array()){
		foreach($arr as $k=>$v){
			if(!in_array($k,$skip)){
				$arr[$k]=$write;}}
		return $arr;}	
	 function blank($arr,$skip=array()){
		foreach($arr as $k=>$v){
			if(!in_array($k,$skip)){
				$arr[$k]="";}}
		return $arr;}	
	 function _trim($arr,$skip=array()){
		foreach($arr as $k=>$v){
			if(!in_array($k,$skip)){
				$arr[$k]=trim($v);}}
		return $arr;}
	 function _strip_tags($arr,$skip=array()){
		foreach($arr as $k=>$v){
			if(!in_array($k,$skip))
				$arr[$k]=strip_tags($v);}
		return $arr;}	
	 function _mysql_real_escape_string($arr,$skip=array()){
		foreach($arr as $k=>$v){
			if(!in_array($k,$skip))
				$arr[$k]=mysql_real_escape_string($v);}
		return $arr;}	
		
	function magic_quote($request){
	if(ini_get('magic_quotes_sybase')+get_magic_quotes_gpc()){
		foreach($request as $k=>$v){
			$request[$k]=stripslashes($request[$k]);
			}}
	return $request;}	
	#sovrascrive l'array record dell'insert o del update con il post inviato
	# return array["nome campo"]="valore"	
	function &post2arr($rec=array("NOME"=>"Gino","COGNOME"=>"Mario")){
		foreach($rec as $k=>$v) {
			if(array_key_exists($k,$_POST) ){	
					$rec[$k]=$_POST[$k]; }
				}
		return $rec;}	
	function post2arr2($rec,$ext){
		foreach($rec as $k=>$v) {
			if(array_key_exists($k."$ext",$_POST) ){	
					$rec[$k]=$_POST[$k."$ext"]; }
				}
		return $rec;}
		
function _unset($arr, $unset = array()){
	foreach($unset as $k => $v){
		$arr[$v] = '';
		unset($arr[$v]);
		}
	return $arr;
}	
		
public function semplifica($arr, $key){
	$ar = array();
	foreach($arr as $k => $v){
		foreach($v as $campo => $valore){
			$ar[$v[$key]][$campo] = $valore;
			}
	}
	return $ar;
}	

public function key2arr($arr, $key){ # RESTITUISCE UN ARRAY CON I VALORI DEL CAMPO CHIAVE FORNITO
	$ret = array();
	foreach($arr as $k => $v){
		$ret[] = $v[$key];
	}
	return $ret;
}	

public function arr2constant($arr, $mk_link){
	$aR = array();
	foreach($arr as $k => $v){
		if(defined($v)){
			$aR[$v] = constant($v);
		}
		else{
		//	$aR[$v] = $v;

			$aR[$v] = !$mk_link ? $v : io::a('lablesites_c.php', array('mknew' => $v), $v, array('target' => '_blank'));
		}
	}
	return $aR;
}

public function unset_vals($arr, $aVals){
	foreach($arr as $k => $v){
		if(in_array($v, $aVals)){
			unset($arr[$k]);
		}
	}
	return $arr;
}

public function duplicate($a, $k){ # RESTITUISCE UN ARRAY CON LA LISTA DEI VALORI DUPLICATI PER UNA CHIAVE
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