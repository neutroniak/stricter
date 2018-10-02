<?php

class MysqlResource implements Resource, DatabaseInterface
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
	private $sqlStatement;

	function __construct(&$config) {
		$this->dbhost = $config['host'];
		$this->dbuser = $config['user'];
		$this->dbpass = $config['password'];
		$this->dbname = $config['name'];
		$this->dbport = $config['port'];

		if(!$this->dbport)
			$this->dbport=3306;

		$this->connect();
	}

	function connect() {
		$conn = mysql_connect($this->dbhost.':'.$this->dbport, $this->dbuser, $this->dbpass);

		mysql_select_db($this->dbname, $conn);

		$this->conn =& $conn;

		if(!$this->conn)
			$this->connected = false;
		else
			$this->connected = true;

		return $conn;
	}

function execute($sql, $params) {

}
	function query($sql) {
		$this->sqlStatement = $sql;

		if($sql == "")
			return false;

		$q = mysql_query($sql, $this->conn);

		if(!$q) {
			Stricter::getInstance()->log("Database::query ".mysql_error()." on query:\n $sql", E_ERROR);
			return false;
		} else {
			return $q;
		}
	}
	
	function numrows(&$resource) {
		$n = mysql_num_rows($resource);
		
		return $n;
	}
	
	function fetch(&$query, $sql_assoc=Database::STRICTER_DB_SQL_ASSOC)	{
		$r = mysql_fetch_array($query, $sql_assoc);
		
		return $r;
	}

	function free(&$query) {
		mysql_free_result($query);
	}

	function lastInsertId($entity) {
		$sql_last_insert = "SELECT LAST_INSERT_ID() lid";

		$q = $this->query($sql_last_insert);

		$r = $this->fetch($q);
		
		return $r["lid"];
	}

	function disconnect() {
		mysql_close($this->conn);
	}

	function escapeString($string_val) {
		$magic_quotes = ini_get('magic_quotes_gpc');

		if($magic_quotes==0 || !$magic_quotes)
			$string_val = mysql_real_escape_string($string_val);

		return $string_val;
	}

	function error() {
		if(mysql_error())
			return mysql_error();
		else
			return false;
	}

	function transaction() {
		$this->query("SET AUTOCOMMIT = 0;");
		
		$this->query("START TRANSACTION;");
	}

	function commit() {
		$this->query("COMMIT;");
	}
	
	function rollback() {
		$this->query("ROLLBACK;");
	}

	function formatField($field) {
		$type=$field->getType();

		if($field->getValue()===null) {
			if($field->getRequired()===false)
				return 'NULL';
			else {
				switch ( $type ) {
					case 'IntegerType':return "0";  break;
					case 'NumericType':return "0";  break;
					case 'BooleanType':return "0"; break;
					case 'StringType':return "''"; break;
				}
			}
		} else {
			switch ( $type ) {
				case 'DateField':
					$dt = date('Y-m-d h:i:s', $field->getValue());
					return "'".$dt."'";
					break;

				case 'BinaryType':
					return "'".$bytea."'";
				break;

				case 'IntegerType':
					return $field->getValue();
					break;

				case 'NumericType':
						return $field->getValue();
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
		return $this->connected;
	}
}

?>
