<?php
class rs{
static function rpt_ctrl($rec,$sql_type,$lable,$comment,$not_null,$oldrec){
	$err=false;
	$data=trim($rec);
	if(strpos($comment,"password")!==false){
		$err=$rec!=$oldrec  ?  $lable.": ".RPT_PSW_ERR : $err ;
	}
	return $err;
}

public static function get_fields($table){
	$aFlds = array();
	$rFlds = rs::inMatrix("SHOW FULL COLUMNS FROM ".$table);
	foreach($rFlds as $k => $v){ # ARRAY PER VERIFICA TESTO SELECT OPTION
		$aFlds[] = $v['Field'];
	}
	return $aFlds;
}

static function uni_ctrl($io){
	$rec=$io->val;
	$sql_type=$io->sql_type;
	$lable=$io->lable;
	$comment=$io->comment;
	$not_null=$io->not_null;
	$dbtable=$io->dbtable;
	$dbkeyfld=$io->dbkeyfld;
	$dbkeyval=$io->dbkeyval;
	$err=false;
	if($io->fkey=="UNI"){
		$count=rs::fld2var($q="SELECT COUNT(*) AS TOT FROM $dbtable WHERE ".$io->name."='".$rec."' AND ".$io->dbkeyfld."<>'".$io->dbkeyval."'");
		$err=$count>=1 ? $lable.": ".UNIQUE_ERR." (".$rec.")" : $err ;
	}
	// print $lable." not_null->".$not_null." data->".$data."<br>";
	return $err;
}	
			
static function syntax_ctrl($io){
	$rec = $io -> val;
	$sql_type = $io -> sql_type;
	$lable = $io -> lable;
	$comment = $io -> comment;
	$not_null = $io -> not_null;
	$err = false;
	$data = trim($rec);
	if($data == "") return $err;
	if(strpos($sql_type,"date")!==false || strpos($sql_type,"datetime")!==false){
		$is_data = false;
		if(strlen($data) == 10){ # USO DATA IN CAMPO TESTO UNICO
			$ad = explode('/', $data);
			if(count($ad) == 3){ # HO TRE CAMPI DATA
				$is_data = true;
			}
		}
		$data = $is_data ? $ad[2].$ad[1].$ad[0] : str_pad($rec,6,"0",STR_PAD_LEFT);
	
		# CONTROLLO CORRETTEZZA DELLA DATA
		$dt=new dtime($data);
		$err = $dt -> err != false ? $dt -> err : $err;
	}
	elseif(strpos($sql_type,"int")!==false ){
		$err=$data!=strval(intval($data)) ||  strlen($data)!=strlen(strval(intval($data))) ?  $lable.": ".IN_ERR_SINTAX : $err ;
	}
	elseif(strpos($sql_type,"decimal")!==false){
		$data." !=".strval(floatval($data))." ".strlen($data)."!= ".strlen(strval(floatval($data)));
		$err=$data!=strval(floatval($data)) ||  strlen($data)!=strlen(strval(($data))) ?  $lable.": ".IN_ERR_SINTAX : $err ;
	}
	elseif(strpos($comment,"e-mail")!==false){
		$err=stringa::isEmail($rec)!=false  ?  $lable.": ".IN_ERR_EMAIL : $err ;
	}
	elseif(strpos($comment,"username")!==false || strpos($comment,"password")!==false){
		$err=stringa::isUsername( $lable,$rec,0)!=false  ?  $lable.": ".USERNAME_ERR_SINTAX : $err ;
	}
	return $err;
}

static function max_ctrl($io){
	$rec=$io->val;
	$sql_type=$io->sql_type;
	$lable=$io->lable;
	$comment=$io->comment;
	$not_null=$io->not_null;
	$err=false;
	$data=trim($rec);
	if($data == "") return $err;
	if(strpos($sql_type,"blob")!==false || strpos($sql_type,"TEXT")!==false){
		if(strpos($sql_type,"tiny")!==false){
			$errstrlen($rec)+1 > pow(2,8) ? $lable.": ".MAXL_ERR. "(".pow(2,8).")" : $err;
		}
		else if(strpos($sql_type,"medium")!==false || strpos($sql_type,"TEXT")!==false){
			$err=strlen($rec)+3 > pow(2,24) ? $lable.": ".MAXL_ERR. "(".pow(2,24).")" : $err;
		}
		else if(strpos($sql_type,"long")!==false || strpos($sql_type,"TEXT")!==false){
			$err=strlen($rec)+4 > pow(2,32) ? $lable.": ".MAXL_ERR. "(".pow(2,32).")" : $err;
		}
		else {
			$err=strlen($rec)+2 > pow(2,16) ? $lable.": ".MAXL_ERR. "(".pow(2,16).")" : $err;
		}
	}
	return $err;
}
		
static function null_ctrl($io){
	$rec=$io->val;
	$sql_type=$io->sql_type;
	$lable=$io->lable;
	$comment=$io->comment;
	$not_null=$io->not_null;
	$err=false;
	$data=trim($rec);
	$datalen= strlen(trim($rec));
	$data= trim($rec);
	$err=$not_null==1 && $data=="" ? $lable.": ".NOT_NULL_ERR : $err;
	// print $lable." not_null->".$not_null." data->".$data."<br>";
	return $err;
}
		
static function get_comment($comment){ # COMMENTO: min(3) max(60) not_empty
	$aRet = array();
	$aRet['msg'] = false;
	$aRet['not_empty'] = false;
	$aRet['min'] = false;

	if($pos = strpos($comment, 'msg') !== false){
		$pos += strlen('msg');
		$tmp = substr($comment, $pos);
		$tmp = stringa::bfw($tmp, '(', ')');
		$aRet['msg'] = constant($tmp);
	}
	if($pos = strpos($comment, 'min') !== false){
		$pos += strlen('min');
		$tmp = substr($comment, $pos);
		$tmp = stringa::bfw($tmp, '(', ')');
		$aRet['min'] = $tmp;
	}
	if($pos = strpos($comment, 'not_empty') !== false){
		$aRet['not_empty'] = true;
	}
	return $aRet;
}

static function min_ctrl($io){
	$rec=$io->val;
	$sql_type=$io->sql_type;
	$lable=$io->lable;
	$comment=$io->comment;
	$not_null=$io->not_null;
	$err=false;
	$data=trim($rec);
	if($data=="") return $err;
	//$minl=is_numeric($comment) ? $comment : 0;
	$datalen= strlen($data);
	$aComment = self::get_comment($comment);
	
	$minl = !empty($aComment['min']) ? $aComment['min'] : 0;
	
	
	if($aComment['not_empty']){
		$msg = $aComment['msg'] ? $aComment['msg'] : NOT_NULL_ERR;
		$err = empty($rec) ? $lable.": ".$msg : $err;
	}
	elseif(strpos($sql_type,"decimal")!==false || strpos($sql_type,"int")!==false){
		$err = $data<$minl ? $lable.": ".MINL_NUM_ERR. "(".$minl.")" : $err;
	}
	elseif(strpos($comment,"username")!==false || strpos($comment,"password")!==false){
		$minl=intval(stringa::rightfrom($comment,","));
		$err=$datalen<$minl ? $lable.": ".MINL_ERR. "(".$minl.")" : $err;
	}
	else{
		$err=$datalen<$minl ? $lable.": ".MINL_ERR. "(".$minl.")" : $err;
	}
	return $err;
}

static function err(){
	$err=trim(mysql_error()." ".mysql_errno());
	$err =$err=="" ? false : $err;
	return $err ;
}
		
static function set0(){return @mysql_query("SET AUTOCOMMIT=0") ? false : mysql_errno()." ".mysql_error();}
static function set1(){return @mysql_query("SET AUTOCOMMIT=1") ? false : mysql_errno()." ".mysql_error();}
static function start(){return @mysql_query("START TRANSACTION") ? false : mysql_errno()." ".mysql_error();}
static function roll(){return @mysql_query("ROLLBACK") ? false : mysql_errno()." ".mysql_error();}
static function comm(){return @mysql_query("COMMIT") ? false : mysql_errno()." ".mysql_error();}

static function lable($atable){
	$atable = !is_array($atable) ? array($atable) : $atable;
	$name=array();
	foreach($atable as $table){
		if($rs=@mysql_query($q="SHOW FULL COLUMNS FROM $table")){
			while($row=mysql_fetch_assoc($rs)){ 
				$name[$row['Field']]=ucfirst(strtolower($row['Field']));
			}
			@mysql_free_result($rs);
		}
		else{
			err::eko("Invalid sql for showfull");
		}
	}
	return $name;
}	
		
static function lastid($tabella){
	$lastid=NULL;
	if($rs=@mysql_query($q="SELECT COUNT(*) AS lastid FROM  $tabella")){
		$row=mysql_fetch_assoc($rs);
		$lastid=intval($row["lastid"]);
		@mysql_free_result($rs);
	}
	else{
		err::eko("Invalid sql for lastid");
	}
	return $lastid;
}
	
static function cursor($qTotRec,$offset){
	$rs=mysql_query($qTotRec);
	if($rs == true){
		$totrecord=mysql_num_rows($rs);
		mysql_free_result($rs);
	}
	else{
		err::sql("Invalid sql for cursor");
		$totrecord=0;
	}
	$totcursor=ceil($totrecord/$offset);
	$icursor= array_key_exists('icursor',$_REQUEST) ? $_REQUEST['icursor'] : 0 ;
	$icursor= $icursor>$totcursor ? $totcursor : $icursor;
	if(array_key_exists('tf',$_REQUEST)) $n=0-$icursor;
	else if(array_key_exists('pt',$_REQUEST)) $n=-10;
	else if(array_key_exists('po',$_REQUEST)) $n=-1;
	else if(array_key_exists('no',$_REQUEST)) $n=1;
	else if(array_key_exists('nt',$_REQUEST)) $n=10;
	else if(array_key_exists('tl',$_REQUEST)) $n=($totcursor-1)-$icursor;
	else $n=0;
	$icursor=$icursor+$n;
	$icursor= $icursor>$totcursor-1 ? $totcursor-1 : $icursor;
	$icursor= $icursor<0 ? 0 : $icursor;
	$limit_sql=" LIMIT ".($icursor*$offset).",".$offset."";
	return array($icursor,$totcursor,$totrecord,$aCurs=array("icursor"=>$icursor),$limit_sql);
}
		
# return resourse connessione
static function conn(){
	$conx=NULL;
	if(@mysql_connect(DBHOST,DBUSER,DBPSW)) ;
	else die("Errore di connessione database");
	if(mysql_select_db(DBNAME));
	else die("Errore di selezione database");
	return $conx;
}
	
public static function dmlfield($rec){
	$str="(";
	$key=array_keys($rec);
	$str.=implode(",",$key);
	return $str.=")";
}
		
public static function dmlval($rec){
	$str="(";
	foreach($rec as $key => $val){
		if(substr($key,0,2) == 'D_'){ $val = dtime::my2db($val); }
	
		if(is_null($val) || (is_bool($val) && $val==false) || (is_string($val) && $val=="")) $str.="NULL,";
		else $str.="'".mysql_real_escape_string($val)."',";
	}
	$str=substr($str,0,strlen($str)-1);
	return $str.=")";	
}	
		
public static function dmlsetfield($rec){
	$str="";
	foreach($rec as $key => $val){
		if(substr($key,0,2) == 'D_'){ $val = dtime::my2db($val); }
	
		if(is_null($val) || (is_bool($val) && $val==false) || (is_string($val) && $val=="")) $valfield='NULL';
		else $valfield="'".mysql_real_escape_string($val)."'";	
		
		$str.=$key."=".$valfield.",";
	}
	$str=substr($str,0,strlen($str)-1);
	return $str;
}

static function and_fld($fld=array()){ 
	$str="";
	foreach($fld as $k=>$v){
		$str.=" ".$k."='".$v."' AND";
	}
	return substr($str,0,strlen($str)-3)." ";
}
	
static function where_or($tab,$field,$arr){
	$str="";
	foreach($arr as $k=>$v){
		if(is_null($v) || (is_bool($v) && $v==false) || (is_string($v) && $v=="")){ $str.=" ".$tab.".".$field." IS "."NULL"." OR"; }
		else{$str.=" ".$tab.".".$field."='".$v."' OR";}
	}
	return $str != "" ? " (".stringa::rcut($str,2).")" : "";
}
	
static function where_and($tab,$fld=array()){ 
	$str="";
	foreach($fld as $k=>$v){
		if(is_null($v) || (is_bool($v) && $val==v) || (is_string($v) && $v=="")){ $str.=" ".$tab.".".$k." IS "."NULL"." AND"; }
		else{ $str.=" ".$tab.".".$k."='".$v."' AND"; }
	}
	return $str!="" ? " ".stringa::rcut($str,3) : "";
}
		
static function or_and($or,$and){
	$str="";
	$or.=$or!="" && $and!="" ? " AND " : "";
	$str=$or.$and;
	return $str=$str!="" ? " WHERE ".$str : $str;
}
	
static function where_fld($fld=array()){
	$str="";
	foreach($fld as $k=>$v){
		$str.=" ".$k."='".$v."' AND";
	}
	return $str!="" ? "WHERE ".substr($str,0,strlen($str)-3) : "";
}	
			
static function execdml($dml="INSERT oppure UPDATE DELETE",$table,$rec=array("COGNOME"=>'Fo'),$aPrime=array('ID_TABELLA'=>3)){
	$err=false;
	reset($aPrime);
	$and_fld=rs::and_fld($aPrime);
	$sql="";
	if($dml=="INSERT"){
		$sql="INSERT INTO ".$table." ".rs::dmlfield($rec)." VALUES ".rs::dmlval($rec)." ";
	}
	else if($dml=="DELETE"){
		$sql="DELETE FROM ".$table." WHERE ".$and_fld."";
	}
	else if($dml=="UPDATE"){
		$sql="UPDATE ".$table." SET ".rs::dmlsetfield($rec)."  WHERE ".$and_fld." ";
	}
	else{
		err::eko("Invalid crud op for execdml");
	}
	if(EXECDML_SQL == 1){
		arr::stampa($_POST);
		print $sql;
	}
	if($rs = @mysql_query($sql));
	else{
		$err=rs::err();
	}
	return $err;
}	
		
static function crud($dml="INSERT oppure UPDATE DELETE",$table,$rec=array("COGNOME"=>'Fo'),$aPrime=array('ID_TABELLA'=>3)){
	$not_err=false;
	reset($aPrime);
	$and_fld=rs::and_fld($aPrime);
	$sql="";
	if($dml=="INSERT"){
		$sql="INSERT INTO ".$table." ".rs::dmlfield($rec)." VALUES ".rs::dmlval($rec)." ";
	}
	else if($dml=="DELETE"){
		$sql="DELETE FROM ".$table." WHERE ".$and_fld."";
	}
	else if($dml=="UPDATE"){
		$sql="UPDATE ".$table." SET ".rs::dmlsetfield($rec)."  WHERE ".$and_fld." ";
	}
	else{
		err::eko("Errore di sistema: Invalid crud");
	}
	if($rs=@mysql_query($sql)){
		$not_err=$sql."<br/>";
	}
	else{
		$not_err=mysql_error()."<br/>";
	}
	print $not_err;
}		

static function &inMenu($keyArray="ID_utente", $elementArray=array("NOME","&nbsp;(","SOPRANOME",")"), $addBlank=true, $sql="SELECT * oppure SELECT ID_CAMPO,NOME"){
	$rs2arr=array();
	if(is_array($addBlank) && !empty($addBlank)){
		reset($addBlank);
		$rs2arr=array(key($addBlank)=>current($addBlank));
	}
	if(is_bool($addBlank) && $addBlank==true){
		$rs2arr=array(""=>"");
	}
	if($resultset=@mysql_query($sql)){
		while ($row =mysql_fetch_assoc($resultset)){
			$primkey = is_string($keyArray) &&  $keyArray=="" ? (int) $i=0 : (string)$row[$keyArray];
			$value="";
			foreach($elementArray as $field){
				if(isset($row[$field])) $value .= $row[$field];
				else $value .= $field;
			}
			$primkey=(string)$primkey;	
			$rs2arr[$primkey]=$value;
			is_numeric($primkey) ? $primkey++ : $primkey;
		}
		@mysql_free_result($resultset);
	}
	else{
		err::eko("Invalid sql for inMenu");
	}
	return $rs2arr;
}	
			
static function &inMatrix($sql="SELECT * oppure SELECT ID_CAMPO,NOME...."){
	$rs2arr=array();
	if($rs=@mysql_query($sql)){
		$i=0;
		while($row=mysql_fetch_object($rs)){
			for($j=0;$j<mysql_num_fields($rs);$j++){
				$name=mysql_field_name($rs,$j);
				$arow[$name]=$row->$name;
			}
			$rs2arr[$i]=$arow;
			$i++;
		}	
		@mysql_free_result($rs);
	}
	else	
	err::sql('Invalid sql inMatrix: '.BR.$sql.BR.__FILE__.' on line: '.__LINE__.BR);
	return $rs2arr;
}

static function &inKey($sql, $key, $val, $keyup=false){ # RESTITUISCE UN ARRAY CHIAVE => VALORE
	$rs2arr=array();
	if($rs = @mysql_query($sql)){
		while($row = mysql_fetch_assoc($rs)){
			if($keyup) $rs2arr[strtoupper($row[$key])]=$row[$val];
			else $rs2arr[$row[$key]]=$row[$val];
		}
		@mysql_free_result($rs);
	}
	else	
	err::sql("Invalid sql inKey ".$sql);
	return $rs2arr;
}
		
static function &arr($sql="SELECT * oppure SELECT ID_CAMPO,NOME...."){
	$rs2arr=array();
	if($rs=@mysql_query($sql)){
		if(mysql_num_fields($rs)==1){
			while($row=mysql_fetch_object($rs)){
				$name=mysql_field_name($rs,0);
				$rs2arr[]=$row->$name;
			}
		}
		else{
			err::sql("inArr fetcha una solo campo");
		}
		@mysql_free_result($rs);
	}
	else	
	err::sql('Invalid sql inMatrix: '.BR.$sql.BR.__FILE__.' on line: '.__LINE__.BR);
	return $rs2arr;
}
		
static function &inMatrix2($sql="SELECT * oppure SELECT ID_CAMPO,NOME...."){
	$rs2arr=array();
	if($rs=@mysql_query($sql)){
		$i=0;
		do{
			for($j=0;$j<mysql_num_fields($rs);$j++){
				$name=mysql_field_name($rs,$j);
				$arow[$name]=isset($row->$name) ? $row->$name : "";

			}
			$rs2arr[$i]=$arow;
			$i++;
		}	
		while($row=mysql_fetch_object($rs));
		@mysql_free_result($rs);
	}
	else	
	err::sql('Invalid sql inMatrix: '.BR.$sql.BR.__FILE__.' on line: '.__LINE__.BR);
	return $rs2arr;
}
		
		
static function &sql2lbl($sql="SELECT * oppure SELECT ID_CAMPO,NOME.... WHERE 1=0 oppure WHERE ID_CAMPO='15' oppure WHERE ID_CAMPO='ABC'"){
	$lable=array();
	$not_costant=array();
	$field=rs::name_fields($sql);	
	foreach($field  as $k => $fld){
		if(defined(strtoupper($fld))){
			$lable[$fld]=constant(strtoupper($fld));
		}
		else{
			$lable[$fld]=strtolower($fld);
		}
	}
	return $lable;
}		

static function &objarr($sql="SELECT * oppure SELECT ID_CAMPO,NOME...."){
	err::sql($sql);
	$rs2arr=array();
	if($rs=@mysql_query($sql)){
		$i=0;
		do{
			$row=mysql_fetch_object($rs);
			for($j=0;$j<mysql_num_fields($rs);$j++){
				$name=mysql_field_name($rs,$j);
				isset($row->$name) ? $row->$name : $row->$name=false;
			}
			$rs2arr[]=$row;
			$i++;
		}
		while($i<mysql_num_rows($rs));
	}
	else	
	err::sql('Invalid sql inMatrix : '.BR.$sql.BR.__FILE__.' on line: '.__LINE__.BR);
	return $rs2arr;
}	
		
static function &rec2arr($sql="SELECT * oppure SELECT ID_CAMPO,NOME.... WHERE 1=0 oppure WHERE ID_CAMPO='15' oppure WHERE ID_CAMPO='ABC'"){
	$rs2arr=array();
	if($rs=@mysql_query($sql)){
		if(@mysql_num_rows($rs)<=1){
			$row=mysql_fetch_object($rs);
			for($j=0;$j<mysql_num_fields($rs);$j++){
				$name=mysql_field_name($rs,$j);
				$rs2arr[$name]=isset($row->$name) ? $row->$name : "";
			}
		}
		else	
		err::sql("Attenzione rec2arr fetcha al massimo 1 riga");
		@mysql_free_result($rs);
	}
	else{
		err::sql('Invalid sql for rec2arr '.$sql.BR);
	}
	return $rs2arr;
}
		
static function &rec2obj($sql="SELECT * oppure SELECT ID_CAMPO,NOME.... WHERE 1=0 oppure WHERE ID_CAMPO='15' oppure WHERE ID_CAMPO='ABC'"){
	$row=false;
	if($rs=@mysql_query($sql)){
		if(@mysql_num_rows($rs)<=1){
			$row=(object)mysql_fetch_object($rs);
			for($j=0;$j<mysql_num_fields($rs);$j++){
				$name=mysql_field_name($rs,$j);
				isset($row->$name) ? $row->$name : $row->$name="";
			}
		}
		else{
			err::sql("Attenzione rec2obj fetcha al massimo 1 riga");
		}
		@mysql_free_result($rs);
	}
	else{
		err::sql("Invalid sql for rec2obj");
	}
	return $row;
}
		
static function &fld2var($sql="SELECT * oppure SELECT ID_CAMPO,NOME.... WHERE 1=0 oppure WHERE ID_CAMPO='15' oppure WHERE ID_CAMPO='ABC'"){
	$fld2var=NULL;
	if($rs=@mysql_query($sql)){
		if(@mysql_num_rows($rs)<=1){
			$row=mysql_fetch_object($rs);
			if(mysql_num_fields($rs)==1){
				$name=mysql_field_name($rs,0);
				$fld2var=isset($row->$name) ? $row->$name : "";
			}
			else {
				err::eko("Attenzione fld2var fetcha al massimo un campo");
			}
		}
		else	
		err::sql("Attenzione fld2var fetcha al massimo 1 riga");
		@mysql_free_result($rs);
	}
	else{
			err::sql("Invalid sql for fld2var");
		}
	return $fld2var;
}
		
static function &fld2arr($sql="SELECT * oppure SELECT ID_CAMPO,NOME.... WHERE 1=0 oppure WHERE ID_CAMPO='15' oppure WHERE ID_CAMPO='ABC'"){
	$fld2var=array();
	if($rs=@mysql_query($sql)){
		if(mysql_num_fields($rs)==1){
			while($row=mysql_fetch_object($rs)){
				$name=mysql_field_name($rs,0);
				$fld2var[]=isset($row->$name) ? $row->$name : "";
			}
		}
		else{
			err::eko("Attenzione fld2arr fetcha al massimo un campo");
		}
		@mysql_free_result($rs);
	}
	else{
		err::eko(err::sql()." "."Invalid sql for fld2arr");
	}
	return $fld2var;
}
		
static function &id2arr($sql="SELECT * oppure SELECT ID_CAMPO,NOME.... WHERE 1=0 oppure WHERE ID_CAMPO='15' oppure WHERE ID_CAMPO='ABC'"){
	$fld2var = array();
	if($rs=@mysql_query($sql)){
		$nField=mysql_num_fields($rs);

		while($row=mysql_fetch_object($rs)){
			$id=mysql_field_name($rs,0);
			$name=mysql_field_name($rs,1);
			$key=(string)$row->$id;
			$fld2var[$key]=isset($row->$name) ? $row->$name : "";
		}
		@mysql_free_result($rs);
	}
	else{
		err::sql('Invalid sql for id2arr: '.BR.$sql.BR.__FILE__.' on line: '.__LINE__.BR);
	}
	return $fld2var;
}
		
static function &sql2label($sql="SELECT * oppure SELECT ID_CAMPO,NOME.... WHERE 1=0 oppure WHERE ID_CAMPO='15' oppure WHERE ID_CAMPO='ABC'"){
	$lable=array();
	$not_costant=array();
	$field=rs::name_fields($sql);	
	foreach($field  as $k => $fld){
		if(defined(strtoupper($fld))){
			$lable[$fld]=constant(strtoupper($fld));
		}
		else{
		$not_costant[]=strtoupper($fld). "not defined";
		}
	}
	if(count($not_costant)>0){
		print_r($not_costant);
	}
	return $lable;
}
	
static function label_frmt($lable){
	$not_costant=array();
	foreach($lable as $k=>$v){
		if(strpos($k,"ID_")!==false){
			$fld=strtoupper(stringa::lcut($k,3));
			$fld=$fld=="GROUP" ? "GRUPPO": $fld;
			if(defined($fld)){
			$lable[$k]=constant($fld);}
			else 
			$not_costant[]=$fld. "label_frmt not defined";
		}
	}
	if(count($not_costant)>0){
		print_r($not_costant);
	}
	return $lable;	
}
	
