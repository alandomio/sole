<?php
$scheda = new nw('');
$scheda -> offset = 10;

# VARIABILI DI DEFAULT
list($icursor,$tabella,$id) = request::get(array("icursor"=>0,"tabella"=>$scheda->table,"id"=>NULL ));
$crud = empty($id) ? 'ins' : 'upd';

$scheda -> ext_table(array('hcompanys'));

# CONFIGURAZIONE NW
$aStripExt = array();
if($MYFILE->file != 'buildings_address.php')$aStripExt = array('LOCALITY', 'LAT_BLD', 'LNG_BLD', 'ADDRESS_BLD', 'K1_ID_STATI', 'K1_ID_REGIONI', 'K1_ID_PROVINCE', 'K1_ID_COMUNI');
foreach($scheda -> ext_table as $k => $v){
	$aStripExt = array_merge($aStripExt, rs::get_fields($k));
	$aStripExt = arr::unset_vals($aStripExt, array(stringa::tbl2id($k)));
}

$aStripC = array('ID_FILE', 'N_FLATS', stringa::tbl2id($scheda -> table));

# LISTA CAMPI DI RICERCA / ORDINAMENTO
$aShowList = array('ID_FILE', 'CODE_BLD', 'CODE_HC', 'YEAR_BLD', 'IS_POWERHOUSE');


$aShowCrud = array_diff($scheda -> aFields, $aStripC, $aStripExt);
//print_r($aShowCrud);
if($user -> idg == 3){
	$aShowCrud = arr::unset_vals($aShowCrud, array('ID_HCOMPANY','IS_HIDE'));
}

# MOLTI A MOLTI
$scheda -> many_to_many(array('buildings_users' => array(
								'id' 	=> 'ID_USER',
								'title' => 'USER',
								'ext'	=> 'users',
								'where'	=> "ID_BUILDING",
								'lbl'	=> "Utenti",
								'file'	=> "buildings_users_ext.php"
						)
					)
				);

$scheda->many_to_many_tot($id);

# CONFIGURAZIONE IMMAGINI (SE USATE)
$scheda -> files = array('images');
$scheda -> personal_folders = false;
$scheda -> thumb_size = 180; 
$scheda -> thumb_format = 'not square';
$thus = 180;
$imgs = 576;
$sqrs = 80;

# CONFIGURAZIONE FILTRI
$my_vars = new ordinamento(array('bl' => 'BUILDING', 'hc' => 'CODE_HC', 'yb' => 'YEAR_BLD', 'cd' => 'CODE_BLD', 'ip'=>'IS_POWERHOUSE'));
$my_vars -> campi_force['text']['HCOMPANY'] = 'buildings';
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

$q_ext = "SELECT * FROM buildings_users WHERE ID_BUILDING = '$id'";
$a_ext = rs::inMatrix($q_ext);
$users_ids = arr::semplifica($a_ext, 'ID_USER');
$tot_ext = count($users_ids);

$sub_nav[CHART] = array($scheda -> file_c);

if($user -> idg <= 3){
	$sub_nav[ADDRESS] = array('buildings_address.php');
	$sub_nav['Upload users ('.$tot_ext.')'] = array($scheda->many_to_many['buildings_users']['file']);
	$sub_nav[COEFFICIENTS] = array('buildings_conversions.php');

	if(!empty($scheda -> files)) $sub_nav[PICTURES] = array($scheda->file_f);
}
?>