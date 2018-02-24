<?php

class HiddenPlugin extends BasicPlugin
{
	public $smarty;
	private $objvar;
	private $params;
	
	function __init()
	{
		$this->smarty->registerPlugin("function", "hidden", array(&$this,"hidden") );
	}

	function hidden($params, &$smarty)
	{
		$objvar=&$params['name'];

		parent::init($params, $objvar);
		
		$theval = $objvar->getValue();
		
		if($params['value']!='')
			$theval = ' value="'.$params['value'].'" ';
		else
			$theval = ' value="'.$objvar->getValue().'" ';

		if($params["multiple"])
			$braces = "[]";

		$str .= "<input type=\"hidden\" name=\"".$objvar->getHash()."$braces\" $theval";

		$str .= parent::csserror();

		$str .= parent::attributes();
		
		$str .= parent::close();
		
		return $str;
	}
}

?>
