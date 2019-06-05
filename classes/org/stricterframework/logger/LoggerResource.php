<?php

class LoggerResource implements Resource, LoggerInterface
{
    const COLOR_RED_BOLD = "\033[31;01m";   
    const COLOR_RED = "\033[31;02m"; 
    const COLOR_GREEN = "\033[32;02m";
    const COLOR_YELLOW = "\033[33;02m";
    const COLOR_YELLOW_BOLD = "\033[33;01m";
    const COLOR_BLUE = "\033[34;02m";
    const COLOR_PURPLE= "\033[35;02m";
    const COLOR_CYAN= "\033[36;02m";
    const COLOR_WHITE = "\033[37;02m";
    const COLOR_END = "\033[m";

	public $config=array();
	private $colors=false;
	private $email;
	private $level;
	private $type='file';
	private $errorLogType=3; // file

	function __construct(&$config) {
		$this->config=&$config;

		if($config['colors']===true)
			$this->colors=true;

		if(isset($config['level']))
			$this->level=$config['level'];

		$this->errorLogType=3;

		if(isset($config['type']))
			$this->setType($config['type']);
		else
			$this->setType('file');

		Stricter::getInstance()->setDefaultLogger($this);
	}

	function debug($msg, $mlevel=E_ALL) {
		$this->dolog($msg, $mlevel=E_ALL);
	}
	function info($msg) {
		$this->dolog($msg, $mlevel=E_NOTICE);
	}
	function warn($msg) {
		$this->dolog($msg, $mlevel=E_WARNING);
	}
	function error($msg) {
		$this->dolog($msg, $mlevel=E_ERROR);
	}

	function log($msg, $level=E_WARNING) {
		if($level && $level <= $this->level) {
			switch($level) {
				case E_ERROR:
				case E_USER_ERROR:
					$this->error($msg); break;
				case E_WARNING:
				case E_USER_WARNING:
					$this->warn($msg); break;
				case E_NOTICE:
				case E_USER_NOTICE:
					$this->info($msg); break;
			}
		}
	}

	private function dolog($message, $mlevel, $dotrace=false){
		if($mlevel <= $this->level){
			if($this->type=="file")
				$logdir = $this->config['file']['dir'];

			if($this->colors) {
				$cerror = LoggerResource::COLOR_RED;
				$cwarn = LoggerResource::COLOR_YELLOW;
				$cnotice = LoggerResource::COLOR_CYAN;
				$cend = LoggerResource::COLOR_END;
			}

			$date=date("Y-m-d H:i:s");
			if(isset($_SERVER['HTTP_REFERER']))
				$referer=$_SERVER['HTTP_REFERER'];
			$uri=$_SERVER['REQUEST_URI'];
			$user_agent=$_SERVER['HTTP_USER_AGENT'];
			$remote_addr=$_SERVER['REMOTE_ADDR'];
			$host=gethostname();

			$trace=null;
			if($dotrace || $this->config['trace']===true) {
				$ar = debug_backtrace();
				foreach($ar as $k=>$v)
					if($ar[$k]["file"] && $ar[$k]["function"]!="")
						$trace .= '    on function '.$ar[$k]["function"].' at '.$ar[$k]["file"].':'.$ar[$k]["line"]."\n";
				$trace = substr($trace, 0, -1);
			}
			
			if($this->type=="database") {
				$db = Stricter::getInstance()->inject($this->config['database']['resource']);
				if($this->config['database']['tablespace'])
					$tablespace=$this->config['database']['tablespace'].'.';
				$table=$this->config['database']['table'];
				$params=array($host, $uri, $referer, $user_agent, $message, $date, $trace, $mlevel);
				$sql = "INSERT INTO $tablespace$table (host, uri, referer, user_agent, message, date, trace, level)
						VALUES ($1, $2, $3, $4, $5, $6, $7, $8)";
				$conn=$db->connect();
				if($conn==null)
					return;
				$db->execute($sql, $params);
			} else {

				$fmessage = $date." ";

				switch($mlevel){
					case E_ERROR: $fmessage.=$cerror."ERROR: ".$cend; break;
					case E_WARNING: $fmessage.=$cwarn."WARNING: ".$cend; break;
					case E_NOTICE: $fmessage.=$cnotice."NOTICE: ".$cend; break;
					case E_USER_ERROR: $fmessage.=$cerror."USER ERROR: ".$cend; break;
					case E_USER_WARNING: $fmessage.=$cwarn."USER WARNING: ".$cend; break;
					case E_USER_NOTICE: $fmessage.=$cnotice."USER NOTICE: ".$cend; break;
				}

				$fmessage .= $message;

				if($trace)
					$fmessage .= "\n$trace";
				$fmessage .= "\n  URI: $uri";
				if($referer)
					$fmessage .= "\n  Referer: $referer, from remote addr: $remote_addr";
				$fmessage .= "\n  User-Agent: $user_agent\n";

				$datefile = date("Y-m-d");
				error_log($fmessage, $this->errorLogType, $logdir.'/stricter-'.$datefile.'.log');
			}
		}
	}

	function setType($type) {
		switch ($type)
		{
			case "database": 
				$this->type="database";
				break;
			case "server":
				$this->errorLogType=0;
				$this->type="server";
				break;
			default:
				$this->errorLogType=3;
				$this->type='file';
				break;
		}
	}
}

?>
