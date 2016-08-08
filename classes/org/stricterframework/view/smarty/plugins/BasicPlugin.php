<?php

abstract class BasicPlugin
{
	//common attributes for almost all forms
	public $attributes = array( 'name', 'csserror', 'jsmin', 'jsminlen', 'jsreq', 'jsemail', 'jsselone', 'jsnum' );
	private $objvar;
	private $params;
	
	public function init(&$params, &$mdlobj)
	{
		$this->params =& $params;
		$this->objvar =& $mdlobj;
	}
	
	function jsvalid()
	{
		if($this->objvar instanceof DateField || $this->objvar instanceof DatetimeField)
			$hash = $this->objvar->getHash().'['.$this->params["field"].']';
		else
			$hash = $this->objvar->getHash();
		
		if($this->params["slice"])
			$hash .= '_'.$this->params["slice"];
			
		if($this->params["jsreq"])
			$str .="<script type=\"text/javascript\">frmvalidator.addValidation(\"".$hash."\",\"req\",'".$this->params["jsreq"]."');</script>";

		if($this->params["jsnum"])
			$str .="<script type=\"text/javascript\">frmvalidator.addValidation(\"".$hash."\",\"num\",'".$this->params["jsnum"]."');</script>";

		if($this->params["jsemail"])
			$str .="<script type=\"text/javascript\">frmvalidator.addValidation(\"".$hash."\",\"email\",'".$this->params["jsemail"]."');</script>";

		if($this->params["jsmin"])
			$str .="<script type=\"text/javascript\">frmvalidator.addValidation(\"".$hash."\",\"minlen=".$this->params["jsminlen"]."\",'".$this->params["jsmin"]."');</script>";

		if($this->params["jsselone"])
			$str .="<script type=\"text/javascript\">frmvalidator.addValidation(\"".$hash."\",\"selone\",'".$this->params["jsselone"]."');</script>";
		
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
