<?php
class stringa{
	
static function alphanum_replace_with($str, $s_replace){
		$allow = 'qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM_0123456789';
		$ret = '';
		for($i=0; $i<strlen($str); $i++){
			if(strpos($allow,$str[$i])!==false){
				$ret .= $str[$i];
			} else {
				$ret .= $s_replace;
			}
		} 
		return $ret;		
	}

static function charclear($stringa){
		$allow=func_num_args()>1 ? func_get_arg(1) : "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM_0123456789";
        $ris="";
        for($i=0;$i<strlen($stringa);$i++)
                {
                if(strpos($allow,$stringa[$i])!==false){
			$ris=$ris.$stringa[$i];
                }
	}
	return $ris;
                }
	
static function  namedir($str){
		$str=trim(strtolower($str));
		$str=str_replace(chr(32),"-",$str);
		return $str=stringa::charclear($str, "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM-_0123456789");
		}
		
static function filename($str){
	$str = trim(strtolower($str));
	$str = str_replace(chr(32),'-',$str);
	return $str = stringa::charclear($str, 'qwertyuiopasdfghjklzxcvbnm-_0123456789');
}

static function get_extension($str){
	$ext = self::rightfromlast($str, '.');
	return $ext;
}
	
public static function leftfrom($stringa,$sep)  {
		$risultato=$stringa;
		if(strpos($stringa,$sep)!==false) $risultato=substr($stringa,0,strpos($stringa,$sep));
		return $risultato;
		}
	
public static function strip($str,$chr)	{
		$nstr="";
		for($i=0;$i<strlen($str);$i++){
			if($str[$i]!=$chr)
			$nstr.=	$str[$i];
	} 	
	return $nstr;
	} 
	
static function rcut($str,$int){
	return substr($str,0,strlen($str)-$int);
	}	
static function lcut($str,$int){
	return substr($str,$int,strlen($str));
}
	
static function leftfromlast($stringa,$sep){
		$risultato = $stringa;
        $pos_sep=strrpos($stringa,"$sep");
		if($pos_sep){
	        $risultato=substr($stringa,0,$pos_sep);
    	}
	    return $risultato;
        }	
public static function rightfrompold($stringa,$sep)
        {
		$risultato="";
		if(strrpos($stringa,$sep)!==false)  $risultato=substr($stringa,strrpos($stringa,$sep)+strlen($sep));
		return $risultato;
        }

public static function rightfrom($stringa,$sep){
	$risultato = $stringa;
	$pos_sep = strpos($stringa,"$sep");
	if($pos_sep){ // se trovo il separatore
		$diff=(strlen($stringa)-($pos_sep+strlen($sep)));
		$risultato=substr($stringa,$pos_sep+strlen($sep),$diff);
	}
	return $risultato;
}

public static function rfw($s, $sep){
	$ret = $s;
	$pos = strpos($s, $sep);
	if($pos >= 0 && $pos !== false){
		$pos += strlen($sep);
		$ret = substr($s, $pos);
	}
	return $ret;
}

public static function lfw($s, $sep){
	$ret = $s;
	$pos = strpos($s, $sep);
	if($pos >= 0){
		//$pos += strlen($sep);
		$ret = substr($s, 0, $pos);
	}
	return $ret;
}

public static function bfw($s, $sep1, $sep2){
	$ret = $s;
	$ret = self::rfw($s, $sep1);
	$ret2 = self::lfw($ret, $sep2);
	if(!empty($ret2)) $ret = $ret2; # SE NON TROVA IL DELIMITATORE DESTRO PRENDE FINO ALLA FINE DELLA STRINGA
	return $ret;
}

static function rightfromlast($stringa,$sep){
        $pos_sep=strrpos($stringa,"$sep");
        $diff=(strlen($stringa)-($pos_sep+strlen($sep)));
        $risultato=substr($stringa,$pos_sep+strlen($sep),$diff);
        return $risultato;
        }

public static function strbeetween($stringa,$sep1,$sep2){
		$risultato="";
	if(strpos($stringa,$sep1)!==false && strpos($stringa,$sep2)!==false && strpos($stringa,$sep1)<strpos($stringa,$sep2)){
			$pos_sep1=strpos($stringa,$sep1);
			$pos_sep2=strpos($stringa,$sep2);
			$diff=($pos_sep2-($pos_sep1+1));
			$risultato=substr($stringa,$pos_sep1+1,$diff);
			}
        return $risultato;
		}
		
public static function isUsername($lable,$sdata,$int){
	$err=false;
	$stringa=strtoupper($sdata);
	if(strlen($sdata)<$int)
		$err="$lable richiede un minino di $int caratteri"	;
	else{
		for($i=0;$i<strlen($stringa);$i++){
			if(!strpos("qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM_0123456789����� /:.,@-_",$stringa[$i])!==false){
				$err="$lable caratteri non ammessi"	;
				break;
			}
	}	
	}		
	return $err;
	}	

	
static function magic_quote($str){
	if(ini_get('magic_quotes_sybase')+get_magic_quotes_gpc())
		$str=stripslashes($str);
	return $str;
}			
		
	
static function cifra($ss, $key){
	$ris="";
	$app=array();
	for($i=0; $i<strlen($ss); $i++){
		$kk=ord($key[$i % strlen($key)]);
		$kk=($kk % 256);
		$x=(ord($ss[$i])) % 256;
		$x+=$kk+$i+71;
		
		$app[$i]=$x;
	}
	$ris=implode(" ",$app);
	return $ris;
}
		
static function decifra($ss, $key){
	$app=explode(" ",$ss);
	$ss="";
	for($i=0;$i<sizeof($app);$i++)
		{
		$kk=ord($key[$i % strlen($key)]);
		$kk=($kk % 256);
		$app[$i]-=(71+$i+$kk);
		$ss.=chr($app[$i]);
		}
	return $ss;
}
	
static function down_up($str, $mode = 'up, low', $change_html = 'true o false'){
	$aChars = array('a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E', 'f' => 'F', 'g' => 'G', 'h' => 'H', 'i' => 'I', 'j' => 'J', 'k' => 'K', 'l' => 'L', 'm' => 'M', 'n' => 'N', 'o' => 'O', 'p' => 'P', 'q' => 'Q', 'r' => 'R', 's' => 'S', 't' => 'T', 'u' => 'U', 'v' => 'V', 'w' => 'W', 'x' => 'X', 'y' => 'Y', 'z' => 'Z');
	
//	, '�'=>'�', '�'=>'�', 'c'=>'C', 'c'=>'C', 'd'=>'�'
	
	$aHtml_low = array(
			'à' => "&agrave;",	"è" => "&egrave;",	'ì' => "&igrave;",	"ò" => "&ograve;",	"ù" => "&ugrave;",	"á" => "&aacute;", 	"é" => "&eacute;", 	"í" => "&iacute;", 	"ó" => "&oacute;", 	"ú" => "&uacute;", 	"ý" => "&yacute;",
			"à" => "&agrave;", 	"è" => "&egrave;", 	"ì" => "&igrave;", 	"ò" => "&ograve;",	"ù" => "&ugrave;",	"á" => "&aacute;",  "é" => "&eacute;", 	"í" => "&iacute;", 	"ó" => "&oacute;",  "ú" => "&uacute;", 	"ý" => "&yacute;", "š" => "&#353;", "ž" => "&#382;", "č" => "&#269;", "ć" => "&#263;", "đ" => "&#273;");

	$aHtml_up = array(
			'À' => "&Agrave;",	"È" => "&Egrave;",	'Ì' => "&Igrave;",	"Ò" => "&Ograve;",	"Ù" => "&Ugrave;",	"Á" => "&Aacute;", 	"É" => "&Eacute;", 	"Í" => "&Iacute;", 	"Ó" => "&Oacute;", 	"Ú" => "&Uacute;", 	"Ý" => "&Yacute;",
			"À" => "&Agrave;", 	"È" => "&Egrave;", 	"Ì" => "&Igrave;", 	"Ò" => "&Ograve;",	"Ù" => "&Ugrave;",	"Á" => "&Aacute;",  "É" => "&Eacute;", 	"Í" => "&Iacute;", 	"Ó" => "&Oacute;",  "Ú" => "&Uacute;", 	"Ý" => "&Yacute;", "Š" => "&#352;", "Ž" => "&#381;", "Č" => "&#268;", "Ć" => "&#262;", "Đ" => "&#272;");
	
	if($mode == 'low'){
		$aChars = array_flip($aChars);
		if($change_html) $aChars = array_merge($aChars, $aHtml_low);
	}
	else{
		if($change_html) $aChars = array_merge($aChars, $aHtml_up);
	}
	$aSrc = array(); $aRpl = array();
	foreach($aChars as $src => $rpl){
		$aSrc[] = utf8_encode($src);
		$aRpl[] = utf8_encode($rpl);
	}
	$str = str_replace($aSrc, $aRpl, $str);
	return $str;
}


static function uppercase_first($str=''){
	$str = self::down_up($str, 'low', true);
	$str = ucfirst($str);
	return $str;
}

	
static function tbl2id($str){
	return 'ID_'.strtoupper(substr($str,0,strlen($str)-1));
}

static function id2tbl($str){ # NON USARE PIU'
	return strtolower(substr($str, 3)).'s';

}

static function id2table($str){
	return strtolower(substr($str, 3)).'s';
}

static function id2fld($str){
	return substr($str, 3, strlen($str));
}

static function tbl2field($str){
	return strtoupper(substr($str,0,strlen($str)-1));
}
static function field2table($str){
	return strtolower($str).'s';
}
	
static function togli_ultimo($str){
	return substr($str,0,strlen($str)-1);
}

static function togli_ultimi($str, $n){
	return substr($str,0,strlen($str)-$n);
}
	
public static function isEmail($val){
	$err=false;
	$regexp = "/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i";
	if (!preg_match($regexp, $val))
	$err=EMAIL." non valida";
	return $err;
}	

public static function is_email($val){
	if(preg_match("/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i", $val) !== false) return true;
	else return false;
}	

static function valid_email($s=''){
	return preg_match("/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i", $s);
}

static function mk_http($s){
	$s = self::rfw($s, 'http://');
	$s = 'http://'.$s;
	return $s;
}

static function strcut($str, $rplstr, $lng){
	$str = strip_tags($str);
	if(strlen($str)>$lng){
		if(strlen($rplstr)>$lng) $lng=strlen($rplstr);
      $str=substr($str, 0 ,$lng-strlen($rplstr));
      
     	if(strlen(strrchr($str, '&'))>0 && strlen(strrchr($str, '&'))<8){ # RISOLVO LO SMEZZAMENTO DEI CARATTERI HTML
        $str = leftfromlast($str,'&');
      }
		$str.=$rplstr;
	}
 	return $str;
}

static function minuscolo($str){
	return strtolower($str);
}

static function get_conffile($s){
	$s = self::leftfromlast(self::leftfromlast($s, '.'),'_');
	return $s.'_conf.php';
}
static function get_constant($s, $lnk){
	$s = strtoupper($s);
	if(defined($s)) $ret = constant($s);
	else{
		if($lnk = true){
			$ret = io::a('lablesites_c.php', array('mknew' => strtoupper($s)), 'Inserisci '.strtoupper($s), array('target' => '_blank'));
		}
		else{
			$ret = $s;
		}
	}
	return $ret;	
}

static function zero_fill($s, $n){
	$len = strlen($s);
	while($len < $n){
		$s = '0'.$s;
		$len += 1;
	}
	return $s;
}

static function file_size($path){
	$filesize = filesize($path)/1024;
	return num::format($filesize,0,'','').' Kb';
}

static function random_alfanum($n){
	if(empty($n)) $n = 10;
	$s = "";
	for($i = 0; $i < $n; $i++){
		do{
			$char = ceil(rand(48,122));
		}while(!((($char >= 48) && ($char <= 57)) || (($char >= 65) && ($char <= 90)) || (($char >= 97) && ($char <= 122))));
		$s .= chr($char);
	}
	return $s;
}

}
?>