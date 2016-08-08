<?php

class SvgComponent extends BasicComponent
{
	private $params;
	
	function __construct()
	{
		$this->smarty->registerPlugin("function", "svg", array(&$this,"svg"));

		$this->addAttribute('data');
	}

	function svg($params, &$smarty)
	{
		parent::init($params, null);

		$str .= "<object data=\"".$params["data"]."\" type=\"image/svg+xml\"";

		$str .= parent::attributes();

		$str .= " >";

		$str .= "</object>";

		return $str;
	}
}

?>
