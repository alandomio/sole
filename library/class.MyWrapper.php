<?php
/**
* MyWrapper - DBAL
*
* Mysql Database Abstraction Layer
* @package MyWrapper
* @author Alan Domio (info@metalogic.it)
* @version 1.7
* @copyright Alan Domio (c) april 2007
*/

class MyWrapper {

	var $host;
	var $user;
	var $pass;
	var $activedb;
	var $connection;
	var $lastresultset;
	var $lastsqlquery;

	// definisce il tipo di array di risposta alla query
	var $modus = Array('MYSQL_ASSOC'=>1, 'MYSQL_NUM'=>2, 'MYSQL_BOTH'=>3);

	
	var $limit; // contiene l'eventuale limite per le query con offset
	

	/**
	*  Creo l'oggetto e setto le prime variabili locali
	*  @param host è l'host del server mysql
	*  @param user è il nome utente
	*  @param password è la password
	*  @param connect se settato a true connette automaticamente al db
	*/
	function MyWrapper($host="localhost", $user="root", $password="root", $dbname="", $connect=true) 
	{	

		$this->host = $host;
		$this->user = $user;
		$this->pass = $password;

		// if $connect is true try connecting
		if($connect) 
		{
			$this->connection = $this->connect();
		}

		// se passo anche un nome di db lo setto attivo
		if($dbname) 
		{
			$this->changedb($dbname);
		}

	}


	/**
	*  Esegue una connessione al server
	*  Nessun parametro, usa le variabili locali
	*/
	function connect() 
	{		

		// first try to disconnect
		if($this->connection) 
		{
			$this->disconnect();
		}

		// and then connects
		$link = mysql_connect($this->host, $this->user, $this->pass);
		
		if (!$link) 
		{
   			die('Not connected : ' . mysql_error());   			
		}

		return $link;
	}


	/**
	*  Disconnette dal server mysql
	*/
	function disconnect() 
	{	
		mysql_close($this->connection)or die(mysql_error());	
	}


	/**
	* Cambia l'active database
	* @param dbname è il nome del database che si vuole selezionare
	*/
	function changedb($dbname) 
	{	

		$db_selected = mysql_select_db($dbname, $this->connection);
		if (!$db_selected) 
		{
		   die ("Can't use {$dbname} : " . mysql_error());
		}

		$this->activedb = $dbname;
	}
	

	/**
	*  Esegue la query e ritorna il resultset
	*  @param $sqlquery contiene la query SQL
	*  @param $arraytype indica il tipo di array che si vuole di ritorno
	*         i valori disponibili sono MYSQL_ASSOC, MYSQL_NUM, MYSQL_BOTH
	*/
	function do_query($sqlquery, $array_type="MYSQL_ASSOC") 
	{
		$time = $this->microtime_float();
		unset($this->lastresultset);
		
		$this->lastsqlquery = $sqlquery;  // salvo l'ultima query
		
		$res = mysql_query($sqlquery)or die(mysql_error());
		
		while($row = mysql_fetch_array($res, $this->modus[$array_type])) 
		{
			$this->lastresultset[] = $row;	
		}
		
		//header('X-MySQL-Time: ' . $time - $this->microtime_float() );
		return $this->lastresultset;
	}
	

	/**
	*  Esegue la query e ritorna il resultset
	*  @param $sqlquery contiene la query SQL
	*  @param $arraytype indica il tipo di array che si vuole di ritorno
	*         i valori disponibili sono MYSQL_ASSOC, MYSQL_NUM, MYSQL_BOTH
	*/
	function do_select($table, $fieldlist=null, $where=null, $orderby=null, $order=null, $array_type="MYSQL_ASSOC")
	{

		unset($this->lastresultset);
		
		// vedo se sono stati selezionati dei campi
		if(isset($fieldlist) && is_string($fieldlist)) {
			$f  = $fieldlist;
		} elseif(isset($fieldlist) && count($fieldlist)) {
			$f = implode(',' , $fieldlist);
		} else {
			$f = "*";
		}

		// preparo il where
		if(isset($where)) {
			$w ="WHERE {$where}";
		}
		
		if(isset($orderby) && isset($order)) {
			$o = "ORDER BY {$orderby} {$order}";
		}
		
		$sqlquery = "SELECT {$f} FROM {$table} {$w} {$o} ".$this->limit;

		$this->lastsqlquery = $sqlquery;  // salvo l'ultima query

		$res = mysql_query($sqlquery)or die(mysql_error());

		while($row = mysql_fetch_array($res, $this->modus[$array_type])) 
		{
			$this->lastresultset[] = $row;
		}

		return $this->lastresultset;
	}


	/**
	*  Esegue la query e ritorna il resultset
	*  @param $sqlquery contiene la query SQL
	*  @param $arraytype indica il tipo di array che si vuole di ritorno
	*         i valori disponibili sono MYSQL_ASSOC, MYSQL_NUM, MYSQL_BOTH
	*/
	function do_update($sqlquery) 
	{
		$this->lastsqlquery = $sqlquery;  // salvo l'ultima query

		$res = mysql_query($sqlquery)or die(mysql_error());

		return $res;
	}


