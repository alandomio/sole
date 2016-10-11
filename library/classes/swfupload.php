<?php
# V.0.1.8
class swfupload{

public function __construct(){
	$this -> flash_url = ''; //"../swfupload/swfupload.swf";
	$this -> upload_url="../../upload.php";
	$this -> img_pulsante="../upload.png";
	$this -> aPostp = array();
	$this -> postp = '';
	$this -> file_size_limit = "2";
	$this -> files_types = "*.*"; # *.jpg; *.pdf; *.doc; *.xls
	$this -> file_types_description = 'scegli file';
	$this -> file_upload_limit = 100;
	$this -> file_queue_limit = 0;
	
	$this -> handler = 'handlers.js';
	
	$this -> css = '';
	$this -> jsf = '';
	$this -> jsc = '';
	
	$this -> debug = 'false';
}

function set_post_params(){
	$cnt = 0;
	foreach($this -> aPostp as $k => $v){
		$vir = $cnt == 0 ? "" : ",\n";
		$this -> postp .= $vir.'"'.$k.'" : "'.$v.'"';
		$cnt++;
	}
}

function set_gradient(){
	$this -> set_post_params();
	if($this -> debug == 'true'){
		$this -> handler = 'handlers_test.js';
	}
	list($w, $h) = getimagesize($this -> img_pulsante);
	
$this -> css = JS_SWF.'style.css';
$this -> jsf = '<script type="text/javascript" src="'.JS_SWF.'swfupload.js"></script>
<script type="text/javascript" src="'.JS_SWF.'swfupload.queue.js"></script>
<script type="text/javascript" src="'.JS_SWF.'fileprogress.js"></script>
<script type="text/javascript" src="'.JS_SWF.$this -> handler.'"></script>';

$this -> jsc = '<script type="text/javascript">
var swfu;
SWFUpload.onload = function () {
	var settings = {
		flash_url : "'.JS_SWF.'swfupload.swf",
		flash9_url : "'.JS_SWF.'swfupload_fp9.swf",
		upload_url: "'.$this->upload_url.'",
		post_params: {
			'.$this -> postp.'
		},
		file_size_limit : "'.$this->file_size_limit.' MB",
		file_types : "'.$this->files_types.'",
		file_types_description : "All Files",
		file_upload_limit : '.$this->file_upload_limit.',
		file_queue_limit : '.$this->file_queue_limit.',
		custom_settings : {
			progressTarget : "fsUploadProgress",
			cancelButtonId : "btnCancel"
		},
		debug: '.$this -> debug.',

		// Button Settings
		button_image_url : "'.$this -> img_pulsante.'",
		button_placeholder_id : "spanButtonPlaceholder",
		button_width: '.$w.',
		button_height: '.($h/4).',

		// The event handler functions are defined in handlers.js
		swfupload_preload_handler : swfUploadPreLoad,
		swfupload_load_failed_handler : swfUploadLoadFailed,
		swfupload_loaded_handler : swfUploadLoaded,
		file_queued_handler : fileQueued,
		file_queue_error_handler : fileQueueError,
		file_dialog_complete_handler : fileDialogComplete,
		upload_start_handler : uploadStart,
		upload_progress_handler : uploadProgress,
		upload_error_handler : uploadError,
		upload_success_handler : uploadSuccess,
		upload_complete_handler : uploadComplete,
		queue_complete_handler : queueComplete	// Queue plugin event
	};
	swfu = new SWFUpload(settings);
}
</script>';

}

function set2(){ ?>
<link href="<?=JS_SWF?>css/style_mercatino.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?=JS_SWF?>swfupload/swfupload.js"></script>
<script type="text/javascript" src="<?=JS_SWF?>js/swfupload.swfobject.js"></script>
<script type="text/javascript" src="<?=JS_SWF?>js/swfupload.queue.js"></script>
<script type="text/javascript" src="<?=JS_SWF?>js/fileprogress.js"></script>
<script type="text/javascript" src="<?=JS_SWF?>js/<?=$this -> handler?>"></script>
<script type="text/javascript">
var swfu;

SWFUpload.onload = function () {
	var settings = {
		flash_url : "<?=$this->flash_url;?>",
		upload_url: "<?=$this->upload_url;?>",	// Relative to the SWF file
		post_params: {
		<?php
		$cnt=0;
		foreach($this->post_params as $k => $v){
			$vir= $cnt==0 ? "" : ",\n";
			print $vir.'"'.$k.'" : "'.$v.'"';
			$cnt++;
		}?>
		},
		file_size_limit : "<?=$this->file_size_limit?> MB",
		file_types : "<?=$this->files_types?>",
		file_types_description : "<?=$this->file_types_description?>",
		file_upload_limit : <?=$this->file_upload_limit?>,
		file_queue_limit : <?=$this->file_queue_limit?>,
		custom_settings : {
			progressTarget : "fsUploadProgress",
			cancelButtonId : "btnCancel"
		},
		debug: false,

		// Button Settings
		button_image_url : "<?=$this->img_pulsante;?>",	// Relativo alla pagina d'utilizzo
		button_placeholder_id : "spanButtonPlaceholder",
		button_width: 274,
		button_height: 22,

		// The event handler functions are defined in handlers.js
		swfupload_loaded_handler : swfUploadLoaded,
		file_queued_handler : fileQueued,
		file_queue_error_handler : fileQueueError,
		file_dialog_complete_handler : fileDialogComplete,
		upload_start_handler : uploadStart,
		upload_progress_handler : uploadProgress,
		upload_error_handler : uploadError,
		upload_success_handler : uploadSuccess,
		upload_complete_handler : uploadComplete,
		queue_complete_handler : queueComplete,	// Queue plugin event
		
		// SWFObject settings
		minimum_flash_version : "9.0.28",
		swfupload_pre_load_handler : swfUploadPreLoad,
		swfupload_load_failed_handler : swfUploadLoadFailed
	};

	swfu = new SWFUpload(settings);
}
</script>
<?php
}

function set(){}



/*function set(){ ?>
<link href="<?=JS_SWF?>css/default.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?=JS_SWF?>swfupload/swfupload.js"></script>
<script type="text/javascript" src="<?=JS_SWF?>js/swfupload.swfobject.js"></script>
<script type="text/javascript" src="<?=JS_SWF?>js/swfupload.queue.js"></script>
<script type="text/javascript" src="<?=JS_SWF?>js/fileprogress.js"></script>
<script type="text/javascript" src="<?=JS_SWF?>js/handlers.js"></script>
<script type="text/javascript">
var swfu;

SWFUpload.onload = function () {
	var settings = {
		flash_url : "<?=$this->flash_url;?>",
		upload_url: "<?=$this->upload_url;?>",	// Relative to the SWF file
		post_params: {
		<?php
		$cnt=0;
		foreach($this->post_params as $k => $v){
			$vir= $cnt==0 ? "" : ",\n";
			print $vir.'"'.$k.'" : "'.$v.'"';
			$cnt++;
		}?>
		},
		file_size_limit : "<?=$this->file_size_limit?> MB",
		file_types : "<?=$this->files_types?>",
		file_types_description : "<?=$this->file_types_description?>",
		file_upload_limit : <?=$this->file_upload_limit?>,
		file_queue_limit : <?=$this->file_queue_limit?>,
		custom_settings : {
			progressTarget : "fsUploadProgress",
			cancelButtonId : "btnCancel"
		},
		debug: false,

		// Button Settings
		button_image_url : "<?=$this->img_pulsante;?>",	// Relativo alla pagina d'utilizzo
		button_placeholder_id : "spanButtonPlaceholder",
		button_width: 274,
		button_height: 22,

		// The event handler functions are defined in handlers.js
		swfupload_loaded_handler : swfUploadLoaded,
		file_queued_handler : fileQueued,
		file_queue_error_handler : fileQueueError,
		file_dialog_complete_handler : fileDialogComplete,
		upload_start_handler : uploadStart,
		upload_progress_handler : uploadProgress,
		upload_error_handler : uploadError,
		upload_success_handler : uploadSuccess,
		upload_complete_handler : uploadComplete,
		queue_complete_handler : queueComplete,	// Queue plugin event
		
		// SWFObject settings
		minimum_flash_version : "9.0.28",
		swfupload_pre_load_handler : swfUploadPreLoad,
		swfupload_load_failed_handler : swfUploadLoadFailed
	};

	swfu = new SWFUpload(settings);
}
</script>
<?php
}*/
/*
function set2(){ ?>
<link href="<?=JS_SWF?>css/style_mercatino.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="<?=JS_SWF?>swfupload/swfupload.js"></script>
<script type="text/javascript" src="<?=JS_SWF?>js/swfupload.swfobject.js"></script>
<script type="text/javascript" src="<?=JS_SWF?>js/swfupload.queue.js"></script>
<script type="text/javascript" src="<?=JS_SWF?>js/fileprogress.js"></script>
<script type="text/javascript" src="<?=JS_SWF?>js/handlers.js"></script>
<script type="text/javascript">
var swfu;

SWFUpload.onload = function () {
	var settings = {
		flash_url : "<?=$this->flash_url;?>",
		upload_url: "<?=$this->upload_url;?>",	// Relative to the SWF file
		post_params: {
		<?php
		$cnt=0;
		foreach($this->post_params as $k => $v){
			$vir= $cnt==0 ? "" : ",\n";
			print $vir.'"'.$k.'" : "'.$v.'"';
			$cnt++;
		}?>
		},
		file_size_limit : "<?=$this->file_size_limit?> MB",
		file_types : "<?=$this->files_types?>",
		file_types_description : "<?=$this->file_types_description?>",
		file_upload_limit : <?=$this->file_upload_limit?>,
		file_queue_limit : <?=$this->file_queue_limit?>,
		custom_settings : {
			progressTarget : "fsUploadProgress",
			cancelButtonId : "btnCancel"
		},
		debug: false,

		// Button Settings
		button_image_url : "<?=$this->img_pulsante;?>",	// Relativo alla pagina d'utilizzo
		button_placeholder_id : "spanButtonPlaceholder",
		button_width: 274,
		button_height: 22,

		// The event handler functions are defined in handlers.js
		swfupload_loaded_handler : swfUploadLoaded,
		file_queued_handler : fileQueued,
		file_queue_error_handler : fileQueueError,
		file_dialog_complete_handler : fileDialogComplete,
		upload_start_handler : uploadStart,
		upload_progress_handler : uploadProgress,
		upload_error_handler : uploadError,
		upload_success_handler : uploadSuccess,
		upload_complete_handler : uploadComplete,
		queue_complete_handler : queueComplete,	// Queue plugin event
		
		// SWFObject settings
		minimum_flash_version : "9.0.28",
		swfupload_pre_load_handler : swfUploadPreLoad,
		swfupload_load_failed_handler : swfUploadLoadFailed
	};

	swfu = new SWFUpload(settings);
}
</script>
<?php
}
*/
function test(){
	$hidden='';
	foreach($this -> aPostp as $k => $v){
		$hidden.='<input type="hidden" name="'.$k.'" value="'.$v.'" />';
	}
	print '<form method="post" action="'.$this->upload_url.'" enctype="multipart/form-data">
	'.$hidden.'
	<input type="file" name="Filedata" />
	<input type="submit" value="vai" />
	</form>';
	}
}
?>