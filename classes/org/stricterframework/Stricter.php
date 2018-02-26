<?php

class Stricter
{
	const OUT_HTML = 'text/html';
	const OUT_AJAX = 'application/xhtml+xml';
	const OUT_XML = 'text/xml';
	const OUT_JSON = 'application/json';
	const OUT_PLAIN = 'text/plain';
	const OUT_XLS = 'application/xls';

	private $contentType=self::OUT_HTML;
	private $requestMethod;
	private $config;
	private $routes;
	private $resources;
	private $resourceObjects=array();
	private $defaultDatabaseId;
	private $viewHandler;
	private $action='index';
	private $mdl;
	private static $instance;

	public function Stricter(){

		include('config.php');

		$this->config=&$config;
		$this->resources=&$resource;
		$this->routes=&$routes;

		if(file_exists('routes.php'))
			include('routes.php');

		if(!$this->config['webpath'])
			$this->config['webpath'] = str_replace('/index.php','',$_SERVER['SCRIPT_NAME']);

		if(!$this->config['site_dir'])
			$this->config['site_dir'] = realpath('.'); 

		if(!$this->config['themes_dir'])
			$this->config['themes_dir'] = $this->config['site_dir'].DIRECTORY_SEPARATOR.'themes';

		if(!$this->config['languages_dir'])
			$this->config['languages_dir'] = $this->config['site_dir'].DIRECTORY_SEPARATOR.'languages';

		$newini = $this->config['site_dir'].DIRECTORY_SEPARATOR.'classes'.PATH_SEPARATOR.
				dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.PATH_SEPARATOR.
				$this->config['include_dirs'].PATH_SEPARATOR;

		set_include_path($newini);

		ini_set("date.timezone", $this->config['timezone']);

		setlocale(LC_ALL, $this->config['locale']);
	
		Stricter::$instance=&$this;

		set_error_handler( array($this, 'setErrorHandler') );

		set_exception_handler( array($this, 'setExceptionHandler') );
	}

	public function inject($name){
		if(!$name)
			return;
		if($this->resourceObjects[$name]) {
			return $this->resourceObjects[$name];
		} else {
			require_once( $this->resources[$name]['class'].'.php' );
			$str = substr( strrchr($this->resources[$name]['class'], DIRECTORY_SEPARATOR), 1);
			$res = new $str( $this->resources[$name], $name );

			$realres =null;

			if($res instanceof ResourceProxy)
				$realres = $res->getRealObject();
			else
				$realres =& $res;

			$this->resourceObjects[$name]=&$realres;
			if($realres instanceof ViewHandler)
				$this->viewHandler=$name;
			if($realres instanceof DatabaseInterface && !$this->defaultDatabaseId)
				$this->defaultDatabaseId=$name;

			return $realres;
		}
	}

	public function dispatch(){
		$ex=explode('/',$_GET['mdl']);

		$path=null;
		$params=array();

		foreach($ex as $k=>$v) {
			if($abs==1 || preg_match('/[^a-zA-Z\-]/',$v)) {
				array_push($params,$v);
			} else {
				$path.='/'.$v;
				if($this->routes[$path][3]==true)
					$abs=1;
			}
		}
		if($path!='/' && (strrpos($path,'/')==strlen($path)-1))
			$path=substr($path,0,-1);

		$sobj = substr( strrchr($this->routes[$path][0], DIRECTORY_SEPARATOR), 1);

		if(!$sobj){ # TODO - implement auto routes
			$this->callError("LANG_PAGE_NOT_FOUND",  404);
			return;
		}

		$viewHandler =& $this->resourceObjects[$this->viewHandler];
		$tplName=null;
		$tplBase=null;
		if($this->routes[$path][2])
			$tplName='/'.$this->routes[$path][2];
		else if($this->routes[$path][1]=="index")
			$tplName = $path.'/index';
		else
			$tplName = $path;
		$tplBase = substr( $tplName, 1, strrpos($tplName,'/')-1 );
		$tplName = substr($tplName,1);
		$inc = include_once($this->routes[$path][0].'.php');

		$this->action=$this->routes[$path][1];

		if($inc) {

			include_once($this->config['languages_dir'].'/'.$this->config['locale'].'.'.$this->config['charset'].'/index.php'); #NLS common (required)

			$obj = new $sobj();
			$obj->stricter =& $this;
			if($viewHandler){
				$obj->view=&$viewHandler;
				if($obj->view->getTemplate()=="")
					$obj->view->setTemplate($tplName);
				$obj->view->assign('mdl',$tplBase);
			}
			
			$this->mdl=$tplBase;

			$obj->params=&$params; # send parameters to controller

			if(method_exists($obj, '__init'))
				$obj->__init();

			call_user_func( array(&$obj, $this->routes[$path][1]) );
		} else {
			$this->callError("LANG_PAGE_NOT_FOUND",  404);
		}
	}

	public function setExceptionHandler($errstr){
		$inst = Stricter::getInstance();
		$inst->log($errstr);
		return true;
	}

	public function setErrorHandler($errno, $errstr){
		$inst = Stricter::getInstance();
		$inst->log($errstr, $errno);
		return true;
	}

