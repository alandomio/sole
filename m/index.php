<?php
include_once '../init.php';
$user = new autentica($aA5);
$user -> login_auto('buildings.php'); # ENTRA NELLA PAGINA INTERNA SE L'UTENTE È GIÀ LOGGATO
ob_start();
?>
<form action="index.php" id="flogin" method="post" data-ajax="false">
<div data-role="fieldcontain">
    <label for="username"><?=USER?>:</label>
    <input type="text" name="username" id="username" value="admin@sole.it" maxlength="50" /></div>
    <label for="password">Password:</label>
    <input type="password" name="password" id="password" value="sole2011" maxlength="50" />    
	<fieldset data-role="controlgroup">
		<legend><?=LOGINTYPE?>:</legend>
		<input type="checkbox" name="persist" id="persist" value="1" class="custom" />
		<label for="persist"><?=REMEMBER?></label>
    </fieldset>    
    <input type="submit" name="login" value="Login" data-theme="a" />
</form>
<?php
$html['FORM_LOGIN'] = ob_get_clean();
$MYFILE -> catch_buffer();
?>
<!DOCTYPE html> 
<html> 
<head> 
	<meta charset="utf-8"> 
	<meta name="viewport" content="width=device-width, initial-scale=1"> 
	<title>Sole Project Mobile</title> 
	<link rel="stylesheet" href="http://code.jquery.com/mobile/1.0b1/jquery.mobile-1.0b1.min.css" /> 
	<script src="http://code.jquery.com/jquery-1.6.1.min.js"></script> 
	<script src="http://code.jquery.com/mobile/1.0b1/jquery.mobile-1.0b1.min.js"></script> 
</head> 
<body> 
<div data-role="page" data-theme="a" id="jqm-home" class="type-home"> 
	<div data-role="content"> 
		<div class="content-secondary"> 
			<div id="jqm-homeheader"> 
				<h1 id="jqm-logo"><img src="<?=IMG_LAYOUT.'logo.png'?>" alt="<?=NOME_SITO?>" /></h1> 
			</div> 
			<p class="intro"><strong>Login</strong></p> 
            <?
print $MYFILE -> system_errors;
$MYFILE -> print_msg(true);
?>
			<?=$html['FORM_LOGIN']?>
		</div>
	</div>	
</div> 
</body> 
</html> 