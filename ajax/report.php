<?php
# V.0.1.8
session_start();
set_time_limit(0);
include_once '../init.php';
$user = new autentica($aA5);
$user->login_no_redirect(false);
$MYFILE -> catch_buffer();

error_reporting(7);

require_once('../library/fpdf/fpdf.php');
require_once('../library/fpdf/fpdi.php');

include_once '../library/classes/rs.php';
include_once '../library/classes/err.php';
include_once '../library/personal/meter.php';
include_once '../library/personal/flat.php';
include_once '../library/personal/outputs.php';
include_once '../library/personal/sole.php';

$_ICONS = '../images/report/';
$_N_ANNI = 6;

$flats = array();

if($user->idg == 5)	{
	$_REQUEST['f1'] = sole::get_flat_by_userid($user->aUser['ID_USER']);
	$_REQUEST['tipo'] = '';
}
	

if($_REQUEST['tipo'] == 'multiplo')	{
	// � stato scelto un edificio
	$flats = sole::get_flats_by_idbuilding( $_REQUEST['bld'] );
	
	// setto un qualsiasi valore ID_FLAT per l'edificio
	if(empty( $_REQUEST['f1'] ))
		$_REQUEST['f1'] = $flats[0]['ID_FLAT'];
	
	// $flats = sole::get_flats_by_idbuilding(sole::get_idbuilding_by_idflat($_REQUEST['f1']) );
} else {
	
	if(empty($_REQUEST['f1'])){
		exit('Error: choose a flat');
	}
	$flats[] = array('ID_FLAT'=>$_REQUEST['f1']);
}
	
$pdf = new FPDF();
$pdf->SetMargins(15,25,15);

// echo 'NUMERO APPARTAMENTI: '.$n_flats = count($flats);
	
$_SESSION['stato_report'] = 0;

$start = microtime(true);




foreach($flats as $f)	{
	$pdf->addPage();
	$pdf->SetFont('Arial','B',7);
	//$pagecount = $pdf->setSourceFile('../pdf/modello-report.pdf');
	//$tplidx = $pdf->importPage(1, '/BleedBox');
	//$pdf->useTemplate($tplidx, 0, 0, 210);

	$f1 = $f['ID_FLAT'];

	if(isset($_REQUEST['id']))	
		$f1 = sole::get_flat_by_usercode($_REQUEST['id']);

	$flat1 = new flat($f1);
	$y1 = $flat1->get_first_year();
	
	if( empty($y1) ){
		exit( 'Report error: check measures for these flats' );
	}
	
	$pdf->SetDrawColor(255, 255, 255);
	$pdf->SetXY(15, 40);
	$pdf->SetFont('Arial','B',14);
	$pdf->Multicell(60,5, 'RAPPORTO CONSUMI', 0);
	$pdf->SetFont('Arial','B',12);
	$pdf->Multicell(80,5, $flat1->get_name() . ' - ' . $flat1->get_user_name(), 0);
	$pdf->SetXY(150, 40);
	$pdf->SetFont('Arial','B',9);
	$pdf->Multicell(45,5, 'Generato il ' . date('d/m/Y'), 0, 'R');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->SetFont('Arial','B',12);
	$pdf->Multicell(80,5, 'Tabella di confronto', 0);
	$y1 = stampa_tabella(15,$pdf->GetY() + 3);

	$pdf->SetXY($pdf->GetX() + 30,$pdf->GetY() - 6);

	$pdf->SetFont('Arial','B',12);
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Multicell(80,5, 'Grafico di riepilogo consumi edificio', 0);

	include_once('graph_report.php');

	$pdf->Image('tmp/graph.png', 15, $pdf->GetY() + 3, 180, 80);

	// testo finale
	$pdf->SetFont('Arial','',10);
	$pdf->SetXY(15,$pdf->GetY() + 90);
	//$pdf->SetFillColor(255, 205, 13);
	$pdf->SetFillColor(255, 255, 0);

	$pdf->SetDrawColor(255, 152, 51);
	//$pdf->Rect(15,$pdf->GetY() + 90, 180, 12, 'FD');
	//$pdf->SetXY(17,$pdf->GetY() + 92);
	$pdf->Multicell(180,5, "I consumi energetici dipendono sia dalle caratteristiche dell'edificio che dalle abitudini degli abitanti. Con questo servizio la tua cooperativa vuole aiutarti a diminuire i consumi, risparmiare e contribuire alla tutela dell'ambiente.", 0, 'L', 1);
	
	//$pdf->Multicell(180,5, "Tempo generazione pagina: " . round(microtime(true) - $start, 3) . " sec", 0, 'L', 1);
	
	//$_SESSION['stato_report']= round(($i++ / $n_flats) * 100);
	//error_log("Tempo generazione pagina: " . round(microtime(true) - $start, 3) . " sec");

}



