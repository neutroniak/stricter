<?php

class BasicType
{
	protected $_value;
	protected $_error = null;
	private $_name;
	private $_required = true;
	private $_size;
	private $_precision;
	private $_hash;
	private $_sequence = null;
	private $_default;

	public function getName() { return $this->_name; }
	public function setName($name) { $this->_name=$name; }
	public function getRequired() { return $this->_required; }
	public function setRequired($t=true) { $this->_required=$t; }
	public function getHash() { return $this->_hash; }
	public function setHash($val) { $this->_hash = $val; }
	public function getSize() { return $this->_size; }
	public function setSize($size) { $this->_size=$size; }
	public function getError() { return $this->_error; }
	public function setError($msg) { $this->_error =& $msg; }
	public function getValue() { return $this->_value; }
	public function setValue($val) {$this->_value=$val;}
	public function getSequence() { return $this->_sequence; }
	public function setSequence($val) { $this->_sequence = $val; }
	public function getDefault() { return $this->_default; }
	public function setDefault($default) { $this->_default = $default; }
	public function getType(){return get_class($this);}
}

?>
