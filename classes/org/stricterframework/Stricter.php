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
	private $defaultDatabase;
	private $defaultLogger;
	private $action='index';
	private $mdl;
	private static $instance;

	public $view;
	public $logger;

	public function __construct(){

		include('config.php');
		include('routes.php');

		$this->config=&$config;
		$this->resources=&$resource;
		$this->routes=&$routes;

		if(!isset($this->config['webpath']))
			$this->config['webpath'] = str_replace('/index.php','',$_SERVER['SCRIPT_NAME']);

		if(!isset($this->config['site_dir']))
			$this->config['site_dir'] = realpath('.'); 

		if(!isset($this->config['theme']))
			$this->config['theme'] = 'default';

		if(!isset($this->config['lang_dir']))
			$this->config['lang_dir'] = $this->config['site_dir'].DIRECTORY_SEPARATOR.'lang';

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
		if(strlen(trim($name))===0) {
			$this->log("Resource with no name: ".$name, E_ERROR);
			return;
		}
		if($this->resourceObjects[$name]) {
			return $this->resourceObjects[$name];
		} else {
			if($this->resources[$name]===null || !is_array($this->resources[$name])) {
				$this->log("Resource ".$name.' cannot be injected. Check config file if the resource exists or is configured correctly.', E_ERROR);
				return null;
			}
			if($this->resources[$name]['class']=="") {
				$this->log("Resource ".$name.' cannot be injected because it does not have a Resource class', E_ERROR);
				return null;
			}
			$checkinc = include_once($this->resources[$name]['class'].'.php') ;
			if($checkinc===false){
				$this->log("Resource ".$this->resources[$name]['class'].' cannot be found on include_path.', E_ERROR);
				return null;
			}
			$str = substr(strrchr($this->resources[$name]['class'], DIRECTORY_SEPARATOR), 1);
			$res = new $str($this->resources[$name], $name);

			$this->resourceObjects[$name]=&$res;

			if($res instanceof ViewInterface && !$this->view) {
				//$this->defaultView=&$res;
				$this->view=&$res;
			}
			if($res instanceof DatabaseInterface && !$this->defaultDatabase)
				$this->defaultDatabase=$res;

			return $res;
		}
	}

	public function dispatch() {
		$getmdl = preg_replace('/[^a-zA-Z0-9-_\/\.]/','', $_GET['mdl']);

		$ex = explode('/', $getmdl);

		$path=null;
		$params=array();

		foreach($ex as $k=>$v) {
			if($abs==1 || preg_match('/[^a-zA-Z\-_\.]/', $v)){
				array_push($params, $v);
			} else {
				$path.='/'.$v;
				if($this->routes[$path][3]==true)
					$abs=1;
			}
		}

		if($path!='/' && (strrpos($path,'/')==strlen($path)-1))
			$path=substr($path,0,-1);

		$sobj = substr(strrchr($this->routes[$path][0], DIRECTORY_SEPARATOR), 1);

		if(!$sobj){
			$this->callError("LANG_PAGE_NOT_FOUND",  404);
			return;
		}

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

		if($inc){
			include_once($this->config['lang_dir'].'/'.$this->config['locale'].'.'.$this->config['charset'].'/index.php'); #NLS common (required)

			$obj = new $sobj();
			$obj->stricter =& $this;
			if($this->view){
				if($this->view->getTemplate()=="")
					$this->view->setTemplate($tplName);
				$this->view->assign('mdl', $tplBase);
			}

			$this->mdl=$tplBase;

			if($this->defaultLogger) {
				$obj->stricter->logger=&$this->defaultLogger;
			}
			if(method_exists($obj, '__init'))
				$obj->__init();

			call_user_func_array( array(&$obj, $this->routes[$path][1]), $params);
			header('Content-type:'.$this->contentType.'; charset='.$this->config['charset']);

			$this->view->assign('template', $this->view->getTemplate() );

			ob_clean();
			ob_start();

			$this->view->output();
		} else {
			$this->callError("LANG_PAGE_NOT_FOUND", 404);
		}
	}

	public function setExceptionHandler($errstr){
		$this->log($errstr);
	}

	public function setErrorHandler($errno, $errstr){
		$this->log($errstr, $errno);
	}

	public function callError($code, $httpCode){
		include_once("org/stricterframework/http/HttpStatus.php");
		if($httpCode!=null)
			header(constant('HttpStatus::HTTP_'.$httpCode));
		$this->view->assign('errmsg', $code);
		$this->view->setDisplay('error');
	}

	public function session($sessName, $lifetime=null){
		session_name($sessName);
		session_start();
		if($_SESSION[$sessName] && $_SESSION[$sessName]!='')
			return true;
		else
			return false;
	}

	public function log($message, $mlevel=E_WARNING, $logdir=null, $dotrace=false){
		if($this->defaultLogger) {
			$this->defaultLogger->log($message, $mlevel);
			return;
		}

		if(!$logdir)
			$logdir = $this->config['log_dir'];

		$fmessage="";

		if($mlevel <= $this->config['log_level']){
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
			if($dotrace || isset($this->config['log_trace'])===true) {
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
	public function getDefaultDatabase(){return $this->defaultDatabase;}
	public function setDefaultLogger(&$logger){
		if($this->defaultLogger==null) {
			$this->defaultLogger=&$logger;
			// replace the error default error handlers defined on constructor
			set_error_handler( array($logger, 'log'));
			set_exception_handler( array($logger, 'log') );
		}
	}
	public function version(){return self::VERSION_MAJOR.'.'.self::VERSION_MINOR.'.'.self::VERSION_PATCH;}
}

// interfaces
interface Controller {
}

interface Resource {
}

interface LoggerInterface {
	function debug($msg);
	function info($msg);
	function warn($msg);
	function error($msg);
	function log($msg, $level);
}

interface ViewInterface {
	function output();
	function assign($var,$val=null);
	function setDisplay($val,$out=Stricter::OUT_HTML);
	function getDisplay();
	function setTemplate($val);
	function getTemplate();
	function fetchTemplate($tpl);
}

interface DatabaseInterface {
	const STRICTER_DB_SQL_ASSOC = 1;
	const STRICTER_DB_SQL_NUM = 2;
	const STRICTER_DB_SQL_BOTH = 3;
	const STRICTER_DB_CASE_LOWER = 0;
	const STRICTER_DB_CASE_UPPER = 1;

	function connect();
	function query($sql);
	function numrows(&$resource);
	function fetch(&$resource, $sql_assoc=Database::STRICTER_DB_SQL_ASSOC);
	function fetchAll(&$resource, $sql_assoc=Database::STRICTER_DB_SQL_ASSOC);
	function free(&$resource);
	function execute($query, $params);
	function disconnect();
	function error();
	function setDebug($dbg);
	function transaction();
	function commit();
	function rollback();
	function lastInsertId($entity);
	function escapeString($string_val);
	function getDbType();
	function getSqlStatement();
	function isConnected();
	function formatField($field);
	function getDbCase();
	function paginate(&$query,$limit,$offset);
}

interface ModelInterface {
	public function init();
}

?>
