<?php

class InputPlugin extends BasicPlugin
{
	public $smarty;
	private $objvar;
	private $params;
	
	function __init()
	{
		$this->smarty->registerPlugin("function", "input", array(&$this,"input"));

		$this->addAttribute( 'negative' );
		$this->addAttribute( 'autotab' );
		$this->addAttribute( 'slice' );
		$this->addAttribute( 'value' );
	}

	function input($params, $smarty)
	{
		$objvar=&$params['name'];
		parent::init($params, $objvar);

		if(!$params['value'])
			$theval = stripslashes($objvar->getValue());
		else
			$theval = $params['value'];

		$exs = explode(',', $objvar->getSize());

		$size = $exs[0];

		if($params["negative"]==true)
			$size +=1;

		if($params["slice"]){
			$slice = '_'.$params["slice"];

			$slicex = explode(',',$params["slice"]);

			if($slicex[0]==0)
				$theval = substr($theval, $slicex[0], $slicex[1]);
			else
				$theval = substr($theval, $slicex[0]-1, $slicex[1]);
			
			$size = $slicex[1];
		}

		$sizen = $size;

		if($objvar->getSize() != "")
			$size = " maxlength=\"".$size."\" ";

		if($params["autotab"]==true)
			$autotab = " onkeyup=\"return autoTab(this, $sizen, event)\" ";

		$this->formatString($theval);

		$theval = 'value="'.$theval.'"';

		$str .= "<input type=\"text\" ";

		$str .= parent::csserror();

		$str .= parent::attributes();

		$str .=" name=\"".$objvar->getHash()."$slice\" $autotab $size $theval />";

		$str .= parent::jsvalid();

		return $str;
	}
}

?>
