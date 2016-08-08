<?php

class FormPlugin extends BasicPlugin
{
	public $smarty;
	private $params;

	function __init()
	{
		$this->smarty->registerPlugin("block", "form", array(&$this,"form") );

		$this->addAttribute( 'action' );
		$this->addAttribute( 'method' );
		$this->addAttribute( 'enctype' );
	}

	function form($params, $content, &$smarty)
	{
		parent::init($params, $objvar);

		if($params["action"] )
			$act = $params["action"];
		else
			$act = $_SERVER['REQUEST_URI'];

		$action = "action=\"".$act."\" ";

		if($params["method"])
			$method="method=\"".$params["method"]."\"";
		else
			$method="method=\"post\"";

		if($params["enctype"])
			$enctype="";
		else
			$enctype=" enctype=\"multipart/form-data\" ";

		$str .= "<form ";
		$str .= parent::attributes();
		$str .= " $method $action $custom $enctype >";
		
		if($params['id']) {
	#		$str .= "<script src=\"".Stricter::getConfig('stricter_vpath')."/javascript/form_validator/gen_validatorv2.js\" type=\"text/javascript\"></script>";
#			$str .= "<script type=\"text/javascript\">var frmvalidator  = new Validator(\"".$params['id']."\");</script>";
		}

		$str.=$content."</form>\n\n";

		if($content)
			return $str;
		else
			return null;
	}
}

?>
