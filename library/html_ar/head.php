<?php
/*
 * css
 * */
$MYFILE->add_css(CSS.'style.css');
$MYFILE->add_css(JS_JQUERY.'css/custom-theme/jquery-ui-1.8.11.custom.css');
$MYFILE->add_css(CSS.'jquery.alerts.css');
$MYFILE->add_css(CSS.'jquery.loady.css');
$MYFILE->add_css('js/tooltipster/tooltipster.css');
$MYFILE->add_css(JS_JQUERY.'css/jqueryslidemenu.css');

/*
 * include javascript comuni a tutte le pagine
 * */
$MYFILE->add_js_group('main', array(
		JS_JQUERY.'jquery-1.7.min.js',
		JS_JQUERY.'jquery-ui-1.8.10.custom.min.js',
		JS_JQUERY.'jqueryslidemenu.js',
		JS_JQUERY.'jquery.form.js',
		JS_JQUERY.'jquery.alerts.js',
		JS_JQUERY.'jquery.loady.min.js',
		JS_JQUERY.'jquery.ui.dialog.js',
		JS_MAIN.'maps/functions.js',
		'js/jquery/jquery.ui.datepicker-it.js',
		'js/jquery/jquery.json-2.2.min.js',
		'js/jquery/jquery.languageswitcher.js',
		'js/tooltipster/jquery.tooltipster.min.js',
		'js/layout.js',
),
		10, 'on', 'head');

