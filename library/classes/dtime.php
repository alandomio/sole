<?php
ob_start();
class dtime{
	public $lgdt;
	public $Y;
	public $M;
	public $D;
	public $H;
	public $I;
	public $S;
	public $C;
	public $E;
	public $K;
	public $aD;
	public $iD;
	public $sD;
	public $fD;
	public $settgiorno;
	public $sgiorno;
	public $mese;
	public $val;
	public $ymd;
	public $isFest;
	public $calendarday;
	public $iGiorno;
	public $iSett;
	public $sGiorno;
	public $isodt;
	public $isod;
	public $isot;
	public $day_greg;
	public $week_greg;
	public $month_greg;	
	public $pos_greg;
	public $err;	
	public $err_pos;
	public $festivi;
	
public function dtime($lgdt){
	$this->Y="";
	$this->M="";
	$this->D="";
	$this->H="";
	$this->I="";
	$this->S="";
	$this->lgdt=$lgdt;

	$this->C=array_fill(0,42,"");
	$this->E=array( 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );
	$this->iE=(-1);
	$this->fD=array();
	$this->iD=array();
	$this->sD=array();
	$this->aD = array( "", "01", "02", "03", "04", "05", "06", "07","08", "09", "10", "11", "12", "13", "14", "15","16", "17", "18", "19", "20", "21", "22", "23","24", "25", "26", "27", "28", "29", "30", "31" );
	$this -> settgiorno=array("Lunedi\x60", "Martedi\x60", "Mercoledi\x60", "Giovedi\x60", "Venerdi\x60", "Sabato","Domenica");
	$this -> sgiorno =array("Lun", "Mar", "Mer", "Gio", "Ven", "Sab","Dom");
	$this -> mese=array("01"=>"Gennaio","02"=> "Febbraio", "03"=>"Marzo", "04"=>"Aprile","05"=> "Maggio", "06"=>"Giugno","07"=>"Luglio", "08"=>"Agosto","09"=> "Settembre", "10"=>"Ottobre", "11"=>"Novembre", "12"=>"Dicembre");
	$this -> imese=array("","Gennaio","Febbraio","Marzo","Aprile","Maggio","Giugno","Luglio","Agosto","Settembre","Ottobre", "Novembre", "Dicembre");
	$this -> val=false;
	$this -> ymd=false;
	$this -> isFest=NULL;
	$this -> calendarday=false;
	$this -> iGiorno=NULL;
	$this -> iSett=NULL;
	$this -> sGiorno=NULL;
	$this -> iso=false;
	$this -> day_greg=array("val"=>$this->val,"ymd"=>$this->ymd,"isFest"=>$this->isFest,"calendarday"=>$this->calendarday,"iGiorno"=>$this->iGiorno,"iSett"=>$this->iSett);
	$this -> week_greg=array_fill(0,6,array_fill(0,7,$this->day_greg));
	$this -> month_greg=array_fill(0,42,$this->day_greg);
	$this -> pos_greg=(-1);
	$this -> err=false;
	$this -> err_pos=false;
	$this -> isodt=false;
	$this -> isot=false;
	$this -> isom=false;
	$this -> festivi=array();
	
	self::err();
	self::festivi();
	self::pop_C();
	self::pop_greg();
	if($this->err==false){
		$this->Y=str_pad($this->Y,4,"0",STR_PAD_LEFT);	
		$this->M=str_pad($this->M,2,"0",STR_PAD_LEFT);
		$this->D=str_pad($this->D,2,"0",STR_PAD_LEFT);
		$this->H=str_pad($this->H,2,"0",STR_PAD_LEFT);
		$this->I=str_pad($this->I,2,"0",STR_PAD_LEFT);
		$this->S=str_pad($this->S,2,"0",STR_PAD_LEFT);
		$this->isodt=$this->Y.'/'.$this->M.'/'.$this->D.chr(32).$this->H.':'.$this->I.':'.$this->S;
		$this->isod=$this->Y.'/'.$this->M.'/'.$this->D;
		$this->isot=$this->H.':'.$this->I.':'.$this->S;
		}
	} # end __constructor
	
public function add_days($days){
	$ret = '';
	$timestamp = mktime(0,0,0,$this -> M,($this -> D + $days),$this -> Y);
	$ret = date("d-m-Y", $timestamp);
	return $ret;
}

public function add_days_db($days){
	$ret = '';
	$timestamp = mktime(0,0,0,$this -> M,($this -> D + $days),$this -> Y);
	$ret = date("Y-m-d", $timestamp);
	return $ret;
}


public function my2iso($mydate){
	if(empty($mydate)) return false;
	$mydate=stringa::strip($mydate,"-");
	$mydate=stringa::strip($mydate," ");
	return substr($mydate,6,2)."/".substr($mydate,4,2)."/".substr($mydate,0,4);
	}
	
public function my2isodt($mydate){
	if(empty($mydate)) return false;
	$mydate=stringa::strip($mydate,"-");
	$mydate=stringa::strip($mydate," ");
	$mydate=stringa::strip($mydate,":");
	return substr($mydate,6,2)."/".substr($mydate,4,2)."/".substr($mydate,0,4)." ".substr($mydate,8,2).":".substr($mydate,10,2);
}

public function lessgraf($mydate){
	if(empty($mydate)) return false;
	$mydate=stringa::strip($mydate,"-");
	$mydate=stringa::strip($mydate," ");
	$mydate=stringa::strip($mydate,":");
	return $mydate;
}
	public function my2isodts($mydate){
	if(empty($mydate)) return false;
	$mydate=stringa::strip($mydate,"-");
	$mydate=stringa::strip($mydate," ");
	$mydate=stringa::strip($mydate,":");
	return substr($mydate,6,2)."/".substr($mydate,4,2)."/".stringa::lcut(substr($mydate,0,4),2)." ".substr($mydate,8,2).":".substr($mydate,10,2);
	}	
	
