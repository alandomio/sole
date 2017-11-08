$(document).ready(function(){
	$('#federations').change(function(){
		$('#hcompanys').html('');
		$('#buildings').html('');
		selectHC();
		selectBLD();
	});
	 $('#hcompanys').change(function(){
			selectBLD();
	});
 	$('#buildings').change(function(){
		showMEASURES();
	}); 
	
	 $('#year').change(function(){
		showMEASURES();
	 });
	 
	 $('#excel').change(function(){
		 
		 filename = $( this ).val();
		 extension = filename.substr(-4);
		 // console.log(extension);
		 if(extension != 'xlsx' && extension != '.xls'){
			 message('r', 'Upload xlsx xls file!');
		 } else {
			 loader('box-col-left', 'show');
			 $('#frm-excel').ajaxSubmit(function(data){
				// operazioni da fare dopo l'upload
				$('#buildings option[value="'+data.ID_BUILDING+'"]').attr("selected", "selected");
				$('#year option[value="'+data.ANNO+'"]').attr("selected", "selected");
				showMEASURES();
				loader('box-col-left', 'hide');
			 });
		 }
	 });
	 
	 
	 $('#delete-ms').click(function(){
			var year = $('#year').val();
			var upload_type = $('#upload_type').val();
			var buildings = $('#buildings').val();
			
			if(year && buildings){
				$('#del-measures').removeClass('hidden');
		
			} else {
				alert('Compila edificio, anno, numero invio');
			}
		});
	 
	 $('#upload_type').change(function(){
		 var year = $('#year').val();
			var upload_type = parseInt($('#upload_type').val());
			var buildings = $('#buildings').val();
	
			
			if(year && upload_type && buildings){
				$('#del-measures').removeClass('hidden');
				var answer = confirm('Confermi la cancellazione delle convalide?');
				if (answer){
					// elimino le misurazioni correlate
					jQuery.getJSON("ajax/json.php?action=delete_all_ms&type=mensile&building=" + buildings + '&year='+year + '&upload_type=' + upload_type,
					  function(data){
							if(data.success){
								alert(data.cnt + ' record eliminati');
								showMEASURES();
							} else {
								alert('Non ci sono record da eliminare');
							}
							$('#del-measures').addClass('hidden');
					  });			
				}	else
					$('#del-measures').addClass('hidden');
			} else {
				alert('Compila edificio, anno, numero invio');
			}
			$('#upload_type option[value=""]').attr('selected','selected');
	 });
	
	$('#buttons a').click(function(){
		if( ! $('#buildings').val() || ! $('#year').val() ){
			message('y', 'Per proseguire scegli edificio e anno');
			return false;
		}
		
		fn = $(this).attr('rel');
		if( fn == 'choose_date')
			$('#dialog-date').dialog('open');
		
		else if ( fn == 'download_model')
			$('#dialog-download').dialog('open');
		
		else if ( fn == 'print_page')
			print_page();
		
		else if ( fn == 'delete_rows')
			delete_rows();
	});
	
	// gestisce il posizionamento in alto di alcuni blocchi che devono essere sempre visualizzati
	$(function() {	
		var msie6 = $.browser == 'msie' && $.browser.version < 7; 
		$('.fixed_position').each(function(){
			if (!msie6) {
				var top = $( this ).offset().top - parseFloat($( this ).css('margin-top').replace(/auto/, 0));
				$(window).scroll(function (event) {
					var y = $(this).scrollTop();  
					if (y >= top) {
						$('.fixed_position').addClass('fixed');
					} else {
						$('.fixed_position').removeClass('fixed');
					}
				});
			}
		});
	});
	
});


/* funzioni pulsantiera */
function print_page(){
	var id = $('#buildings').val();
	var year = $('#year').val();
	
	if( id == '' ){
		message('y', 'Seleziona un edificio');
	} else {
		window.open( 'print_measures12.php?id=' + id + '&year=' + year );
		return false;
	}
}

function delete_rows(){
	$('.dele_measures').each(function(){
		
		if( $( this ).is(':checked') ){
			
			// restituisce un array con year, id_uploadtype, id_meter
			values = get_measure_info( this );
			
			var element = $( this );
			$.getJSON("ajax/json.php?action=del_measure12&year=" + values[0] + '&id_uploadtype=' + values[1] + '&id_meter=' + values[2],
			function( data ){
				if(data.success){
					// azzero i valori senza ricaricare la pagina
					$( element ).closest('tr').find('.edit_value').val('');
					$( element ).attr('checked', false);
				}
			});
		}
	});
}

