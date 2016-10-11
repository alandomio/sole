<?php
//include_once 'init.php';
//$data = date("d/m/Y", time());


$font = 'font-family:Arial, Helvetica, sans-serif; ';
$size = 'font-size:12px; ';
$color = 'color:#777777; ';
$weight = 'font-weight:normal; ';
$bold = 'font-weight:bold; ';
$title = $font.' font-weight:bold; font-size:20px; color:#4EB832';
$def = 'style="'.$font.$size.$color.$weight.'"';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Il Mercatino - Newsletter</title>
</head>
<body bgcolor="#FFFFFF">
<table width="739" border="0" align="center" cellpadding="0" cellspacing="0" bgcolor="#ffffff">
<tr>
<td colspan="2" height="100" background="<?=ABS_NEWSLETTER?>header.jpg" >
<div style=" <?=$font?> font-size:17px; font-weight:bold; color:#555555; text-align:right; height:43px; padding:40px 20px 0 0;"><!-- DATE --></div>
<div style=" <?=$font?> font-size:12px; color:#dddddd; text-align:right; font-weight:normal; height:30px; padding:0 20px 0 0;"><a href="<?=ABS_PATH?>" style="font-size:12px; color:#DDDDDD; text-decoration:none;"><!-- DOMAIN --></a></div>
</td>
</tr>
<tr>
<td colspan="2" background="<?=ABS_NEWSLETTER?>bg.png" style="padding:10px 70px 10px 70px; <? print $title; ?>" align="left">
<!-- TITLE -->
</td>
</tr>
<!-- IMAGE -->
<tr>
<td colspan="2" background="<?=ABS_NEWSLETTER?>bg.png" style="padding:10px 70px 10px 70px;">
<!-- DESCRIPTION -->
</td>
</tr>
<tr>
<td colspan="2" background="<?=ABS_NEWSLETTER?>footer.gif" bgcolor="#ffffff" height="100" <?=$def?>>
<div style="font-size:11px; color:#999999; font-weight:normal; text-align:center;">
Editore: Tredicom S.r.l. - Via Gambini 3, Trieste - Tel. 040.367.528 - Fax 040.660.088<br />
Reg. Impr. Trieste - CF/P.iva 00233270321 Cap. Soc. I.V. Euro 100.000,00
<br />
<div id="cancellazione"><!-- DELETE --></div>
</div>
</td>
</tr>
</table>
</body>
</html>