	# return array["nome campo"]=type "varchar" oppure "int" ....
static function name_fields($sql){
	$rs2arr=array();
	if($rs=@mysql_query($sql)){
		$campi = mysql_num_fields($rs);
		for ($i=0; $i < $campi; $i++){
			$rs2arr[]= mysql_field_name($rs, $i);
		}
		@mysql_free_result($rs);
	}
	else
	err::sql("Invalid sql for name_fld");
	return $rs2arr;	
}
		
static function &type($table=""){
	$rs2arr=array();
	if($rs=@mysql_query("SHOW FULL COLUMNS FROM $table")){
		while($row=mysql_fetch_assoc($rs)){
			$rs2arr[$row['Field']]=(string) strpos($row['Type'],"(")!==false ? trim(stringa::leftfrom($row['Type'],"(")) : $row['Type'];
		}
		@mysql_free_result($rs);
	}
	else{
		err::eko("Invalid sql for type");
	}
	return $rs2arr;
}
		
static function &field($table=""){
	$rs2arr=array();
	if($rs=@mysql_query("SHOW FULL COLUMNS FROM $table")){
		while($row=mysql_fetch_assoc($rs)){
			$rs2arr[]=$row['Field'];
		}
		@mysql_free_result($rs);
	}
	else{
		err::eko(err::sql()." "."Invalid sql for field");
	}
	return $rs2arr;
}
		
static function mytype($arr){
	foreach($arr as $k=>$v){
		$type="text";
		$type=strpos($v,"text")!==false ? "textarea" : $type;
		$type=$v=="date" ? "ddmm4y" :  $type;
		$arr[$k]=$type;
	}
	return $arr;
}

static function showfull3($atabelle,$rec,$lable,$add,$skipExt){
	$name=array();
	$type=array();
	$lenght=array();
	$default=array();
	$not_null=array();
	$dec=array();
	$key=array();
	$aval=array();
	$addblank=array();
	$comment=array();
	$sql_type=array();
	$is_justpri=0;
	$field_list = array();
	
	foreach($atabelle as $tabella){
		$$tabella = rs::inMatrix( $q="SHOW FULL COLUMNS FROM $tabella");
		foreach($$tabella as $k => $v){ # ARRAY PER VERIFICA TESTO SELECT OPTION
			$field_list[] = $v['Field'];
		}
	}
	$ctrl=0;
	foreach($lable as $fld=>$desc){
		$tabx = NULL;
		$xrow=-1;
		foreach($atabelle as $tabella){
			$irow=0;
			$is_add=0;
			foreach($$tabella as $row){
				if($row["Field"]==$fld){
					$tabx=$$tabella;
					$xrow=$irow;
					break;
				}
				$irow++;
			}
			if(!is_null($tabx))
			break;
		}
		if($tabx==NULL ){
			print $fld." not find in tables (-)";
			return;
		}
		$row=$tabx[$xrow];
		$Field=$row['Field'];
		$name[]=$Field;
		$aval[$Field]=array();
		$addblank[$Field]=0;
		$len=stringa::strbeetween($row['Type'],"(",")");
		$lenght[$Field]=stringa::leftfrom($len,",");
		$dec[$Field]=stringa::rightfrom($len,",");
		$lenght[$Field]=trim($lenght[$Field])==""? $len : $lenght[$Field];
		$comment[$Field]=$row["Comment"];
		$def = is_null($row['Default']) || trim($row['Default'])=="" || $row['Default']=="CURRENT_TIMESTAMP" ? NULL : $row['Default'];
		$default[$Field]=$def;
		if(array_key_exists($Field,$rec) && $def!="" && strlen($def)>0 && $rec[$Field]=="" && strlen($rec[$Field])==0){
			$rec[$Field]=$def;
		}
		$type[$Field] = strpos($row['Type'],"(")!==false ? trim(stringa::leftfrom($row['Type'],"(")) : $row['Type'];
		$sql_type[$Field]=$row['Type'];
		$type[$Field] = rs::type_parser($type[$Field],$lenght[$Field],$comment[$Field]);
		$not_null[$Field] = $row['Null']=='YES' ? 0 : 1;
		$key[$Field]=$row["Key"];
		
		if($key[$Field]=="MUL" && !in_array($Field,$skipExt)){
			$is_dpt = false;
			if(substr($Field,strlen($Field)-1,strlen($Field))=="2"){
				$reftable=stringa::rcut(stringa::lcut($Field,3),1)."S2";
			}
			else{ 
				$reftable = stringa::lcut($Field,3)."S";
			}
			$refField=stringa::lcut($Field,3);
			$where="";
			$reftable = strtolower($reftable);
			
			$qIdarr = "SELECT $Field, $refField from $reftable $where ORDER BY $refField ASC";

			if($Field == 'ID_SELF_USER'){
				$refField = 'RAGIONESOCIALE_USR';
				$reftable = 'users';
				$qIdarr = "SELECT ID_USER, $refField from $reftable WHERE IS_FRANCHISOR = '1' ORDER BY $refField ASC";
			}



			if(!in_array($refField,$field_list)){
				if(in_array($refField.'_IT',$field_list)) { $refField  = $refField.'_IT'; } # ESCAPE TESTO SELECT OPTION. SI POTRA' PREVEDERE DI INSERIRE IL CAMPO LIGUA
				elseif(in_array('NOME_'.$refField, $field_list)){ $refField  = 'NOME_'.$refField; }
				elseif(in_array('K1_'.$refField, $field_list)) { $refField = 'K1_'.$refField; $is_dpt = true; }
				elseif(in_array('K0_'.$refField, $field_list)) { $refField = 'K0_'.$refField; $is_dpt = true; }
				
				if($is_dpt){
					# USO L'OGGETTO io::controls_js($table = 'descriptors', $ramo = 'statis', $valore = 0)
					# POSSO ANCHE PASSARE TUTTE LE VARIABILI $ordinamento -> js per automatizzare la cosa
					
					$tbl_name = stringa::id2tbl($refField);
					$qIdarr = "SELECT
					descriptors.ID_DESCRIPTOR,
					descriptors.DESCRIPTOR_".LANG_DEF.",
					descriptors_types.DESCRIPTORS_TABLE,
					descriptors.ID_DESCRIPTOR_SELF,
					descriptors_types.DESCRIPTORS_TYPE
					FROM
					descriptors_types
					Left Join descriptors ON descriptors.ID_DESCRIPTORS_TYPE = descriptors_types.ID_DESCRIPTORS_TYPE
					WHERE descriptors_types.DESCRIPTORS_TABLE = '".$tbl_name."'
					ORDER BY descriptors.DESCRIPTOR".LANG_DEF." ASC
					";
				}
			}
			$aval[$Field] = rs::id2arr($qIdarr);
			$type[$Field] = "select";
			$addblank[$Field]=0;
		}
		$ctrl++;
	}
	
	if(array_key_exists($Field, $add)){ # COSA FA STA COSA???
		$New=$add[$Field];
		$name[]=$New;
		$lenght[$New]=$lenght[$Field];
		$dec[$New]=$dec[$Field];
		$default[$New]=$default[$Field];
		$type[$New]=$type[$Field];
		$not_null[$New]=$not_null[$Field];
		$key[$New]=$key[$Field];
		$comment[$New]=$comment[$Field];
	}
	return array($name,$rec,$type,$lenght,$default,$not_null,$lable,$dec,$key,$aval,$addblank,$comment,$sql_type);	
}

static function light($atabelle,$rec,$lable){ // NUOVO METODO CHE USA I VALORI DELL'OGGETTO ordinamento
	$is_dpt = false;
	$dpt_select = array();
	$dpt_select['aFval'] = array();
	$name=array();
	$type=array();
	$disabled=array();
	$lenght=array();
	$default=array();
	$not_null=array();
	$dec=array();
	$key=array();
	$aval=array();
	$addblank=array();
	$comment=array();
	$sql_type=array();
	$is_justpri = 0;
	$field_list = array();
	
	foreach($atabelle as $tabella){
		$$tabella = rs::inMatrix( $q="SHOW FULL COLUMNS FROM $tabella");
		foreach($$tabella as $k => $v){ # ARRAY PER VERIFICA TESTO SELECT OPTION
			$field_list[] = $v['Field'];
		}
	}
	$ctrl=0;
	foreach($lable as $fld => $desc){
		$tabx = NULL;
		$xrow=-1;
		foreach($atabelle as $tabella){
			$irow=0;
			$is_add=0;
			foreach($$tabella as $row){
				if($row["Field"]==$fld){
					$tabx=$$tabella;
					$xrow=$irow;
					break;
				}
				$irow++;
			}
			if(!is_null($tabx))
			break;
		}
		if($tabx==NULL ){
			print $fld." not find in tables (-)";
			return;
		}
		$js = '';
		$row = $tabx[$xrow];
		$Field = $row['Field'];
		$name[] = $Field;
		$aval[$Field] = array();
		$addblank[$Field] = 0;
		$len = stringa::strbeetween($row['Type'],"(",")");
		$lenght[$Field] = stringa::leftfrom($len,",");
		$dec[$Field] = stringa::rightfrom($len,",");
		$lenght[$Field] = trim($lenght[$Field])==""? $len : $lenght[$Field];
		$comment[$Field]=$row["Comment"];
		$def = is_null($row['Default']) || trim($row['Default'])=="" || $row['Default']=="CURRENT_TIMESTAMP" ? NULL : $row['Default'];
		$default[$Field] = $def;
		if(array_key_exists($Field, $rec) && $def!="" && strlen($def)>0 && $rec[$Field]=="" && strlen($rec[$Field])==0){
			$rec[$Field]=$def;
		}

		$type[$Field] = strpos($row['Type'],"(")!==false ? trim(stringa::leftfrom($row['Type'],"(")) : $row['Type'];
		$sql_type[$Field]=$row['Type'];
		$type[$Field] = rs::type_parser($type[$Field],$lenght[$Field],$comment[$Field]);
		$not_null[$Field] = $row['Null'] == 'YES' ? 0 : 1;
		
		if(strpos($comment[$Field],"not_null")!==false){ $not_null[$Field] = 1; }

		$key[$Field] = $row["Key"];
		
		$ctrl++;
	}

	return array($name, $rec, $type, $lenght, $default, $not_null, $lable, $dec, $key, $aval, $addblank, $comment, $sql_type, $js, $disabled);	
}	



static function showfulljs($atabelle,$rec,$lable,$add,$skipExt, $ordinamento){ # NUOVO METODO CHE USA I VALORI DELL'OGGETTO ordinamento
	$name=array();
	$type=array();
	$disabled=array();
//	$js=array();
	$lenght=array();
	$default=array();
	$not_null=array();
	$dec=array();
	$key=array();
	$aval=array();
	$addblank=array();
	$comment=array();
	$sql_type=array();
	$is_justpri = 0;
	$field_list = array();
	
	foreach($atabelle as $tabella){
		$$tabella = rs::inMatrix( $q="SHOW FULL COLUMNS FROM $tabella");
		foreach($$tabella as $k => $v){ # ARRAY PER VERIFICA TESTO SELECT OPTION
			$field_list[] = $v['Field'];
		}
	}
	$ctrl=0;
	foreach($lable as $fld => $desc){
		$tabx = NULL;
		$xrow=-1;
		foreach($atabelle as $tabella){
			$irow=0;
			$is_add=0;
			foreach($$tabella as $row){
				if($row["Field"]==$fld){
					$tabx=$$tabella;
					$xrow=$irow;
					break;
				}
				$irow++;
			}
			if(!is_null($tabx))
			break;
		}
		if($tabx==NULL ){
			print $fld." not find in tables (-)";
			return;
		}
		$js = '';
		$row = $tabx[$xrow];
		$Field = $row['Field'];
		$name[] = $Field;
		$aval[$Field] = array();
		$addblank[$Field] = 0;
		$len = stringa::strbeetween($row['Type'],"(",")");
		$lenght[$Field] = stringa::leftfrom($len,",");
		$dec[$Field] = stringa::rightfrom($len,",");
		$lenght[$Field] = trim($lenght[$Field])==""? $len : $lenght[$Field];
		$comment[$Field]=$row["Comment"];
		$def = is_null($row['Default']) || trim($row['Default'])=="" || $row['Default']=="CURRENT_TIMESTAMP" ? NULL : $row['Default'];
		$default[$Field] = $def;
		if(array_key_exists($Field, $rec) && $def!="" && strlen($def)>0 && $rec[$Field]=="" && strlen($rec[$Field])==0){
			$rec[$Field]=$def;
		}

		$type[$Field] = strpos($row['Type'],"(")!==false ? trim(stringa::leftfrom($row['Type'],"(")) : $row['Type'];
		$sql_type[$Field]=$row['Type'];
		$type[$Field] = rs::type_parser($type[$Field],$lenght[$Field],$comment[$Field]);
		$not_null[$Field] = $row['Null'] == 'YES' ? 0 : 1;
		
		if(strpos($comment[$Field],"not_null")!==false){ $not_null[$Field] = 1; }
		
		$key[$Field]=$row["Key"];
		
		if($key[$Field]=="MUL" && !in_array($Field,$skipExt)){
			$is_dpt = false;
			if(substr($Field,strlen($Field)-1,strlen($Field))=="2"){
				$reftable=stringa::rcut(stringa::lcut($Field,3),1)."S2";
			}
			else{ 
				$reftable = stringa::lcut($Field,3)."S";
			}
			$refField=stringa::lcut($Field,3);
			$where="";
			$reftable = strtolower($reftable);
			$qIdarr = "SELECT $Field, $refField from $reftable $where ORDER BY $refField ASC";
			
			if(strpos($Field, 'ID_SELF_USER') !== false){
				$refField = stringa::rightfromlast($refField, '_');
				$reftable = stringa::field2table($refField);
				$id_field = 'ID_'.$refField;
				$qIdarr = "SELECT ID_USER, RAGIONESOCIALE_USR FROM users WHERE IS_FRANCHISOR = '1' ORDER BY USER ASC";
			}
			elseif(strpos($Field, 'SELF') !== false){
				$refField = stringa::rightfromlast($refField, '_');
				$reftable = stringa::field2table($refField);
				$qIdarr = "SELECT $Field, $refField from $reftable $where ORDER BY $refField ASC";
			}
			elseif(strpos($Field, 'ID_GRUPPI') !== false){
				$refField = stringa::rightfromlast($refField, '_');
				$reftable = stringa::field2table($refField);
				$qIdarr = "SELECT ID_GRUPPI, TITLE from gruppis $where ORDER BY TITLE ASC";
			}
			if(!in_array($refField,$field_list)){
				if(in_array($refField.'_IT',$field_list)) { $refField  = $refField.'_IT'; } # ESCAPE TESTO SELECT OPTION. SI POTRA' PREVEDERE DI INSERIRE IL CAMPO LIGUA
				elseif(in_array('NOME_'.$refField, $field_list)){ $refField  = 'NOME_'.$refField; }
				elseif(in_array('K1_'.$refField, $field_list)) { $refField = 'K1_'.$refField; $is_dpt = true; continue; }
				elseif(in_array('K0_'.$refField, $field_list)) { $refField = 'K0_'.$refField; $is_dpt = true; continue; }
				elseif(in_array('K2_'.$refField, $field_list)) { $refField = 'K2_'.$refField; $is_dpt = true; continue; }
				elseif(in_array('K3_'.$refField, $field_list)) { $refField = 'K3_'.$refField; $is_dpt = true; continue; }
			}
			$aval[$Field] = rs::id2arr($qIdarr);
			$type[$Field] = "select";
			$addblank[$Field] = 0;
		}
		$ctrl++;
	}
	if($is_dpt){
		$dpt_select = self::select_js2('descriptors', $ordinamento, $field_list);
	}
	foreach($dpt_select as $Field => $v){
		foreach($v as $var => $val){
			
			if($var == 'aval') 			$aval[$Field] = $val;
			elseif($var == 'type') 		$type[$Field] = $val;
			elseif($var == 'addblank') 	$addblank[$Field] = $val;
			elseif($var == 'val') 		{$val[$Field] = $val; /*print $val.' '.$Field.BR;*/  }
			elseif($var == 'default') 	$default[$Field] = $val;
			elseif($var == 'disabled') 	{ $disabled[$Field] = $val; /* if($val) print $Field.BR;*/ }
			elseif($var == 'js') 		$js[$Field] = $val;
		}
	}
	
	if(array_key_exists($Field, $add)){ # COSA FA STA COSA???
		$New=$add[$Field];
		$name[]=$New;
		$lenght[$New]=$lenght[$Field];
		$dec[$New]=$dec[$Field];
		$default[$New]=$default[$Field];
		$type[$New]=$type[$Field];
		$not_null[$New]=$not_null[$Field];
		$key[$New]=$key[$Field];
		$comment[$New]=$comment[$Field];
	}
	
	$aFval = $dpt_select['aFval'];

	return array($name, $rec, $type, $lenght, $default, $not_null, $lable, $dec, $key, $aval, $addblank, $comment, $sql_type, $js, $disabled, $aFval);	
}


static function showfull($atabelle,$rec,$lable,$add, $skipExt, $ordinamento){
	$is_dpt = false;
	$dpt_select = array();
	$dpt_select['aFval'] = array();
	$name=array();
	$type=array();
	$disabled=array();
	$lenght=array();
	$default=array();
	$not_null=array();
	$dec=array();
	$key=array();
	$aval=array();
	$addblank=array();
	$comment=array();
	$sql_type=array();
	$is_justpri = 0;
	$field_list = array();
	
	foreach($atabelle as $tabella){
		$$tabella = rs::inMatrix( $q="SHOW FULL COLUMNS FROM $tabella");
		foreach($$tabella as $k => $v){ # ARRAY PER VERIFICA TESTO SELECT OPTION
			$field_list[] = $v['Field'];
		}
	}
	
	$ctrl=0;
	foreach($lable as $fld => $desc){
		$tabx = NULL;
		$xrow=-1;
		foreach($atabelle as $tabella){
			$irow=0;
			$is_add=0;
			foreach($$tabella as $row){
				if($row["Field"]==$fld){
					$tabx=$$tabella;
					$xrow=$irow;
					break;
				}
				$irow++;
			}
			if(!is_null($tabx))
			break;
		}
		if($tabx==NULL ){
			print $fld." not find in tables (-)";
			return;
		}
		$js = '';
		$row = $tabx[$xrow];
		$Field = $row['Field'];
		$name[] = $Field;
		$aval[$Field] = array();
		$addblank[$Field] = 0;
		$len = stringa::strbeetween($row['Type'],"(",")");
		$lenght[$Field] = stringa::leftfrom($len,",");
		$dec[$Field] = stringa::rightfrom($len,",");
		$lenght[$Field] = trim($lenght[$Field])==""? $len : $lenght[$Field];
		$comment[$Field]=$row["Comment"];
		$def = is_null($row['Default']) || trim($row['Default'])=="" || $row['Default']=="CURRENT_TIMESTAMP" ? NULL : $row['Default'];
		$default[$Field] = $def;
		if(array_key_exists($Field, $rec) && $def!="" && strlen($def)>0 && $rec[$Field]=="" && strlen($rec[$Field])==0){
			$rec[$Field]=$def;
		}

		$type[$Field] = strpos($row['Type'],"(")!==false ? trim(stringa::leftfrom($row['Type'],"(")) : $row['Type'];
		$sql_type[$Field]=$row['Type'];
		$type[$Field] = rs::type_parser($type[$Field],$lenght[$Field],$comment[$Field]);
		$not_null[$Field] = $row['Null'] == 'YES' ? 0 : 1;
		
		if(strpos($comment[$Field],"not_null")!==false){ $not_null[$Field] = 1; }

		$key[$Field]=$row["Key"];
		if(($key[$Field]=="MUL" && !in_array($Field,$skipExt) || strpos($Field, 'K0_ID') !== false || strpos($Field, 'K1_ID') !== false || strpos($Field, 'K2_ID') !== false)){
			$is_dpt = false;
			if(substr($Field,strlen($Field)-1,strlen($Field))=="2"){
				$reftable=stringa::rcut(stringa::lcut($Field,3),1)."S2";
			}
			else{ 
				$reftable = stringa::lcut($Field,3)."S";
			}
			$refField=stringa::lcut($Field,3);
			
			$where="";
			$reftable = strtolower($reftable);
			$qIdarr = "SELECT $Field, $refField from $reftable $where ORDER BY $refField ASC";
			
			if(strpos($Field, 'ID_SELF_USER') !== false){
				$refField = stringa::rightfromlast($refField, '_');
				$reftable = stringa::field2table($refField);
				$id_field = 'ID_'.$refField;
				$qIdarr = "SELECT ID_USER, USER FROM users WHERE IS_FRANCHISOR = '1' ORDER BY USER ASC";
			}
			elseif(strpos($Field, 'SELF') !== false){
				$refField = stringa::rightfromlast($refField, '_');
				$reftable = stringa::field2table($refField);
				$qIdarr = "SELECT $Field, $refField from $reftable $where ORDER BY $refField ASC";
			}
			elseif(strpos($Field, 'ID_GRUPPI') !== false){
				$refField = stringa::rightfromlast($refField, '_');
				$reftable = stringa::field2table($refField);
				$qIdarr = "SELECT ID_GRUPPI, TITLE from gruppis $where ORDER BY TITLE ASC";
			}
			if(!in_array($refField, $field_list)){
				if(in_array($refField.'_'.LANG_DEF, $field_list)){  # ESCAPE TESTO SELECT OPTION. SI POTRA' PREVEDERE DI INSERIRE IL CAMPO LIGUA
					$refField  = $refField.'_'.LANG_DEF;
					$qIdarr = "SELECT $Field, $refField from $reftable $where ORDER BY $refField ASC";
				}
				elseif(in_array('NOME_'.$refField, $field_list)){ $refField  = 'NOME_'.$refField; }
				elseif(in_array('K1_'.$refField, $field_list)) { $refField = 'K1_'.$refField; $is_dpt = true; continue; }
				elseif(in_array('K0_'.$refField, $field_list)) { $refField = 'K0_'.$refField; $is_dpt = true; continue; }
				elseif(in_array('K2_'.$refField, $field_list)) { $refField = 'K2_'.$refField; $is_dpt = true; continue; }
				elseif(in_array('K3_'.$refField, $field_list)) { $refField = 'K3_'.$refField; $is_dpt = true; continue; }
			}
			
			if(!empty($aval[$Field])){ print $Field; }
			
			$aval[$Field] = rs::id2arr($qIdarr);
			$type[$Field] = "select";
			$addblank[$Field] = 0;
		}
		$ctrl++;
	}

	if($is_dpt){
		$dpt_select = self::select_js('descriptors', $ordinamento, $field_list);
	}
	foreach($dpt_select as $Field => $v){
		foreach($v as $var => $value){
			//arr::stampa($v);	
			
			if($var == 'aval') 			$aval[$Field] = $value;
			elseif($var == 'type') 		$type[$Field] = $value;
			elseif($var == 'addblank') 	$addblank[$Field] = $value;
			elseif($var == 'val'){
				$val[$Field] = $value;
				if(array_key_exists($Field,$rec)){ 
					if(strpos($Field, 'K2_') !== false){
						# NON FACCIO NULLA
					}
					else{ # ALLINEO IL REC PER MENU AD ALBERO K0 E K1	
						$rec[$Field] = $value; 
					}
				}
			}
			elseif($var == 'default') 	{ $default[$Field] = $value; /*print $value.' '.$Field.BR;*/ }
			elseif($var == 'disabled') 	{ $disabled[$Field] = $value; /* if($value) print $Field.BR;*/ }
			elseif($var == 'js') 		{ $js[$Field] = $value; } # INSERISCE L'ONCHANGE NEL CAMPO
		}
	}
	
	if(array_key_exists($Field, $add)){ # AGGIUNGE I CAMPI EXTRA INDICATI IN FUNZIONE
		$New=$add[$Field];
		$name[]=$New;
		$lenght[$New]=$lenght[$Field];
		$dec[$New]=$dec[$Field];
		$default[$New]=$default[$Field];
		$type[$New]=$type[$Field];
		$not_null[$New]=$not_null[$Field];
		$key[$New]=$key[$Field];
		$comment[$New]=$comment[$Field];
	}
	
	$aFval = $dpt_select['aFval'];

	return array($name, $rec, $type, $lenght, $default, $not_null, $lable, $dec, $key, $aval, $addblank, $comment, $sql_type, $js, $disabled, $aFval);	
}

static function showfullpersonal($atabelle,$rec,$lable,$add, $skipExt, $aPersonalSelect, $ordinamento){ # NUOVO METODO CHE USA I VALORI DELL'OGGETTO ordinamento
	$is_dpt = false;
	$dpt_select = array();
	$dpt_select['aFval'] = array();
	$name=array();
	$type=array();
	$disabled=array();
	$lenght=array();
	$default=array();
	$not_null=array();
	$dec=array();
	$key=array();
	$aval=array();
	$addblank=array();
	$comment=array();
	$sql_type=array();
	$is_justpri = 0;
	$field_list = array();
	
	foreach($atabelle as $tabella){
		$$tabella = rs::inMatrix( $q="SHOW FULL COLUMNS FROM $tabella");
		
		// print_r($$tabella);
		foreach($$tabella as $k => $v){ # ARRAY PER VERIFICA TESTO SELECT OPTION
			$field_list[] = $v['Field'];
		}
	}
	
	$ctrl=0;
	foreach($lable as $fld => $desc){
		$tabx = NULL;
		$xrow=-1;
		foreach($atabelle as $tabella){
			$irow=0;
			$is_add=0;
			foreach($$tabella as $row){
				if($row["Field"]==$fld){
					$tabx=$$tabella;
					$xrow=$irow;
					break;
				}
				$irow++;
			}
			if(!is_null($tabx))
			break;
		}
		if($tabx==NULL ){
			print $fld." not find in tables (-)";
			return;
		}
		$js = '';
		$row = $tabx[$xrow];
		$Field = $row['Field'];
		$name[] = $Field;
		$aval[$Field] = array();
		$addblank[$Field] = 0;
		$len = stringa::strbeetween($row['Type'],"(",")");
		$lenght[$Field] = stringa::leftfrom($len,",");
		$dec[$Field] = stringa::rightfrom($len,",");
		$lenght[$Field] = trim($lenght[$Field])==""? $len : $lenght[$Field];
		$comment[$Field]=$row["Comment"];
		$def = is_null($row['Default']) || trim($row['Default'])=="" || $row['Default']=="CURRENT_TIMESTAMP" ? NULL : $row['Default'];
		$default[$Field] = $def;
		if(array_key_exists($Field, $rec) && $def!="" && strlen($def)>0 && $rec[$Field]=="" && strlen($rec[$Field])==0){
			$rec[$Field]=$def;
		}

		$type[$Field] = strpos($row['Type'],"(")!==false ? trim(stringa::leftfrom($row['Type'],"(")) : $row['Type'];
		$sql_type[$Field]=$row['Type'];
		$type[$Field] = rs::type_parser($type[$Field],$lenght[$Field],$comment[$Field]);
		$not_null[$Field] = $row['Null'] == 'YES' ? 0 : 1;
		
		if(strpos($comment[$Field],"not_null")!==false){ $not_null[$Field] = 1; }

		$key[$Field]=$row["Key"];
		if(($key[$Field]=="MUL" && !in_array($Field,$skipExt) || strpos($Field, 'K0_ID') !== false || strpos($Field, 'K1_ID') !== false || strpos($Field, 'K2_ID') !== false)){
			
			//print $Field.BR;
			
			if(substr($Field,strlen($Field)-1,strlen($Field))=="2"){
				$reftable=stringa::rcut(stringa::lcut($Field,3),1)."S2";
			}
			else{ 
				$reftable = stringa::lcut($Field,3)."S";
			}
			$refField=stringa::lcut($Field,3);
			
			$where="";
			$reftable = strtolower($reftable);
			$qIdarr = "SELECT $Field, $refField from $reftable $where ORDER BY $refField ASC";

			if(strpos($Field, 'ID_SELF_USER') !== false){
				$refField = stringa::rightfromlast($refField, '_');
				$reftable = stringa::field2table($refField);
				$id_field = 'ID_'.$refField;
				$qIdarr = "SELECT ID_USER, USER FROM users WHERE IS_FRANCHISOR = '1' ORDER BY USER ASC";
			}
			elseif(strpos($Field, 'SELF') !== false){
				$refField = stringa::rightfromlast($refField, '_');
				$reftable = stringa::field2table($refField);
				$qIdarr = "SELECT $Field, $refField from $reftable $where ORDER BY $refField ASC";
			}
			elseif(strpos($Field, 'ID_GRUPPI') !== false){
				$refField = stringa::rightfromlast($refField, '_');
				$reftable = stringa::field2table($refField);
				$qIdarr = "SELECT ID_GRUPPI, TITLE from gruppis $where ORDER BY TITLE ASC";
			}
			
			if(!in_array($refField, $field_list)){
				// echo $refField.BR;
				if(in_array($refField.'_'.LANG_DEF, $field_list)){  # ESCAPE TESTO SELECT OPTION. SI POTRA' PREVEDERE DI INSERIRE IL CAMPO LIGUA
					$refField  = $refField.'_'.LANG_DEF;
					$qIdarr = "SELECT $Field, $refField from $reftable $where ORDER BY $refField ASC";
				}
				elseif(in_array('NOME_'.$refField, $field_list)){ $refField  = 'NOME_'.$refField; }
				elseif(in_array('K1_'.$refField, $field_list)) { $refField = 'K1_'.$refField; $is_dpt = true; continue; }
				elseif(in_array('K0_'.$refField, $field_list)) { $refField = 'K0_'.$refField; $is_dpt = true; continue; }
				elseif(in_array('K2_'.$refField, $field_list)) { $refField = 'K2_'.$refField; $is_dpt = true; continue;  }
				elseif(in_array('K3_'.$refField, $field_list)) { $refField = 'K3_'.$refField; $is_dpt = true; continue; }
			}
			
			if(array_key_exists($Field, $aPersonalSelect)){ # USA LA QUERY PERSONALIZZATA PER LA CREAZIONE DEL MENU A TENDINA
				$qIdarr = $aPersonalSelect[$Field];
			}
			
			if(!empty($aval[$Field])){ print $Field; }
			$aval[$Field] = rs::id2arr($qIdarr);
			$type[$Field] = "select";
			$addblank[$Field] = 0;
		}
		$ctrl++;
	}

	if($is_dpt){
		$dpt_select = self::select_js('descriptors', $ordinamento, $field_list);
	}
	foreach($dpt_select as $Field => $v){
		foreach($v as $var => $value){
			if($var == 'aval') 			$aval[$Field] = $value;
			elseif($var == 'type') 		$type[$Field] = $value;
			elseif($var == 'addblank') 	$addblank[$Field] = $value;
			elseif($var == 'val'){
				$val[$Field] = $value;
				if(array_key_exists($Field,$rec)){ 
					if(strpos($Field, 'K2_') !== false){
						# NON FACCIO NULLA
					}
					else{ # ALLINEO IL REC PER MENU AD ALBERO K0 E K1	
						$rec[$Field] = $value; 
					}
				}
			}
			elseif($var == 'default') 	{ $default[$Field] = $value; /*print $value.' '.$Field.BR;*/ }
			elseif($var == 'disabled') 	{ $disabled[$Field] = $value; /* if($value) print $Field.BR;*/ }
			elseif($var == 'js') 		{ $js[$Field] = $value; } # INSERISCE L'ONCHANGE NEL CAMPO
		}
	}
	
	if(array_key_exists($Field, $add)){ # AGGIUNGE I CAMPI EXTRA INDICATI IN FUNZIONE
		$New=$add[$Field];
		$name[]=$New;
		$lenght[$New]=$lenght[$Field];
		$dec[$New]=$dec[$Field];
		$default[$New]=$default[$Field];
		$type[$New]=$type[$Field];
		$not_null[$New]=$not_null[$Field];
		$key[$New]=$key[$Field];
		$comment[$New]=$comment[$Field];
	}
	
	$aFval = $dpt_select['aFval'];

	return array($name, $rec, $type, $lenght, $default, $not_null, $lable, $dec, $key, $aval, $addblank, $comment, $sql_type, $js, $disabled, $aFval);	
}	

public static function select_js($table = 'descriptors', $ordinamento, $field_list){ # RESTITUISCE UN ARRAY DI CAMPI PER INTERFACCIARSI COL METODO rs::showfull
	$aRet = array(); # $aRet['K0_ID_STATI']['aval'] = '5'
	$aFval = array();
	
	$q = "SELECT * FROM ".$table."_types";
	$aType = rs::inMatrix($q);
	$aIdType = arr::semplifica($aType, 'DESCRIPTORS_TABLE');
	$aIdType2 = arr::semplifica($aType, 'ID_DESCRIPTORS_TYPE');
	$aIdType3 = arr::semplifica($aType, 'ID_DESCRIPTORS_TYPE_SELF');
	
	if(array_key_exists('id', $_REQUEST) && !empty($_REQUEST['id'])){ # DATI RECORD
		$id_record = $_REQUEST['id'];
		$id_name = stringa::tbl2id($ordinamento -> tabella);

		$qMain = "SELECT * FROM ".$ordinamento -> tabella." WHERE $id_name='".$id_record."'";
		$rMain = rs::rec2arr($qMain);
	}
	
	foreach($ordinamento -> js as $t => $rec_id){
		$flg_set_id = false;
		if(empty($rec_id)) $flg_set_id = true;
		
		# GESTIONE RAMI
		$key = $aIdType[$t]['ID_DESCRIPTORS_TYPE']; # RAMO PIU BASSO P.ES ST (statis)
		$livello = 1;
		while(array_key_exists($key, $aIdType3) && $livello < 20){
			$id_f = stringa::tbl2id($aIdType3[$key]['DESCRIPTORS_TABLE']);
			$k0 = 'K0_'.$id_f;
			$k1 = 'K1_'.$id_f;
			$k2 = 'K2_'.$id_f;
			$k3 = 'K3_'.$id_f;
			$nome_campo = '';
			if(in_array($k0, $field_list)){ $nome_campo = $k0; }
			elseif(in_array($k1, $field_list)){ $nome_campo = $k1; }
			elseif(in_array($k2, $field_list)){ $nome_campo = $k2; }
			elseif(in_array($k3, $field_list)){ $nome_campo = $k3; }
			else{ print 'Nessun campo K0 o K1 valorizzato per '.$id_f; }
			
			# INIZIALIZZAZIONE CAMPI
			$aRet[$nome_campo]['type'] = 'select';
			$aRet[$nome_campo]['disabled'] = 'disabled';
			$aRet[$nome_campo]['addblank'] = 1;
			
			if((array_key_exists('id', $_REQUEST) && !empty($_REQUEST['id']))){
				# SETTO $rec_id AL LIVELLO PI� ALTO PER AVERE AUTOMATICAMENTE I DEFAULT PER GLI ALTRI CAMPI
				
				
				if(!empty($rMain[$nome_campo]) && $flg_set_id){
					$rec_id = $rMain[$nome_campo];
				}
			}
			if(array_key_exists($key, $aIdType3)){
				$key = $aIdType3[$key]['ID_DESCRIPTORS_TYPE'];
			}
			else break;
			$livello ++;
		}
		$cnt = 0; $default = '0';
		$id_t = stringa::tbl2id($table);

		# INIZIALIZZO I CAMPI (UPDATE OPPURE INSERT)
		if(empty($rec_id)){
			$where = "WHERE descriptors.ID_DESCRIPTOR_SELF IS NULL AND descriptors_types.DESCRIPTORS_TABLE = '".$t."' LIMIT 0,1";
		}
		else{
			$where = "WHERE descriptors.ID_DESCRIPTOR_SELF = '$rec_id' LIMIT 0,1";
		}
		do{ 
			if($cnt > 0){ $where = "WHERE descriptors.ID_DESCRIPTOR = '$rec_id'"; }
			$q = "SELECT
			descriptors.ID_DESCRIPTOR_SELF,
			descriptors.ID_DESCRIPTOR,
			descriptors_types.DESCRIPTORS_TABLE,
			descriptors_types.DESCRIPTORS_TYPE,
			descriptors.IS_DESCRIPTOR,
			descriptors.ID_DESCRIPTORS_TYPE,
			descriptors.SIGLA_DPT,
			descriptors.IS_DESCRIPTOR_DEFAULT,
			descriptors.DESCRIPTOR_".LANG_DEF."
			FROM
			descriptors
			Left Join descriptors_types ON descriptors.ID_DESCRIPTORS_TYPE = descriptors_types.ID_DESCRIPTORS_TYPE
			$where";

			$rec = rs::rec2arr($q);
			
			if(empty($rec['ID_DESCRIPTOR'])){ # RINNOVO IL $rec E LO RENDO FUNZIONALE
				$rec = array();
				$q = "SELECT
				descriptors.ID_DESCRIPTOR_SELF,
				descriptors.ID_DESCRIPTOR,
				descriptors_types.DESCRIPTORS_TABLE,
				descriptors_types.DESCRIPTORS_TYPE,
				descriptors.IS_DESCRIPTOR,
				descriptors.ID_DESCRIPTORS_TYPE,
				descriptors.SIGLA_DPT,
				descriptors.IS_DESCRIPTOR_DEFAULT,
				descriptors.DESCRIPTOR_".LANG_DEF."
				FROM
				descriptors
				Left Join descriptors_types ON descriptors.ID_DESCRIPTORS_TYPE = descriptors_types.ID_DESCRIPTORS_TYPE
				WHERE descriptors.ID_DESCRIPTOR = '$rec_id' LIMIT 0,1";
				$rec = rs::rec2arr($q);
			}
			$id_f = stringa::tbl2id($rec['DESCRIPTORS_TABLE']);
			if($cnt == 0){
				$ctrl_loop = 0;
				$ramo = $rec['DESCRIPTORS_TABLE'];
				# IL WHILE DEVE DIVENTARE: FINCH� CI SONO RECORD DA VISUALIZZARE (STATO OLTRE ALL'ID_REC DISABLED)
				while(!empty($aIdType[$ramo]['ID_DESCRIPTORS_TYPE_SELF']) && $ctrl_loop < 20){ # ok
					$ramo = $aIdType2[$aIdType[$ramo]['ID_DESCRIPTORS_TYPE_SELF']]['DESCRIPTORS_TABLE'];
					$ctrl_loop++;
				}
			$hidden_name = 'js'.$ramo;
			}		
			
			# RICAVO IL NOME CAMPO DB
			$not_k2 = true;
			$k0 = 'K0_'.$id_f;
			$k1 = 'K1_'.$id_f;
			$k2 = 'K2_'.$id_f;
			$k3 = 'K3_'.$id_f;

			if(in_array($k0, $field_list)){ $nome_campo = $k0; }
			elseif(in_array($k1, $field_list)){ $nome_campo = $k1; }
			elseif(in_array($k2, $field_list)){ $nome_campo = $k2;  $not_k2 = false; }
			elseif(in_array($k3, $field_list)){ $nome_campo = $k3;   }
			else{ print 'Nessun campo '.$nome_campo.' K0 o K1 0 K2 valorizzato'; }
			# QUERY PER I VALORI NEL SELECT OPTION
			$self_where = empty($rec['ID_DESCRIPTOR_SELF']) ? " AND ID_DESCRIPTOR_SELF IS NULL" : " AND ID_DESCRIPTOR_SELF = '".$rec['ID_DESCRIPTOR_SELF']."'";
			$qId2arr = "SELECT ID_DESCRIPTOR, DESCRIPTOR_".LANG_DEF." FROM descriptors WHERE ID_DESCRIPTORS_TYPE = '".$rec['ID_DESCRIPTORS_TYPE']."'$self_where ORDER BY RANK ASC, DESCRIPTOR_".LANG_DEF." ASC";
			
			# CONTROLLO IL RAMO SUCCESSIVO
			
			// print $nome_campo.' '.$rec_id.BR;
			$aRet[$nome_campo]['val'] = $rec_id;
			$aRet[$nome_campo]['default'] = $rec_id;
			$aRet[$nome_campo]['aval'] = rs::id2arr($qId2arr);
			$aRet[$nome_campo]['type'] = 'select';
			$aRet[$nome_campo]['addblank'] = 1;
			$aRet[$nome_campo]['disabled'] = false;
			//if($not_k2) $aRet[$nome_campo]['js'] = 'onchange="if(this.value == \'\'){return false;}else{ this.form.'.$hidden_name.'.value = this.value; this.form.submit() }"';
			if($not_k2) $aRet[$nome_campo]['js'] = 'onchange="if(this.value == \'\'){return false;}else{ this.form.'.$hidden_name.'.value = this.value; }" ';
			if($k2 == false) $aRet[$nome_campo]['js'] = '';
			
			$aFval[$nome_campo] = $rec_id; # rec_id non viene azzerato, quindi duplicati in array

			$rec_id = $rec['ID_DESCRIPTOR_SELF'];
			$cnt++; # Protezione loop
		}
		while($rec['ID_DESCRIPTOR_SELF'] != NULL || $cnt > 20);
	}
	$aRet['aFval'] = $aFval;
	return $aRet;
}

public static function select_js2($table = 'descriptors', $ordinamento, $field_list){ # RESTITUISCE UN ARRAY DI CAMPI PER INTERFACCIARSI COL METODO rs::showfull
	$aRet = array(); # $aRet['K0_ID_STATI']['aval'] = '5'
	$aFval = array();
	
	$q = "SELECT * FROM ".$table."_types";
	$aType = rs::inMatrix($q);
	$aIdType = arr::semplifica($aType, 'DESCRIPTORS_TABLE');
	$aIdType2 = arr::semplifica($aType, 'ID_DESCRIPTORS_TYPE');
	$aIdType3 = arr::semplifica($aType, 'ID_DESCRIPTORS_TYPE_SELF');
	
	if(array_key_exists('id', $_REQUEST) && !empty($_REQUEST['id'])){ # DATI RECORD
		$id_record = $_REQUEST['id'];
		$id_name = stringa::tbl2id($ordinamento -> tabella);

		$qMain = "SELECT * FROM ".$ordinamento -> tabella." WHERE $id_name='".$id_record."'";
		$rMain = rs::rec2arr($qMain);
	}
	
	foreach($ordinamento -> js as $t => $rec_id){
		$flg_set_id = false;
		if(empty($rec_id)) $flg_set_id = true;
		
		# GESTIONE RAMI
		$key = $aIdType[$t]['ID_DESCRIPTORS_TYPE'];
		$livello = 1;
		while(array_key_exists($key, $aIdType3) && $livello < 20){
			$id_f = stringa::tbl2id($aIdType3[$key]['DESCRIPTORS_TABLE']);
			$k0 = 'K0_'.$id_f;
			$k1 = 'K1_'.$id_f;
			$k2 = 'K2_'.$id_f;
			$k3 = 'K3_'.$id_f;
			$nome_campo = '';
			if(in_array($k0, $field_list)){ $nome_campo = $k0; }
			elseif(in_array($k1, $field_list)){ $nome_campo = $k1; }
			elseif(in_array($k2, $field_list)){ $nome_campo = $k2; }
			elseif(in_array($k3, $field_list)){ $nome_campo = $k3; }
			else{ print 'Nessun campo K0 o K1 valorizzato per '.$id_f; }
			
			# INIZIALIZZAZIONE CAMPI
			$aRet[$nome_campo]['type'] = 'select';
			$aRet[$nome_campo]['disabled'] = 'disabled';
			$aRet[$nome_campo]['addblank'] = 1;
			
			if(array_key_exists('id', $_REQUEST) && !empty($_REQUEST['id'])){
				# SETTO $rec_id AL LIVELLO PI� ALTO PER AVERE AUTOMATICAMENTE I DEFAULT PER GLI ALTRI CAMPI
				if(!empty($rMain[$nome_campo]) && $flg_set_id){
					$rec_id = $rMain[$nome_campo];
				}
			}
			if(array_key_exists($key, $aIdType3)) $key = $aIdType3[$key]['ID_DESCRIPTORS_TYPE'];
			else break;
			$livello ++;
		}
		$cnt = 0; $default = '0';
		$id_t = stringa::tbl2id($table);

		# INIZIALIZZO I CAMPI (UPDATE OPPURE INSERT)
		if(empty($rec_id)){
			$where = "WHERE descriptors.ID_DESCRIPTOR_SELF IS NULL AND descriptors_types.DESCRIPTORS_TABLE = '".$t."' LIMIT 0,1";
		}
		else{
			$where = "WHERE descriptors.ID_DESCRIPTOR_SELF = '$rec_id' LIMIT 0,1";
		}
		do{ 
			if($cnt > 0){ $where = "WHERE descriptors.ID_DESCRIPTOR = '$rec_id'"; }
			$q = "SELECT
			descriptors.ID_DESCRIPTOR_SELF,
			descriptors.ID_DESCRIPTOR,
			descriptors_types.DESCRIPTORS_TABLE,
			descriptors_types.DESCRIPTORS_TYPE,
			descriptors.IS_DESCRIPTOR,
			descriptors.ID_DESCRIPTORS_TYPE,
			descriptors.SIGLA_DPT,
			descriptors.IS_DESCRIPTOR_DEFAULT,
			descriptors.DESCRIPTOR_".LANG_DEF."
			FROM
			descriptors
			Left Join descriptors_types ON descriptors.ID_DESCRIPTORS_TYPE = descriptors_types.ID_DESCRIPTORS_TYPE
			$where";

			$rec = rs::rec2arr($q);
			
			if(empty($rec['ID_DESCRIPTOR'])){ # RINNOVO IL $rec E LO RENDO FUNZIONALE
				$rec = array();
				$q = "SELECT
				descriptors.ID_DESCRIPTOR_SELF,
				descriptors.ID_DESCRIPTOR,
				descriptors_types.DESCRIPTORS_TABLE,
				descriptors_types.DESCRIPTORS_TYPE,
				descriptors.IS_DESCRIPTOR,
				descriptors.ID_DESCRIPTORS_TYPE,
				descriptors.SIGLA_DPT,
				descriptors.IS_DESCRIPTOR_DEFAULT,
				descriptors.DESCRIPTOR_".LANG_DEF."
				FROM
				descriptors
				Left Join descriptors_types ON descriptors.ID_DESCRIPTORS_TYPE = descriptors_types.ID_DESCRIPTORS_TYPE
				WHERE descriptors.ID_DESCRIPTOR = '$rec_id' LIMIT 0,1";
				$rec = rs::rec2arr($q);
			}
			$id_f = stringa::tbl2id($rec['DESCRIPTORS_TABLE']);
			if($cnt == 0){
				$ctrl_loop = 0;
				$ramo = $rec['DESCRIPTORS_TABLE'];
				# IL WHILE DEVE DIVENTARE: FINCH� CI SONO RECORD DA VISUALIZZARE (STATO OLTRE ALL'ID_REC DISABLED)
				while(!empty($aIdType[$ramo]['ID_DESCRIPTORS_TYPE_SELF']) && $ctrl_loop < 20){ # ok
					$ramo = $aIdType2[$aIdType[$ramo]['ID_DESCRIPTORS_TYPE_SELF']]['DESCRIPTORS_TABLE'];
					$ctrl_loop++;
				}
			$hidden_name = 'js'.$ramo;
			}		
			
			# RICAVO IL NOME CAMPO DB
			$not_k2 = true;
			$k0 = 'K0_'.$id_f;
			$k1 = 'K1_'.$id_f;
			$k2 = 'K2_'.$id_f;
			$k3 = 'K3_'.$id_f;
			$nome_campo = '';
			if(in_array($k0, $field_list)){ $nome_campo = $k0; }
			elseif(in_array($k1, $field_list)){ $nome_campo = $k1; }
			elseif(in_array($k2, $field_list)){ $nome_campo = $k2;  $not_k2 = false; }
			elseif(in_array($k3, $field_list)){ $nome_campo = $k3; }
			else{ print 'Nessun campo K0 o K1 0 K2 valorizzato'; }
			# QUERY PER I VALORI NEL SELECT OPTION
			$self_where = empty($rec['ID_DESCRIPTOR_SELF']) ? " AND ID_DESCRIPTOR_SELF IS NULL" : " AND ID_DESCRIPTOR_SELF = '".$rec['ID_DESCRIPTOR_SELF']."'";
			$qId2arr = "SELECT ID_DESCRIPTOR, DESCRIPTOR_".LANG_DEF." FROM descriptors WHERE ID_DESCRIPTORS_TYPE = '".$rec['ID_DESCRIPTORS_TYPE']."'$self_where ORDER BY RANK ASC, DESCRIPTOR_".LANG_DEF." ASC";
			# CONTROLLO IL RAMO SUCCESSIVO
			
			$aRet[$nome_campo]['val'] = $rec_id;
			$aRet[$nome_campo]['default'] = $rec_id;
			$aRet[$nome_campo]['aval'] = rs::id2arr($qId2arr);
			$aRet[$nome_campo]['type'] = 'select';
			$aRet[$nome_campo]['addblank'] = 1;
			$aRet[$nome_campo]['disabled'] = false;
			if($not_k2) $aRet[$nome_campo]['js'] = 'onchange="if(this.value == \'\'){return false;}else{ this.form.'.$hidden_name.'.value = this.value; this.form.submit() }"';
			//if($not_k2) $aRet[$nome_campo]['js'] = 'onchange="if(this.value == \'\'){return false;}else{ this.form.'.$hidden_name.'.value = this.value; }"';
			if($k2 == false) $aRet[$nome_campo]['js'] = '';
			
			$aFval[$nome_campo] = $rec_id; # rec_id non viene azzerato, quindi duplicati in array
			$rec_id = $rec['ID_DESCRIPTOR_SELF'];
			$cnt++; # Protezione loop
		}
		while($rec['ID_DESCRIPTOR_SELF'] != NULL || $cnt > 20);
	}
	$aRet['aFval'] = $aFval;
	return $aRet;
}


static function type_parser($type,$lenght,$comment){
	$mytype="text";
	if(strpos($type,"text")!==false) 					$mytype="textarea";
	else if(strpos($type,"decimal")!==false) 			$mytype="iidde";
	else if($type=="date") 								$mytype="ddmm4y";
	else if($type=="datetime" || $type=="timestamp")	$mytype="ddmm4yhhiiss";
	else if($type=="int" && $lenght==1 )				$mytype="checkbox";
	else if(strpos($comment,"password")!==false ) 		$mytype="password";
	return $mytype;
}

static function &lenght($table=""){
	$rs2arr=array();
	if($rs=@mysql_query($q="SHOW FULL COLUMNS FROM $table")){
		while($row=mysql_fetch_assoc($rs)){
			$rs2arr[$row['Field']]=(string)stringa::strbeetween($row['Type'],"(",")");
		}
		@mysql_free_result($rs);
	}
	else	
	err::eko("Invalid sql for lenght");
	return $rs2arr;
}
			
static function &_default($table=""){
	$rs2arr=array();
	if($rs=@mysql_query("SHOW FULL COLUMNS FROM $table")){
		while($row=mysql_fetch_assoc($rs)){
			$rs2arr[$row['Field']]=(string)
			is_null($row['Default']) || trim($row['Default'])=="" ? NULL : $row['Default'];
		}
		@mysql_free_result($rs);
	}
	else	
	err::eko("Invalid sql for _default");
	return $rs2arr;
}
		
		
static function default_load($def,$rs2arr){
	foreach ($def as $Field=>$val){
		if((is_string($rs2arr[$Field]) && trim($rs2arr[$Field])=="") || is_null($rs2arr[$Field]) || (is_bool($rs2arr[$Field]) && $rs2arr[$Field] ==false)){
			if(!is_null($val) && trim($val)!=""){
				$rs2arr[$Field]=(string)$val;
			}
		}
	}
	return $rs2arr;	
}

static function load_default($table="",$rs2arr=array()){
	if($rs=@mysql_query("SHOW FULL COLUMNS FROM $table")){
		while($row=mysql_fetch_assoc($rs)){
			$Field=$row['Field'];
			if((is_string($rs2arr[$Field]) && trim($rs2arr[$Field]) == "") || is_null($rs2arr[$Field])  || (is_bool($rs2arr[$Field])  && $rs2arr[$Field] ==false)){
				if(!is_null($row['Default']) && trim($row['Default']) != "" ){
					$rs2arr[$Field]=(string)$row['Default'];
				}
			}
		}
		@mysql_free_result($rs);
	}
	else{
		err::eko("Invalid sql for load_default");
	}
	return $rs2arr;
}

static function &_null($table=""){ # RESTITUISCE array['nome campo'] = 1 oppure 0 
	$rs2arr=array();
	if($rs=@mysql_query("SHOW FULL COLUMNS FROM $table")){
		while($row=mysql_fetch_assoc($rs)){
			$rs2arr[$row['Field']]=$row['Null']=='YES' ? 1 : 0;
		}
		@mysql_free_result($rs);
	}
	else{	
		err::eko("Invalid sql for _null");
	}
	return $rs2arr;	
}

# VECCHI METODI
static function get_icursor($qTotRec,$offset){
	$rs=mysql_query($qTotRec);
	if($rs==true){
		$totrecord=mysql_num_rows($rs);
		mysql_free_result($rs);
	}
	else{
		$totrecord=0;
	}
	$totcursor=ceil($totrecord/$offset);
	$totcursor=$totcursor==0 ? 1 : $totcursor;
	$icursor= array_key_exists('icursor',$_REQUEST) ? $_REQUEST['icursor'] : 0 ;
	$icursor= $icursor>$totcursor ? $totcursor : $icursor;
	if(array_key_exists('tf',$_REQUEST)) $n=0-$icursor;
	else if(array_key_exists('pt',$_REQUEST)) $n=-10;
	else if(array_key_exists('po',$_REQUEST)) $n=-1;
	else if(array_key_exists('no',$_REQUEST)) $n=1;
	else if(array_key_exists('nt',$_REQUEST)) $n=10;
	else if(array_key_exists('tl',$_REQUEST)) $n=($totcursor-1)-$icursor;
	else $n=0;
	$icursor=$icursor+$n;
	$icursor= $icursor<0 ? 0 : $icursor;
	$icursor= $icursor>$totcursor-1 ? $totcursor-1 : $icursor;
	return $icursor;
}

static function html_cursor($qTotRec,$offset,$argf=array()){
	$rs=mysql_query($qTotRec);
	if($rs==true){
		$totrecord=mysql_num_rows($rs);
		mysql_free_result($rs);
	}
	else{
		$totrecord=0;
	}
	$totcursor=ceil($totrecord/$offset);
	$totcursor=$totcursor==0 ? 1 : $totcursor;
	$icursor= array_key_exists('icursor',$_REQUEST) ? $_REQUEST['icursor'] : 0;
	$icursor= $icursor>$totcursor ? $totcursor : $icursor;
	if(array_key_exists('tf',$_REQUEST)) $n=0-$icursor;
	else if(array_key_exists('pt',$_REQUEST)) $n=-10;
	else if(array_key_exists('po',$_REQUEST)) $n=-1;
	else if(array_key_exists('no',$_REQUEST)) $n=1;
	else if(array_key_exists('nt',$_REQUEST)) $n=10;
	else if(array_key_exists('tl',$_REQUEST)) $n=($totcursor-1)-$icursor;
	else $n=0;
	$icursor=$icursor+$n;
	$icursor= $icursor<0 ? 0 : $icursor;
	$icursor= $icursor>$totcursor-1 ? $totcursor-1 : $icursor;
	
	$html_cursor="";
	$tf_dis="<span>prima</span>";
	$pt_dis="<span>&lt;&lt;</span>";
	$po_dis="<span>&lt;</span>";
	$no_dis="<span>&gt;</span>";
	$nt_dis="<span>&gt;&gt;</span>";
	$tl_dis="<span>ultima</span>";
	
	$argf['icursor']=$icursor;
	
	$tf=ahref($href=$_SERVER['PHP_SELF'],$text="prima",		$arg=$argf, $opz=array("tf"=>"a"),$target="",$title="prima pagina");
	$pt=ahref($href=$_SERVER['PHP_SELF'],$text="&lt;&lt;",	$arg=$argf, $opz=array("pt"=>"n"),$target="",$title="pagina precedente");
	$po=ahref($href=$_SERVER['PHP_SELF'],$text="&lt;",		$arg=$argf, $opz=array("po"=>"a"),$target="",$title="indietro di 10 pagine");
	$no=ahref($href=$_SERVER['PHP_SELF'],$text="&gt;",		$arg=$argf, $opz=array("no"=>"w"),$target="",$title="pagina successiva");
	$nt=ahref($href=$_SERVER['PHP_SELF'],$text="&gt;&gt;",	$arg=$argf, $opz=array("nt"=>"i"),$target="",$title="avanti di 10 pagine");
	$tl=ahref($href=$_SERVER['PHP_SELF'],$text="ultima",	$arg=$argf, $opz=array("tl"=>"m"),$target="",$title="ultima pagina");
	
	$html_cursor.='<div id="nav_page"><ul>';
	$html_cursor.='<li>'.($icursor>0 ? $tf : $tf_dis)."</li>\n";
	$html_cursor.='<li>'.($icursor-10>=0 ? $pt : $pt_dis)."</li>\n";
	$html_cursor.='<li>'.($icursor>0 ? $po : $po_dis)."</li>\n";
	$html_cursor.='<li class="nav_page_num">'.($totrecord>0 ? 1+$icursor.' di '.$totcursor : "---" )."</li>\n";
	$html_cursor.='<li>'.($icursor+1<$totcursor ? $no : $no_dis)."</li>\n";
	$html_cursor.='<li>'.($icursor+10<$totcursor ? $nt : $nt_dis)."</li>\n";
	$html_cursor.='<li>'.($icursor+1<$totcursor ? $tl : $tl_dis)."</li>\n";
	$html_cursor.='</ul></div>';
	return $html_cursor;
}	

public static function getfield($table, $fld, $id){
	$idn = stringa::tbl2id($table);
	$q = "SELECT $fld FROM $table WHERE $idn = '$id' LIMIT 0,1";
	$r = rs::rec2arr($q);
	return $r[$fld];
}

public static function get_ext($f, $v){
	$table = stringa::id2table($f);
	$fld = stringa::id2fld($f);
	$q = "SELECT $fld, $f FROM $table WHERE $f = '$v'";
	$r = rs::rec2arr($q);
	return $r[$fld];
}

public static function is_table($table_name, $dbname){ # CONTROLLA SE ESISTE UNA TABELLA NEL DB
	$ret = false;
	$db = empty($dbname) ? DBNAME : $dbname;
	$r = rs::inMatrix("SHOW TABLES FROM ".$db);
	foreach($r as $k){
		foreach($k as $table){
			if($table == $table_name) $ret = true;
		}
	}
	return $ret;
}
}
?>