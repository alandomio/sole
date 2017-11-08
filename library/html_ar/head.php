<?php
# AGGIUNGO SCRIPT
$MYFILE -> add_js('<script type="text/javascript" src="js/jquery/jquery.ui.datepicker-it.js" ></script>', 'file', 'footer');
$MYFILE -> add_js('<script type="text/javascript" src="js/jquery/jquery.json-2.2.min.js" ></script>', 'file', 'footer');
$MYFILE -> add_js('<script type="text/javascript" src="js/jquery/jquery.languageswitcher.js" ></script>', 'file', 'footer');

if($MYFILE -> file != 'home.php'){
	$MYFILE -> add_js('<script type="text/javascript" src="js/layout.js" ></script>', 'file', 'footer');
}

$MYFILE -> add_js('<script type="text/javascript" src="'.JS_TINYMCE.'jscripts/tiny_mce/tiny_mce.js"></script>', 'file', 'head');
$MYFILE -> add_js('<script type="text/javascript">
tinyMCE.init({
		mode : "textareas",
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
</script>', 'code', 'head');
$MYFILE -> add_js('<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push([\'_setAccount\', \'UA-4745234-21\']);
  _gaq.push([\'_trackPageview\']);

  (function() {
    var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
    ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';
    var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>', 'code', 'head');

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

$MYFILE -> add_js('<script src="'.JS_MAIN.'fnc.js" type="text/javascript"></script>', 'file', 'head');
##############################

$nav = array(); $main_menu = ''; $path = '';

# NUOVO MENU
$nav[ MENU_SOLE_PROJECT ] = array(
	'Homepage' => array('home')
);

if($user -> autentica){
	/*
	$aA1 = array('ADMIN');
	$aA2 = array('ADMIN', 'FM');
	$aA3 = array('ADMIN', 'FM', 'MHCU');
	$aA4 = array('ADMIN', 'FM', 'MHCU', 'HCU');
	$aA5 = array('ADMIN', 'FM', 'MHCU', 'HCU', 'HHU');
	*/

	$gp = $user -> aUser['GRUPPI'];
	if(in_array($gp, $aA1)){
		$nav[ MENU_SYSTEM ] = array(
			
			$MYFILE -> files_list['descriptors_types.php']['TITLE_'.$lang -> def] => nw::file_list('descriptors_types'),				
			$MYFILE -> files_list['metertypes.php']['TITLE_'.$lang -> def] => nw::file_list('metertypes'),
			$MYFILE -> files_list['descriptors.php']['TITLE_'.$lang -> def] => nw::file_list('descriptors'),
			$MYFILE -> files_list['lablesites.php']['TITLE_'.$lang -> def] => nw::file_list('lablesites'),
			$MYFILE -> files_list['lables.php']['TITLE_'.$lang -> def] => nw::file_list('lables'),
			$MYFILE -> files_list['myfiles.php']['TITLE_'.$lang -> def] => nw::file_list('myfiles'),
			$MYFILE -> files_list['remove_from_db.php']['TITLE_'.$lang -> def] => nw::file_list('remove_from_db')

		);
	}
	
	// in base al gruppo costruisco il menu utenti
	if($user -> ck_group($aA3)) $nav[ MENU_USERS ][
		$MYFILE -> files_list['users.php']['TITLE_'.$lang -> def]
	] = nw::file_list('users');
	if($user -> ck_group($aA2)) $nav[ MENU_USERS ][
		$MYFILE -> files_list['federations.php']['TITLE_'.$lang -> def]
	] = array_merge(nw::file_list('federations'),array('federations_address'));
	if($user -> ck_group($aA3))$nav[ MENU_USERS ][
		$MYFILE -> files_list['hcompanys.php']['TITLE_'.$lang -> def]
	] = nw::file_list('hcompanys');
	if($user -> ck_group($aA4))$nav[ MENU_USERS ][
		$MYFILE -> files_list['buildings.php']['TITLE_'.$lang -> def]
	] = array_merge(nw::file_list('buildings'),array('buildings_address','buildings_users_ext'));
	if($user -> ck_group($aA3))$nav[ MENU_USERS ][
		$MYFILE -> files_list['flats.php']['TITLE_'.$lang -> def]
	] = nw::file_list('flats');
	if($user -> ck_group($aA4))$nav[ MENU_USERS ][
		$MYFILE -> files_list['meters-ins.php']['TITLE_'.$lang -> def]
	] = array_merge(array('meters-ins','meters-sinottica'), nw::file_list('meters'));
	if($user -> ck_group($aA4)) $nav[ MENU_MEASURES ][
		$MYFILE -> files_list['measures-ins.php']['TITLE_'.$lang -> def]
	] = array_merge(array('measures-ins'), nw::file_list('measures'));
	if($user -> ck_group($aA4)) $nav[ MENU_MEASURES ][
	$MYFILE -> files_list['measures2.php']['TITLE_'.$lang -> def]
	] = array('measures2');	
	if($user -> ck_group($aA4)) $nav[ MENU_MEASURES ][
	$MYFILE -> files_list['measures12.php']['TITLE_'.$lang -> def]
	] = array('measures12');	
	if($user -> ck_group($aA3)) $nav[ MENU_MEASURES ][
		$MYFILE -> files_list['validations.php']['TITLE_'.$lang -> def]
	] = nw::file_list('validations');
	
	if($user -> idg == 5) $nav[ MENU_SOLE_PROJECT ][
		$MYFILE -> files_list['info-hhu.php']['TITLE_'.$lang -> def]
	] = array('info-hhu');
	if($user -> ck_group($aA5)) $nav[MENU_MEASURES][
		$MYFILE -> files_list['outputs.php']['TITLE_'.$lang -> def]
	] = nw::file_list('outputs');

	# ELIMINO LA VOCE DI MENU IN CASI PARTICOLARI
/* 	if($user -> aUser['ID_GRUPPI'] > 3 && $user -> aUser['IS_UPLOADER'] != 1){
		unset($nav['Misurazioni']);
	}
 */
	
	$main_menu = ''; $path = '';
	foreach($nav as $k => $arr){
		$t_sm = '';	$menu_sel = '';

		if(in_array($MYFILE -> filename, $arr)){
			$menu_sel = ' class="menu_sel"';
		}
		
		$main_menu .= '
		<li><a href="#">'.$k.'</a>
		<ul class="sublist"><!-- SUB_MENU --></ul>
		</li>
		';
		
		foreach($arr as $titolo => $aFiles){
			if(in_array($MYFILE -> filename, $aFiles)){
				$main_menu = str_replace('<a href="#">'.$k.'</a>', '<a href="#" class="menu_sel">'.$k.'</a>', $main_menu);
			}
			$t_sm .= '<li class="sub"><a href="'.$aFiles[0].'.php">'.$titolo.'</a></li>'."\n";
			if(in_array($MYFILE -> filename, $aFiles)){
				$path = $k.' &rsaquo; '.$titolo;
			}
		}
		$main_menu = str_replace('<!-- SUB_MENU -->', $t_sm, $main_menu);
	}
} else { # menu statico per utenti non registrati
	$main_menu = '<li><a href="index.php">'.$MYFILE -> files_list['index.php']['TITLE_'.$lang -> def].'</a></li>';
	$path = MENU_SOLE_PROJECT.' &rsaquo; '.$MYFILE -> files_list['index.php']['TITLE_'.$lang -> def];
}

$main_menu = '
<div id="menu-downloads" style="position:absolute; top:160px; right:30px; padding:12px 0 0 0;">
<a href="'.SYSTEM_PATH.'downloads/hhu-guide-'.strtolower(LANG_DEF).'.pdf" title="'.MANUALE_DUSO.'" target="_blank" />'.MANUALE_DUSO.'</a>
</div>


<div id="myslidemenu" class="jqueryslidemenu">
<ul id="nav">
'.$main_menu.'
</ul>
</div>
';

// $lng_menu = '
// <div id="myslidemenu" class="menu_lang">
// <ul id="">
// <li><img src="images/IT.png" alt="'.LANGUAGE.'" />
// 	<ul><li><img src="images/IT.png" alt="'.LANGUAGE.'" /> Italiano</li>
// 		<li><img src="images/EN.png" alt="'.LANGUAGE.'" /> Inglese</li>
// 	</ul>	
// 		</li>
// </ul>
// </div>
// ';

$class = ''; $select_lang = '';
foreach($lang -> langs as $k => $ln){
	// $selected = '';
// 	if($ln == $lang -> def){
// 		$selected = ' selected="selected"';
// 		$class = 'flag_'.strtolower($ln);
// 	}
	//$select_lang .= '<option'.$selected.' value="'.strtolower($ln).'">'.constant('INT_'.$ln).'</option>';
	$select_lang .= '<li> <a class="change_lang" href="#" title="'.strtolower($ln).'"><span>'.constant('INT_'.$ln).'</span><img src="images/'.$ln.'.png" alt="'.LANGUAGE.'" /></a></li>';
}

//$fileget = $MYFILE -> file.url::mk_get($_GET);
//$fileget = url::mk_get($_GET);

// $select_lang = '<div id="langs"><img src="images/'.$lang -> def.'.png" alt="'.LANGUAGE.'" />
// <select name="lang" id="languages">
// '.$select_lang.'
// </select>
// </div>

// <script type="text/javascript">
// $(document).ready(function(){
// 	$("#languages").change(function(){
		
// 		var goto = "change_lang.php?lang=" + $(this).val() + "&return='.$MYFILE -> file.'";
// 		// alert(goto);
// 		window.location = goto;
// 	});
// });
// </script>
// ';

$select_lang = '<div class="rfloat">
<input type="hidden" id="goto" value="'.$MYFILE -> file.'" />
<a id="topUserLink" href="#" class="sel-white"><img src="images/'.$lang -> def.'.png" alt="'.LANGUAGE.'" /></a>
</div>
<div class="shadow-3" id="u-box">
<ul>'.
$select_lang.'
</ul>
</div>		
';


$html['block_user'] =  '<div id="logout">'.$select_lang.'</div>';
//'<div id="logout"><a href="login.php" title="Login">Login</a><a href="enable-user.php" title="Enable User">Codice appartamento</a></div>';

if(!empty($user -> aUser['ID_USER'])){
	$html['block_user'] = '<div id="logout">'.$select_lang.'<strong>'.$user -> aUser['USER'].'</strong> ('.$user -> aUser['TITLE'].')
	<a href="password.php" title="'.CHANGE_PASSWORD.'">'.CHANGE_PASSWORD.'</a> <a href="index.php?action=logout">Logout</a>
	
	</div>';

}

if($user -> autentica){
	$html['block_user'] = '<div id="logout">'.$select_lang.'<strong>'.$user -> aUser['USER'].'</strong> ('.$user -> aUser['TITLE'].')
	<a href="password.php" title="'.CHANGE_PASSWORD.'">'.CHANGE_PASSWORD.'</a> <a href="index.php?action=logout">Logout</a>
	</div>';
}

if($MYFILE -> filename == 'index'){
	$MYFILE -> title = 'Homepage Sole Project';
}

$html['titolo_sezione'] = empty($MYFILE -> title) ? io::a('myfiles_c.php', array('mknew' => $MYFILE -> file), $MYFILE -> file, array('target' => '_blank')) : $MYFILE -> title;

$MYFILE -> catch_buffer();
/* xmlns="http://www.w3.org/1999/xhtml" */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<link rel="shortcut icon" href="favicon.ico" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?=$MYFILE -> css?>
<?=$MYFILE -> js_head?>
<title><?=$MYFILE -> title?></title>
</head>
<body<?=$initialize?>>
<div id="main-messages" class="message"><ul></ul></div>
<div id="intestazione">
<div id="logo"><a href="<?=MAIN_SITE?>" title="Homepage"><img src="<?=IMG_LAYOUT?>logo.png" alt="Progetto Sole" /></a></div>
<!-- <div id="path"><?=$path?></div> --> 
<div id="menu_top">
<?=$html['block_user']?>
<?=$main_menu?>
</div>
</div>

<div id="mother"><?
print $MYFILE -> system_errors;
$MYFILE -> print_msg(true);
?>
<div id="message" class="alert"></div>

<h2><?=$path?></h2>
<?php
foreach($ERR_CRUD as $k => $v){
	if(!empty($v)) $err[] = $v;
}
?>