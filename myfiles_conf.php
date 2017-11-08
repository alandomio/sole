<?php
$scheda = new nw('');

# VARIABILI DI DEFAULT
list($icursor,$tabella,$id) = request::get(array("icursor"=>0,"tabella"=>$scheda->table,"id"=>NULL));
$crud = empty($id) ? 'ins' : 'upd';
//$backUri['id'] = $id;

$scheda -> ext_table(array('myfiles_types'));
//$scheda -> descriptors('descriptors', $aDescriptors);

# CONFIGURAZIONE NW
//$aStripExt =	array(); // campi tabelle esterne o interne da rimuovere in tutte le pagine
$aStripC = array('ID_FILE');
$aShowList = array('ID_MYFILE','MYFILES_TYPE','FILE_IT', 'TITLE_IT', 'TITLE_EN');
$aShowCrud = array_diff($scheda -> aFields, $aStripC);

# ALLEGATI
$scheda -> files = array();
$scheda -> personal_folders = false;
$scheda -> thumb_size = 267; 
$scheda -> thumb_format = 'not square';
$thus = 267;
$imgs = 570;
$sqrs = 160;

# CONFIGURAZIONE FILTRI
$aOrd = array();

$my_vars = new ordinamento(array('if' => 'ID_MYFILE', 'ty' => 'MYFILES_TYPE', 'fi' => 'FILE_IT', 'ti' => 'TITLE_IT', 'te' => 'TITLE_EN' ));
$my_vars->sort_default($scheda->f_id);
$my_vars->tabella = $scheda->table;

$rec_scheda = rs::rec2arr("SELECT * FROM ".$scheda->table." WHERE ".$scheda->f_id."='$id'");

//$scheda -> etichetta = 'news'; // 'se non specificata qui, è il nome tabella senza la s finale'
//$etichetta = $rec_scheda[$aShowList[0]];
$etichetta = $rec_scheda[$aShowList[0]];

# LIMITE FOTO PER UPLOAD
if(!empty($scheda -> files)){
	$totali = $scheda -> cnt_files($id);
	$swf_limite = LIMITE_FOTO-$totali['i'] <= 0 ? 0 : LIMITE_FOTO-$totali['i'];
}

$sub_nav['Scheda'] = array($scheda -> file_c);
if(!empty($scheda -> files)) $sub_nav['Allegati'] = array($scheda->file_f);
?>