<?php
include_once 'init.php';

$user = new autentica($aA5);
$user->login_no_redirect(false);

if($user->autentica){
	io::headto('building-chart.php', array());
}

$MYFILE->add_js_group('contacts', array(JS_MAIN.'contacts.js'), 100, 'debug', 'head');

/*
 * invia la mail
 * */
if(array_key_exists('cmd', $_POST)){
	
	include_once CLASSES_PATH.'extra/gump.php';
	
	$success='r';

	$gump=new GUMP();
	$rules = array(
			'name'  	 	=> 'required',
			'email'   		=> 'required|valid_email',
			'message'  		=> 'required',
	);

	$validated = $gump->validate( $_POST, $rules );

	if($validated===true){

		$subject = ! empty($_POST['subject']) ? $_POST['subject'] : 'Email inviata da Hive Project';

		$body='';
		$body .= 'Nome: '.$_POST['name']."\n";
		$body .= 'Email: '.$_POST['email']."\n";
		$body .= 'Messaggio: '."\n\n".$_POST['message']."\n";

		$mail = new PHPMailer();
		$mail->From=		$_POST['email'];
		$mail->FromName=	$_POST['name'];
		$mail->Sender=		'noreply@hiveproject.eu'; // indicates ReturnPath header
		$mail->AddReplyTo($_POST['email'], $_POST['name']); // indicates ReplyTo headers
		$mail->AddAddress('info@hiveproject.net', 'Hive Project');
		$mail->CharSet  = 'utf-8';
		$mail->Subject  = $subject;
		$mail->Body     = $body;
			
		if( $mail->Send()){
			$success='g';
		} else {
			$success='r';
		}
	}
	exit(json_encode(array('success'=>$success)));
}

include_once HEAD_AR;
?>
<div id="page-container">
	<div class="content liquid-content" id="mappaedificio">
		<div class="content" id="ricercaedificio">
			<h3>Login</h3>
			<form method="post" action="./">
			<div class="campo_form">
				<label for="username">Email:</label>	
				<input type="text" id="username" name="username" class="input_form text " style="width:218px;">
			</div>
			<div class="campo_form">
				<label for="password">Password:</label>	
				<input type="password" id="password" name="password" class="input_form text " style="width:218px;">
			</div>
			<div class="campo_form" style="margin-top:8px;">
				<input type="submit" style="width:224px;" value="Login" class="g-button g-button-yellow"/>
			</div>
			</form>
			
			<div class="clear"></div>
		</div>
		
		<div id="content" class="content resizable" style="padding:20px;">
		
			<h1><?=__('Contatti')?></h1>
	
			<div class="contact-form-wrapper" id="gdl-contact-form">
				<form id="gdl-contact-form" method="post" action="contacts.php">
					<input type="hidden" name="cmd" value="send" />
					<ol class="forms">
						<li><strong><?=__('Nome')?> *</strong>
						<input type="text" name="name" id="m_name" class="require-field"/>
						</li>
						<li><strong>Email *</strong>
						<input type="text" name="email" id="m_email" class="require-field email"/>
						</li>
						<li class="textarea"><strong><?=__('Messaggio')?> *</strong>
						<textarea name="message" id="m_message" class="require-field"></textarea>
						</li>
						<li class="sending-result" id="sending-result">
						<div class="message-box-wrapper green">
						</div>
						</li>
						<li class="buttons">
						<button type="submit" class="g-button g-button-yellow"><?=__('Invia')?></button>
						<div class="contact-loading" id="contact-loading">
						</li>
					</ol>
				</form>
				<div class="clear"></div>
			</div>

		</div>
	</div>
</div>
<div class="clear"></div>
<?php
include_once FOOTER_AR;
?>