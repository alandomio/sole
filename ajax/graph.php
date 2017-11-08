<?php
session_start();
//echo 'ok';
include_once '../init.php';


include_once '../library/classes/rs.php';
include_once '../library/classes/err.php';
include_once '../library/personal/meter.php';
include_once '../library/personal/flat.php';
include_once '../library/personal/outputs.php';

global $firephp;

$conn = rs::conn();

ob_end_clean ();


header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
header("Expires: ".gmdate("D, d M Y H:i:s", time() + 84600)." GMT");
//include ('../init.php');

error_reporting(0);
$_JPGRAPH = '../library/jpgraph/src/';

include ($_JPGRAPH . 'jpgraph.php');
include ($_JPGRAPH . 'jpgraph_bar.php');
include ($_JPGRAPH . 'jpgraph_line.php');

include_once ('../library/personal/sole.php');

$_PERIOD['W'] = PERIOD_W;
$_PERIOD['S'] = PERIOD_S;
				

header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
header("Cache-Control: no-cache, must-revalidate" );
header("Pragma: no-cache" );
//header("Content-type: text/x-json");
 
//var_dump($_REQUEST);

//$sole = new sole();

$f1 = $_REQUEST['f1'];
$f2 = $_REQUEST['f2'];
$b1 = $_REQUEST['b1'];
$b2 = $_REQUEST['b2'];
$y1 = $_REQUEST['y1'];
$y2 = $_REQUEST['y2'];
$p = $_REQUEST['p'];
$period = $_REQUEST['p']=='1'?'W':'S';

$fillcolor = array(	array(255, 204, 51),
					array(255, 153, 51),
					array(255, 102, 51),
					array(213, 125, 49),
					array(190, 81, 85),
					array(180, 140, 37),
					array(177, 166, 64)
					);

// Create the graph. These two calls are always required
	
	
	
	if($b1>0)
		$id_building = $b1;
	else
		$id_building = sole::get_idbuilding_by_idflat($f1);
		
	// Imposto la dimensione del grafico in base al tipo di grafico e al numero di alloggi
	if($_REQUEST['t']==7)	{
		$flats = sole::get_flats_num($id_building);
		
		if($flats < 50)
			$graph = new Graph(900,500,"auto"); 
		elseif ($flats >= 50 && $flats < 100)
			$graph = new Graph(1800,1000,"auto");
		else 
			$graph = new Graph(2700,1500,"auto");
		
	} else 
		$graph = new Graph(900,500,"auto");    
	$graph->SetScale("textlin");

	$graph->SetShadow();
	$graph->img->SetMargin(75,150,20,100);		

	$graph->SetUserFont('DejaVuSans.ttf');
	$graph->title->SetFont(FF_USERFONT,FS_NORMAL,14);
	$graph->yaxis->title->SetFont(FF_USERFONT,FS_NORMAL);
	$graph->xaxis->title->SetFont(FF_USERFONT,FS_NORMAL);
	$graph->yaxis->SetTitlemargin(45);
	
	$graph->legend->Pos(0.02,0.1);
	



if ($_REQUEST['action'] == 'demo')	{
	$data1y=array(12,8,19);
	$data2y=array(8,2,11);
	$data3y=array(3,9,2);
	$data4y=array(1,5,11);
	$data5y=array(3,6,11);
	$data6y=array(8,5,17);
	$datax = array('2009', '2010', '2011');

	
	
	$graph->xaxis->SetTickLabels($datax);

	$b1plot = new BarPlot($data1y);
	$b1plot->SetFillColor(array(255, 204, 51));
	$b2plot = new BarPlot($data2y);
	$b2plot->SetFillColor(array(255, 153, 51));
	$b3plot = new BarPlot($data3y);
	$b3plot->SetFillColor(array(255, 102, 51));
	$b4plot = new BarPlot($data4y);
	$b4plot->SetFillColor(array(255, 204, 51));
	$b5plot = new BarPlot($data5y);
	$b5plot->SetFillColor(array(255, 153, 51));
	$b6plot = new BarPlot($data6y);
	$b6plot->SetFillColor(array(255, 102, 51));

	// Create the accumulated bar plots
	$ab1plot = new AccBarPlot(array($b1plot,$b2plot, $b3plot));
	$ab2plot = new AccBarPlot(array($b4plot,$b5plot, $b6plot));

	// Create the grouped bar plot
	$gbplot = new GroupBarPlot(array($ab1plot,$ab2plot));

	// ...and add it to the graph
	$graph->Add($gbplot);

	$graph->title->Set("Year consumption comparison");
	$graph->xaxis->title->Set("Year");
	$graph->yaxis->title->Set("Primary energy");

	

	// Display the graph
	$graph->Stroke();
}

