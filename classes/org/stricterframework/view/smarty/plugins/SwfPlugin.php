<?php

class SwfPlugin extends BasicPlugin
{
	private $params;
	
	function __init()
	{
		$this->smarty->registerPlugin('function', 'swf', array(&$this,'swf'));
		$this->addAttribute('data');
		$this->addAttribute('width');
		$this->addAttribute('height');
		$this->addAttribute('bgcolor');
		$this->addAttribute('quality');
		$this->addAttribute('wmode');
	}

	function swf($params, &$smarty)
	{
		parent::init($params, $objvar);

		$str .= "<object data=\"".$params["data"]."\" type=\"application/x-shockwave-flash\" width=\"".$params["width"]."\" height=\"".$params["height"]."\" ";

		$str .= parent::attributes();

		$str .= " >\n";

		$str .= "<param name=\"movie\" value=\"".$params["data"]."\" />\n";

		if($params["quality"])
			$str .= "<param name=\"quality\" value=\"".$params["quality"]."\" />\n";
		else
			$str .= "<param name=\"quality\" value=\"high\" />\n";

		$str .= "<param name=\"allowScriptAccess\" value=\"sameDomain\" />\n";

		if($params["bgcolor"])
			$str .= "<param name=\"bgcolor\" value=\"".$params["bgcolor"]."\" />";

		if($params["wmode"])
			$str .= "<param name=\"wmode\" value=\"".$params["wmode"]."\" />";
		else
			$str .= "<param name=\"wmode\" value=\"transparent\" />";

		$str .= "</object>\n";

		return $str;
	}
}

?>
