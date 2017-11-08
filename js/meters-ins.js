$(document).ready(function(){

	// dialogo campi produzione
	$('#dialog_altre_utenze').live('change', function(){
		if( $('#dialog_altre_utenze').is(':checked')){
			$('#dialog_SUM_DIVISIONAL').removeClass('dn');
		} else {
			$('#dialog_SUM_DIVISIONAL').addClass('dn');
			$('#dialog_SUM_DIVISIONAL option[value="0"]').attr("selected", "selected");
		}
	});	
	
	
	$('#dialog_choose_thermal').live('change', function(){
		if( $( this ).val() == 2 ){
			//alert(2);
			$('#dialog_FUEL').removeClass('dn');
		} else {
			$('#dialog_FUEL').addClass('dn');
			$('#dialog_FUEL option[value="0"]').attr("selected", "selected");
		}
	});

	// SCELTA EDIFICIO
	$(document).ready(function(){
		$('#federations').change(function(){
			$('#hcompanys').html('');
			$('#buildings').html('');
			selectHC();
			selectBLD();
		});
		 $('#hcompanys').change(function(){
				selectBLD();
				//$('#buildings').html('');
		});
		$('#buildings').change(function(){
			// MOSTRA SCHEDA INSERIMENTO CONTATORE
			var id_building = $('#buildings').val();
			showFORM(id_building);
			showMETERS(id_building);
		});
	});
});
	
	function selectHC(){
		federation = $('#federations').val();
		jQuery.getJSON("ajax/json.php?action=select_hc&fed=" + federation,
        function(data){
			options = '<option selected="selected" value="">- Scegli housing company</option>	' + "\n\r";
          jQuery.each(data, function(i,item){
				options += '<option  value="' + item.optionValue + '">' + item.optionDisplay + '</option>' + "\n\r";
          });
		  $('#hcompanys').html(options);
        });
	}
	
	function selectBLD(){
		hcompanys = $('#hcompanys').val();
		federation = $('#federations').val();
		jQuery.getJSON("ajax/json.php?action=select_bld&hc=" + hcompanys + "&fed=" + federation,
        function(data){
			options = '<option selected="selected" value="">- Scegli edificio</option>	' + "\n\r";
          jQuery.each(data, function(i,item){
				options += '<option  value="' + item.optionValue + '">' + item.optionDisplay + '</option>' + "\n\r";
          });
		  $('#buildings').html(options);
        });
	}
	
	function wizard_formula(f_input, id_building){
		f_input.click(function(){
			// CARICO I VALORI NEL FORM
			jQuery.getJSON("ajax/json.php?action=formula_meters&id=" + id_building,
			function(meters){
				$("#meters").html(meters.list);
				$('.meter-formula').click(function(){
					insertMETERINTOFORMULA($(this).attr('title'), 'input-formula');
				});
			});
			
			valore = f_input.val();
			$('#input-formula').val(valore);
			
			
			$("#dialog_formula").dialog('open');
		});
	}
	
	function wizard_ab(f_input, id_building, prefisso){
		f_input.click(function(){
			var show = parseInt($(this).attr('title'));
			var ab = $('#status-ab');
			if( show == 0){
				ab.val('a');
			}
			else if( show == 1){
				ab.val('b');
			}
			
			// CARICO I VALORI NEL FORM
			jQuery.getJSON("ajax/json.php?action=formula_meters&id=" + id_building,
			function(meters){
				$("#meters_ab").html(meters.list);
				$('.meter-formula').click(function(){
					insertMETERINTOFORMULA($(this).attr('title'), 'input-' + ab.val());
				});
			});
			
			// GESTISCI TABS E APRI DIALOG
			$('#tabs').tabs({selected: show});
			$('#tabs').tabs({select: function(){
				if(ab.val() == 'a'){
					ab.val('b');
				}
				else if(ab.val() == 'b'){
					ab.val('a');
				}
			}});
		
			vA = $('#' + prefisso + 'A').val();
			vB = $('#' + prefisso + 'B').val();
			
			$('#input-a').val(vA);
			$('#input-b').val(vB);
			
			$("#dialog_ab").dialog('open');
		});	
	}
	
	function showFORM(id_building){
		idg = $('#idg').val();
		if(id_building != ''){
		 jQuery.getJSON("ajax/json.php?action=form_insert_meter&id=" + id_building + "&idg=" + idg,
			function(data){
				$('#col_right').html(data.form);
				
				// INIZIALIZZO LE OPERAZIONI DA FARE
				var sel = $('#idmetertype');
				var hmeter = $('#hmeter');
				var rf = $('#rf');
				var ab = $('#ab');
				var idtype = $('#idtype');
				
				// INIZIALIZZO DATEPICKER
				$( "#D_FIRSTVALUE" ).datepicker({
					dateFormat: "dd/mm/yy",
				});
				
				// WIZARD
				wizard_formula($('#mk-formula'), id_building);
				wizard_ab($('.a_b'), id_building, '');
				
				sel.change(function() {
					// valori di default
					hmeter.val('1');
					$('.hmeter').css('display','none');
					$('#start_2').val('');
					$('#start_3').val('');
					$('#end_2').val('');
					$('#end_3').val('');
					$('.hourly_m').css('display','none');
					$('#is_double input').attr('checked', false);
					$('#watermeter').addClass('dn');
					$('#SUM_DIVISIONAL').addClass('dn');
					
					$('#dim_impianto').addClass('dn');
					
					// impianto termico
					$('#thermal').addClass('dn');
					$('#choose_thermal option[value=""]').attr("selected", "selected");
					nascondi_thermal();
					
					if(sel.val() == 1){ // elettricità 
						$('.hmeter').css('display','block');
						// azzero i valori
						$('#is_double input').attr('checked', false); // no check
						$('#dim_impianto input').val('');
					}
					else if( sel.val() == 2 ){ // energia termica
						$('#thermal').removeClass('dn');
					} 
					else if( sel.val() == 5 ){ // acqua
						$('#watermeter').removeClass('dn');
						$('#watermeter input').attr('checked', false);
						$('#SUM_DIVISIONAL option[value="0"]').attr("selected", "selected");
					} 
				});

				hmeter.change(function(){
					if(hmeter.val() == 1){
						$('.hourly_m').css('display','none');
					} else {
						$('.hourly_m').css('display','block');
					}
				});

				$('[name="IS_DOUBLE"]').change(function(){
					var ck_double = $('[name="IS_DOUBLE"]');

					if( ck_double.is(':checked')){
						$('.double_m').css('display','block');
						$('#dim_impianto').removeClass('dn');					
						// $('#tr_rf').addClass('dn'); // visualizzazione campo formula				 
					} else {
						$('.double_m').css('display','none');
						$('#dim_impianto').addClass('dn');	
						// $('#tr_rf').removeClass('dn');	
					}
				});				
				
				$('#altre_utenze').change(function(){
					if( $('#altre_utenze').is(':checked')){
						$('#SUM_DIVISIONAL').removeClass('dn');
					} else {
						$('#SUM_DIVISIONAL').addClass('dn');
						$('#SUM_DIVISIONAL option[value="0"]').attr("selected", "selected");
					}
				});				
				$('#choose_thermal').change(function(){
					// resetto i controlli per l'impianto termico
					nascondi_thermal();
				
					thermal_val = $('#choose_thermal').val();
					
					// alert(thermal_val);
					
					if(thermal_val != ''){
						$('#thermal-' + thermal_val).removeClass('dn');
					} 
/* 					else {
						// nascondi_thermal();
					} */
	
				});
				
				rf.change(function() {
					displayRF( rf.val() );
				});

				ab.change(function() {
					if(ab.val() == 2){ // A/B
						$('.ab').css('display','block');
					}
					else{
						$('.ab').css('display','none');
					}
				});

				$('#idsupplytype').change(function(){
					showFLATS();
				});
				
				$('#save_new').click(function() {
					var id_building = $('#buildings').val();

					$("#contatore").ajaxSubmit(function(data){
						if(data.success){
							$('#message').html(data.message);
							$('#jq_nav').css('display','block');
							
							// AGGIORNAMENTO LISTA CONTATORI E RESET CAMPI
							$('#flats_list').html('');
							$('#MATRICULA_ID').val('');
							$('#idsupplytype').val('');
							
							showMETERS(id_building);
						} else {
							$('#message').html(data.message);
						}
					});
				});				
			});
		}
	}

