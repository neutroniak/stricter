<?php

class CssPlugin extends BasicPlugin
{
	public $smarty;
	private $params;

	function __init()
	{
		$this->smarty->registerPlugin('function', 'css', array(&$this,'css'));

		$this->addAttribute( 'src' );
		$this->addAttribute( 'method' );
	}

	function css($params, &$smarty)
	{
		if(!$params['src'])
			return "css file required";

		if($params['method']=='include') {
			return "<link href=\"".$params['src']."\" rel=\"stylesheet\" type=\"text/css\" />";
		} else { 
			if($str=file_get_contents( $params['src'] ) )
				return '<style type="text/css"><!--'."\n".$str.'--></style>';
		}
	}
}

?>