	function my2tuning($mydate,$is_cut){
	
	if(empty($mydate)) return false;
	$mydate=stringa::strip($mydate,"-");
	$mydate=stringa::strip($mydate," ");
	$mese=array("","Gennaio","Febbraio","Marzo","Aprile","Maggio","Giugno","Luglio","Agosto","Settembre","Ottobre", "Novembre", "Dicembre");
	$mes=$is_cut==1 ? substr($mese[intval(substr($mydate,4,2))],0,3) : $mese[intval(substr($mydate,4,2))];
	return array(substr($mydate,6,2),$mes,substr($mydate,0,4));
	}
	
public function	mydate2lg($mydate){
	$mydate=stringa::strip($mydate,"-");
	$mydate=stringa::strip($mydate,":");
//	$mydate=stringa::strip($mydate,"0");
	return substr($mydate.="00000000",0,14);
	}
	
public function date2str($frm){
	$str="";
	for($i=0;$i<strlen($frm);$i++){
		switch ($s=$frm[$i]) {
			case "Y":
				$str.=$this->Y;
				break;
			case "y":
				$str.=$this->y;
				break;
			case "m":
				$str.=$this->M;
				break;
			case "M":
				$str.=$this->mese[$this->M];
				break;	
			case "d":
				$str.=$this->D;
				break;	
			case "D":
				$str.=$this->sGiorno;
				break;	
			case "F":
				$str.=$this->F;
				break;	
			case "H":
				$str.=$this->H;
				break;	
			case "i":
				$str.=$this->I;
				break;	
			case "s":
				$str.=$this->S;
				break;			
			default:
				$str.=$s;}
		}
	return $str;
	}

