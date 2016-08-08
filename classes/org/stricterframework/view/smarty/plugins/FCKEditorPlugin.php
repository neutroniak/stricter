<?php

class FCKEditorForm extends BasicForm
{
	private $objvar;
	private $params;

	function __construct()
	{
		Stricter::getSmarty()->register_function("fckeditor", array(&$this,"fckeditor"));

		//$this->addAttribute( 'fckpath' );
	}

	#=========================================================================
	function fckeditor($params, &$smarty)
	{
		if(! Stricter::getConfig('fckpath') )
			return "fck needs a path";
		else 
			$fckpath = Stricter::getConfig('fckpath');

		$objvar=&$params['name'];

		parent::init($params, $objvar);

		$theval = $objvar->getValue();

		$str .= "<script src=\"".$fckpath."/fckeditor.js\" type=\"text/javascript\"></script>\n";
		$str .= "<script type=\"text/javascript\">\n";
		$str .= "function __fck".$params['id']."()\n";
		$str .= "{\n";
		$str .= "  if(document.getElementById('".$params['id']."'))\n";
		$str .= "  {\n";
		$str .= "    var oFCKeditor".$params['id']." = new FCKeditor('".$params['id']."') ;\n";
		$str .= "    oFCKeditor".$params['id'].".BasePath = '".$fckpath."/';\n";

		if($params['toolbar']){
			$str .= "    oFCKeditor".$params['id'].".ToolbarSet = '".$params['toolbar']."';\n";
		}
		if($params["width"])
			$str .= "    oFCKeditor".$params['id'].".Width = ".$params["width"].";\n";
		if($params["height"])
			$str .= "    oFCKeditor".$params['id'].".Height = ".$params["height"].";\n";
		
		$str .= "    oFCKeditor".$params['id'].".ReplaceTextarea();\n";
		$str .= "  }\n";
		$str .= "}\n";
		$str .= "if(window.addEventListener)\n";
		$str .= "	window.addEventListener(\"load\", __fck".$params['id'].", false);\n";
		$str .= "else\n";
		$str .= "	window.attachEvent(\"onload\", __fck".$params['id'].");\n";
		$str .= "</script>\n";

		$str .= "<textarea name=\"".$objvar->getHash()."\" ";

		$str .= parent::csserror();

		$str .= parent::attributes();
		
		$str .= ">";

		$str .= $theval;

		$str .= "</textarea>";

		$str .= parent::jsvalid();
		
		return $str;
	}

}


?>
