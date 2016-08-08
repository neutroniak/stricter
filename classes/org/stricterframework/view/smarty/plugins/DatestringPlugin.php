<?php

class DatestringPlugin extends BasicPlugin
{
	private $objvar;
	private $params;

	private $field;
	private $months;
	private $minyear;
	private $maxyear;

	function __construct()
	{
		Stricter::getSmarty()->registerPlugin('function', "datestring", array(&$this,"datestring"));
	}

	function datestring($params, &$smarty)
	{
		$ex = explode(':',$params["name"]);
		
		$ent = $ex[0];
		$field = $ex[1];

		$objvar =& Stricter::$models[$ent]->$field;
		
		parent::init($params, $objvar);
		
		$theval = $objvar->getValue();
		
		if($params['value']!='')
			$theval = ' value="'.$params['value'].'" ';
		else
			$theval = ' value="'.$objvar->getValue().'" ';

		$str .= "<input type=\"text\" name=\"".$objvar->getHash()."$braces\" $theval";

		$str .= parent::csserror();

		$str .= parent::attributes();
		
		$str .= parent::close();
		
		return $str;
	}
}

?>
