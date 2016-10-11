<?php
require_once LIBRARY . 'phpexcel/Classes/PHPExcel.php';



class excel extends PHPExcel	{
	public $mappatura = array(	array('etichetta'=>'ID MISURAZIONE', 'campo'=>'ID_MEASURE', 'larghezza'=>0, 'indice'=>0),
										array('etichetta'=>'ID_EDIFICIO', 'campo'=>'ID_BUILDING', 'larghezza'=>0, 'indice'=>1),
										array('etichetta'=>'Nome contatore', 'campo'=>'CODE_METER', 'larghezza'=>30, 'indice'=>2),
										array('etichetta'=>'Matricola contatore', 'campo'=>'MATRICULA_ID', 'larghezza'=>18, 'indice'=>3),
										array('etichetta'=>'Anno', 'campo'=>'anno', 'larghezza'=>6, 'indice'=>4),
										array('etichetta'=>'Num invio', 'campo'=>'invio', 'larghezza'=>9, 'indice'=>5),
										array('etichetta'=>'Tipo lettura', 'campo'=>'tipo', 'larghezza'=>11, 'indice'=>6),
										array('etichetta'=>'Data', 'campo'=>'data', 'larghezza'=>16, 'indice'=>7),
										array('etichetta'=>'Lettura1', 'campo'=>'lettura1', 'larghezza'=>16, 'indice'=>8),
										array('etichetta'=>'Lettura2', 'campo'=>'lettura2', 'larghezza'=>16, 'indice'=>9),
										array('etichetta'=>'Lettura3', 'campo'=>'lettura3', 'larghezza'=>16, 'indice'=>10)
									);
	
	public function excel()	{
		
						
		parent::__construct();
	}
	
	/**
	 * Genera un file Excel modello per l'inserimento di una serie di misurazioni
	 */
	public function modello()	{
		
		// esempio nome file da generare:
		// HC2.001_M_2012_8 (M/S mensile / semestrale)
		$q = "SELECT CODE_BLD FROM buildings WHERE ID_BUILDING='{$_REQUEST['id_building']}' LIMIT 1";
		$r = rs::rec2arr($q);
		
		$this -> filename = $r['CODE_BLD'];
		
		
		if($_REQUEST['tipo']=='mensile'){
			$meters = sole::get_real_12_by_id_bld($_REQUEST['id_building'], true);
			$this -> filename .= '_M';
		} else { 
			$meters = sole::get_real_2_by_id_bld($_REQUEST['id_building'], true, true);
			$this -> filename .= '_S';
		}
		
		$this -> filename .= '_'.$_REQUEST['anno'].'_'.$_REQUEST['mese'];
		
		
		// Rows to repeat at top
		//$objPHPExcel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 1);
		// Creo l'intestazione
		
		$sheet = $this->getActiveSheet();

		foreach($this->mappatura as $campo)	{
			
			$sheet->setCellValueByColumnAndRow($campo['indice'], 1, $campo['etichetta']);
			$sheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($campo['indice']))->setWidth($campo['larghezza']);
		}
		
		
			
		//$sheet->getStyle('B1')->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_PROTECTED);
		
		$sheet->getProtection()->setSheet(true);
		
		
		
		// Add data
		$nriga = 2;

		
		//var_dump($meters);
		
