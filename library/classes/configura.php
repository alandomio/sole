<?php
# GESTIONE RICORSIVA DI RECORD

class configura{
public function __construct($table){
	global $lang;
	$this -> lang = $lang;
	$this -> aLangs = $lang -> langs;
	$this -> table = !empty($table) ? strtolower($table) : strtolower(stringa::leftfrom(FILENAME,'_'));
	$this -> f_tb = strtoupper(substr($this->table,0,strlen($this->table)-1));
	$this -> f = $this -> f_tb;
	$this -> f_pr = $this -> f_tb.'_'.$this -> lang -> def;
	//$this -> f_tb = $this -> f_pr;
	$this -> f_id = 'ID_'.$this->f_tb;
	$this -> f_is = 'IS_'.$this->f_tb;
		
	$this -> f_idself = $this -> f_id.'_SELF';
	$this -> f_idcateg = $this -> f_id.'S_CATEG';
	$this -> f_idtype = $this -> f_id.'S_TYPE';
	$this -> f_categ = $this -> f_tb.'S_CATEG';
	
	$this->f_tb = $this->f_tb.'_'.LANG_DEF; # ALLINEO PER L'USO MULTILINGUA
	
	$this -> err = array();
	$this -> ack = array();
	$this -> v_type = array('ct', 'il'); # ct => categoria, il => id lista
	$this -> mode = ''; # ins mod del
	$this -> aCateg = array();
	$this -> aType = array();
	$this -> categoria = '';
	$this -> html = '';
	$this -> path = '';
	$this -> path_s = '';
	$this -> tr = array();
	$this -> target = '';
	$this -> livello = '';
	$this -> naviga = false;
	$this -> next_level = false;
	$this -> backUri = array();
	$this -> ct = '';
	$this -> il = '';
	
	$this -> get_variables($_REQUEST);
	$this -> get_types();
	//print 'ct'.$this -> ct;
	if(!empty($this -> ct)){
		$this -> get_path();
		$this -> crud();
		$this -> set();
	}
}

function get_variables($a){
	foreach($a as $k => $v){
		$pfx = substr($k, 0, 2);
		$ctr = substr($k, 0, 3);
		if(in_array($pfx, $this->v_type)){
			if($pfx == 'ct')	{$this -> set_ct($v); }
			elseif($pfx == 'il'){$this -> set_il($v); }
		}
		if($ctr == 'ins') $this -> mode = 'ins';
		elseif($ctr == 'mod') $this -> mode = 'mod';
		elseif($ctr == 'mal') $this -> mode = 'mal';
		elseif($ctr == 'del') $this -> mode = 'del';
	}
	//$this -> backUri = array('ct' => $this -> ct, 'il' => $this -> il);
}

function set_il($il){
	$this -> il = $il;
	$this -> backuri['il'] = $il;
}

function set_ct($ct){
	$this -> ct = $ct;
	$this -> backuri['ct'] = $ct;
}


public function crud(){
	if($this -> mode == 'del') $this -> elimina($_REQUEST['del']);
	elseif($this -> mode == 'ins') $this -> inserisci();
	elseif($this -> mode == 'mod') $this -> modifica($_REQUEST['id_rec']);
	elseif($this -> mode == 'mal') $this -> mod_all();
}

public function set(){
	$flag = true;
	if(empty($this -> ct)){
		$flag = false;
	}
	elseif(empty($this -> il)){ 
		$this -> get_categ();
		$this -> q = "SELECT * FROM ".$this -> table." WHERE ".$this -> f_idcateg." = '".$this -> ct."' AND ".$this -> f_idself." IS NULL AND IS_SIMPLE = '0' ORDER BY ".$this -> f_pr." ASC";
		$rs = rs::inMatrix($this -> q);
	}
	else{
		$this -> get_categ();
		$this -> q = "SELECT * FROM ".$this -> table." WHERE ".$this -> f_idcateg." = '".$this -> ct."' AND ".$this -> f_idself." = '".$this -> il."' ORDER BY ".$this -> f_pr." ASC";
		$rs = rs::inMatrix($this -> q);
	}
	foreach($rs as $k => $v){
		if($flag) $this -> crea_riga($v);
	}
}

function get_categ(){
	$q = "SELECT * FROM ".$this -> table."_categs WHERE ".$this -> f_idcateg." = '".$this -> ct."' ORDER BY ORDINAMENTO_CATEG ASC";
	$this -> aCateg = rs::rec2arr($q);
}

function get_categs(){
	$q = "SELECT * FROM ".$this -> table."_categs ORDER BY ORDINAMENTO_CATEG ASC";
	$this -> aCateg = rs::inMatrix($q);
}

function get_types(){
	$q = "SELECT * FROM ".$this -> table."_types";
	$aType = rs::inMatrix($q);
	$this -> aType = arr::semplifica($aType, 'ID_DESCRIPTORS_TYPE_SELF');
}

function get_path(){
	$this -> path = '';
	$this -> path_s = '';
	if(!empty($this -> il)){
		$rec_id = $this -> il;
		$separatore = ' &rsaquo; ';
		$cnt = 0; 
		do{
			$q = "SELECT * FROM ".$this -> table." WHERE ".$this -> f_id." = '$rec_id'";
			$qSelf = "SELECT * FROM ".$this -> table." WHERE ".$this -> f_idself." = '$rec_id' LIMIT 0,1" ;
			
			$rec = rs::rec2arr($q);
			$rec_id = $rec[$this -> f_idself];
			
			if($cnt == 0){
				$rSelf = rs::rec2arr($qSelf);
				if(!empty($rSelf[$this -> f_id])) $this -> next_level = true;
				else $this -> next_level = false;

				if(!empty($this -> aType[$rec[$this -> f_idtype]]['ID_DESCRIPTORS_TYPE'])){
				$this -> livello = $this -> aType[$rec[$this -> f_idtype]]['ID_DESCRIPTORS_TYPE'];
				if(array_key_exists($this -> livello, $this -> aType)) $this -> naviga = true; # ABILITO I LINK DI SOTTONAVIGAZIONE E QUINDI L'INSERIMENTO
				}
			}
			if(empty($this -> target)) $this -> target = $rec[$this -> f_pr];
			$this -> path = io::ahref('', array('ct' => $this -> ct, 'il' => $rec[$this -> f_id]), $rec[$this -> f_pr], 'g-button').$separatore.$this -> path;
			$this -> path_s = $rec[$this -> f_pr].$separatore.$this -> path_s;
			
			$cnt++; # Protezione loop
		}
		while($rec[$this -> f_idself] != NULL || $cnt > 20); # FINE DO
		
		if(!empty($this -> aCateg[$this -> f_categ])){ 
			$this -> path = io::ahref('', array('ct' => $this -> ct), $this -> aCateg[$this -> f_categ], '').$separatore.$this -> path;
			$this -> path_s = $this -> aCateg[$this -> f_categ].$separatore.$this -> path_s;
		}
		if(!empty($this -> path)){ 
			$this -> path = ' '.$separatore.' '.stringa::togli_ultimi($this -> path, strlen($separatore));
			$this -> path_s = stringa::togli_ultimi($this -> path_s, strlen($separatore));
		}
	}
	else{
		$this -> naviga = true;
	}
}

public function get(){
	//print $this -> html;
	$this -> mk_tabella();
	print $this -> tabella;
}

function crea_riga($a){
	$cls_dele = ''; $dsb = '';
	$q_cnt = "SELECT COUNT(*) AS RICORRENZE FROM ".$this -> table." WHERE ".$this -> f_idself." = '".$a[$this -> f_id]."'";
	$r_cnt = rs::rec2arr($q_cnt);
	$cnt = $r_cnt['RICORRENZE'];
	$lnk = $this -> naviga ? io::ahref('', array('ct' => $this -> ct, 'il' => $a[$this -> f_id]), $a[$this -> f_pr].' ('.$cnt.')', '') : $a[$this -> f_pr];
	if($cnt > 0){
		$cls_dele = ' puls_dis';
		$dsb = ' disabled="disabled"';
	}
	
	$class_riga = ''; $chk_attiva = '';
	if($a[$this -> f_is] == 0){
		$class_riga = ' class="giallo"';
		$chk_attiva = '<span class="attiva"><input type="checkbox" name="attiva" value="1" /> attiva</span>';
	}
	$inputs = '';
	foreach($this -> aLangs as $k => $lng){
		$inputs .= '<div class="box_input"><span>'.constant($lng).':</span>'.BR.'<input type="text" name="ln'.$lng.$a[$this -> f_id].'" class="centocinquanta char_piccolo" maxlength="255" value="'.$a[$this -> f.'_'.$lng].'" /></div>';
	}

$send_vars = $_GET;
$send_vars = arr::_unset($send_vars, array('err','ack'));
$action = url::get(FILENAME.'.php', $send_vars);

$href_del = io::a(FILENAME.'.php', array_merge(array('del' => $a[$this -> f_id]), $send_vars), 'Elimina', array('class' => 'g-button g-button-red', 'onclick' => CONFIRM_DEL, 'style' => 'margin:18px 0 0 20px; '));
if(!empty($dsb)) $href_del = '';

	$this -> tr[] = '
<tr'.$class_riga.'>
<td class="dpt_riga" colspan="3">
<!-- <input type="hidden" name="id_rec" value="'.$a[$this -> f_id].'" /> -->
<div class="text_normal">
'.$inputs.'
</div>
'.$chk_attiva.'
<div>
<!-- <input name="mod" type="submit" value="modifica" class="button_modifica" style="margin:0  0 6px 20px; width:80px;"/><br /><input name="del" type="submit" value="Elimina" '.ON_CLICK_DEL_CONF.' class="button_elimina'.$cls_dele.'"'.$dsb.' style="margin:18px 0 0 20px; width:70px;" /> -->
'.$href_del.'

</div>
<div class="dpt_lnkgotosub">
'.$lnk.'
</div>
</td>
</tr>
';
}

function mk_tabella(){
	if(empty($this -> ct)){
		$this -> get_categs();
		$pulsanti = '';
		foreach($this -> aCateg as $k => $v){
			$q_cnt = "SELECT COUNT(*) AS OCCORRENZE FROM ".$this -> table." WHERE ".$this -> f_idcateg." = '".$v[$this -> f_idcateg]."'";
			$r_cnt = rs::rec2arr($q_cnt);
			$num = $r_cnt['OCCORRENZE'];

			$pulsanti .= io::ahref('', array('ct' => $v[$this -> f_idcateg]), $v[$this -> f_categ].' ('.$num.')', 'g-button').' '; 
		}
		$this -> tabella = '<table class="dark">
		<tr class="bg"><th>'.CHOOSE_CATEGORY.': '.$pulsanti.'</th></tr>
		</table>';
	}
	else{
		$lbl = ''; 
		$back = io::ahref('', array(), CHANGE_CATEGORY, 'g-button');
		$main_cat = io::ahref('', array('ct' => $this -> ct), $this -> aCateg['DESCRIPTORS_CATEG'], 'g-button');
		$tmp_tabella = '';
		$k = 0;
		foreach($this -> tr as $k => $v){
			$tmp_tabella .= $v;
		}
		$this -> tabella = '<table class="dark">
		<tr class="bg"><th colspan="3">'.$back.' | '.$main_cat.$this -> path.'</th></tr>';
		
		if(!empty($this -> target) || !empty($this -> livello)){	
		$lbl = ($k > 0) ? ($k+1).' records in '.$this -> target : 'no record in '.$this -> target; 
		
		# COSTRUISCO I CAMPI TEXT
		$txtinserts = '';
		foreach($this -> aLangs as $k => $lng){
			$txtinserts .= '<div class="box_input"><span>'.constant($lng).':</span>'.BR.'<input type="text" name="txDESCRIPTOR_'.$lng.'" class="centocinquanta char_piccolo" maxlength="255" /></div>';
		}
		
		$send_vars = $_GET;
		$send_vars = arr::_unset($send_vars, array('err','ack'));
		$action = url::get(FILENAME.'.php', $send_vars);
		
		$this -> tabella .= '<form method="post" action="'.$action.'">
		<table class="list">
		<tr><th>'.INSERT_NEW_SUBCATEGORY.'</th></tr>
		<tr><td>
		<input type="hidden" name="livello" value="'.$this -> livello.'" />
		'.$txtinserts.'
		<input name="ins" type="submit" value="'.ADD_IN.' \''.$this -> target.'\'" class="g-button" title="aggiungi in '.$this -> target.'" style="margin:20px 0 0 20px;" />
		</td></tr>
		</table></form>';
		}

		$this -> tabella .= '<form method="post">';
		$this -> tabella .= '<table class="list">';
		$this -> tabella .= '<tr><th colspan="2">'.EDIT.'</th><th><input name="mal" type="submit" value="'.SAVE.'" class="g-button g-button-yellow floatright"/></th></tr>';
		$this -> tabella .= $tmp_tabella;
	//	$this -> tabella .= '<tr><th colspan="2">'.$lbl.'</th><th><input name="mal" type="submit" value="Salva modifiche" class="g-button g-button-yellow"/></th></tr>';
		$this -> tabella .= '</table>';
		$this -> tabella .= '</form>';
		
	}
}

function inserisci(){
	if(!empty($_POST['ins'])){
		$vars = new ordinamento(array());
		
		# COSTRUISCO L'INSERT
		$sFlds = ''; $sVals = '';
		foreach($vars -> tx as $campo => $valore){
			$s = prepare4sql($valore);
			$sFlds .= " $campo,";
			$sVals .= " '$s',";
			$sAcks .= " &quot;$s&quot;,";
		}
		$qIns = "INSERT INTO ".$this -> table." (ID_DESCRIPTOR_SELF, $sFlds ID_DESCRIPTORS_TYPE, ID_DESCRIPTORS_CATEG, IS_DESCRIPTOR) VALUES ('".$this -> il."', $sVals '".$this -> livello."', '".$this -> ct."', '1')";
		
		//print $qIns.BR;
		
		# RICKY: REDIRECT DOPO L'INSERIMENTO
		
		if(mysql_query($qIns)){ 
			io::headto(FILENAME.'.php', array_merge($_GET, array('ack' => ADDED." ".stringa::togli_ultimo($sAcks))));
		}
		else $this -> err[] = INSERT_ERROR;
	}
	//else $this -> err[] = 'Non puoi inserire un record vuoto';
}

function inserisci_valore($str){ # USATO IN MERCATINO PER L'INSERIMENTO DALLA SEZIONE PUBBLICA
	if(!empty($str)){
		$stringa = $str;
		$add = dbChkQuery("SELECT * FROM ".$this -> table." WHERE ".$this -> f_pr." = '$stringa' AND ".$this -> f_idself." = '".$this -> il."'", 
		"INSERT INTO ".$this -> table." (ID_DESCRIPTOR_SELF, DESCRIPTOR, ID_DESCRIPTORS_TYPE, ID_DESCRIPTORS_CATEG, IS_DESCRIPTOR, IS_NEW) VALUES ('".$this -> il."', '".prepare4sql($stringa)."', '".$this -> livello."', '".$this -> ct."', '1', '1')");
		if($add === true){ 
			# INVIO EMAIL???
			$this -> ack[] = ADDED." $stringa";
		
		}
		else $this -> err[] = INSERT_ERROR;
	}
	else $this -> err[] = ERROR_EMPTY_RECORD;
}

function mod_all(){
	$vars = new ordinamento(array());
	
	$r = 0;
	foreach($vars -> ln as $k => $v){ # lang, id, value
		if(!empty($v['value'])){
			$vv = prepare4sql($v['value']);
			$qu = "UPDATE descriptors SET DESCRIPTOR_".$v['lang']." = '$vv' WHERE ID_DESCRIPTOR = '".$v['id']."'";
			if(mysql_query($qu)){ $r++; }
			else{
				$this -> err[] = 'Error: '.$qu;
			}
		}
	}
	if($r > 0) $this -> ack[] = UPDATED.' '.$r.' record';
}

function modifica($id_mod){
	if(!empty($_POST['mod'])){ # PREMUTO PULSANTE DI MODIFICA
		$vars = new ordinamento(array());
		
		# COSTRUISCO L'UPDATE
		$sUpd = '';
		foreach($vars -> tx as $campo => $valore){
			$s = prepare4sql($valore);
			$sUpd .= "$campo = '$s', ";
		}
		$sUpd = stringa::togli_ultimi($sUpd, 2);
		$qExe = "UPDATE ".$this -> table." SET $sUpd WHERE ".$this -> f_id." = '".$id_mod."'";
		$mod = mysql_query($qExe);
		
		if($mod) $this -> ack[] = DATA_SAVED;
		else $this -> err[] = ERROR_EDIT;
	
		if(!empty($_POST['attiva'])){ # ATTIVAZIONE DEL RAMO
			$qAttiva = "UPDATE ".$this -> table." SET IS_".$this -> f_tb." = '1' WHERE ".$this -> f_id." = '".$id_mod."'";
			if(mysql_query($qAttiva)) $this -> ack[] = $stringa.' attivata';
		}
	}
	else $this -> err[] = ERROR_EMPTY_RECORD;
}

function elimina($id_del){
	$q_get = "SELECT * FROM ".$this -> table." WHERE ".$this -> f_id." = '".$id_del."'";
	$r_get = rs::rec2arr($q_get);
	if(!empty($r_get[$this -> f_id])){
		$q_del = "DELETE FROM ".$this -> table." WHERE ".$this -> f_id." = '".$id_del."'";
		if(mysql_query($q_del)) { $this -> ack[] = 'Record "'.$r_get[$this -> f_pr].'" eliminato'; }
		else{ $this -> err[] = 'Impossibile eliminare il record "'.$r_get[$this -> f_pr].'"'; }
	}
	else{
		$this -> err[] = 'Record gi&agrave; eliminato o non trovato';
	}
	$_GET['del'] = NULL; unset($_GET['del']);
	io::headto(FILENAME.'.php', $_GET);
}

######################################

function new_element($js, $val, $is_dis){
	//if(empty($is_dis)){
		if($js != $val && empty($val)){
			print 'Non trovi la categoria? Aggiungila:<br />
			<input type="hidden" name="add_js" value="'.$js.'" />
			<input type="text" maxlength="20" name="add_val" />
			<input name="add_sub" type="submit" value="Aggiungi" />';
		}
	//}
}

function get_wizard($a, $db, $il){
	$retFlds = '';
	$this -> il = $il;
	$this -> path_s = '';
	$rec_id = $this -> il;
	$separatore = ' &rsaquo; ';
	$cnt = 0; 
	do{
		$q = "SELECT * FROM ".$this -> table." WHERE ".$this -> f_id." = '$rec_id'";
		$rec = rs::rec2arr($q);
		$qSelf = "SELECT * FROM ".$this -> table." WHERE ".$this -> f_idself." = '$rec_id' LIMIT 0,1" ;
		$rSelf = rs::rec2arr($qSelf);
		$fld_type = $rec['ID_DESCRIPTORS_TYPE'];
		$num = substr($fld_type, strlen($fld_type)-1, strlen($fld_type)); # SERVE A RISALIRE AL NOME CAMPO $db
		$rec_id = $rec[$this -> f_idself]; # PROSSIMO ID
		if($cnt == 0){
			if(!empty($rSelf[$this -> f_id])){
				$this -> next_level = true;

				if(array_key_exists($num+1, $a)){
					ob_start(); ?>
					<div class="menu_multipli"><h2><?=constant($a[$num+1])?></h2><? $db -> $a[$num+1] -> get(); ?>
                    <?
                    print 'Non trovi la categoria? Aggiungila:<br />
			<input type="hidden" name="add_js" value="'.$this -> il.'" />
			<input type="text" maxlength="20" name="add_val" />
			<input name="add_sub" type="submit" value="Aggiungi" />';
					?>
                    </div>
					<?php
					
					$retFlds = ob_get_clean().$retFlds; # # #
				}
			}
			else{ 
				$this -> next_level = false; 
			}

			if(!empty($this -> aType[$rec[$this -> f_idtype]]['ID_DESCRIPTORS_TYPE'])){
			$this -> livello = $this -> aType[$rec[$this -> f_idtype]]['ID_DESCRIPTORS_TYPE'];
			if(array_key_exists($this -> livello, $this -> aType)) $this -> naviga = true; # ABILITO I LINK DI SOTTONAVIGAZIONE E QUINDI L'INSERIMENTO
			}
		}
		if(empty($this -> target)) $this -> target = $rec[$this -> f_pr];
		$this -> path = io::ahref('', array('ct' => $this -> ct, 'il' => $rec[$this -> f_id]), $rec[$this -> f_pr], 'g-button').$separatore.$this -> path;
		
		if(array_key_exists($num, $a)){
		ob_start(); ?>
		<div class="menu_multipli"><h2><?=$rec['DESCRIPTOR_'.LANG_DEF]?></h2><? $db -> $a[$num] -> get(); ?></div>
		<?php
		$retFlds = ob_get_clean().$retFlds; # # #
		}
		$this -> path_s = $rec[$this -> f_pr].$separatore.$this -> path_s;
		
		$cnt++; # Protezione loop
	}
	while($rec[$this -> f_idself] != NULL || $cnt > 20); # FINE DO
	//print $cnt;
	if($cnt < count($a) && !$this -> next_level){
		$retFlds .= '<div class="nuova_sottovoce"><h2>Aggiungi nuova sottovoce</h2>
					<input type="hidden" name="add_js" value="'.$this -> il.'" />
					<input type="text" maxlength="20" name="add_val" />
					<input name="add_sub" type="submit" value="Aggiungi" />
					</div>';
	}
	
	
	if(!empty($this -> aCateg[$this -> f_categ])){ 
		$this -> path = io::ahref('', array('ct' => $this -> ct), $this -> aCateg[$this -> f_categ], '').$separatore.$this -> path;
		$this -> path_s = $this -> aCateg[$this -> f_categ].$separatore.$this -> path_s;
	}
	if(!empty($this -> path)){ 
		$this -> path = ' | '.stringa::togli_ultimi($this -> path, strlen($separatore));
		$this -> path_s = stringa::togli_ultimi($this -> path_s, strlen($separatore));
	}
	return $retFlds;
}


function get_wizard_simple($a, $db, $il){ # USATO NELLA SEZIONE LOGIN
	$retFlds = '';
	$this -> il = $il;
	$this -> path_s = '';
	$rec_id = $this -> il;
	$separatore = ' &rsaquo; ';
	$cnt = 0; 
	do{
		$q = "SELECT * FROM ".$this -> table." WHERE ".$this -> f_id." = '$rec_id'";
		$rec = rs::rec2arr($q);
		$qSelf = "SELECT * FROM ".$this -> table." WHERE ".$this -> f_idself." = '$rec_id' LIMIT 0,1" ;
		$rSelf = rs::rec2arr($qSelf);
		$fld_type = $rec['ID_DESCRIPTORS_TYPE'];
		$num = substr($fld_type, strlen($fld_type)-1, strlen($fld_type)); # SERVE A RISALIRE AL NOME CAMPO $db
		$rec_id = $rec[$this -> f_idself]; # PROSSIMO ID
		if($cnt == 0){
			if(!empty($rSelf[$this -> f_id])){
			
				$this -> next_level = true;
				
				if(array_key_exists($num+1, $a)){
					$this -> puls_next = false;
					ob_start(); ?>
                    <div>
					<? $db -> $a[$num+1] -> get(); ?>
                    <?php
                    /*print 'Non trovi la categoria? Aggiungila:<br />
			<input type="hidden" name="add_js" value="'.$this -> il.'" />
			<input type="text" maxlength="20" name="add_val" />
			<input name="add_sub" type="submit" value="Aggiungi" />';*/
					?>
                    </div>
					<?php
					$retFlds = ob_get_clean().$retFlds;
				}
			}
			else{ 
				$this -> next_level = false; 
			}

			if(!empty($this -> aType[$rec[$this -> f_idtype]]['ID_DESCRIPTORS_TYPE'])){
			$this -> livello = $this -> aType[$rec[$this -> f_idtype]]['ID_DESCRIPTORS_TYPE'];
			if(array_key_exists($this -> livello, $this -> aType)) $this -> naviga = true; # ABILITO I LINK DI SOTTONAVIGAZIONE E QUINDI L'INSERIMENTO
			}
		}
		if(empty($this -> target)) $this -> target = $rec[$this -> f_tb];
		$this -> path = io::ahref('', array('ct' => $this -> ct, 'il' => $rec[$this -> f_id]), $rec[$this -> f_tb], 'g-button').$separatore.$this -> path;
		
		
		if(is_numeric($num) && array_key_exists($num, $a)){
		ob_start(); ?>
		<!--<div class="menu_multipli"><h2><?=$rec['DESCRIPTOR']?></h2>-->
		<div><? $db -> $a[$num] -> get(); ?></div>
		<?php
		$retFlds = ob_get_clean().$retFlds; # # #
		}
		$this -> path_s = $rec[$this -> f_tb].$separatore.$this -> path_s;
		$cnt++; # Protezione loop
	}
	while($rec[$this -> f_idself] != NULL || $cnt > 20); # FINE DO
	if($cnt < count($a) && !$this -> next_level){
		/*$retFlds .= '<div class="nuova_sottovoce"><h2>Aggiungi nuova sottovoce</h2>
					<input type="hidden" name="add_js" value="'.$this -> il.'" />
					<input type="text" maxlength="20" name="add_val" />
					<input name="add_sub" type="submit" value="Aggiungi" />
					</div>';*/
	}
	
	
	if(!empty($this -> aCateg[$this -> f_categ])){ 
		$this -> path = io::ahref('', array('ct' => $this -> ct), $this -> aCateg[$this -> f_categ], '').$separatore.$this -> path;
		$this -> path_s = $this -> aCateg[$this -> f_categ].$separatore.$this -> path_s;
	}
	if(!empty($this -> path)){ 
		$this -> path = ' | '.stringa::togli_ultimi($this -> path, strlen($separatore));
		$this -> path_s = stringa::togli_ultimi($this -> path_s, strlen($separatore));
	}
	return $retFlds;
}

function get_path_2(){
	if(!empty($this -> il)){
		$rec_id = $this -> il;
		$separatore = ' &raquo; ';
		$cnt = 0; 
		do{
			$q = "SELECT * FROM ".$this -> table." WHERE ".$this -> f_id." = $rec_id";
			$rec = rs::rec2arr($q);
			$rec_id = $rec[$this -> f_idself];
			if($cnt == 0){
				$this -> livello = $this -> aType[$rec[$this -> f_idtype]]['ID_DESCRIPTORS_TYPE'];
				if(array_key_exists($this -> livello, $this -> aType)) $this -> naviga = true; # ABILITO I LINK DI SOTTONAVIGAZIONE E QUINDI L'INSERIMENTO
			}
			if(empty($this -> target)) $this -> target = $rec[$this -> f_pr];
			$this -> path = io::ahref('', array('ct' => $this -> ct, 'il' => $rec[$this -> f_id]), $rec[$this -> f_pr], 'g-button').$separatore.$this -> path;
			$cnt++; # Protezione loop
		}
		while($rec[$this -> f_idself] != NULL || $cnt > 20);
	if(!empty($this -> aCateg[$this -> f_categ])) $this -> path = io::ahref('', array('ct' => $this -> ct), $this -> aCateg[$this -> f_categ], '').$separatore.$this -> path;
	if(!empty($this -> path)) $this -> path = ' | '.stringa::togli_ultimi($this -> path, strlen($separatore));
		
	}
	else{
		$this -> naviga = true;
	}
}

function mk_branch($rs){ # FORMALIZZA I DATI ESSENZIALI DI UN RAMO
	$ret = array();
	$ret['tbl'] = $rs['DESCRIPTORS_TABLE'];
	$ret['type'] = $rs['ID_DESCRIPTORS_TYPE'];
	$ret['top'] = $rs['ID_DESCRIPTORS_TYPE_SELF'];
	$ret['low'] = '';
	
	$q = "SELECT * FROM descriptors_types WHERE ID_DESCRIPTORS_TYPE_SELF = '".$ret['type']."' LIMIT 0, 1";
	$r = rs::rec2arr($q);
	if(!empty($r['ID_DESCRIPTORS_TYPE'])){
		$ret['low'] = $r['ID_DESCRIPTORS_TYPE'];
	}
	return $ret;
}

function get_structure($tbl){
	$i = 0; $ret = array();
	do{
		if(empty($ret)){ # INIZIALIZZO PER RICERCA DAL RAMO PIU BASSO
			$q = "SELECT * FROM descriptors_types WHERE DESCRIPTORS_TABLE = '$tbl' LIMIT 0, 1";
			$rs = rs::rec2arr($q);
			$ret[] = $this -> mk_branch($rs);
			$i ++;
		}
		
		$q = "SELECT * FROM descriptors_types WHERE ID_DESCRIPTORS_TYPE_SELF = '".$ret[($i-1)]['type']."' LIMIT 0, 1";
		$rs = rs::rec2arr($q);
		if(!empty($rs['ID_DESCRIPTORS_TYPE'])){
			$ret[] = $this -> mk_branch($rs);
			$i ++;
		}
	}
	while(!empty($rs['ID_DESCRIPTORS_TYPE']) && $i < 20);
	
	$this -> structure = $ret;
	$this -> levels = $i;
}

function get_level($id){
	$this -> level = 0;
	$q = "SELECT ID_DESCRIPTORS_TYPE FROM descriptors WHERE ID_DESCRIPTOR = '$id' LIMIT 0,1";
	$r = rs::rec2arr($q);
	
	foreach($this -> structure as $p => $b){
		if($b['type'] == $r['ID_DESCRIPTORS_TYPE']){
			$this -> level = $p;
			break;
		}
	}
}

function get_info($tbl, $val){ # PASSATO UNA ARRAY ([statis] => 9) INDICA IL RAMO IN CUI CI TROVIAMO, QUELLI PRECEDENTI E QUELLI SUCCESSIVI
	$this -> get_structure($tbl);
	# TROVO IL LIVELLO
	$this -> get_level($val);

}

function match_K($arr, $aVals){ # I CAMPI TABELLA IN ARRAY
	$ret = array();
	$aK = array('K0_','K1_','K2_','K3_');
	foreach($aVals as $k => $v){
		foreach($aK as $i => $pre){
			if(in_array($pre.$v, $arr)) { 
				$ret[] = $pre.$v;
			}
		}
	}
	return $ret;
}

function set_null_fields($main_table, $tbl, $val, $id){
	$id_table = stringa::tbl2id($main_table);
	$this -> get_info($tbl, $val);
	
	$aNull = array();
	foreach($this -> structure as $k => $v){ # CREO LA LISTA DEI LIVELLI DA SETTARE A NULL
		if($k > $this -> level){
			$aNull[] = stringa::tbl2id($v['tbl']);
		}
	}
	
	if(!empty($aNull)){
		$aF = rs::get_fields($main_table);
		$aKF = $this -> match_K($aF,$aNull);
		if(!empty($aKF)){ # UPDATE NULL A TUTTI I CAMPI OLTRE SUCCESSIVI ALL'ULTIMO VALORIZZATO
			foreach($aKF as $i => $kf){
				$q = "UPDATE ".$main_table." SET $kf = NULL WHERE $id_table = '$id'";
				mysql_query($q);
			}
		}
	}
}

}
?>