if($_REQUEST['tipo'] == 'multiplo')	{
	$filename = $flat1->get_building_name() . '_' . date("Y-m-d") . '.pdf';
} else {
	$filename = $flat1->get_building_name().'-'.$flat1->get_flat_name() . '_' . date("Y-m-d") . '.pdf';
}


$pdf->Output($filename, 'D');



function iconamedia ($dati, $media)	{

	
	if($media > 0)
		$quoziente = $dati['value'] / $media;
	else return 0;
	$differenziale = $quoziente - 1;
	
	if($dati['value'] > 0 && $dati['status'] == 'valid')	{
		if ($differenziale < -0.3)
			$icona = 1;
		else if ($differenziale < -0.1)
			$icona = 2;	
		else if ($differenziale <  0.1)
			$icona = 3;	
		else if ($differenziale < 0.3)
			$icona = 4;	
		else 
			$icona = 5;	
	}
	else
		$icona = 0;
	
	return $icona;
}


function iconadelta ($dati, $precedente)	{
	if($dati['status'] == 'nd')
		return 'nd';
	$tolleranza = 0.05;
	if($precedente['value'] > 0 && $dati['value'] > 0 && $precedente['status'] == 'valid' && $dati['status'] == 'valid')	{
		if($dati['value'] > ($precedente['value'] * (1+$tolleranza)))
			return 'su';
		elseif ($dati['value'] < ($precedente['value'] * (1-$tolleranza)))
			return 'giu';
		else
			return 'ug';
	}	else
		return 'nd';

}
		


