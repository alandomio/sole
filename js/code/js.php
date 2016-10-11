<?php
// you can pass this script to PHP CLI to convert your file.

// adapt these 2 paths to your files.

//$out = 'corsi_partecipanti.js';

// or uncomment these lines to use the argc and argv passed by CLI :
/*
if ($argc >= 3) {
	$src = $argv[1];
	$out = $argv[2];
} else {
	echo 'you must specify  a source file and a result filename',"\n";
	echo 'example :', "\n", 'php example-file.php myScript-src.js myPackedScript.js',"\n";
	return;
}
*/

require 'class.JavaScriptPacker.php';
$offuscamento = false;

$src = $_REQUEST['source'].'.js';

$cached = '_cache/'.$src;


ob_start("ob_gzhandler");

if(file_exists($cached) AND filemtime($cached) > filemtime($src))	{
	$last_modified_time = filemtime($cached);
	$etag = md5_file($cached);

	header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT");
	header("Etag: $etag");
	header("Expires: ".gmdate("D, d M Y H:i:s", time() + 86400)." GMT");
	header('Content-type: application/javascript');

	if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time ||
		trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag) {
		header("HTTP/1.1 304 Not Modified");
		exit;
	} 
	else
		echo file_get_contents($cached);
	
}

else	{
	if($src=='library.js')	{
		$script .= file_get_contents('jquery-1.3.2.js')."\n";
		$script .= file_get_contents('jquery-ui-1.8.1.custom.min.js')."\n";
		$script .= file_get_contents('jquery.json-2.2.min.js')."\n";
		$script .= file_get_contents('jquery.contextMenu.js')."\n";
		$script .= file_get_contents('jquery.autocomplete.js')."\n";
		//$script .= file_get_contents('jquery.alerts.js')."\n";
		$script .= file_get_contents('jquery.tooltip.pack.js')."\n";
		$script .= file_get_contents('jquery.form.js')."\n";
		$script .= file_get_contents('jquery-lightbox-min.js')."\n";
		//$script .= file_get_contents('jquery.ui.datepicker-it.js')."\n";
		$script .= file_get_contents('flexigrid.js')."\n";
		$script .= file_get_contents('cust_select_plugin.js')."\n";
		
		
		
	}
	else
		$script = file_get_contents($src);

	$t1 = microtime(true);

	$packer = new JavaScriptPacker($script, 'Numeric', true, false);
	if ($offuscamento)
		$packed = $packer->pack();
	else
		$packed=$script;

	//$packed=md5($script);

	$t2 = microtime(true);
	$time = sprintf('%.4f', ($t2 - $t1) );

	
	//echo 'script ', $src, ' packed in ' , $out, ', in ', $time, ' s.', "\n";
	/*$file_content="<?php\n".'$etag="'.$etag.'"'.";\n".'$packed="'.addslashes($packed).'";'."\n?>";*/
	//file_put_contents('_cache/'.$src.'.php', $file_content);
	file_put_contents($cached, $packed);
	$last_modified_time = filemtime($cached);
	$etag = md5_file($cached);

	header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT");
	header("Etag: $etag");
	header("Expires: ".gmdate("D, d M Y H:i:s", time() + 86400)." GMT");
	header('Content-type: application/javascript');
	header('Content-type: application/javascript');
	echo $packed;
}

ob_end_flush();

?>
