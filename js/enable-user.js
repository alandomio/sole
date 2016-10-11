$(document).ready(function()	{
	$('#check_code').click(function(){
		var code = $('#activation_code').val();
		jQuery.getJSON("ajax/json.php?action=check_activation_code&code=" + code,
		function(data){
			if(data.success){
				$('#form_users').html(data.form);
				$("#dialog_users").dialog('open');
			}
			else{
				$("#message").html(data.message);
			}
		});
	});
	
	$('#test').click(function(){
		jQuery.getJSON("ajax/json.php?action=form_login",
		function(code){
			$('#main-box').html(code.form);
		});
	});	
 });

$("#dialog_users").dialog({
		autoOpen: false,
		height: 500,
		width: 900,
		modal: true,
		title: $('#title-dialog').val(),
		buttons: {
			'Save': function() {
				if($('#password').val() != $('#rpt_password').val()){
					jQuery.getJSON("ajax/json.php?action=get_message&code=ERR_RPT_PASSWORD&mode=err",
					function(data){
						if(data.success){
							$('#message-users').html(data.message);
							$(this).dialog('close');
						}
					});
				}
				else{
					$("#form_users").ajaxSubmit(function(data){
						if(data.success){
							// AGGIORNO IL DEFAULT E IL SELECT OPTION
							$("#dialog_users").dialog('close');
							$("#message").html(data.message);
							jQuery.getJSON("ajax/json.php?action=form_login",
							function(code){
								$('#main-box').html(code.form);
							});
						}
						else{
							$('#message-users').html(data.message);
						}
					});
				}
			},
			'Close': function() {
				$(this).dialog('close');
			}
		},
		close: function(){

		}
	});		