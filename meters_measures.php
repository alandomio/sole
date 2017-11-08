<?php
include_once 'init.php';

$user = new autentica($aA1);
$user -> login_standard();
include_once 'meters_measures_conf.php'; //stringa::get_conffile($MYFILE -> filename);

$is_swf=0; $is_head_mce=0; $is_multibox=0; $is_js=1;

if(array_key_exists('del',$_GET) || array_key_exists('del',$_POST)){
	$cnt = 0;
	foreach($my_vars->ck as $id_del){
		if(!empty($id_del) && $scheda->dele_main($id_del)) $cnt++;
	}
	if($cnt>0) $ack[] = "$cnt eliminati";
}
$my_vars->where(''); 
$aFromp=array();

$scheda -> query_list();
$qTotRec = $scheda->query_list;

$sublable = arr::arr2constant($aShowList, false);

$qTotRec .= ' '.$my_vars->where;


$cursor = new cursor($qTotRec, $scheda -> offset);
$cursor -> set_passo(5);
$cursor -> set_mode('full'); # simple normal full


//$player_curs = new player_curs($qTotRec,$scheda->offset);

$backUri = array_merge($my_vars->href);
$backUri['idi'] = $idi;
$backUri['id'] = $id;

$backUriHidden=array_merge($my_vars->hidden);
$my_vars->backuri = $backUri;
$my_vars -> etichette_sort($sublable);

$backUriIds = $backUri;
//$player_curs -> vars = $backUri;
//$player_curs -> set();

$rs = rs::inMatrix($qrs=$qTotRec." ".$cursor -> limit);

$lista=''; $cnt=0;
foreach($rs as $rec){
	//$scheda->set_upld_dirs(FLD_MAIN.$rec['FOLDER_USR']);

	$backUriIds["id"] = $rec[$scheda->f_id];
	if(!empty($scheda->files)){ // GESTIONE MULTIPLA FILE
		$totali = $scheda->cnt_files($rec[$scheda->f_id]);
		$rec['ID_FILE'] = '';
		if(in_array('files', $scheda->files) && in_array('images', $scheda->files)) $rec['ID_FILE'] = io::a($scheda->file_f, $backUriIds, $totali['f'].' file - '.$totali['i'].' img', array('class' => 'puls_allegati_text'));
		elseif(in_array('images', $scheda->files)) $rec['ID_FILE'] = io::a($scheda->file_f, $backUriIds, $totali['i'].' img', array('class' => 'puls_allegati_text'));
	}
	if($scheda->img){ // IMMAGINE SINGOLA
		//$scheda->set_upld_dirs($rec['FOLDER_USR']);
	if($scheda->img){ // IMMAGINE SINGOLA
		//$scheda->set_upld_dirs($rec['FOLDER_RADUN']);
	
		$rec[$scheda->f_path] = is_file($scheda->img_main_web.$rec[$scheda->f_path]) ? io::a($scheda->file_c, array_merge($backUriIds,array('crud' => 'upd', '<img src="'.$scheda->img_main_web.$rec[$scheda->f_path].'" alt="image" width="80" />', array('title' => EDIT)))) : io::a($scheda->file_c, array_merge($backUriIds,array('crud' => 'upd')), '<img src="'.THU_JOLLY.'" alt="no image" width="80" />', array('title' => EDIT));
	}	
	
/*		$rec[$scheda->f_path] = is_file($scheda->img_main_web.$rec[$scheda->f_path]) ? io::a($scheda->file_c, array_merge($backUriIds,array('crud' => 'upd')), '<img src="'.$scheda->img_main_web.$rec[$scheda->f_path].'" alt="image" width="80" />', array('title' => EDIT)) : io::a($scheda->file_c, array_merge($backUriIds,array('crud' => 'upd')), '<img src="'.THU_JOLLY.'" alt="no image" width="80" />', array('title' => EDIT));
*/	}
	$color = $cnt%2==0 ? '' : ' class="contrast"';
	$href[EDIT]=io::a($scheda->file_c, array_merge($backUriIds, array('crud' => 'upd')), EDIT, array('class' => 'href'));
//	$href[MEASURE]=io::a('meters_measures.php', array_merge($backUriIds, array('crud' => 'upd')), MEASURE, array());
//	$href[ADD.' '.MEASURE]=io::a('meters_add_measure.php', array_merge($backUriIds, array('crud' => 'upd')), ADD.' '.MEASURE, array());

# FORMATTAZIONE PARTICOLARE DI ALCUNI CAMPI ########################################
# FINE CONFIGURAZIONE CAMPI LISTA ##################################################
	$celle='';
	foreach($sublable as $k=>$v){
		if(substr($k,0,2)=='D_') $rec[$k] = dtime::my2iso($rec[$k]); // DATA
		elseif(substr($k,0,3)=='IS_') $rec[$k] = !empty($rec[$k]) ? '<div class="icon_check_att"></div>' : '<div class="icon_check_dis"></div>'; // SI NO
		elseif($k == 'ID_FILE'){ 
			if(in_array('images', $scheda -> files)){
				$a_img = $scheda -> files_list($rec[$scheda -> f_id], 'i', $scheda -> img_alb_sqr);
				if(!empty($a_img)){
					$prw_img = $a_img[0]['obj_img'];
					$prw_img -> set_attr(50,50);
					$rec[$k] = io::a($scheda->file_f, $backUriIds, $prw_img -> html, array());
				}
			}
		}		
		elseif(substr($k,0,3)=='ID_' || $k == $scheda->f_path) $rec[$k]; // IMMAGINE
		elseif($k == 'USER') $rec[$k];
		else $rec[$k] = strcut(trim(strip_tags($rec[$k])),'...',20);
		$celle.='<td>'.$rec[$k].'</td>';
	}
	
	$links = '';
	foreach($href as $k => $link){
		$links .= '<li>'.$link.'</li>';
	}
	if(!empty($links)) $links = '<ul>'.$links.'</ul>';
	$links = '<div id="links">'.$links.'</div>';
	
	
	$lista.='<tr'.$color.' valign="top">'.$celle.'
	<td class="contrast" align="right">'.$links.'
    <input type="checkbox" name="ck'.$rec[$scheda->f_id].'" value="1" class="checkbox" /></td></tr>';
	
$cnt++;
}

