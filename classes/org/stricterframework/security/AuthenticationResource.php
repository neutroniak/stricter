<?php

class AuthenticationResource implements Resource
{
	private $stricter;
	private $error;
	private $dbpg;
	private $config;
	private $auth;

	public function __construct(&$config) {

		$this->config=$config;

		ini_set('session.hash_function','sha512');
		ini_set('session.use_strict_mode','On');
		ini_set('session.sid_length','256');

		session_set_cookie_params(4000, null, null, true, true);
		session_name("STRICTER");
		session_start();

		$this->stricter=&Stricter::getInstance();

		switch($this->config['method']) {
			case 'ldap':
				require_once('org/stricterframework/security/LdapAuthentication.php');
				$this->auth = new LdapAuthentication($config); 
				break;

			case 'database':

				break;
		}
	}

	public function login($user, $password)	{
		if($arr=$this->auth->login($user,$password)){
			$this->createSession($arr);
			return true;
		}
	}

	private function createSession($arr) {
		$_SESSION['stricter']=$arr;
	}
	
	public function getError(){
		return $this->error;
	}

	public function setUser(&$user)	{
		$this->user=&$user;
	}

	public function logout() {
		unset($_SESSION["stricter"]);
	}
}

?>
