<?php
class url{
static function urlunix($url){
	$url=str_replace("//","/",$url);
	$url=str_replace("///","/",$url);
	return $url;
}

static function uri($url,$var){
	$uri=$url."?";
	foreach($var as $k=>$v){
		$uri.=$k."=".rawurlencode($v)."&";
	}
	return $uri=substr($uri,0,strlen($uri)-1);
}

static function get($url, $var, $sep='&amp;'){

	$slash = count($var)>0 ? '/' : '';

	if( substr($url, -1) == '/'){
		$slash='';
	}
		
	if(strpos($url, '.php') !== false){
		$uri=$url.'?';
	} else {
		$uri=$url.$slash.'?';
	}

	foreach($var as $k=>$v){
		$uri .= $k."=".rawurlencode((string)$v).$sep;
	}
	if($uri[strlen($uri)-1]==';') $uri = substr($uri,0,strlen($uri)-5);
	else $uri=substr($uri,0,strlen($uri)-1);
	
	return $uri;
}
	
static function strip($uri){
	return (strpos($uri,"?")!==false ? stringa::rightfrom($uri,"?") : $uri);
}

static function getRealIpAddr(){
	if(!empty($_SERVER['HTTP_CLIENT_IP'])){ // CHECK IP FROM SHARE INTERNET
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}
	elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){ // TO CHECK IP IS PASS FROM PROXY
		$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	else{
		$ip=$_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}

}
?>