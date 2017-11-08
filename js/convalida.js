$(document).ready(function() {
	$(window).resize(function() {
		height = $("#content_map").height();
		width = $("#content_map").width();
		$("#map_canvas").css("height", height + "px");
		$("#map_canvas").css("width", width + "px");
	});
	
	$('#show').click(function(){
		id = $('#buildings1').val();
		year = $('#year').val();
		upload_type = $('#upload_type').val();
	
		if(year != '' &&  id != '' && upload_type != ''){
			// VADO AL TAB SUCCESSIVO
			selezioneEdificio(id, upload_type, year, false);
		} else {
				$( "#dialog-confirm" ).dialog({
				resizable: false,
				height:160,
				modal: true,
				buttons: {
					"Ok": function() {
						$( this ).dialog( "close" );
					}
				}
			});
		}
	});
	
	// al passaggio del mouse visualizzo i controlli girofondoscala e sostituzione
	$('.npvm2').live({
		  mouseover: function() {
			// se i controlli sono nascosti li visualizzo
			if ($( this ).find('.turncontrols img').is(':hidden')) {
				
				type = $( this ).attr('tipo');
				id_type = $( this ).attr('id_tipo');
			
				// se la misurazione è stata pubblicata non faccio nulla
				if(is_publish(type, id_type)){
					
					$( this ).find('img').show();
					$( this ).mouseout( function(){
						$( this ).find('img').hide();
					});
					
				}
			} 
		 }
	});
	
	
	
	
	
	$('#delete-ms').click(function(){
		var year = $('#year').val();
		var upload_type = $('#upload_type').val();
		var buildings = $('#buildings1').val();
		
		if(year && upload_type && buildings){
			var answer = confirm('Confermi la cancellazione delle convalide?');
			if (answer){
				// elimino le misurazioni correlate
				jQuery.getJSON("ajax/json.php?action=delete_all_ms&building=" + buildings + '&year='+year + '&upload_type=' + upload_type,
				  function(data){
						if(data.success){
							alert(data.cnt + ' record eliminati');
						} else {
							alert('Non ci sono record da eliminare');
						}
				  });			
			}			
		} else {
			alert('Compila edificio, anno, numero invio');
		}
	});
	
	$('#ripubblica').click(function(){
		var year = $('#year').val();
		var upload_type = $('#upload_type').val();
		var buildings = $('#buildings1').val();
		
		if(year && upload_type && buildings){
			var answer = confirm('Confermi la cancellazione e successiva ripubblicazione delle convalide?');
			if (answer){
				// elimino le misurazioni correlate
				jQuery.getJSON("ajax/json.php?action=delete_all_ms&building=" + buildings + '&year='+year + '&upload_type=' + upload_type,
				  function(data){
						if(data.success){
							pubblica(buildings, year, upload_type);
						}
						
				  });			
			}			
		} else {
			alert('Compila edificio, anno, numero invio');
		}
	});
	
	
	
	$('.restart-meter').live('click', function(){
		id_meter = $( this ).attr('alt');
		var id_measure = $('#turn-' + id_meter).attr('title');
		// alert(id_measure);
	
		// ottengo il valore del fondoscale
		jQuery.getJSON("ajax/json.php?action=get_turn_around_values&id=" + id_meter + '&id_measure=' + id_measure,
		function(data){
			jConfirm( data.valori, data.intestazione, function(r) {
				if(r){ // se ho confermato eseguo la procedura
					turn_around_meter(id_meter);
				}
			});
		});	
	});
	
	$('.change-meter').live('click', function(){
		var id_old_meter = $( this ).attr('alt');
		var id_measure = $('#turn-' + id_old_meter).attr('title');
		
		// carico i valori del vecchio misuratore e inizializzo i campi del nuovo
		replace_meter_form(id_old_meter, id_measure);
		
		$("#dialog-change-meter").dialog({ title:'Change Meter ID ' + id_meter });
		$("#dialog-change-meter").dialog('open');
	
	});
	
	$("#dialog-change-meter").dialog({
		autoOpen: false,
		height: 440,
		width: 420,
		modal: true,
		buttons: {
			'Salva': function() {
				save_replace_meter();
	
			},
			'Close': function(){
				$(this).dialog('close');
			}
		},
		close: function(){
		}
	});
});


/*
 * Pubblica automaticamente tutti i valori dell'edificio per il periodo selezionato
 */

function pubblica(buildings, year, upload_type){
	
	selezioneEdificio(buildings, upload_type, year, true);
	
	
	
}


// controlla se la riga è stata pubblicata type(direct / shared) id_type (id_flat / id_meter)
function is_publish( type, id_type ){
	if(type == 'dir'){
		return $('#dir-' + id_type).length;
	}
	else if(type == 'sha'){
		return $('#sha-' + id_type).length;
	}
}

function replace_meter_form(id_old_meter, id_measure){
	jQuery.getJSON("ajax/json.php?action=replace_meter_form&id=" + id_old_meter,
	function(data){
		// compila i valori riepilogativi del contatore
		var row = data.row;
		$('#meter-data ul').html('');
		$('#D_CHANGE').val('');
		
		// aggiorno i campi hidden d'appoggio
		$('#change_ID_METER').val(id_old_meter);
		$('#change_ID_MEASURE').val(id_measure);
		
		$('#meter-data ul').append('<li>'+ row.CODE_METER +'</li>');
		// $('#meter-data ul').append('<li>'+ row.MATRICULA_ID +'</li>');

		$('#old_meter').html(data.old_inputs);
		$('#new_meter').html(data.new_inputs);
	});
}

function save_replace_meter(){
	
	$("#frm-replace").ajaxSubmit(function(data){
		if(data.success){
			
			console.log( data );
			
			message('g', data.message);
			$("#dialog-change-meter").dialog('close');
			
			$('td#main-' + data.id_meter + ' #ID_CHANGE').val( data.id_change );
			
			//$('td#main-' + data.id_meter).append('<input type="hidden" id="ID_CHANGE" value="'+data.id_change+'">');
			$('td#main-' + data.id_meter).append('<input type="hidden" name="START_1" value="'+data.start_1+'">');
			$('td#main-' + data.id_meter).append('<input type="hidden" name="START_2" value="'+data.start_2+'">');
			$('td#main-' + data.id_meter).append('<input type="hidden" name="START_3" value="'+data.start_3+'">');
			$('td#main-' + data.id_meter).append('<input type="hidden" name="END_1" value="'+data.end_1+'">');
			$('td#main-' + data.id_meter).append('<input type="hidden" name="END_2" value="'+data.end_2+'">');
			$('td#main-' + data.id_meter).append('<input type="hidden" name="END_3" value="'+data.end_3+'">');

			aggiornaContatoriCondivisi();
			$('#turn-' + data.id_meter).html('');
				
			// non eseguire prima altrimenti restituisce Nan
			calcolaValoriMisuratore(data.id_meter);

		} else {
			message('r', data.message);
		}
		
		$('#message').html(data.message);
	});	
}