function nascondi_thermal(){
	$('#thermal-1').addClass('dn');
	$('#thermal-2').addClass('dn');
	
	$('#ACS option[value=""]').attr("selected", "selected");
	$('#ETE option[value=""]').attr("selected", "selected");
	$('#FUEL option[value=""]').attr("selected", "selected");
	$('#SIZE_THERMAL').val('');	
}	
	
function showMETERS(id_building){
	idg = $('#idg').val();
	if(id_building != ''){
	 jQuery.getJSON("ajax/json.php?action=get_meters_list_by_id_building&id=" + id_building + "&idg=" + idg,
		function(data){
			$('#meters_list').html(data.list);
			//if(data.duplicated){}
			$('#message').html(data.message);
			$('.view_meter').click(function(){
				dialog_meter($(this));
			});
		});
	}
}

function dialog_meter(mt){
	var id_building = $('#buildings').val();
	var mode = $('#idsupplytype').val();
	var idmeter =  mt.attr('alt');
	var name =  mt.text();

	jQuery.getJSON("ajax/json.php?action=get_riepilogo_contatore&idmeter=" + idmeter + "&id_building=" +id_building + "&mode=" + mode,
		function(data){
			$('#content_meter').html(data.meter);
			$('#content_valori_iniziali').html(data.valori_iniziali);
			$('#content_flats').html(data.flats);
			$('#content_usages').html(data.usages);
			$("#dialog_meter").dialog({title: name});
			
			// Inizializzazione funzionalità interfaccia
			$( "#edit_D_FIRSTVALUE" ).datepicker({
				dateFormat: "dd/mm/yy",
			});
			
			var ab = $('#edit_ID_OUTPUT');
			
			if(ab.val() == 2){
				$('#tr_A').css('display','block');
				$('#tr_B').css('display','block');
			}
			
			ab.change(function() {
				if(ab.val() == 2){ // A/B
					$('#tr_A').css('display','block');
					$('#tr_B').css('display','block');
				}
				else{
					$('#tr_A').css('display','none');
					$('#tr_B').css('display','none');
				}
			});
			
			wizard_formula($('#edit_FORMULA'), id_building);
			wizard_ab($('.a_b'), id_building, 'edit_');
			
			$("#dialog_meter").dialog('open');
			
			// ABILITA O DISABILITA IL PULSANTE DELETE SE NECESSARIO
			var act_delete = $('#act_delete').val();
			if(act_delete == 1){ 
				$(".ui-dialog-buttonpane button:contains('Delete')").button("enable");
			}
			else{
				$(".ui-dialog-buttonpane button:contains('Delete')").button("disable");
			}
			
			$( '.datepicker' ).datepicker({
				dateFormat: "dd/mm/yy",
			});

			///$('.datepicker').removeAttr( 'readonly' );
			
		});
}

