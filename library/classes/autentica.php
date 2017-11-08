<?php
class autentica{
function __construct($aGroups){
	$this -> autentica = false;
	$this -> g = '';
	$this -> idg = 0;
	$this -> username = '';
	$this -> password = '';
	$this -> ck_secondi = 86400; # 24ORE
	$this -> aUser = array();
	$this -> groups_admitted = $aGroups;
	$this -> get_usr_psw();
	$this -> login_page = 'index.php';
	$this -> err = array();
	$q = Q_AUTENTICA;
	$q = str_replace('<!-- USERNAME -->', $this -> username, $q);
	$q = str_replace('<!-- PASSWORD -->', $this -> password, $q);
	$this->query = $q;
	
	$this -> ack = array();
	$this -> err = array();
	$this -> aUser = rs::rec2arr($this->query);
}

function logout(){
	if(array_key_exists('action', $_GET) && $_GET['action'] == 'logout'){
		$this -> autentica = false;
		$_COOKIE['username'] = NULL;
		$_COOKIE['password'] = NULL;
		unset($_COOKIE['username']);
		unset($_COOKIE['password']);
		setcookie('username', '',time());
		setcookie('password', '',time());
		$messaggio_logout = PERR_LOGOUT;
		if(!empty($this -> aUser['NAME'])){
			$messaggio_logout = str_replace('NOME', $this -> aUser['NAME'], PERR_LOGOUT_NAME);
		}
		unset($_GET['action']);
		io::headto(FILENAME.'.php', array_merge($_GET, array('ack' => $messaggio_logout)));
	}
}

function logout2file($file){
	$ret = false;
	if(array_key_exists('action', $_GET) && $_GET['action'] == 'logout'){
		$this -> autentica = false;
		$_COOKIE['username'] = NULL;
		$_COOKIE['password'] = NULL;
		unset($_COOKIE['username']);
		unset($_COOKIE['password']);
		setcookie('username', '',time());
		setcookie('password', '',time());
		$messaggio_logout = PERR_LOGOUT;
		if(!empty($this -> aUser['NAME'])){
			$messaggio_logout = str_replace('NOME', $this -> aUser['NAME'], PERR_LOGOUT_NAME);
		}
		unset($_GET['action']);
		io::headto($file, array());
		$ret = true;
	}
	return $ret;
}

function get_usr_psw(){
	if(isset($_POST['username']) || isset($_POST['password'])){
		$this->username=isset($_POST['username']) ? $_POST['username'] : "";
		$this->password=isset($_POST['password']) ? md5($_POST['password']) : "";}
	elseif(isset($_COOKIE['username']) || isset($_COOKIE['password'])){
		$this->username=isset($_COOKIE['username']) ? stringa::decifra($_COOKIE['username'],KEYCIFRA) : "";
		$this->password=isset($_COOKIE['password']) ? stringa::decifra($_COOKIE['password'],KEYCIFRA) : "";}
}

function header_no_login($message){
	io::headto($this -> login_page, array('err' => $message));
}

function header_msg($to, $ack, $err){
	$url = $to.'?'.err::geturl($ack,$err);
	header(HEADER_TO.$url);
}

function cookieStandard($secondi){
	setcookie('username','',time()+$secondi);
	setcookie('password','',time()+$secondi);
}

function set_cookie(){
	setcookie('username',stringa::cifra($this->username,KEYCIFRA),time() + $this->ck_secondi);
	setcookie('password',stringa::cifra($this->password,KEYCIFRA),time() + $this->ck_secondi);
}

function set_normal_cookie($fino_a){
	if($fino_a){ # COOKIE A SCADENZA
		// echo 'persist!';
		setcookie('username',stringa::cifra($this->username,KEYCIFRA),time() + $this->ck_secondi);
		setcookie('password',stringa::cifra($this->password,KEYCIFRA),time() + $this->ck_secondi);
	}
	else{ # COOKIE ELIMINATO ALLA CHIUSURA DEL BROWSER
		setcookie('username',stringa::cifra($this->username,KEYCIFRA));
		setcookie('password',stringa::cifra($this->password,KEYCIFRA));
	}
}

# POLITICHE AUTENTICAZIONE
function login_standard(){ # CONTROLLO STANDARD DELLE POLICIES: USERNAME, PASSWORD, GRUPPO, ABILITAZIONI VARIE
	if(empty($this->aUser['USER']) || empty($this->aUser['PASSWORD']) || $this->username!=$this->aUser['USER'] || $this->password!=$this->aUser['PASSWORD']){$this->header_no_login(PERR_LOGIN); 	print 'username o password errati'; }
	else if(!in_array($this -> aUser['GRUPPI'],$this->groups_admitted)) { $this->header_no_login(PERR_NOT_ALLOWED); print 'gruppo respinto';}
	else{ 
		$this -> set_cookie();
		$this -> autentica = true;
		$this -> g = $this -> aUser['GRUPPI'];
		$this -> idg = $this -> aUser['ID_GRUPPI'];
	}
}

function login_no_redirect($redirect){ # CONTROLLO STANDARD DELLE POLICIES: USERNAME, PASSWORD, GRUPPO, ABILITAZIONI VARIE
	$is_persist = false;
	if(array_key_exists('persist', $_POST)){
		$this -> ck_secondi = 7776000; # 86400 * 90 giorni
		$is_persist = true;
	}
	if(empty($this->aUser['USER']) || empty($this->aUser['PASSWORD']) || $this->username!=$this->aUser['USER'] || $this->password!=$this->aUser['PASSWORD']){
		$this -> err[] = PERR_LOGIN;
	}
	elseif(!in_array($this->aUser['GRUPPI'],$this->groups_admitted)){
		$this -> err[] = PERR_GROUP;
	} else {
		if(empty($_COOKIE['username'])){
			$this -> set_normal_cookie($is_persist); 
			$messaggio_benvenuto = PERR_WELLCOME.' '.NOME_SITO;
			/* if(!empty($this -> aUser['NAME'])){
				$messaggio_benvenuto = $this -> aUser['NAME'].', '.PERR_WELLCOME.' '.NOME_SITO;
			} */
			$this -> autentica = true;
			define('ID_USER_INI' , $this -> aUser['ID_USER']);
			$this -> ack[] = $messaggio_benvenuto;
			io::headto(FILENAME.'.php', array_merge($_POST, array('ack' => $messaggio_benvenuto, 'first_login' => 'true')));
		}
		$this -> autentica = true;
		$this -> g = $this -> aUser['GRUPPI'];
		$this -> idg = $this -> aUser['ID_GRUPPI'];

		define('ID_USER_INI' , $this -> aUser['ID_USER']);
	}
	if($redirect && count($this -> err) >0){
		$this -> header_msg($redirect, $this -> ack, $this -> err);
	}
	if(empty($_POST['login'])) $this -> err = array(); # RESETTO I MESSAGGI SE NON SONO NON HO FATTO RICHIESTA DI LOGIN
	$this -> logout();
}

function login_auto($file){ # CONTROLLO STANDARD DELLE POLICIES: USERNAME, PASSWORD, GRUPPO, ABILITAZIONI VARIE
	$is_logout = $this -> logout2file('index.php');
	if($is_logout === false){
		$is_persist = false; 
		$this -> autentica = false;
		if(array_key_exists('persist', $_REQUEST) && $_REQUEST['persist'] == '1'){
			$is_persist = true;
			$this -> ck_secondi = 7776000; # 86400 * 90 giorni
		}
		# NO LOGIN
		if(empty($this->aUser['USER']) || empty($this->aUser['PASSWORD']) || $this->username!=$this->aUser['USER'] || $this->password!=$this->aUser['PASSWORD']){
			$this -> err[] = PERR_LOGIN;
		}
		elseif(!in_array($this->aUser['GRUPPI'],$this->groups_admitted)){
			$this -> err[] = PERR_GROUP;
		}
		else{ # SI LOGIN
			if(empty($_COOKIE['username'])){
				$messaggio_benvenuto = 'Ciao, benvenuto su '.NOME_SITO;
				if(!empty($this -> aUser['NAME'])){
					$messaggio_benvenuto = 'Ciao '.$this -> aUser['NAME'].', benvenuto su '.NOME_SITO;
				}
			}
			define('ID_USER_INI' , $this -> aUser['ID_USER']);
			$this -> autentica = true;
		}
		if(count($this -> err) > 0 && !empty($_POST['login'])){ # ERRORE DI AUTENTICAIONE
			$this -> header_msg('index.php', $this -> ack, $this -> err);
		}
		else{
			if(array_key_exists('login', $_POST)){ # CREO IL COOKIE SOLO SE è STATO PREMUTO IL PULSANTE DI LOGIN
				$this -> set_normal_cookie($is_persist); 
			}
			if(FILENAME == 'index' && $this -> autentica == true){ # REDIRECT PAGINA INTERNA
				io::headto($file, array());
			}
		}
		if(empty($_POST['login'])) $this -> err = array(); # RESETTO I MESSAGGI SE NON SONO NON HO FATTO RICHIESTA DI LOGIN
	}
}

function login_gruppo($aGroup){ # CONTROLLO STANDARD DELLE POLICIES: USERNAME, PASSWORD, GRUPPO, ABILITAZIONI VARIE
	if(empty($this->aUser['USERNAME']) || empty($this->aUser['PASSWORD']) || $this->username!=$this->aUser['USERNAME'] || $this->password!=$this->aUser['PASSWORD'])  $this->header_no_login(NO_LOGIN);
	else if(!in_array($this->aUser['GRUPPI'],$this->groups_admitted)) { $this->header_no_login(NO_GRUPPO); print 'gruppo respinto'; }
	else $this->set_cookie();
}

public function ck_group($a){
	return in_array($this -> g, $a);
}

public function add_log(){
	if( $this -> autentica ){ // accesso riuscito
		$qInsertLog = "INSERT INTO users_logs (id_user, ip, success) VALUES ('".ID_USER_INI."', INET_ATON('".$_SERVER["REMOTE_ADDR"]."'), '1' )";
	} else { // accesso fallito
		$qInsertLog = "INSERT INTO users_logs (id_user, ip, success) VALUES ('0', INET_ATON('".$_SERVER["REMOTE_ADDR"]."'), '0' )";
	}
	$qInsertLog ;
	mysql_query($qInsertLog);
	
}

}
?>