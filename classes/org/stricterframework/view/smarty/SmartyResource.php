<?php

require_once('Smarty.class.php');

class SmartyResource extends Smarty implements Resource, ViewInterface
{
	public $config=array();
	private $extension;
	private $display;
	private $template;
	private $cacheId=null;
	private $version;

	function __construct(&$config, $c){

		parent::__construct();

		$this->config=&$config;

		$site_dir = Stricter::getInstance()->getConfig('site_dir');

		$this->version=self::SMARTY_VERSION;
		$this->version=preg_replace('/[^0-9\.]/','',self::SMARTY_VERSION);
		$sep=DIRECTORY_SEPARATOR;
		$config['templateDir'] ? $this->setTemplateDir($config['templateDir']) : $this->setTemplateDir($site_dir.$sep.'views'.$sep.'smarty'.$sep.'templates');
		$config['compileDir'] ? $this->setCompileDir($config['compileDir']) : $this->setCompileDir($site_dir.$sep.'views'.$sep.'smarty'.$sep.'templates_c');
		$config['configDir'] ? $this->setConfigDir($config['configDir']) : $this->setConfigDir($site_dir.$sep.'views'.$sep.'smarty'.$sep.'config');
		$config['cacheDir'] ? $this->setCacheDir($config['cacheDir']): $this->setCacheDir($site_dir.$sep.'views'.$sep.'smarty'.$sep.'cache');
		$config['extension'] ? $this->extension=$config['extension']: $this->extension='.tpl';

		$this->display = 'index'.$this->extension;

		if($this->config['preloadPlugins']) 
			foreach($config['preloadPlugins'] as $kp=>$vp)
				$this->addPlugin($vp);

		$cfg = Stricter::getInstance()->getConfig();
		if($cfg['desenv']===false) {
			$this->force_compile=false;
			$this->compile_check=false;
		} else {
			$this->compile_check=true;
		}
		$this->assign('stricter',$cfg);
	}

	public function addPlugin($strcomponent){
		try{
			require_once('org/stricterframework/view/smarty/plugins/BasicPlugin.php');
			require_once("org/stricterframework/view/smarty/plugins/".$strcomponent."Plugin.php");
			if(strrchr($strcomponent,'/'))
				$formclassname = substr(strrchr($strcomponent, '/'), 1).'Plugin';
			else
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
		$this->display=$display.$this->extension;
		Stricter::getInstance()->setContentType($type);
		if($type===Stricter::OUT_AJAX && strtolower(Stricter::getInstance()->getConfig('charset'))!='utf-8')
			array_walk_recursive($_POST,array(&$this, 'iconvAjax'));
	}
	public function getTemplate() { return $this->template; }
	public function setTemplate($template) { $this->template=$template.$this->extension; }
	public function getCacheId() {return $this->cacheId;}
	public function fetchTemplate($val) { return $this->fetch($val.$this->extension); }

    public function select($varname, $sql, $is_html_options=false, $sql_assoc=DatabaseInterface::STRICTER_DB_SQL_ASSOC, &$dbinstance=null) {
		if($dbinstance==null) {
			$dbi=Stricter::getInstance()->getDefaultDatabase();
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
			$dbi=Stricter::getInstance()->inject( Stricter::getInstance()->getDefaultDatabase() );

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
}

?>
