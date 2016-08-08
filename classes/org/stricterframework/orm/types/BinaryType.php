<?php

class BinaryType extends BasicType
{
	private $mime;

	public function __construct($name, $size=null) {
		$this->setName($name);
		$this->setSize($size);	
	}

	public function setValue($newval)
	{
		if($newval===null) {
			unset($_FILES[$this->hash]);
			$this->_value=null;
		} else {
			if($newval[1]=='x')
				$this->_value=(pg_unescape_bytea($newval)); # TODO - multidb
			else
				$this->_value=($newval);
		}
	}

	function filterPost(&$post) {
		$hash=$this->getHash();
		if( $_FILES[$hash]==NULL ) {
			$this->setValue(null);
		} else {
			if( $_FILES[$hash]['tmp_name'] ) {
				
				$path = $_FILES[$hash]['tmp_name'];
				$fp=fopen($path, 'r');
				$content = fread($fp, filesize($path) );
				$this->setValue($content);
				fclose($fp);
				$this->setMime( $_FILES[$hash]['type'] );
			}
		}
	}

	function setMime(&$mime) {
		$this->mime=$mime;
	}

	function getMime() {
		return $this->mime;
	}

	function isValid() {
		return 0;
	}
}

?>