function displayRF( mode ){
	if(mode == 2){ // FORMULA
		$('#idmeterproperty').val('');

		$('.rf').css('display','block');
		$('.shformula').css('display','none');
		$('#IS_12').addClass('hide');
		
		$('input[name=IS_12]').attr('checked', false);
		
	} else { // REAL
		$('.shformula').css('display','block');
		$('.rf').css('display','none');
		$('#IS_12').removeClass('hide');
		if($('#hmeter').val()<3){
			$('.hourly_m').css('display','none');
		}
	}
}

function showFLATS(){
	var id_building = $('#buildings').val();
	var mode = $('#idsupplytype').val();

	jQuery.getJSON("ajax/json.php?action=get_multicheck_flats_list_by_id_building&id=" + id_building + "&mode=" + mode,
	function(data){
		$('#flats_list').html(data.list);
	});
}

function insertMETERINTOFORMULA(nome, s){
	var my_input = $('#' + s);
	var position = parseInt(my_input.caret().start) + 1;
	
	var formula = my_input.val();
	var pre = formula.substring(0, position - 1 );
	var next = formula.substring(position - 1, formula.length);
	
	var spre = pre + ' ' + nome + ' ';
	var new_formula = spre + next;
	var new_position = spre.length;
	
	my_input.val(new_formula);
	my_input.focus();
	
	my_input.caret(new_position, new_position );
}

$("#dialog_formula").dialog({
		autoOpen: false,
		height: 400,
		width: 900,
		modal: true,
		title: 'Formula',
		buttons: {
			'Save': function() {
				$('#mk-formula').val( $('#input-formula').val() );
				$('#edit_FORMULA').val( $('#input-formula').val() );
				$(this).dialog('close');
			},
			'Close': function(){
				$(this).dialog('close');
			}
		},
		close: function(){
		}
	});
	
$("#dialog_ab").dialog({
		autoOpen: false,
		height: 400,
		width: 900,
		modal: true,
		title: 'Formula',
		buttons: {
			'Save': function() {
				$('#a').val( $('#input-a').val() );
				$('#b').val( $('#input-b').val() );
				$('#edit_A').val( $('#input-a').val() );
				$('#edit_B').val( $('#input-b').val() );

				$(this).dialog('close');
			},
			'Close' : function(){
				$(this).dialog('close');
			}
		},
		close: function(){
		}
	});	

$("#dialog_meter").dialog({
		autoOpen: false,
		height: 600,
		width: 900,
		position: [ 'center', 60 ],
		modal: true,
		title: 'Misuratore',
		buttons: {
			'Delete': function() {
				$( "#dialog-confirm" ).dialog({
					resizable: false,
					height:160,
					modal: true,
					buttons: {
						'Delete': function() {
							// ELIMINO
							var id_del = $('#ID_METER').val();
							jQuery.getJSON("ajax/json.php?action=delete_meter&id=" + id_del,
							function(data){
								if(data.success){
									$( this ).dialog( "close" ); // CHIUDO I DIALOG
									$( '#dialog_meter' ).dialog( "close" );
									$('#message').html(data.message);
								}
							});							
							$( this ).dialog( "close" );
						},
						"Cancel": function() {
							$( this ).dialog( "close" );
						}
					}
				});
			},
			'Save': function() {
				$("#update_meters").ajaxSubmit(function(data){
					if(data.success){
						$("#dialog_meter").dialog('close');
					}
					else{
						alert('Errore');
					}
				});
			},
			'Close': function() {
				$(this).dialog('close');
			}
		},
		close: function(){
			var id_building = $('#buildings').val();
			showFORM(id_building);
			showMETERS(id_building);
		}
	});		