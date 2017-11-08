<?php
class blocks{

function lista_misurazioni($id_meter, $json){
	$ret = array();
	$sk_msl = new nw('measures');
	$sk_msl -> offset = 10000;
	
	$sk_msl -> ext_table(array());
	
	// CONFIGURAZIONE NW
	$aStripExt = array('ID_METER');
	$aStripC = array_merge(array('TS'), array(stringa::tbl2id($sk_msl -> table)));
	
	// LISTA CAMPI DI RICERCA / ORDINAMENTO
	$aShowList = array('D_MEASURE', 'F1', 'F2', 'F3', 'O1', 'O2', 'O3');
	$aShowCrud = array_diff($sk_msl -> aFields, $aStripC, $aStripExt);
	
	// CONFIGURAZIONE FILTRI
	$vars_msl = new ordinamento(array('im' => 'ID_METER'));
	$vars_msl -> set_filtro(array('vaim' => $id_meter));
	$vars_msl -> sort_default($sk_msl->f_id);
	$vars_msl -> tabella = $sk_msl->table;
	$vars_msl -> where(" IS_DEL = '0'");

	// CREAZIONE QUERY
	$sk_msl -> query_list();
	$qTotRec = $sk_msl->query_list;
	$sublable = arr::arr2constant($aShowList, false);
	$qTotRec .= ' '.$vars_msl->where;
	
	$cursor = new cursor($qTotRec, $sk_msl -> offset);
	$cursor -> set_passo(5);
	$cursor -> set_mode('full'); # simple normal full
	
	$backUri = array_merge($vars_msl->href);
	$backUri['id'] = $id_meter;
	
	$backUriHidden=array_merge($vars_msl->hidden);
	$vars_msl->backuri = $backUri;
	$vars_msl -> etichette_sort($sublable);
	
	$backUriIds = $backUri;
	
	$rs = rs::inMatrix($qrs=$qTotRec." ".$cursor -> limit);
	
	$lista=''; $cnt=0;
	foreach($rs as $rec){
		$backUriIds["id"] = $rec[$sk_msl->f_id];
		if(!empty($sk_msl->files)){ // GESTIONE MULTIPLA FILE
			$totali = $sk_msl->cnt_files($rec[$sk_msl->f_id]);
			$rec['ID_FILE'] = '';
			if(in_array('files', $sk_msl->files) && in_array('images', $sk_msl->files)) $rec['ID_FILE'] = io::a($sk_msl->file_f, $backUriIds, $totali['f'].' file - '.$totali['i'].' img', array('class' => 'puls_allegati_text'));
			elseif(in_array('images', $sk_msl->files)) $rec['ID_FILE'] = io::a($sk_msl->file_f, $backUriIds, $totali['i'].' img', array('class' => 'puls_allegati_text'));
		}
	
		$color = $cnt%2==0 ? '' : ' class="contrast"';
	//	$href[EDIT]=io::a($sk_msl->file_c, array_merge($backUriIds, array('crud' => 'upd')), '', array('class' => 'puls_modifica'));
		$href['cestino'] = '<span class="puls_trash" onclick="dele_measure('.$rec['ID_MEASURE'].');"></span>';
	
		$celle='';
		foreach($sublable as $k=>$v){
			if(substr($k,0,2)=='D_') $rec[$k] = dtime::my2iso($rec[$k]); // DATA
			elseif(substr($k,0,3)=='IS_') $rec[$k] = !empty($rec[$k]) ? '<div class="icon_check_att"></div>' : '<div class="icon_check_dis"></div>'; // SI NO
			elseif($k == 'ID_FILE'){ 
				if(in_array('images', $sk_msl -> files)){
					$a_img = $sk_msl -> files_list($rec[$sk_msl -> f_id], 'i', $sk_msl -> img_alb_sqr);
					if(!empty($a_img)){
						$prw_img = $a_img[0]['obj_img'];
						$prw_img -> set_attr(50,50);
						$rec[$k] = io::a($sk_msl->file_f, $backUriIds, $prw_img -> html, array());
					}
				}
			}
			elseif(substr($k,0,3)=='ID_' || $k == $sk_msl->f_path) $rec[$k]; // IMMAGINE
			elseif($k == 'USER') $rec[$k];
			else $rec[$k] = strcut(trim(strip_tags($rec[$k])), '...', 20);
			$celle.='<td>'.$rec[$k].'</td>';
		}
		
		$links = '';
		foreach($href as $k => $link){
			$links .= '<li>'.$link.'</li>';
		}
		if(!empty($links)) $links = '<ul class="links">'.$links.'</ul>';
		$links = '<div id="links" class="floatright">'.$links.'</div>';
		
		$lista.='<tr'.$color.' valign="top" id="line'.$rec['ID_MEASURE'].'">'.$celle.'
		<td class="contrast" align="right">'.$links.'</td></tr>';
		
	$cnt++;
	}
	
	$vars_msl->campi_ricerca();
	
	$send_vars = $_GET;
	$send_vars = arr::_unset($send_vars, array('err','ack'));
	$action = url::get(FILENAME.'.php', $send_vars);
	
	ob_start();
	?>
	<form method="get" name="list" action="<?=$action?>">
	<?=request::hidden($backUriHidden)?>
	<table class="list">
	<tr class="sort"><?=$vars_msl->th?><th align="right"></th></tr>
	<tr class="search"><?=$vars_msl->ricerca?><th align="right"><input id="button" <?=$vars_msl->sortbutton;?> class="button_cerca" type="submit" value="<?=FIND?>" name="button"/></th></tr>
	<?=$lista?>
	<tr><th colspan="<?=$vars_msl->colonne+1?>"><?=$cursor -> player?></th></tr>
	<tr><th colspan="<?=$vars_msl->colonne+1?>"><?=$cursor -> t_recs?> record | <?=$cursor -> t_curs?> pagine</th></tr>
	</table>
	</form>
	<?php
	$ret['list'] = ob_get_clean();
	
	if($json){
		echo json_encode(array('list' => $ret['list']));
	}
	
	return $ret;
	
	
	
}

}
?>