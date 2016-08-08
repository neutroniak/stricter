<?php

class TextareaPlugin extends BasicPlugin
{
	public $stricter;
	public $objvar;
	public $smarty;

	function __init() {
		$this->smarty->registerPlugin('function', "textarea", array(&$this,"textarea"));
	}

	function textarea($params, &$smarty)
	{
		$objvar=&$params['name'];

		parent::init($params, $objvar);

		$theval = $objvar->getValue();

		$str = "<textarea name=\"".$objvar->getHash()."\" ";

		$str .= parent::csserror();

		if($params["cols"]=="")
			$str .=" cols=\"\" ";

		if($params["rows"]=="")
			$str .=" rows=\"\" ";

		$str .= parent::attributes();

		$str .= ">";

		$this->formatString($theval);

		$str .= $theval;

		$str .= "</textarea>";

		$str .= parent::jsvalid();

		return $str;
	}
}

?>
