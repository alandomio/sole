<?php
# V.0.1.8
class url{
function urlunix($url){
	$url=str_replace("//","/",$url);
	$url=str_replace("///","/",$url);
	return $url;
}

function uri($url,$var){
	$uri=$url."?";
	foreach($var as $k=>$v){
		$uri.=$k."=".rawurlencode($v)."&";
	}
	return $uri=substr($uri,0,strlen($uri)-1);
}

function get($url, $var){
	$uri=$url."?";
	foreach($var as $k=>$v)
		$uri.=$k."=".rawurlencode($v)."&amp;";
		
	if($uri[strlen($uri)-1]==';') $uri = substr($uri,0,strlen($uri)-5);
	else $uri=substr($uri,0,strlen($uri)-1);
	
	return $uri;
}
	
function strip($uri){
	return (strpos($uri,"?")!==false ? stringa::rightfrom($uri,"?") : $uri);
}

function getRealIpAddr(){
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