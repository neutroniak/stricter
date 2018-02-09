<?php

include_once("Mail.php");

class MailResource implements Resource
{
	public $config=array();
	private $host;
	private $port;
	private $username;
	private $password;
	private $alias;
	private $smtp;
	private $error;
	private $socket_options;

	function __construct(&$config) {
		$this->config=&$config;

		$this->host=$config['host'];
		$this->port=$config['port'];
		$this->username=$config['username'];
		$this->password=$config['password'];

		if($config['socket_options'])
			$this->socket_options = $config['socket_options'];

		$this->smtp =& Mail::factory
			(
				'smtp',
				array ( 
				'auth'=>true,
				'host'=>$this->host,
				'localhost'=>$this->host,
				'username'=>$this->username,
				'password'=>$this->password,
				'port'=>$this->port,
				'socket_options'=>array('ssl' => array('verify_peer_name' => false))
				)
		);
	}

	public function send($to, $subject, &$tpl, $replyto=null)
	{
		if($this->alias) {
			$alias = $this->alias.'<'.$this->username.'>';
		} else {
			$alias = $this->username;
		}

		$headers = array (
				'From'=>$alias,
				'To'=>$to,
				'Bcc'=>'',
				'Subject'=>$subject,
				'Content-type'=>'text/html; charset=UTF-8');

		if($replyto)
			$headers["Reply-to"]=$replyto;

		$mail = $this->smtp->send($to, $headers, $tpl);

		if (PEAR::isError($mail)) {
			Stricter::getInstance()->log( $mail->getMessage() );
			$this->error=$mail->getMessage();
			return false;
		} else {
			return true;
		}
	}

	public function getAlias() {
		return $this->alias;
	}
	public function setAlias($val) {
		$this->alias = $val;
	}
	public function getError(){
		return $this->error;
	}
}

?>
