<?php

class meter	{
	public function meter($id_meter)	{
		$this->id = $id_meter;
		$this->meter = rs::rec2arr("SELECT * FROM meters WHERE ID_METER='$id_meter' LIMIT 1"); 
		
	}
	
	function get_cnpvm2 ($anno, $uploadtype)	{
		$sql = "SELECT msoutputs.*  FROM meters
					LEFT JOIN measures USING(ID_METER)
					LEFT JOIN msoutputs USING(ID_MEASURE)
				WHERE meters.ID_METER=" . $this->id .
				" AND measures.ANNO_MS=" . $anno .
				" AND measures.ID_UPLOADTYPE=" . $uploadtype .
				" GROUP BY meters.ID_METER LIMIT 1";
				//echo $sql;
		$r = rs::inMatrix($sql);
		if($r[0]['CNPVM2']!=null)
			return $r[0]['CNPVM2'];	
		else 
			return 0;
	
	}
	
	function get_cnpv ($anno, $uploadtype)	{
		$sql = "SELECT msoutputs.*  FROM meters
					LEFT JOIN measures USING(ID_METER)
					LEFT JOIN msoutputs USING(ID_MEASURE)
				WHERE meters.ID_METER=" . $this->id .
				" AND measures.ANNO_MS=" . $anno .
				" AND measures.ID_UPLOADTYPE=" . $uploadtype .
				" GROUP BY meters.ID_METER LIMIT 1";
				//echo $sql;
		$r = rs::inMatrix($sql);
		if($r[0]['CNPV']!=null)
			return $r[0]['CNPV'];	
		else 
			return 0;
	
	}
	
	function resa_PV($anno, $uploadtype)	{
		
		$taglia = $this->get_size();
		$consumo = $this->get_cnpv($anno, $uploadtype);
		
		if(($taglia*$consumo) > 0)	{
			$resa = $consumo / $taglia;
			return sprintf("%01.2f", $resa);
		} else
			return 'nd';
		
	}
	
	
	
	
	function resa_ST($anno, $uploadtype)	{
		$taglia = $this->get_size();
		$consumo = $this->get_cnpv($anno, $uploadtype);
		//echo $taglia.BR;
		// echo $consumo.BR;
		if(($taglia*$consumo) > 0)	{
			$resa = $consumo / $taglia;
			return sprintf("%01.2f", $resa);
		} else
			return 'nd';
	
	}
	
	function resa_GT($anno, $uploadtype)	{
		$id_building = $this->get_building();
		$m = new EvalMath($anno, $uploadtype, $id_building);

		$consumo = $this->get_cnpv($anno, $uploadtype);
		$FUELcode = $this->get_fuel_code();
		$taglia = $this->get_size();
		$A = $m->e_ws($FUELcode); $value = $A['value']; $status = $A['status'];
		

		// calcolo l'energia primaria relativa al consumo carburante
		$value = misurazioni::get_primary_energy_value_by_codename($FUELcode, $id_building, $value);

		if(($consumo) > 0)	{
			$resa = $consumo / $value;
			return sprintf("%01.2f", $resa*100);
		} else
			return 'nd';
	
	}
	
	function rapporto_area_servita ()	{
		$sql = "SELECT SUM(NETAREA) AS area FROM flats LEFT JOIN flats_meters USING(ID_FLAT)  WHERE ID_METER=" . $this->id . " GROUP BY ID_METER";
		$dati = rs::rec2arr($sql);
		$area = $dati['area'];
		$taglia = $this->get_size();
		if(($area*$taglia)>0)	
			return sprintf("%01.2f", $area/$taglia);
		else
			return 'nd';
		
	}
	
	function consumo_acqua_calda($anno, $uploadtype)	{
		$id_building = $this->get_building();
		
		$m = new EvalMath($anno, $uploadtype, $id_building);
		$ACScode = $this->get_acs_code();
		$taglia = $this->get_size();
		$A = $m->e_ws($ACScode); $value = $A['value']; $status = $A['status'];
		if($status != 'valid' )
			return 'nd';
		else
			return sprintf("%01.2f", $value / ($taglia * 183));


	}
	
