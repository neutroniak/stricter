<?php

class MessagePlugin extends BasicPlugin
{
	public $smarty;
	private $objvar;
	private $params;

	function __init()
	{
		$this->smarty->registerPlugin("function", "message", array(&$this,"message"));

		$this->addAttribute( 'delay' );
		$this->addAttribute( 'close' );
		$this->addAttribute( 'text' );
		$this->addAttribute( 'jqueryOn' );
		$this->addAttribute( 'jqueryOff' );
	}

	function message($params, $smarty)
	{
		parent::init($params, $objvar);

		if(!$params['id'])
			return "Error: Message needs id attribute.";

		if(!$params['delay'])
			$params['delay'] = "300";

		if(!$params['jqueryOn'])
			$params['jqueryOn'] = "show(".$params['delay'].")";

		if(!$params['jqueryOff'])
			$params['jqueryOff'] = "hide(".$params['delay'].")";

		$str .="<script>
				function __predelay_".$params['id']."() {
					\$('#".$params['id']."').".$params['jqueryOff']."; 
				};
				\$(document).ready(function() {
					\$('#".$params['id']."').hide();
					setTimeout('__predelay_".$params['id']."()', ".$params['delay'].");
					\$('#".$params['id']."').".$params['jqueryOn'].";
				});
				</script>";

		$str .= "<div ";
		$str .= parent::attributes();
		$str .= " >".$params['text']."</div>";

		return $str;
	}
}

?>

