<?php

class RadioPlugin extends BasicPlugin
{
	public $smarty;
	private $objvar;
	private $params;

	function __init()
	{
		$this->smarty->registerPlugin("function", "radio", array(&$this,"radio"));
	}

	function radio($params, &$smarty)
	{
		$objvar=&$params['name'];

		parent::init($params, $objvar);

		$theval = $objvar->getValue();

		$hash = $objvar->getHash();

		if($objvar->getRequired() == true && ! $_POST[$hash]) { //workaround for empty radio initial values
			$objreq = $objvar->getRequired();
			$_POST[$objreq] = "";
		}

		$str .= "<input type=\"radio\" name=\"".$objvar->getHash()."\"";

		$str .= parent::csserror();

		$str .= parent::attributes();

		if($params['value'] == $theval)
			$sel = ' checked="checked" ';
		else
			$sel = '';

		$str .= " $sel />";

		$str .= parent::jsvalid();

		return $str;
	}
}

?>
