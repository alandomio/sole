<?php
include_once '../init.php';
$user = new autentica($aA5);
$user -> login_auto(''); # ENTRA NELLA PAGINA INTERNA SE L'UTENTE È GIÀ LOGGATO

	/*
	UU: UPLOADER USER are MHMU and those HMU or HHU who has been selected by the MHMU to upload consumption and production data for one or a selected number of buildings. Flag IS_UPLOADER in users, si presuppone che in default gli ADMIN, GM, MHMU siano upload users	
	*/

/* ESEMPIO INTESTAZIONE A PULSANTIERA
<div data-position="inline" data-role="header" class="ui-bar-a ui-header" role="banner">
			<h1 class="ui-title" tabindex="0" role="heading" aria-level="1">Bar theme "a"</h1>
			<a data-theme="c" data-icon="plus" href="index.html" class="ui-btn-left ui-btn ui-btn-icon-left ui-btn-corner-all ui-shadow ui-btn-up-c"><span class="ui-btn-inner ui-btn-corner-all"><span class="ui-btn-text">New</span><span class="ui-icon ui-icon-plus ui-icon-shadow"></span></span></a>
		</div>


*/



$rB = sole::building_user($user -> aUser['ID_USER']);

$html['LIST_BUILDING'] = ''; $li = '<li data-role="list-divider">Building list</li>';
foreach($rB as $k => $v){
	$h = io::a('controls.php', array('id' => $v['ID_BUILDING']), $v['CODE_BLD'], array());
	$li .= mytag::in($h, 'li', array());
}
$html['LIST_BUILDING'] = mytag::in($li, 'ul', array('data-role' => 'listview', 'data-inset' => 'true', 'data-theme' => 'a', 'data-dividertheme' => 'a', 'data-transition' => 'pop'));


include 'head.php';
?>
<div class="content-secondary"> 
<?=$html['LIST_BUILDING']?>
</div>
<?php
include 'footer.php';
?>