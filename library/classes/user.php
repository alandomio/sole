<?php
# V.0.1.8
class user{
function __construct(){
	global $user;
	$this -> row = $user -> aUser; # RECORD UTENTE SE LOGGATO
	$this -> is_logged = $user -> autentica;

	$this -> federations = array('rows' => array(), 'ids' => array());
	$this -> hcompanys = array('rows' => array(), 'ids' => array());
	$this -> buildings = array('rows' => array(), 'ids' => array());
	$this -> flats = array('rows' => array(), 'ids' => array());
}

function get_federations(){
	if($this -> row['ID_GRUPPI'] == '1'){ # OK
		$qB = "SELECT * FROM federations";
	}
	elseif($this -> row['ID_GRUPPI'] == '2'){ # OK 
		$qB = "SELECT * 
		FROM	federations
		WHERE
		federations.ID_USER = '".$this -> row['ID_USER']."'";
	}
	elseif($this -> row['ID_GRUPPI'] == '3'){ # OK
	$qB = "SELECT federations.* 
		FROM hcompanys
		Left Join federations ON hcompanys.ID_FEDERATION = federations.ID_FEDERATION
		WHERE hcompanys.ID_USER = '".$this -> row['ID_USER']."'";
	}
	elseif($this -> row['ID_GRUPPI'] == '4'){ # OK
		$qB = "SELECT federations.*
		FROM buildings_users
		Left Join buildings ON buildings_users.ID_BUILDING = buildings.ID_BUILDING
		Left Join hcompanys ON buildings.ID_HCOMPANY = hcompanys.ID_HCOMPANY
		Left Join federations ON hcompanys.ID_FEDERATION = federations.ID_FEDERATION
		WHERE buildings_users.ID_USER = '".$this -> row['ID_USER']."'
		GROUP BY federations.ID_FEDERATION
		";
	}
	elseif($this -> row['ID_GRUPPI'] == '5'){ # HHU tabella molti a molti buildings_users
		$qB = "SELECT federations.*
		FROM buildings_users
		Left Join buildings ON buildings_users.ID_BUILDING = buildings.ID_BUILDING
		Left Join hcompanys ON buildings.ID_HCOMPANY = hcompanys.ID_HCOMPANY
		Left Join federations ON hcompanys.ID_FEDERATION = federations.ID_FEDERATION
		WHERE buildings_users.ID_USER = '".$this -> row['ID_USER']."'";
	}
	$this -> federations['rows'] = rs::inMatrix($qB);
	$this -> federations['ids'] = arr::key2arr($this -> federations['rows'], 'ID_FEDERATION');
	return $this -> federations['ids'];
}

function get_hcompanys(){
	if($this -> row['ID_GRUPPI'] == '1'){ # OK
		$qB = "SELECT * FROM hcompanys";
	}
	elseif($this -> row['ID_GRUPPI'] == '2'){ # OK
		$qB = "SELECT
		hcompanys.*
		FROM
		federations
		Left Join hcompanys USING(ID_FEDERATION)
		WHERE
		federations.ID_USER = '".$this -> row['ID_USER']."'";
	}
	elseif($this -> row['ID_GRUPPI'] == '3'){ # OK
		$qB = "SELECT * FROM hcompanys WHERE hcompanys.ID_USER = '".$this -> row['ID_USER']."'";
	}
	elseif($this -> row['ID_GRUPPI'] == '4'){ # OK
		$qB = "SELECT * FROM hcompanys WHERE hcompanys.ID_USER = '".$this -> row['ID_USER']."'";
	}
	elseif($this -> row['ID_GRUPPI'] == '5'){ # OK
		$qB = "SELECT 
		*
		FROM 
		hcompanys
		Left Join buildings USING(ID_HCOMPANY)
		Left Join flats USING(ID_BUILDING)
		WHERE flats.ID_USER = '".$this -> row['ID_USER']."'";
	}
	$this -> hcompanys['rows'] = rs::inMatrix($qB);
	$this -> hcompanys['ids'] = arr::key2arr($this -> flats['rows'], 'ID_FLAT');
	return $this -> hcompanys['ids'];
}



function get_buildings(){ # RESTITUISCE TUTTI I RECORD DEGLI EDIFICI LEGATI ALL'ID UTENTE INDICATO
	if($this -> row['ID_GRUPPI'] == '1'){ # OK
		$qB = "SELECT * FROM buildings";
	}
	elseif($this -> row['ID_GRUPPI'] == '2'){ # OK
		$qB = "SELECT
		buildings.*
		FROM
		federations
		Inner Join hcompanys ON federations.ID_FEDERATION = hcompanys.ID_FEDERATION
		Inner Join buildings ON hcompanys.ID_HCOMPANY = buildings.ID_HCOMPANY
		WHERE
		federations.ID_USER = '".$this -> row['ID_USER']."'";
	}
	elseif($this -> row['ID_GRUPPI'] == '3'){ # OK
		$qB = "SELECT * FROM buildings
		Inner Join hcompanys USING(ID_HCOMPANY)
		WHERE hcompanys.ID_USER = '".$this -> row['ID_USER']."'";
	}
	elseif($this -> row['ID_GRUPPI'] == '4'){ # OK
		$qB = "SELECT buildings.*
		FROM buildings_users
		Inner Join buildings ON buildings_users.ID_BUILDING = buildings.ID_BUILDING
		WHERE buildings_users.ID_USER = '".$this -> row['ID_USER']."'";
	}
	elseif($this -> row['ID_GRUPPI'] == '5'){ # OK
		$qB = "SELECT buildings.*
		FROM buildings_users
		Inner Join buildings ON buildings_users.ID_BUILDING = buildings.ID_BUILDING
		WHERE buildings_users.ID_USER = '".$this -> row['ID_USER']."'";
	}
	$this -> buildings = rs::inMatrix($qB);
	$this -> buildings['ids'] = arr::key2arr($this -> buildings['rows'], 'ID_BUILDING');
	return $this -> buildings['ids'];
}

function get_flats(){ # RESTITUISCE TUTTI I RECORD DEGLI APPARTAMENTI LEGATI ALL'ID UTENTE INDICATO
	if($this -> row['ID_GRUPPI'] == '1'){ # OK
		$qB = "SELECT * FROM flats";
	}
	elseif($this -> row['ID_GRUPPI'] == '2'){ # OK
		$qB = "SELECT
		flats.*
		FROM
		federations
		Left Join hcompanys USING(ID_FEDERATION)
		Left Join buildings USING(ID_HCOMPANY)
		Left Join flats USING(ID_BUILDING)
		WHERE
		federations.ID_USER = '".$this -> row['ID_USER']."' AND
		flats.ID_FLAT IS NOT NULL
		";
	}
	elseif($this -> row['ID_GRUPPI'] == '3'){ # OK
		$qB = "SELECT
		flats.*
		FROM 
		hcompanys
		Left Join buildings USING(ID_HCOMPANY)
		Left Join flats USING(ID_BUILDING)
		WHERE hcompanys.ID_USER = '".$this -> row['ID_USER']."' AND
		flats.ID_FLAT IS NOT NULL
		";
	}
	elseif($this -> row['ID_GRUPPI'] == '4'){ # OK
		$qB = "SELECT
		flats.*
		FROM 
		hcompanys
		Left Join buildings USING(ID_HCOMPANY)
		Left Join flats USING(ID_BUILDING)
		WHERE hcompanys.ID_USER = '".$this -> row['ID_USER']."' AND
		flats.ID_FLAT IS NOT NULL
		";
	}
	elseif($this -> row['ID_GRUPPI'] == '5'){ # OK
		$qB = "SELECT * FROM flats
		WHERE flats.ID_USER = '".$this -> row['ID_USER']."'";
	}
	$this -> flats['rows'] = rs::inMatrix($qB);
	$this -> flats['ids'] = arr::key2arr($this -> flats['rows'], 'ID_FLAT');
	return $this -> flats['ids'];
}

}
?>