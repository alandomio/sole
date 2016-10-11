<?php
include_once 'init.php';
list($il, $jsstatis) = request::get(array('il' => NULL,'jsstatis' => NULL));

$user = new autentica($aA5);
$user -> login_no_redirect(false);

if($user -> autentica){
	io::headto('home.php', array());
}

//include_once stringa::get_conffile($MYFILE -> filename);
$scheda -> img = false;

$MYFILE -> add_js('
<script type="text/javascript">
	$(function() {
		//height = $(document).height();
		height = $(\'#col-left\').height();
		width = $(document).width();
		
		
		//$("#map_canvas").height(height-350);
		$("#map_canvas").height(height+24);
		$("#map_canvas").width(width-610);
	});
	/*$(window).resize(function() {
		height = $("#content_map").height();
		width = $("#content_map").width();
		$("#map_canvas").css("height", height + "px");
		$("#map_canvas").css("width", width + "px");
	});
	*/
</script>' ,'code', 'head');

$MYFILE -> add_js('<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>', 'file', 'head');

$initialize = '';
$html['gmaps'] = '<div id="map_canvas"></div>
';

$html['wizard_1'] = sole::select_fhb('1', 'select_flat');
$html['wizard_2'] = sole::select_fhb('2', 'select_flat');

# SELECT FEDERATIONS


$input['graphtype'] = new io();
$input['graphtype'] -> type = 'select'; 
$input['graphtype'] -> addblank = true; 
$input['graphtype'] -> aval = rs::id2arr("SELECT ID, TYPE FROM graphtypes WHERE attivo = 1 ORDER BY TYPE ASC"); 
$input['graphtype'] -> css = 'trecento'; 
$input['graphtype'] -> id = 'graphtype'; 
$input['graphtype'] -> txtblank = '- Scegli tipo grafico'; 
$input['graphtype'] -> set('graphtype');

$input['y1'] = new io();
$input['y1'] -> type = 'select'; 
$input['y1'] -> addblank = true; 
$input['y1'] -> aval = rs::id2arr("SELECT ANNO_MS, ANNO_MS FROM measures ORDER BY ANNO_MS ASC"); 
$input['y1'] -> css = 'trecento dn'; 
$input['y1'] -> id = 'y1'; 
$input['y1'] -> txtblank = '- Scegli anno inizio'; 
$input['y1'] -> set('y');

$input['y2'] = new io();
$input['y2'] -> type = 'select'; 
$input['y2'] -> addblank = true; 
$input['y2'] -> aval = rs::id2arr("SELECT ANNO_MS, ANNO_MS FROM measures ORDER BY ANNO_MS ASC"); 
$input['y2'] -> css = 'trecento dn'; 
$input['y2'] -> id = 'y2'; 
$input['y2'] -> txtblank = '- Scegli anno fine'; 
$input['y2'] -> set('y');

$input['p'] = new io();
$input['p'] -> type = 'select'; 
$input['p'] -> addblank = true; 
$input['p'] -> aval['1'] = 'winter';
$input['p'] -> aval['2'] = 'summer';
$input['p'] -> css = 'trecento dn'; 
$input['p'] -> id = 'period'; 
$input['p'] -> txtblank = '- Scegli periodo'; 
$input['p'] -> set('y');

$input['entype1'] = new io();
$input['entype1'] -> type = 'select'; 
$input['entype1'] -> addblank = true; 
$input['entype1'] -> aval = rs::id2arr("SELECT ID_METERTYPE, METERTYPE_IT FROM metertypes  ORDER BY METERTYPE_IT ASC ");  
$input['entype1'] -> css = 'trecento dn'; 
$input['entype1'] -> id = 'et1'; 
$input['entype1'] -> txtblank = '- Scegli tipo energia'; 
$input['entype1'] -> set('et');

$input['entype2'] = new io();
$input['entype2'] -> type = 'select'; 
$input['entype2'] -> addblank = true; 
$input['entype2'] -> aval = rs::id2arr("SELECT ID_METERTYPE, METERTYPE_IT FROM metertypes  ORDER BY METERTYPE_IT ASC "); 
$input['entype2'] -> css = 'trecento dn'; 
$input['entype2'] -> id = 'et2'; 
$input['entype2'] -> txtblank = '- Scegli tipo energia'; 
$input['entype2'] -> set('et');

'ajax/report.php';

$link_last_report = '';
if($user -> idg == 5){
	$link_last_report = io::a('ajax/report.php', array('id' => $user -> aUser['CODE']), DOWNLOAD_REPORT, array('class' => 'g-button g-button-yellow'));
}

if(array_key_exists('first_login', $_GET)){
	if (isset($_SERVER['HTTP_USER_AGENT'])) {
		$agent = trim($_SERVER['HTTP_USER_AGENT']);
	}
	if(!empty($agent) && $user -> idg < 5){
		
		$browsers = ' <a href="http://www.google.it/search?q=Google+Chrome+download">Chrome</a> <a href="http://www.google.it/search?q=Mozilla+Firefox+download">Firefox</a> <a href="http://www.google.it/search?q=safari+download">Safari</a>';
	
		if(strpos($agent, 'Firefox') === false && strpos( $agent, 'Chrome') === false){
			$MYFILE -> add_err(ERR_BROWSER.$browsers);
		}
	
	}
}

$html['youtube'] = LANG_DEF == 'IT' ? '
<iframe width="186" height="124" src="http://www.youtube.com/embed/a8oeMQE6Zb8" frameborder="0" allowfullscreen></iframe>' : '
<iframe width="186" height="124" src="http://www.youtube.com/embed/juboAEYXD60" frameborder="0" allowfullscreen></iframe>';

$html['login'] = file_get_contents(AJAX.'login_form.php');
$html['link-codice-appartamento'] = io::a('enable-user.php', array(), LABEL_INSERT_FLAT_CODE, array('class' => 'g-button g-button-yellow'));

include_once HEAD_AR;
?>
<div id="col-left">
<form id="myForm" method="post">
<div id="wizard">
<div id="graph">
<span>Graph type</span>
<? $input['graphtype'] -> get(); ?>
<? $input['y1'] -> get(); ?>
<? $input['y2'] -> get(); ?>
<? $input['p'] -> get(); ?>
</div>
<div id="wizard1" style="display:none;">
<span>First entity</span>
<?=$html['wizard_1']?>
<? $input['entype1'] -> get(); ?>
</div>
<div id="wizard2" style="display:none;">
<span>Second entity</span>
<?=$html['wizard_2']?>

<? $input['entype2'] -> get(); ?>
</div>

<input type="button" value="Demo grafico" class="g-button g-button-yellow m-top" id="button_grafico" />
<?=BR.BR.$link_last_report?>
</div>
</form>

<div id="left-login">
<h2>Login</h2>
<?=$html['login']?>
</div>

<div id="left-register-flat">
<h2><?=LABEL_REGISTER_FLAT?></h2>
<?=$html['link-codice-appartamento']?>
</div>

</div>
<div id="col-main">
<?=$html['gmaps']?>
<?=sole::get_coords_buildings()?>
</div>
<div id="col-right">
<div id="youtube">
<h2><?=LABEL_YOUTUBE?></h2>
<?=$html['youtube']?>
</div>

<a href="http://www.abitaresostenibile.coop/" title="Abitare sostenibile: sostenibilit&agrave; ambientale, economica, sociale" target="_blank"><img src="<?=IMG_LAYOUT?>logo-abitaresostenibile.png" alt="www.abitaresostenibile.coop" width="186" height="90" /></a>

</div>

<div id="grafico" title="Comsumption graph">
<img  style="margin:10px 12px;" id="graphimage" src="" />
</div>

<div class="clear"></div>
<?php
include_once FOOTER_AR;
?>