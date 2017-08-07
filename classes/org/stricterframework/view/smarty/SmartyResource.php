<?php

require_once('Smarty.class.php');

class SmartyResource extends Smarty implements Resource, ViewHandler
{
	public $config=array();
	private $display='index.tpl';
	private $theme;
	private $template;
	private $cacheId=null;
	private $version;

	function __construct(&$config, $c){

		parent::__construct();

		$this->config=&$config;

		$themeDefaultDir= Stricter::getInstance()->getConfig('themes_dir').DIRECTORY_SEPARATOR.Stricter::getInstance()->getConfig('theme').DIRECTORY_SEPARATOR;

		if($this->_version){
			$this->version=preg_replace('/[^0-9\.]/','',$this->_version);
			$config['templateDir'] ? $this->template_dir=$config['templateDir'] : $this->template_dir=$themeDefaultDir.'templates';
			$config['compileDir'] ? $this->compile_dir=$config['compileDir'] : $this->compile_dir=$themeDefaultDir.'templates_c';
			$config['configDir'] ? $this->config_dir=$config['configDir'] : $this->config_dir=$themeDefaultDir.'config';
			$config['cacheDir'] ? $this->cache_dir=$config['cacheDir'] : $this->cache_dir=$themeDefaultDir.'cache';
		} else {
			$this->version=self::SMARTY_VERSION;
			$this->version=preg_replace('/[^0-9\.]/','',self::SMARTY_VERSION);
			$config['templateDir'] ? $this->setTemplateDir($config['templateDir']) : $this->setTemplateDir($themeDefaultDir.'templates');
			$config['compileDir'] ? $this->setCompileDir($config['compileDir']) : $this->setCompileDir($themeDefaultDir.'templates_c');
			$config['configDir'] ? $this->setConfigDir($config['configDir']) : $this->setConfigDir($themeDefaultDir.'config');
			$config['cacheDir'] ? $this->setCacheDir($config['cacheDir']): $this->setCacheDir($themeDefaultDir.'cache');
		}
		$stricter=&Stricter::getInstance();
		$cfg = $stricter->getConfig();
		if($cfg['desenv']===false) {
			$this->force_compile=false;
			$this->compile_check=false;
		} else {
			$this->compile_check=true;
		}
		$this->assign('stricter',$cfg);
	}

	public function preloadPlugins(){
		if($this->config['preloadPlugins']) {
			foreach($this->config['preloadPlugins'] as $kp=>$vp)
				$this->addPlugin($vp);
		}
	}

	public function addPlugin($strcomponent){
		try{
			require_once('org/stricterframework/view/smarty/plugins/BasicPlugin.php');
			require_once("org/stricterframework/view/smarty/plugins/".$strcomponent."Plugin.php");
			$formclassname = $strcomponent.'Plugin';
			$forminstance = new $formclassname();
			$forminstance->smarty=&$this;
			$forminstance->stricter=&Stricter::getInstance();
			$forminstance->__init();
		} catch (Exception $ex){
			Stricter::getInstance()->log("SmartyResource: Could not load plugin: ".$strcomponent);
		}
	}

	function cache($lifetime=null, $aparams=null){
		if($lifetime===null && $this->config['cacheLifetime'])
			$lifetime=$this->config['cacheLifetime'];
		if($aparams==null)
			$aparams=$_GET;

		$this->cache_lifetime=$lifetime;
		$this->caching=2;
		$this->cacheId=sha1(serialize($aparams));

		if($this->version>3)
			$isCached = $this->isCached($this->display, $this->cacheId);
		else
			$isCached = $this->is_cached($this->display, $this->cacheId);

		if($isCached) {
			$this->compile_check=false;
			$this->force_compile=false;
			return true;
		} else {
			$this->compile_check=true;
			return false;
		}
	}

	public function output(){
		$this->assign('template',$this->template);
		$this->display($this->display, $this->cacheId);
	}

	public function getDisplay() {return $this->display;}
	public function setDisplay($display, $type=Stricter::OUT_HTML){
		$this->display=$display.'.tpl';
		Stricter::getInstance()->setContentType($type);
		if($type===Stricter::OUT_AJAX && strtolower(Stricter::getInstance()->getConfig('charset'))!='utf-8')
			array_walk_recursive($_POST,array(&$this, 'iconvAjax'));
	}
	public function getTemplate() { return $this->template; }
	public function setTemplate($template) { $this->template=$template.'.tpl'; }
	public function getCacheId() {return $this->cacheId;}
	public function getTheme() { return $this->theme; }
	public function setTheme($theme) { $this->theme=$theme; }
	public function fetchTemplate($val) { return $this->fetch($val.'.tpl'); }

    public function select($varname, $sql, $is_html_options=false, $sql_assoc=DatabaseInterface::STRICTER_DB_SQL_ASSOC, &$dbinstance=null) {
		if($dbinstance==null) {
			$db=Stricter::getInstance()->getDefaultDatabaseId();
			$dbi=Stricter::getInstance()->inject($db);
		}	
		$smarty_array = array();

		$query = $dbi->query($sql);

		$numrows = $dbi->numrows($query);

		if($is_html_options == true)
			while($r = $dbi->fetch($query, DatabaseInterface::STRICTER_DB_SQL_NUM))
				$smarty_array[$r[0]]= $r[1];
		else
			while($r = $dbi->fetch($query, $sql_assoc))
				array_push($smarty_array, $r);

		if($dbi->getDbCase()!==null && $is_html_options===false)
			foreach($smarty_array as $k=>$v)
				$smarty_array[$k] = array_change_key_case($smarty_array[$k], $dbi->getDbCase());
		$this->assign($varname, $smarty_array);

		$dbi->free($query);

		$realn = count($smarty_array);

		if($realn==0)
			$this->assign($varname, null);

		return $numrows;
	}

	public function paginate(&$query, $limit, $currentPage=null, $db=null) {
		$this->addPlugin("Pager");
		$isgetpg=null;
		if(!$currentPage){
			$isgetpg=true;
			$currentPage=$_GET['pg']+0;
		}else{
			$isgetpg=false;
		}
		if($db)
			$dbi =& $db;
		else
			$dbi=Stricter::getInstance()->inject( Stricter::getInstance()->getDefaultDatabaseId() );

		$offset=$currentPage*$limit;
		$q=$dbi->query($query);
		$n=$dbi->numrows($q);
		$dbi->paginate($query, $limit+0, $offset+0);
		$toceil=$n/$limit;
		$pages = ceil($toceil)+1;
		$currentPage++;
		$this->assign('_pager', array('length'=>$pages-1,'count'=>$pages,'limit'=>$limit,'offset'=>$offset,'current'=>$currentPage,'total'=>$n,'isgetpg'=>$isgetpg, 'url'=>$_SERVER['SCRIPT_URL']));
		return $query;
	}

	private function iconvAjax(&$v ,$k){ 
		$v=iconv("UTF-8", $this->config['charset'], $v);
	}

	//for Smarty v2 compatibility
	function __call($name, $args){
		switch ($name) {
		case "registerPlugin":
			if($args[0]=="function")
				$this->register_function($args[1], $args[2]);
			else if($args[0]=="modifier")
				$this->register_modifier($args[1], $args[2]);
			else if($args[0]=="block")
				$this->register_block($args[1], $args[2]);
		break;
		case "clearAllCache":
			$this->clear_cache();
		break;
		case "getTemplateVars":
			return $this->_tpl_vars[$args[0]];
		break;
		default:
			Stricter::getInstance()->log("Unhandled Smarty method: $name");
		break;
		}
	}
}

?>
