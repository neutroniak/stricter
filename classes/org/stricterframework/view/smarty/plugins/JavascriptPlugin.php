<?php

class JavascriptPlugin extends BasicPlugin
{
	public $smarty;
	private $params;

	function __init()
	{
		$this->smarty->registerPlugin('function', 'javascript', array(&$this,'javascript'));

		$this->addAttribute( 'src' );
		$this->addAttribute( 'method' );
	}

	function javascript($params, $smarty)
	{
		parent::init($params, $objvar);

		if(!$params['src'])
			return "js file required";

		if($params['method']=='include') {
			return "<script src=\"".$params['src']."\" type=\"text/javascript\"></script>";
		} else {
			if($str=file_get_contents($params['src']) )
				return "<script type=\"text/javascript\"><!--\n".$str."\n//--></script>";
		}
	}
}

?>
