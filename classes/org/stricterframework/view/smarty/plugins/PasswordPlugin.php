<?php

class PasswordPlugin extends BasicPlugin
{	
	public $smarty;
	private $objvar;
	private $params;
	
	function __init()
	{
		$this->smarty->registerPlugin("function", "password", array(&$this,"password"));

		$this->addAttribute( 'value' );
	}

	function password($params, $smarty)
	{
		$objvar=&$params['name'];

		parent::init($params, $objvar);
		
		$str .= "<input type=\"password\" ";

		$str .= parent::csserror();

		$str .= parent::attributes();

		$str .= " name=\"".$objvar->getHash()."\" maxlength=\"".$objvar->getSize()."\" value=\"".$objvar->getValue()."\"";
		
		$str .= parent::close();
		
		$str .= parent::jsvalid();

		return $str;
	}
}

?>
