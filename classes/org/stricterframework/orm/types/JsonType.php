<?php

class JsonType extends BasicType
{
	public function __construct($name, $size=null, $req=true, $def=null) {
		$this->setName($name);
		$this->setSize($size);
		$this->setRequired($req);
	}

	function setValue($newval) {
		if($newval===null)
			unset($_POST[$this->hash]);

		$this->_value=$newval;
	}

	function filterPost(&$post) {
		if($post===NULL)
			$this->setValue(null);
		else
			$this->setValue($post);
	}

	function isValid()
	{
		if($this->getRequired() && trim($this->getValue())=='')
			$this->setError( LANG_REQUIRED_FIELD_ERROR );

		if( $this->getSize()>0 && strlen($this->getValue())>$this->getSize() && $this->getSize()!="")
			$this->setError( LANG_SIZE_LIMIT_ERROR );
	}
}

?>
