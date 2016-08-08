<?php

class UploadPlugin extends BasicPlugin
{
	public $smarty;
	private $objvar;
	private $params;

	function __init()
	{
		$this->smarty->registerPlugin("function", "upload", array(&$this,"upload") );
	}

	function upload($params, &$smarty)
	{
		$objvar=&$params['name'];
	
		parent::init($params, $objvar);

		$str .= "<input type=\"file\" ";

		$str .= " name=\"".$objvar->getHash()."\" ";

		$str .= parent::csserror();

		$str .= parent::attributes();

		//$str .= parent::jsvalid();

		$str .= parent::close();

		return $str;
	}
}

?>
