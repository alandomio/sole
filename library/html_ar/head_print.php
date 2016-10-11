<?php
$MYFILE -> catch_buffer();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<link rel="shortcut icon" href="favicon.ico" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?=$MYFILE -> css?>
<title><?=$title_page?></title>
</head>
<body>

<div id="mother">
<?
print $MYFILE -> system_errors;
$MYFILE -> print_msg(true);
?>
<div id="message" class="alert"></div>
<h2><?=$title_page?></h2>
<?php
foreach($ERR_CRUD as $k => $v){
	if(!empty($v)) $err[] = $v;
}
?>