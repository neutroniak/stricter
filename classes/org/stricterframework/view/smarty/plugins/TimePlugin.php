<?php

class TimePlugin extends BasicPlugin
{
	public $stricter;
	public $smarty;
	private $objvar;
	private $params;
	private $field;

	function __init()
	{
		$this->smarty->registerPlugin('function', "time", array(&$this,"time"));

		$this->addAttribute( 'field' );
		$this->addAttribute( 'months' );
		$this->addAttribute( 'offset' );
		$this->addAttribute( 'limit' );
		$this->addAttribute( 'empty' );
		$this->addAttribute( 'interval' );
		$this->addAttribute( 'format' );
		$this->addAttribute( 'string' );
	}

	function time($params, &$smarty)
	{
		$objvar=&$params['name'];

		parent::init($params, $objvar);

		if($params['field']){
			$str.=$this->selectBox($params, $objvar);
		} else if($params['interval']) {
			$str.=$this->optionsBox($params,$objvar);
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

	private function optionsBox(&$params, &$objvar) {

		$str .= "<select name=\"".$objvar->getHash()."\" ";

		$str .= parent::csserror();

		$str .= parent::attributes();

		$str .= ">";

		$st=&Stricter::getInstance();		
	
		$date = DateTime::createFromFormat($st->getConfig('time_format'), '00:00:00');

		$params['interval'] ? $interval=$params['interval'] : $interval="PT30M";

		$params['format'] ? $format=$params['format'] : $format="H:i:s";

		$intervalobj = new DateInterval($interval);

		$str .= "<option value=\"".$date->format('H:i:s')."\">".$date->format($format)."</option>";

		while(1) {
			$date->add($intervalobj);

			if( $date->format('H')=="00" && $date->format('i')=="00")
				break;

			if($objvar->getValue()==$date->format('H').':'.$date->format('i').':'.$date->format('s'))
				$selected='selected="selected"';
			else
				$selected="";
			$str .= "<option $selected value=\"".$date->format('H:i:s')."\">".$date->format($format)."</option>";
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
