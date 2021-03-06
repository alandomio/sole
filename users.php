<?php
include_once 'init.php';

$user = new autentica($aA3);
$user -> login_standard();
include_once 'users_conf.php';

$is_swf=0; $is_head_mce=0; $is_multibox=0; $is_js=1;

if(array_key_exists('del',$_GET) || array_key_exists('del',$_POST)){
	$cnt = 0;
	foreach($my_vars->ck as $id_del){
		if(!empty($id_del) && $scheda->dele_main($id_del)) $cnt++;
		mysql_query("DELETE FROM users_federations WHERE ID_USER = '$id_del'");
	}
	if($cnt>0) $ack[] = "$cnt deleted";
}

$aCurs=array("icursor"=>$icursor);
$aFromp=array();
$field_list=rs::rec2arr("SELECT * FROM ".$scheda->table." LIMIT 0,1");
$select='';
foreach($field_list as $k => $v){
	$select.= $scheda->table.".$k, ";
}

$select = substr($select, 0, strlen($select)-2);
$scheda->query_list();
$qTotRec = $scheda->query_list;
$aTbl = array($scheda->table, $scheda->file_table);
$lable=rs::sql2lbl($qTotRec); 
$fil="";

$sublable=arr::_unset($lable,$aStripList);
$sublable=rs::label_frmt($sublable);

$extra_where = '';

$id_federation = array_key_exists('federations', $_GET) ? $_GET['federations'] : '';
$id_hcompany = array_key_exists('hcompanys', $_GET) ? $_GET['hcompanys'] : '';
$id_building = array_key_exists('blbld', $_GET) ? $_GET['blbld'] : '';

if($user->idg == 2){ # GM VEDE SOLO UTENTI DELLA PROPRIA FEDERAZIONE
	$qTotRec .= "
LEFT JOIN users_federations ON users.ID_USER = users_federations.ID_USER 
";

$extra_where = "
users_federations.ID_FEDERATION = '{$user -> aUser['ID_FEDERATION']}'
";

}
elseif($user->idg == 3){ # MHMU VEDE SOLO UTENTI HHU, HMU, MHMU (S� STESSO) DELLE PROPRIE HC
	
	$qTotRec = stringa::lfw($qTotRec, 'Left Join hcompanys');

	$qTotRec .= "LEFT JOIN users_federations ON users.ID_USER = users_federations.ID_USER
LEFT JOIN hcompanys ON hcompanys.ID_FEDERATION = users_federations.ID_FEDERATION
";

	$q_id_hc = "SELECT ID_HCOMPANY FROM hcompanys WHERE ID_USER = '".$user -> aUser['ID_USER']."' LIMIT 1";
	$r_id_hc = rs::rec2arr($q_id_hc);
	$id_hc = $r_id_hc['ID_HCOMPANY'];
	

	$extra_where = "
users.ID_GRUPPI >= '4' AND
users.ID_HCOMPANY = '$id_hc'
GROUP BY users.ID_USER
";
}

// filtro edificio
if(!empty($id_building)){
	
	$qTotRec .= "
LEFT JOIN flats ON users.ID_USER = flats.ID_USER
";
	$and = empty($extra_where) ? '' : ' AND ';
	$extra_where = " flats.ID_BUILDING='".prepare4sql($id_building)."' ".$and.$extra_where;
}


$my_vars -> where($extra_where);

$qTotRec.=' '.$my_vars->where;

$cursor = new cursor($qTotRec, $scheda -> offset);
$cursor -> set_passo(5);
$cursor -> set_mode('full'); # simple normal full

$backUri=array_merge($my_vars->href);
$backUriHidden=array_merge($my_vars->hidden);
$my_vars->backuri = $backUri;
$my_vars->etichette_sort($sublable);
$backUriIds = $backUri;
$rs=rs::inMatrix($qrs=$qTotRec." ".$cursor -> limit.$fil);

$lista='';
$cnt=0;

