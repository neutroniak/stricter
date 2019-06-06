<?php

class HeadPlugin extends BasicPlugin
{
	public $smarty;
	private $objvar;
	private $params;

	function __init()
	{
		$this->smarty->registerPlugin("block", "head", array(&$this, 'head'));

		$this->addAttribute( 'value' );
		$this->addAttribute( 'doctype' );
		$this->addAttribute( 'jquery-ui-theme' );
	}

	function head($params, $content, $smarty)
	{
		$theme=Stricter::getInstance()->getConfig('theme');
		$webpath=Stricter::getInstance()->getConfig('webpath');
		$charset=Stricter::getInstance()->getConfig('charset');
		$mdl=Stricter::getInstance()->getMdl();

		$str="<head>\n";
		$str.='	<meta http-equiv="content-type" content="text/html; charset='.$charset.'"/>';

		$ajax=false;
		if(Stricter::getInstance()->getContentType()==Stricter::OUT_AJAX)
			$ajax=true;

		if($ajax==false) {
			$str.='<meta name="description" content=""/>';
			$str.='<meta name="keywords" content=""/>';
			$str.='<meta name="copyright" content="'.$params['copyright'].'"/>';
			$str.='<meta name="author" content="'.$params['author'].'" />';
			$str.='<meta name="MSSmartTagsPreventParsing" content="true"/>';
			$str.='<meta name="robots" content="index, follow"/>';
			$str.='<meta http-equiv="pragma" content="no-cache"/>';
			$str.='<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">';
			$str.='<meta http-equiv="cache-control" content="no-cache"/>';
			$str.='<link href="'.$webpath.'/themes/'.$theme.'/images/favicon.png" rel="shortcut icon" type="image/png"/>';
			$str.='<link href="'.$webpath.'/themes/'.$theme.'/css/global.css" rel="stylesheet" type="text/css"/>'."\n";
			$str.='<script src="'.$webpath.'/js/global.js" type="text/javascript" ></script>'."\n";
			$str.='<script src="https://code.jquery.com/jquery-1.12.1.min.js" integrity="sha256-I1nTg78tSrZev3kjvfdM5A5Ak/blglGzlaZANLPDl3I="
     crossorigin="anonymous"></script>'."\n";
		}
		$str.=' <script type="text/javascript" src="'.$webpath.'/stricter/javascript/stricter.js"></script>'."\n";
		
		$str.='	<script type="text/javascript">var webpath="'.$webpath.'";var theme="'.$theme.'";';
		$ajax ?	$str.='var isAjax=true;' : $str.='var isAjax=false;';
		$str.='stricter.ajax.charset="UTF-8";var mdl="'.$mdl.'";</script>'."\n";
		
		$str.=' <script type="text/javascript" src="'.$webpath.'/stricter/javascript/stricter-ui.js"></script>'."\n";
		if($params['jquery-ui-theme']) {
			$str.=' <link rel="stylesheet" href="https://code.jquery.com/ui/1.11.4/themes/'.$params['jquery-ui-theme'].'/jquery-ui.css"/>'."\n";
			$str.='<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js" integrity="sha256-xNjb53/rY+WmG+4L6tTl9m6PpqknWZvRt0rO1SRnJzw="
     crossorigin="anonymous"></script>'."\n";
		}
		$str.=$content;
		$str.='	<title>'.$params['title'].'</title>';
		$str.="\n</head>";
		if($content)
			return $str;
	}
}

?>
