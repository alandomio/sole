<?php
$img = getimagesize(CONTATTI.TESTA_MAIL2); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Mail <?=NOME_SITO?></title>
<style type="text/css">
<!--
body{ color:#000000;
	margin: 0px;
}
ol{ font-family:Geneva, Arial, Helvetica, sans-serif;}
-->
</style></head>
<body vlink="#000000" link="#000000" bgcolor="#ffffff" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="740" border="0" align="center" cellpadding="0" cellspacing="0">
<tr><td><img src="<?=ABS_PATH_CONTATTI?>header_nl2.png" alt="<?=NOME_SITO?>" width="<?=$img[0]?>" height="<?=$img[1]?>" border="0" /></td></tr>
<tr><td style="padding:3px 0; border-bottom:5px solid #000000; border-top:5px solid #000000;"><div><strong style="float:right;"><font color="#000000" size="2" face="Geneva, Arial, Helvetica, sans-serif"><?=date('d/m/Y',time())?></font></strong><h1 style="font-family:Arial, Helvetica, sans-serif; font-size:14px; font-weight:bold; color:#55AB26"><?=$configura -> path_s?></h1></div></td></tr>
<tr><td>
<?=$dati_mittente?>
</td></tr>
<tr><td>
<?=$preview_article?>
</td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td style="border-top:5px solid #000000;">
<div><span style="font-family: Verdana,Arial,Helvetica,sans-serif; font-size: 9px; line-height: 15px; color:#000000; vertical-align: top;"><strong>Il Mercatino Il portale di annunci del Friuli Venezia Giulia | <a href="http://www.ilmercatino.it" target="_blank">www.ilmercatino.it</a></strong><br />
</span></div></td></tr>
</table>
</body>
</html>
