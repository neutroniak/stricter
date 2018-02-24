<?php

class OutPlugin extends BasicPlugin
{
	public $smarty;
	private $objvar;
	private $params;

	function __init()
	{
		$this->smarty->registerPlugin('function', "out", array(&$this,"out"));

		$this->addAttribute( 'date' );
		$this->addAttribute( 'strftime' );
	}

	function out($params, &$smarty)
	{
		$objvar=&$params['name'];

		$theval = $objvar->getValue();

		if($params['date'] && $theval)
			$theval = date($params['date'], $theval);
		else if($params['strftime'] && $theval)
			$theval = strftime($params['strftime'], strtotime($theval));

		$str = $theval;

		return $str;
	}
}

?>
