<?php

class StringType extends BasicType
{
	public function __construct($name, $size=null) {
		$this->setName($name);
		$this->setSize($size);	
	}

	public function setValue($newval)
	{
		if($newval===null)
			unset($_POST[$this->hash]);

		$this->_value=$newval;
	}

	function filterPost(&$post) {
		if($post===NULL)
			$this->setValue(null);
		else
			$this->setValue(stripslashes($post));
	}

	function isValid()
	{
		if($this->getRequired() && trim($this->getValue())=='')
			$this->setError( LANG_REQUIRED_FIELD_ERROR );

		if( strlen($this->getValue())>$this->getSize() && $this->getSize()!="")
			$this->setError( LANG_SIZE_LIMIT_ERROR );
	}
}

?>
