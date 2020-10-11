<?php

class DateType extends BasicType
{
	private $year;
	private $month;
	private $day;
	private $format;
	private $dateValue;

	public function __construct($name, $size=null, $req=true, $def=null) {
		$this->setName($name);
		$this->setSize($size);	
		$this->setRequired($req);
	}

	// format yyyy-mm-dd
	public function setValue($newval){

		if(is_array($newval)){
			$this->year=$newval['year'];
			$this->month=$newval['month'];
			$this->day=$newval['day'];
			if($this->year & $this->month & $this->day)
				$this->_value=$this->year.'-'.$this->month.'-'.$this->day;
		} else {
			if(strlen($newval)==0)
				return;
			if(($format=Stricter::getInstance()->getConfig('date_format'))=="")
				$format="Y-m-d";
			$date = DateTime::createFromFormat($format, $newval);
			if(!$date){
				Stricter::getInstance()->log("DateType error: could not recognize date value: ".$newval.' using format:'.$format);
				return null;
			}
			$this->year=$date->format('Y');
			$this->month=$date->format('m');
			$this->day=$date->format('d');
			if($this->year && $this->month && $this->day)
				$this->_value=$this->year.'-'.$this->month.'-'.$this->day;
			else
				return null;
		} 
	}

	function filterPost(&$post) {
		$this->dateValue=$post;
		$this->setValue($post);
	}

	function isValid(){
		$isdate = checkdate($this->month, $this->day, $this->year);
		if($this->getRequired()===true && $this->getValue()===null) {
			if($this->_default){
				$this->_value=$this->default;
				return 0;	
			} else {
				$this->setError( LANG_REQUIRED_FIELD_ERROR );
				return 1;
			}
		}

		if($this->getValue()!=null && !$isdate && $this->getRequired()===true) {
			$this->setError( LANG_INVALID_DATE_ERROR );
			return 1;
		}
		
		return 0;
	}

	function setNow(){
		$this->setValue(date('Y-m-d'));
	}

	function __get($field){
		return $this->$field;
	}

	function toString(){
		return $this->dateValue;	
	}

	function setFormat($format){
		$this->format=$format;
	}

	function setDefaultNow(){
		$this->setNow();
	}

}

?>