	function copertura_fabbisogno($anno, $uploadtype)	{
		$id_building = $this->get_building();

		$m = new EvalMath($anno, $uploadtype, $id_building);
		$ETEcode = $this->get_ete_code();
		$consumo = $this->get_cnpv($anno, $uploadtype);
		$A = $m->e_ws($ETEcode); $value = $A['value']; $status = $A['status'];

		if($status != 'valid' || ($consumo*$value)==0)
			return 'nd';
		else
			return sprintf("%01.2f", $consumo * 100 / ($consumo+$value));
	}
	
	function perdite($anno, $uploadtype)	{
		$id_building = $this->get_building();
		$m = new EvalMath($anno, $uploadtype, $id_building);
		$SUMcode = $this->get_sum_divisional_code();
		$consumo = $this->get_cnpv($anno, $uploadtype);
		$A = $m->e_ws($SUMcode); $value = $A['value']; $status = $A['status'];

		
		if($status != 'valid' || $consumo==0 )
			return 'nd';
		else
			return sprintf("%01.2f", ($consumo - $value) * 100 / ($consumo));
	
	
	}
	
	
	function get_size() 	{
		$sql = "SELECT * FROM meters_productions WHERE ID_METER=" . $this->id;
		$dati = rs::rec2arr($sql);
		$taglia = $dati['SIZE'];
		
		return $taglia;
	
	}
	
	function get_net_area()	{
		$sql = "SELECT SUM(NETAREA) AS area FROM flats LEFT JOIN flats_meters USING(ID_FLAT)  WHERE ID_METER=" . $this->id . " GROUP BY ID_METER";
		$dati = rs::rec2arr($sql);
		$area = $dati['area'];
		return $area;
	
	}
	
	function get_acs_code()	{
		$sql = "SELECT * FROM meters_productions LEFT JOIN meters ON meters.ID_METER=meters_productions.ACS WHERE meters_productions.ID_METER=" . $this->id;
		$dati = rs::rec2arr($sql);
		$codename = $dati['CODE_METER'];
		return $codename;
	}
	
	
	function get_ete_code()	{
		$sql = "SELECT * FROM meters_productions LEFT JOIN meters ON meters.ID_METER=meters_productions.ETE WHERE meters_productions.ID_METER=" . $this->id;
		$dati = rs::rec2arr($sql);
		$codename = $dati['CODE_METER'];
		return $codename;
	}
	
	function get_fuel_code()	{
		$sql = "SELECT * FROM meters_productions LEFT JOIN meters ON meters.ID_METER=meters_productions.FUEL WHERE meters_productions.ID_METER=" . $this->id;
		//echo $sql;
		$dati = rs::rec2arr($sql);
		$codename = $dati['CODE_METER'];
		return $codename;
	}
	
	function get_sum_divisional_code()	{
		$sql = "SELECT * FROM meters_productions LEFT JOIN meters ON meters.ID_METER=meters_productions.SUM_DIVISIONAL WHERE meters_productions.ID_METER=" . $this->id;
		$dati = rs::rec2arr($sql);
		$codename = $dati['CODE_METER'];
		return $codename;
	}
	
	
	
	
	function get_measure_id($anno, $uploadtype)	{
		$sql = "SELECT ID_MEASURE FROM measures
				LEFT JOIN meters USING(ID_METER)
				WHERE meters.ID_METER=" . $this->id .
				" AND measures.ANNO_MS=" . $anno .
				" AND measures.ID_UPLOADTYPE=" . $uploadtype .
				" GROUP BY meters.ID_METER LIMIT 1";
		$dati = rs::rec2arr($sql);
		$id = $dati['ID_MEASURE'];
		return $id;
	
	}
	
	function get_tipo ()	{
		$sql = "SELECT CODE_METER, METERTYPE_IT, METERTYPE_EN FROM meters LEFT JOIN metertypes USING(ID_METERTYPE) WHERE meters.ID_METER=".$this->id;
		$r = rs::inMatrix($sql);
		return $r[0]['METERTYPE_IT'];	
	
	}
	
	function get_building()	{
		$sql = "SELECT ID_BUILDING FROM flats LEFT JOIN flats_meters USING(ID_FLAT) WHERE ID_METER=".$this->id." GROUP BY ID_BUILDING ";
		$dati = rs::rec2arr($sql);
		$id_building = $dati['ID_BUILDING'];
		return $id_building;
		
		
	}
	
	function primary_energy ()	{
	
	}
	
	
	/**
	 * @return boolean
	 */
	function is_direct()	{
		if($this->meter['ID_SUPPLYTYPE']==1)
			return true;
		else
			return false;
	}
	
}

?>