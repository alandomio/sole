<?php




//global $firephp;

$conn = rs::conn();

//ob_end_clean ();


error_reporting(0);
$_JPGRAPH = '../library/jpgraph/src/';

include_once ($_JPGRAPH . 'jpgraph.php');
include_once ($_JPGRAPH . 'jpgraph_bar.php');
include_once ($_JPGRAPH . 'jpgraph_line.php');

include_once ('../library/personal/sole.php');

$_PERIOD['W'] = 'I';
$_PERIOD['S'] = 'E';

$f1 = $_REQUEST['f1'];
$f2 = $_REQUEST['f2'];
$b1 = $_REQUEST['b1'];
$b2 = $_REQUEST['b2'];
$p = $_REQUEST['p'];
$y2 = $y1 + $_N_ANNI;
$period = $_REQUEST['p']=='1'?'W':'S';

if(isset($_REQUEST['id']))	
	$f1 = sole::get_flat_by_usercode($_REQUEST['id']);

$fillcolor = array(	array(255, 204, 51),
					array(255, 153, 51),
					array(255, 102, 51),
					array(213, 125, 49),
					array(190, 81, 85),
					array(180, 140, 37),
					array(177, 166, 64)
					);

// Create the graph. These two calls are always required
	//$graph = new Graph(1140,540,"auto");    
	$graph = new Graph(1800,800,"auto");    
	$graph->SetScale("textlin");

	$graph->SetShadow();
	$graph->img->SetMargin(150,300,40,100);		

	$graph->SetUserFont('DejaVuSans.ttf');
	$graph->title->SetFont(FF_USERFONT,FS_NORMAL,18);
	$graph->yaxis->title->SetFont(FF_USERFONT,FS_NORMAL,15);
	$graph->xaxis->title->SetFont(FF_USERFONT,FS_NORMAL,15);
	$graph->legend->SetFont(FF_USERFONT,FS_NORMAL,15);
	$graph->xaxis->SetFont(FF_USERFONT,FS_NORMAL,15);
	$graph->yaxis->SetFont(FF_USERFONT,FS_NORMAL,15);
	
	$graph->yaxis->SetTitlemargin(90);
	
	$graph->legend->Pos(0.02,0.1);
	

		$id_building = sole::get_idbuilding_by_idflat($f1);
		
	
	
	
	$flats = sole::get_flats_by_idbuilding($id_building);
	//var_dump($flats);
	$metertypes = sole::get_metertypes_by_idbuilding($id_building);
	//var_dump($metertypes);
	$buildingname = sole::get_building_info($id_building);
	
	$b1 = $id_building;
	
	//$flat1 = new flat($f1);
	$i = 0;
	
	/*
	foreach ($metertypes as $metertype)	{
		$usages = sole::get_usages_by_idbuilding($id_building, $metertype['ID_METERTYPE'] );
		//var_dump($usages);
		$fillcolor = sfumature($metertype['ID_METERTYPE'], count($usages));
		$energytype = sole::get_metertype_description($metertype['ID_METERTYPE']);
		$j = 0;
		$colonna=0;
		if(count($usages))
			foreach($usages as $usage)	{
				for($year=$y1;$year<=$y2;$year++)	{
					$consumo1 = $consumo2 = 0;
					foreach($flats as $flat)	{
						$flat1 = new flat($flat['ID_FLAT']);
						$dati = $flat1->get_npvm2_primary($usage['ID_USAGE'], $year, 1);
						//var_dump($dati);
						if( $dati['status']=='nd' )
							$consumo1 += 0;
						else
							$consumo1 += $dati['value'];
						if( $dati['status']=='wrong' )
							$bande[] = banda_wrong($colonna);
						
						$dati = $flat1->get_npvm2_primary($usage['ID_USAGE'], $year, 2);
						if( $dati['status']=='nd' )
							$consumo2 += 0;
						else
							$consumo2 += $dati['value'];
						if( $dati['status']=='wrong' )
							$bande[] = banda_wrong($colonna+1);
						

					}	
					
					$colonna+=2;	
					
					$data[$usage['ID_USAGE']][] = $consumo1;
					$data[$usage['ID_USAGE']][] = $consumo2;
					$datax[] = $year . ' W';
					$datax[] = $year . ' S';
				}
				//var_dump($data[$type['ID_METERTYPE']]);
				$b1plot[$i] = new BarPlot($data[$usage['ID_USAGE']]);
				$b1plot[$i]->SetFillColor($fillcolor[$j]); 
				$b1plot[$i]->SetLegend($energytype . ' - ' . $usage['description']);	
				$i++;
				$j++;
			
			}
	
	}
	*/
	
	
	foreach ($metertypes as $metertype)	{
		
		//$usages = sole::get_usages_by_idbuilding($b1, $metertype['ID_METERTYPE'] );
		$usages = sole::get_usage_list_by_idbuilding($b1, $metertype['ID_METERTYPE'], 't' );
		$fillcolor = sfumature($metertype['ID_METERTYPE'], count($usages));
		$energytype = sole::get_metertype_description($metertype['ID_METERTYPE']);
		
		$j = 0;
		$colonna = 0;
		if(count($usages))
			foreach($usages as $usage)	{
				for($year=$y1;$year<=$y2;$year++)	{
					$consumo1 = $consumo2 = 0;
					foreach($flats as $flat)	{
						$flat1 = new flat($flat['ID_FLAT']);
						//$dati = $flat1->get_npv_primary($usage['ID_USAGE'], $year, 1);
						$dati = $flat1->get_value('a', 'p', $usage['ID_USAGE'], $year, 1);
						//var_dump($dati);
						if( $dati['status']=='nd' )
							$consumo1 += 0;
						else
							$consumo1 += $dati['value'];
						/*
						if( $dati['status']=='wrong' )
							$bande[] = banda_wrong($colonna);
						*/
						$dati = $flat1->get_value('a', 'p', $usage['ID_USAGE'], $year, 2);
						if( $dati['status']=='nd' )
							$consumo2 += 0;
						else
							$consumo2 += $dati['value'];
						/*
						if( $dati['status']=='wrong' )
							$bande[] = banda_wrong($colonna+1);
						*/

					}	
					
					$colonna+=2;
					
					$data[$usage['ID_USAGE']][] = $consumo1;
					$data[$usage['ID_USAGE']][] = $consumo2;
					$datax[] = $year . ' ' . $_PERIOD['W'];
					$datax[] = $year . ' ' . $_PERIOD['S'];
				}
				//var_dump($data[$type['ID_METERTYPE']]);
				$b1plot[$i] = new BarPlot($data[$usage['ID_USAGE']]);
				$b1plot[$i]->SetFillColor($fillcolor[$j]); 
				$b1plot[$i]->SetLegend($energytype . ' - ' . $usage['description']);
				$i++;
				$j++;
			
			}
	
	}
	
	foreach($bande as $banda)	{
		$graph->Add($banda);
	}
	
	
	// Per ogni misuratore creo un array contenente tutti i consumi periodici
	
	
	$graph->xaxis->SetTickLabels($datax);
	// Create the accumulated bar plots
	$ab1plot = new AccBarPlot($b1plot);
	
	//$ab1plot->SetLegend($legenda);

	// ...and add it to the graph
	$graph->Add($ab1plot);

	$graph->title->Set("Consumo totale edificio");
	$graph->xaxis->title->Set("Periodo");
	$graph->yaxis->title->Set("Energia primaria [kWh]");

	//$graph->title->SetFont(FF_FONT1,FS_BOLD);
	//$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
	//$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

	// Display the graph
	$graph->Stroke('tmp/graph.png');
	




