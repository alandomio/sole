<?php
class mydb{

static function post2db($id, $table){
	$ret = array('result' => false, 'message' => '', 'id' => $id);
	
	$msg = new myerr();
	$at = self::get_info($table);
	
	if(empty($id)){ # INSERT
		$crud_op = "INSERT";
		$msg_krud = "REC_INS_BCKH";
		$fil =  " WHERE 1 = 0 ";
		$ack_msg = INS_RECORD;
	} else { # UPDATE
		$crud_op = "UPDATE";
		$msg_krud = "REC_MOD_BCKH";
		$fil = " WHERE ".$table.".".$at['id']."='$id' "; 
		$ack_msg = UPD_RECORD;
	}
	
	$q = "SELECT * FROM $table $fil";
	$rec = rs::rec2arr($q);
	$lable = rs::sql2lbl($q);
	
	$db=new dbio();
	$aRet = rs::light(array($table),$rec,$lable);
	
	list($db->a_name, $db->a_val,$db->a_type,$db->a_maxl,$db->a_default,$db->a_not_null,$db->a_lable,$db->a_dec,$db->a_fkey,$db->a_aval,$db->a_addblank,$db->a_comment,$db->a_sql_type, $db->a_js, $db->a_disabled) = $aRet;
	$db->dbset();

	# C.R.U.D.
	$ERR_CRUD = err::crud($rec);
	$_POST = request::adjustPost($_POST);
	$rec = request::post2arr($lable);
	$rec = arr::magic_quote($rec);
	$rec = arr::_trim($rec);
	
	$db -> a_val = array_merge($db->a_val,$rec);
	$db -> dbset();

	$aPrime = array($db -> primkey -> name => $db -> primkey -> val)  ;
	$ctrl = array("null_ctrl","syntax_ctrl","max_ctrl","min_ctrl","uni_ctrl");
		
	foreach ($rec as $fld => $val){
		$db -> $fld -> dbtable = $table;
		$db -> $fld -> dbkeyfld = $db -> primkey -> name;
		$db -> $fld -> dbkeyval = $db -> primkey -> val;
		
		foreach($ctrl as $func){
			$ERR_CRUD[$fld] = empty($ERR_CRUD[$fld]) ? rs::$func($db->$fld) : $ERR_CRUD[$fld];
		}
	}
	
	if(err::allfalse($ERR_CRUD)){
		$err=rs::execdml($crud_op,$table,$rec,$aPrime);
		$ERR_CRUD['SYSTEMERR'] = err::sqlcrud(SYSTEMERR);
		
		$ERR_CRUD = arr::strip($ERR_CRUD);
		
		if(err::allfalse($ERR_CRUD)){
			$ret['result'] = true;
			if($crud_op == "INSERT"){
				$ret['id'] = mysql_insert_id();
			}
 			$msg -> add_ack ($ack_msg);
		} else {
			$ret['result'] = false;
			$msg -> add_msg($ERR_CRUD, 'err');
		}
	} else $msg -> add_msg($ERR_CRUD, 'err');
	
	$ret['message'] = $msg -> print_msg(false);
	$ret['mode'] = strtolower($crud_op);

	return $ret;
}

static function get_info($table){
	$ret = array();
	$ret['table'] = $table;
	$ret['id'] = stringa::tbl2id($table);
	$ret['field'] = stringa::tbl2field($table);
	
	return $ret;
}

}
?>