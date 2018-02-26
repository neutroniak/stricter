<?php

class TimeType extends BasicType
{
	private $hour;
	private $minute;
	private $second;
	private $format;
	private $dateValue;

	public function __construct($name, $size=null) {
		$this->setName($name);
		$this->setSize($size);	
	}

	// format hh:mm:ss
	public function setValue($newval){

		if(is_array($newval)){
			$newval['hour'] ? $this->hour=$newval['hour'] : $this->hour='00';
			$newval['minute'] ? $this->minute=$newval['minute'] : $this->minute='00';
			$newval['second'] ? $this->second=$newval['second'] : $this->second='00';
			$this->_value=$this->hour.':'.$this->minute.':'.$this->second;
		} else {
			if(($format=Stricter::getInstance()->getConfig('time_format'))=="")
				$format="H:i:s";
			$date = DateTime::createFromFormat($format, $newval);

			if(!$date){
				Stricter::getInstance()->log("DateType error: could not recognize date value: ".$newval.' using format:'.$format);
				return null;
			}

			$this->hour=$date->format("H");
			$this->minute=$date->format("i");
			$this->second=$date->format("s");
			$this->_value=$this->hour.':'.$this->minute.':'.$this->second;
		} 
	}

	function filterPost(&$post) {
		$this->dateValue=$post;
		$this->setValue($post);
	}

	function isValid(){

		$istime=true;
		if($this->getRequired()===true && $this->getValue()===null) {
			if($this->_default){
				$this->_value=$this->default;
				return 0;	
			} else {
				$this->setError( LANG_REQUIRED_FIELD_ERROR );
				return 1;
			}
		}

		if($this->getValue()!=null && !$istime) {
			$this->setError( LANG_INVALID_TIME_ERROR );
			return 1;
		}
		
		return 0;
	}

	function setNow(){
		$this->setValue(date('H:i:s'));
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
