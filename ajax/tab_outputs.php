<?php
include_once '../init.php';

function get_meters_by_idbuilding($id_bld){
	# TUTTI I CONTATORI DI UN EDIFICIO
	$q = "SELECT
consumptions.*,
flats.*
FROM
consumptions
Inner Join flats ON consumptions.ID_FLAT = flats.ID_FLAT
WHERE
flats.ID_BUILDING =  '$id_bld'
ORDER BY CODE_FLAT ASC, ID_CONSUMPTION DESC
"; 
	
	$r = rs::inMatrix($q);
	
/*	$a = array(); # $a[$id_flat][$id_meter] => $aRecord
	foreach($r as $k => $v){
		$a[$v['ID_FLAT']][$v['D_']] = $v;
	}
*/	//return $a;	
	return $r;
}

function get_flats_by_idbuilding($id){
	$q = "SELECT * FROM flats WHERE ID_BUILDING = '$id'";
	
	

}

function get_last_measures_by_idmeter($aM){
	$ret = array('F1' => 0, 'F2' => 0, 'F3' => 0);
	
	$q = "SELECT *, TO_DAYS(D_MEASURE) AS GIORNI FROM measures WHERE ID_METER = '".$aM['ID_METER']."' AND IS_DEL = '0' ORDER BY D_MEASURE DESC LIMIT 0, 2";
	$r = rs::inMatrix($q);
	$ret['recs'] = $r; 
	
	# TRATTO ARRAY
	$i = 1; $vals = array();
	$verify = (count($r) < 2) ? false : true;
	
	foreach($r as $k => $v){ # NORMALIZZO L'ARRAY
		$vals[$i] = $v;
		$i ++;
	}	
	
	# CALCOLO CONSUMI
	if($aM['ID_METERTYPE'] == 2){ # CONTATORE MULTIORARIO INPUT / OUTPUT
		$aMis = array('F1', 'F2', 'F3');
	}
	else{
		$aMis = array('F1');
	}
	
	$alt = 'NULL';
	
	$ret['inputs'] = '';
	foreach($aMis as $a => $b){
		if($verify){
			$mis1 = $vals[1][$b];
			$mis2 = $vals[2][$b];

			if(is_numeric($mis1) && is_numeric($mis2)) $cons = $mis1 - $mis2;
			else $cons = $alt;
			$ret[$b] = '<input type="text" value="'.$cons.'" class="consumo" id="'.$b.$aM['ID_METER'].'" name="'.$aM['ID_METER'].'" readonly="readonly" />';
		}
		else{
			$ret[$b] = '<input type="text" value="'.$alt.'" class="consumo" id="'.$b.$aM['ID_METER'].'"  name="'.$aM['ID_METER'].'" readonly="readonly" />';
		}
		$ret['inputs'] .= $ret[$b].BR;
	}
	
	
	if($verify){
		$cons = $vals[1]['GIORNI'] - $vals[2]['GIORNI'];
		$ret['days'] = '<input type="hidden" id="days" value="'.$cons.'" />';
	}
	else{
		$ret['days'] = '<input type="hidden" id="days" value="0" />';;
	}
	$ret['inputs'] .= $ret['days'];
	return $ret;
}


function mk_validation($id_bld){
	//$aFunctions = array('pvFull','pvM2','npvM2','npvFullCorr','npvM2Corr');

	$ret['html'] = '';


	$rs = get_meters_by_idbuilding($id_bld); # FLATS E METERS
	
	$ret['html'] .= '<table class="dark list">
	<tr>
	<th width="200" ><strong>Appartamento</strong></td>
	<th width="90">Occupato</th>
	<th width="90">PV/Full</th>
	<th width="90">PV/m2</th>
	<th width="90">NPV/m2</th>
	<th width="90">NPV/Full corr.</th>
	<th width="90">NPV/m2 corr.</th>
	</tr>';
	
	$idf = ''; $cnt = 0;
	foreach($rs as $k => $rec){
		if(empty($idf)) $idf = $rec['ID_FLAT'];
		else{
			if($idf != $rec['ID_FLAT']){
				$idf = $rec['ID_FLAT'];
				$cnt ++;
			}
		}
		
		$class = $cnt % 2 == 0 ? '' : ' class="contrast"';
		
		$ret['html'] .= '<tr'.$class.'>';
		$ret['html'] .= mytag::tag($rec['CODE_FLAT'], 'td');
		$ret['html'] .= mytag::tag($rec['IS_OCCUPIED'], 'td');
		$ret['html'] .= mytag::tag($rec['PVFULL'], 'td');
		$ret['html'] .= mytag::tag($rec['PVM2'], 'td');
		$ret['html'] .= mytag::tag($rec['NPVM2'], 'td');
		$ret['html'] .= mytag::tag($rec['NPVFULLCORR'], 'td');
		$ret['html'] .= mytag::tag($rec['NPVM2CORR'], 'td');
		$ret['html'] .= '</tr>';
	}
	
	$ret['html'] .= '</table>';
	
	return $ret;
}

$val = mk_validation($_REQUEST['id']);
echo $val['html'];
echo '<div class="fix"></div>';
?>