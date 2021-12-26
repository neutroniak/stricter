<?php

class DateTimeType extends BasicType
{
	private $year;
	private $month;
	private $day;
	private $hour;
	private $minute;
	private $second;
	private $timezone;
	private $format;
	private $dateValue;

	public function __construct($name, $size=null, $req=true, $def=null) {
		$this->setName($name);
		$this->setSize($size);	
		$this->setRequired($req);
	}

	// format yyyy-mm-dd hh:mm:ss
	public function setValue($newval){

		if(is_array($newval)){
			$this->year=$newval['year'];
			$this->month=$newval['month'];
			$this->day=$newval['day'];
			$newval['hour'] ? $this->hour=$newval['hour'] : $this->hour='00';
			$newval['minute'] ? $this->minute=$newval['minute'] : $this->minute='00';
			$newval['second'] ? $this->second=$newval['second'] : $this->second='00';
			$this->_value=$this->year.'-'.$this->month.'-'.$this->day.' '.$this->hour.':'.$this->minute.':'.$this->second;
		} else {
			if(strlen($newval)==0) {
				$this->_value=null;
				return;
			}
			if(($format=Stricter::getInstance()->getConfig('datetime_format'))=="")
				$format="Y-m-d H:i:s";
			$date = DateTime::createFromFormat($format, $newval);
			if(!$date){
				Stricter::getInstance()->log("DateType error: could not recognize date value: ".$newval.' using format:'.$format);
				return null;
			}
			$this->year=$date->format('Y');
			$this->month=$date->format('m');
			$this->day=$date->format('d');
			$this->hour=$date->format('H');
			$this->minute=$date->format('i');
			$this->second=$date->format('s');
			$this->_value=$this->year.'-'.$this->month.'-'.$this->day.' '.$this->hour.':'.$this->minute.':'.$this->second;
		} 
	}

	function filterPost(&$post){
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
		$this->setValue(date('Y-m-d H:i:s'));
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
