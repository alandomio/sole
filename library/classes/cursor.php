<?php
# ic = PAGINA ATTIVA
class cursor{

function __construct($q, $offset){
	global $MYFILE;
	$this -> MYFILE = $MYFILE;
	$this -> q = $q;
	$this -> passo = 10;
	$this -> set_offset($offset);
	$this -> backuri = $_GET;
	$this -> mode = 'full'; # full, normal, simple
	
	$this -> ic = !empty($_GET['ic']) ? $_GET['ic'] : 1;
	$this -> arr = array();
	$this -> player = '';
	$this -> txt = array('back' => '&lsaquo;', 'forward' => '&rsaquo;', 'first' => '&laquo;', 'last' => '&raquo;');
	$this -> exe_q();
	$this -> set_html();
}

public function set_offset($i){
	$this -> offset = $i;
	if(empty($this -> offset)) $this -> offset = 1; # EVITA LA DIVISIONE PER ZERO
}

public function set_passo($i){
	$this -> passo = $i;
	if(empty($this -> passo)) $this -> passo = 1; # EVITA LA DIVISIONE PER ZERO
	$this -> set_html();
}

private function exe_q(){
	$this -> rs = rs::inMatrix($this -> q);
	$this -> t_recs = count($this -> rs);
	$this -> t_curs = ceil($this -> t_recs/$this -> offset);
	$this -> limit = " LIMIT ".(($this -> ic * $this -> offset)-$this -> offset).",".$this -> offset."";
}

public function set_mode($s){	# full, normal, simple
	$this -> mode = $s;
	$this -> set_html();
}

private function get_arr(){
	$a = array('first' => array(), 'back_one' => array(), 'cursors' => array(), 'next_one' => array(), 'last' => array());
	
	$bid = $this -> backuri; $bid['ic'] = 1;
	$a['first'][] = io::a($this -> MYFILE -> file, $bid, $this -> txt['first'], array());
	$bid = $this -> backuri; $bid['ic'] = $this -> ic - 1;
	$a['back_one'][] = io::a($this -> MYFILE -> file, $bid, $this -> txt['back'], array());
	
 	$i = $this -> ic - $this -> passo;
	$i = $i < 1 ? 1 : $i;
	$f = $this -> ic + $this -> passo;
	$f = $f > $this -> t_curs ? $this -> t_curs : $f;
	
	$bid = $this -> backuri; # VARIABILI GET TEMPORANEE ALLE QUALI CAMBIO $ic
	for($ic = $i; $ic <= $f; $ic++){
		$bid['ic'] = $ic;
		$opt = array();
		if($ic == $this -> ic){ $opt['class'] = 'current'; }
		$a['cursors'][] = io::a($this -> MYFILE -> file, $bid, $ic, $opt);
	}
	
	$bid = $this -> backuri; $bid['ic'] = $this -> ic + 1;
	$a['next_one'][] = io::a($this -> MYFILE -> file, $bid, $this -> txt['forward'], array());
	$bid = $this -> backuri; $bid['ic'] = $this -> t_curs;
	$a['last'][] = io::a($this -> MYFILE -> file, $bid, $this -> txt['last'], array());
	
	if($this -> ic == 1){ unset($a['first'][0]); unset($a['back_one'][0]); }
	if($this -> ic == $this -> t_curs){ unset($a['next_one'][0]); unset($a['last'][0]);}
	if($this -> mode == 'normal'){unset($a['first'][0]); unset($a['last'][0]);}
	if($this -> mode == 'simple'){unset($a['first'][0]); unset($a['back_one'][0]); unset($a['next_one'][0]); unset($a['last'][0]);}
	$this -> arr = array_merge($a['first'], $a['back_one'], $a['cursors'], $a['next_one'], $a['last']);
}

public function set_html(){
	$this -> get_arr();
	$this -> player = '';	
	foreach($this -> arr as $k => $v){
		$this -> player .= $v;
	}
	$this -> player = '<div class="pagination">'.$this -> player.'</div>';
}

}
?>