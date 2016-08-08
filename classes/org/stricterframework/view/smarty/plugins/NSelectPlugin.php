<?php

class NSelectForm
{
	function __construct()
	{
		Stricter::getSmarty()->register_function("nselect", array(&$this,"nselect"));
	}

	#=========================================================================
	function nselect($var, &$smarty)
	{exit;//deprecated
		$ex = explode("->",$var["name"]);
		
		$ent = $ex[0];
		$field = $ex[1];
		
		$objvar =& Stricter::$models[$ent]->$field;

		$theval = $objvar->value;

		//set new css on error
		if($name->error!=""  &&  $var["_csserror"]!="")
		{
			unset($var["class"]);
			$custom .= " class=\"".$var["_csserror"]."\" ";
		}
		
		foreach($var as $k=>$v)
		{
			if($k[0]!='_' && $k!='name')
				$custom .= " $k=\"$v\" " ;
			else
				$custom .= '';
		}
		
		if($var['_conditions'])
			$where = 'WHERE '.$var['_conditions'];
		
		$fkx = explode(':', $name->fk);
			
	 	$sql = "SELECT $keyname, ${var['_field']} FROM  ${var['name']} $where";
		
		$query = $this->stricter->db->query($sql);
		
		$nselected = array();
		
		if(!$_POST[$objvar])
		{
			$sqlrel = "SELECT $keyname FROM ${modelname2}".$this->stricter->db->table_ndelimiter."$objvar WHERE $keynamethis = '$_GET[id]' ";
			
			$queryrel = $this->stricter->db->query($sqlrel);

			$i = 0;

			while($rrel = $this->stricter->db->fetch($queryrel))
			{
				$nselected[$i]=$rrel[0];

				$i++;
			}
		}
		else
		{
			$nselected = $_POST[$objvar];
		}
		
		$str = "<select multiple=\"multiple\" name=\"".$objvar."[]\" $custom ><option value=\"\" ></option>\n";
		
		while($r = $this->stricter->db->fetch($query))
		{
			if(in_array($r[0], $nselected))
				$sel = 'selected="selected"';
			else $sel = '';
			
			$field = $r[$var['_field']];
			
			$str .= "\n<option value=\"$r[0]\" $sel>$field</option>";
		}
				
		$str .= "\n</select>";

		if($var["_jsreq"])
			$str .="<script>frmvalidator.addValidation(\"".$objvar->name."\",\"req\",'".$var["_jsreq"]."');</script>";

		
		return $str;
	}


}


?>
