<?php

class BodyPlugin extends BasicPlugin
{
	public $smarty;
	private $objvar;
	private $params;

	function __init()
	{
		$this->smarty->registerPlugin("block", "body", array(&$this,"body"));

	}

	function body($params, $content, $smarty)
	{
		$str ="<body>\n";
		$str .='	<div class="st-icon-loading" style="display:none;"></div>';
		$str.=$content;
		$str.="\n".'</body>'."\n";
		if($content)
			return $str;
	}
}

?>