function turn_around_meter(id_meter){
	var id_measure = $('#turn-' + id_meter).attr('title');
	jQuery.getJSON("ajax/json.php?action=turn_around_meter&id=" + id_meter + '&id_measure=' + id_measure,
	function(data){
		Element = $('table.misuratore tr#' + data.id_meter).first();
		// $(Element).find('.inputvalues input.TURNAROUND').val(data.turnaround);
	
		var input_turn = $(Element).find('.inputvalues input.TURNAROUND');
		if(!input_turn.length){
			Element = $('table.shared tr#' + data.id_meter).first();
			var input_turn = $(Element).find('.inputvalues input.TURNAROUND');
		}
		
		input_turn.val(data.turnaround);
		
		calcolaValoriMisuratore(data.id_meter);
		aggiornaContatoriCondivisi();
		
		message(data.success, data.message);
		// rimuovo i pulsanti
		if(data.success == 'g'){
			$('#turn-' + data.id_meter).html('');
		}
	});	
}

function listenerCambiaStato()	{
				var id = $(this).attr('name');
				//console.log(this);
				
				// se non esiste il pulsante "pubblica" non faccio niente
				var publish_reference = $(this).attr('alt');
				if($('#' + publish_reference).length == 0 ){
					return false;
				} 
				
				checkButton = this;
				id_misuratore = $(checkButton).parents('tr').first().attr('id');
				id_measure = $(checkButton).parent().find('#id_measure').val();

				//console.log($(this).attr('src'));

				if($(this).attr('src') == 'images/Bad.png')	{
					stato = 'Corrected';

					jQuery.getJSON("ajax/json.php?action=update_status&id=" + id_misuratore + '&id_measure='+id_measure+'&status=' + stato,
						function(data){
							if(data.success)	{
								$('input#last' + id).removeAttr('readonly');
								$('input#last' + id).addClass('write');
								$('input#dt' + id).removeAttr('readonly');
								$('input#dt' + id).addClass('write');
								$('input#dt' + id).datepicker();
								$(checkButton).attr('src', 'images/Modify.png');
								calcolaValoriMisuratore(id_misuratore);
								
								// Se la misura diventa Bad o privacy (o viceversa) devo ricalcolare tutte le medie corrette
								$('.appartamento').each(function(index, Element){
									calcolaValoriAppartamentoBad(Element);
								});
								aggiornaContatoriCondivisi();
							}	
						});
				}
					
				else if($(checkButton).attr('src') == 'images/Good.png')	{
					stato = 'Wrong';
					
					jQuery.getJSON("ajax/json.php?action=update_status&id=" + id_misuratore + '&id_measure='+id_measure+'&status=' + stato,
						function(data){
							if(data.success)	{
								$(checkButton).attr('src', 'images/Bad.png');
								calcolaValoriMisuratoreCorretti(id_misuratore);
								// Se la misura diventa Bad o privacy (o viceversa) devo ricalcolare tutte le medie corrette
								$('.appartamento').each(function(index, Element){
															calcolaValoriAppartamentoBad(Element);
														});
								aggiornaContatoriCondivisi();
							}
								
						  });
				}
					
				else if($(checkButton).attr('src') == 'images/Modify.png')	{
					stato = 'Privacy';
					
					jQuery.getJSON("ajax/json.php?action=update_status&id=" + id_misuratore + '&id_measure='+id_measure+'&status=' + stato,
						function(data){
							if(data.success)	{
								$(checkButton).attr('src', 'images/Privacy.png');
								calcolaValoriMisuratoreCorretti(id_misuratore);
								$('input#last' + id).attr('readonly', 'readonly');
								$('input#last' + id).removeClass('write');
								$('input#dt' + id).attr('readonly', 'readonly');
								$('input#dt' + id).removeClass('write');
								$('input#dt' + id).datepicker("destroy"); 
								// Se la misura diventa Bad o privacy (o viceversa) devo ricalcolare tutte le medie corrette
								$('.appartamento').each(function(index, Element){
															calcolaValoriAppartamentoBad(Element);
														});
								aggiornaContatoriCondivisi();
							}
								
						  });
					
				}
				
				else if($(checkButton).attr('src') == 'images/Privacy.png')	{
					stato = 'Validated';
					
					jQuery.getJSON("ajax/json.php?action=update_status&id=" + id_misuratore + '&id_measure='+id_measure+'&status=' + stato,
						function(data){
							if(data.success)	{
								$(checkButton).attr('src', 'images/Good.png');
								$('input#last' + id).attr('readonly', 'readonly');
								$('input#last' + id).removeClass('write');
								$('input#dt' + id).attr('readonly', 'readonly');
								$('input#dt' + id).removeClass('write');
								$('input#dt' + id).datepicker("destroy"); 
								calcolaValoriMisuratore(id_misuratore);
								// Se la misura diventa Bad o privacy (o viceversa) devo ricalcolare tutte le medie corrette
								$('.appartamento').each(function(index, Element){
															calcolaValoriAppartamentoBad(Element);
														});
								aggiornaContatoriCondivisi();
							}
								
						  });
					
				}
				
				else 	{
					//console.log('Validated');
					stato = 'Validated';
					jQuery.getJSON("ajax/json.php?action=update_status&id=" + id_misuratore + '&id_measure='+id_measure+'&status=' + stato,
						function(data){
							if(data.success)	{
								$(checkButton).attr('src', 'images/Good.png');
								calcolaValoriMisuratoreCorretti(id,0);
								$('input#last' + id).attr('readonly', 'readonly');
								$('input#last' + id).removeClass('write');
								$('input#dt' + id).attr('readonly', 'readonly');
								$('input#dt' + id).removeClass('write');
								$('input#dt' + id).datepicker("destroy"); 
								aggiornaContatoriCondivisi();
							}
							
						  });
				}
		}

