<?php
include_once 'init.php';
$user = new autentica($aA2);
$user -> login_standard();
include_once stringa::get_conffile($MYFILE -> filename);

/*
 * ricava i valori salvati in db
 * */
$convs=array('EP'=>'ba', 'CO2'=>'bb', 'EURO'=>'bc');

$q="SELECT * FROM federations_conversions WHERE ID_FEDERATION={$id}";
$rows=rs::inMatrix($q);
$aValues=array();
foreach($rows as $row){
	foreach($convs as $k=>$tmp){
		$aValues[$row['ID_METERTYPE']][$k]=$row[$k];
	}
}

/*
 * ricava la lista dei tipi di energia
 * */
$q = "SELECT * FROM metertypes";
$rMtypes = rs::inMatrix($q);
$rMtypes = arr::semplifica($rMtypes, 'ID_METERTYPE');

/*
 * crud
 * */
if( array_key_exists('cmd', $_POST)){
	$rs=new ordinamento();
	$id = prepare4sql($_POST['id']);	
	
	/*
	 * inizializza la tabella federations_convesions controllando che esista un record per ogni tipologia di energia ed eventualmente lo inserisce
	 * */
	$q='';
	foreach($rMtypes as $id_energy => $tmp){
		if( ! array_key_exists($id_energy, $aValues)){
			$q.="({$id}, {$id_energy}), ";
		}
	}
	if( ! empty($q)){
		$q=substr($q, 0, -2);
		$q="INSERT INTO federations_conversions (ID_FEDERATION, ID_METERTYPE) VALUES {$q} ";
		mysql_query($q);
	}
	
	foreach($convs as $field=>$var){
		foreach($rs->$var as $k =>$v ){
			$v = str_replace(',', '.', $v);
			
			/*
			 * permette l'inserimento del valore zero
			 * */
	 		if( is_numeric($v) && $v >= 0){
				$v;	 			
	 		} else {
	 			$v='NULL';
	 		}
	 		$q="UPDATE federations_conversions SET {$field}=$v WHERE ID_FEDERATION={$id} AND ID_METERTYPE={$k}";
	 		mysql_query($q);
		}
	}
	
 	$backUri = array_merge($rs->href, array('id'=>$id, 'crud'=>'upd'));
 	io::headto(FILE, $backUri);
}

$sub_menu = '';
foreach($sub_nav as $k => $arr){
	$href_to='';
	if(in_array(FILENAME.'.php',$arr)) { 
		$sub_menu.='<div class="menu_dis"><span>'.$k.'</span></div>';
	}
	elseif($crud=='ins'){
		$sub_menu.='<div class="menu_att_dis">'.$k.'</div>'; 
	} else {
		$href_to = io::a($arr[0], array_merge($_GET,array('crud'=>'upd')), $k, array('title' => $title=strtolower($k)));
		$sub_menu.='<div class="menu_att"><span>'.$href_to.'</span></div>';
	}
}
$sub_menu = '<div id="menu_bar">'.$sub_menu.'</div>';

$backUri = $_GET;
$backUri['id'] = NULL; unset($backUri['id']);
$backUri['crud'] = NULL; unset($backUri['crud']);

$href_lista = '';
if($user -> idg == 1){
	$href_lista = io::a($scheda->file_l, $backUri, LISTA, array('title' => LISTA, 'class' => 'g-button'));
}

/*
 * costruzione del corpo tabella
 * */
ob_start();
$colspan=count($convs)+2;

$energy = array();
foreach($rMtypes as $k => $type){
	// acqua non ha bisogno della conversione
// 	if($type['ID_METERTYPE'] == 5){
// 		continue;
// 	}
// jira SOLEDUE-367

	$energy[$type['ID_METERTYPE']] = '<span style="float:right;">1 '.$type['UNIT'].' = </span>'.$type['METERTYPE_'.LANG_DEF].':';
}

$th='<th width="260"></th>';
foreach($convs as $conv=>$field_prefix){
	
	switch ($conv){
		case 'EP':
			$tmp=__('Energia Primaria');
			break;
		case 'CO2':
			$tmp=__('CO2 Equivalente');
			break;
		case 'EURO':
			$tmp=__('Costo');
			break;
	}
	
	$th.='<th width="300">'.$tmp.'</th>';
}
$th.='<th></th>';

$a_suffix=array(	'EP'=>	' kWhEP',
					'CO2'=>	' kg CO<sub>2</sub>-eq',
					'EURO'=>' &euro;'
				);

$i=0;
foreach($energy as $id_energy => $label){
	$class = $i%2==1 ? ' class="contrast"' : '';
	?>
	<tr<?=$class?>>
		<td valign="top"><?=$label?></td>
		<?
		foreach($convs as $conv=>$field_prefix){ 
			
			$value='';
			if( array_key_exists($id_energy, $aValues) && array_key_exists($conv, $aValues[$id_energy])){
				if( $aValues[$id_energy][$conv]>=0){
					$value=$aValues[$id_energy][$conv];
				}
			}

			$field_name=$field_prefix.$id_energy;

			$input = new io();
			$input->type = 'text';
			$input->val = $value;
			$input->id = $field_name;
			$input->css = 'conversion';

		?>
		<td><?=$input->set($field_name)?><?=$a_suffix[$conv]?></td>
		<?
		}
		?>
		<td></td>
	</tr>
	<?
	$i++;
}
$tr = ob_get_clean();

include_once HEAD_AR;
print $sub_menu;
?>
<form action="<?=FILE?>" method="post">
	<?=request::hidden($_GET)?>
	<input type="hidden" name="cmd" value="crud">
 <table class="list">    
	<tr class="bg">
		  <th colspan="<?=$colspan?>"><?=$href_lista?> 
		  	<input type="submit" value="<?=SAVE?>" class="g-button g-button-yellow" /> 
		  </th>
	</tr>    
	<? 
	/*
	 * stampa l'intestazione del record
	 * */
	if($crud=='upd'){
		print'<tr class="yellow"><td colspan="'.$colspan.'"><div class="table_cell"><strong>'.$etichetta.'</strong></div></td></tr>';
  }

	?>
	<tr class="base"><?=$th?></tr>
	<?=$tr?>
<tr>
		<th colspan="<?=$colspan?>"></th>    
</tr>  
</table>
</form>
<?php
include_once FOOTER_AR;
?>