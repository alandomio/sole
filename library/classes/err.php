<?php
# V.0.1.5
class err{
	function allfalse($arr){
		$count=count($arr);
		$iEmpty=0;
		foreach($arr as $k=>$v){
			if( is_null($v) || (is_bool($v) && $v==false) || (is_string($v) && trim($v)=="") || (is_numeric($v) && ($v)==0))
				$iEmpty++;	}
		return $iEmpty==$count ? true : false;
		}

	function ackerr($ack,$err){
		$listerr = ''; $listack = ''; $flag = false;
		if(!empty($err)){
			foreach(arr::strip($err) as $s){
				$listerr.='<li>'.$s.'</li>'."\n";
			}
			if(strlen($listerr)>0) { $listerr = '
			<div id="alert_msg_ko">
			<ul>'.$listerr.'</ul>
			</div>';
			$flag = true;
			}
	}
		if(!empty($ack)){
			foreach(arr::strip($ack) as $s){
				$listack.='<li>'.$s.'</li>'."\n";
			}
			if(strlen($listack)>0) { $listack = '
			<div id="alert_msg_ok">
			<ul>'.$listack.'</ul>
			</div>'; 
			$flag = true; }
		}	
		
			
		if($flag==true){
			return '<div id="alert">'.$listerr.$listack.'</div>';
		}
	}
	
	function geturl($ack,$err){
		$listerr = ''; $listack = ''; $flag_err = false; $flag_ack = false;
		if(!empty($err)){
			foreach(arr::strip($err) as $s){
				$listerr .= $s.' ';
			}
			if(!empty($listerr)) { $listerr = 'err='.rawurlencode($listerr); $flag_err = true; }
	}
		if(!empty($ack)){
			foreach(arr::strip($ack) as $s){
				$listack.= $s.' ';
			}
			if(!empty($listack)) { $listack = 'ack='.rawurlencode($listack); $flag_ack = true; }
		}		
		if($err && $ack){
			return $listerr.'&'.$listack;
		}
		else return $listerr.$listack;
	}

	function msgcrud($err){
	if(empty($err['SYSTEMERR']) && err::allfalse($err)==false ){
		$err['SYNTAXERR']=SYNTAXERR;
		$err['SYNTAXPRT']=SYNTAXPRT;}
	return implode("<br>",arr::strip($err));
	}
	
	function msgcrudlist($err){
	if(empty($err['SYSTEMERR']) && err::allfalse($err)==false ){
		$err['SYNTAXERR']=SYNTAXERR;
		$err['SYNTAXPRT']=SYNTAXPRT;}
	$list='';
	foreach(arr::strip($err) as $alert){
		$list.='<li>'.$alert.'</li>';}
	$list = strlen($list)>0 ? "<div id=\"alert\"><span class=\"ko\"><ul>$list</ul></span></div>" : $list;
	return $list;
	}

function msgacklist($err){
	$list='';
	foreach(arr::strip($err) as $alert){
		$list.='<li>'.$alert.'</li>';}
	$list = strlen($list)>0 ? "<div id=\"alert\"><span class=\"ok\"><ul>$list</ul></span></div>" : $list;
	return $list;
	}
	
	function sql($msg){
			$myerr=rs::err();
			$msg=$myerr==false ? "" : $msg;
			$risp=ERR_SQL==0 ? "" : "";
			$risp=ERR_SQL==1 ? $msg  : $risp;
			$risp=ERR_SQL==2 ? $myerr  : $risp;
			$risp=ERR_SQL==3 ? $msg." ".$myerr : $risp;
			if(EKO_ERR=="1")
				print(trim($risp));
			return $risp;
			}
			
	function sqlcrud($msg){
			$myerr=rs::err();
			$msg=strpos($myerr,"1451")!==false || strpos($myerr,"parent row") !==false ? FOREIGNERR : $myerr;
			$msg=$myerr==false ? "" : $msg;
			$risp=ERR_SQL==0 ? "" : "";
			$risp=ERR_SQL==1 ? $msg  : $risp;
			$risp=ERR_SQL==2 ? $myerr  : $risp;
			$risp=ERR_SQL==3 ? $msg." ".$myerr : $risp;
			$risp=trim($risp);
			return $risp;
			}
	
	function eko($stringa_err){
		if(EKO_ERR=="1")
			print($stringa_err);}

	function my_or_system($systemerr,$my_systemerr){
		$my_systemerr=is_array($my_systemerr) ? implode("\n",$my_systemerr): $my_systemerr;
		$risp=MY_OR_SYSTEMERR==0 ? " " : " ";
		$risp=MY_OR_SYSTEMERR==1 ? $systemerr  : $risp;
		$risp=MY_OR_SYSTEMERR==2 ? $my_systemerr  : $risp;
		$risp=MY_OR_SYSTEMERR==3 ? $systemerr." ".$my_systemerr : $risp;
		return $risp;
		}
	function crud(){
		$arr=array();
		for($i=0;$i<func_num_args();$i++){
			$aArg=func_get_arg($i);
			$arr=array_merge($arr,$aArg);}
		$arr=array_merge(array('SYNTAXERR'=>false),arr::blank($arr),array('SYSTEMERR'=>false,'SYNTAXPRT'=>false));
		$arr=arr::blank2false($arr);
		return $arr;
		}		
	}
?>
