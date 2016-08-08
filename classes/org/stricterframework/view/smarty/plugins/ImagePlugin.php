<?php

class ImagePlugin extends BasicPlugin
{
	public $smarty;
	private $params;
	
	function __init()
	{
		$this->smarty->registerPlugin('function', "image", array(&$this,"image"));
		
		$this->addAttribute( 'src' );
		$this->addAttribute( 'vprefix' );
		$this->addAttribute( 'fprefix' );
	}

	function image($params, &$smarty)
	{
		parent::init($params, $objvar);

		$str .= "<img src='".$this->base64ImageEncode($params['src'],$params['vprefix'], $params['fprefix'])."'";

		$str .= parent::attributes();
		
		$str .= parent::close();
		
		return $str;
	}

	private function base64ImageEncode($filename, $vprefix=null, $fprefix=null) {
		if($fprefix!=null)
			$filename=$fprefix.'/'.$filename;
		else
			$filaneme=$filename;

		$filetype=filetype($filename);
		$imgbinary = fread(fopen($filename, "r"), filesize($filename));
		$baseimage = base64_encode($imgbinary);
		
		if( (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 8')  && strlen($baseimage)>=32768) || strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE 7')){ // IE8 puts a stupid limit on large URIs
			$vprefix=$vprefix.'/';
			return $vprefix.$filename;
		} else {
			return 'data:image/' . $filetype . ';base64,' . $baseimage;
		}
	}
}

?>
