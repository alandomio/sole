// JavaScript Document
function hid_mostra(){
	if(document.getElementById('hid_mostra').className=='puls_nuovo_avviso'){
		document.frm_nuovo.style.display='block'
		document.getElementById('hid_mostra').className='puls_annulla'
	}
	else{
		document.frm_nuovo.style.display='none'
		document.getElementById('hid_mostra').className='puls_nuovo_avviso'
	}
	
}
function check_all(frm, sel) {
    var val = document.forms[frm].elements[sel].value;
    for (var i = 0; i < document.forms[frm].elements.length; i++) {
        var e = document.forms[frm].elements[i];
        if (e.type == "checkbox") {
            if (val == 2) {
                e.checked = true;
            } else if (val == 1) {
                e.checked = false;
            }
        }
    }
}function checkAllById(frm, sel) {
    var val = document.getElementById('sel').value;
    for (var i = 0; i < document.forms[frm].elements.length; i++) {
        var e = document.forms[frm].elements[i];
        if (e.type == "checkbox") {
            if (val == 2) {
                e.checked = true;
            } else if (val == 1) {
                e.checked = false;
            }
        }
    }
}