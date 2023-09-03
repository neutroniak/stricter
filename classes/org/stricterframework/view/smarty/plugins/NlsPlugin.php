<?php

class NlsPlugin extends BasicPlugin
{
	public $smarty;
	private $params;

	function __init()
	{
		$this->smarty->registerPlugin('block', 'nls', array(&$this,'nls'));
	}

	function nls($params, $content, $smarty)
	{
		parent::init($params, $objvar);

		$wordto = $content;

		if(defined($wordto))
			$content = @constant($wordto);

		if(count($params)>0) {

			if(isset($params['upper']))
				$content=strtoupper($content);

			if(isset($params['lower']))
				$content=strtolower($content);

			if(isset($params['capital']))
				$content=ucfirst($content);
		}
		return $content;
	}
}

?>