foreach($rs as $rec){
	$backUriIds["id_rec"]=$rec[$scheda->f_id];
	$color = $cnt%2==0 ? '' : ' class="contrast"';
	$href_mod=io::a($scheda->file_c, array_merge($backUriIds, array('crud' => 'upd')), EDIT, array('class' => 'href'));

	$celle='';
	foreach($sublable as $k=>$v){
		if(substr($k,0,2)=='D_') $rec[$k] = dtime::my2iso($rec[$k]); // DATA
		elseif(substr($k,0,3)=='IS_') $rec[$k] = !empty($rec[$k]) ? '<div class="icon_check_att"></div>' : '<div class="icon_check_dis"></div>'; // SI NO
		elseif(substr($k,0,3)=='ID_' || $k == $scheda->f_path) $rec[$k]; // IMMAGINE
		elseif($k == 'USER') $rec[$k];
		else $rec[$k] = strcut(trim(strip_tags($rec[$k])),'...',20);
		$celle.='<td>'.$rec[$k].'</td>';
	}
	
	
	/*
	 * campi calcolati in base al tipo utente
	 * gruppo (federazione)
	 * hc
	 * edificio
	 * alloggio
	 * */
	$row['federation']='---';
	$row['hc']='---';
	$row['building']='---';
	$row['flat']='---';
	
	/*
	 * admin, mhcu, hcu non hanno informazioni extra da recuperare
	 * */
	if( ! empty($rec['CODE_HC'])){
		$row['hc']=$rec['CODE_HC'];
	}
	
	if($rec['ID_GRUPPI']==2){ /* federation manager */
		/*
		 * solo gruppo
		 * */
		$q="SELECT FEDERATION FROM federations WHERE ID_USER={$rec['ID_USER']} LIMIT 1";
		$r=rs::rec2arr($q);
		$row['federation']=$r['FEDERATION'];
	}
	elseif($rec['ID_GRUPPI']==5){ /* house hold user */
		/*
		 * può avere associato un appartamento
		 * */
		$q="SELECT CODE_FLAT FROM flats WHERE ID_USER={$rec['ID_USER']} LIMIT 1";
		$r=rs::rec2arr($q);
		$row['flat']=$r['CODE_FLAT'];
	}
	
	$celle.='<td align="center">'.$row['federation'].'</td>';
	$celle.='<td align="center">'.$row['hc'].'</td>';
	$celle.='<td align="center">'.$row['building'].'</td>';
	$celle.='<td align="center">'.$row['flat'].'</td>';
	
	$lista.='<tr'.$color.' valign="top">'.$celle.'
	<td class="contrast" align="right" colspan="2">'.$href_mod.'
    <input type="checkbox" name="ck'.$rec[$scheda->f_id].'" value="1" class="checkbox" /></td></tr>';

	$cnt++;
}

/*
 * pulsante nuovo utente solo per HMU
 * */
if($user->idg == 4 || $user->idg == 1 ){
$href_new = io::a($scheda->file_c, array_merge($backUri,array('crud' => 'ins')), L_NUOVO, array('class' => 'g-button'));
} else {
	$href_new = '';
	
}

$my_vars->campi_ricerca();

$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);

$html['select_building'] = sole::select_fhb('');

/*
 * sole 2.0 richiesta di visualizzazione dei seguenti campi:
 * gruppo (federazione)
 * hc
 * edificio
 * alloggio
 * queste colonne verranno inserite per la sola visualizzazione
 * */

$i_col=4;

include_once HEAD_AR;
?>
<form method="get" name="list" action="<?=$action?>">
<?=request::hidden($backUriHidden)?>


<table class="list">
<tr class="bg">  
<th colspan="<?=$my_vars->colonne+$i_col+1?>">
<?=$href_new?>
</th>
<th align="right"><input name="del" type="submit" class="g-button g-button-red" value="<?=DELETE_SELECTED?>" /></th>
</tr>
<tr class="sort">
	<?=$my_vars->th?>
	<th><a class="g-button" href="#" style="background-image:none;"><?=__('Gruppo')?></a></th>
	<th><a class="g-button" href="#" style="background-image:none;"><?=__('Gestore')?></a></th>
	<th><a class="g-button" href="#" style="background-image:none;"><?=__('Edificio')?></a></th>
	<th><a class="g-button" href="#" style="background-image:none;"><?=__('Appartamento')?></a></th>
	<th rowspan="2">
		<div id="select_building">
		<?=$html['select_building']?>
		</div>
	</th>
	<th align="right"><span class="a_sel_tutti"><?=S_SELALL?> <input type="checkbox" class="checkbox" onchange="javascript:check_all_by_name('list', 'ck')" name="ck" value="1" /></span></th>
</tr>
<tr class="search">
	<?=$my_vars->ricerca?>
	<th colspan="<?=$i_col?>">&nbsp;</th>
	<th align="right"><input id="button" <?=$my_vars->sortbutton;?> class="g-button" type="submit" value="<?=FIND?>" name="button"/></th>
</tr>
<?=$lista?>
<tr><th colspan="<?=$my_vars->colonne+2+$i_col?>"><?=$cursor->player?></th></tr>
<tr><th colspan="<?=$my_vars->colonne+2+$i_col?>"><?=$cursor->t_recs?> record | <?=$cursor->t_curs?> pagine</th></tr>
</table>

<div class="clear"></div>
<input type="hidden" id="id_federation" value="<?=$id_federation?>"/>
<input type="hidden" id="id_hcompany" value="<?=$id_hcompany?>"/>
<input type="hidden" id="id_building" name="blbld" value="<?=$id_building?>"/>
</form>
<script type="text/javascript" src="<?=JS_MAIN.'users.js'?>" /></script>
<?php
include_once FOOTER_AR;
?>