<?php

class ResetForm extends BasicForm
{
	private $params;

	function __construct()
	{
		$this->smarty->registerPlugin("function", "reset", array(&$this,"reset"));
	}

	function reset($params, &$smarty)
	{
		parent::init($params, null);

		$str .= "<input type=\"reset\" ";

		$str .= parent::attributes();

		$str .= parent::close();

		return $str;
	}
}

?>
