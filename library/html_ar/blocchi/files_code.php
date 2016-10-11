<?php
# V.0.1.10
$is_swf=0; $is_head_mce=0; $is_multibox=0; $is_js=1;
$aCurs=array("icursor"=>$icursor);
$backUri=array_merge($aCurs,$my_vars->href);
$backUri['id_rec'] = $id_rec;
$title_pag = "Gestione files ".$scheda->etichetta;

if(array_key_exists('subdel',$_POST)){
$cnt = 0;
	foreach($my_vars->ck as $id_del){
		if(!empty($id_del)){
			$q = "SELECT * FROM files WHERE ID_FILE = '$id_del'";
			$rec = rs::rec2arr($q);
			$file = $rec['PATH'];
			
			if($rec['TYPE'] == 'i'){ // ELIMINO LE IMMAGINI
				if(is_file($scheda->img_alb_thu.$file)) unlink($scheda->img_alb_thu.$file);
				if(is_file($scheda->img_alb_web.$file)) unlink($scheda->img_alb_web.$file);
				if(is_file($scheda->img_alb_big.$file)) unlink($scheda->img_alb_big.$file);
			}
			elseif($rec['TYPE'] == 'f'){ // ELIMINO I FILE
				if(is_file($scheda->file_atc.$file)) unlink($scheda->file_atc.$file);
			}
			# AGGIORNAMENTO DB
			mysql_query("DELETE FROM files WHERE ID_FILE = '$id_del'");
			mysql_query("DELETE FROM ".$scheda->file_table." WHERE ID_FILE = '$id_del'");
			
		}
	$cnt++;
	}
	$ack_msg =  $cnt>0 ? $cnt.' '.FILE_DELETED : '';
	header(HEADER_TO.url::uri($scheda->file_f,array_merge($backUri,array('ack'=>$ack_msg))));
}

if(array_key_exists('subsave',$_POST)){
	$cnt = 0;
	foreach($my_vars->tx as $k => $val){
		$file_rec = rs::rec2arr("SELECT TITLE FROM files WHERE ID_FILE = '$k'");
		if($file_rec['TITLE']!=$val){
			mysql_query("UPDATE files SET TITLE = '".prepare4sql($val)."' WHERE ID_FILE = '$k'");
			$cnt++;
		}
	}
	header(HEADER_TO.url::uri($scheda->file_f,$backUri));
}

$aFromp=array();
$aFiltro=array();
$qTotRec="SELECT * FROM ".$scheda->table;
if($scheda->ext_table != NULL) {
	$qTotRec.="";
}
?>