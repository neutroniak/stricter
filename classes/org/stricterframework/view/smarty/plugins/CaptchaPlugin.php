<?php

require_once("Text/CAPTCHA.php");

class CaptchaPlugin extends BasicPlugin
{
	private $objvar;
	private $params;
	
	function __init()
	{
		$this->smarty->registerPlugin('function', "captcha", array(&$this,"captcha"));

		$this->addAttribute( 'dir' );
		$this->addAttribute( 'fontfile' );
		$this->addAttribute( 'fontsize' );
	}

	function captcha($params, $smarty)
	{
		$params["fontfile"] ? $font_file = $params["fontfile"] : $font_file =  'Vera.ttf';
		$params["fontsize"] ? $font_size = $params["fontsize"] : $font_size =  16;

		$options = array(
			'font_size'	=> $font_size,
			'font_path'	=> Stricter::getConfig('stricter_rpath').'/stricter/fonts/',
			'font_file'	=> $font_file
		);
		
		$objvar = null;

		parent::init($params, $objvar);
		
		$str .= "<img  src=\"". md5(session_id()) . '.png?' . time() ." ";
		
		$str .= parent::csserror();

		$str .= parent::attributes();

		$str .= " name=\"".session_id()."\" ";
		
		$str .= parent::close();
		
		return $str;


			$c = Text_CAPTCHA::factory('Image');

			$retval = $c->init(100, 45, null, $options);

			if (PEAR::isError($retval))
			{
				return $retval->getMessage();
				exit;
			}

			$png = $c->getCAPTCHAAsPNG();

			if (PEAR::isError($png))
				echo $png->getMessage();

			$_SESSION['phrase'] = $c->getPhrase();

			file_put_contents("images/captcha/".md5(session_id()) . '.png', $png);

			$res = tmpfile();

			return '<img '.$custom.' src="images/captcha/' . md5(session_id()) . '.png?' . time() . '" />';

	}
}

?>
