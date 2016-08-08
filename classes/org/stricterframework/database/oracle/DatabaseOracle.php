<?php

class DatabaseOracle implements DatabaseInterface
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
		$this->dbport = 1521;
		$this->dbtype = 'oracle';
	}

	function connect()
	{
		$conn = oci_connect($this->dbuser, $this->dbpass, "//".$this->dbhost.':'.$this->dbport.'/'.$this->dbname);

		$this->conn =& $conn;

		if(!$this->conn)
			$this->isConnected = false;
		else
			$this->isConnected = true;

		return $conn;
	}
	
	function query($sql)
	{
		$this->sqlStatement = $sql;
		if($sql == "")
			return false;

		$query = oci_parse($this->conn, $sql);

		if($this->debug===true)
			Stricter::getInstance()->log("Database::query ".$this->error()." on query:\n $sql", E_INFO, null, true);

		if($this->error()) {
			Stricter::getInstance()->log("Database::error ".$this->error()." on query:\n $sql", E_ERROR);
		}

		$x = oci_execute($query);

		return $query;
	}
	
	function numrows(&$resource)
	{
		#TODO# - implement COUNT(*) here..

		$n = oci_num_rows($resource);

		return $n;
	}

	function fetch(&$query, $sql_assoc=DatabaseInterface::STRICTER_DB_SQL_ASSOC)
	{
		$r = oci_fetch_array($query, $sql_assoc+OCI_RETURN_NULLS);
		return $r;
	}

	function free(&$query)
	{
		oci_free_statement($query);
	}

	function lastInsertId($entity)
	{
		$entity_name = $entity->getModelName();

		$pk = $entity->getPrimaryKey();

		$primary_key = $pk[0];

		$sql = "SELECT ".$primary_key->getSequence().".CURRVAL AS DD FROM DUAL";

		$q = $this->query($sql);

		$r = $this->fetch($q);

		return $r["DD"];
	}

	function disconnect()
	{
		if($this->isConnected)
			oci_close($this->conn);
	}

	function escapeString($string_val)
	{
		$magic_quotes = ini_get('magic_quotes_gpc');

		if($magic_quotes==0 || !$magic_quotes)
			$string_val = escapeshellcmd($string_val);

		return $string_val;
	}

	function error()
	{
		if(oci_error())
			return oci_error();
		else
			return false;
	}

	function transaction()
	{
		//
	}

	function commit()
	{
		oci_commit( $this->conn );
	}

	function rollback()
	{
		oci_rollback( $this->conn );
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
					$dt = date('Y-M-d h:i:s', $field->getValue());
					return "to_date('".$dt."','YYYY-MM-DD HH:MI:SS')";
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
