<?php
$scheda = new nw('');

# VARIABILI DI DEFAULT
$def_fed = NULL;
if($user -> idg == 2){
	$def_fed = $user -> aUser['ID_FEDERATION'];
}

list($icursor,$tabella,$id, $slnf) = request::get(array("icursor"=>0,"tabella"=>$scheda->table,"id"=>NULL, 'slnf' => $def_fed));
$crud = empty($id) ? 'ins' : 'upd';

$scheda -> ext_table(array('federations', 'users'));
//$scheda -> descriptors('descriptors', $aDescriptors);

# CONFIGURAZIONE NW
$aStripExt = array();
foreach($scheda -> ext_table as $k => $v){
	$aStripExt = array_merge($aStripExt, rs::get_fields($k));
	$aStripExt = arr::unset_vals($aStripExt, array(stringa::tbl2id($k)));
}

$aStripC = array('ID_FILE', stringa::tbl2id($scheda -> table));

if($user -> idg == 3){
	$aShowList = array('PATH_HCOMPANY', 'CODE_HC', 'FEDERATION');
	$aShowCrud = array_diff($scheda -> aFields, $aStripC, $aStripExt);
	
	$aShowCrud = arr::unset_vals($aShowCrud, array('ID_FEDERATION', 'ID_USER'));
	
	
	$my_vars = new ordinamento(array('hc' => 'CODE_HC', 'us' => 'USER', 'nf' => 'FEDERATION'));
	$my_vars -> set_filtro(array('slus' => $user -> aUser['ID_USER']));
} else {
	$aShowList = array('PATH_HCOMPANY', 'CODE_HC', 'USER', 'FEDERATION');
	$aShowCrud = array_diff($scheda -> aFields, $aStripC, $aStripExt);
	$my_vars = new ordinamento(array('hc' => 'CODE_HC', 'us' => 'USER', 'nf' => 'FEDERATION'));
}

$my_vars->sort_default($scheda->f_id);
$my_vars->tabella = $scheda->table;



# CONFIGURAZIONE IMMAGINI (SE USATE)
$scheda -> files = array();
$scheda -> personal_folders = false;
$scheda -> thumb_size = 180; 
$scheda -> thumb_format = 'not square';
$thus = 180;
$imgs = 576;
$sqrs = 160;

# PRE CARICO I DATI DEL RECORD CORRENTE IN MODO DA USARLI DOVE SERVE
$rec_scheda = rs::rec2arr("SELECT * FROM ".$scheda->table." WHERE ".$scheda->f_id."='$id' LIMIT 0,1");

$aOrd = array();
# ETICHETTA DA MOSTRARE COME EVIDENZA DEL RECORD
$etichetta = $rec_scheda[$aShowList[1]];

# LIMITE FOTO PER UPLOAD
if(!empty($scheda -> files)){
	$totali = $scheda -> cnt_files($id);
	$swf_limite = LIMITE_FOTO-$totali['i'] <= 0 ? 0 : LIMITE_FOTO-$totali['i'];
}

$sub_nav['Scheda'] = array($scheda -> file_c);
if(!empty($scheda -> files)) $sub_nav['Allegati'] = array($scheda->file_f);
?>