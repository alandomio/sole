<?php
# V.0.1.8
// Funzione per sql
function prepare4sql($string) {
	return prepare($string);
}
function prepare($string) {
	$string = isset($string) ? $string : '';
	if(get_magic_quotes_gpc()==1 || ini_get('magic_quotes_sybase')==1) {
		$string = stripslashes($string);
		}
	$string = mysql_real_escape_string($string);
	return $string;
}

// per stampa e db
function mysql_strip($sString) {
	if(ini_get('magic_quotes_sybase')+get_magic_quotes_gpc()) $sString=stripslashes($sString);
    return $sString;
   } 
function dbOpen(){
    global $dns;
	global $sHost;
	global $sUser;
	global $sPass;
	if($myConn = mysql_pconnect($sHost,$sUser,$sPass));
	   else{
	   die(mysql_error());
	   } 
	mysql_select_db($dns) || die(mysql_error());
	return $myConn;
}

function dbClose($myConn)
   {
   if(mysql_close($myConn))
      {
	  }
   else
      {
	  die (mysql_error());
	  }
   }
 function dbExec($conn,$sQuery)   
   {
	global $flgEchoQ;
	if ($flgEchoQ==true)
	   {
	   echo "<font color=#FF0000>query: $sQuery ;</font>"; 
	   }
   if($rsMysql=mysql_query($sQuery,$conn))
      {
      }
   else
      {
      die (mysql_error());
	  }  

     if ($flgEchoQ==true)
	   {
	   echo "<font color=#FF0000>rs:$rsMysql<br> </font>"; 
	   }  
   return $rsMysql;
   }
function dbFree($rs)
   {
    global $flgEchoFree;
     if ($flgEchoFree==true)
	   {
	   echo "<font color=#FF0000>free_result: $rs<br> </font>"; 
	   }
	// mysql_free_result($rs); 
	if($rs==true)
	   {
		if(mysql_free_result($rs))
		  {
		  }
	   else
		  {
		  die (mysql_error());
		  }
	   }  
   }	 
function dbFetchRow($rs)
	{
	return mysql_fetch_row($rs);
	}
function dbResult($rs,$iRow=0,$sField,$ifNullReturn=NULL)
	{  
	global $flgEchoRs;
	if ($flgEchoRs==true)
	   {
	   echo "<font color=#FF0000>result:  $rs $iRow $sField</font><br />"; 
	   }
	   //echo "$rs $row $sField $iRow<br> ";
	   if(is_null($iRow)) $iRow=0;
	    $result= mysql_result($rs,$iRow,$sField);
		if(!is_null($ifNullReturn))
		   {
			if(!isset($result) || $result=="") 
			   {
				$result=$ifNullReturn;
			   }
			else
			   {
				$result;
			   }
			}   
	return $result;	
	}
function getSqlConditionFromArray($sField,$aValue,$sOper="AND")
   {
   $sSql="";
   if(isset($aValue))
      {
	  $sSql=" (";
	  for($i=0;$i<count($aValue);$i++)
	     {
		 $sValue=$aValue[$i];
		 if($i==(count($aValue)-1))
		    {
			$sSql.=$sField."="."'".$sValue."'".")";
			}	
		else
		    {
			$sSql.=$sField."="."'".$sValue."'"." ".$sOper." ";
			}		
		 }
	  }
   return $sSql;  
   }  
   
function getArrayFromSearch($sString,$sDelim,$iLenMin)
	{
	$aClearString= array();
    $sClearString="";
	for($i=0;$i<strlen($sString);$i++)
			{
			if(strpos("QWERTYUIOPASDFGHJKLZXCVBNMqwertyuiopasdfghjklzxcvbnm+-_'.@ 0123456789",$sString[$i])!==false)
			   {
			   $sClearString=$sClearString.$sString[$i]; 
			   }
			}
	$aString=explode($sDelim,$sClearString);
	$y=0;
	for($i=0;$i<count($aString);$i++)
	   {
	   $sWord=$aString[$i];
	   if(strlen($sWord)>=$iLenMin)
	      {
	      $aClearString[$y]=trim($sWord);
	      $y++;
		  }
	   }
	return $aClearString;   			
   } 

function prepareMultiLikefromStringSearch($aField,$aSearch,$sOperInt,$sOperExt)
   { 
   $sSqlLike=" ( ";
   for($x=0;$x<count($aField);$x++)
      {
	  $sField=$aField[$x];
	  $sFieldLike="";
	  for($y=0;$y<count($aSearch);$y++)
	     {
		 if($y==(count($aSearch)-1))
		    {
			$sWord=$aSearch[$y];
			$sWord="'%".$sWord."%'";
			$sFieldLike=$sFieldLike.$sField." LIKE ".$sWord." ";
			}
		 else
		    {
			$sWord=$aSearch[$y];
			$sWord="'%".$sWord."%'";
			$sFieldLike=$sFieldLike.$sField." LIKE ".$sWord." ".$sOperInt." ";
			}
        }
	    if($x==(count($aField)-1))
		   {
		   $sSqlLike=$sSqlLike." ".$sFieldLike." ";
		   }
		else
		   {
		   $sSqlLike=$sSqlLike." ".$sFieldLike." ".$sOperExt." ";
		   }   
	  }
	$sSqlLike=$sSqlLike." ) ";
	return  $sSqlLike;
   }
   
/*function dbChkQuery($conx,$chkQ,$exeQ)
	{
	$rsChk=dbExec($conx,$chkQ);
	if(!dbFetchRow($rsChk))
		{
		dbExec($conx,$exeQ);
		dbFree($rsChk);
		return true;
		}
	else
		{
		dbFree($rsChk);
		return false;
		}
	}*/
	
function dbChkQuery($chkQ,$exeQ){
	$rsChk=mysql_query($chkQ);
	if(!dbFetchRow($rsChk)){
		mysql_query($exeQ);
		dbFree($rsChk);
		return true;
	}
	else{
		dbFree($rsChk);
		return false;
	}
}
?>
