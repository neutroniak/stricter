<?php

class JsonPlugin extends BasicPlugin
{
	public $stricter;
	public $smarty;
	private $objvar;
	private $params;
	private $field;

	function __init()
	{
		$this->smarty->registerPlugin('function', "json", array(&$this,"json"));

		$this->addAttribute( 'field' );
		$this->addAttribute( 'months' );
		$this->addAttribute( 'minyear' );
		$this->addAttribute( 'maxyear' );
		$this->addAttribute( 'offset' );
		$this->addAttribute( 'limit' );
		$this->addAttribute( 'empty' );
	}

	function json($params, $smarty)
	{
		$objvar=&$params['name'];

		parent::init($params, $objvar);

		return "";
	}
}

?>

