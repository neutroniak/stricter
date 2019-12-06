<?php

class BoxPlugin extends BasicPlugin
{
	public $smarty;
	private $objvar;
	private $params;
	
	function __init()
	{
		$this->smarty->registerPlugin("function", "box", array(&$this,"box"));

		$this->addAttribute( 'tools' );
		$this->addAttribute( 'name' );
		$this->addAttribute( 'link' );
		$this->addAttribute( 'ajax' );
	}

	function box($params, $smarty)
	{
		parent::init($params, $objvar);
		$str='<a class="st-ajaxlink" href="'.$params['link'].'"><div class="st-box '.$params['class'].'">'.$params['link'].'
				<div></div>
				<div class="st-box-content st-box-content-'.$params['name'].'"><img style="opacity: 0.7;" src="/themes/default/images/loaderA64.gif"/></div>
			</div></a>';
		if($params['ajax']){
			$str.='<script type="text/javascript">
			$.ajax({ url:"'.$params['ajax'].'/?ajax=1", dataType:"text", method:"GET"})
			.success( function(res){ $(".st-box-content-'.$params['name'].'").html(res); })
			.fail(function(){ console.log("Failed request: '.$params['ajax'].'") })
			.complete(function() {} );
			</script>';
		}
		return $str;
	}
}

?>
