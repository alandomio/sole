$(document).ready(function(){
	$('.change_lang').click(function(event){
		event.stopPropagation();
		var gotopage = "change_lang.php?lang=" + $(this).attr('title') + "&return=" + $('#goto').val();
		window.location = gotopage;
	});  
});

$('#topUserLink').click(function() {
	$('#topUserLink').toggleClass('sel-white');
	$('#u-box').fadeToggle('fast', function(){
	});
});