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
		$this->debug = $config['debug'];

		if(!$this->dbport)
			$this->dbport=3306;

		$this->connect();
	}

	function connect() {
		$conn = mysqli_connect($this->dbhost.':'.$this->dbport, $this->dbuser, $this->dbpass);

		mysqli_select_db($conn, $this->dbname);

		$this->conn =& $conn;

		if(!$this->conn)
			$this->connected = false;
		else
			$this->connected = true;

		return $conn;
	}

	function execute($sql, $params) {
		if($sql == "")
			return false;

		$q = mysqli_prepare($this->conn, $sql);
		$types=str_pad('',count($params),'s');
		$refarg=array($q,$types);
		foreach ($params as $key => $value) {
			$refarg[] =& $params[$key];
		}
		call_user_func_array('mysqli_stmt_bind_param', $refarg);
		mysqli_stmt_execute($q);

		if($this->debug===true)
			Stricter::getInstance()->log("Database::query ".$this->error()." on query:\n $sql", E_INFO, null, true);

		if($this->error()) {
			Stricter::getInstance()->log("Database::error ".$this->error()." on query:\n $sql", E_ERROR);
		}

		if(!$q)
			return false;
		else
			return $q;
	}
	
	function query($sql) {
		$this->sqlStatement = $sql;

		if($sql == "")
			return false;

		$q = mysqli_query($this->conn, $sql);

		if(!$q) {
			Stricter::getInstance()->log("Database::query ".mysqli_error()." on query:\n $sql", E_ERROR);
			return false;
		} else {
			return $q;
		}
	}

	function numrows(&$resource) {
		$n = mysqli_num_rows($resource);
		
		return $n;
	}
	
	function fetch(&$query, $sql_assoc=Database::STRICTER_DB_SQL_ASSOC)	{
		$r = mysqli_fetch_array($query, $sql_assoc);
		
		return $r;
	}

	function fetchAll(&$query, $sql_assoc=DatabaseInterface::STRICTER_DB_SQL_ASSOC) {
		$arr = array();

		while($r = mysqli_fetch_array($query, $sql_assoc)) {
			array_push($arr, $r);
		}
				
		return $arr;
	}

	function fetchOptions(&$query) {
		$arr = array();

		while($r = mysqli_fetch_array($query, DatabaseInterface::STRICTER_DB_SQL_NUM)) {
			$k=$r[0];
			$arr[$k]=$r[1];
		}

		return $arr;
	}

	function free(&$query) {
		mysqli_free_result($query);
	}

	function lastInsertId($entity) {
		$sql_last_insert = "SELECT LAST_INSERT_ID() lid";

		$q = $this->query($sql_last_insert);

		$r = $this->fetch($q);
		
		return $r["lid"];
	}

	function disconnect() {
		mysqli_close($this->conn);
	}

	function escapeString($string_val) {
		$magic_quotes = ini_get('magic_quotes_gpc');

		if($magic_quotes==0 || !$magic_quotes)
			$string_val = mysqli_real_escape_string($string_val);

		return $string_val;
	}

	function error() {
		if(mysqli_error($this->conn))
			return mysqli_error($this->conn);
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