	protected function err(){
		if(is_array($this->lgdt)){
			$this->Y=$this->lgdt[0];
			$this->M=$this->lgdt[1];
			$this->D=$this->lgdt[2];
			$this->H=$this->lgdt[3];
			$this->I=$this->lgdt[4];
			$this->S=$this->lgdt[5];
			$this->lgdt=implode("",$this->lgdt);
			
			}
		else	{
			$this->lgdt=self::mydate2lg($this->lgdt);
			if( ($this->lgdt!=false || $this->lgdt!="") && strlen($this->lgdt)<14 || strlen($this->lgdt)>14 )
				$this->err=" data errata"; 
			else 	
				
						{
						
				$this->Y=substr($this->lgdt,0,4);
				$this->M=substr($this->lgdt,4,2);
				$this->D=substr($this->lgdt,6,2);
				$this->H=substr($this->lgdt,8,2);
				$this->I=substr($this->lgdt,10,2);
				$this->S=substr($this->lgdt,12,2);}}
		$this->y=substr($this->Y,2,2);
		if($this->err==false){
			if(!is_numeric($this->Y) || is_float($this->Y) || $this->Y<1582 || $this->Y>9999){
				$this->err="Anno non corretto"; //1582 al 9999
				$this->err_pos="Y";	}
			else 	{
				$M=str_pad($this->M,2,"0",STR_PAD_LEFT);
				if(!array_key_exists($M,$this->mese)){
					$this->err="Mese non corretto";
					$this->err_pos="M";}
				else	{
					$D=str_pad($this->D,2,"0",STR_PAD_LEFT);
					if(!array_search($D,$this->aD)){
						$this->err="Giorno non corretto";
						$this->err_pos="D";	}
					else if ((int)$this->D == 31 && ((int)$this->M == 4 || (int)$this->M == 6 || (int)$this->M == 9 || (int)$this->M == 11)){ 
						$this->err="31 in un mese di 30 giorni";
						$this->err_pos="D";}
					else if ((int)$this->D >= 30 && (int)$this->M == 2){
						$this->err="30 o 31 in Febbraio";
						$this->err_pos="D";}
					elseif ((int)$this->M == 2 && (int)$this->D == 29 && !((int)$this->Y % 4 == 0 && ((int)$this->Y % 100 != 0 || (int)$this->Y % 400 == 0))){
						$this->err="29 Feb in anno non bisestile";
						$this->err_pos="D";}
					else 
						if(!is_numeric($this->H) || is_float($this->H) || $this->H<0 || $this->H>23)
							$this->err="Ora errata";
						else if(!is_numeric($this->I) || is_float($this->I) || $this->I<0 || $this->I>59)
							$this->err="Minuti errati"; 
						else if(!is_numeric($this->S) || is_float($this->S) || $this->S<0 || $this->S>59)
							$this->err="Secondi errati";
						else 
							$this->err = false;		}   }	}
		//echo "err".$this->err;
		}	
	
	protected function pop_greg()		{
		if($this->err==true)  return""; 
		for($n42=0;$n42<count($this->C);$n42++)
			{
			$isFest=false;
			if(is_numeric($this->C[$n42])) {
				if($this->D==$this->C[$n42]) $this->pos_greg=$n42;
				$ymd=$this->Y.$this->M.$this->C[$n42];
				$calendarday=$this->settgiorno[$n42%7].' '.$this->C[$n42].' '.$this->mese[	$this->M=str_pad($this->M,2,"0",STR_PAD_LEFT)].' '.$this->Y;
				if(array_key_exists($this->M.$this->C[$n42],$this->festivi)) {
					$isFest=true;
					$calendarday.=" ".$this->festivi[$this->M.$this->C[$n42]];	}
				if($n42%7==6 ) 
					$isFest=true;
				$this->month_greg[$n42]=array("val"=>$this->C[$n42],"ymd"=>$ymd,"isFest"=>$isFest,"calendarday"=>$calendarday,"iGiorno"=>($n42%7),"iSett"=>floor($n42/7)) ;} # end if
			$this->week_greg[floor($n42/7)][$n42%7]=$this->month_greg[$n42];}# end for
		if($this->pos_greg==-1) {
			$this->D=max($this->C);
			$this->pos_greg=array_search($this->D,$this->C);}
		$day=$this->month_greg[$this->pos_greg];
		$this->val=$day['val'];
		$this->ymd=$day['ymd'];
		$this->isFest=$day['isFest'];
		$this->calendarday=$day['calendarday'];
		$this->F=array_key_exists($this->M.$this->D,$this->festivi) ? $this->festivi[$this->M.$this->D] : "";
		$this->iGiorno=$day['iGiorno'];
		$this->iSett=$day['iSett'];	
		$this->sGiorno=$this->settgiorno[$this->iGiorno];
		
		}
	
