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
	
	$('#upload_type').change(function(){
	 showMEASURES();
	});
	
	$('#choose_date').change(function(){
		$('.new_dmeasure').val($(this).val());
		showMEASURES();
	});
	
	$('#print-measures').click(function(){
		// apro una nuova pagina con i dati delle misurazioni
		var id = $('#buildings').val();
		var year = $('#year').val();
		var upload_type = $('#upload_type').val();
		
		if( id == '' ){
			message('y', 'Seleziona un edificio');
		} else {
			window.open( 'print_measures.php?id=' + id + '&year=' + year + '&upload_type=' + upload_type );
			return false;
		}
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
	var upload_type = $('#upload_type').val();
	var d_measure = $('#choose_date').val();

	if(id_building != ''){
	 jQuery.getJSON('ajax/json.php?action=form_insert_measures&id=' + id_building + '&upload_type=' + upload_type + '&year=' + year + '&d_measure=' + d_measure,
		function(data){
			$('#col_right').html(data.form);
			
			$('.edit_value').keypress(function() {
				$(this).css('color' , 'blue');
			});
			
 			$('.edit_value').change(function() {
					var fieldname = $(this).attr('id');
					var id_measure = fieldname.substring(4,fieldname.length);
					var valore = $(this).val()
					valore = String(valore).replace(/\,/g,'.');
					$(this).val(valore);					
					var field = $(this).attr('name');
					update_measure(id_measure, valore, field, fieldname, this);
			}); 
			
			$('.save_new').click(function(){
				var id = $(this).attr('id');
				var id_meter = id.substring(4, id.length);
				
				add_measure(id_meter);
			});
			
			del_measure();
			
			$('.new_dmeasure').val($('#choose_date').val());
		});
	}
}

function update_measure(id_measure, valore, field, fieldname, campo){
	jQuery.getJSON('ajax/json.php?action=update_single_measure&id=' + id_measure + '&valore=' + valore + '&field=' + field,
	function(data){
		if(data.success){
			$(campo).css('color','green');
		}
	});
}

function add_measure(id_meter){
	var ID_METER = id_meter;
	var D_MEASURE = $('#choose_date').val();
	var ID_UPLOADTYPE = $('#upload_type').val();
	var ANNO_MS = $('#year').val();
	var F1 = $('#F1' + id_meter).val();
	var F2 = $('#F2' + id_meter).val();
	var F3 = $('#F3' + id_meter).val();
	
	jQuery.getJSON('ajax/json.php?action=insert_measure&ID_METER=' + ID_METER + '&D_MEASURE=' + D_MEASURE + '&ID_UPLOADTYPE=' + ID_UPLOADTYPE + '&ANNO_MS=' + ANNO_MS + '&F1=' + F1 + '&F2=' + F2 + '&F3=' + F3,
	function(data){			
		$('#message' + id_meter).html(data.message);
		$('#message' + id_meter).delay(100).slideDown(700);
		$('#message' + id_meter).delay(5000).slideUp(700);		

		if(data.success){
			$('#save'+id_meter).remove();
			$('#tr'+id_meter+' input').each(function(e, el) {
				if($(el).attr('type') == 'text'){
					$("#"+this.value).removeAttr('disabled');
					$(el).removeAttr('disabled');
					$(el).removeClass('new_year');
					$(el).removeClass('new_uploadtype');
					$(el).removeClass('new_dmeasure');
					$(el).addClass('edit_value');
					$(el).attr('id', 'edit'+data.id);				
				
					update_ms($(el));
				}
			});
		}
	});
}

function del_measure(){
	$('.del-measure').click(function(){
		if(confirm('Eliminare questa misurazione?')){
			id_measure = $(this).attr('id').substring(12);
 			jQuery.getJSON('ajax/json.php?action=delete_single_measure&id=' + id_measure,
			function(data){
				if(data.success){
					message('g', data.message);
					$('#tr-'+id_measure).remove();
					
				} else {
					message('r', data.message);
				}
			});			
		}
	});
}