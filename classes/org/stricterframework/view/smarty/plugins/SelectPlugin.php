<?php

class SelectPlugin extends BasicPlugin
{
	public $smarty;
	private $objvar;
	private $params;

	private $options;
	private $multiple;
	private $empty;

	function __init()
	{
		$this->smarty->registerPlugin("function", "select", array(&$this,"select"));

		$this->addAttribute( 'options' );
		$this->addAttribute( 'multiple' );
		$this->addAttribute( 'empty' );
	}

	function select($params, $smarty)
	{
		$objvar=&$params['name'];

		$theval = $objvar->getValue();

		parent::init($params, $objvar);

		$str .= "<select ";

		if($params["multiple"])
		{
			$params["multiple"] = "multiple";
			$braces = '[]';
			$multiple = ' multiple="multiple" ';
		}

		$str .= parent::csserror();

		$str .= parent::attributes();

		$str .= " name=\"".$objvar->getHash().$braces."\" $multiple>\n";

		if($params['empty']=="false")
			$str .= "";
		else if($params['empty']!="")
			$str .= "<option value=\"\">".$params['empty']."</option>\n";
		else
			$str .= "<option></option>\n";

		if(isset($params["options"]) && count($params["options"])>0){
			foreach($params["options"] as $key=>$value) {
				if( is_array($theval) && in_array($key, $theval) )
					$sel = 'selected="selected"';
				else if( $key == $objvar->getValue())
					$sel = 'selected="selected"';
				else
					$sel = '';
				$str .= "\n<option value=\"$key\" $sel>$value</option>";
			}
		}

		$str .= "\n</select>";

		$str .= parent::jsvalid();

		return $str;
	}
}

?>
