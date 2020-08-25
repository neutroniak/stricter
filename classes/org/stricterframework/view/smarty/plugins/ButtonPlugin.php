<?php

class ButtonPlugin extends BasicPlugin
{
	public $smarty;
	private $params;

	function __init()
	{
		$this->smarty->registerPlugin('function', 'button', array(&$this,'button'));
		$this->addAttribute('href');
	}

	function button($params, $smarty)
	{
		parent::init($params, $params);

		if($params['href'])
			$href=' onclick="window.location.href=\''.$params['href'].'\'" ';
		$str .= "<input type=\"button\" ".$href;

		$str .= parent::attributes();

		$str .= parent::close();

		return $str;
	}
}

?>
