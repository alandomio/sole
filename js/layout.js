 $(document).ready(function(){
 
$('#alert').delay(5000).slideUp(700);

$(function() {
	$( '.datepicker' ).datepicker({
		dateFormat: "dd/mm/yy",
	});
});

$(function() {
	$( '#lista_img' ).sortable();
	$( '#lista_img' ).disableSelection();
});

$(function() {
	$('#sortable tbody.sorttr').sortable();
	$('#sortable tbody.sorttr').disableSelection();
});		


$(function() {
	function enable() {
		$('.Tips1').tooltip();
	}
	enable();
});


});
 
function message(tipo, txt){
	$('#main-messages').stop(); // ferma la precedente animazione
 	$('#main-messages ul').html('');
	$('#main-messages ul').append('<li>' + txt + '</li>');
 	$('#main-messages').removeClass('g');
	$('#main-messages').removeClass('y');
	$('#main-messages').removeClass('r');
	$('#main-messages').addClass(tipo);
	$('#main-messages').fadeIn('slow').delay(7000).fadeOut('slow');
}

/*
 * oscura il box relativo alla richiesta ajax
 * facendo apparire un ajaxloader al centro
 * parametri:
 * @myDiv: l'attributo html "id" del div che dovrà essere oscurato (senza il cancelletto #)
 * @fnc: "show": mostra l'ajaxloader, "hide": lo nasconde
 */

function loader(myDiv, fnc) {
	// div dell'effetto loader
	var loaderDiv = "<div id='ajax-loader' style='position: relative;' class='hide'></div>";
	// dimensioni del box da sostituire
	var gifHeight 		= 55;
	var gifWidth 		= 54;
	var imageUrl		= "/images/pattern_samples/white.png";
	// dimensioni immagine gif con loader
	var divHeight 		= jQuery('#'+myDiv).height();
	var divWidth 		= jQuery('#'+myDiv).width();
	// posizionamento centrato corretto della gif
	var height			= Math.round((divHeight / 2) - (gifHeight /2));
	var width			= Math.round((divWidth / 2) - (gifWidth /2));
	// contenuto aggiuntivo
	var htmlLoader = "<div style='position: absolute; width: "+divWidth+"px; height: "+divHeight+"px; background: transparent url("+imageUrl+") top left repeat'><img src='/images/ajax-loader.gif' style='position: absolute; top: "+height+"px; left: "+width+"px;' width='54' height='55' /></div>";
	switch(fnc){
		case 'hide' :
			jQuery('#ajax-loader').hide('slow');
			jQuery('#ajax-loader').remove();
			break;
		case 'show' : default :
			jQuery('#'+myDiv).prepend(loaderDiv);
			jQuery('#ajax-loader').html(htmlLoader);
			jQuery('#ajax-loader').show('slow');
			break;
	}
}