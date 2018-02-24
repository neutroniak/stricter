<?php

class ButtonPlugin extends BasicPlugin
{
	public $smarty;
	private $params;

	function __init()
	{
		$this->smarty->registerPlugin('function', 'button', array(&$this,'button'));
	}

	function button($params, &$smarty)
	{
		parent::init($params, $params);

		$str .= "<input type=\"button\" ";

		$str .= parent::attributes();

		$str .= parent::close();

		return $str;
	}
}

?>
