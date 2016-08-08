<?php

class TimestampForm
{	
	function __construct()
	{
		Stricter::getSmarty()->register_function("timestamp", array(&$this,"timestamp"));
	}

	#=========================================================================
	function timestamp($var, &$smarty)
	{
		$ex = explode(':',$var["name"]);
		
		$ent = $ex[0];
		$field = $ex[1];

		$objvar =& Stricter::$models[$ent]->$field;

		$theval = $objvar->value;
		
		$datefield  = $var["_field"];

		//set new css on error
		if($objvar->error!=""  &&  $var["_csserror"]!="")
		{
			unset($var["class"]);
			$custom .= " class=\"".$var["_csserror"]."\" ";
		}
		
		foreach($var as $k=>$v)
			if($k!='name' && $k[0]!='_')
				$custom .= " $k=\"$v\" " ;
		
		switch($var['_field'])
		{
			case 'day':
				$rangeb = 1;
				$rangee = 31;
			break;
			
			case 'month':
				$rangeb = 1;
				$rangee = 12;
				if($var['_months'])
				{
					$nmonths = $var['_months'];
					$arrmonths = $smarty->_tpl_vars[$nmonths];
				}
			break;

			case 'year':
				if($var["_minyear"])
					$rangeb = $var["_minyear"];
				else
					$rangeb = 1900;
					
				if($var["_maxyear"])
					$rangee = $var["_maxyear"];
				else
					$rangee = date('Y');
			break;

			case 'hour':
				$rangeb = 0;
				$rangee = 23;
			break;
			
			case 'minute':
				$rangeb = 0;
				$rangee = 59;
			break;
			
			case 'second':
				$rangeb = 0;
				$rangee = 59;
			break;				
		}
		
		$str .= "<select name=\"".$objvar->name."[".$var['_field']."]\" $custom >";
		
		$str .= "<option value=\"\"></option>";
		
		for($y=$rangeb; $y<=$rangee; $y++)
		{
			strlen($y)==1 ? $yy='0'.$y : $yy=$y;
			
			$theval[$datefield]==$yy ? $sel='selected="selected"' : $sel='';
			if($var['_months']) 
			{
				$yyval= $arrmonths[$y];
				$str .= "<option value=\"$yy\" $sel>$yyval</option>\n";
			}
			else
			{
				$str .= "<option value=\"$yy\" $sel>$yy</option>\n";
			}
		}
		
		$str .= "</select>";	

		if($var["_jsreq"])
			$str .="<script>frmvalidator.addValidation(\"".$objvar->name."[".$var["_field"]."]"."\",\"req\",'".$var["_jsreq"]."');</script>";
		
		return $str;
	}
}

?>
