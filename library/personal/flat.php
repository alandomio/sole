<?php

class flat	{
	public function flat($id_flat)	{
		$this->id = $id_flat;
		$this->id_building = $this->get_building_id();
	}
	
	function get_consumptions ($usage, $anno, $uploadtype, $type='NPVFULL', $id_flat=NULL) {
		//echo $uploadtype;
		if($id_flat==NULL)
			$id_flat = $this->id;
		$sql = "SELECT measures.ID_MEASURE FROM flats_meters
					LEFT JOIN meters USING(ID_METER)
					LEFT JOIN measures USING(ID_METER)
					WHERE flats_meters.ID_FLAT=" . $id_flat .
				" AND meters.K2_ID_USAGE=" . $usage .
				" AND measures.ANNO_MS=" . $anno .
				" AND measures.ID_UPLOADTYPE=" . $uploadtype;
		//echo $sql;
		$meters = rs::inMatrix($sql);
		
		$overallstatus = 'valid';
		foreach($meters as $meter)	{

			$dati = misurazioni::get_output($meter['ID_MEASURE'], $this->id, $type);
			
			//var_dump($dati);
			
			//if($meter['ID_METER']==781)
				//var_dump($dati);
			//error_log($dati);
			$consumptions += $dati['value'];
			$status = $dati['status'];
			
			// Se almeno una delle misurazioni � ND o Wrong allora devo invalidare tutto il valore accumulato
			if($status=='nd')
				$overallstatus = 'nd';
			elseif($status=='wrong')
				if($status!='nd')
					$overallstatus = 'wrong';

		}
		return array('value' => $consumptions, 'status' => $overallstatus);
	}
	
	function get_netarea($anno, $uploadtype)	{
		$flat = rs::rec2arr("SELECT NETAREA, IS_OCCUPIED FROM flats LEFT JOIN occupancys USING(ID_FLAT) WHERE flats.ID_FLAT={$this->id} AND occupancys.ANNO_MS=$anno AND occupancys.ID_UPLOADTYPE=$uploadtype LIMIT 1");
		$area = $flat['NETAREA'] * $flat['IS_OCCUPIED'];
		//echo $area;
		return $area;
	
	}
	
	function get_meter_consumptions ($meter, $anno, $uploadtype, $type='NPVFULL') {
		//echo $uploadtype;
		$sql = "SELECT * FROM meters
					LEFT JOIN metertypes USING(ID_METERTYPE)
					LEFT JOIN measures USING(ID_METER)
					LEFT JOIN flats_meters USING(ID_METER)
					LEFT JOIN msoutputs USING(ID_MEASURE)
					WHERE flats_meters.ID_FLAT=" . $this->id .
				" AND meters.ID_METER=" . $meter .
				" AND measures.ANNO_MS=" . $anno .
				" AND measures.ID_UPLOADTYPE=" . $uploadtype .
				" GROUP BY meters.ID_METER";
				
		$meters = rs::inMatrix($sql);

		$overallstatus = 'valid';
		foreach($meters as $meter)	{
			$dati = misurazioni::get_output($meter['ID_MEASURE'], $this->id, $type);
			//var_dump($consumo);
			$consumptions += $dati['value'];
			$status = $dati['status'];
			
			// Se almeno una delle misurazioni � ND o Wrong allora devo invalidare tutto il valore accumulato
			if($status=='nd')
				$overallstatus = 'nd';
			elseif($status=='wrong')
				if($status!='nd')
					$overallstatus = 'wrong';

		}

		return array('value' => $consumptions, 'status' => $overallstatus);
		
	
	}
	
	function get_npv ($metertype, $anno, $uploadtype)	{
		$r = $this->get_consumptions ($metertype, $anno, $uploadtype);
		if($r['value']!=null)
			return array('value' => $r['value'], 'status' => $r['status']);
		else 
			return array('value' => 0, 'status' => 'nd');
	}
	
	function get_npv_primary ($metertype, $anno, $uploadtype)	{
		$r = $this->get_consumptions ($metertype, $anno, $uploadtype, 'NPVFULLEP');
		//var_dump($r);
		if($r['value']!=null)
			return array('value' => $r['value'], 'status' => $r['status']);
		else 
			return array('value' => 0, 'status' => 'nd');
	}
	
	function get_npvm2_primary ($metertype, $anno, $uploadtype)	{
		$r = $this->get_consumptions ($metertype, $anno, $uploadtype, 'NPVM2EP');
		//var_dump($r);
		if($r['value']!=null)
			return array('value' => $r['value'], 'status' => $r['status']);
		else 
			return array('value' => 0, 'status' => 'nd');
	}
	
	
	
	function get_npvm2 ($metertype, $anno, $uploadtype)	{
		$r = $this->get_consumptions ($metertype, $anno, $uploadtype, 'NPVM2');
		if($r['value']!=null)
			return array('value' => $r['value'], 'status' => $r['status']);
		else 
			return array('value' => 0, 'status' => 'nd');
	}
	
	function get_F1 ($meter, $anno, $uploadtype)	{
		$r = $this->get_meter_consumptions ($meter, $anno, $uploadtype, 'F1');
		if($r['value']!=null)
			return array('value' => $r['value'], 'status' => $r['status']);
		else 
			return array('value' => 0, 'status' => 'nd');
	}
	
	
	
	
	
