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

interface DatabaseInterface
{
	const STRICTER_DB_SQL_ASSOC = 1;
	const STRICTER_DB_SQL_NUM = 2;
	const STRICTER_DB_SQL_BOTH = 3;
	const STRICTER_DB_CASE_LOWER = 0;
	const STRICTER_DB_CASE_UPPER = 1;

	function connect();
	function query($sql);
	function numrows(&$resource);
	function fetch(&$resource, $sql_assoc=Database::STRICTER_DB_SQL_ASSOC);
	function free(&$resource);
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

?>
