<?php

class SubmitPlugin extends BasicPlugin
{
	public $smarty;

	function __init()
	{
		$this->smarty->registerPlugin("function", "submit", array(&$this,"submit"));
	}

	function submit($params, $smarty)
	{
		parent::init($params, $objvar);

		$str .= "<input type=\"submit\" ";

		$str .= parent::attributes();

		$str .= parent::close();

		return $str;
	}
}

?>
