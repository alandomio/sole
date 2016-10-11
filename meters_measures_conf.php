<?php
$scheda = new nw('measures');
$scheda -> offset = 10;

# VARIABILI DI DEFAULT
list($icursor,$tabella,$idi, $id) = request::get(array("icursor"=>0,"tabella"=>$scheda->table,"idi"=>NULL, 'id' => NULL));
$crud = empty($idi) ? 'ins' : 'upd';

$qm = "SELECT * FROM meters WHERE ID_METER = '$id'";
$rm = rs::rec2arr($qm);

$scheda -> ext_table(array('uploadtypes'));
//$scheda -> descriptors('descriptors', $aDescriptors);

# AGGIUNGO MISURE SCARPE
//$scheda -> add_mm('usages', $idi);

# CONFIGURAZIONE NW
$aStripExt = array('ID_METER');
foreach($scheda -> ext_table as $k => $v){
	$aStripExt = array_merge($aStripExt, rs::get_fields($k));
	unset($aStripExt[stringa::tbl2id($v)]);
}

$aStripC = array_merge(array('TS'), array(stringa::tbl2id($scheda -> table)));

# LISTA CAMPI DI RICERCA / ORDINAMENTO
$aShowList = array('D_MEASURE','IS_CONFIRMED_MS','ANNO_MS','UPLOADTYPE');
$aShowCrud = array_diff($scheda -> aFields, $aStripC, $aStripExt);

# MOLTI A MOLTI
/*$scheda -> many_to_many(array('flats_meters' => array(
								'id' 	=> 'ID_FLAT',
								'title' => 'REGISTERNUM',
								'ext'	=> 'flats',
								'where'	=> "ID_METER",
								'lbl'	=> "Contatori",
								'file' => 'meters_flats_ext.php'
						)
					)
				);
*/
/*$scheda->many_to_many_tot($id);
*/
# CONFIGURAZIONE IMMAGINI (SE USATE)
$scheda -> files = array();
$scheda -> personal_folders = false;
$scheda -> thumb_size = 180; 
$scheda -> thumb_format = 'not square';
$thus = 180;
$imgs = 576;
$sqrs = 80;

# CONFIGURAZIONE FILTRI
$my_vars = new ordinamento(array('dm' => 'D_MEASURE', 'ic' => 'IS_CONFIRMED_MS', 'ym' => 'ANNO_MS', 'ut' => 'UPLOADTYPE'));
$my_vars -> campi_force['text']['HCOMPANY'] = 'flats';
$my_vars -> campi_force['text']['USER'] = 'flats';

$my_vars->sort_default($scheda->f_id);
$my_vars->tabella = $scheda->table;

# PRE CARICO I DATI DEL RECORD CORRENTE IN MODO DA USARLI DOVE SERVE
$rec_scheda = rs::rec2arr("SELECT * FROM ".$scheda->table." WHERE ".$scheda->f_id."='$idi' LIMIT 0,1");

$aOrd = array();
# ETICHETTA DA MOSTRARE COME EVIDENZA DEL RECORD
$etichetta = $rec_scheda[$aShowList[0]];

# LIMITE FOTO PER UPLOAD
if(!empty($scheda -> files)){
	$totali = $scheda -> cnt_files($idi);
	$swf_limite = LIMITE_FOTO-$totali['i'] <= 0 ? 0 : LIMITE_FOTO-$totali['i'];
}

/*$q_ext = "SELECT * FROM flats_meters WHERE ID_METER = '$idi'";
$a_ext = rs::inMatrix($q_ext);
$meters_ids = arr::semplifica($a_ext, 'ID_METER');
$tot_ext = count($meters_ids);*/

$q_ext = "SELECT * FROM flats_meters WHERE ID_METER = '$id'";
$a_ext = rs::inMatrix($q_ext);
$meters_ids = arr::semplifica($a_ext, 'ID_METER');
$tot_ext = count($meters_ids);

$sub_nav[METERS] = array('meters_c.php');
$sub_nav[ADD.' '.MEASURE] = array('meters_add_measure.php');
$sub_nav[MEASURES] = array('meters_measures.php');
$sub_nav[FLATS.' ('.$tot_ext.')'] = array('meters_flats_ext.php');



/*$sub_nav['Appartamenti ('.$tot_ext.')'] = array($scheda -> many_to_many['flats_meters']['file']);
*/
//$sub_nav['Scheda'] = array($scheda -> file_c);
if(!empty($scheda -> files)) $sub_nav['Allegati'] = array($scheda->file_f);

?>