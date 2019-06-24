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

		include_once('security.php');

		$this->stricter=&Stricter::getInstance();

		$path=$this->stricter->getPath();

		if($acl[$path]===false || $path==$config['login-url']) { // open
			return;
		}  else {
			$this->sessionStart();
			if(!$_SESSION['stricter']) {
				$this->stricter->redirect('/login');
			}
		}
	}

	public function login($user, $password)	{
		switch($this->config['method']) {
			case 'ldap':
				require_once('org/stricterframework/security/LdapAuthentication.php');
				$this->auth = new LdapAuthentication($this->config); 
				break;

			case 'database':

				break;
		}
		
		if( $arr=$this->auth->login($user,$password) ) {
			$this->createSession($arr);
			return true;
		}
		else
		{
			return false;
		}
	}

	private function sessionStart() {
		ini_set('session.hash_function','sha512');
		ini_set('session.use_strict_mode','On');
		ini_set('session.sid_length','256');
		if($this->config['session']['lifetime'])
			$lifetime=$this->config['session']['lifetime'];
		session_set_cookie_params($lifetime, "/", null, true, true);
		session_name("STRICTER");
		session_start();	
	}

	private function createSession($arr) {
		$this->sessionStart();
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
