<?php

class CheckboxPlugin extends BasicPlugin
{
	public $smarty;
	public $stricter;
	private $objvar;
	private $params;
	
	function __init()
	{
		$this->smarty->registerPlugin('function', "checkbox", array(&$this,"checkbox"));
	}

	function checkbox($params, $smarty)
	{
		$objvar=&$params['name'];

		$theval = $objvar->getValue();

		parent::init($params, $objvar);

		$realname = $objvar->getHash();

		if($params["multiple"])
		{
			$params["multiple"] = "multiple";
			$braces = '[]';
			
			if($params["value"]) {
			
					$pvalue = $params["value"];
		
					if( @in_array($pvalue, $theval) )
						$sel = " checked='checked' ";
					else
						$sel = null;
					
					$str .= "<input type=\"checkbox\" $custom $sel value=\"".$params["value"]."\" name=\"".$objvar->getHash()."[]\" />";
			} else {

				foreach ( $params['options'] as $ko=>$vo)
				{
					if( @in_array($ko, $theval) )
						$sel = " checked='checked' ";
					else
						$sel = null;

					$str .= "<input type=\"checkbox\" $custom $sel value=\"$ko\" name=\"".$objvar->getHash()."[]\" />$vo";
				}
			}
		}
		else
		{
			if( ($params['value'] == $params['sel']) && $params["sel"] )
			{
				$sel = ' checked="checked" ';
			}
			else if($theval == true)
			{
				$val = 1;
				$sel = ' checked="checked" ';
			}
			else
			{
				$val = 0;
				$sel = '';
			}

			$str .= "<input type=\"checkbox\" id=\"__".$realname."\" ";

			$str .= parent::attributes();

			$str .= " $sel value=\"$val\" onclick=\"__chg".$realname."();\" />";

			$str .= "\n <input type=\"hidden\" name=\"".$objvar->getHash()."\" id=\"".$realname."\" value=\"$val\" />";

			$str .=  "<script type=\"text/javascript\">function __chg".$realname."() {
					if(document.getElementById('__".$realname."').value == 1) {
						document.getElementById('__".$realname."').value = 0;
						document.getElementById('".$realname."').value = 0;
					}
					else {
						document.getElementById('__".$realname."').value = 1;
						document.getElementById('".$realname."').value = 1;
					}
				}</script>";
		}
		
		return $str;
	}
}

?>
