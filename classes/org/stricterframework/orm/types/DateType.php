<?php

class DateType extends BasicType
{
	private $year;
	private $month;
	private $day;
	private $hour;
	private $minute;
	private $second;

	public function __construct($name, $size=null) {
		$this->setName($name);
		$this->setSize($size);	
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
			$this->year=substr($newval,0,4);
			$this->month=substr($newval,5,2);
			$this->day=substr($newval,8,2);
			$this->hour=substr($newval,11,2);
			$this->minute=substr($newval,14,2);
			$this->second=substr($newval,17,2);
			$this->_value=$newval;
		}
	}

	function filterPost(&$post) {
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

		if($this->getValue()!=null && !$isdate) {
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

	}

	function setDefaultNow(){
		$this->setNow();
	}
}

?>
