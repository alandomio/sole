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
		 $('#id_building').val($('#buildings').val());
	});
	
	 $('#federations').val( $('#id_federation').val() ).attr('selected', true);
	 $('#hcompanys').val( $('#id_hcompany').val() ).attr('selected', true);
	 $('#buildings').val( $('#id_building').val() ).attr('selected', true);
	 
});

function selectHC(){
	federation = $('#federations').val();
	jQuery.getJSON("ajax/json.php?action=select_hc&fed=" + federation,
	  function(data){
		options = '<option selected="selected" value="">- Housing company</option>	' + "\n\r";
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
		options = '<option selected="selected" value="">- Building</option>	' + "\n\r";
		 jQuery.each(data, function(i,item){
			options += '<option  value="' + item.optionValue + '">' + item.optionDisplay + '</option>' + "\n\r";
		 });
	  $('#buildings').html(options);
	  });
}