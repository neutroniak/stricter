<?php

class BackButtonPlugin extends BasicPlugin
{
	private $stricter;
	private $params;
	
	function __init()
	{
		$this->stricter->getSmarty()->registerPlugin('function', "backbutton", array(&$this,"backbutton"));
	}

	function backbutton($params, $smarty)
	{
		parent::init($params, $params);

		$str .= "<input type=\"button\" onclick=\"javascript:window.history.go(-1);\" ";

		$str .= parent::attributes();

		$str .= " />";

		return $str;
	}
}

?>
