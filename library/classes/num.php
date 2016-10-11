<?php
# V.0.1.8
class num{
function eueccesso($num){
	$flt = ceil($num);
	$flt = number_format($flt  ,2  , ',', '.');
	return $flt;
}

function formatvaluta($num){
	$flt =number_format($num  ,2  , ',', '.');
	return $flt;
}
	
function pFloat($lable,$val,$ok_null){
	$err=false;
	//echo $val."!=".floatval($val)."<br>";
	if( $val!=strval(floatval($val)) ||  strlen($val)!=strlen($val) || floatval($val)<0)//
		$err=" $lable valore \"$val\" non corretto "	;
	else{
		if(!$ok_null && (empty($val) ||  floatval($val)==0))
			$err="$lable specificare valore"	;
		}		
return $err;}

function isFloat($lable,$val,$ok_null){
	$err=false;
	//echo $val."!=".floatval($val)."<br>";
	if( $val!=strval(floatval($val)) ||  strlen($val)!=strlen($val) || floatval($val)<0)//
		$err=" $lable valore \"$val\" non corretto "	;
	else{
		if(!$ok_null && (empty($val) ||  floatval($val)==0))
			$err="$lable specificare valore"	;
		}		
	return $err;}
	
function ita($valuta){
	$flt="";
	if(!empty($valuta)){
	//if(trim($valuta)!="" && !is_null($valuta) && $valuta!=false){
	$dec=func_num_args()>0 && $valuta==ceil($valuta) ? 0 : 2; 
	$flt =number_format( $valuta  , $dec, "," , "." );}
	return $flt;
}
	
function bit2kb($bit){
	$kb="";
	if(trim($bit)!="" && !is_null($bit) )
		{//$kb=$bit;
			$kb=$bit/1024;
			$kb=round($kb,2);
			}
	return $kb;
	}	
function itaf($valuta,$um,$zerodec)	{
	$flt="";
	if(trim($valuta)!="" && !is_null($valuta) && $valuta!=false){
	$dec=$zerodec==0 && $valuta==ceil($valuta) ? 0 : 2; 
	$flt =number_format( $valuta  , $dec, "," , "." );}
	$flt=$um.$flt;
	return $flt;	}
	

	
function format($int, $n_decimals,$dec_sep, $thousands_sep){
	return number_format($int, $n_decimals, $dec_sep, $thousands_sep);
}
	
public function getdec($num,$type_getdec)
	{
	if(strpos($num,".")!==false){
		$int=stringa::rightfrom($num,'.');
		$dec=stringa::leftfrom($num,'.'); 
	}
	else {
		$int=$num;
		$dec=0;
	}
	if(strtoupper($type_getdec)=="INT") return $int;
	else if(strtoupper($type_getdec)=="DEC") return $dec;
	else 
		{
		print "invalid type_getdec";
		return false;
		}
	}
public function countdec($num,$type_countdec)
	{
	if(strpos($num,".")!==false)	
		{
		$int=stringa::rightfrom($num,'.');
		$dec=stringa::leftfrom($num,'.'); 
		}
	else 
		{
		$int=$num;
		$dec="";
		}
	if(strtoupper($type_countdec)=="INT") return strlen($int);
	else if(strtoupper($type_countdec)=="DEC") return strlen($dec);
	else
		{
		print "invalid type_countdec";
		return false;
		}
	}
	
public function countmydec($num,$type_countmydec){
	if(strpos($num,",")!==false){
		$dec=stringa::leftfrom($num,'.'); 
		$int=(stringa::rightfrom($num,'.')-1-$dec);
		}
	else{
		$int=$num;
		$dec=0;
	}
	if(strtoupper($type_countmydec)=="INT")  return strlen($int);
	else if(strtoupper($type_countmydec)=="DEC") return  strlen($int);
	else{
		print "invalid type_countmydec";
		return false;
	}
}
	
}
?>