	public function output(){
		header('Content-type:'.$this->contentType.'; charset='.$this->config['charset'] );
		ob_clean();
		ob_start();
		if($this->viewHandler){
			$vw =& $this->viewHandler;
			$this->resourceObjects[$vw]->setTheme($this->config['theme']);
			$this->resourceObjects[$vw]->assign('template', $this->resourceObjects[$vw]->getTemplate() );
			$this->resourceObjects[$vw]->output();
		}
	}

	public function callError($code, $httpCode){
		include_once("org/stricterframework/http/HttpStatus.php");
		if($httpCode!=null)
			header(constant('HttpStatus::HTTP_'.$httpCode));
		$vw =& $this->resourceObjects[$this->viewHandler];
		$vw->assign('errmsg', $code);
		$vw->setDisplay('error');
	}

	public function session($sessName){
		session_name($sessName);
		session_start();
		if($_SESSION[$sessName] && $_SESSION[$sessName]!='')
			return true;
		else
			return false;
	}

	public function log($message, $mlevel=E_WARNING, $logdir=null, $dotrace=false){
		if(!$logdir)
			$logdir = $this->config['log_dir'];

		if($mlevel <= $this->config['log_level']) 
		{
			$datefile = date("Y-m-d");

			switch($mlevel){
				case E_ERROR: $fmessage="ERROR: "; break;
				case E_WARNING: $fmessage="WARNING: "; break;
				case E_NOTICE: $fmessage="NOTICE: "; break;
				case E_USER_ERROR: $fmessage="USER ERROR: "; break;
				case E_USER_WARNING: $fmessage="USER WARNING: "; break;
				case E_USER_NOTICE: $fmessage="USER NOTICE: "; break;
			}

			$fmessage.=date("Y-m-d H:i:s");

			$fmessage .= ' '.$message;
			$trace=null;
			if($dotrace || $this->config['log_trace']===true) {
				$ar = debug_backtrace();
				foreach($ar as $k=>$v)
					if($ar[$k]["file"] && $ar[$k]["function"]!="")
						$trace .= '     on function '.$ar[$k]["function"].' at '.$ar[$k]["file"].':'.$ar[$k]["line"]."\n";

			}
			$trace .= '  URI: '.$_SERVER["REQUEST_URI"]."\n";
			if(isset($_SERVER['HTTP_REFERER']))
				$trace .= '  Referer: '.$_SERVER["HTTP_REFERER"].', from remote addr: '.$_SERVER["REMOTE_ADDR"]."\n";
			$trace .= '  User-Agent: '.$_SERVER["HTTP_USER_AGENT"]."\n";
			error_log($fmessage.$trace, 3, $logdir.'/stricter-'.$datefile.'.log');
		}
	}

	public function redirect($location, $full=false){
		ob_end_clean();
		if($full===true)
			header("Location: ".$location);
		else
			header("Location: ".$this->getConfig('webpath').$location);
		exit;
	}

	public function requireHttps($istrue){		
		$url = null;
		
		$requestURI = str_replace($this->config['webpath'], '', $_SERVER['REQUEST_URI']);

		if($istrue==true && $_SERVER['HTTPS']!="on") {
			if ($this->config['https_url'])
				$url = $this->config['https_url'].$requestURI;
			else
				$url = "https://" . $_SERVER['SERVER_NAME'] . $requestURI;
			self::redirect($url, true);
		} else if($istrue===false && $_SERVER['HTTPS']) {
			if($this->port != 80)
				$port = $this->port;
			else
				$port = 80;

			if($this->config['http_url'])
				$url = $this->config['http_url'].$requestURI;
			else
				$url ="http://" . $_SERVER['SERVER_NAME'] .":$port". $requestURI;
			
			self::redirect($url, true);
		}
	}

	public function isPost(){
		$_SERVER['REQUEST_METHOD']==="POST" ? $ret=true : $ret=false;
		return $ret;
	}

	public static function getInstance() {return Stricter::$instance;}
	public function getContentType() { return $this->contentType; }
	public function setContentType($val) { $this->contentType = $val; }
	public function getConfig($item=null) { if($item) return $this->config[$item];else return $this->config; }
	public function setConfig($item, $itemval) { $this->config[$item]=$itemval; }
	public function getAction() { return $this->action; }
	public function getMdl() { return $this->mdl; }
	public function getViewHandler(){return $this->viewHandler;}
	public function getDefaultDatabaseId(){return $this->defaultDatabaseId;}
	public function version(){return self::VERSION_MAJOR.'.'.self::VERSION_MINOR.'.'.self::VERSION_PATCH;}
}

//interfaces

interface Controller
{
	public function index();
}

interface Resource
{

}

interface ResourceProxy
{
	function getRealObject();
}

interface ViewHandler
{
	function output();
	function assign($var,$val=null);
	function setDisplay($val,$out=Stricter::OUT_HTML);
	function getDisplay();
	function setTemplate($val);
	function getTemplate();
	function setTheme($val);
	function getTheme();
	function fetchTemplate($tpl);
}

?>
