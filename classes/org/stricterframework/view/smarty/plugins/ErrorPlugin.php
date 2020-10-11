<?php

class ErrorPlugin extends BasicPlugin
{
	public $smarty;
	private $objvar;
	private $params;

	function __init()
	{
		$this->smarty->registerPlugin("function", "error", array(&$this,"error"));
	}

	function error($params, $smarty)
	{
		$objvar=&$params['name'];
		parent::init($params, $objvar);

		$err = $objvar->getError();

		$str .= '<span ';
		$str .= parent::attributes();
		$str .= '>';

		if($params["prefix"] && $err )
			$str .= $params["prefix"];

		$str .= $err;

		if($params["sufix"] && $err)
			$str .= $params["sufix"];

		$str .= '</span>';

		return $str;
	}
}

?>
