<?php
# V.0.1.8
include_once 'init.php';
$user = new autentica($aA5);
$user -> login_no_redirect(false);

include_once HEAD_AR;
?>
<div id="main-box">
<div class="cbox">
<h4>Inserisci il codice di attivazione che hai ricevuto</h4>
<div class="fieldcontain">
	<label for="activation_code"><?=ACTIVATION_CODE?>:</label>
	<input name="activation_code" id="activation_code" value="" />
</div>	 

<div class="fieldcontain">
<input type="button" id="check_code" value="Avanti" class="g-button g-button-yellow m-top" />
</div>
</div>
</div>

<div id="dialog_users" class="h_dialog">
<input type="hidden" id="title-dialog" value="<?=DIALOG_HHU?>" />
<div class="alert" id="message-users"></div>
<form id="form_users" method="post" action="ajax/json.php?action=crud_activation_users">
</form>
</div>
<script type="text/javascript" src="<?=JS_MAIN.'enable-user.js'?>" />
<?php
include_once FOOTER_AR;
?>