	protected function pop_C()	{
		if($this->err==true) return"";
			$n42;$W;$X;$Z;
			$J=  367 *$this->Y - floor( 7*($this->Y + floor( ($this->M + 9 ) / 12 ) ) / 4 ) +  floor( 275 *$this->M / 9 ) + 1721031;  
			$this->K=0;
			if($this->M<=2) $this->K=-1;
			$J=$J-floor(3*(floor(($this->Y+$this->K)/100)+1)/4);
			$this->K=$this->E[$this->M-1];
			if($this->M==2)	{
				$W=floor($this->Y-100*floor($this->Y/100));
				$X=floor($this->Y-4*floor($this->Y/4));
				$Z=floor($this->Y-400*floor($this->Y/400));
				if( $X == 0 ){
					if( !( $W == 0 && $Z != 0 ) )	$this->K = 29;}}
			$X = $J - 7*floor( $J / 7 ) - 1;
			for( $n42 = 0; $n42 <= $this->K; $n42++ )
				$this->C[$n42+$X] = $this->aD[$n42];
			$this->iD=$this->aD;
			array_shift($this->iD);
			$a=array_chunk($this->iD,$this->K,true);
			$this->iD=$a[0];
			self::ordsunday();
			foreach($this->C as $n42=>$d) {
				$this->sD[$n42]= $d!='' ? $this->sgiorno[$n42%7] : false;
				$this->fD[$n42]= $n42%7==6 || self::isfestivo($this->M,$d)==true ? true :false;}
			$a=array_filter($this->sD);
			$this->sD=$a;
			$a=array_intersect_key($this->fD,$this->sD);
			$this->fD=$a;unset($a);
			foreach ($this->sD as $v)
				 $a[]=$v;
			$this->sD=$a;unset($a);
			foreach ($this->fD as $v) 
				$a[]=$v;
			$this->fD=$a;unset($a);	}
		
	protected function ordsunday() {
		$ordC=array();
		$xSun=false;
		if(count($this->C)>42) {
			$cC=array_chunk($this->C,42,true);
			$this->C=$cC[0];}
		if($this->C[0]=="01" && $this->C[0]%7==1) 
			$passo=(-6);
		else 
			$passo=1;
		$countC=count($this->C);
		for($n42=0;$n42<$countC;$n42++) {
			if($n42+$passo<$countC && $n42+$passo>=0)
				$xDay=$this->C[$n42+$passo];
			else
				$xDay=$this->C[$countC-($n42+1)];
			array_push($ordC,$xDay); }
		$this->C=$ordC;	
		}
			
	protected function easter()  		{ 
		if($this->err==true) return"";
		$g = $this->Y % 19;   
		$c = floor($this->Y / 100);  	 
		$h = ($c - floor($c / 4) - floor((8 * $c + 13) / 25) + 19 * $g + 15) % 30;   
		$i = floor($h - ($h / 28) * (1 - floor(29 / ($h + 1)) * floor((21 - $g) / 11)));    
		$j = ($this->Y + floor($this->Y / 4) + $i + 2 - $c + floor($c / 4)) % 7; 
		$l = $i - $j                                   ;
		$month = 3 + floor(($l + 40) / 44);       
		$day = $l + 28 - 31 * floor ($month / 4); 
		$month=str_pad($month,2,"0",STR_PAD_LEFT);
		$day=str_pad($day,2,"0",STR_PAD_LEFT);strval($month).strval($day);
		return (string)strval($month).strval($day); } 	
		
	public function isfestivo($m,$d){
		$isFest=false;
		$d=str_pad($d,2,"0",STR_PAD_LEFT);
		$m=str_pad($m,2,"0",STR_PAD_LEFT);
		if(array_key_exists((string)$m.$d,$this->festivi)==true) {
			$isFest=true;
		}
		return $isFest;		}
				
	protected function festivi()	{
		if($this->err==true) return"";
		$this->festivi["0101"]="Capodanno";
		$this->festivi["0106"]="Epifania";
		$this->festivi["0425"]="Festa della liberazione";
		$this->festivi["0501"]="Festa del lavoro";
		$this->festivi["0602"]="Festa della repubblica";
		$this->festivi["0815"]="Ferragosto";
		$this->festivi["1101"]="Tutti i santi";
		$this->festivi["1103"]="San Giusto patrono";
		$this->festivi["1208"]="Immacolata Concezione";
		$this->festivi["1225"]="Natale";
		$this->festivi["1226"]="Santo Stefano";
		$easter=self::easter();
		$this->festivi[$easter]="Pasqua";
		$this->festivi[str_pad(intval($easter)+1,4,"0",STR_PAD_LEFT)]="Lunedi\x60dell'Angelo";
		ksort($this->festivi);}	
		
	public function aii($f_blank=true) {
		$aH=array();
		if($f_blank==true) $aH=array("".chr(32).chr(32).""=>chr(32).chr(32));
		for($i=0;$i<60;$i=$i+5)			{
			$val=(string)str_pad($i,2,"0",STR_PAD_LEFT);
			$aH[$val]=(string)$val;			}
		return $aH;		} 
		
