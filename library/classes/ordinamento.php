<?php
class ordinamento{
var $sa;
var $sd;
var $in;
var $nn;
var $sl;
var $va;
var $lk;
var $mi;
var $ma;
var $ck;
var $v_type;
var $where;
var $aFields;
var $th;
var $search;
var $ordina;
var $href;
var $sortbutton;
public function __construct($a){
	$this->campi=$a; # $a = array( 'dd' => 'DESCRIZIONE', 'hp' => 'IS_HOME')
	$this->chiavi=array_flip($a);
	$this->v_type = array('sa', 'sd', 'in', 'nn', 'sl', 'va', 'lk', 'mi', 'ma', 'bl', 'ck', 'tx', 'nc', 'aa', 'mc', 'js','kk', 'ba', 'bb', 'bc', 'bd', 'ln','ic','mm');
	foreach($this->v_type as $k) { $this->$k = array(); }
	$this->aFields = array();
	$this->ordina = array();
	$this->backuri = array();
	$this->href = array();
	$this->css = array();
	$this->empty_field = array();
	$this->sortbutton='';
	$this->unset_sort = '';
	$this->th='';
	$this->search='';
	$this->tabella='';
	$this -> tabelle = array();
	$this->colonne=0;
	$this->hidden = array();
	$this->campi_ricerca_sort = array();
	$this->inputs = array();
	$this->campi_force = array('select' => array(),'checkbox' => array(), 'text'=>array(), 'range' => array(), 'multicheck' => array());
	$this->link_sort = array();
	//$this -> js_dpt = array();
	$pref = '';
	$this->set_filtro($_REQUEST);
}

public function set_filtro($a){

	foreach($a as $k => $v){
		$pref = substr($k,0,2);
		$varname = substr($k,2,strlen($k));
		
		if($pref=='kk'){$this->kk[$varname] = $v; if(strlen(trim(strip_tags($v)))>0) {/* $this->aFields[$k]=$v;*/ $this->href[$k]=$v;}} # = USATO PER I CAMPI DESCRITTORI

		if(!empty($varname) && in_array($pref, $this->v_type) && array_key_exists($varname, $this->campi)){ # TRATTAMENTO VARIABILI SU CAMPI DB
		if($pref=='sa'){$this->sa[$this->campi[$varname]] = 1; $this->unset_sort=$k; if(strlen(trim(strip_tags($v)))>0) {$this->ordina[$this->campi[$varname]]='ASC'; $this->hidden[$k]=$v; $this->href[$k]=1;}}
		elseif($pref=='sd'){$this->sd[$this->campi[$varname]] = 1; $this->unset_sort=$k; if(strlen(trim(strip_tags($v)))>0) {$this->ordina[$this->campi[$varname]]='DESC'; $this->hidden[$k]=$v; $this->href[$k]=1;}}
		elseif($pref=='in'){$this->in[$this->campi[$varname]] = $v; if(strlen(trim(strip_tags($v)))>0) {$this->aFields[$k]=$v; $this->href[$k]=$v;}} # IS NULL
		elseif($pref=='nn'){$this->nn[$this->campi[$varname]] = $v; if(strlen(trim(strip_tags($v)))>0) {$this->aFields[$k]=$v; $this->href[$k]=$v;}} # NOT NULL
		elseif($pref=='sl'){$this->sl[$this->campi[$varname]] = $v; if(strlen(trim(strip_tags($v)))>0) {$this->aFields[$k]=$v; $this->href[$k]=$v;}} # =
		elseif($pref=='va'){$this->va[$this->campi[$varname]] = $v; if(strlen(trim(strip_tags($v)))>0) {$this->aFields[$k]=$v; $this->href[$k]=$v;}} # =
		elseif($pref=='lk'){$this->lk[$this->campi[$varname]] = $v; if(strlen(trim(strip_tags($v)))>0) {$this->aFields[$k]=$v; $this->href[$k]=$v;}} # LIKE
		elseif($pref=='mi'){$this->mi[$this->campi[$varname]] = $v; if(strlen(trim(strip_tags($v)))>0) {$this->aFields[$k]=$v; $this->href[$k]=$v;}} # MIN
		elseif($pref=='ma'){$this->ma[$this->campi[$varname]] = $v; if(strlen(trim(strip_tags($v)))>0) {$this->aFields[$k]=$v; $this->href[$k]=$v;}} # MAX
		elseif($pref=='bl'){$this->bl[$this->campi[$varname]] = $v; if(strlen(trim(strip_tags($v)))>0) {$this->aFields[$k]=$v; $this->href[$k]=$v;}} // usato per filtro id_building
		}
		if(!empty($varname)){
			if($pref=='mc'){ # MULTI CHECK
				$tmp_varname = $varname; $varname = substr($tmp_varname, 0, 2); $v = substr($tmp_varname, 2, strlen($tmp_varname));
				$this->mc[$this->campi[$varname]][] = $v;
				if(strlen(trim(strip_tags($v)))>0){
          $this->aFields[$k] = $v; 
					$this->href[$k]=$v;
				}
			}
		}
		if($pref=='js'){ # campi hidden javascript
			//print $v.' '.$varname.BR;
			if(empty($v)) $v = '0';
			$this->js[$varname] = $v; 
			$this->hidden[$k] = $v; 
			//if(strlen(trim(strip_tags($v)))>0){	$this->href[$k]=$v;	}
		} 
		
		# VARIABILE CURSORI
		if($pref == 'ic'){
			if(empty($this -> ic)){ # LO SETTO SOLO UNA VOLTA
				$this -> ic = $v; 
				$this -> hidden['ic'] = $v; 
				$this -> href['ic'] = $v;
			}
		} 
		# TRATTAMENTO VARIABILI FUNZIONALITA' (ELIMINAZIONI MULTIPLE ECC...), AMPLIABILI ALL'OCCORRENZA
		if($pref=='aa'){$this->aa[] = $varname; }
		elseif($pref=='vw'){$this->vw = $v; $this->href['vw']=$v; } # USATA SINGOLARMENTE TIPO VIEW
		elseif($pref=='kw'){$this->kw = $v; $this->href['kw']=$v; } # USATA SINGOLARMENTE KEYWORD
		elseif($pref=='rd'){$this->rd = $v; $this->href['rd']=$v; } # USATA SINGOLARMENTE RADIO
		elseif($pref=='iu'){$this->iu = $v; $this->href['iu']=$v; } # USATA SINGOLARMENTE
		elseif($pref=='cu'){$this->cu = $v; $this->href['cu']=$v; } # USATA SINGOLARMENTE
		elseif($pref=='ct'){$this->ct = $v; $this->href['ct']=$v; } # USATA SINGOLARMENTE
		elseif($pref=='ak'){$this->ak = $v; $this->href['ak']=$v; } # USATA SINGOLARMENTE
		elseif($pref=='ck'){$this->ck[] = $varname; }
		elseif($pref=='nc'){$this->nc[$varname] = $v; }
		elseif($pref=='tx'){$this->tx[$varname] = $v; }
		
		elseif($pref=='ba'){$this->ba[$varname] = $v; }
		elseif($pref=='bb'){$this->bb[$varname] = $v; }
		elseif($pref=='bc'){$this->bc[$varname] = $v; }
		elseif($pref=='bd'){$this->bd[$varname] = $v; }
		
		elseif($pref=='ln'){ # variabile del tipo lnIT105
			$this -> ln[$varname]['lang'] = substr($varname, 0, 2);
			$this -> ln[$varname]['id'] = substr($varname, 2, strlen($varname));
			$this -> ln[$varname]['value'] = $v; 
		}
		elseif($pref=='mm'){ # variabile molti a molti
			$aEl = explode('_',$varname);
			$tbl = $aEl[0];
			$tid = $aEl[1];
			
			if(!empty($aEl[2]) && $aEl[2] == 'chkhid'){
				$this -> mm[$tbl][$tid]['hid'] = 1;
			}
			else{
				$this -> mm[$tbl][$tid]['id'] = 1;
			}
		}		
	}
}

public function sort_default($a){
		if(empty($this->sa) && empty($this->sd)) $this->sd[$a] = 1; 
	}

public function where($str){ # GENERAZIONE WHERE
	$where = ''; $sort='';
	foreach($this->sl as $campo => $valore){
		if(array_key_exists($campo, $this->campi_force['select'])){ # FORCE
			$f_id_ext = stringa::tbl2id($this->campi_force['select'][$campo]);
			$valore = trim(strip_tags($valore)); if(strlen($valore)>0 && !empty($campo)) $where.= $this->tabella.".".$f_id_ext." = '$valore' AND\n";
		}
		else{ $valore = trim(strip_tags($valore)); if(strlen($valore)>0 && !empty($campo)) $where.= $this->tabella.".ID_$campo = '$valore' AND\n"; }
	}
	foreach($this -> va as $campo => $valore){$valore = trim(strip_tags($valore)); if(strlen($valore)>0 && !empty($campo)) $where .= "$campo = '$valore' AND\n"; }
	foreach($this -> lk as $campo => $valore){$valore = trim(strip_tags($valore)); if(strlen($valore)>0 && !empty($campo)) $where .= "$campo LIKE '%$valore%' AND\n";}
	foreach($this -> mi as $campo => $valore){ # CONTROLLO SE MIN E MAX SONO VALORIZZATI
		if(!empty($this -> mi[$campo]) && empty($this -> ma[$campo])){ # SOLO VALORE MINORE
			$where .= "($campo >= '".$this -> mi[$campo]."') AND\n";
		}
		elseif(empty($this -> mi[$campo]) && !empty($this -> ma[$campo])){ # SOLO VALORE MASSIMO
			$where .= "($campo <= '".$this -> ma[$campo]."') AND\n";
		}
		elseif(!empty($this -> mi[$campo]) && !empty($this -> ma[$campo])){# INTERVALLO VALORI
			$where .= "($campo >= '".$this -> mi[$campo]."' AND $campo <= '".$this -> ma[$campo]."') AND\n";
		}
	}
	foreach($this -> mc as $campo => $valore){
		$tmp_where = '';
		# $campo == CASA_TYPE
		foreach($valore as $k => $val){ # $k = posizione array numerico, $val = valore (dovrebbe essere un id)
			$val = trim(strip_tags($val));
			$f_id_ext = stringa::tbl2id($this->campi_force['multicheck'][$campo]);
			if(strlen($val)>0){	$tmp_where .= $this->tabella.".$f_id_ext = '$val' OR "; }
		}
		$tmp_where = strlen($tmp_where) > 0 ? '('.substr($tmp_where,0,strlen($tmp_where)-3).') AND ' : '';
		$where .= $tmp_where;
	}
	foreach($this -> kk as $campo => $valore){
		
		if(!empty($valore)){
			$is_field = true;
			$field_list = rs::get_fields($this -> tabella);
			
			$qFld = "SELECT
			descriptors_types.DESCRIPTORS_TABLE,
			descriptors.ID_DESCRIPTOR
			FROM
			descriptors_types
			Inner Join descriptors ON descriptors.ID_DESCRIPTORS_TYPE = descriptors_types.ID_DESCRIPTORS_TYPE
			WHERE
			descriptors.ID_DESCRIPTOR = '$valore' LIMIT 0,1";
			$rFld = rs::rec2arr($qFld);
			$id_f = stringa::tbl2id($rFld['DESCRIPTORS_TABLE']);

			# RICAVO IL NOME CAMPO
			$k0 = 'K0_'.$id_f;
			$k1 = 'K1_'.$id_f;
			$k2 = 'K2_'.$id_f;
			$k3 = 'K3_'.$id_f;
			$nome_campo = '';
			if(in_array($k0, $field_list)){ $nome_campo = $k0; }
			elseif(in_array($k1, $field_list)){ $nome_campo = $k1; }
			elseif(in_array($k2, $field_list)){ $nome_campo = $k2; }
			elseif(in_array($k3, $field_list)){ $nome_campo = $k3; }
			else{ $is_field = false; }
			
			
			//print $id_f.' '.$nome_campo.'pipo'.BR;
			
			if($is_field){
				$where.= $this->tabella.".".$nome_campo." = '$valore' AND\n";
			}		
		}
	}

	$where .= strlen($str)>0 ? $str.' AND ' : '';
	foreach($this->nn as $campo => $valore){if(!empty($campo))$where.= "$campo IS NOT NULL AND\n";}
	foreach($this->in as $campo => $valore){if(!empty($campo))$where.= "$campo IS NULL AND\n";}


	# PAROLA CHIAVE
	$qKw = '';
/* 	if(!empty($this -> kw)){
		if(empty($this -> rd) || $this -> rd != '2'){ # RICERCA PER PAROLA CHIAVE
		$aParole = stringa::prepara_dizionario($this -> kw);
		$aParole = array_unique($aParole);
		$or = '';
		foreach($aParole as $k => $v){
			$or .= "PAROLA_CHIAVE LIKE '%$v%' OR ";
		}
		if(!empty($or)) $qKw = "articles.ID_ARTICLE IN (SELECT ID_ARTICLE FROM dizionarios WHERE (".stringa::togli_ultimi($or, 3).")) AND\n";
		# pulizia kw e creazione del where campo = 'val' or campo = 'val2' ...
		}
		else{ # RICERCA PER CODICE
			$qKw = "articles.ID_ARTICLE = '".$this -> kw."' AND\n";
		}
	} */

	$where .= $sort;	
	$where = strlen($where)>0 ? 'WHERE '.$qKw.substr($where,0,strlen($where)-4) : '';
	
	foreach($this->sa as $campo => $valore){ 
		$tmpTbl = '';		
		foreach($this -> tabelle as $xtbl => $xfld){
			if($campo == $xfld) $tmpTbl = $xtbl.'.';
		}
		if(!empty($campo))$sort = "\nORDER BY ".$tmpTbl."$campo ASC ";
		$tmpTbl = '';
	}
	foreach($this->sd as $campo => $valore){ 
		$tmpTbl = '';		
		foreach($this -> tabelle as $xtbl => $xfld){
			if($campo == $xfld) $tmpTbl = $xtbl.'.';
		}
		if(!empty($campo))$sort = "\nORDER BY ".$tmpTbl."$campo DESC ";
		$tmpTbl = '';
		}
	$this -> wherenosort = $where;
	$this->where=$where.$sort;
}

public function etichette_sort($a){
	$this->etichette = $a;
	$cnt = 0;
	
	unset($this->backuri[$this->unset_sort]);
	foreach($this->etichette as $campo => $etichetta){
	$link = '';
		$this->campi_ricerca_sort[] = $campo;
		if(in_array($campo, $this->campi)){
			if(array_key_exists($campo, $this->sa)){
			unset($this->backuri['sa'.$this->chiavi[$campo]]); # PULISCO IL VECCHIO FILTRO ORDINAMENTO (NE PREVEDIAMO UNO SOLO)
			unset($this->backuri['sd'.$this->chiavi[$campo]]);
			$link = io::href(PHP_SELF, array_merge($this->backuri,array('sd'.$this->chiavi[$campo]=> '1')),$txt=$etichetta,$js='',$target='',$title='', $id='', $css="g-button desc");
			$this->th.= '<th>'.$link.'</th>';
			$this->link_sort[$campo] = $link;
			}
			elseif(array_key_exists($campo, $this->sd)){
			unset($this->backuri['sd'.$this->chiavi[$campo]]);
			unset($this->backuri['sa'.$this->chiavi[$campo]]);
			$link = io::href(PHP_SELF, array_merge($this->backuri,array('sa'.$this->chiavi[$campo]=> '1')),$txt=$etichetta,$js='',$target='',$title='', $id='', $css="g-button asc");
			$this->th.='<th>'.$link.'</th>';
			$this->link_sort[$campo] = $link;
			}
			else {
			unset($this->backuri['sd'.$this->chiavi[$campo]]);
			unset($this->backuri['sa'.$this->chiavi[$campo]]);
			$link = io::href(PHP_SELF, array_merge($this->backuri,array('sa'.$this->chiavi[$campo]=> '1')),$txt=$etichetta,$js='',$target='',$title='', $id='', $css="g-button");
			$this->th.='<th>'.$link.'</th>';
			$this->link_sort[$campo] = $link;
			}
		}
		else { # ETICHETTA SENZA FILTRO
			$this->th.='<th>'.$etichetta.'</th>';
			$this->empty_field[] = $cnt; 
		}
	$cnt++;
	}
	$this->colonne = $cnt;
}

public function ext_select($table, $vals, $blank){
	$select_html = '';
	$f_id_ext = stringa::tbl2id($table);
	$aTmp = array_flip($this->campi_force['select']);
	if(array_key_exists($table,$aTmp)){
		$field = $aTmp[$table];
	}
	else{ $field = stringa::tbl2field($tabella); }

	$blank = empty($blank) ? '' : '<option></option>';
	$flds = rs::inMatrix("SELECT * FROM $table ORDER BY $field ASC");
	$select_html.='<select name="'.$f_id_ext.'">'.$blank;
	foreach($flds as $rec){
	$selected = '';	
	if(array_key_exists($field, $this->sl)){}
	if($vals[$f_id_ext]==$rec[$f_id_ext]) $selected = ' selected="selected"';

	 $select_html.='<option value="'.strcut($rec[$f_id_ext],'',15).'"'.$selected.'>'.$rec[$field].'</option>'."\n";
	}
	$select_html.="</select>\n";
	return $select_html;
}

public function campi_ricerca(){ # GENERA I CAMPI FILTRO (SELECT, CHECKBOX, TEXT)
	$table_list = rs::inMatrix('SHOW TABLES FROM '.DBNAME);
	$cnt=0;
	foreach($table_list as $a => $b){ foreach($b as $c) {$aTables[] = $c;}};
	foreach($this->campi_ricerca_sort as $campo){

	//if(in_array($campo,$this->campi_force['select'])) print $campo.BR;
	//echo $campo.BR;
		$css = array_key_exists($campo, $this->css) ? ' class="'.$this->css[$campo].'"' : ' class="input"';
		
		if(array_key_exists($campo, $this->campi_force['select'])){ # SELECT
			
			$selected = '';
			$f_id_ext = stringa::tbl2id($this->campi_force['select'][$campo]);
			$flds = rs::inMatrix("SELECT * FROM ".$this->campi_force['select'][$campo]." ORDER BY $campo ASC");
			$this->ricerca.='<th><select name="sl'.$this->chiavi[$campo].'"><option></option>';
			foreach($flds as $rec){
			$selected = '';
				if(array_key_exists($campo, $this->sl)){ if($this->sl[$campo]==$rec[$f_id_ext]) $selected = ' selected="selected"';}
				$this->ricerca.='<option value="'.strcut($rec[$f_id_ext],'',15).'"'.$selected.'>'.$rec[$campo].'</option>'."\n";
			}
			$this->ricerca.="</select></th>\n";
		}
		
		elseif(in_array(strtolower($campo).'s', $aTables) && !array_key_exists($campo,$this->campi_force['text'])){ # SELECT
			$tbl = stringa::field2table($campo);
			
 			$qSel = "SELECT ID_".$campo.", $campo FROM $tbl ORDER BY $campo ASC";
			$flds = rs::inMatrix($qSel);
			$this->ricerca.='<th><select name="sl'.$this->chiavi[$campo].'"'.$css.'><option></option>';
			foreach($flds as $rec){
				$selected = '';
				if(array_key_exists($campo, $this->sl)){ if($this->sl[$campo]==$rec['ID_'.$campo]) $selected = "selected=\"selected\"";}
				$this->ricerca.='<option value="'.$rec['ID_'.$campo].'"'.$selected.'>'.$rec[$campo].'</option>'."\n";
			}
			$this->ricerca.="</select></th>\n";
		}
		elseif(substr($campo,0,3)=='IS_'){ # CHECKBOX
			$checked='';
			if(array_key_exists($campo, $this -> va)){$checked = " checked=\"checked\"";
			
			}
			$this->ricerca.='<th><input name="va'.$this->chiavi[$campo].'" type="checkbox" class="checkbox" value="1"'.$checked.' /></th>';
			
		}
		else{ # TEXT
			$value='';
			if(array_key_exists($campo, $this->lk)){$value = ' value="'.$this->lk[$campo].'"';}
			if(in_array($campo, $this->campi))$this->ricerca.='<th><input name="lk'.$this->chiavi[$campo].'" type="text"'.$value.' maxlength="18"'.$css.' /></th>';
		}
		if(in_array($cnt,$this->empty_field)){$this->ricerca.='<th></th>';}
	$cnt++;
	}
}

public function inputs(){ # GENERA I CAMPI FILTRO (SELECT, CHECKBOX, TEXT)
	$table_list = rs::inMatrix('SHOW TABLES FROM '.DBNAME);
	//$cnt=0;
	foreach($table_list as $a => $b){ foreach($b as $c) {$aTables[] = $c;}};
	foreach($this->campi_ricerca_sort as $campo){
		//print $campo.'<br />';
		$css = array_key_exists($campo, $this->css) ? ' class="'.$this->css[$campo].'"' : ' class="input"';
		if(array_key_exists($campo, $this->campi_force['select'])){ # SELECT
			$selected = '';
			$f_id_ext = stringa::tbl2id($this->campi_force['select'][$campo]);
			$flds = rs::inMatrix("SELECT * FROM ".$this->campi_force['select'][$campo]." ORDER BY $campo ASC");
			$this->inputs[$campo]='<select name="sl'.$this->chiavi[$campo].'"><option></option>';
			foreach($flds as $rec){
			$selected = '';
				if(array_key_exists($campo, $this->sl)){ if($this->sl[$campo]==$rec[$f_id_ext]) $selected = ' selected="selected"';}
				$this->inputs[$campo].='<option value="'.strcut($rec[$f_id_ext],'',15).'"'.$selected.'>'.$rec[$campo].'</option>'."\n";
			}
			$this->inputs[$campo].="</select>\n";
		}
		elseif(array_key_exists($campo, $this->campi_force['range'])){ # MIN MAX
			$valmi = ''; $valma = '';
			if(array_key_exists($campo, $this->mi)){ $valmi = ' value="'.$this->mi[$campo].'"'; }
			if(array_key_exists($campo, $this->ma)){ $valma = ' value="'.$this->ma[$campo].'"'; }
			if(in_array($campo, $this->campi)) $this->inputs[$campo] = '<span class="lbl">da:</span> <input name="mi'.$this->chiavi[$campo].'" type="text"'.$valmi.' maxlength="10"'.$css.' /> <span class="lbl">a:</span> <input name="ma'.$this->chiavi[$campo].'" type="text"'.$valma.' maxlength="10"'.$css.' />';
		}
		elseif(array_key_exists($campo, $this->campi_force['multicheck'])){ # MULTI CHECK
			$checked = '';
			$f_id_ext = stringa::tbl2id($this -> campi_force['multicheck'][$campo]);
			$flds = rs::inMatrix("SELECT * FROM ".$this->campi_force['multicheck'][$campo]." ORDER BY $campo ASC");
			$this -> inputs[$campo] = '';
			foreach($flds as $rec){
				$checked = '';
				if(array_key_exists($campo, $this -> mc)){
					$vals = array();
					foreach($this -> mc[$campo] as $p => $c){
						$vals[] = $c;
					}
					if(in_array($rec[$f_id_ext], $vals)) { $checked = ' checked="checked"'; } //print $rec[$f_id_ext].BR;
				}
				$this -> inputs[$campo].= '<span class="lbl">'.stringa::uppercase_first($rec[$campo]).'</span> <input name="mc'.$this->chiavi[$campo].$rec[$f_id_ext].'" type="checkbox" value="1"'.$checked.' class="checkbox" /> ';
			}
		}
		
		elseif(in_array(strtolower($campo).'s', $aTables) && !array_key_exists($campo,$this->campi_force['text'])){ # SELECT
			$flds = rs::inMatrix("SELECT * FROM ".strtolower($campo)."s ORDER BY $campo ASC");
			
			$this->inputs[$campo] = '<select name="sl'.$this->chiavi[$campo].'"'.$css.'><option></option>';
			foreach($flds as $rec){
				$selected = '';
				if(array_key_exists($campo, $this->sl)){ if($this->sl[$campo]==$rec['ID_'.$campo]) $selected = "selected=\"selected\"";}
				$this->inputs[$campo].='<option value="'.$rec['ID_'.$campo].'"'.$selected.'>'.$rec[$campo].'</option>'."\n";
			}
			$this->inputs[$campo].="</select>\n";
		}
		elseif(substr($campo,0,3)=='IS_'){ # CHECKBOX
			$checked='';
			if(array_key_exists($campo, $this->va)){$checked = " checked=\"checked\"";}
			$this->inputs[$campo]='<input name="va'.$this->chiavi[$campo].'" type="checkbox" value="1"'.$checked.' />';
		}
		else{ # TEXT
			$value='';
			if(array_key_exists($campo, $this->lk)){$value = ' value="'.$this->lk[$campo].'"';}
			if(in_array($campo, $this->campi))$this->inputs[$campo]='<input name="lk'.$this->chiavi[$campo].'" type="text"'.$value.' maxlength="18"'.$css.' />';
		}
	}
}

function link_totali($id, $vaid, $lbl,  $var){
  $cq = $this -> q_rv." AND $id = '$vaid'";
  $rn = rs::rec2arr($cq);
  
  if(!array_key_exists($var, $this -> aFields)){
  //print $rn['TOTS'];
  $this -> cerca .=  io::href(PHP_SELF, $val=array_merge($this->backuri,array($var => $vaid)), $text=$lbl.'('.$rn['TOTS'].')', $js='', $target="", $title=$lbl, $id="", $class="").BR;
  }
  else{
    if($vaid == $this -> aFields[$var]){
    $tmpBackUri = $this->backuri;
    $tmpBackUri[$var] = NULL; unset($tmpBackUri[$var]);
    $this -> togli .=  $lbl.'['.io::href(PHP_SELF, $val=array_merge($tmpBackUri), $text='X', $js='', $target="", $title='Rimuovi '.$lbl, $id="", $class="red").']'.BR;
    }
  }
}

function link_totali2($id, $vaid, $lbl, $var){ # CAMPI FLAG
  $cq = $this -> q_rv." AND $id = '$vaid'";
  $rn = rs::rec2arr($cq);
  
  if(!array_key_exists($var.$vaid, $this -> aFields)){
  //print $rn['TOTS'];
  $this -> cerca .=  io::href(PHP_SELF, $val=array_merge($this->backuri,array($var.$vaid => '1')), $text=$lbl.'('.$rn['TOTS'].')', $js='', $target="", $title=$lbl, $id="", $class="").BR;
  }
  else{
    if($vaid == $this -> aFields[$var.$vaid]){
    $tmpBackUri = $this->backuri;
    $tmpBackUri[$var.$vaid] = NULL; unset($tmpBackUri[$var.$vaid]);
    $this -> togli .=  $lbl.'['.io::href(PHP_SELF, $val=array_merge($tmpBackUri), $text='X', $js='', $target="", $title='Rimuovi '.$lbl, $id="", $class="red").']'.BR;
    }
  }
}

function link_totali3($fld, $lbl, $min, $max, $minvar, $maxvar){
  $cq = $this -> q_rv." AND ($fld >= '$min' AND $fld <= '$max')";
  $rn = rs::rec2arr($cq);
  
  if(!array_key_exists($minvar, $this -> aFields)){
    if(!empty($rn['TOTS'])){
    $this -> cerca .=  io::href(PHP_SELF, $val=array_merge($this->backuri,array($minvar => $min, $maxvar => $max)), $text=$lbl.'('.$rn['TOTS'].')', $js='', $target="", $title=$lbl, $id="", $class="").BR;
    }
  }
  else{
  //print 'min '.$min.' aFields '.$this -> aFields[$minvar].BR;
    if($min == $this -> aFields[$minvar] )
    {
    $tmpBackUri = $this->backuri;
    $tmpBackUri[$minvar] = NULL; unset($tmpBackUri[$minvar]);    
    $tmpBackUri[$maxvar] = NULL; unset($tmpBackUri[$maxvar]);
    $this -> togli .=  $lbl.'['.io::href(PHP_SELF, $val=array_merge($tmpBackUri), $text='X', $js='', $target="", $title='Rimuovi '.$lbl, $id="", $class="red").']'.BR;
    }
  }
}

public function filtri_link($qMain, $aOrdine){
	# DEVE RESTITUIRE ARRAY CON I BLOCCHI DEI LINK DI AGGIUNTA FILTRO E DI RIMOZIONE FILTRO
	# Dove:
	# Italia (x)
	# Friuli Venezia Giulia (x)
	# =========================
	# Prezzo
	# fino a 10,00€ (15)
	# da 10,00€ a 20,00€ (46)
	#
	# NON DEVONO COMPARIRE LINK SENZA RECORD IN DB. ES: Trieste (0)
	
	# PARAMETRI:
	# UN ARRAY CON LA LISTA DEI CAMPI DESCRITTORI INTERDIPENDENTI O MENO (Stati > Regioni > Province > Comuni, OPPURE Posizione, Tipo arredo, Riscaldamento)
	# UN ARRAY CON I CAMPI PRESENTI IN DB DA AGGIUNGERE COME FILTRO(IS_GIARDINO, IS_CLIMATIZZATO ECC..) UTILITA' NON RICHIESTA, DA SVILUPPARE SUCESSIVAMENTE
	
	$qMain1 = stringa::bfw($qMain, "FROM", "WHERE");
	$qMain2 = stringa::bfw($qMain, "WHERE", "ORDER");
	
	if(!empty($qMain2)) $qMain2 = ' AND '.$qMain2.' '; # COSTRUZIONE STRINGA QUERY

	
	$field_list = rs::get_fields($this -> tabella);
	$aRet = array();
	$aRet['where'] = '';
	$aRet['lnk_att'] = '';
	$aRet['lnk_dis'] = '';
	
	$q = "SELECT * FROM descriptors_types";
	$aType = rs::inMatrix($q);
	$aIdType = arr::semplifica($aType, 'DESCRIPTORS_TABLE');
	$aIdType2 = arr::semplifica($aType, 'ID_DESCRIPTORS_TYPE');
	$aIdType3 = arr::semplifica($aType, 'ID_DESCRIPTORS_TYPE_SELF');
	
	if(array_key_exists('id_rec', $_REQUEST) && !empty($_REQUEST['id_rec'])){ # DATI RECORD
		$id_record = $_REQUEST['id_rec'];
		$id_name = stringa::tbl2id($this -> tabella);

		$qMain = "SELECT * FROM ".$this -> tabella." WHERE $id_name='".$id_record."'";
		$rMain = rs::rec2arr($qMain);
	}
	# IMPLEMENTARE UN ORDINAMENTO DEI kk IN MODO DA FORNIRLO SEMPRE UGUALE. SI POTREBBE PASSARE COME PARAMETRO UN ARRAY GENERICO E CONFRONTARLO COL KK E ORDINARLO DI CONSEGUENZA
	# IN QUESTO ARRAY POSSIAMO ANCHE INSERIRE IL NOME DELL'ETICHETTA GENERICA RELATIVA (kkstatis => Localizzazione).
	

	
/*	$aVarMod = array(	'2' => array('jscasa1s'),
						'3' => array('jsves','jsmta1s','jsmtm1s'),
						'4' => array('jslv1s'),
						'5' => array('jscat1s'),
						'6' => array('jsna1s')
					); 
*/	
	
	$aTmp = $this -> kk;
	
	//print_r($this -> kk);
	
	$this -> kk = array();
	foreach($aOrdine as $var => $etichetta){
		if(array_key_exists($var, $aTmp)){
			$this -> kk[$var] = $aTmp[$var];
		}
	}
	
	$main_li_dis = ''; $tmp_li_dis = '';
	foreach($this -> kk as $t => $id){
		if(empty($id_precedente)){ $id_precedente = NULL; }
		$id_iniziale = $id;
		$flg_set_id = false;
		if(empty($id)) $flg_set_id = true;
		
		$cnt = 0; $default = '0';
		$id_t = 'ID_DESCRIPTOR';

		# INIZIALIZZO I CAMPI (UPDATE OPPURE INSERT)
		if(empty($id)){
			$where = "WHERE descriptors.ID_DESCRIPTOR_SELF IS NULL AND descriptors_types.DESCRIPTORS_TABLE = '".$t."' LIMIT 0,1";
		}
		else{
			$where = "WHERE descriptors.ID_DESCRIPTOR_SELF = '$id' LIMIT 0,1";
		}
		do{ 
			$backUri = $this -> backuri;
			$is_lnk = false;
			if($cnt > 0){ $where = "WHERE descriptors.ID_DESCRIPTOR = '$id'"; }
			
			$q = "SELECT descriptors.*,
			descriptors_types.*
			FROM
			descriptors
			Left Join descriptors_types ON descriptors.ID_DESCRIPTORS_TYPE = descriptors_types.ID_DESCRIPTORS_TYPE
			$where";
		
			$rec = rs::rec2arr($q);
			
			if(empty($rec['ID_DESCRIPTOR'])){ # RINNOVO IL $rec E LO RENDO FUNZIONALE
				$rec = array();
				$q = "SELECT descriptors.*,
				descriptors_types.*
				FROM
				descriptors
				Left Join descriptors_types ON descriptors.ID_DESCRIPTORS_TYPE = descriptors_types.ID_DESCRIPTORS_TYPE
				WHERE descriptors.ID_DESCRIPTOR = '$id' LIMIT 0,1";

				$rec = rs::rec2arr($q);
			}
			$id_f = stringa::tbl2id($rec['DESCRIPTORS_TABLE']);
			if($cnt == 0){
				$is_lnk = true;
				$ctrl_loop = 0;
				$ramo = $rec['DESCRIPTORS_TABLE'];

				# NOME CAMPO PER I LINK (RAMO PIù ALTO)
				while(!empty($aIdType[$ramo]['ID_DESCRIPTORS_TYPE_SELF']) && $ctrl_loop < 20){ # ok
					$ramo = $aIdType2[$aIdType[$ramo]['ID_DESCRIPTORS_TYPE_SELF']]['DESCRIPTORS_TABLE'];
					$ctrl_loop++;
				}
				$varname = 'kk'.$ramo;
			}		
			
			# RICAVO IL NOME CAMPO DB
			$k0 = 'K0_'.$id_f;
			$k1 = 'K1_'.$id_f;
			$k2 = 'K2_'.$id_f;
			$k3 = 'K3_'.$id_f;
			$nome_campo = '';
			if(in_array($k0, $field_list)){ $nome_campo = $k0; }
			elseif(in_array($k1, $field_list)){ $nome_campo = $k1; }
			elseif(in_array($k2, $field_list)){ $nome_campo = $k2; }
			elseif(in_array($k3, $field_list)){ $nome_campo = $k3; }
			else{ print 'Nessun campo K* valorizzato '.$id_f;}
			
			# QUERY PER I VALORI DEI LINK
			if(empty($rec['ID_DESCRIPTOR_SELF'])){
				$self_where = " AND ID_DESCRIPTOR_SELF IS NULL";
			}
			else{
				$self_where = " AND ID_DESCRIPTOR_SELF = '".$rec['ID_DESCRIPTOR_SELF']."'";
			}
			$f_descriptor = "DESCRIPTOR_".LANG_DEF;
			
			$qDpt = "SELECT ID_DESCRIPTOR, ID_DESCRIPTOR_SELF, $f_descriptor FROM descriptors WHERE ID_DESCRIPTORS_TYPE = '".$rec['ID_DESCRIPTORS_TYPE']."'$self_where ORDER BY RANK ASC, $f_descriptor ASC";
			
			$rDpt = rs::inMatrix($qDpt);
			if($flgUsed = arr::srcArrKey(array($id_iniziale), $rDpt, 'ID_DESCRIPTOR')){ # L'ID DEL RAMO SCELTO
				$aRet['where'] .= 'articles.'.$nome_campo."='$id' AND ";
			}
			
			if( $cnt == 0 || empty($rec['ID_DESCRIPTOR'])){ # ENTRATA NEL LIVELLO PIù ALTO DEI DESCRITTORI
				# STAMPA DELLA LISTA (LINK NON SCELTI)
				$tmp_li_att = ''; 
				foreach($rDpt as $k => $v){ 
					$backUri = $this -> backuri;
					$idDpt = $v['ID_DESCRIPTOR'];
					$idDptSelf = $v['ID_DESCRIPTOR_SELF'];
					$vaDpt = $v[$f_descriptor];
					// print 'rec '.$id.' id '.$idDpt.' idself '.$idDptSelf.BR;
					if($id == $idDpt){ # SONO NELLO STESSO LIVELLO DI QUELLO SCELTO E FORZO LA CREAZIONE DEL LINK DI ELIMINAZIONE
						$lable = io::lable(array($nome_campo => $rec['ID_DESCRIPTOR']), $nome_campo, true, 0);
						$idDpt = $rec['ID_DESCRIPTOR'];
						$idDptSelf = $rec['ID_DESCRIPTOR_SELF'];
						$backUri[$varname] = $idDptSelf;
						$tmp_li_dis = '<li class="elimina_ricerca">'.io::ahref(FILENAME.'.php', $backUri, $lable, $class="").'</li>'."\n".$tmp_li_dis;
						$tmp_li_att = '';
						break;
					}
					
					
					$qCount = "SELECT COUNT(ID_ARTICLE) AS TOTS, articles.$nome_campo  FROM ".$qMain1." WHERE articles.$nome_campo = '$idDpt'".$qMain2."GROUP BY articles.$nome_campo";
					$rCount = rs::rec2arr($qCount);
					if(!empty($rCount['TOTS'])){
						$lable = io::lable(array($nome_campo => $idDpt), $nome_campo, false, 0);
						if($is_lnk === true){ # LINK
							$backUri[$varname] = $idDpt;
							$tmp_li_att = '<li>'.io::ahref(FILENAME.'.php', $backUri, $lable.' ('.$rCount['TOTS'].')', $class="")."</li>\n".$tmp_li_att;
						}
					}
				}
				if(!empty($tmp_li_att)){
					$aRet['lnk_att'] .= '<li><h2>'.$aOrdine[$t].'</h2>
						<ul>'.$tmp_li_att.'
						</ul>
					</li>';
				}
			}
			else{
				$lable = io::lable(array($nome_campo => $rec['ID_DESCRIPTOR']), $nome_campo, false, 0);
				$idDpt = $rec['ID_DESCRIPTOR'];
				$idDptSelf = $rec['ID_DESCRIPTOR_SELF'];
				$backUri[$varname] = $idDptSelf;
				//if($cnt == 0) $aRet['lnk_dis'] .= '<li>'.$aOrdine[$t].'<li>'."\n";
				$tmp_li_dis = '<li class="elimina_ricerca">'.io::ahref(FILENAME.'.php', $backUri, $lable, $class="").'</li>'."\n".$tmp_li_dis;
			}
			$id_precedente = $id;
			$id = $rec['ID_DESCRIPTOR_SELF'];
			$cnt++;
		}
		while($rec['ID_DESCRIPTOR_SELF'] != NULL || $cnt > 100);
		if(!empty($tmp_li_dis)){
			$main_li_dis .= '<li class="sezione">'.$aOrdine[$t].'</li>'.$tmp_li_dis;
			$tmp_li_dis = '';
		}
	}
	//$aRet['lnk_dis'] = $tmp_li_dis;
	//print $t;

	$qPrewhere = stringa::lfw($qMain, "WHERE");
	$qPostwhere = stringa::rfw($qMain, "WHERE");
	
	if(!empty($this -> kw)){
		$tmpBackuri = $this -> backuri;
		unset($tmpBackuri['kw']);
		$tmp_li_dis = '<li class="elimina_ricerca">'.io::ahref(FILENAME.'.php', $tmpBackuri, $this -> kw, $class="").'</li>'."\n";
		$main_li_dis = '<li class="sezione">Parola chiave</li>'.$tmp_li_dis.$main_li_dis;
	} 

	$aRet['where'] = $qPrewhere.'WHERE '.$aRet['where'].$qPostwhere;
	
	if(!empty($main_li_dis))	$aRet['lnk_dis'] = '<li class="tua_ricerca">La tua ricerca</li>'."\n".$main_li_dis;
	return $aRet;
}

}
?>