if ($_REQUEST['t'] == '1')	{

	
	$meters1 = sole::get_meters_by_idflat($f1);
	$meters2 = sole::get_meters_by_idflat($f2);
	
	$i = 0;
	// Per ogni misuratore creo un array contenente tutti i consumi periodici
	foreach($meters1 as $meter)	{
		$METER['ID_METER'] = new meter($meter['ID_METER']);
		for($year=2009;$year<=2011;$year++)	{
			$data[$meter['ID_METER']][] = $METER['ID_METER']->get_cnpvm2($year, 1);
			$data[$meter['ID_METER']][] = $METER['ID_METER']->get_cnpvm2($year, 2);
		}
	}
	
	foreach($meters2 as $meter)	{
		$METER['ID_METER'] = new meter($meter['ID_METER']);
		for($year=2009;$year<=2011;$year++)	{
			$data[$meter['ID_METER']][] = $METER['ID_METER']->get_cnpvm2($year, 1);
			$data[$meter['ID_METER']][] = $METER['ID_METER']->get_cnpvm2($year, 2);
		}
	}
	

	
	

	$datax = array('2009 W', '2009 S', '2010 W', '2010 S', '2011 W', '2011 S',);


	
	$graph->xaxis->SetTickLabels($datax);
	
	$i=0;
	foreach($meters1 as $meter)	{
		$b1plot[$i] = new BarPlot($data[$meter['ID_METER']]);
		$b1plot[$i]->SetFillColor($fillcolor[$i]); 
		$i++;
	}
	$i=0;
	foreach($meters2 as $meter)	{
		$b2plot[$i] = new BarPlot($data[$meter['ID_METER']]);
		$b2plot[$i]->SetFillColor($fillcolor[$i]);
		//var_dump($data[$meter['ID_METER']]);
		$i++;
	}
	
	
	// Create the accumulated bar plots
	$ab1plot = new AccBarPlot($b1plot);
	$ab2plot = new AccBarPlot($b2plot);


	// Create the grouped bar plot
	$gbplot = new GroupBarPlot(array($ab1plot, $ab2plot));

	// ...and add it to the graph
	$graph->Add($gbplot);

	$graph->title->Set("Year consumption comparison");
	$graph->xaxis->title->Set("Year");
	$graph->yaxis->title->Set("Primary energy / m2 [kWh]");

	$graph->title->SetFont(FF_FONT1,FS_BOLD);
	$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
	$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

	// Display the graph
	$graph->Stroke();
}