function initialize()	{
	$(document).ready(function() {
		height = $(document).height()-500;
		height = 400;
		width = $(document).width()-380;
		$("#map_canvas").css("height", height + "px");
		$("#map_canvas").css("width", width + "px");
		var latlng = new google.maps.LatLng(41.442, 12.392);
		var myOptions = {
		  zoom: 5,
		  center: latlng,
		  mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		map = new google.maps.Map(document.getElementById("map_canvas"),myOptions);
		$(window).resize();
	});
}
			
	$(document).ready(function()	{
		$('#federations1').change(function(){
					 $('#hcompanys1').html('');
					 $('#buildings1').html('');
					aggiornaMappa(1);
					selectHC(1);
					selectBLD(1);
				});
		 $('#hcompanys1').change(function(){
					$('#buildings1').html('');
					aggiornaMappa(1);
					selectBLD(1);
		});
		$('#buildings1').change(function(){
					aggiornaMappa(1);
		});
	});
	
	function checkSEND(){
		var bld = $('#buildings1');
		var upl = $('#upload_type');
		var yer = $('#year');
		
		if(bld.val() != '' && upl.val() != '' && yer.val() != ''){
			return true;
		}
		else{
			return false;
		}
	}
	
	function selectHC(id)	{
		federation = $('#federations' + id).val();
		jQuery.getJSON("ajax/json.php?action=select_hc&fed=" + federation,
        function(data){
			options = '<option selected="selected" value="">- Scegli housing company</option>	' + "\n\r";
          jQuery.each(data, function(i,item){
				options += '<option  value="' + item.optionValue + '">' + item.optionDisplay + '</option>' + "\n\r";
				
				
          });
		  $('#hcompanys' + id).html(options); 
		 
		  //map.setCenter(cornice.getCenter());
        });
	}
	
	function selectBLD(id)	{
		hcompanys = $('#hcompanys' + id).val();
		federation = $('#federations' + id).val();
		jQuery.getJSON("ajax/json.php?action=select_bld&hc=" + hcompanys + "&fed=" + federation,
        function(data){
			options = '<option selected="selected" value="">- Scegli edificio</option>	' + "\n\r";
          jQuery.each(data, function(i,item){
				options += '<option  value="' + item.optionValue + '">' + item.optionDisplay + '</option>' + "\n\r";
          });
		  $('#buildings' + id).html(options);
		 
		  //map.setCenter(cornice.getCenter());
        });
	}
	
	function selezioneEdificio(id, upload, year, pubblica)	{
		idbuilding = id;
		$( "#tabs" ).tabs( "option", "selected", 1 );
		$('#tabs-1').loady({
						url: "ajax/tab_validate.php?id=" + id + '&upload=' + upload + '&year=' + year,
						parser: function(data) { 
									
									$('#tabs-1').html(data);
									$('.puls_validate img').click(listenerCambiaStato);
									
									periodDays = parseInt($('#periodlength').html());
									
									$('input.consumo').change(function(){
										id = $(this).attr('name');
										area = $(this).parents('table.appartamento').first().find('input[name="area_flat"]').first().val();
										////console.log("area:" + area);
										calcolaValoriMisuratore(id);
										metertype = $(this).parents('tr').first().attr('type');
										//avgNPV(metertype);	
									});
									$('table.appartamento input[name="cknumid"]').change(function(){

										//console.log('Occupato');
										area = $(this).parent().find('input[name="area_flat"]').first().val();
										aggiornaContatoriCondivisi();
										idAppartamento = $(this).parents('table.appartamento').first().attr('id');
										salvaStatoOccupato(idAppartamento);
										$(this).parents('table.appartamento').first().find('table.misuratore tr').each(function(index, Element){
														id = $(Element).attr('id');
														if (id > 0) {
															calcolaValoriMisuratore(id);
															//calcolaValoriMisuratoreCorretti(id);
														}
														
														////console.log(id);
										});
									});
									$('.appartamento').each(function(index, Element){
															calcolaValoriAppartamentoGood(Element);
														});
									$('.appartamento').each(function(index, Element){
															calcolaValoriAppartamentoBad(Element);
														});
										
									$('.direct img.img_puls').click(function()	{
												id = $(this).parents('table.appartamento').first().attr('id');
												inviaDatiAppartamento(id);
											});
									$('.shared .img_puls').click(function()	{
												id = $(this).parents('tr').first().attr('id');
												inviaDatiMisuratore(id);
											});
									$('.ultima_misurazione').change(function(){
											var misurazioniArr = new Array();
											misurazione = $(this).val();
											tr = $(this).parents('tr').first();
											area = $(this).parents('table.appartamento').first().find('input[name="area_flat"]').first().val();
											id_misuratore = $(tr).first().attr('id');
											////console.log("area:" + area);
											calcolaValoriMisuratore(id_misuratore, area, tr);
											metertype = $(tr).first().attr('type');
											//avgNPV(metertype);
											$(tr).find('.inputvalues input.ultima_misurazione').each(function(index, Element){
															misurazioniArr.push(parseFloat($(Element).val()));
														});
											dataString = jQuery.toJSON(misurazioniArr);
											jQuery.post("ajax/json.php", {action: "update_measures", 
																json: dataString, 
																ID_UPLOADTYPE: $('input#ID_UPLOADTYPE').val(), 
																ANNO_MS: $('input#ANNO_MS').val(),
																ID_METER: id_misuratore
															}, 
													function(data) {
													
													});
										});
										
									
									$('td.inputvalues .data').change(function(){
											date = $(this).val();
											tr = $(this).parents('tr').first();
											id_misuratore = $(tr).attr('id');
											
											jQuery.getJSON("ajax/json.php?action=get_days&data=" + date + "&id=" + id_misuratore,
													function(data){
														$(tr).find('input#days').val(data.days);
														area = $(tr).find('input[name="area_flat"]').first().val();
														calcolaValoriMisuratore(id_misuratore, area);
														var misurazioniArr = new Array();
														$(tr).find('.inputvalues input.ultima_misurazione').each(function(index, Element){
															misurazioniArr.push(parseFloat($(Element).val()));
														});
														dataString = jQuery.toJSON(misurazioniArr);
														jQuery.post("ajax/json.php", {action: "update_measures", 
																	json: dataString, 
																	ID_UPLOADTYPE: $('input#ID_UPLOADTYPE').val(), 
																	ANNO_MS: $('input#ANNO_MS').val(),
																	ID_METER: id_misuratore,
																	DATE: date
																	}, 
															function(data) {
															
															});
													});
										});
									
									$('#tabs-1 table.appartamento').each(function(index, Element) {
											validato = true;
											$(Element).find('table.misuratore tr').
												each(function(index, Element){
													if( id_meter = $(Element).attr('id') ){ // salta le intestazioni
														
														stato = $(Element).find('.puls_validate img').first().attr('src');
														id_measure = $(Element).find('input#id_measure').first().val();
														
														if (id_measure > 0)	{
															if(stato == "images/Good-grey.png") {
																validato = false;
															}
														}
													}
												});
										});
									
										
									$('a.publish').click(function(){
										
										id = $(this).parents('table.appartamento').first().attr('id');
										//alert(id);
										Element = $('table.appartamento#' + id);
										occupied = $(Element).find('form input[name="cknumid"]').first().is(':checked');
										
										var pulsante = this;
										$(this).parents('table.appartamento').first().find('table.misuratore tr').each(function(index, Element) {
											id_meter = $(Element).attr('id');
											id_measure = $(Element).find('input#id_measure').val();
											pv = $(Element).find('input.PV').val();
											pvm2 = $(Element).find('input.PVM2').val();
											
											if (id_measure > 0)	{
												status = 'valid';
												idu = $('#idu').val();
												
												// Verifico se le ultime due misurazioni sono valide e se non è settata la privacy
												if($(Element).find('.ultima_misurazione.wrong').size() > 0)
													status = 'wrong';
												if($(Element).find('.penultima_misurazione.wrong').size() > 0)
													status = 'wrong';
												
												if( ! ($(Element).find('input.CNPV').val() >= 0) )
													status = 'nd';

												jQuery.post("ajax/json.php", {	action: "put_outputs", 
													//json: dataString, 
													idu: idu,
													PV: pv, 
													PVM2: pvm2, 
													NPV: $(Element).find('input.NPV').val(), 
													NPVM2: $(Element).find('input.NPVM2').val(),  
													CNPV: $(Element).find('input.CNPV').val(), 
													CNPVM2: $(Element).find('input.CNPVM2').val(), 
													NPVM2F1: $(Element).find('input.NPVM2F1').val(), 
													ID_FLAT: id,
													ID_METER: id_meter,
													ID_MEASURE: id_measure,
													OCCUPIED: occupied,
													STATUS: status
													}, 
													function(data) {
														
														// rimuove il pulsante di pubblicazione
														$(pulsante).remove();
												});
											}
										});	
									});
									
									$('a.shared_publish').click(function(){
										var pulsante = this;
										Element = $(this).parents('tr').first();
										
										id_meter = $(Element).attr('id');
										id_measure = $(Element).find('input#id_measure').val();
										pv = $(Element).find('input.PV').val();
										pvm2 = $(Element).find('input.PVM2').val();
										
										$(this).parents('tr').first().each(function(index, Element) {
											id_misuratore = $(Element).attr('id');
											////console.log('aggiornaContatoriCOndivisi: ' + id_misuratore);
											if (id_misuratore>0)	{
												status = 'valid';
												area = 0;
												area_occupied = 0;
												flats = $(Element).find('form input.idflats').first().val();
												var flatsArr = flats.split(',');
												// Questa procedura invia la misura di ogni appartamento per i contatori condivisi
												// in realtà basta salvare il valore NPV/m2, quindi verrà sostituita con una nuova
												/*
												for(i=0;i<flatsArr.length;i++)	{
													flat_area = flatArea(flatsArr[i]);
													flat_occupied = flatIsOccupied(flatsArr[i]);
													if (flat_occupied)	{
														jQuery.post("ajax/json.php", {	action: "put_outputs", 
														//json: dataString, 
														PV: pvm2 * flat_area, 
														PVM2: pvm2, 
														NPV: $(Element).find('input.NPVM2').val() * flat_area, 
														NPVM2: $(Element).find('input.NPVM2').val(),  
														CNPV: $(Element).find('input.CNPVM2').val() * flat_area, 
														CNPVM2: $(Element).find('input.CNPVM2').val(), 
														ID_FLAT: flatsArr[i],
														ID_METER: id_meter,
														ID_MEASURE: id_measure
														}, 
														function(data) {
															$(pulsante).html('Pubblicato');
															$(pulsante).attr('class', 'shared_published');
													});
													}
												}
												*/
												totalArea = 0;
												for(i=0;i<flatsArr.length;i++)	{
													if(flatIsOccupied(flatsArr[i]))
														totalArea += flatArea(flatsArr[i]);
												}
												
												// Verifico se le ultime due misurazioni sono valide e se non è settata la privacy
												if($(Element).find('.ultima_misurazione.wrong').size() > 0)
													status = 'nd';
												if($(Element).find('.penultima_misurazione.wrong').size() > 0)
													status = 'nd';
												
												if( ! ($(Element).find('input.CNPV').val() >= 0) )
													status = 'nd';
												
												idu = $('#idu').val();
												jQuery.post("ajax/json.php", {	action: "put_outputs", 
														idu: idu,
														PV: pvm2 * totalArea, 
														PVM2: pvm2, 
														NPV: $(Element).find('input.NPVM2').val() * totalArea, 
														NPVM2: $(Element).find('input.NPVM2').val(),  
														CNPV: $(Element).find('input.CNPVM2').val() * totalArea, 
														CNPVM2: $(Element).find('input.CNPVM2').val(), 
														NPVM2F1: $(Element).find('input.NPVM2F1').val(), 
														ID_FLAT: flatsArr[i],
														ID_METER: id_meter,
														ID_MEASURE: id_measure,
														STATUS: status
														}, 
														function(data) {
															// rimuove il pulsante di pubblicazione
															if(pubblica && ($(pulsante).attr('id')==$(ultimoCondiviso).attr('id')))	{
																$( "#tabs" ).tabs('select', 2);
																load_formulas(idbuilding, year, upload, true);
															}
															if(pubblica)
																$(document).scrollTop( $(pulsante).offset().top ); 
															$(pulsante).remove();
															
													});
											}
										});
									});
									
									ultimoCondiviso = $('a.shared_publish').last();
									
									
									aggiornaContatoriCondivisi();
									aggiornaContatoriFormula();
									
									pulsanti_contatore();
									
									
										if(pubblica)	{
											$('a.publish').trigger('click');
											$('a.shared_publish').trigger('click');
										}
							
									
									
								}
					});
			//alert(id);
			load_formulas(idbuilding, year, upload, false);
			}
		
		
		function load_formulas(id, year, upload, publishformula)	{
			$('#tabs-3').loady({
						url: "ajax/tab_formulas.php?id="+id+ '&upload=' + upload + '&year=' + year,
						parser: function(data) {
								$('#tabs-3').html(data);
								
								$('a.formula_publish').click(function(){
										pulsante = this;
										Element = $(this).parents('tr').first();
										
										id_meter = $(Element).attr('id');
										id_measure = $(Element).find('input.id_measure').val();
										pv = $(Element).find('input.PV').val();
										pvm2 = $(Element).find('input.PVM2').val();
										
										//$('table.shared tr').each(function (index, Element)	{
										$(this).parents('tr').first().each(function(index, Element) {
											id_misuratore = $(Element).attr('id');
											////console.log('aggiornaContatoriCOndivisi: ' + id_misuratore);
											if (id_misuratore>0)	{
												area = 0;
												area_occupied = 0;
												flats = $(Element).find('form input.idflats').first().val();
												var flatsArr = flats.split(',');
												

												totalArea = 0;
												for(i=0;i<flatsArr.length;i++)	{
													if(flatIsOccupied(flatsArr[i]))
														totalArea += flatArea(flatsArr[i]);
												}
												idu = $('#idu').val();
												jQuery.post("ajax/json.php", {	action: "put_outputs", 
														idu: idu,
														PV: pvm2 * totalArea, 
														PVM2: pvm2, 
														NPV: $(Element).find('input.NPV').val(), 
														NPVM2: $(Element).find('input.NPV').val() / totalArea,  
														CNPV: $(Element).find('input.NPV').val(), 
														CNPVM2: $(Element).find('input.NPV').val() / totalArea, 
														NPVM2F1: $(Element).find('input.NPVM2F1').val(), 
														ID_FLAT: flatsArr[i],
														ID_METER: id_meter,
														ID_MEASURE: id_measure,
														STATUS: $(Element).find('input.status').val(), 
														}, 
														function(data) {
															$(pulsante).html('Pubblicato');
															$(pulsante).attr('class', 'shared_published');
															load_formulas(id, year, upload, publishformula);

													});
											}
										});
									});
								if(publishformula)	{
									$('a.formula_publish').first().click();
									//console.log($('a.formula_publish').first());
									if($('a.formula_publish').size()==0)	{
										$( "#tabs" ).tabs('select', 0);
										if($('a.publish').size()==0)	
											alert("Pubblicazione completa!");
										else
											alert("Pubblicazione non completa! Verificare manualmente");
									}
								}
									
								
							}
						});
	}
	
	function aggiornaMappa(id)	{
		return;
		federation = $('#federations' + id).val();
		hcompanys = $('#hcompanys' + id).val();
		buildings = $('#buildings' + id).val();
		
		jQuery.getJSON("ajax/json.php?action=getbuildings&fed=" + federation + "&hc=" + hcompanys + "&bld=" + buildings,
        function(data){
			var cornice = new google.maps.LatLngBounds();
			deleteOverlays();
			icon = new google.maps.MarkerImage('images/houseicon.png');
			jQuery.each(data, function(i,item){
				coordinate = new google.maps.LatLng(parseFloat(item.LAT_BLD), parseFloat(item.LNG_BLD));
				//addMarker(coordinate);
				 var marker = new google.maps.Marker({
						  position: coordinate, 
						  map: map,
						  icon: icon,
						  title: item.id
							});
			  
				  google.maps.event.addListener(marker, 'click', function(event) {
					upload = $('#upload_type').val();
					year = $('#year').val();
					////console.log(event);
					////console.log(marker);
					selezioneEdificio(item.id, year, upload);
			  });
				cornice.extend(coordinate);
			});
		  
		  if (!cornice.isEmpty())
			map.fitBounds(cornice);
			showOverlays();
		  //map.setCenter(cornice.getCenter());
        });
	}
	
	// Questa funzione mi calcola solo i valori dei misuratori che risultano corretti
	function calcolaValoriAppartamentoGood(Element)	{
		//console.log(Element);
		area_flat = $(Element).find('input[name="area_flat"]').val();
		
		$(Element).find('.misuratore tr').each(function(index, Element){
														////console.log('area: ' + area_flat);
														id = $(Element).attr('id');
														stato = $(Element).find('.puls_validate img').first().attr('src');
														penultimaWrong = $(Element).find('.penultima_misurazione').hasClass('wrong');
														if ((stato == "images/Good.png" || stato == "images/Modify.png" || stato == "images/Good-grey.png") && !(penultimaWrong))	
															calcolaValoriMisuratore(id, area_flat, Element);
														
													});
	}
	
	// Questa funzione calcola i valori dei misuratori Wrong, dopo che sono stati calcolati tutti i corretti
	// altrimenti non riesco a calcolare le medie
	function calcolaValoriAppartamentoBad(Element)	{
		
		area_flat = $(Element).find('input[name="area_flat"]').val();
		
		$(Element).find('.misuratore tr').each(function(index, Element){
														////console.log('area: ' + area_flat);
														id = $(Element).attr('id');
														stato = $(Element).find('.puls_validate img').first().attr('src');
														penultimaWrong = $(Element).find('.penultima_misurazione').hasClass('wrong');
														if (stato == "images/Bad.png" || stato == "images/Privacy.png" || penultimaWrong)	
															calcolaValoriMisuratoreCorretti(id);
														////console.log(id);
													});
	}
	
	function flatArea(id){
		Element = $('table.appartamento#' + id);
		area_flat = $(Element).find('input[name="area_flat"]').val();
		return parseFloat(area_flat);
	}
	
	function flatIsOccupied(id)	{
		Element = $('table.appartamento#' + id);
		return $(Element).find('form input[name="cknumid"]').first().is(':checked');
	}
	
	function calcolaValoriMisuratore(id){
		// console.log("period: " + periodDays);
		
		if (id > 0)	{
			
			// evidenzio la data errata
			if( $('#dt' + id).val() == '00/00/0000' ){
				$('#dt' + id).addClass('date_error');
			}
		
			var npvM2, pvM2, ultima, penultima;
			area_flat = flatArea(id);
			consumo = ultima = penultima = start = end = 0;
			consumoF1 = 0;
			//var Element = $('.misuratore tr#' + id).first();
			
			Element = $('table.misuratore tr#' + id).first();
			metertype = $(Element).attr('type');
			id_misurazione = parseInt( $(Element).find('td input#id_measure').first().val() );
			// alert(id_misurazione);
			stato = $(Element).find('.puls_validate img').first().attr('src');
			//console.log(Element);
			//console.log('id_misurazione:'+id_misurazione);
			////console.log('stato:'+stato);
			if (id_misurazione > 0)	{
				area_flat = $(Element).parents('table.appartamento').first().find('input[name="area_flat"]').first().val();
				if ($(Element).parents('table.appartamento').first().find('input[name="cknumid"]').first().is(':checked'))
					occupato = 1;
				else
					occupato = 0;
				////console.log('occupato:' + occupato);
				fasciaOraria=0;
				fondoscala =  parseInt ( $(Element).find('.inputvalues input.TURNAROUND').val() );
				
				$(Element).find('.inputvalues input.ultima_misurazione').each(function(index, Element){
																ultima += parseFloat($(Element).val());
																fasciaOraria++;
															});
				$(Element).find('.penultima_misurazione').each(function(index, Element){
																penultima += parseFloat($(Element).html());
															});	
				
				// verifico se è un misuratore triorario
				if(fasciaOraria > 1)	{
					$(Element).find('.inputvalues input.ultima_misurazione').first().each(function(index, Element){
																ultimaF1 = parseFloat($(Element).val());
															});	
					$(Element).find('.penultima_misurazione').first().each(function(index, Element){
																penultimaF1 = parseFloat($(Element).html());
															});	
					consumoF1 = ultimaF1 - penultimaF1;	
					
				}

				// è stato eseguito un girofondoscala
				if(fondoscala>0) {
					// aggiungo l'icona grigia
					$('#turn-' + id).addClass('turn-dis');
					console.log('#turn-' + id + ' id_misurazione: ' + id_misurazione);
					
					consumo = (fondoscala - penultima) + ultima;
				} else
					consumo = ultima - penultima;	

				sostituito = parseInt ( $(Element).find('td input#ID_CHANGE').first().val() );

				// è stato sostituito
				if(sostituito > 0)	{
					
					// aggiungo l'icona grigia
					$('#turn-' + id).addClass('change-dis');
					
					start = parseFloat($('td#main-' + id + ' input[name="START_1"]').val());
					end = parseFloat($('td#main-' + id + ' input[name="END_1"]').val());
					if(fasciaOraria > 1)	{
						consumoF1 = (end - penultimaF1) + (ultimaF1 - start);
						start += parseFloat($('td#main-' + id + ' input[name="START_2"]').val());
						start += parseFloat($('td#main-' + id + ' input[name="START_3"]').val());
						end += parseFloat($('td#main-' + id + ' input[name="END_2"]').val());
						end += parseFloat($('td#main-' + id + ' input[name="END_3"]').val());
					}

					if(end==0)	{ // non ho inserito l'ultima misurazione del vecchio contatore
						$(Element).find('.penultima_misurazione').removeClass('good').addClass('wrong');
						consumo = 0;
					} else
						consumo = (end - penultima) + (ultima - start);
				} 
			
				area = parseFloat(area_flat);
				////console.log('consumo:' + consumo);
				$(Element).find('#pvFull').html(consumo);
				consumo = parseFloat(consumo);
				pvM2 = Math.round(consumo/area_flat*1000) / 1000;
				pvM2F1 = Math.round(consumoF1/area_flat*1000) / 1000;
				$(Element).find('#PVM2').html(pvM2);

				days = $(Element).find('input#days').val();
				//console.log('days:' +days);
				npvM2 = Math.round((consumo/area_flat) * periodDays / days * 1000) / 1000;
				npvM2F1 = Math.round(pvM2F1 * periodDays / days * 1000) / 1000;
				npv = Math.round(consumo * periodDays / days * 1000) / 1000;
				////console.log('npvM2:' +npvM2);
				$(Element).find('#npvM2').html(npvM2);
				if(npvM2F1>0)	{
					percF1 = Math.round(npvM2F1 * 1000 / npvM2) / 10;
					if($(Element).find('#npvM2F1').size() > 0)
						$(Element).find('#npvM2F1').first().html(+npvM2F1+'('+percF1+'%)');
					else
						$(Element).find('#npvM2').parent().append('<br><span id="npvM2F1">'+npvM2F1+'('+percF1+'%)</span>');
					$(Element).find('.NPVM2F1').val(npvM2F1);
				}
				
				$(Element).find('.PV').val(consumo);
				$(Element).find('.PVM2').val(pvM2);
				$(Element).find('.NPV').val(npv);
				$(Element).find('.NPVM2').val(npvM2);
				if (stato == "images/Bad.png" || stato == "images/Privacy.png")	{
					cnpvM2 = 0;
				}	else
					cnpvM2 = npvM2;
				
				if (stato == "images/Bad.png" || cnpvM2 < 0)	{
					$(Element).find('.restart-meter').css('display', 'block');
					$(Element).find('.change-meter').css('display', 'block');
				} else	{
					$(Element).find('.restart-meter').css('display', 'none');
					$(Element).find('.change-meter').css('display', 'none');
				}

				if(cnpvM2<0 || end == 0){
					Element.find('#npvM2').removeClass('good').addClass('wrong');
					
					// attivo i pulsanti di giro/sostituzione quando la data è corretta
					if( $('#dt' + id).val() != '00/00/0000' ){
						Element.find('#npvM2').addClass('turn');
					}

				} else {
					Element.find('#npvM2').removeClass('wrong').addClass('good');
					console.log('cnpvM2 good: ' + cnpvM2);
				}
				
				$(Element).find('.CNPV').val((cnpvM2 * area_flat) * occupato);
				$(Element).find('.CNPVM2').val(cnpvM2 * occupato);
				$(Element).find('#npvM2').html(cnpvM2 * occupato);

				calcolaValoriMisuratoreCorretti(id);
			}
			
	
			/*
			else {
				console.log( '#turn' + id );
			} */
			// nascondo i controlli di sostituzione se è già stato pubblicato
			// console.log(id);
			id_flat = $(Element).find('#id_flat').val();
			if( $('#dir-' + id_flat).length){
				// mostro i controlli
			} else {
				// nascondo i controlli
				if ( ($(Element).find('.turncontrols img').length) ){
					// alert($(Element).find('.turncontrols img').attr('alt') ); 
					$(Element).find('.turncontrols img').hide();
				}
			}				

		}
	}
	
	
	function calcolaValoriMisuratoreCorretti(id)	{
		console.log('ID: ' + id);
		Element = $('table.misuratore tr#' + id).first();

		metertype = $(Element).attr('type');

		area_flat = $(Element).parents('table.appartamento').first().find('input[name="area_flat"]').first().val();
		occupato = $(Element).parents('table.appartamento').first().find('input[name="cknumid"]').first().is(':checked');
		stato = $(Element).find('.puls_validate img').first().attr('src');
		npvM2Corr = 0;
		penultimaMis =  $(Element).find('.penultima_misurazione');
		penultimaWrong = $(penultimaMis).hasClass('wrong');

		if ((stato == "images/Good.png" || stato == "images/Modify.png" || stato == "images/Good-grey.png" ) && !(penultimaWrong))	{
			$(Element).find('.ultima_misurazione').addClass('good');
			$(Element).find('.ultima_misurazione').removeClass('wrong');
			Element.find('#npvM2').addClass('good');
			Element.find('#npvM2').removeClass('wrong');
			if(occupato)
				npvM2Corr = $(Element).find('#npvM2').html();
			else
				npvM2Corr = 0;

/*		} else if (stato == "images/Good-grey.png" && !(penultimaWrong))	{
			npvM2Corr = ""; */

		} else if (stato == "images/Bad.png" || stato == "images/Privacy.png" || penultimaWrong )	{
			if(occupato)
				npvM2Corr = avgNPVM2(metertype);
			else
				npvM2Corr = 0;
			// faccio diventare la valore della lettura rosso
			if(stato == "images/Bad.png" || stato == "images/Privacy.png") {
				$(Element).find('.ultima_misurazione').addClass('wrong');
				$(Element).find('.ultima_misurazione').removeClass('good');
			} else {
				$(Element).find('.ultima_misurazione').addClass('good');
				$(Element).find('.ultima_misurazione').removeClass('wrong');
			}
			
			Element.find('#npvM2').addClass('wrong');
			Element.find('#npvM2').removeClass('good');
		}
			
		npvFullCorr = npvM2Corr * area_flat;
		Element.find('input.CNPVM2').val(npvM2Corr);
		Element.find('input.CNPV').val(npvFullCorr);
		Element.find('#npvM2').html(npvM2Corr);
		
		if(npvM2Corr<0)	
			Element.find('#npvM2').removeClass('good').addClass('wrong');
		else
			Element.find('#npvM2').removeClass('wrong').addClass('good');
		
		$(Element).find('#npvM2Corr').html(npvM2Corr);
		$(Element).find('#npvFullCorr').html(npvFullCorr);
		
		
		// icone grigie
		var show_controls = true;
		
		fondoscala =  parseInt ( $(Element).find('.inputvalues input.TURNAROUND').val() );
		if(fondoscala>0) {
			$('#turn-' + id).addClass('turn-dis');	
			show_controls = false;
		}
		
		sostituito = parseInt ( $(Element).find('td input#ID_CHANGE').first().val() );
		if(sostituito > 0)	{
			$('#turn-' + id).addClass('change-dis');
			show_controls = false;
		}	
		
		id_flat = $(Element).find('#id_flat').val();
		if( $('#dir-' + id_flat).length && show_controls ){
			// mostro i controlli
		} else {
			// nascondo i controlli
			if ( ($(Element).find('.turncontrols img').length) ){
				// alert($(Element).find('.turncontrols img').attr('alt') ); 
				$(Element).find('.turncontrols img').hide();
			}
		}
		

		
		
		//$(Element).children('#pvFull').html(
	}
	
	function aggiornaContatoriCondivisi()	{
		////console.log('aggiornaContatoriCOndivisi');
		var npvM2, pvM2, ultima, penultima;
		$('table.shared tr').each(function (index, Element)	{
			id_misuratore = $(Element).attr('id');
			console.log('Condiviso: ' + id_misuratore);
		// 	alert(id_misuratore);
			
			// evidenzio la data errata
			if( $('#dt' + id_misuratore).val() == '00/00/0000' ){
				$('#dt' + id_misuratore).addClass('date_error');
			}
			
			////console.log('aggiornaContatoriCOndivisi: ' + id_misuratore);
			if (id_misuratore>0){
				area = 0;
				area_occupied = 0;
				flats = $(Element).find('form input.idflats').first().val();
				var flatsArr = flats.split(',');
				for(i=0;i<flatsArr.length;i++)	{
					flat_area = flatArea(flatsArr[i]);
					flat_occupied = flatIsOccupied(flatsArr[i]);
					area += flat_area;
					if (flat_occupied)
						area_occupied += flat_area;
				}
				////console.log('area: ' + area + '  area occupata: ' + area_occupied);
					//alert(flatsArr[i]);
				Element = $('table.shared tr#' + id_misuratore);
				metertype = $(Element).attr('type');
				id_misurazione = $(Element).find('input#id_measure').first().val();
				stato = $(Element).find('.puls_validate img').first().attr('src');
				////console.log('id_misurazione:'+id);
				
				if (id_misurazione>0)	{
					
					area_flat = area;
					////console.log('occupato:' + occupato);
					
					fondoscala = $(Element).find('.inputvalues input.TURNAROUND').val();
					// alert(fondoscala);
					
					ultima = 0;
					penultima = 0;
					Element.find('.inputvalues input.ultima_misurazione').each(function(index, Element){
																	ultima += parseFloat($(Element).val());
																});
					Element.find('.penultima_misurazione').each(function(index, Element){
																	penultima += parseFloat($(Element).html());
																});	
					
					if(parseInt(fondoscala)>0) {
						consumo = (fondoscala - penultima) + ultima;
						
						// aggiungo l'icona grigia
						$('#turn-' + id_misuratore).addClass('turn-dis');
						// console.log('Grigio fondoscala > 0');
					} else
						consumo = ultima - penultima;	
						
 					sostituito = $(Element).find('td input#ID_CHANGE').first().val();
					
					if(sostituito > 0)	{
						
						
						
						start = parseFloat($('td#main-' + id_misuratore + ' input[name="START_1"]').val());
						end = parseFloat($('td#main-' + id_misuratore + ' input[name="END_1"]').val());
						if(fasciaOraria > 1)	{
							consumoF1 = (end - penultimaF1) + (ultimaF1 - start);
							start += parseFloat($('td#main-' + id_misuratore + ' input[name="START_2"]').val());
							start += parseFloat($('td#main-' + id_misuratore + ' input[name="START_3"]').val());
							end += parseFloat($('td#main-' + id_misuratore + ' input[name="END_2"]').val());
							end += parseFloat($('td#main-' + id_misuratore + ' input[name="END_3"]').val());
							
						}
						// aggiungo l'icona grigia
						$('#turn-' + id_misuratore).addClass('change-dis');
						console.log(start);
						console.log(end);
						console.log(id_misuratore);
						consumo = (end - penultima) + (ultima - start);
					}		
					
					// consumo = ultima - penultima;												
					area = parseFloat(area_flat);
					////console.log('consumo:' + consumo);
					Element.find('#pvFull').html(consumo);
					consumo = parseFloat(consumo);
					pvM2 = Math.round(consumo/area_flat*1000) / 1000;
					Element.find('#PVM2').html(pvM2);
					//console.log("consumo:" + consumo);
					days = $(Element).find('input#days').val();
					//console.log('days:' +days);
					//console.log('periodDays:' +periodDays);
					npvM2 = Math.round(pvM2 * periodDays / days * 1000) / 1000;
					npv = Math.round(consumo * periodDays / days * 1000) / 1000;
					
					//Element.find('#npvM2').html(npvM2);
					Element.find('input.PV').val(consumo);
					Element.find('input.PVM2').val(pvM2);
					Element.find('input.NPV').val(npv);
					Element.find('input.NPVM2').val(npvM2);
					if (stato == "images/Bad.png" || stato == "images/Privacy.png")		{
						$(Element).find('.ultima_misurazione').addClass('wrong');
						$(Element).find('.ultima_misurazione').removeClass('good');
						cnpvM2 = avgNPVM2(metertype);
						//console.log('cnpvM2:' +cnpvM2);
					}
						
					else	{
						$(Element).find('.ultima_misurazione').addClass('good');
						$(Element).find('.ultima_misurazione').removeClass('wrong');
						cnpvM2 = npvM2;
					}
						
					
					cnpvM2 = Math.round(cnpvM2 / area_occupied * area * 1000) / 1000;
					cnpv =  Math.round(cnpvM2 * area_occupied * 1000) / 1000;
					
					if(cnpvM2<0){
						Element.find('#npvM2').removeClass('good').addClass('wrong');
						
						// il valore è negativo quando la data è errata o quando il contatore è in giro / sostituzione
						if( $('#dt' + id_misuratore).val() != '00/00/0000' ){
							Element.find('#npvM2').addClass('turn');
						}

					} else {
						Element.find('#npvM2').removeClass('wrong').addClass('good');
					}
					
					if (stato == "images/Bad.png" || cnpvM2 < 0)	{
						// console.log(Element);
						$(Element).find('.restart-meter').css('display', 'block');
						$(Element).find('.change-meter').css('display', 'block');
					} else	{
						$(Element).find('.restart-meter').css('display', 'none');
						$(Element).find('.change-meter').css('display', 'none');
						//alert('no');
					}
					
					Element.find('#npvM2').html(cnpvM2);
					Element.find('input.CNPVM2').val(cnpvM2);
					Element.find('input.CNPV').val(cnpv);
					//console.log('npvM2:' +npvM2);
					//console.log('cnpvM2:' +cnpvM2);
					/*
					Element.find('.CNPV').html((cnpvM2 * area_flat) * occupato);
					Element.find('.CNPVM2').html(cnpvM2 * occupato);
					Element.find('#npvM2').html(cnpvM2 * occupato);
					*/
					//$(Element).children('#pvFull').html(
				}
				
				if( $('#sha-' + id_misuratore).length){
					// mostro i controlli
				} else {
					// nascondo i controlli
					if ( ($(Element).find('.turncontrols img').length) ){
						// alert($(Element).find('.turncontrols img').attr('alt') ); 
						$(Element).find('.turncontrols img').hide();
					}
				}	
				
			}
		});
	}
	
	function aggiornaContatoriFormula()	{
		////console.log('aggiornaContatoriFormula');
		var npvM2, pvM2, ultima, penultima;
		$('table.formula tr').each(function (index, Element)	{
			id_misuratore = $(Element).attr('id');
			////console.log('aggiornaContatoriFormula: ' + id_misuratore);
			if (id_misuratore>0)	{
				area = 0;
				area_occupied = 0;
				flats = $(Element).find('form input.idflats').first().val();
				var flatsArr = flats.split(',');
				for(i=0;i<flatsArr.length;i++)	{
					flat_area = flatArea(flatsArr[i]);
					flat_occupied = flatIsOccupied(flatsArr[i]);
					area += flat_area;
					if (flat_occupied)
						area_occupied += flat_area;
				}
				////console.log('area: ' + area + '  area occupata: ' + area_occupied);
					//alert(flatsArr[i]);
				Element = $('table.formula tr#' + id_misuratore);
				metertype = $(Element).attr('type');
				id_misurazione = $(Element).find('input#id_measure').first().val();
				stato = $(Element).find('.puls_validate img').first().attr('src');
				////console.log('id_misurazione:'+id);
				
				npv = Element.find('.NPV').val();
				npvM2 = Math.round(npv * 1000 / area_occupied) / 1000;
				Element.find('.NPVM2').val(npvM2);
			}
		});
	}
	
	function avgNPVM2(metertype)	{
		//console.log(metertype);
		var i=0;
		var NPV=0;
		var areatot = 0;
		var NPVM2pesato = 0;
		NPVM2 = 0;
		//console.log("type:" + metertype);
		$('.misuratore tr[type="'+metertype+'"]')
			.each(function(index, Element){
				//console.log(Element);
				occupato = $(Element).parents('table.appartamento').first().find('input[name="cknumid"]').first().is(':checked');
				stato = $(Element).find('.puls_validate img').first().attr('src');
				penultimaMis =  $(Element).find('.penultima_misurazione');
				penultimaWrong = $(penultimaMis).hasClass('wrong');
				
				// Se la misura è corretta e l'appartamento è occupato, la considero per la media
				if ((stato == "images/Good.png" || stato == "images/Modify.png") && !(penultimaWrong))	{
					if(occupato)	{
						//console.log($(Element).html());
						area = parseFloat( $(Element).parents('table.appartamento').first().find('input[name="area_flat"]').first().val());
						console.log(area);
						areatot += area;
						npvm2 = parseFloat($(Element).find('#npvM2').first().html());
						npvm2pesato = npvm2 * area;
						NPVM2 += npvm2;
						NPVM2pesato += npvm2pesato;
						//console.log(npvm2);
						//console.log('NPVM"' + NPVM2);
						
						i++;
					}
						
					else
						npvM2Corr = 0;  // per il momento questo valore non lo uso
				} else if (stato == "images/Good-grey.png")	{
					npvM2Corr = "";
				}
			});
			
			//console.log(NPVM2 / i);
			//return Math.round(NPVM2 / i * 1000) / 1000;
		console.log(NPVM2pesato);
		console.log(areatot);
			return Math.round(NPVM2pesato / areatot * 1000) / 1000;
	
	}
	
	function inviaDatiAppartamento(id)	{
		//console.log(id);
		validato = true;
		$('#tabs-1 table#' + id +'').find('table.misuratore tr').
			each(function(index, Element){ 
				
				id_meter = $(Element).attr('id');
				stato = $(Element).find('.puls_validate img').first().attr('src');
				id_measure = $(Element).find('input#id_measure').first().val();
				
				var misurazioniArr = new Array();
				$(Element).find('.inputvalues input.ultima_misurazione').each(function(index, Element){
															misurazioniArr.push(parseFloat($(Element).val()));
														});
				dataString = jQuery.toJSON(misurazioniArr);
				if (id_measure>0)	{
				
					if(stato != "images/Good-grey.png") {
						idu = $('#idu').val();
						jQuery.post("ajax/json.php", {	action: "put_misure_convalidate", 
													idu: idu,
													json: dataString, 
													PVFULL: $(Element).find('#pvFull').html(), 
													PVM2: $(Element).find('#pvM2').html(), 
													NPVM2: $(Element).find('#npvM2').html(), 
													NPVFULLCORR: $(Element).find('#npvFullCorr').html(), 
													NPVM2CORR: $(Element).find('#npvM2Corr').html(),
													ID_FLAT: id,
													ID_METER: id_meter,
													ID_MEASURE: id_measure
													}, 
										function(data) {
											
								});
					}	else	{
						//(alert("You have to validate data!");
						validato = false;
					}
				}
				
			});
			
			if(validato)	{
				$('#tabs-1 table#' + id +' .img_puls').attr('src', 'images/convalida.png');
			} else
				alert("You have to validate data!");
	}
	
	function salvaStatoOccupato(idAppartamento)	{
		stato = $('#tabs-1 table#' + idAppartamento +'').find('input[name="cknumid"]').is(':checked');
		
		jQuery.post("ajax/json.php", {	action: "set_stato_occupato", 
													id: idAppartamento,
													occupato: stato,
													year: $('#wizard #year').val(),
													id_uploadtype: $('#wizard #upload_type').val(),
													}, 
										function(data) {
											
								});
	}

		function inviaDatiMisuratore(id)	{
		////console.log(id);
		validato = true;
		$('table.shared tr#' + id +'').
			each(function(index, Element){ 
				
				id_meter = $(Element).attr('id');
				stato = $(Element).find('.puls_validate img').first().attr('src');
				id_measure = $(Element).find('input#id_measure').first().val();
				
				var misurazioniArr = new Array();
				$(Element).find('.inputvalues input.ultima_misurazione').each(function(index, Element){
															misurazioniArr.push(parseFloat($(Element).val()));
														});
				dataString = jQuery.toJSON(misurazioniArr);
				if (id_measure>0)	{
				
					if(stato != "images/Good-grey.png") {
						idu = $('#idu').val();
						jQuery.post("ajax/json.php", {	action: "put_misure_convalidate", 
													idu: idu,
													json: dataString, 
													PVFULL: $(Element).find('#pvFull').html(), 
													PVM2: $(Element).find('#pvM2').html(), 
													NPVM2: $(Element).find('#npvM2').html(), 
													NPVFULLCORR: $(Element).find('#npvFullCorr').html(), 
													NPVM2CORR: $(Element).find('#npvM2Corr').html(),
													ID_FLAT: id,
													ID_METER: id_meter,
													ID_MEASURE: id_measure
													}, 
										function(data) {
											
								});
					}	else	{
						//(alert("You have to validate data!");
						validato = false;
					}
				}
				
			});
			
			if(validato){
				$('#tabs-1 table#' + id +' .img_puls').attr('src', 'images/convalida.png');
			} else
				alert("You have to validate data!");
	}
	
	// crea i pulsanti giro fondo scala contatore e sostituzione contatore
	function pulsanti_contatore(){
 		$('#npvM2.turn').each(function(index, Element){
			tr = $( Element ).parents('tr').first();
			id_meter = $(tr).first().attr('id');

			// controllo se è triorario
			last_measure = $('table.misuratore tr#' + id_meter).first();

			//$( '#turn-' + id_meter ).html('<img class="restart-meter" src="images/arrow-restart.png" alt="'+ id_meter +'" title="Full scale turn around"> <img class="change-meter" src="images/arrow-change.png" alt="'+ id_meter +'" title="Change meter"> ');
		});
	}	