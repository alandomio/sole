<?php
# V.0.1.8
class stringa
	{
	function spancol($hex,$str,$fontcol){
	$hex=strtoupper($hex);
	$fontcol=strtoupper($fontcol);
	$fontcol=empty($fontcol) ? "#000000": $fontcol;
	
		//$fontcol=$hex=="#000000" ? "#FFFFFF" : "#000000";
		return"<span style=\"padding:3px;background-color:$hex; font-weight:bold; color:$fontcol; border:solid 3px $fontcol\">$str</span>";}
	
	function ico($ico,$val){
		$val=intval($val);
		if($ico=="bit"){
			return ($val==1 ? IMG_SI : IMG_NO);
			
			}
		}
		
	function get_filename($s){
	$max_lenght = 42;
	$s = str_replace('-', ' ', $s);
	$s = self::remove_spaces(strtolower($s));
	$s = str_replace(chr(32),'-',$s);
	
	if(strpos($s, '-') !== false){
		$i = 0;
		while(strlen($s) > $max_lenght && $i < 10 ){
			$s = self::leftfromlast($s, '-');
			$i ++;
		}
	}
	if(strlen($s) > $max_lenght){
		$s = strcut($s, '', $max_lenght);
	}
	$s = self::clear($s, "qwertyuiopasdfghjklzxcvbnm-0123456789");
	$s = str_replace(array('---', '--'), '-', $s);
	
	return $s;
}

	function no_tags($str){
		$tags_list = array('<br>', '<br/>', '<p>', '</p>', '  ', '   ');
			foreach($tags_list as $strip){
			$str = str_replace($strip, ' ', $str);
		}
		return $str;
	}
	
	function comma2dec($str){
		return $str = str_replace(',', '.', $str);
	}
	function punto2virgola($str){
		return $str = str_replace('.', ',', $str);
	}
	
	function alphanum_replace_with($str, $s_replace){
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

			
	function charclear($stringa)
        {
		$allow=func_num_args()>1 ? func_get_arg(1) : "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM_0123456789";
        $ris="";
        for($i=0;$i<strlen($stringa);$i++)
                {
                if(strpos($allow,$stringa[$i])!==false){
               	 $ris=$ris.$stringa[$i];}
                }
        return $ris;  }	
		
	function dirty($stringa)
        {
		$allow=func_num_args()>1 ? func_get_arg(1) : "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM_0123456789";
        $ris="";
        for($i=0;$i<strlen($stringa);$i++)
                {
                if(strpos($allow,$stringa[$i])!==false);
				else $ris=$ris.$stringa[$i];
                }
	
        return $ris;  }	
		
/*	function  namefile($args,$sep){
		$str="";
		foreach($args as $arg){
			$arg=trim(strtolower($arg));
			$arg=str_replace(chr(32),$sep,$arg)."_";
			$str.=$arg;
			}
		$str=stringa::rcut($str,1);
		return $str=stringa::charclear($str,"qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM_0123456789");
		}*/
/*		function  dnamefile($pref,$args,$sep,$length,$ext){
		$str="";
		foreach($args as $arg){
			$arg=trim(strtolower($arg));
			$arg=str_replace(chr(32),$sep,$arg)."_";
			$str.=$arg;
			}
		$str=stringa::rcut($str,1);
		$str=stringa::charclear($str,"qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM_0123456789");
		$str=substr($pref.$sep.$str,0,$length-(strlen($ext)+1)).".".$ext;
		return $str;
		}	*/	
		
	function  namedir($str){
		$str=trim(strtolower($str));
		$str=str_replace(chr(32),"-",$str);
		return $str=stringa::charclear($str, "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM-_0123456789");
		}
		
function filename($str){
	$str = trim(strtolower($str));
	$str = str_replace(chr(32),'-',$str);
	return $str = stringa::charclear($str, 'qwertyuiopasdfghjklzxcvbnm-_0123456789');
}

function get_extension($str){
	$ext = self::rightfromlast($str, '.');
	return $ext;
}
	
	public function a($a){
		if(!is_array($a)){echo "$a not array<br>"; return "";}
		foreach ($a as $k=>$v) echo "$k=>$v<br>";
	}
		public function ar($a){
		
		if(!is_array($a)){echo "$a not array<br>"; return "";}
		foreach ($a as $k=>$v) echo "ar: $k=>$v<br>";
	}
	public function leftfrom($stringa,$sep)  {
		$risultato=$stringa;
		if(strpos($stringa,$sep)!==false) $risultato=substr($stringa,0,strpos($stringa,$sep));
		return $risultato;
		}
	
	public function chkusername($sdata){
	$err=false;
	$stringa=strtoupper($sdata);
	for($i=0;$i<strlen($stringa);$i++){
		if(strpos("QWERTYUIOPASDFGHJKLZXCVBNM_.@0123456789",$stringa[$i])!==false){
			$err=true;
			break;
		}
	}
	return $err;}	
	
	public function chkpassword($sdata){
	$err=false;
	$stringa=strtoupper($sdata);
	echo $stringa;
	for($i=0;$i<strlen($stringa);$i++){
		if(strpos("QWERTYUIOPASDFGHJKLZXCVBNM_0123456789",$stringa[$i])!==false)
			$err=true;
			break;
	}
	return $err;
}
	public function strip($str,$chr)	{
		$nstr="";
		for($i=0;$i<strlen($str);$i++){
			if($str[$i]!=$chr)
				$nstr.=	$str[$i];}
		return $nstr;}
	
public function name($str){
	return ucfirst(strtolower($str));
	} 
public function iname($surn,$name){
	return ucfirst(strtolower($surn)).".".ucfirst(strtolower(substr($name,0,1)));
	} 	
	public function bit2img($int,$img){
	if(is_numeric($int))
		$int=$int>0 ? $img:'';
	else if(is_bool($int))
		$int=$int==true? $img:'';
	else 
		$int="--";
	return $int;
	}
	
	
	
public function bit2str($int){
	if(is_numeric($int))
		$int=$int>0 ?'S':'N';
	else if(is_bool($int))
		$int=$int==true? 'S':'N';
	else 
		$int="--";
	return $int;
	} 
public function bit2strn($int){
	if(is_numeric($int))
		$int=$int>0 ?'S&igrave;':'No';
	else if(is_bool($int))
		$int=$int==true? 'S&igrave':'No';
	else 
		$int="--";
	return $int;
	} 
	
function _abstract($str, $etc, $mlenght)
	{
	$nstr="";
	if(strlen($str)<=0 || strlen($str)<$mlenght) 
		$nstr=$str;
	else if(strlen($str)+strlen($etc)<=$mlenght)
		$nstr=$str.$etc;
	//else if(strlen($str)>$mlenght)	
	else
		$nstr=$str=substr($str, 0 ,$mlenght-strlen($etc)).$etc;
	return $str;
	}	
function rcut($str,$int){
	return substr($str,0,strlen($str)-$int);
	}	
function lcut($str,$int){
	return substr($str,$int,strlen($str));}
	
function leftfromlast($stringa,$sep){
		$risultato = $stringa;
        $pos_sep=strrpos($stringa,"$sep");
		if($pos_sep){
	        $risultato=substr($stringa,0,$pos_sep);
    	}
	    return $risultato;
        }	
	public function rightfrompold($stringa,$sep)
        {
		$risultato="";
		if(strrpos($stringa,$sep)!==false)  $risultato=substr($stringa,strrpos($stringa,$sep)+strlen($sep));
		return $risultato;
        }

public function rightfrom($stringa,$sep){
	$risultato = $stringa;
	$pos_sep = strpos($stringa,"$sep");
	if($pos_sep){ // se trovo il separatore
		$diff=(strlen($stringa)-($pos_sep+strlen($sep)));
		$risultato=substr($stringa,$pos_sep+strlen($sep),$diff);
	}
	return $risultato;
}

public function rfw($s, $sep){
	$ret = $s;
	$pos = strpos($s, $sep);
	if($pos >= 0 && $pos !== false){
		$pos += strlen($sep);
		$ret = substr($s, $pos);
	}
	return $ret;
}

public function lfw($s, $sep){
	$ret = $s;
	$pos = strpos($s, $sep);
	if($pos >= 0){
		//$pos += strlen($sep);
		$ret = substr($s, 0, $pos);
	}
	return $ret;
}

public function bfw($s, $sep1, $sep2){
	$ret = $s;
	$ret = self::rfw($s, $sep1);
	$ret2 = self::lfw($ret, $sep2);
	if(!empty($ret2)) $ret = $ret2; # SE NON TROVA IL DELIMITATORE DESTRO PRENDE FINO ALLA FINE DELLA STRINGA
	return $ret;
}




function rightfromlast($stringa,$sep)
        {
        $pos_sep=strrpos($stringa,"$sep");
        $diff=(strlen($stringa)-($pos_sep+strlen($sep)));
        $risultato=substr($stringa,$pos_sep+strlen($sep),$diff);
        return $risultato;
        }
	public function strbeetween($stringa,$sep1,$sep2)
        {
		$risultato="";
		if(strpos($stringa,$sep1)!==false && strpos($stringa,$sep2)!==false && strpos($stringa,$sep1)<strpos($stringa,$sep2))
			{
			$pos_sep1=strpos($stringa,$sep1);
			$pos_sep2=strpos($stringa,$sep2);
			$diff=($pos_sep2-($pos_sep1+1));
			$risultato=substr($stringa,$pos_sep1+1,$diff);
			}
        return $risultato;
		}
		
public function isUsername($lable,$sdata,$int){
	$err=false;
	$stringa=strtoupper($sdata);
	if(strlen($sdata)<$int)
		$err="$lable richiede un minino di $int caratteri"	;
	else{
		for($i=0;$i<strlen($stringa);$i++){
			if(!strpos("qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM_0123456789����� /:.,@-_",$stringa[$i])!==false){
				$err="$lable caratteri non ammessi"	;
				break;}}}
	return $err;			
	}	
	
public function isPswRpt($lable,$sdata,$sdata2,$int){
	$err=false;
	$sdata=strtoupper($sdata);
	$sdata2=strtoupper($sdata2);
	if(strlen($sdata)<$int)
		$err="$lable richiede un minino di $int caratteri"	;
	else if($sdata!=$sdata2)
		$err="$lable password e ripetizione password devono essere uguali"	;	
	else{
		for($i=0;$i<strlen($sdata);$i++){
			if(!strpos("qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM_0123456789����� /:.,@-_",$sdata[$i])!==false){
				$err="$lable caratteri non ammessi"	;
				break;}}}
	return $err;			
	}		
		
		
 public function isLenght($lable,$val,$int)
	{
	$err=false;
	if(!empty($int)){
		if(strtolower($int)=="!null" && strlen($val)==0)
			$err="$lable e\x60 un campo obbligatorio"	;
		else if ( strlen($val)<$int )
			$err="$lable richiede un minino di $int caratteri"	;}
	return $err;
	}	

function myformat($str){
	if(ini_get('magic_quotes_sybase')+get_magic_quotes_gpc())
		$str=stripslashes($str);
	return mysql_real_escape_string($str);
}			
	
function myformatrim($str){
	if(ini_get('magic_quotes_sybase')+get_magic_quotes_gpc())
		$str=stripslashes($str);
	return trim(mysql_real_escape_string($str));
}			
		
function magic_quote($str){
if(ini_get('magic_quotes_sybase')+get_magic_quotes_gpc())
	 $str=stripslashes( $str);
return $str;}
	
	
function cifra($ss, $key){
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
		
function decifra($ss, $key){
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

function cifra_old($ss, $key)
	{
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
		
function decifra_old($ss, $key){
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
	
function ekotitle($str){
	echo $str." - ".NOME_SITO;
}

function char_html($str){
	# USO L'ARRAY aSpecial PER AVERE EVIDENZA DEL CARATTERE E IL SUO SOSTITUTO
	$aSpecial = array(
	'�' => '&agrave;',
	'�' => '&egrave;',
	'�' => '&igrave;',
	'�' => '&ograve;',
	'�' => '&ugrave;',
	);
	$aSrc = array(); $aRpl = array();
	foreach($aSpecial as $src => $rpl){
		$aSrc[] = $src;
		$aRpl[] = $rpl;
	}
	$str = str_replace($aSrc, $aRpl, $str);
	return $str;
}

function down_up($str, $mode = 'up, low', $change_html = 'true o false'){
	$aChars = array('a' => 'A', 'b' => 'B', 'c' => 'C', 'd' => 'D', 'e' => 'E', 'f' => 'F', 'g' => 'G', 'h' => 'H', 'i' => 'I', 'j' => 'J', 'k' => 'K', 'l' => 'L', 'm' => 'M', 'n' => 'N', 'o' => 'O', 'p' => 'P', 'q' => 'Q', 'r' => 'R', 's' => 'S', 't' => 'T', 'u' => 'U', 'v' => 'V', 'w' => 'W', 'x' => 'X', 'y' => 'Y', 'z' => 'Z');
	
//	, '�'=>'�', '�'=>'�', 'c'=>'C', 'c'=>'C', 'd'=>'�'
	
	$aHtml_low = array('�' => "&agrave;",	"�" => "&egrave;",	'�' => "&igrave;",	"�" => "&ograve;",	"�" => "&ugrave;",	"�" => "&aacute;", 	"�" => "&eacute;", 	"�" => "&iacute;", 	"�" => "&oacute;", 	"�" => "&uacute;", 	"�" => "&yacute;",
	"�" => "&agrave;", 	"�" => "&egrave;", 	"�" => "&igrave;", 	"�" => "&ograve;",	"�" => "&ugrave;",	"�" => "&aacute;",  	"�" => "&eacute;", 	"�" => "&iacute;", 	"�" => "&oacute;",  	"�" => "&uacute;", 	"�" => "&yacute;", "�" => "&#353;", "�" => "&#382;", "C" => "&#269;", "C" => "&#263;", "�" => "&#273;");
	
	$aHtml_up = array('�' => "&Agrave;",	"�" => "&Egrave;",	'�' => "&Igrave;",	"�" => "&Ograve;",	"�" => "&Ugrave;",	"�" => "&Aacute;", 	"�" => "&Eacute;", 	"�" => "&Iacute;", 	"�" => "&Oacute;", 	"�" => "&Uacute;", 	"�" => "&Yacute;",
	"�" => "&Agrave;", 	"�" => "&Egrave;", 	"�" => "&Igrave;", 	"�" => "&Ograve;",	"�" => "&Ugrave;",	"�" => "&Aacute;",  	"�" => "&Eacute;", 	"�" => "&Iacute;", 	"�" => "&Oacute;",  	"�" => "&Uacute;", 	"�" => "&Yacute;", "�" => "&#352;", "�" => "&#381;", "c" => "&#268;", "c" => "&#262;", "d" => "&#272;");
	
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

function html2string($str){ # TRASFORMA I CARATTERI HTML IN STRINGA
	$aChars = array('�' => "&agrave;",	"�" => "&egrave;",	'�' => "&igrave;",	"�" => "&ograve;",	"�" => "&ugrave;",	"�" => "&aacute;", 	"�" => "&eacute;", 	"�" => "&iacute;", 	"�" => "&oacute;", 	"�" => "&uacute;", 	"�" => "&yacute;",	"�" => "&Agrave;", 	"�" => "&Egrave;", 	"�" => "&Igrave;", 	"�" => "&Ograve;",	"�" => "&Ugrave;",	"�" => "&Aacute;",  	"�" => "&Eacute;", 	"�" => "&Iacute;", 	"�" => "&Oacute;",  	"�" => "&Uacute;", 	"�" => "&Yacute;", "'" => "&rsquo;"); 

# IMPOSTO CARATTERI PARTICOLARI
	$aSrc[] = utf8_encode("&rdquo;");
	$aRpl[] = utf8_encode('"');

	$aSrc[] = utf8_encode("&ldquo;");
	$aRpl[] = utf8_encode('"');

	$aSrc[] = utf8_encode("&ndash;");
	$aRpl[] = utf8_encode("-");


	foreach($aChars as $s => $html){
		$aSrc[] = utf8_encode($html);
		$aRpl[] = utf8_encode($s);
	}
	$str = str_replace($aSrc, $aRpl, $str);
	return $str;
}

function uppercase_first($str=''){
	$str = self::down_up($str, 'low', true);
	$str = ucfirst($str);
	return $str;
}

function uppercase_word($str=''){
	$str = self::down_up($str, 'low', true);
	$str = ucwords($str);
	return $str;
}	
	
function tbl2id($str){
	return 'ID_'.strtoupper(substr($str,0,strlen($str)-1));
}

function id2tbl($str){ # NON USARE PIU'
	//return strtolower(substr($str, 3+(strlen('ID_')), strlen($str))).'s';
	return strtolower(substr($str, 3)).'s';

}

function id2table($str){
	return strtolower(substr($str, 3)).'s';
}

function id2fld($str){
	return substr($str, 3, strlen($str));}

function tbl2field($str){
	return strtoupper(substr($str,0,strlen($str)-1));
}
function field2table($str){
	return strtolower($str).'s';
}
	
function togli_ultimo($str){
	return substr($str,0,strlen($str)-1);
}

function togli_ultimi($str, $n){
	return substr($str,0,strlen($str)-$n);
}
	
public function isEmail($val){
	$err=false;
	$regexp = "/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i";
	if (!preg_match($regexp, $val))
	$err=EMAIL." non valida";
	return $err;
}	

public function is_email($val){
	if(preg_match("/^[a-z0-9]+([_\\.-][a-z0-9]+)*@([a-z0-9]+([\.-][a-z0-9]+)*)+\\.[a-z]{2,}$/i", $val) !== false) return true;
	else return false;
}	

function mk_http($s){
	$s = self::rfw($s, 'http://');
	$s = 'http://'.$s;
	return $s;
}

function strcut($str, $rplstr, $lng){
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

function minuscolo($str){
	return strtolower($str);
}

function normalizza($s){
	if(empty($s)) $s= '';
	$find = array('�','�','c','c','d','�','�','C','C','�');
	$repl = array('s','z','c','c','d','S','Z','C','C','D');
	return str_replace($find, $repl, $s);
}

function prepara_dizionario($str){
	$aRet = array();
	$str = self::normalizza($str); # TOLGLIE CARATTERI STRANI �, �, c..
	$str = self::minuscolo($str);
	$str = strip_tags($str);
	
	$str = self::charclear($str,'qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM_0123456789����� /:.,@');
	if(!empty($str))$aRet = explode(' ', $str);
	return $aRet;
}

function adjust_query($qMain){
	$aMain = explode(',', $qMain);
	foreach($aMain as $k => $v){
		print $v.BR;
	}
}
function get_conffile($s){
	$s = self::leftfromlast(self::leftfromlast($s, '.'),'_');
	return $s.'_conf.php';
}
function get_constant($s, $lnk){
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

function remove_first($s){
	if(empty($s)) return false;
	else return substr($s, 1, strlen($s));
}

function remove_last($s){
	if(empty($s)) return false;
	else return substr($s, 0, strlen($s)-1);
}


function remove_firstlast($s, $char){
	if(empty($s)) return false;
	else{
		if($s[0] == $char){	$s = self::remove_first($s); }
		if($s[strlen($s)-1] == $char){	$s = self::remove_last($s); }
		return $s;
	}
}

function zero_fill($s, $n){
	$len = strlen($s);
	while($len < $n){
		$s = '0'.$s;
		$len += 1;
	}
	return $s;
}

function file_size($path){
	$filesize = filesize($path)/1024;
	return num::format($filesize,0,'','').' Kb';
}

function random_alfanum($n){
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