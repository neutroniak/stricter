<?php

class DatabaseResource implements ResourceProxy
{
	public $configName;
	private $stricter;
	private $realObject;

	function __construct(&$config, $cfgnm) {
		$this->config=&$config;
		$stricter =& Stricter::getInstance();
		$udbtype = ucwords($this->config['type']);
		include_once("org/stricterframework/database/".$this->config['type']."/Database".$udbtype.".php");
		$cdb_str = "Database".$udbtype;
		$inst = new $cdb_str(
			$this->config['host'], 
			$this->config['user'], 
			$this->config['password'], 
			$this->config['name']
		);
		if($this->config['port'])
			$inst->setDbPort( $this->config['port'] );

		if($config['debug']===true)
			$inst->setDebug(true);

		$inst->connect();

		$this->realObject =& $inst;
	}

	function getRealObject() {
		return $this->realObject;
	}
}

?>
