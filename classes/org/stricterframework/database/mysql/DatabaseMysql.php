<?php

class DatabaseMysql implements DatabaseInterface
{
	private $conn;
	private $debug = false;
	private $connected;
	private $dbhost;
	private $dbuser;
	private $dbpass;
	private $dbname;
	private $dbcase;
	private $dbport;
	private $dbtype;
	private $sqlStatement;

	function __construct($dbhost, $dbuser, $dbpass, $dbname)
	{
		$this->dbhost = $dbhost;
		$this->dbuser = $dbuser;
		$this->dbpass = $dbpass;
		$this->dbname = $dbname;
		$this->dbport = 3306;
		$this->dbtype = 'mysql';
	}

	function connect()
	{
		$conn = mysql_connect($this->dbhost.':'.$this->dbport, $this->dbuser, $this->dbpass);

		mysql_select_db($this->dbname, $conn);

		$this->conn =& $conn;

		if(!$this->conn)
			$this->connected = false;
		else
			$this->connected = true;

		return $conn;
	}
	
	function query($sql)
	{
		$this->sqlStatement = $sql;

		if($sql == "")
			return false;

		$q = mysql_query($sql, $this->conn);

		if($this->error()) {
			Stricter::log("Database::query ".$this->error()." on query:\n $sql", E_ERROR);
		}

		if(!$q)
			return false;
		else
			return $q;
	}
	
	function numrows(&$resource)
	{
		$n = mysql_num_rows($resource);
		
		return $n;
	}
	
	function fetch(&$query, $sql_assoc=Database::STRICTER_DB_SQL_ASSOC)
	{
		$r = mysql_fetch_array($query, $sql_assoc);
		
		return $r;
	}

	function free(&$query)
	{
		mysql_free_result($query);
	}

	function lastInsertId($entity)
	{
		$sql_last_insert = "SELECT LAST_INSERT_ID() lid";

		$q = $this->query($sql_last_insert);

		$r = $this->fetch($q);
		
		return $r["lid"];
	}

	function disconnect()
	{
		mysql_close($this->conn);
	}

	function escapeString($string_val)
	{
		$magic_quotes = ini_get('magic_quotes_gpc');

		if($magic_quotes==0 || !$magic_quotes)
			$string_val = mysql_real_escape_string($string_val);

		return $string_val;
	}

	public function error()
	{
		if(mysql_error())
			return mysql_error();
		else
			return false;
	}

	public function transaction()
	{
		$this->query("SET AUTOCOMMIT = 0;");
		
		$this->query("START TRANSACTION;");
	}

	public function commit()
	{
		$this->query("COMMIT;");
	}
	
	public function rollback()
	{
		$this->query("ROLLBACK;");
	}

	function formatField($field)
	{
		$type=get_class($field);

		if($field->getValue()===null) {
			if($field->getRequired()===false)
				return 'NULL';
			else
				return "''";			
		} else {
			switch ( $type ) {
				case 'DateField':
					$dt = date('Y-m-d h:i:s', $field->getValue());
					return "'".$dt."'";
				break;

				default:
					return "'".addslashes($field->getValue())."'";
				break;
			}			
		}
	}

	function paginate(&$query, $limit, $offset) {
		$query .= " LIMIT $limit OFFSET $offset";	
		return $query;
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
		return $connected;
	}
}

?>
