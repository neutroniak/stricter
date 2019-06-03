<?php

class RedisResource implements Resource, DatabaseInterface
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

	function __construct(&$config) {
		$this->dbhost = $config['host'];
		$this->dbuser = $config['user'];
		$this->dbpass = $config['password'];
		$this->dbname = $config['name'];
		$this->dbport = $config['port'];

		$this->redis = new Redis();

		if(!$this->dbport)
			$this->dbport=6379;

		$this->redis->connect($this->dbhost, $this->dbport);
	}

	function connect() {
		
	}

	function execute($sql, $params) {

	}
	
	function query($sql) {
		$this->redis->lpush('mylist', $sql);	

	}

	function numrows(&$resource) {

	}
	
	function fetch(&$query, $sql_assoc=Database::STRICTER_DB_SQL_ASSOC)	{
		$v = $this->redis->get('mykey');	
	}

	function fetchAll(&$query, $sql_assoc=Database::STRICTER_DB_SQL_ASSOC)	{

	}

	function free(&$query) {
		
	}

	function lastInsertId($entity) {
		
	}

	function disconnect() {
		
	}

	function escapeString($string_val) {
	}

	function error() {
		
	}

	function transaction() {
		
	}

	function commit() {
		
	}
	
	function rollback() {
		
	}

	function formatField($field) {
	
	}

	function paginate(&$query, $limit, $offset) {
	}

	function setDebug($dbg) {
		$this->debug=$dbg;
	}

	function setDbPort($port) {
		$this->port=$port;
	}

	function getDbCase() {
		return $this->dbcase;
	}

	function getDbType() {
		return $this->dbtype;
	}
	function getSqlStatement() {
		return $this->sqlStatement;
	}

	function isConnected() {
		return $this->connected;
	}
}

?>
