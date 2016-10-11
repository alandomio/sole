<?php
$img = getimagesize(CONTATTI.TESTA_MAIL); 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Mail <?=NOME_SITO?></title>
<style type="text/css">
<!--
body { color:#000;
	margin: 0px;
}
ol{ font-family:Geneva, Arial, Helvetica, sans-serif;}
-->
</style></head>
<body vlink="#000000" link="#000000" bgcolor="#ffffff" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="600" border="0" align="center" cellpadding="0" cellspacing="0">
<tr><td><img src="<?=ABS_PATH_CONTATTI?>header_nl.png" alt="<?=NOME_SITO?>" width="<?=$img[0]?>" height="<?=$img[1]?>" border="0" /></td></tr>
<tr><td style="padding:3px 0; border-bottom:1px solid #ff0000; border-top:1px solid #ff0000;"><div align="right"><strong><font color="#749dfe" size="2" face="Geneva, Arial, Helvetica, sans-serif"><?=date('d/m/Y',time())?></font></strong></div></td></tr>
<tr><td>Nome e cognome: <?=$a['mNome']?></td></tr>
<tr><td>Email: <?=$a['mEmail']?></td></tr>
<tr><td>Telefono: <?=$a['mTel']?></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td><?=$a['mMessaggio']?></td></tr>
<tr><td>&nbsp;</td></tr>
<tr><td style="border-top:1px solid #ff0000;">
<div align="center"><span style="font-family: Verdana,Arial,Helvetica,sans-serif; font-size: 9px; line-height: 15px; color:#666; vertical-align: top; text-align: center;"><strong><?=NOME_SITO?></strong><br />
</span></div></td></tr>
</table>
</body>
</html>
