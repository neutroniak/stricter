<?php

class RedisResource extends Redis implements Resource
{
	private $conn;
	private $debug = false;
	private $connected = false;
	private $dbhost;
	private $dbuser;
	private $dbpass;
	private $dbname;
	private $dbcase;
	private $dbport;
	private $redis;

	function __construct(&$config, $c) {

		parent::__construct();

		$this->dbhost = $config['host'];
		$this->dbuser = $config['user'];
		$this->dbpass = $config['password'];
		$this->dbname = $config['name'];
		$this->dbport = $config['port'];

		if(!$this->dbport)
			$this->dbport=6379;
		$this->connect($this->dbhost,$this->dbport);
	}
}

?>