$MYFILE->add_js(JS_TINYMCE.'jscripts/tiny_mce/tiny_mce.js', 20, 'head', 'file');
$MYFILE->add_js('
tinyMCE.init({
		mode : "textareas",
		editor_selector : "editor",
		theme : "advanced",
		plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,inlinepopups,autosave",
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,preview,|,forecolor,backcolor,|,fullscreen",
		theme_advanced_buttons3 : "",
		theme_advanced_buttons4 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,
		template_external_list_url : "lists/template_list.js",
		external_link_list_url : "lists/link_list.js",
		external_image_list_url : "lists/image_list.js",
		media_external_list_url : "lists/media_list.js",
	});
', 21, 'head', 'code');

$MYFILE->add_js('
  var _gaq = _gaq || [];
  _gaq.push([\'_setAccount\', \'UA-4745234-21\']);
  _gaq.push([\'_trackPageview\']);

  (function() {
    var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
    ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';
    var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);
  })();
', 30, 'head', 'code');

if(isset($is_swf) && $is_swf == 1){
$swf = new swfupload();
$swf -> upload_url="../login/upload.php";
$swf -> img_pulsante="../library/img_ar/upload.png";
$swf -> post_params = array("folder"=>$_GET["id_rec"]);
$swf -> set_gradient();

$MYFILE -> add_css($swf -> css);
$MYFILE -> add_js($swf -> jsf, 'file', 'head');
$MYFILE -> add_js($swf -> jsc, 'code', 'head');
}

$nav = array(); $main_menu = ''; $path = '';

/*
 * menu visibile a tutti
 * */
// $nav[ 'aa '.MENU_SOLE_PROJECT ] = array(
// 	'Homepage' => array('home')
// );

/*
 * menu per utenti registrati
 * */
if($user -> autentica){
	/*
	$aA1 = array('ADMIN');
	$aA2 = array('ADMIN', 'GM');
	$aA3 = array('ADMIN', 'GM', 'MHMU');
	$aA4 = array('ADMIN', 'GM', 'MHMU', 'HMU');
	$aA5 = array('ADMIN', 'GM', 'MHMU', 'HMU', 'HHU');
	*/

	$gp = $user -> aUser['GRUPPI'];
	
	/*
	 * menu "Sistema" per utenti admin
	 * */
	if(in_array($gp, $aA1)){
		$nav[ MENU_SYSTEM ] = array(
			// $MYFILE->files_list['descriptors_types.php']['TITLE_'.$lang->def] => nw::file_list('descriptors_types'),				
			$MYFILE -> files_list['metertypes.php']['TITLE_'.$lang -> def] => nw::file_list('metertypes'),
			$MYFILE -> files_list['descriptors.php']['TITLE_'.$lang -> def] => nw::file_list('descriptors'),
			$MYFILE -> files_list['lablesites.php']['TITLE_'.$lang -> def] => nw::file_list('lablesites'),
			$MYFILE -> files_list['lables.php']['TITLE_'.$lang -> def] => nw::file_list('lables'),
			$MYFILE -> files_list['myfiles.php']['TITLE_'.$lang -> def] => nw::file_list('myfiles'),
			$MYFILE->files_list['remove_from_db.php']['TITLE_'.$lang->def] => nw::file_list('remove_from_db'),
			'Clean cache js' => array('reset_cache_js'),
		);
	}
	
	/*
	 * menu condizionato dal gruppo
	 * */
	if($user -> ck_group($aA3)) $nav[ MENU_USERS ][
		$MYFILE -> files_list['users.php']['TITLE_'.$lang -> def]
	] = nw::file_list('users');
	if($user -> ck_group($aA2)) $nav[ MENU_USERS ][
		$MYFILE -> files_list['federations.php']['TITLE_'.$lang -> def]
	] = array_merge(nw::file_list('federations'),array('federations_address', 'federations_conversions'));
	if($user -> ck_group($aA3))$nav[ MENU_USERS ][
		$MYFILE -> files_list['hcompanys.php']['TITLE_'.$lang -> def]
	] = nw::file_list('hcompanys');
	if($user -> ck_group($aA4))$nav[ MENU_USERS ][
		$MYFILE -> files_list['buildings.php']['TITLE_'.$lang -> def]
	] = array_merge(nw::file_list('buildings'),array('buildings_address','buildings_users_ext'));
	if($user -> ck_group($aA3))$nav[ MENU_USERS ][
		$MYFILE -> files_list['flats.php']['TITLE_'.$lang -> def]
	] = nw::file_list('flats');
	
// 	if($user->ck_group($aA4))$nav[ MENU_USERS ][
// 		$MYFILE->files_list['meters-ins.php']['TITLE_'.$lang->def]
// 	] = array_merge(array('meters-ins','meters-sinottica'), nw::file_list('meters'));
	
	if($user->ck_group($aA4)){
		$nav[ MENU_USERS ][ $MYFILE->files_list['meters.php']['TITLE_'.$lang->def] ] = array_merge(array('meters'), nw::file_list('meters'));
	}
	
// 	if($user->ck_group($aA4)) $nav[ MENU_MEASURES ][
// 		$MYFILE->files_list['measures-ins.php']['TITLE_'.$lang->def]
// 	] = array_merge(array('measures-ins'), nw::file_list('measures'));
	
	if($user -> ck_group($aA4)) $nav[ MENU_MEASURES ][
	$MYFILE -> files_list['measures2.php']['TITLE_'.$lang -> def]
	] = array('measures2');	
	
// 	if($user->ck_group($aA4)) $nav[ MENU_MEASURES ][
// 	$MYFILE->files_list['measures12.php']['TITLE_'.$lang->def]
// 	] = array('measures12');
		
	if($user -> ck_group($aA3)) $nav[ MENU_MEASURES ][
		$MYFILE -> files_list['validations.php']['TITLE_'.$lang -> def]
	] = nw::file_list('validations');
	
// 	if($user->idg == 5) $nav[ MENU_SOLE_PROJECT ][
// 		$MYFILE->files_list['info-hhu.php']['TITLE_'.$lang->def]
// 	] = array('info-hhu');
	if($user -> ck_group($aA5)) $nav[MENU_MEASURES][
		$MYFILE->files_list['output.php']['TITLE_'.$lang->def]
	] = nw::file_list('output');

	if($user->ck_group($aA3)){
		$nav[ MENU_MEASURES ]['Reset cache'] = nw::file_list('reset_cache');
	}
	
	/*
	 * menu utente
	 * */
	$nav[$user->aUser['NAME'].' '.$user->aUser['SURNAME']]=array(
		PROFILE=>array('profile'),
		MANUALE_DUSO=>array('downloads/hhu-guide-'.strtolower(LANG_DEF).'.pdf'),
		'Logout'=>array('logout')
	);

	/*
	 * menu scelta lingua
	 * */
	$files_langs[constant('INT_'.LANG_DEF)]=array('change-lang.php?ln='.LANG_DEF);
	foreach($lang->langs as $k => $ln){
		if($ln != LANG_DEF){
			$files_langs[constant('INT_'.$ln)]=array('change-lang.php?ln='.$ln);
		}
	}
	
	$nav[constant('INT_'.LANG_DEF)]=$files_langs;
	
} else {

	/*
	 * utente non loggato
	 * */
	$nav['Home']=array(
			'Home'=>array('index.php')
	);
// 	$nav[__('Cos\'è Hive')]=array(
// 			'sole'=>array('about.php')
// 	);
	$nav[__('Edifici')]=array(
			'edifici'=>array('building-chart.php')
	);
// 	$nav[__('Come aderire')]=array(
// 			'collaboration'=>array('collaboration.php')
// 	);
	$nav[__('Contatti')]=array(
			'contacts'=>array('contacts.php')
	);
	
	/*
	 * menu scelta lingua
	 * */
	$files_langs[constant('INT_'.LANG_DEF)]=array('change-lang.php?ln='.LANG_DEF);
	foreach($lang->langs as $k => $ln){
		if($ln != LANG_DEF){
			$files_langs[constant('INT_'.$ln)]=array('change-lang.php?ln='.$ln);
		}
	}
	$nav[constant('INT_'.LANG_DEF)]=$files_langs;
	}
	
	$main_menu = ''; $path = '';
	foreach($nav as $k => $arr){
		$t_sm = '';	$menu_sel = '';

		if(in_array($MYFILE -> filename, $arr)){
			$menu_sel = ' class="menu_sel"';
		}
		
		$main_menu .= '
	<li>@@'.$k.'@@
	<!-- SUB_MENU -->
		</li>
		';
		
	$count=0;
		foreach($arr as $titolo => $aFiles){
		
		$filename= strpos($aFiles[0], '.php') !== false ? $aFiles[0] : $aFiles[0].'.php';
		$target='';
		if(strpos($aFiles[0], '.pdf')){
			$filename=$aFiles[0];
			$target=' target="_blank"';
			}
		
		/*
		 * crea il titolo per la pagina
		 * */
			if(in_array($MYFILE -> filename, $aFiles)){
				$path = $k.' &rsaquo; '.$titolo;
			}

		/*
		 * link per i sottomenu,
		 * i menu con una sola voce non hanno sottovoci di menu
		 * */
		if(count($arr)>1){
			$t_sm .= '<li class="sub"><a href="'.$filename.'"'.$target.'>'.$titolo.'</a></li>'."\n";
		}

		if($count==0){
			$class = in_array($MYFILE->filename, $aFiles) ? ' class="menu_sel top_menu"' : ' class="top_menu"';

			$icon= strpos($aFiles[0], 'change-lang.php') !== false ? '<span><img src="images/icon-world.png" style="float:left;margin-right:8px;" /></span>' : '';
			$main_menu = str_replace('@@'.$k.'@@', '<a href="'.$filename.'"'.$class.'>'.$icon.'<span>'.$k.'</span></a>', $main_menu);
		}

		$count++;
	}

	$main_menu = str_replace('<!-- SUB_MENU -->', '<ul class="sublist">'.$t_sm.'</ul>', $main_menu);
}

//if($user->autentica){
	$logo='<div id="logo"><a href="'.MAIN_SITE.'" title="Homepage"><img src="'.IMG_LAYOUT.'logo-sole.png" alt="Progetto Hive" /></a></div>';
	$header_homepage='';
	$class='';
//} else {
//	$logo='';
//	$header_homepage='<div id="header_homepage">
//		<div id="logo_homepage"><a href="../" title="Homepage"><img src="images/homepage/sole_homepage.png" alt="Progetto Hive" /></a></div>
//	</div>';
//	$class=' no-bg';
//}

$main_menu = '
<div id="myslidemenu" class="jqueryslidemenu'.$class.'">
	<ul id="nav">
		'.$main_menu.'
</ul>
</div>		
';

if($MYFILE -> filename == 'index'){
	$MYFILE->title = 'Homepage Hive Project';
}

$html['titolo_sezione'] = empty($MYFILE -> title) ? io::a('myfiles_c.php', array('mknew' => $MYFILE -> file), $MYFILE -> file, array('target' => '_blank')) : $MYFILE -> title;

$MYFILE -> catch_buffer();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<link rel="shortcut icon" href="favicon.ico" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?=$MYFILE -> css?>
	<?
	$MYFILE->print_js($position='head', $type='file');
	$MYFILE->print_js($position='head', $type='code');
	?>
<title><?=$MYFILE -> title?></title>
</head>
<body<?=$initialize?>>

<?
function get_user_browser(){
	$u_agent = $_SERVER['HTTP_USER_AGENT'];
	$ub = '';
	if(preg_match('/MSIE/i',$u_agent)){
		$ub = "ie";
	}
	elseif(preg_match('/Firefox/i',$u_agent)){
		$ub = "firefox";
	}
	elseif(preg_match('/Safari/i',$u_agent)){
		$ub = "safari";
	}
	elseif(preg_match('/Chrome/i',$u_agent)){
		$ub = "chrome";
	}
	elseif(preg_match('/Flock/i',$u_agent))	{
		$ub = "flock";
	}
	elseif(preg_match('/Opera/i',$u_agent))	{
		$ub = "opera";
	}
	return $ub;
}

$ub = get_user_browser();
if($ub=='ie'){ 

?>
<script>
	$(document).ready(function(){
		message('y', '<?=__('Browser non supportato. Si consigliano Firefox, Chrome, Safari')?>', false);
	});
</script>
<?
}
?>


<div id="main-messages" class="message"><ul></ul></div>
<?=$header_homepage?>
<div id="head">
<div id="intestazione">
	<?=$logo?>
<div id="menu_top">
<?=$main_menu?>
</div>
</div>
</div>

<div id="mother"><?
print $MYFILE -> system_errors;
$MYFILE -> print_msg(true);
if($user->autentica){
?>
<div id="dashboard">
    <ul>
    	<li class="icona icona-profilo"><a href='profile.php'><span><?=__('Profilo')?></span></a></li>
    	<li class="icona icona-edificio"><a href='building-chart.php'><span><?=__('Edifici')?></span></a></li>
    	<? if($user->aUser['ID_GRUPPI']==5) {?>
    	<li class="icona icona-gestore"><a href='hcompany-chart.php'><span><?=__('Gestore')?></span></a></li>
    	<li class="icona icona-appartamento"><a href='flat-chart.php'><span><?=__('Appartamento')?></span></a></li>
    	<li class="icona icona-report"><a href='/ajax/report.php?id=<?=$user->aUser['CODE']?>'><span>Report</span></a></li>
    	<?} ?>
    	<li class="icona icona-output"><a href='output.php'><span><?=__('Risultati')?></span></a></li>
    	<? if($user->ck_group($aA2)) {?>
    	<li class="icona icona-contatori"><a href='meters.php'><span><?=__('Contatori')?></span></a></li>
    	<?} ?>
    	<? if($user->ck_group($aA4) || $user->aUser['IS_UPLOADER']==1) {?>
    	<li class="icona icona-misurazioni"><a href='measures2.php'><span><?=__('Misurazioni')?></span></a></li>
    	<?} ?>
    	<? if($user->ck_group($aA3)) {?>
    	<li class="icona icona-convalida"><a href='validations.php'><span><?=__('Convalida')?></span></a></li>
    	<?} ?>
     </ul>
 </div>
<? } ?>
<div class="clear"></div>
<div id="message" class="alert"></div>
<div id="breadcrumb">
<h2><?=$path?></h2>
</div>
<?php
foreach($ERR_CRUD as $k => $v){
	if(!empty($v)) $err[] = $v;
}
?>
<div class="clear"></div>