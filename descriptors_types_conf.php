<?php
# V.0.1.8
$scheda = new nw('descriptors_types');
$scheda -> offset = 100;

# VARIABILI DI DEFAULT
list($icursor,$tabella,$crud,$id)=request::get(array("icursor"=>0,"tabella"=>$scheda->table,"crud"=>NULL,"id"=>NULL));

# CONFIGURAZIONE NW
$scheda->ext_table(array());
$aStripExt =	array('ID_DESCRIPTORS_TYPE_SELF', 'DESCRIPTORS_TABLE', 'IS_SELECT'); // campi tabelle esterne o interne da rimuovere in tutte le pagine
$aStripList=	array_merge($scheda->astrip, $aStripExt, array());
$aStripC   =	array_merge($scheda->astrip, $aStripExt, array('ID_FILE'));

$scheda->files = array();

# CONFIGURAZIONE SALVATAGGIO IMMAGINI
$scheda->personal_folders = false;
$scheda->thumb_size = 180; // lato lungo immagine principale
$scheda->thumb_format = 'not square';
$thus = 70;		// lato lungo anteprime album
$imgs = 576;	// lato lungo immagine web album

# CONFIGURAZIONE FILTRI
$my_vars = new ordinamento(array('dt' => 'DESCRIPTORS_TYPE','is' => 'IS_SELECT'));

$my_vars -> set_filtro(array('vais' => '1', 'sadt' => 'DESCRIPTORS_TYPE'));

//$my_vars->campi_force['text']['FUNC'] = 'funcs';
//$my_vars->campi_force['select']['EMAIL'] = 'users';  //FORZO IL FORMATO DEL CAMPO (FIELDNAME) A SELECT (select) PASSANDO IL NOME TABELLA (tablename) RELATIVO
//$my_vars->sort_default('DESCRIPTORS_TYPE');
$my_vars->tabella = $scheda->table;

# STILE CAMPI RICERCA
//$my_vars->css['TITLE'] = 'trenta';

# MENU, TITLE, ETICHETTE ECC...
//$scheda->action_type = 'read';
$rec_scheda = rs::rec2arr("SELECT * FROM ".$scheda->table." WHERE ".$scheda->f_id."='$id'");

$scheda->etichetta = 'Campi selezione'; // 'se non specificata qui, è il nome tabella senza la s finale'
$etichetta = $rec_scheda['DESCRIPTORS_TYPE'];
//$scheda->set_main_path(YT_WORLD);
//$scheda->set_upld_dirs($rec_scheda['FOLDER_CL']);

# LIMITE FOTO PER UPLOAD
//$totali = $scheda->cnt_files($id_rec);
//$swf_limite = LIMITE_FOTO-$totali['i'] <= 0 ? 0 : LIMITE_FOTO-$totali['i'];

//$sub_nav['SCHEDA'] = array($scheda->file_c);
$sub_nav = array();
?>