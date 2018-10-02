<?php

class BooleanType extends BasicType
{
	public function __construct($name, $size=null, $req=true, $def=null ) {
		$this->setName($name);
		$this->setSize($size);	
		$this->setRequired($req);
	}

	public function setValue($newval)
	{
		if($newval===null) {
			unset($_POST[$this->hash]);
		} else {
			$this->_value=$newval;
		}
	}

	function getValue() {
		if($this->_value=='t' || $this->_value===0 || $this->_value=='y' || $this->_value===true)
			return true;
		else
			return false;
	}

	function isTrue() {
		if($this->getValue()===true)
			return true;
		else
			return false;
	}

	function isFalse() {
		if($this->getValue()===false)
			return true;
		else
			return false;
	}

	function filterPost(&$post) {
		if($post==1)
			$this->setValue(true);
		else
			$this->setValue(false);
	}

	function isValid()
	{
		#if( $this->getRequired() && $this->getValue()===null )
			#$this->setError( LANG_REQUIRED_FIELD_ERROR );

		if(strlen($this->getValue() ) > $this->getSize() && $this->getSize()!="")
			$this->setError( LANG_SIZE_LIMIT_ERROR );
	}
}

?>