$href_new = io::a('meters_add_measure.php', array_merge($backUri,array('crud' => 'ins')), 'Nuova misurazione', array('title' => 'Aggiungi nuovo record', 'class' => 'g-button'));

$sub_menu = '';
foreach($sub_nav as $k => $arr){
	$href_to='';
	if(in_array(FILENAME.'.php',$arr)) { $sub_menu.='<div class="menu_dis"><span>'.$k.'</span></div>'; }
	/*elseif($crud=='ins') { $sub_menu.='<div class="menu_att_dis">'.$k.'</div>'; }*/
	else {
		$href_to = io::a($arr[0], array_merge($backUri,array('crud'=>'upd')), $k, array('title' => $title=strtolower($k)));
		$sub_menu.='<div class="menu_att"><span>'.$href_to.'</span></div>';
	}
}
$sub_menu = '<div id="menu_bar">'.$sub_menu.'</div>';

$my_vars->campi_ricerca();

$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);

include_once HEAD_AR;
print $sub_menu;
?>
<form method="get" name="list" action="<?=$action?>">
<?=request::hidden($backUriHidden)?>
<table class="list">
<tr class="bg">  
<th colspan="<?=$my_vars->colonne?>">
<?=$href_new?>
</th>
<th align="right"><input name="del" type="submit" class="g-button g-button-red" value="<?=DELETE_SELECTED?>" /></th>
</tr>
<tr class="yellow"><td colspan="<?=$my_vars->colonne+1?>"><div class="table_cell"><strong><?=$rm['REGISTERNUM']?></strong></div></td></tr>

<tr class="sort"><?=$my_vars->th?><th align="right"><span class="a_sel_tutti"><?=S_SELALL?> <input type="checkbox" class="checkbox" onchange="javascript:check_all_by_name('list', 'ck')" name="ck" value="1" /></span></th></tr>
<tr class="search"><?=$my_vars->ricerca?><th align="right"><input id="button" <?=$my_vars->sortbutton;?> class="g-button" type="submit" value="<?=FIND?>" name="button"/></th></tr>
<?=$lista?>
<tr><th colspan="<?=$my_vars->colonne+1?>"><?=$cursor -> player?></th></tr>
<tr><th colspan="<?=$my_vars->colonne+1?>"><?=$cursor -> t_recs?> record | <?=$cursor -> t_curs?> pagine</th></tr>
</table>
</form>
<?php
include_once FOOTER_AR;
?>