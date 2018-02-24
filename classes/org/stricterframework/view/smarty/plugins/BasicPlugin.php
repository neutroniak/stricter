<?php

abstract class BasicPlugin
{
	//common attributes for almost all plugins
	public $attributes = array( 'name', 'csserror' );
	private $objvar;
	private $params;
	
	public function init(&$params, &$mdlobj)
	{
		$this->params =& $params;
		$this->objvar =& $mdlobj;
	}
	
	function jsvalid()
	{
		$hash = $this->objvar->getHash();
		
		if($this->params["slice"])
			$hash .= '_'.$this->params["slice"];
			
		return $str;
	}

	function attributes()
	{
		if(!$this->params)
			return null;
		
		foreach($this->params as $k=>$v)
			if( !in_array($k, $this->attributes) )
				$str .= " $k=\"$v\" " ;

		return $str;
	}

	function csserror()
	{
		if(!$this->params['csserror'])
			return;
		$custom = "";

		if($this->objvar->getError()!=null)	{
			unset($this->params["class"]);
			$custom = " class=\"".$this->params["csserror"]."\" ";
		}

		return $custom;
	}

	function close()
	{		
		return " />";
	}

	function formatString(&$wstring)
	{
		$wstring = str_replace('"', '&quot;', $wstring);
		$wstring = str_replace('\\', '&#92;', $wstring);
		$wstring = str_replace('\'', '&#39;', $wstring);
		$wstring = str_replace("\\r\\n", "\n", $wstring);

		$wstring = stripslashes($wstring);

		return $wstring;
	}

	protected function addAttribute($arr)
	{
		array_push($this->attributes, $arr);
	}
}

?>