function sfumature ($tono, $n_sfumature)	{
	$colori = array(2 => array('inizio' => 'FF0000', 'fine' => 'FF9900'), // rosso
					3 => array('inizio' => '003300', 'fine' => '00FF00'), // verde
					5 => array('inizio' => '0000FF', 'fine' => '00FFFF'), // blu
					7 => array('inizio' => 'FFFF00', 'fine' => 'FFFF99'), // giallo
					4 => array('inizio' => 'CC33FF', 'fine' => 'CCCCFF'), // viola
					6 => array('inizio' => '603813', 'fine' => 'C3996B'), // marrone
					1 => array('inizio' => '000000', 'fine' => 'CCCCCC'), // grigio
					);
	$inizio = hex2rgb($colori[$tono]['inizio']);
	$fine = hex2rgb($colori[$tono]['fine']);
	if($n_sfumature > 1)
		$delta = array(	($fine[0] - $inizio[0]) / ($n_sfumature-1),
						($fine[1] - $inizio[1]) / ($n_sfumature-1),
						($fine[2] - $inizio[2]) / ($n_sfumature-1));
	
	for($i=0;$i<$n_sfumature;$i++)	{
		$colore =  array(round($inizio[0] + $delta[0] * $i), round($inizio[1] + $delta[1] * $i), round($inizio[2] + $delta[2] * $i));
		//echo '<div style="background-color:'.$colore.'">'.$colore.'</div><br/>';
		$sfumature[$i] = $colore;
	
	}
	return $sfumature;

}

function hex2rgb($color)	{
		if ($color[0] == '#')
			$color = substr($color, 1);

		if (strlen($color) == 6)
			list($r, $g, $b) = array($color[0].$color[1],
									 $color[2].$color[3],
									 $color[4].$color[5]);
		elseif (strlen($color) == 3)
			list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
		else
			return false;

		$r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

		return array($r, $g, $b);
	}
	
function rgb2hex($r, $g=-1, $b=-1)	{
		if (is_array($r) && sizeof($r) == 3)
			list($r, $g, $b) = $r;

		$r = intval($r); $g = intval($g);
		$b = intval($b);

		$r = dechex($r<0?0:($r>255?255:$r));
		$g = dechex($g<0?0:($g>255?255:$g));
		$b = dechex($b<0?0:($b>255?255:$b));

		$color = (strlen($r) < 2?'0':'').$r;
		$color .= (strlen($g) < 2?'0':'').$g;
		$color .= (strlen($b) < 2?'0':'').$b;
		return $color;
	}
	
function banda_wrong($colonna)	{
	$band = new PlotBand(VERTICAL,BAND_RDIAG,$colonna,$colonna+1,'red');
	$band->ShowFrame(true);
	$band->SetOrder(DEPTH_BACK);
	return $band;

}


?>