/* predisposizione dialog */
$("#dialog-date").dialog({
	autoOpen: false,
	height: 260,
	width: 340,
	modal: true,
	title: 'Set date',
	position: [ 'center', 160 ],
	buttons: {
		'Set date' : function(){
			set_dates();
		},
		'Cancel' : function(){
			$(this).dialog('close');
		},
	},
	open: function() {
		$(this).removeClass('hide');
	},
	close: function(){
		$('#date_date').val('');
	}
});

$("#dialog-download").dialog({
	autoOpen: false,
	height: 200,
	width: 340,
	modal: true,
	title: 'Download',
	position: [ 'center', 160 ],
	buttons: {
		'Download' : function(){
			window.open( 'ajax/excel.php?action=modello&tipo=mensile&anno='+$('#year').val()+'&mese='+ $('#month_download').val() +'&id_building=' + $('#buildings').val() );
			$("#dialog-download").dialog('close');
			
		},
		'Cancel' : function(){
			$(this).dialog('close');
		},
	},
	open: function() {
		// $('#date_date').datepicker('enable');
		$(this).removeClass('hide');
	},
	close: function(){
		// $('#date_date').datepicker('disable');
	}
});

function set_dates(){
	
	year = $('#year').val();
	date = $('#date_date').val();
	month = $('#date_month').val();

	var all_changed = true;
	
	$('.' + year + month ).each( function(){
		
		var input_date = $( this ).find('input[name="D_MEASURE"]');
		
		if( $(input_date).val() != '' ){
			all_changed = false;
			return;
		} else {
			$( input_date ).val(date);
			// ora devo salvare il valore in db
			save_data_value( input_date );
		}
	});
	
	$('#dialog-date').dialog('close');
	
	if( ! all_changed ){
		alert('Alcune date erano già inserite e non sono state modificate');
	}
}
	
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

function update_ms(el){
	$(el).keypress(function() {
		$(el).css('color' , 'blue');
	});
	
	$(el).change(function() {
		var fieldname = $(this).attr('id');
		//alert(fieldname);
		var id_measure = fieldname.substring(4,fieldname.length);
		var valore = $(this).val()
		valore = String(valore).replace(/\,/g,'.');
		$(this).val(valore);					
		var field = $(this).attr('name');
		update_measure(id_measure, valore, field, fieldname, this);
	});
}

function showMEASURES(){
	var id_building = $('#buildings').val();
	var year = $('#year').val();
	//var upload_type = $('#upload_type').val();
	//var d_measure = $('#choose_date').val();

	if(id_building && year){
		
		loader('table-content', 'show');
		
		$('#table-content').load('ajax/json.php?action=measures_12_form&id=' + id_building + '&year=' + year, function(){
			$('.edit_value').keypress(function() {
				$(this).css('color' , 'blue');
			});
			
			$('.datepicker').datepicker();
			
 			$('.edit_value').change(function() {
 				save_data_value( this );
			}); 
			
 			loader('table-content', 'hide');
 			
		});
	}
}

function get_measure_info( element ){
	var values = new Array();
	// year
	values[0] = $( element ).closest('tr').attr('class').substring(0, 4);
	
	// month
	values[1] = $( element ).closest('tr').attr('class').substring(4);
	
	// id_meter
	values[2] = $( element ).closest('table').attr('id');
	
	return values;
}

function save_data_value( element ){
		year = $( element ).closest('tr').attr('class').substring(0, 4);
		id_uploadtype = $( element ).closest('tr').attr('class').substring(4);
		id_meter = $( element ).closest('table').attr('id');
		fieldname = $(element).attr('name');
		
		// sostituisco le virgole coi punti
		value = $(element).val()
		value = String(value).replace(/\,/g,'.');
		
		// console.log('year: ' + year + ' id_uploadtype: ' + id_uploadtype + ' id_meter: ' + id_meter);
		save_measure(year, id_uploadtype, id_meter, fieldname, value, element);
}


function save_measure(year, id_uploadtype, id_meter, fieldname, value, campo){
	jQuery.getJSON('ajax/json.php?action=save_measure12&year=' + year + '&id_uploadtype=' + id_uploadtype + '&id_meter=' + id_meter+ '&fieldname=' + fieldname+ '&value=' + value,
	function(data){
		if(data.success){
			$(campo).css('color','green');
		}
		
		if( data.error || ! data.success){
			message('y', data.error);
			$(campo).val( data.reset_value );
			$(campo).css('color','red');
		}

	});
}