<?php

class ResetPlugin extends BasicPlugin
{
	public $smarty;
	private $objvar;
	private $params;

	function __init()
	{
		$this->smarty->registerPlugin("function", "reset", array(&$this,"reset"));
	}

	function reset($params, $smarty)
	{
		parent::init($params, null);

		$str .= "<input type=\"reset\" ";

		$str .= parent::attributes();

		$str .= parent::close();

		return $str;
	}
}

?>
