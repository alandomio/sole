<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Esempio</title>
<style type="text/css">
table#preferita{font-size:24px; border:1px solid #000000; }
table#preferita td{ border: dotted 1px #000000; }
</style>
</head>
<body style="font-family:Arial, Helvetica, sans-serif;">
<?php
# FACCIAMO LA STESSA COSA CON UN WILE E CON UN FOR

$a = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i');
$i = 0;
while(array_key_exists($i, $a)){
	echo $a[$i].' '.$i.'<br />';
	$i++;
}

for($i = 0; array_key_exists($i, $a); $i++){
	echo $a[$i].' '.$i.'<br />';
}

print '<br />';


# ESEMPIO LIBRO ROSSO PAGINA 56
for($i = 1; $i <= 10; $i++){
	$temp= 4000 / $i;
	print "4000 diviso $i fa... $temp<br />";
}

# ESEMPIO CREAZIONE TABELLA FOR NEL FOR, LIBRO ROSSO PAG 58
# VOGLIO FARE UNA TABELLA CON 5 RIGHE E CON 7 CELLE PER RIGA
print '<br />';
print '<table id="preferita">';

for($tr = 1; $tr <= 5; $tr++){
	print "\n<tr>\n";
	
	for($td = 1; $td <= 7; $td++){
		print "<td>";
		print "$tr * $td fa ".$tr*$td; # il punto . serve a concatenare, in pratica appiccichi del testo ad una stringa
		print "</td>\n";
	}
	
	print "</tr>\n";
}

print '</table>';
?>
<table id="nonpreferita">
<tr><td>pippo</td></tr>
</table>
<table id="nonpreferita2">
<tr><td>pippo</td></tr>
</table>

</body>
</html>