<?php
session_start();
ini_set("memory_limit", "128M");
# DOMINIO
define('NOME_SITO', 'Sole Project');
define('DOMINIO', 'sole-project.com');
define('RAGIONE_SOCIALE_AZIENDA', '');
define('INDIRIZZO_AZIENDA', '');

# PUNTAMENTI ASSOLUTI
define('SUBDIR_REMOTE',"");
define('SUBDIR_LOCAL',"");
define('LOGIN_ACTION',"index.php");
$on_line=strpos($_SERVER['HTTP_HOST'], DOMINIO)===false ? false : true;
$is_on_local_server = false;

if( ! $on_line){
	// IP E NOMI HOST SUL WEBSERVER DI SVILUPPO CONDIVISO
	$a_local_server_urls = array('sole.dev');
	foreach($a_local_server_urls as $k => $url){
		if(strpos($_SERVER['HTTP_HOST'], $url) !== false){
			// $is_on_line = false;
			$is_on_local_server = true;
			$db_area = 'local_server';
			break;
		}
	}
}

define('SUBDIR_TESTING', $on_line ? SUBDIR_REMOTE : SUBDIR_LOCAL);

# INIZIALIZZAZIONE CONFIGURAZIONI ASSOLUTE
/*error_reporting(7); 
ini_set('display_errors', 'on');
*/

ini_get("default_charset"); 
intval(ini_get("memory_limit"))<=32 ? '' : ini_set('memory_limit',"128M"); ini_get("max_execution_time"); ini_get("post_max_size"); define('UPLOAD_MAX_FILESIZE',ini_get("upload_max_filesize"));

// echo ini_set("display_errors", '7'); // ini_set('display_errors',"7");

# PATHS FONDAMENTALI
define('SYSTEM_PATH', @opendir('library/') ? ''  : '../');
define('LIBRARY', SYSTEM_PATH.'library/');
define('CLASSES_PATH', LIBRARY.'classes/');
define('LIBRARY_CONDIVISA', '../'.SYSTEM_PATH.'library/classes_2/');

define('CONTATTI', LIBRARY.'contatti/');
define('CLASSES', CLASSES_PATH);

# IMPORTA LIBRERIA (PHP FILES)
if(function_exists("__autoload")){
	function __autoload($class_name){
	require_once(CLASSES.$class_name.".php");}}
	else {if($handle = opendir(CLASSES)); 
	else die("Errore di sistema dir mancante");
	while (false !== ($file = readdir($handle))){ 
	if ($file != "." && $file != ".." && $file != "Thumbs.db" && !is_dir($file) && basename($file)!=basename($file,".php")){
		include_once(CLASSES.$file); }}
	closedir($handle); 
}

# IMPORT LIBRERIE PERSONALIZZATE
if($extra = @opendir(LIBRARY.'personal')){
	while(false !== ($file = readdir($extra))){ 
	if ($file != "." && $file != ".." && $file != "Thumbs.db" && !is_dir($file) && basename($file)!=basename($file,".php")){
		include_once(LIBRARY.'personal/'.$file);}
	}
	closedir($extra); 
}

# GMAPS
define('DEF_COORDS', '41.442, 12.392');

# DB E SERVER
if($on_line){
	define('DBHOST', "localhost" );
	define('DBUSER', "c11u1" );
	define('DBNAME', "c11db1");
	define('DBPSW',  "WahMiG6a" );
} 
elseif($is_on_local_server){
	define('DBHOST', "localhost" );
	define('DBUSER', "root" );
	define('DBNAME', "sole");
	define('DBPSW',  "password" );
} else {
	define('DBHOST', "localhost" );
	define('DBUSER', "sole" );
	define('DBNAME', "sole");
	define('DBPSW',  "sole" ); 
}

define('KEYCIFRA','SF35BAH3787GHhNBVS(76786AHDFJH4HGAsBlLKhBDFGSDFG��^�D2;MJNBADd546Kiudyh&7aya');
define('UPLOAD_MAX_BYTE',1024*1024*(stringa::charclear(UPLOAD_MAX_FILESIZE,"0123456789")));

