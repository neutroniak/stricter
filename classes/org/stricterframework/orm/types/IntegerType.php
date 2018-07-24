<?php

class IntegerType extends BasicType
{
	public function __construct($name, $size=null, $req=true, $def=null) {
		$this->setName($name);
		$this->setSize($size);	
	}

	public function setValue($newval)
	{
		if($newval===null) {
			unset($_POST[$this->hash]);
			$this->_value=null;
		} else {
			$this->_value=$newval;
		}
	}

	function filterPost(&$post) {
		if($post===NULL)
			$this->setValue(null);
		else
			$this->setValue(stripslashes($post));
	}

	function isValid()
	{
		if($this->_value!==null)
			$this->setValue( str_replace(',', '.', $this->getValue() ) );

		if($this->getValue()=='on' && $this->getSize()==1)
		{
			$this->setValue(1);
			return 0;
		}

		if($this->getRequired() && trim($this->getValue())=='')
			$this->setError( LANG_REQUIRED_FIELD_ERROR );

		$unsigned = '([^0-9.-]+)';

		if(preg_match("/".$unsigned."/i", $this->getValue()) )
		{
			$this->setError( LANG_INVALID_CHARACTERS_ERROR );
			return 1;
		}

		$exs = explode(',', $this->getSize());
		$exv = explode('.', $this->getValue());

		$ilimit = $exs[0] - $exs[1];

		$integer = str_replace('-','',$exv[0]); //remove minus sign for strlen()
		$decimal = $exv[1];

		if( $this->getSize() != "" )
		{
			if(strlen($integer) > $ilimit || strlen($decimal) > $exs[1])
			{
				$this->setError( LANG_SIZE_LIMIT_ERROR );
				return 1;			
			}
		}

		return 0;
	}
}

?>

