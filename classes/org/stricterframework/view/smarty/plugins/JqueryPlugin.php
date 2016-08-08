<?php

class JqueryPlugin extends BasicPlugin
{
	public $smarty;
	public $stricter;

	function __init() 
	{
		$this->smarty->registerPlugin('function', 'jquery', array(&$this,'jquery'));
		$this->addAttribute( 'version' );
		$this->addAttribute( 'plugin' );
		$this->addAttribute( 'min' );
	}

	function jquery($params, &$smarty) {

		if($params['plugin']){

			switch($params['plugin']){
			case "jquery-ui":

			break;

			default:

				break;
			}
		} else {
			$resourcesDir=$this->stricter->getConfig('resources_dir');
			if(!$params['version'] || ! $resourcesDir )
				return "<!-- no jquery -->";

			if($params['min']===false)
				$minjs='.js';
			else
				$minjs='.min.js';

			$jquery=$resourcesDir.'/jquery/jquery-'.$params['version'].$minjs;

			if(! $content=file_get_contents($jquery) ){
				Stricter::getInstance()->log("Jquery download!");
				$content=file_get_contents("http://code.jquery.com/jquery-".$params['version'].$minjs);
				mkdir($resourcesDir.'/jquery/');
				$fp=fopen($jquery, 'w');
				fwrite($fp, $content, strlen($content)+1);
				fclose($fp);
			}
			$webpath=$this->stricter->getConfig('webpath');
			$jquery=$webpath.'/'.$jquery;
			return '<script src="'.$jquery.'" type="text/javascript"></script>';	
		}
	}
}

?>