	public function ahh($f_blank=true)		{
		$aH=array();
		if($f_blank==true) $aH=array("".chr(32).chr(32).""=>chr(32).chr(32));
		for($i=0;$i<24;$i++){
			$val=(string)str_pad($i,2,"0",STR_PAD_LEFT);
			$aH[$val]=$val;	}
		return $aH;	}	
		
	public function isohi($str)		{
		return substr($str,0,strlen($str)-2).":".substr($str,strlen($str)-2);
		//return substr($str,0,strlen($str)-2).;	
		}
	
	
public function diffmin($sInizio,$sFine)
   {
   $iInizioMin=intval(substr($sInizio,0,2))*60+intval(substr($sInizio,2,2));
   $iFineMin=intval(substr($sFine,0,2))*60+intval(substr($sFine,2,2));
   $iMinutiTrasc=$iFineMin-$iInizioMin;
   return $iMinutiTrasc;
   }
public function min2ore($iMin)
   {
   	 if($iMin>59)		{
		$iOra=floor($iMin/60);
		$iMin=$iMin%60;		} 
	 else		{
		$iMin=$iMin;
		$iOra="";		}
   $sOra=str_pad(strval($iOra),2,"0",STR_PAD_LEFT);
   $sMin=str_pad(strval($iMin),2,"0",STR_PAD_LEFT);
   $sOraMin=(string)$sOra.$sMin;
   return $sOraMin;	
   } 

public function add2now($a = array('d' => '0', 'm' => '0', 'Y' => '0')){
	
	$d = empty($a['d']) ? date("d") : date("d")+$a['d'];
	$m = empty($a['m']) ? date("m") : date("m")+$a['m'];
	$Y = empty($a['Y']) ? date("Y") : date("Y")+$a['Y'];
		
	return date('Y-m-d', mktime(0, 0, 0, $m, $d, $Y));
	
}

public function check($date){
	$a = explode('/', $date);
	if(count($a) != 3) return false;
	elseif(checkdate($a[1], $a[0], $a[2])) return true;
}

public function my2db($date){ # SE RET � PASSATO IN FORMATO ADATTO AL DB NON VIENE MODIFICATO
	$ret = $date;
	if(strlen($date) == 10){ # DATA IN FORMATO STANDARD 10/05/2010 o 20-05-2010
		if(strpos($date, '/')){
			$a = explode('/', $date);
		}
		elseif(strpos($date, '-')){
			$a = explode('-', $date);
		}
	if(checkdate($a[1], $a[0], $a[2])) $ret = $a[2].'-'.$a[1].'-'.$a[0];
	}
	return $ret;
}

public function s2d($date){ // controlla una striga data e la restituisce adatta al db
	$ret = $date;
	if(strpos($date, '/')){
		$a = explode('/', $date);
	} 
	elseif(strpos($date, '-')){
		$a = explode('-', $date);
	} else {
		return false;
	}
	
	if( strlen($date) != 10){
		return false;
	}
	
	// ad ogni modo setto l'array al formato 31 12 2012
	if( strlen($a[0]) == 4){
		$b = $a;
		$a[0] = $b[2];
		$a[2] = $b[0];
		unset( $b );
	}
	
	// giorno e mese con lo zero davanti se ad una cifra 05 06 2012
	if( checkdate($a[1], $a[0], $a[2])){
		if(strlen($a[0]) == 1) $a[0] = '0'.$a[0];
		if(strlen($a[1]) == 1) $a[1] = '0'.$a[1];
	} else {
		return false;
	}
	
	return $a[2].'-'.$a[1].'-'.$a[0];
}

public function db2my($date){ # SE RET � PASSATO IN FORMATO ADATTO AL DB NON VIENE MODIFICATO
	$ret = $date;
	if(strlen($date) == 10){ # DATA IN FORMATO STANDARD 10/05/2010 o 20-05-2010
		if(strpos($date, '/')){
			$a = explode('/', $date);
		}
		elseif(strpos($date, '-')){
			$a = explode('-', $date);
		}
		$ret = $a[2].'/'.$a[1].'/'.$a[0];
	}
	return $ret;
}


public function diffdays($d1, $d2){ # DIFFERENZA IN GIORNI TRA 2 DATE. FORMATO DATA DB (2011-08-01)
	$time1 = strtotime($d1);
	$time2 = strtotime($d2);
	return floor(($time1 - $time2)/(60*60*24));
}

}
$ob_dtime=ob_get_contents();
ob_clean();
?>	