$(document).ready(function(){
	$('.submit').click(function(){
		var form = 'frm_' + $( this ).attr('id');
		if(confirm('Eliminare?')){
			$( '#' + form ).submit(); 
		}
	});
});
	
	