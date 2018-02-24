<?php

class DatePlugin extends BasicPlugin
{
	public $stricter;
	public $smarty;
	private $objvar;
	private $params;
	private $field;
	private $months;
	private $minyear;
	private $maxyear;

	function __init()
	{
		$this->smarty->registerPlugin('function', "date", array(&$this,"date"));

		$this->addAttribute( 'field' );
		$this->addAttribute( 'months' );
		$this->addAttribute( 'minyear' );
		$this->addAttribute( 'maxyear' );
		$this->addAttribute( 'offset' );
		$this->addAttribute( 'limit' );
		$this->addAttribute( 'empty' );
		$this->addAttribute( 'string' );
	}

	function date($params, &$smarty)
	{
		$objvar=&$params['name'];

		parent::init($params, $objvar);

		if($params['field']){
			$str.=$this->selectBox($params, $objvar);
		} else {
			$str.=$this->inputBox($params,$objvar);
		}

		return $str;
	}

	private function selectBox(&$params, &$objvar) {

		$datefield=$params['field'];
		$theval = $objvar->__get($params['field']);
		$str .= "<select name=\"".$objvar->getHash()."[".$datefield."]\" ";

		$str .= parent::csserror();

		$str .= parent::attributes();

		$str .= ">";

		switch($params["field"])
		{
			case "day":
				$rangeb = 1;
				$rangee = 31;
			break;

			case "month":
				$rangeb = 1 + $params['offset'];
				$rangee = 12 + $params['offset']  - $params['limit'];
				if($params['months'])
					$arrmonths = $params['months'];
			break;

			case 'year':
				if($params["minyear"])
					$rangeb = $params["minyear"];
				else
					$rangeb = 1902;

				if($params["maxyear"])
					$rangee = $params["maxyear"];
				else
					$rangee = date('Y');
				
			break;

			case 'hour':
				$rangeb = 0;
				$rangee = 23;
			break;

			case 'minute':
				$rangeb = 0;
				$rangee = 59;
			break;

			case 'second':
				$rangeb = 0;
				$rangee = 59;
			break;
		}

		$str .= "<option value=\"\">".$params["empty"]."</option>";

		for($y=$rangeb; $y<=$rangee; $y++)
		{
			strlen($y)==1 ? $yy='0'.$y : $yy=$y;

			if($theval!==null)
				$theval==$yy ? $sel='selected="selected"' : $sel='';

			if($params['months'])
				$str .= "<option value=\"$yy\" $sel>".$arrmonths[$y]."</option>\n";
			else
				$str .= "<option value=\"$yy\" $sel>$yy</option>\n";
		}

		$str .= "</select>";
		return $str;
	}

	private function inputBox(&$params, &$objvar){
		$str = "<input type=\"text\" ";

		$str .= parent::csserror();

		$str .= parent::attributes();

		$str .=" name=\"".$objvar->getHash()."\" value=\"".$objvar->toString()."\" />";

		$str .= parent::jsvalid();

		return $str;
	}
}

?>
