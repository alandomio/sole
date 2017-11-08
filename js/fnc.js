// JavaScript Document
/*function check_all(frm, sel){
	var val=document.forms[frm].elements[sel].value;
	for (var i = 0; i < document.forms[frm].elements.length; i++) {
		var e = document.forms[frm].elements[i];
		if (e.type == 'checkbox') {
			if(val==2){
			e.checked = true; 
			}
			else if (val==1) { 
			e.checked = false; 
			}
		}
	}
}*/
function check_all(frm, sel){
	var val=document.forms[frm].elements[sel].checked;
	for (var i = 0; i < document.forms[frm].elements.length; i++) {
		var e = document.forms[frm].elements[i];
		if (e.type == 'checkbox') {
			if(val==true){
			e.checked = true; 
			}
			else{ 
			e.checked = false; 
			}
		}
	}
}

function check_all_by_name(frm, sel){
	var val=document.forms[frm].elements[sel].checked;
	for (var i = 0; i < document.forms[frm].elements.length; i++) {
		var e = document.forms[frm].elements[i];

		if (e.type == 'checkbox') { 
			if(val==true){ 
				if(e.name.slice(0,sel.length)==sel){
					e.checked = true;
				}
			}
			else{
				if(e.name.slice(0,sel.length)==sel){
					e.checked = false;
				}
			}
		}
	}
}