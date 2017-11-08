$(document).ready(function()	{
	if ($(".checkme").is(":checked")){
		$("#extra").show("fast");
	}
	else{     
		$("#extra").hide("fast");
	}
	// Gestisce il click
	$(".checkme").click(function(){
		$("#extra").toggle(600);
	});
	
	$('#subDo').click(function(){
		$('#frm_flat').submit();
	});
	
	 show_hide_rows();
	$('#sel_bld').change(function(){
		show_hide_rows()
	});

	
/* 	$('#open_dialog_user').click(function(){
		//	INIZIALIZZO IL FORM
		var id_bld = $('#sel_bld').val();
		
		jQuery.getJSON("ajax/json.php?action=form_users&id_bld=" + id_bld,
		function(data){
			$('#form_users').html(data.form);
			$('#NAME').val( $('#NAME_USER').val() );
			$('#SURNAME').val( $('#SURNAME_USER').val() );
			});
		$("#dialog_users").dialog('open');
	}); */
 });

function show_hide_rows(){
	if ($('#sel_bld').val() >= 1){
	//	aggiorna_select_users();
		$('.sh').show('fast');
	}
	else{
		$('.sh').hide('fast');
	}
}

/* function aggiorna_select_users(){
	var id_bld = $('#sel_bld').val();
	var selected = $('#def_user').val();
	
	jQuery.getJSON("ajax/json.php?action=select_users&id_bld=" + id_bld + "&selected=" + selected,
	function(data){
		$('#sel_users').html(data.select);
	});
} */
 
function selectBLD(id)	{
	hcompanys = $('#sel_hc' + id).val();
	hc = $('#sel_hc').val();
	jQuery.getJSON("ajax/json.php?action=select_hc2bld&hc=" + hc,
	function(data){
		options = '<option selected="selected" value="">- Scegli edificio</option>	' + "\n\r";
	  jQuery.each(data, function(i,item){
			options += '<option  value="' + item.optionValue + '">' + item.optionDisplay + '</option>' + "\n\r";
	  });
	  $('#sel_bld').html(options);
	});
}

/* $("#dialog_users").dialog({
		autoOpen: false,
		height: 400,
		width: 900,
		modal: true,
		title: 'Users',
		buttons: {
			'Save': function() {
				$("#form_users").ajaxSubmit(function(data){
					if(data.success){
						// AGGIORNO IL DEFAULT E IL SELECT OPTION
						$('#def_user').val(data.id);
						aggiorna_select_users();
						$("#dialog_users").dialog('close');
						$("#message").html(data.message);
						
					}
					else	{
						$("#message-users").html(data.message);
					}
				});
				
			},
			'Close': function() {
				$(this).dialog('close');
			}
		},
		close: function(){

		}
	}); */		