<?php
# V.0.1.8
class tiny_mce{
public function __construct(){
	$this->selector = '';
	$this->unselector = '';
}

public function set_selector($s){
	$this->selector = 'editor_selector : "'.$s.'",';
}

public function  unset_selector($s){
	$this->unselector = 'editor_deselector : "'.$s.'",';
}

public function set(){
print '<script language="javascript" type="text/javascript" src="'.JS_TINYMCE.'jscripts/tiny_mce/tiny_mce.js"></script>';
print '<script language="javascript" type="text/javascript">
tinyMCE.init({
	mode : "textareas",
	theme : "advanced",
	language : "it",
	plugins : "preview",
	'.$this->selector.'
	'.$this->unselector.'	

	// Theme options
	theme_advanced_buttons1 : "preview,code,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,link,unlink,|,undo,redo,|,forecolor,backcolor",
	theme_advanced_buttons2 : "",
	theme_advanced_buttons3 : "",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom"
});
</script>';
}

public function set2(){
print '<script language="javascript" type="text/javascript" src="'.JS_TINYMCE.'jscripts/tiny_mce/tiny_mce.js"></script>'."\n";
print '<script language="javascript" type="text/javascript">
tinyMCE.init({
	mode : "textareas",
	theme : "advanced",
	language : "it",
	plugins : "preview",
	'.$this->selector.'
	'.$this->unselector.'	

	// Theme options
	theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,bullist,numlist,|,forecolor,backcolor",
	theme_advanced_buttons2 : "",
	theme_advanced_buttons3 : "",
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "none"
});
</script>';
}

public function set3(){
print '<script language="javascript" type="text/javascript" src="'.JS_TINYMCE.'jscripts/tiny_mce/tiny_mce.js"></script>'."\n";
print '<script language="javascript" type="text/javascript">
tinyMCE.init({
	mode : "textareas",
	theme : "advanced",
	language : "it",
	plugins : "preview",
	'.$this->selector.'
	'.$this->unselector.'	

	// Theme options
	theme_advanced_buttons1 : "none",
	theme_advanced_buttons2 : "none",
	theme_advanced_buttons3 : "none",
	theme_advanced_toolbar_location : "none",
	theme_advanced_statusbar_location : "none",
	theme_advanced_toolbar_align : "left"
});
</script>';
}


}
?>