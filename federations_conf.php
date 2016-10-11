<?php
$scheda = new nw('');

# VARIABILI DI DEFAULT
list($icursor,$tabella,$id) = request::get(array("icursor"=>0,"tabella"=>$scheda->table,"id"=>NULL));
$crud = empty($id) ? 'ins' : 'upd';
//$backUri['id'] = $id;

$scheda -> ext_table(array('users'));

# STE DUE RIGHE SERVONO??
$aDescriptors = array('K0_ID_STATI','K0_ID_REGIONI','K0_ID_PROVINCE','K0_ID_COMUNI'); 
$scheda -> descriptors('descriptors', $aDescriptors);

# CONFIGURAZIONE NW
//$aStripExt =	array(); // campi tabelle esterne o interne da rimuovere in tutte le pagine

$aStripC = array('ID_FILE', 'LAT_FED', 'LNG_FED', 'PASSWORD', 'NAME', 'SURNAME','ID_GRUPPI','IS_PRIVACY','IS_CONFIRMED','IS_ABIL','IS_UPLOADER','CODE','TS', 'ID_HCOMPANY', stringa::tbl2id($scheda -> table));



# LISTA CAMPI DI RICERCA / ORDINAMENTO
$aShowList = array('PATH_FEDERATION', 'FEDERATION','USER');
$aShowCrud = array_diff($scheda -> aFields, $aStripC);

# CONFIGURAZIONE IMMAGINI (SE USATE)
$scheda -> files = array();
$scheda -> personal_folders = false;
$scheda -> thumb_size = 180; 
$scheda -> thumb_format = 'not square';
$thus = 180;
$imgs = 576;
$sqrs = 160;

# CONFIGURAZIONE FILTRI

$my_vars = new ordinamento(array('nf' => 'FEDERATION', 'iu' => 'USER'));

if($user -> aUser['ID_GRUPPI'] == '2'){ # FACCIO VEDERE LE FEDERAZIONI SOLAMENTE AL RELATIVO FM
	$aStripC[] = 'ID_USER';
	$my_vars -> set_filtro(array('vaiu' => $user -> aUser['ID_USER']));
	if($MYFILE -> filename == 'federations'){
		
		$q = "SELECT ID_FEDERATION FROM federations WHERE ID_USER = '".$user -> aUser['ID_USER']."' LIMIT 1";
		$r = rs::rec2arr($q);
		if(!empty($r['ID_FEDERATION'])){ # REDIRECT ALLA PAGINA DI MODIFICA
			io::headto('federations_c.php', array('id' => $r['ID_FEDERATION'], 'crud' => 'upd'));
		}
		else{ # NON CI SONO FM ASSEGNATI ALLA FEDERAZIONE, QUINDI REDIRECT IN HOMEPAGE
			io::headto('index.php', array('ack' => FM_WITHOUT_FEDERATION));
		}
	
		
	}
}

$my_vars -> campi_force['text']['FEDERATION'] = 'federations';  # CAMBIO IL FORMATO DI UN CAMPO RICERCA campi_force['select','text']['FIELDNAME'] = 'tablename'
$my_vars -> sort_default($scheda->f_id);
$my_vars -> tabella = $scheda->table;

$rec_scheda = rs::rec2arr("SELECT * FROM ".$scheda->table." WHERE ".$scheda->f_id."='$id'");

$aOrd = array();
# ETICHETTA DA MOSTRARE COME EVIDENZA DEL RECORD
$etichetta = $rec_scheda[$aShowList[1]];

# LIMITE FOTO PER UPLOAD
if(!empty($scheda -> files)){
	$totali = $scheda -> cnt_files($id);
	$swf_limite = LIMITE_FOTO-$totali['i'] <= 0 ? 0 : LIMITE_FOTO-$totali['i'];
}

$sub_nav[CHART] = array($scheda -> file_c);
$sub_nav[ADDRESS] = array('federations_address.php');
$sub_nav[CONVERSION_TABLE] = array('federations_conversions.php');
if(!empty($scheda -> files)) $sub_nav['Allegati'] = array($scheda->file_f);
?>