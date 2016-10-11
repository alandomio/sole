<?php
$scheda = new nw('');

# VARIABILI DI DEFAULT
list($icursor,$tabella,$id) = request::get(array("icursor"=>0,"tabella"=>$scheda->table,"id"=>NULL));
$crud = empty($id) ? 'ins' : 'upd';

$scheda -> ext_table(array());
//$scheda -> descriptors('descriptors', $aDescriptors);

# CONFIGURAZIONE NW
$aStripExt = array();
foreach($scheda -> ext_table as $k => $v){
	$aStripExt = array_merge($aStripExt, rs::get_fields($k));
	$aStripExt = arr::unset_vals($aStripExt, array(stringa::tbl2id($k)));
}

/*$aStripExt = array('PATH_FEDERATION','DESCRIP_FED','ADDRESS_FED','K0_ID_STATI','K0_ID_REGIONI','K0_ID_PROVINCE','K0_ID_COMUNI','PASSWORD','NAME','SURNAME','ID_GRUPPI','IS_PRIVACY','IS_CONFIRMED','IS_ABIL','IS_UPLOADER','CODE','TS');
*/

$aStripC = array('ID_FILE', stringa::tbl2id($scheda -> table));

# LISTA CAMPI DI RICERCA / ORDINAMENTO
$aShowList = array('METERTYPE_'.LANG_DEF, 'UNIT', 'STYPE');
$aShowCrud = array_diff($scheda -> aFields, $aStripC, $aStripExt);

# CONFIGURAZIONE IMMAGINI (SE USATE)
$scheda -> files = array();
$scheda -> personal_folders = false;
$scheda -> thumb_size = 180; 
$scheda -> thumb_format = 'not square';
$thus = 180;
$imgs = 576;
$sqrs = 160;

# CONFIGURAZIONE FILTRI
$my_vars = new ordinamento(array('mt' => 'METERTYPE_'.LANG_DEF, 'ut' => 'UNIT', 'tp' => 'STYPE'));
$my_vars -> set_filtro(array('vaiu' => $user -> aUser['ID_USER']));

$my_vars->sort_default($scheda->f_id);
$my_vars->tabella = $scheda->table;

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