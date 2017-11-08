<?php
session_start();
include_once '../init.php';

define('DBHOST', "localhost" );
define('DBUSER', "sole" );
define('DBNAME', "sole");
define('DBPSW',  "sole" );



include_once '../library/classes/rs.php';
include_once '../library/classes/err.php';
include_once '../library/personal/meter.php';
include_once '../library/personal/flat.php';

global $firephp;

$conn = rs::conn();

ob_end_clean ();
header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" );
header("Expires: ".gmdate("D, d M Y H:i:s", time() + 84600)." GMT");
//include ('../init.php');

error_reporting(7);
$_JPGRAPH = '../library/jpgraph/src/';

include ($_JPGRAPH . 'jpgraph.php');
include ($_JPGRAPH . 'jpgraph_bar.php');
include ($_JPGRAPH . 'jpgraph_line.php');

include ('../library/personal/sole.php');

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
	$graph = new Graph(900,500,"auto");    
	$graph->SetScale("textlin");

	$graph->SetShadow();
	$graph->img->SetMargin(75,150,20,50);		

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





if ($_REQUEST['t'] == '6')	{  // Consumi totali appartamento 

	
	$meters1 = sole::get_meters_by_idflat($f1);
	//$types = sole::get_metertypes_by_idflats($f1,0 );
	$usages = sole::get_usages_by_idflats($f1,0, $_REQUEST['et'] );
	
	$flat1 = new flat($f1);
	$i = 0;
	// Per ogni misuratore creo un array contenente tutti i consumi periodici
	foreach($usages as $usage)	{
		for($year=$y1;$year<=$y2;$year++)	{
			$data[$usage['ID_USAGE']][] = $flat1->get_npv_primary($usage['ID_USAGE'], $year, 1);
			$data[$usage['ID_USAGE']][] = $flat1->get_npv_primary($usage['ID_USAGE'], $year, 2);
			$datax[] = $year . ' W';
			$datax[] = $year . ' S';
		}
		//var_dump($data[$type['ID_METERTYPE']]);
		$b1plot[$i] = new BarPlot($data[$usage['ID_USAGE']]);
		$b1plot[$i]->SetFillColor($fillcolor[$i]); 
		$b1plot[$i]->SetLegend($usage['description']);
		$i++;
		
	}


	
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

	$graph->title->Set("Flat year consumption ()");
	$graph->xaxis->title->Set("Year");
	$graph->yaxis->title->Set("Primary energy / m2[kWh]");

	$graph->title->SetFont(FF_FONT1,FS_BOLD);
	$graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
	$graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

	// Display the graph
	$graph->Stroke();
}

if ($_REQUEST['t'] == '7')	{  // Consumi totali appartamento 

	
	$flats = sole::get_flats_by_idbuilding($b1);
	
	$j = 0;
	
	$usages = sole::get_usages_by_idbuilding($b1, $_REQUEST['et'] );
	//var_dump($usages);
	
	
	// Per ogni misuratore creo un array contenente tutti i consumi periodici
	
	foreach($usages as $usage)	{
	//$usage = $usages[0];
		//echo $usage['description'];
		foreach($flats as $flat)	{
			$flat1 = new flat($flat['ID_FLAT']);
			//$data[$usage['ID_USAGE']][] = $flat1->get_npv_primary($usage['ID_USAGE'], $y1, $p);
			$data[$flat['ID_FLAT']] += $flat1->get_npv_primary($usage['ID_USAGE'], $y1, $p);
			
			$datax[] = $flat['CODE_FLAT'];
		}	
		$bplot[$j] = new BarPlot($data);
		$bplot[$j]->SetFillColor($fillcolor[$j]); 
		$bplot[$j]->SetLegend($usage['description']);
		$j++;

			
		
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
	
	$graph->xaxis->SetTickLabels($datax);


	// Create a bar pot
	//$bplot = new BarPlot($datay);
	
	

	// Adjust fill color
	//$bplot->SetFillColor($fillcolor[0]);
	$graph->Add($abplot);
	//$graph->Add($line);
	


	$graph->title->Set("Building consumption ( period $y1 $period)");
	$graph->xaxis->title->Set("Flat");
	$graph->yaxis->title->Set("Primary energy / m2 [kWh]");


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
			$datax[] = $year . ' W';
			$datax[] = $year . ' S';
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
			$datax[] = $year . ' W';
			$datax[] = $year . ' S';
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


?>
