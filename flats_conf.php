<?php
$scheda = new nw('');

# VARIABILI DI DEFAULT
list($icursor,$tabella,$id, $def_build) = request::get(array("icursor"=>0,"tabella"=>$scheda->table,"id"=>NULL, 'def_build' => NULL));
$crud = empty($id) ? 'ins' : 'upd';

$scheda -> ext_table(array('users', 'buildings'));
//$scheda -> descriptors('descriptors', $aDescriptors);

# CONFIGURAZIONE NW
$aStripExt = array();
foreach($scheda -> ext_table as $k => $v){
	$aStripExt = array_merge($aStripExt, rs::get_fields($k));
	unset($aStripExt[stringa::tbl2id($v)]);
}

$aStripC = array(stringa::tbl2id($scheda -> table));

# LISTA CAMPI DI RICERCA / ORDINAMENTO
$aShowList = array('CODE_FLAT', 'ACTIVATION_CODE', 'NETAREA', 'USER');
$aShowCrud = array_diff($scheda -> aFields, $aStripC, $aStripExt);
//print_r($aShowCrud);

# CONFIGURAZIONE IMMAGINI (SE USATE)
$scheda -> files = array();
$scheda -> personal_folders = false;
$scheda -> thumb_size = 180; 
$scheda -> thumb_format = 'not square';
$thus = 180;
$imgs = 576;
$sqrs = 80;

# CONFIGURAZIONE FILTRI
$my_vars = new ordinamento(array('ft' => 'CODE_FLAT', 'ac' => 'ACTIVATION_CODE', 'na' => 'NETAREA', 'iu' => 'USER', 'bld' => 'ID_BUILDING'));
//$my_vars -> campi_force['text']['HCOMPANY'] = 'flats';
$my_vars -> campi_force['text']['USER'] = 'flats';

$my_vars->sort_default($scheda->f_id);
$my_vars->tabella = $scheda->table;

# PRE CARICO I DATI DEL RECORD CORRENTE IN MODO DA USARLI DOVE SERVE
$rec_scheda = rs::rec2arr("SELECT * FROM ".$scheda->table." WHERE ".$scheda->f_id."='$id' LIMIT 0,1");

$aOrd = array();
# ETICHETTA DA MOSTRARE COME EVIDENZA DEL RECORD
$etichetta = $rec_scheda[$aShowList[0]].' - '.ACTIVATION_CODE.': '.$rec_scheda['ACTIVATION_CODE'];

# LIMITE FOTO PER UPLOAD
if(!empty($scheda -> files)){
	$totali = $scheda -> cnt_files($id);
	$swf_limite = LIMITE_FOTO-$totali['i'] <= 0 ? 0 : LIMITE_FOTO-$totali['i'];
}

$q_ext = "SELECT * FROM flats_meters WHERE ID_FLAT = '$id'";
$a_ext = rs::inMatrix($q_ext);
$meters_ids = arr::semplifica($a_ext, 'ID_METER');
$tot_ext = count($meters_ids);


$sub_nav['Appartamenti'] = array($scheda -> file_c);
//$sub_nav['Contatori ('.$tot_ext.')'] = array($scheda->many_to_many['flats_meters']['file']);
if(!empty($scheda -> files)) $sub_nav['Allegati'] = array($scheda->file_f);
?>