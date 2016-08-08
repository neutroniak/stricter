<?php

class EmailType extends BasicType
{
	public function __construct($name) {
		$this->setName($name);
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

		if( strlen($this->_value)>$this->getSize() && $this->getSize()!="")
			$this->setError( LANG_SIZE_LIMIT_ERROR );

		$filter = filter_var($this->getValue(), FILTER_VALIDATE_EMAIL);

		if(! $filter )
			$this->setError( LANG_EMAIL_NOT_VALID );
	}
}

?>
