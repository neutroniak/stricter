<?php

class ErrorPlugin
{
	public $smarty;

	function __init()
	{
		$this->smarty->registerPlugin("function", "error", array(&$this,"error"));
	}

	function error($params, &$smarty)
	{
		$objvar=&$params['name'];

		if($params["prefix"] && $objvar->getError() )
			$str .= $params["prefix"];

		$str .= $objvar->getError();

		if($params["sufix"] && $objvar->getError())
			$str .= $params["sufix"];

		return $str;
	}
}

?>