		if(is_array($meters))
			foreach ($meters as $riga)	{
				foreach($this->mappatura as $colonna)	{
					if(isset($riga[$colonna['campo']]))
					$sheet->getCell(PHPExcel_Cell::stringFromColumnIndex($colonna['indice']) . $nriga)->setValueExplicit($riga[$colonna['campo']], PHPExcel_Cell_DataType::TYPE_STRING);
				}
				$sheet->getCell(PHPExcel_Cell::stringFromColumnIndex(1) . $nriga)->setValueExplicit($_REQUEST['id_building'], PHPExcel_Cell_DataType::TYPE_NUMERIC);
				$sheet->getCell(PHPExcel_Cell::stringFromColumnIndex(4) . $nriga)->setValueExplicit($_REQUEST['anno'], PHPExcel_Cell_DataType::TYPE_STRING);
				$sheet->getCell(PHPExcel_Cell::stringFromColumnIndex(5) . $nriga)->setValueExplicit($_REQUEST['mese'], PHPExcel_Cell_DataType::TYPE_STRING);
				$sheet->getCell(PHPExcel_Cell::stringFromColumnIndex(6) . $nriga)->setValueExplicit($_REQUEST['tipo'], PHPExcel_Cell_DataType::TYPE_STRING);
				//$sheet->protectCells('A'.$nriga.':G'.$nriga,'PHPExcel');
				$sheet->getStyle('H'.$nriga)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
				$sheet->getStyle('I'.$nriga)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
				$sheet->getStyle('J'.$nriga)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
				$sheet->getStyle('K'.$nriga)->getProtection()->setLocked(PHPExcel_Style_Protection::PROTECTION_UNPROTECTED);
				
				// In Excel5 la validazione funziona circa, quindi non la applichiamo
				//$this->validate_date('H'.$nriga);
				
				$nriga++;
		}
		
	}
	

	private function validate_date($cell)	{
		$objValidation = $this->getActiveSheet()->getCell($cell)->getDataValidation();
		$objValidation->setType( PHPExcel_Cell_DataValidation::TYPE_DATE);
		$objValidation->setErrorStyle( PHPExcel_Cell_DataValidation::STYLE_STOP );
		$objValidation->setAllowBlank(false);
		$objValidation->setShowInputMessage(true);
		$objValidation->setShowErrorMessage(true);
		$objValidation->setErrorTitle('Input error');
		$objValidation->setError('Only Date is permitted!');
		$objValidation->setPromptTitle('Allowed input');
		$objValidation->setPrompt('Only dates between 01/01/'.$_REQUEST['anno'].' and 31/12/'.$_REQUEST['anno'].' are allowed.');
		$objValidation->setFormula1(PHPExcel_Shared_Date::PHPToExcel(mktime($_REQUEST['anno'], 1, 1)));
		$objValidation->setFormula2(PHPExcel_Shared_Date::PHPToExcel(mktime($_REQUEST['anno'], 12, 31)));
		
	}
	
	public function importa()	{
		

		$inputFileName = $_FILES['excel']['tmp_name'];
		
		/**  Identify the type of $inputFileName  **/
		$inputFileType = PHPExcel_IOFactory::identify($inputFileName);
		/**  Create a new Reader of the type that has been identified  **/
		$objReader = PHPExcel_IOFactory::createReader($inputFileType);
		/**  Load $inputFileName to a PHPExcel Object  **/
		$objPHPExcel = $objReader->load($inputFileName);
			
		foreach ($objPHPExcel->getActiveSheet()->getRowIterator() as $row) {
			$row = $row->getRowIndex();
			foreach ($this->mappatura as $campo)	{
				$dati[$campo['campo']] = trim($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($campo['indice'], $row)->getValue(), '"');
				
				//echo $dati[$campo['campo']];
			}
			

			
			
			if(strlen($dati['data']) > 0)	{
				if($dati['tipo'] == 'mensile')
					$log = misurazioni::save_measure12(	$dati['anno'],
							$dati['invio'],
							sole::get_idmeter_by_codename($dati['CODE_METER'], $dati['ID_BUILDING']),
							PHPExcel_Style_NumberFormat::toFormattedString($dati['data'], "D/M/YYYY"),
							$dati['lettura1'],
							$dati['lettura2'],
							$dati['lettura3'],
							false);
				else if($dati['tipo'] == 'semestrale')
					
					$log = misurazioni::save_measure(	$dati['anno'],
							$dati['invio'],
							sole::get_idmeter_by_codename($dati['CODE_METER'], $dati['ID_BUILDING']),
							PHPExcel_Style_NumberFormat::toFormattedString($dati['data'], "D/M/YYYY"),
							$dati['lettura1'],
							$dati['lettura2'],
							$dati['lettura3'],
							false);
			}
			
			
			
		}
		
		$res = array(	'ID_BUILDING'=>$dati['ID_BUILDING'],
						'ANNO'=>$dati['anno'],
						'INVIO'=>$dati['invio'],
				);
		//echo $log;
		echo json_encode($res);
		
	}
	
	public function importaSemestrale()	{
	
	}
	
	public function write($filename)	{
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$filename.'.xls"');
		header('Cache-Control: max-age=0');
		
		$objWriter = PHPExcel_IOFactory::createWriter($this, 'Excel5');
		
		ob_end_clean();
		$objWriter->save('php://output');
	}
	
} 


?>

