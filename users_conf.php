<?php
# V.0.1.8
$scheda = new nw('users');

# VARIABILI DI DEFAULT

list($icursor,$tabella,$id_rec)=request::get(array("icursor"=>0,"tabella"=>$scheda->table, "id_rec" => NULL));

$crud = empty($id_rec) ? 'ins' : 'upd';

# CONFIGURAZIONE NW
$scheda -> ext_table(array('gruppis', 'hcompanys'));

$aStripExt = array('TITLE', 'CODE',
'ID_FEDERATION', 'CODE_HC', 'PATH_HCOMPANY', 'DESCRIP_HC_IT', 'DESCRIP_HC_EN', 'NAME_HC', 'ADDRESS_HC', 'REFERENCE_HC', 'EXT_ID_HCOMPANY'
); 

// campi tabelle esterne o interne da rimuovere in tutte le pagine
$aStripList= array_merge($scheda->astrip, $aStripExt, array('TS', 'PASSWORD'));
$aStripC = array_merge($scheda->astrip, $aStripExt, array('ID_FILE'));

# CONFIGURAZIONE SALVATAGGIO IMMAGINI
$scheda -> files = array();
$scheda -> img = false;
$scheda -> personal_folders = false;
$scheda -> thumb_size = 200; // lato lungo immagine principale
$scheda -> thumb_format = 'not square';
$scheda -> elimina_originale = false;
$thus = 70;		// lato lungo anteprime album
$imgs = 576;	// lato lungo immagine web album$thus = 110;
$sqrs = 92;		// lato immagina quadrata

# CONFIGURAZIONE FILTRI
$my_vars = new ordinamento(array(
'gp'=>'GRUPPI', 
'us'=>'USER', 
'iab'=>'IS_ABIL', 
'ic' => 'IS_CONFIRMED', 
'ip' => 'IS_PRIVACY', 
'iu' => 'IS_UPLOADER',
'hc' => 'ID_HCOMPANY',
'nm' => 'NAME', 
'sn' => 'SURNAME', 
'bld' => 'ID_BUILDING'));

$my_vars->campi_force['text']['USER'] = 'users';  //FORZO IL FORMATO DEL CAMPO (FIELDNAME) A SELECT (select) PASSANDO IL NOME TABELLA (tablename) RELATIVO
$my_vars->sort_default($scheda->f_id);
$my_vars->tabella = $scheda->table;

# MENU, TITLE, ETICHETTE ECC...
$rec_scheda = rs::rec2arr("SELECT * FROM ".$scheda->table." WHERE ".$scheda->f_id."='$id_rec'");
$scheda->etichetta = 'utenti'; 

$etichetta = $rec_scheda['USER'].' - '.INSERT_ON.': '.dtime::my2iso($rec_scheda['TS']);

# LIMITE FOTO PER UPLOAD
$sub_nav['Scheda'] = array($scheda->file_c);
?>