	function get_avg_npvm2 ($usage, $anno, $uploadtype)	{
		$sql = "SELECT AVG(msoutputs.CNPVM2) AS CNPVM2  FROM meters
					LEFT JOIN metertypes USING(ID_METERTYPE)
					LEFT JOIN measures USING(ID_METER)
					LEFT JOIN flats_meters USING(ID_METER)
					LEFT JOIN flats USING(ID_FLAT)
					LEFT JOIN msoutputs USING(ID_MEASURE)
					
				WHERE flats.ID_BUILDING=" . $this->id_building .
				" AND meters.K2_ID_USAGE=" . $usage .
				" AND measures.ANNO_MS=" . $anno .
				" AND measures.ID_UPLOADTYPE=" . $uploadtype .
				" AND msoutputs.STATUS='valid'" . 
				//" AND measures.STATUS='Validated'"  .
				" GROUP BY meters.K2_ID_USAGE LIMIT 1";
		
		$r = rs::inMatrix($sql);
		//var_dump($r);
		if($r[0]['CNPVM2']!=null)
			return $r[0]['CNPVM2'];		
		else
			return 0;
		
	}
	
	
	function get_avg_F1 ($meter, $anno, $uploadtype)	{
		$sql = "SELECT K2_ID_USAGE FROM meters WHERE ID_METER=$meter";
		$r = rs::rec2arr($sql);
		$usage = $r['K2_ID_USAGE'];
		$sql = "SELECT AVG(msoutputs.NPVM2F1 / msoutputs.NPVM2 * 100) AS CNPVM2F1  FROM meters
					LEFT JOIN metertypes USING(ID_METERTYPE)
					LEFT JOIN measures USING(ID_METER)
					LEFT JOIN flats_meters USING(ID_METER)
					LEFT JOIN flats USING(ID_FLAT)
					LEFT JOIN msoutputs USING(ID_MEASURE)
					
				WHERE flats.ID_BUILDING=" . $this->id_building .
				" AND meters.K2_ID_USAGE=" . $usage .
				" AND measures.ANNO_MS=" . $anno .
				" AND measures.ID_UPLOADTYPE=" . $uploadtype .
				" AND msoutputs.STATUS='valid'" . 
				//" AND measures.STATUS='Validated'"  .
				" GROUP BY meters.K2_ID_USAGE LIMIT 1";
		//echo $sql;
		$r = rs::inMatrix($sql);
		//var_dump($r);
		if($r[0]['CNPVM2F1']!=null)
			return $r[0]['CNPVM2F1'];		
		else
			return 0;
		
	}
	
	
	function get_building_id ()	{
		$sql = "SELECT ID_BUILDING FROM flats WHERE ID_FLAT=" . $this->id;
		
		$r = rs::inMatrix($sql);
		return $r[0]['ID_BUILDING'];
	}
	
	
	
	function get_metertypes () {
		$sql = "SELECT metertypes.ID_METERTYPE, metertypes.METERTYPE_IT FROM meters
					LEFT JOIN metertypes USING(ID_METER)
					LEFT JOIN measures USING(ID_METER)
					LEFT JOIN flats_meters USING(ID_METER)
					LEFT JOIN msoutputs USING(ID_MEASURE)
					
					";
	}
	
	function get_tipo ()	{
		$sql = "SELECT CODE_METER, METERTYPE_IT, METERTYPE_EN FROM meters LEFT JOIN metertypes USING(ID_METERTYPE) WHERE meters.ID_METER=".$this->id;
		$r = rs::inMatrix($sql);
		return $r[0]['METERTYPE_IT'];	
	
	}
	
	function primary_energy ()	{
	
	}
	
	function get_first_year()	{
		$sql = "SELECT measures.ANNO_MS  FROM meters
					LEFT JOIN metertypes USING(ID_METERTYPE)
					LEFT JOIN measures USING(ID_METER)
					LEFT JOIN flats_meters USING(ID_METER)
					LEFT JOIN flats USING(ID_FLAT)
					RIGHT JOIN msoutputs USING(ID_MEASURE)
					
				WHERE flats.ID_BUILDING=" . $this->id_building .
				" ORDER BY measures.ANNO_MS ASC LIMIT 1";
		//echo $sql;
		$r = rs::rec2arr($sql);
			return $r['ANNO_MS'];
	
	}
	
	function get_name()	{
		$q = "SELECT 
		CONCAT_WS(' - ".APP."', buildings.CODE_BLD, flats.CODE_FLAT) AS NOME
		FROM
		flats
		Left Join buildings USING(ID_BUILDING)
		WHERE
		flats.ID_FLAT = '{$this->id}'
		LIMIT 1
		";
		$r = rs::rec2arr($q);
		return $r['NOME'];
	}

	function get_flat_name()	{
		$q = "SELECT flats.CODE_FLAT
		FROM
		flats
		WHERE
		flats.ID_FLAT = '{$this->id}'
		LIMIT 1
		";
		$r = rs::rec2arr($q);
		return $r['CODE_FLAT'];
	}
	
		function get_building_name()	{
		$q = "SELECT buildings.CODE_BLD
		FROM
		flats
		Left Join buildings USING(ID_BUILDING)
		WHERE
		flats.ID_FLAT = '{$this->id}'
		LIMIT 1
		";
		$r = rs::rec2arr($q);
		return $r['CODE_BLD'];
	}
	
	function get_user_name()	{
		$q = "SELECT CONCAT(NAME, ' ', SURNAME) AS name FROM users LEFT JOIN flats USING (ID_USER) WHERE ID_FLAT=" . $this->id;
		$r = rs::rec2arr($q);
		return $r['name'];
	}
	
}
?>