function stampa_tabella($x, $y)	{
	global $pdf, $y1, $flat1, $f1, $_N_ANNI, $avg;
	$tipi = sole::get_metertypes_by_idflats($f1,0);
	$b1 = sole::get_idbuilding_by_idflat($f1);
	
	$y1 = $flat1->get_first_year();
	// echo 'anno '.$y1;
	
	error_reporting(7);

	$pdf->SetFont('Arial','B',9);
	$pdf->SetXY($x + 37, $y);
	for($year=$y1;$year<=$y1 + $_N_ANNI;$year++)	{
		$pdf->Cell(16,5,$year, 'LR', 0, 'C');
		$pdf->SetXY($pdf->GetX() - 16,$pdf->GetY() + 5);
		$pdf->Cell(8,5,'I', 'LR', 0, 'C');
		$pdf->Cell(8,5,'E', 'LR', 0, 'C');
		$pdf->SetXY($pdf->GetX(),$pdf->GetY() - 5);
		
	}
	
	
	
	
	$pdf->SetXY($x,$y + 10);
	$pdf->SetFont('Arial','B',5.5);
	
		foreach ($tipi as $tipo)	{
	//var_dump($tipo);
		$usages = sole::get_directusages_by_idflats($f1, $tipo['ID_METERTYPE']);
		//var_dump($usages);
		if(count($usages))
			foreach($usages as $usage)	{
				//echo $usage['description'];
				//$pdf->SetXY($pdf->GetX(),$pdf->GetY() + 2);
				$pdf->SetX($x);
				$pdf->Multicell(37,3, $tipo['METERTYPE_IT']."\n  ".$usage['description'], 1);
				$pdf->SetXY($x + 37,$pdf->GetY() - 6);
				for($year=$y1;$year<=$y1 + 6;$year++)	{
					

					//$dati = $flat1->get_npvm2($usage['ID_USAGE'], $year, 1);
					$dati = $flat1->get_value('m', 'f', $usage['ID_USAGE'], $year, 1);
					//$value = $dati['value'];
 					//echo $usage['description'] . '<br>';
 					//var_dump($dati);

					if(!isset($avg[$usage['ID_USAGE']][$year][1]))
						$avg[$usage['ID_USAGE']][$year][1] = sole::get_avg_npvm2($b1, $usage['ID_USAGE'], $year, 1);
					//echo $avg[$usage['ID_USAGE']][$year][1];
					
					$value1[$usage['ID_USAGE']][$year] = $dati;
					
					//echo $year.' 1' . ' ' . $value . ' - '. $avg[$usage['ID_USAGE']][$year][1] . '<br>';
					$pdf->Cell(8,8,'', 'LR');
					$pdf->Image('../images/report/' 	. iconadelta($dati, $value1[$usage['ID_USAGE']][$year-1]) 
														. iconamedia ($dati, $avg[$usage['ID_USAGE']][$year][1]) 
														. '.png', $pdf->GetX() - 6.5, $pdf->GetY() + 0.5, 5, 5);
					
					//$dati = $flat1->get_npvm2($usage['ID_USAGE'], $year, 2);
					$dati = $flat1->get_value('m', 'f', $usage['ID_USAGE'], $year, 2);
					$value = $dati['value'];
					//var_dump($dati);
					if(!isset($avg[$usage['ID_USAGE']][$year][2]))
						$avg[$usage['ID_USAGE']][$year][2] = sole::get_avg_npvm2($b1, $usage['ID_USAGE'], $year, 2);
					
					$value2[$usage['ID_USAGE']][$year] = $dati;
					
					//echo $year.'2' . ' ' . $value . ' - '. $avg[$usage['ID_USAGE']][$year][2] . '<br>';
					$pdf->Cell(8,8,'', 'LR');
					$pdf->Image('../images/report/' 	. iconadelta($dati, $value2[$usage['ID_USAGE']][$year-1]) 
														. iconamedia ($dati, $avg[$usage['ID_USAGE']][$year][2]) 
														. '.png', $pdf->GetX() - 6.5, $pdf->GetY() + 0.5, 5, 5);
					
				}
				$pdf->Ln();
				
				
			}
			
		
			
		

		}
		
		// Parte di generazioni contatori triorari
		$meters = sole::get_direct_hourly_meters($f1);	
		//var_dump($meters);
		if( count($meters) )
			foreach ($meters as $meter)	{
				$pdf->SetX($x);
				$pdf->SetFont('Arial','B',5);
				$pdf->Multicell(37,2.5, $meter['METERTYPE_IT'] . "\n Usi nell'alloggio \n % uso nelle ore di picco", 1);
				$pdf->SetXY($x + 37,$pdf->GetY() - 6);
				for($year=$y1;$year<=$y1 + 6;$year++)	{
				
					$dati = $flat1->get_F1($meter['ID_METER'], $year, 1);
					//$avg = $flat1->get_avg_F1($meter['ID_METER'], $year, 1);
					
					$avgF1 = 33.33;
					
					$value1[$usage['ID_USAGE']][$year] = $dati;
					
					//echo $year.'1' . ' ' . $value . ' - '. $avg . '<br>';
					$pdf->Cell(8,8,'', 'LR');
					$pdf->Image('../images/report/' 	. iconadelta($dati, $value1[$usage['ID_USAGE']][$year-1]) 
														. iconamedia ($dati, $avgF1) 
														. '.png', $pdf->GetX() - 6.5, $pdf->GetY() + 0.5, 5, 5);
					
					$dati = $flat1->get_F1($meter['ID_METER'], $year, 2);
					//$avg = $flat1->get_avg_F1($meter['ID_METER'], $year, 2);
					
					
					$value2[$usage['ID_USAGE']][$year] = $dati;
					
					//echo $year.'2' . ' ' . $value . ' - '. $avg . '<br>';
					$pdf->Cell(8,8,'', 'LR');
					$pdf->Image('../images/report/' 	. iconadelta($dati, $value2[$usage['ID_USAGE']][$year-1]) 
														. iconamedia ($dati, $avgF1) 
														. '.png', $pdf->GetX() - 6.5, $pdf->GetY() + 0.5, 5, 5);
					
				}
				$pdf->Ln();
			
			}
	
	
	$fine_tabella = $pdf->GetY();
	if ($fine_tabella < $y + 38)
		$fine_tabella = $y + 38;
	
	//legenda
	//$pdf->Rect($x + 110, $y, 80, 55);   //Bordo legenda
	$pdf->SetFont('Arial','',5.5);
	//$pdf->SetLeftMargin($x + 100);
	$pdf->SetXY($x + 154.5, $y);
	$pdf->Multicell(30,2.7, 'Rispetto allo stesso periodo nell\'anno precedente i tuoi consumi sono:', 0, 'L');
	$pdf->Ln();
	$pdf->SetX($x + 160);
	$pdf->Image('../images/report/up.png', $pdf->GetX() - 5.5, $pdf->GetY() + 0.5, 5, 5);
	$pdf->Cell(30,7, 'in aumento', 0);
	$pdf->Ln();
	$pdf->SetX($x + 160);
	$pdf->Image('../images/report/equal.png', $pdf->GetX() - 5.5, $pdf->GetY() + 0.5, 5, 5);
	$pdf->Cell(30,7, 'circa uguali (+/- 5%)', 0);
	$pdf->Ln();
	$pdf->SetX($x + 160);
	$pdf->Image('../images/report/down.png', $pdf->GetX() - 5.5, $pdf->GetY() + 0.5, 5, 5);
	$pdf->Cell(30,7, 'in diminuizione', 0);
	$pdf->Ln();
	$pdf->SetX($x + 160);
	$pdf->Image('../images/report/no.png', $pdf->GetX() - 5.5, $pdf->GetY() + 0.5, 5, 5);
	$pdf->Cell(30,7, 'dato non disponibile', 0);
	$pdf->Ln();
	$pdf->SetX($x + 160);
	
	$pdf->Ln();
	$pdf->SetXY($x, $fine_tabella+2);
	$pdf->Multicell(100,2.7, 'Rispetto alla media dell\'edificio, considerate le dimensioni del tuo alloggio, i tuoi consumi sono:', 0, 'L');

	$pdf->SetX($x + 7);
	$pdf->Image('../images/report/5.png', $pdf->GetX() - 5.5, $pdf->GetY() + 0.5, 5, 5);
	$pdf->Cell(30,7, 'superiori del 30%', 0);

	$pdf->Image('../images/report/4.png', $pdf->GetX() - 5.5, $pdf->GetY() + 0.5, 5, 5);
	$pdf->Cell(30,7, 'superiori del 10%', 0);
	$pdf->Image('../images/report/3.png', $pdf->GetX() - 5.5, $pdf->GetY() + 0.5, 5, 5);
	$pdf->Cell(30,7, 'nella media', 0);
	$pdf->Image('../images/report/2.png', $pdf->GetX() - 5.5, $pdf->GetY() + 0.5, 5, 5);
	$pdf->Cell(30,7, 'inferiori del 10%', 0);
	$pdf->Image('../images/report/1.png', $pdf->GetX() - 5.5, $pdf->GetY() + 0.5, 5, 5);
	$pdf->Cell(30,7, 'inferiori del 30%', 0);
	$pdf->Image('../images/report/0.png', $pdf->GetX() - 5.5, $pdf->GetY() + 0.5, 5, 5);
	$pdf->Cell(30,7, 'dato non disponibile', 0);
	
	return $y1;
}
?>