	/**
	*  Esegue la query e ritorna il resultset
	*/
	function do_insert($sqlquery) 
	{
		$this->lastsqlquery = $sqlquery;  // salvo l'ultima query

		$res = mysql_query($sqlquery)or die(mysql_error());
		
		$lid = mysql_insert_id();

		return $lid;
	}
	
	/**
	* Elimino un file in base ad una chiave
	*/
	function do_delete($table, $key, $value) {
		
		$sqlquery = "DELETE FROM {$table} WHERE $key='{$value}'";
		
		$this->lastsqlquery = $sqlquery;  // salvo l'ultima query
		
		$res = mysql_query($sqlquery)or die(mysql_error());
		
		return $res;
	}
	

	/**
	*  Return a matrix with data from lastresultset
	*  @param limit indica quanti record devono essere ritornati
	*               se viene passato 0 o niente ritornano tutti
	*  @param startfrom è il punto di partenza da cui ritornare i dati
	*/
	function get_result_data($offset=0, $length="") 
	{
		return array_slice($this->lastresultset, $offset, $length);		
	}


	/**
	*  Return only the first row of the lastresultset
	*/
	function first() 
	{
		return $this->get_result_data(0, 1);
	}


	/**
	*  Return only the first row of the lastresultset
	*/
	function last() 
	{
		return $this->get_result_data(-1, 1);
	}

	
	/**
	*  Ritorna la lista dei nomi delle colonne
	*  dell'ultimo recordset
	*/
	function get_fields_name()
	{
		$names = $this->first();
		
		return array_keys($names[0]);
	}
	
	/**
	*  Returns the index of a table
	*/
	function get_index($tablename)
	{
		$sqlquery = "SHOW INDEX FROM {$tablename}";
		
		$res = mysql_query($sqlquery)or die(mysql_error());

		$row = mysql_fetch_array($res);
		
		return $row['Column_name'];
	}


	/**
	* Get all server and client info
	*/
	function get_info() 
	{
		$info['host']		= mysql_get_host_info();
		
		$info['client']		= mysql_get_client_info();
		
		$info['protocol']	= mysql_get_proto_info();
		
		$info['server']	= mysql_get_server_info();
		
		return $info;
	}
	


	/*
	*  Prepare the SQL statement to update a table recordset
	*/
	function prepare_update($table, $values, $where)
	{
		// carico i campi della tabella
		$res = mysql_query("SHOW FIELDS FROM {$table}")or die(mysql_error());

		while($row = mysql_fetch_object($res))
		{
			$campo = $row->Field;

			if ( isset($values[$campo]) )
			{
				$coppie[] = "{$campo}='{$values[$campo]}'";
			}

		}

		$sql = "UPDATE $table SET ". implode(",", $coppie) ." ". trim($where);

		return $sql;
	}


	/*
	*  Prepare the SQL statement to insert a record
	*/
	function prepare_insert($table, $values)
	{

		// carico i campi della tabella
		$res = mysql_query("SHOW FIELDS FROM {$table}") or die(mysql_error());

		while($row = mysql_fetch_object($res)) 
		{

			$campo = $row->Field;

			if ( isset($values[$campo]) ) 
			{
				$campi[] = $campo;
				
				if(strstr($campo, 'data') AND strstr($values[$campo], '/'))
					$valori[] = "STR_TO_DATE('".addslashes($values[$campo])."', '%d/%m/%Y')'";
				else
					$valori[] = "'". addslashes($values[$campo]) ."'";

			}

		}

		$sql = "INSERT INTO {$table} (". implode(",", $campi) .") VALUES (". implode(",",$valori) .")";

		return $sql;
	}
	
	
	function prepare_replace($table, $values)
	{
		//var_dump($values);
		// carico i campi della tabella
		$res = mysql_query("SHOW FIELDS FROM {$table}") or die(mysql_error());

		while($row = mysql_fetch_object($res)) 
		{
			
			$campo = $row->Field;
			$tipo = $row->Type;

			if ( isset($values[$campo]) ) 
			{
				$campi[] = $campo;
				
				if(strstr($tipo, 'decimal'))
					$values[$campo] = str_replace(',', '.', $values[$campo]);
				if(strstr($campo, 'data') AND strstr($values[$campo], '/'))
					$valori[] = "STR_TO_DATE('".addslashes($values[$campo])."', '%d/%m/%Y')";
				else
					//$valori[] = "'". addslashes($values[$campo]) ."'";
					$valori[] = "'". ($values[$campo]) ."'";
				

			}

		}

		$sql = "REPLACE INTO {$table} (". implode(",", $campi) .") VALUES (". implode(",",$valori) .")";
		//echo $sql;
		return $sql;
	}
	
	
	
	/**
	 * Imposto il limite per le query
	 */
	function limit($from, $step) {
		$this->limit = "LIMIT {$from}, $step";
	}
	
	function microtime_float()
	{
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}
	

} // end of myWrapper

?>