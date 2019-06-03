<?php

class ToolbarPlugin extends BasicPlugin
{
	public $smarty;
	private $objvar;
	private $params;
	private $theme;

	function __init()
	{
		$this->smarty->registerPlugin("function", "toolbar", array(&$this,"toolbar"));

		$this->addAttribute( 'tools' );

		$this->theme=Stricter::getInstance()->getConfig('theme');
	}

	function toolbar($params, $smarty)
	{
		parent::init($params, $objvar);
		$str="";
		if($params['tools']) {
			$ex=explode(',',$params['tools']);
			foreach($ex as $k=>$v) {
				switch(trim($v)) {
					case 'hamburger': $str.=$this->hamburger(); break;
					case 'user': $str.=$this->user($params); break;
					case 'search': $str.=$this->search(); break;
					case 'path': $str.=$this->path($params, $smarty); break;
					case 'help': $str.=$this->help(); break;
					case 'alert': $str.=$this->alert(); break;
					case 'search': $str.=$this->search(); break;
					case 'logo': $str.=$this->logo(); break;
				}
			}
		}

		return '<div class="'.$params['class'].'">'.$str.'</div>';
	}

	private function hamburger() {
		return '<img class="st-toolbar-hamburger" src="/themes/'.$this->theme.'/images/icons/hamburger.svg" />';
	}
	private function user(&$params) {
		return '<div class="st-toolbar-user">
			<a href="/user" class="st-ajaxlink"><div class="st-toolbar-user-name">Hi, '.$params['user'].'</div></a>
			<a href="/user" class="st-ajaxlink"><img class="st-toolbar-user-icon" src="/themes/'.$this->theme.'/images/icons/user.svg" /></a>
			<div style="clear:both;"></div>
		</div>';
	}
	private function search() {
		return '<div><img class="st-toolbar-alert" src="/themes/'.$this->theme.'/images/icons/search.svg" /></div>';
	}
	private function path(&$params, $smarty) {
	}
	private function logo() {
		return '<a class="st-ajaxlink" href="/home"><img class="st-toolbar-logo" src="/themes/'.$this->theme.'/images/logo.svg" /></a>';
	}
	private function help() {
		return '<img class="st-toolbar-help" src="/themes/'.$this->theme.'/images/icons/help.svg" />';
	}
	private function alert() {
		return '<img class="st-toolbar-alert" src="/themes/'.$this->theme.'/images/icons/alert.svg" />';
	}
}

?>