if ($_REQUEST['t'] == '3')	{ 	// Consumi appartamento comparati

	
	$meters1 = sole::get_meters_by_idflat($f1);
	$types = sole::get_metertypes_by_idflats($f1, $f2);
	//var_dump($types);
	$flat1 = new flat($f1);
	$flat2 = new flat($f2);
	$i = 0;
	// Per ogni misuratore creo un array contenente tutti i consumi periodici
	foreach($types as $type)	{
		for($year=$y1;$year<=$y2;$year++)	{
			$data[$type['ID_METERTYPE']][] = $flat1->get_npv_primary($type['ID_METERTYPE'], $year, 1);
			$data[$type['ID_METERTYPE']][] = $flat1->get_npv_primary($type['ID_METERTYPE'], $year, 2);
			$datax[] = $year . ' W';
			$datax[] = $year . ' S';
		}
		//var_dump($data[$type['ID_METERTYPE']]);
		$b1plot[$i] = new BarPlot($data[$type['ID_METERTYPE']]);
		$b1plot[$i]->SetFillColor($fillcolor[$i]); 
		$b1plot[$i]->SetLegend($type['METERTYPE_IT']);
		$i++;
		
	}
	
	$i = 0;
	if ($f != 'media')	{
		unset($data);
		foreach($types as $type)	{
			for($year=$y1;$year<=$y2;$year++)	{
				$data[$type['ID_METERTYPE']][] = $flat2->get_npv_primary($type['ID_METERTYPE'], $year, 1);
				$data[$type['ID_METERTYPE']][] = $flat2->get_npv_primary($type['ID_METERTYPE'], $year, 2);
				$datax[] = $year . ' W';
				$datax[] = $year . ' S';
			}
			$b2plot[$i] = new BarPlot($data[$type['ID_METERTYPE']]);
			$b2plot[$i]->SetFillColor($fillcolor[$i]); 
			$i++;
		
		}
	
	} else {  // calcolo la media degli appartamenti
		
	
	}
	//$datax = array('2009 W', '2009 S', '2010 W', '2010 S', '2011 W', '2011 S',);

	$graph->xaxis->SetTickLabels($datax);
	

	
	
	// Create the accumulated bar plots
	$ab1plot = new AccBarPlot($b1plot);
	$ab2plot = new AccBarPlot($b2plot);


	// Create the grouped bar plot
	$gbplot = new GroupBarPlot(array($ab1plot, $ab2plot));

	// ...and add it to the graph
	$graph->Add($gbplot);

	$graph->title->Set("Period consumption comparison");
	$graph->xaxis->title->Set("Year");
	$graph->yaxis->title->Set("Primary energy [kWh]");



	// Display the graph
	$graph->Stroke();
}


