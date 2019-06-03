<?php

class HtmlPlugin extends BasicPlugin
{
	public $smarty;
	private $objvar;
	private $params;
	
	function __init()
	{
		$this->smarty->registerPlugin("block", "html", array(&$this,"html"));

		$this->addAttribute( 'value' );
		$this->addAttribute( 'doctype' );
	}

	function html($params, $content, $smarty)
	{
		$str ='<?xml version="1.0" encoding="UTF-8"?>'."\n";
		$str.='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"'."\n";
		$str.='  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\n";
		$str.='<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">'."\n";
		$str.=$content;
		$str.="\n".'</html>'."\n";
		if($content)
			return $str;
	}
}

?>
