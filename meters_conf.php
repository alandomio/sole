<?php
$scheda = new nw('');
$scheda -> offset = 10;

# VARIABILI DI DEFAULT
list($icursor,$tabella,$id) = request::get(array("icursor"=>0,"tabella"=>$scheda->table,"id"=>NULL));
$crud = empty($id) ? 'ins' : 'upd';

$scheda -> ext_table(array('meterpropertys', 'outputs', 'supplytypes','rfs'));

# AGGIUNGO CHECKBOX MOLTI A MOLTI
$scheda -> add_mm('usages', $id);

# CONFIGURAZIONE NW
$aStripExt = array();
$aStripC = array_merge(array('ID_SELF_METER'), array(stringa::tbl2id($scheda -> table)));

# LISTA CAMPI DI RICERCA / ORDINAMENTO
$aShowList = array('REGISTERNUM', 'METERPROPERTY', 'IS_DOUBLE', 'OUTPUT');
$aShowCrud = array_diff($scheda -> aFields, $aStripC, $aStripExt);

# MOLTI A MOLTI
$scheda -> many_to_many(array('flats_meters' => array(
								'id' 	=> 'ID_FLAT',
								'title' => 'REGISTERNUM',
								'ext'	=> 'flats',
								'where'	=> "ID_METER",
								'lbl'	=> "Contatori",
								'file' => 'meters_flats_ext.php'
						)
					)
				);

$scheda -> many_to_many_tot($id);

# CONFIGURAZIONE IMMAGINI (SE USATE)
$scheda -> files = array();
$scheda -> personal_folders = false;
$scheda -> thumb_size = 180; 
$scheda -> thumb_format = 'not square';
$thus = 180;
$imgs = 576;
$sqrs = 80;

# CONFIGURAZIONE FILTRI
$my_vars = new ordinamento(array('rn' => 'REGISTERNUM', 'mp' => 'METERPROPERTY', 'do' => 'IS_DOUBLE', 'ou' => 'OUTPUT', 'sp' => 'SUPPLYTYPE'));
$my_vars -> campi_force['text']['HCOMPANY'] = 'flats';
$my_vars -> campi_force['text']['USER'] = 'flats';

$my_vars->sort_default($scheda->f_id);
$my_vars->tabella = $scheda->table;

# PRE CARICO I DATI DEL RECORD CORRENTE IN MODO DA USARLI DOVE SERVE
$rec_scheda = rs::rec2arr("SELECT * FROM ".$scheda->table." WHERE ".$scheda->f_id."='$id' LIMIT 0,1");

$aOrd = array();
# ETICHETTA DA MOSTRARE COME EVIDENZA DEL RECORD
$etichetta = $rec_scheda[$aShowList[0]];

# LIMITE FOTO PER UPLOAD
if(!empty($scheda -> files)){
	$totali = $scheda -> cnt_files($id);
	$swf_limite = LIMITE_FOTO-$totali['i'] <= 0 ? 0 : LIMITE_FOTO-$totali['i'];
}

$q_ext = "SELECT * FROM flats_meters WHERE ID_METER = '$id'";
$a_ext = rs::inMatrix($q_ext);
$meters_ids = arr::semplifica($a_ext, 'ID_METER');
$tot_ext = count($meters_ids);

$sub_nav[METER] = array($scheda -> file_c);
$sub_nav[ADD.' '.MEASURE] = array('meters_add_measure.php');
$sub_nav[MEASURES] = array('meters_measures.php');
$sub_nav[FLATS.' ('.$tot_ext.')'] = array($scheda -> many_to_many['flats_meters']['file']);

//$sub_nav['Scheda'] = array($scheda -> file_c);
if(!empty($scheda -> files)) $sub_nav['Allegati'] = array($scheda->file_f);
?>