if ($_REQUEST['t'] == '5')	{  // Edificio energia primaria

	
	$flats = sole::get_flats_by_idbuilding($b1);
	
	$metertypes = sole::get_metertypes_by_idbuilding($b1);
	$buildingname = sole::get_building_info($b1);
	
	//var_dump($metertypes);
	
	
	//$flat1 = new flat($f1);
	$i = 0;
	
	foreach ($metertypes as $metertype)	{
		$usages = sole::get_usages_by_idbuilding($b1, $metertype['ID_METERTYPE'] );
		$fillcolor = sfumature($metertype['ID_METERTYPE'], count($usages));
		$energytype = sole::get_metertype_description($metertype['ID_METERTYPE']);
		
		$j = 0;
		$colonna = 0;
		if(count($usages))
			foreach($usages as $usage)	{
				for($year=$y1;$year<=$y2;$year++)	{
					$consumo1 = $consumo2 = 0;
					$area1 = $area2 = 0;
					foreach($flats as $flat)	{
						$flat1 = new flat($flat['ID_FLAT']);
						$dati = $flat1->get_npvm2_primary($usage['ID_USAGE'], $year, 1);
						//var_dump($dati);
						if( $dati['status']=='nd' )
							$consumo1 += 0;
						else	{
							$area = $flat1->get_netarea($year, 1);
							$consumo1 += $dati['value'] * $area ;
							$area1 += $area;
						}
							
						/*
						if( $dati['status']=='wrong' )
							$bande[] = banda_wrong($colonna);
						*/
						$dati = $flat1->get_npvm2_primary($usage['ID_USAGE'], $year, 2);
						if( $dati['status']=='nd' )
							$consumo2 += 0;
						else {
							$area = $flat1->get_netarea($year, 2);
							$consumo2 += $dati['value'] * $area ;
							$area2 += $area;
						}
						/*
						if( $dati['status']=='wrong' )
							$bande[] = banda_wrong($colonna+1);
						*/

					}	
					
					$colonna+=2;
					
					
					$consumo1 = $consumo1 / $area1;
					$consumo2 = $consumo2 / $area2;
					
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
	
	$graph->SetUserFont('DejaVuSans.ttf');
	$graph->title->SetFont(FF_USERFONT,FS_NORMAL,14);

	$graph->title->Set(GRAPH_BUILDING . " (".$buildingname['CODE_BLD'].")");
	$graph->xaxis->title->Set(LABEL_PERIOD);
	$graph->yaxis->title->Set(LABEL_PRIMARY_ENERGY . " [ kWh/m2 ]");

	//$graph->title->SetFont(FF_FONT1,FS_BOLD);
	//$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
	//$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

	// Display the graph
	$graph->Stroke();
}



if ($_REQUEST['t'] == '6')	{  // Alloggio e tipo fornitura


	
	$meters1 = sole::get_meters_by_idflat($f1);
	//$types = sole::get_metertypes_by_idflats($f1,0 );
	$b1 = sole::get_idbuilding_by_idflat($f1);
	$usages = sole::get_usages_by_idbuilding($b1, $_REQUEST['et'] );
	
	$unit = sole::get_unit_by_metertype($_REQUEST['et'] );
	$energytype = sole::get_metertype_description($_REQUEST['et']);
	$fillcolor = sfumature($_REQUEST['et'], count($usages));
	$flat1 = new flat($f1);
	$i = 0;
	//var_dump($usages);

	// Per ogni misuratore creo un array contenente tutti i consumi periodici
	foreach($usages as $usage)	{
		
		$colonna = 0;
		for($year=$y1;$year<=$y2;$year++)	{
			$dati = $flat1->get_npvm2($usage['ID_USAGE'], $year, 1);

			if( $dati['status']=='nd' )
				$data[$usage['ID_USAGE']][] = 0;
			else
				$data[$usage['ID_USAGE']][] = $dati['value'] ;
			if( $dati['status']=='wrong' )
				$bande[] = banda_wrong($colonna);
			$colonna++;
			
			$dati = $flat1->get_npvm2($usage['ID_USAGE'], $year, 2);
			if( $dati['status']=='nd' )
				$data[$usage['ID_USAGE']][] = 0;
			else
				$data[$usage['ID_USAGE']][] = $dati['value'] ;
			if( $dati['status']=='wrong' )
				$bande[] = banda_wrong($colonna);
			
			$colonna++;

			$datax[] = $year . ' ' . $_PERIOD['W'];
			$datax[] = $year . ' ' . $_PERIOD['S'];
			
		}
		//var_dump($data[$type['ID_METERTYPE']]);
		$b1plot[$i] = new BarPlot($data[$usage['ID_USAGE']]);
		$b1plot[$i]->SetFillColor($fillcolor[$i]); 
		$b1plot[$i]->SetLegend($energytype . ' - ' . $usage['description']);
		//$b1plot[$i]->SetPattern(PATTERN_DIAG4); 
		$i++;
		
	}
	
	foreach($bande as $banda)	{
		$graph->Add($banda);
	}

	//die();
	
	$graph->xaxis->SetTickLabels($datax);
	/*
	$i=0;
	foreach($meters1 as $meter)	{
		$b1plot[$i] = new BarPlot($data[$meter['ID_METER']]);
		$b1plot[$i]->SetFillColor($fillcolor[$i]); 
		$b1plot[$i]->SetLegend($legend[$i]);
		$i++;
	}
	$i=0;
*/

	
	// Create the accumulated bar plots
	$ab1plot = new AccBarPlot($b1plot);
	
	//$ab1plot->SetLegend($legenda);

	// ...and add it to the graph
	$graph->Add($ab1plot);

	$graph->SetUserFont('DejaVuSans.ttf');
	$graph->title->SetFont(FF_USERFONT,FS_NORMAL,14);
	$graph->title->Set(GRAPH_FLAT . ' ('.$flat1->get_name().')');
	$graph->xaxis->title->Set(LABEL_YEAR);
	$graph->yaxis->title->Set("[$unit/m2]");

	/*
	$graph->title->SetFont(FF_FONT1,FS_BOLD);
	$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
	$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
	*/
	// Display the graph
	$graph->Stroke();
}

if ($_REQUEST['t'] == '7')	{  // Periodo e tipo fornitura

	
	$flats = sole::get_flats_by_idbuilding($b1);
	
	$j = 0;
	
	$usages = sole::get_usages_by_idbuilding($b1, $_REQUEST['et'] );
	$unit = sole::get_unit_by_metertype($_REQUEST['et'] );
	$energytype = sole::get_metertype_description($_REQUEST['et']);
	//$usages = sole::get_usages_by_idbuilding($b1 );
	//var_dump($usages);
	
	$colori = sfumature($_REQUEST['et'], count($usages));
	
	// Per ogni misuratore creo un array contenente tutti i consumi periodici
	
	
	foreach($usages as $usage)	{
		//echo $usage['description'];
		$colonna = 0;
		foreach($flats as $flat)	{
			$flat1 = new flat($flat['ID_FLAT']);
			$dati = $flat1->get_npvm2($usage['ID_USAGE'], $y1, $p);
			
			if( $dati['status']=='nd' )	{
				$data[$usage['ID_USAGE']][] = 0;
			}
				
			else	{
				$data[$usage['ID_USAGE']][] = $dati['value'];
				end($data[$usage['ID_USAGE']]);
				//echo key($data[$usage['ID_USAGE']]);
				$flats_usage[key($data[$usage['ID_USAGE']])] += $dati['value'];
			}
				
			if( $dati['status']=='wrong' )
				$bande[$colonna] = true; 
			$colonna++;
	
			
			$datax[] = $flat['CODE_FLAT'];
		}	
	}
	
	// A questo punto devo ordinare l'array $data
	arsort($flats_usage);

	
	foreach($usages as $usage)	{
		foreach($flats_usage as $k=>$v)	{
			$ordered_data[$usage['ID_USAGE']][] = $data[$usage['ID_USAGE']][$k];
			$ordered_datax[] = $datax[$k];
			$ordered_bande[] = $bande[$k];
		}
		$bplot[$j] = new BarPlot($ordered_data[$usage['ID_USAGE']]);
		$bplot[$j]->SetFillColor($colori[$j]); 
		$bplot[$j]->SetLegend($energytype . ' - ' . $usage['description']);
		$j++;
	}
	

	
	foreach($ordered_bande as $k=>$banda)	{
		if($banda==true)
			$graph->Add(banda_wrong($k));
	}
	
	$abplot = new AccBarPlot($bplot);
	
	/*
	//$media = $total_consumption / $i;
	//asort($dati);
	foreach ($dati as $k=>$v)	{
		$datax[] = $k;
		$datay[] = $v;
		$mediay[] = $media;
	}
	*/
	
	//$line = new LinePlot($mediay); 
	//$line->SetBarCenter(); 
	
	$graph->xaxis->SetTickLabels($ordered_datax);
	$graph->xaxis->SetLabelAngle(90);
	//$graph->xaxis->SetTickPositions(array(100,100,100));


	// Create a bar pot
	//$bplot = new BarPlot($datay);
	
	

	// Adjust fill color
	//$bplot->SetFillColor($fillcolor[0]);
	$graph->Add($abplot);
	//$graph->Add($line);
	
	

	$graph->title->Set(GRAPH_PERIOD . " ($y1 ".$_PERIOD[$period].")");
	$graph->xaxis->title->Set(LABEL_FLAT);
	$graph->yaxis->title->Set("[$unit / m2]");
	$graph->xaxis->SetTitleMargin(50);


	// Display the graph
	$graph->Stroke();
}


if ($_REQUEST['t'] == '10')	{ 	// Consumi appartamento comparati

	
	$meters1 = sole::get_meters_by_idflat($f1);
	$types = sole::get_metertypes_by_idflats($f1, 0);
	//var_dump($types);
	$flat1 = new flat($f1);
	$i = 0;
	// Per ogni misuratore creo un array contenente tutti i consumi periodici
	foreach($types as $type)	{
		for($year=$y1;$year<=$y2;$year++)	{
			$data[$type['ID_METERTYPE']][] = $flat1->get_npvm2($type['ID_METERTYPE'], $year, 1);
			$data[$type['ID_METERTYPE']][] = $flat1->get_npvm2($type['ID_METERTYPE'], $year, 2);
			//echo $flat1->get_npvm2($type['ID_METERTYPE'], $year, 2);
			$datax[] = $year . ' ' . $_PERIOD['W'];
			$datax[] = $year . ' ' . $_PERIOD['S'];
		}
		//var_dump($data[$type['ID_METERTYPE']]);
		$b1plot[$i] = new BarPlot($data[$type['ID_METERTYPE']]);
		$b1plot[$i]->SetFillColor($fillcolor[$i]); 
		$b1plot[$i]->SetLegend($type['METERTYPE_IT']);
		$i++;
		
	}

	unset($data);
	
	   // calcolo la media degli appartamenti
	foreach($types as $type)	{
		for($year=$y1;$year<=$y2;$year++)	{
			$data[$type['ID_METERTYPE']][] = $flat1->get_avg_npvm2($type['ID_METERTYPE'], $year, 1);
			$data[$type['ID_METERTYPE']][] = $flat1->get_avg_npvm2($type['ID_METERTYPE'], $year, 2);
			//echo $flat1->get_avg_npvm2($type['ID_METERTYPE'], $year, 2);
			$datax[] = $year . ' ' . $_PERIOD['W'];
			$datax[] = $year . ' ' . $_PERIOD['S'];
		}
		//var_dump($data[$type['ID_METERTYPE']]);
		$b2plot[$i] = new BarPlot($data[$type['ID_METERTYPE']]);
		$b2plot[$i]->SetFillColor($fillcolor[$i]); 
		$b2plot[$i]->SetLegend($type['METERTYPE_IT']);
		$i++;
		
	}

	//var_dump($b2plot);


	$graph->xaxis->SetTickLabels($datax);
	
	// Create the accumulated bar plots
	//$ab1plot = new AccBarPlot($b1plot);
	//$ab2plot = new AccBarPlot($b2plot);


	// Create the grouped bar plot
	//$gbplot = new GroupBarPlot(array($ab1plot, $ab2plot));

	// ...and add it to the graph
	//$graph->Add($gbplot);
	$graph->Add($b1plot);

	$graph->title->Set("Year consumption with average comparison");
	$graph->xaxis->title->Set("Year");
	$graph->yaxis->title->Set("Primary energy [kWh]");

	// Display the graph
	$graph->Stroke();
}





if ($_REQUEST['t'] == 'sfumature')	{

	sfumature(7, 4);

}


function sfumature ($tono, $n_sfumature)	{
	$colori = array(2 => array('inizio' => 'FF0000', 'fine' => 'FF9900'), // rosso
					3 => array('inizio' => '003300', 'fine' => '00FF00'), // verde
					5 => array('inizio' => '0000FF', 'fine' => '00FFFF'), // blu
					6 => array('inizio' => 'FFFF00', 'fine' => 'FFFF99'), // giallo
					4 => array('inizio' => 'CC33FF', 'fine' => 'CCCCFF'), // viola
					7 => array('inizio' => '603813', 'fine' => 'C3996B'), // marrone
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