# LOGIN
define('Q_AUTENTICA', "SELECT
	gruppis.*,
	users.*,
	users_federations.ID_FEDERATION
	FROM
	users
	Left Join gruppis USING(ID_GRUPPI)
	Left Join users_federations USING(ID_USER)
	WHERE
	users.USER = '<!-- USERNAME -->' AND
	users.PASSWORD = '<!-- PASSWORD -->'");

	# EMAIL
define('MAILTO','riccardosiccardi@gmail.com');

# IMPOSTAZIONI DATABASE
$conx = rs::conn();
mysql_query('set names utf8');
mysql_query('max_allowed_packet='.UPLOAD_MAX_FILESIZE.'');

# IMPOSTAZIONI PROGRAMMA
define('SHOW_FROM_YEAR', '2009');

$PATHINFO=pathinfo($_SERVER['PHP_SELF']);
define('DIRNAME',   $PATHINFO['dirname']);
define('BASENAME',  $PATHINFO['basename']);
define('EXTENSION', $PATHINFO['extension']);
define('FILENAME',  $PATHINFO['filename']);
define('HTTP_HOST', $_SERVER['HTTP_HOST'] );
define('PHP_SELF', $_SERVER['PHP_SELF'] );
define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT'] );
define('ABS_PATH',"http://".url::urlunix(HTTP_HOST."/".SUBDIR_TESTING));
define('MAIN_SITE',  $on_line ? 'http://'.DOMINIO.'/'.SUBDIR_TESTING : ABS_PATH);

define('ABS_PATH_CONTATTI', ABS_PATH.'library/contatti/');
define('FULL_URL', 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

define('NEWSLETTER', SYSTEM_PATH.'newsletter/');
define('ABS_NEWSLETTER', ABS_PATH.'newsletter/');
define('HEADER_NWL', ABS_NEWSLETTER.'header.png');
define('FOOTER_NWL', ABS_NEWSLETTER.'footer.png');
define('BKGROUND_NWL', ABS_NEWSLETTER.'bg.png');

define('ABS_HEAD_NEWSLETTER', ABS_NEWSLETTER.'header.png');
define('ABS_IMG_NEWSLETTER_PULS',ABS_NEWSLETTER.'pulsante_nl.png');

//define('NEWSLETTER', LIBRARY.'newsletter/');
define('TESTA_MAIL', ABS_PATH.'images/head_mail.gif');
//define('TESTA_MAIL2', 'header_nl2.png');

# MESSAGGI ERRORE, CRUD ARRAY E ALTRE VARIABILI
define('EKO_ERR', '1');
define('ERR_SQL', '1'); //$ERR_CRUD[SYSTEMERR]:0="&nbsp;", 1=SYSTEMERR, 2=sql err 3=SYSTEMERR." ".sql err
define('EXECDML_SQL', '0');
//define('OCTET-STREAM','application/octet-stream');
$ERR_CRUD=array("SYNTAXERR"=>false,"SYNTAXPRT"=>false,"SYSTEMERR"=>false);
$ERR_THUMB=array("SYNTAXERR"=>false,"SYNTAXPRT"=>false,"SYSTEMERR"=>false);

# INIZIALIZZA COSTANTI LINGUA
$lang = new lang(array('IT', 'EN')); 

# JAVASCRIPT E FLASH 
define('CONFIRM_DEL', "if(confirm('Puoi eliminare solamente le voci con 0 occorrenze. Eliminare?')) return true; else return false;");
define('CONFIRM_DELETE', "if(confirm('Eliminare?')) return true; else return false;");
define('ON_CLICK_DEL_CONF',"onclick=\"if(confirm('Puoi eliminare solamente le voci con 0 occorrenze. Eliminare?')) return true; else return false;\"");
define('LIMITE_PESO_SWF', "2" );

# PATH FILE CARTELLE IMG CSS 
define('LOCAT_HTTP', "Location:http://" );
define('HEADER_TO',LOCAT_HTTP.url::urlunix(HTTP_HOST.DIRNAME."/"));

define('CSS', SYSTEM_PATH.'css/');
define('HTTP_LEVEL', "http://".url::urlunix(HTTP_HOST.DIRNAME."/"));
define('LOGOUT',"index.php");
define('IMG_AR',url::urlunix(LIBRARY.'img_ar/'));
define('LOGIN', SYSTEM_PATH.'login/');

# HTML_AR
define('HTML_AR', LIBRARY.'html_ar/');
define('CSS_AR', LIBRARY.'css/');
define('HEAD_AR', HTML_AR.'head.php');
define('FOOTER_AR', HTML_AR.'footer.php');
define('BLOCCHI_AR', HTML_AR.'blocchi/');
define('CONFIG_AR', HTML_AR.'config/');

# HTML
define('HTML', SYSTEM_PATH.'html/');
define('MAIN', HTML.'main/');
define('HEAD', MAIN.'head.php');
define('FOOTER', MAIN.'footer.php');

define('CONTROLS', SYSTEM_PATH.'controls/');
define('BLOCKS', HTML.'blocks/');
define('PAGES', HTML.'pages/');
define('PROCS', HTML.'procs/');
define('FORMS', HTML.'forms/');
define('CONFIG', HTML.'config/');

define('THU_JOLLY',IMG_AR.'thumb.jpg');
define('IMG_LAYOUT', SYSTEM_PATH.'images/');
define('ALT_IMG', IMG_LAYOUT.'icon_no_foto.png');
define('ABS_PATH_THU', "http://".url::urlunix(HTTP_HOST."/".SUBDIR_TESTING."upld/avv/web/"));

# PATH DEDICATI
define('P_INDEX', PAGES.'index/');
define('AJAX', 'ajax/');

# ICONE
define('ICO_ALERT', '<img src="'.IMG_LAYOUT.'system-error-alt.png" alt="Alert" width="20" height="20" />');

# CARTELLE UPLOAD FILE
define('UPLD_MAIN', SYSTEM_PATH.'upld/');
define('IMG_MAIN_WEB', UPLD_MAIN.'avv/web/');	# IMG AVVISI
define('IMG_MAIN_BIG', UPLD_MAIN.'avv/big/');
define('IMG_ALB_THU', UPLD_MAIN.'img/thu/');	# IMG ALBUMS
define('IMG_ALB_WEB', UPLD_MAIN.'img/web/');
define('IMG_ALB_BIG', UPLD_MAIN.'img/big/');
define('IMG_ALB_SQR', UPLD_MAIN.'img/sqr/');
define('IMG_ALB_LND', UPLD_MAIN.'img/lnd/');
define('PATH_FILE', UPLD_MAIN.'atc/');			# ALLEGATI
define('IMG_NEWSLETTER_MAIN', UPLD_MAIN.'newsletter/big/');
define('IMG_NEWSLETTER_THU', UPLD_MAIN.'newsletter/thu/');
define('ABS_PATH_NEWSLETTER_MAIN',ABS_PATH.'upld/newsletter/big/');
define('PATH_PORTFOLIO_THU', IMG_LAYOUT.'portfolio/thu/');			# ALLEGATI

# CARTELLE UPLOADS UTENTI
define('FLD_MAIN', SYSTEM_PATH.'annunci/');
define('FLD_AZIENDE', SYSTEM_PATH.'azienda/');
define('FLD_LOGHI', FLD_AZIENDE.'logo/');
define('FLD_BANNER', FLD_AZIENDE.'banner/');

# NOME CARTELLA DAL NOME AZIENDA
define('FLD_TMP', FLD_MAIN.'tmp/');

$aFolderSchema = array('atc', 'main', 'big', 'web', 'thu'); 

define('LIMITE_FOTO', '30');
define('LIMITE_FOTO_ACCOUNT', '25');

# PATH JAVASCRIPT
define('JS_MAIN', SYSTEM_PATH.'js/');
define('JS_SWF', JS_MAIN.'swf/');
define('JS_TINYMCE', JS_MAIN.'tinymce/');
define('JS_MULTIBOX', JS_MAIN.'multibox/');
define('JS_SLIDEITMOO', JS_MAIN.'slideitmoo/');
define('JS_IMAGE_VIEWER', JS_MAIN.'image_viewer/');
define('JS_IMAGE_SLIDER', JS_MAIN.'image_slider/');
define('JS_MOOTOOLS',JS_MAIN.'mootools/');
define('JS_CUFON',JS_MAIN.'cufon/');
define('JS_MENUMATIC',JS_MAIN.'menumatic/');
define('JS_ACCORDION',JS_MAIN.'accordion/');

define('JS_JQUERY',JS_MAIN.'jquery/');

# TAGS
define('BR', "<br />\n");
define('META_KEYWORDS', 'Annunci auto, moto, affitto case, vendita case, annunci imbarcazioni, cerco e offro lavoro');

# GRUPPI # L'UPLOADER USER � UN MHCU OPPURE UN HCU O UN HHU SCELTO DALL'MHCU
$aA1 = array('ADMIN');
$aA2 = array('ADMIN', 'FM');
$aA3 = array('ADMIN', 'FM', 'MHCU');
$aA4 = array('ADMIN', 'FM', 'MHCU', 'HCU');
$aA5 = array('ADMIN', 'FM', 'MHCU', 'HCU', 'HHU');

# INIZIALIZZAZIONE VARIABILI
$html = array();
$backUri = array();
$initialize = '';
$body_ini = ' onload="initialize()"';
# INIZIALIZZAZIONE VARIABILI SEZIONE PUBBLICA
if(strpos(PHP_SELF,'/login') === false){
	list($id)=request::get(array('id'=>NULL));
}
$MYFILE = new myfile(array());
$MYFILE -> set_meta_from_db();

$MYFILE -> add_css(CSS.'style.css');
//$MYFILE -> add_css(JS_JQUERY.'css/ui-lightness/jquery-ui-1.8.10.custom.css');
$MYFILE -> add_css(JS_JQUERY.'css/custom-theme/jquery-ui-1.8.11.custom.css');
$MYFILE -> add_css(CSS.'jquery.alerts.css');
$MYFILE -> add_css(CSS.'jquery.loady.css');

$MYFILE -> add_css(JS_JQUERY.'css/jqueryslidemenu.css');
$MYFILE -> add_js('<script type="text/javascript" src="'.JS_JQUERY.'jquery-1.7.min.js"></script>', 'file', 'head');
$MYFILE -> add_js('<script type="text/javascript" src="'.JS_JQUERY.'jquery-ui-1.8.10.custom.min.js"></script>', 'file', 'head');
$MYFILE -> add_js('<script type="text/javascript" src="'.JS_JQUERY.'jqueryslidemenu.js"></script>', 'file', 'head');
$MYFILE -> add_js('<script type="text/javascript" src="'.JS_JQUERY.'jquery.form.js"></script>', 'file', 'head');
$MYFILE -> add_js('<script type="text/javascript" src="'.JS_JQUERY.'jquery.alerts.js"></script>', 'file', 'head');
$MYFILE -> add_js('<script type="text/javascript" src="'.JS_JQUERY.'jquery.loady.min.js"></script>', 'file', 'head');
$MYFILE -> add_js('<script type="text/javascript" src="'.JS_JQUERY.'jquery.ui.dialog.js"></script>', 'file', 'head');
$MYFILE -> add_js('<script type="text/javascript" src="'.JS_MAIN.'maps/functions.js"></script>', 'file', 'head');
putenv('GDFONTPATH=' . realpath('.'));
?>
