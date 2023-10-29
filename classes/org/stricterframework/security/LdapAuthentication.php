<?php

class LdapAuthentication
{
	private $stricter;
	private $error;
	private $dbpg;
	private $config;

	public function __construct(&$config) {
		$this->config=$config;
		$this->stricter=&Stricter::getInstance();
	}

	public function login($user, $password)	{
		$ldapconn = ldap_connect($this->config['ldap']['host']) or die("Could not connect to LDAP server.");
		$proto = ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
		$ldapbind = ldap_bind($ldapconn, 'uid='.$user.','.$this->config['ldap']['users'], $password);

		if(!$ldapbind) {
			$this->error=LANG_LDAP_ERROR;
			$this->stricter->log(LANG_LDAP_ERROR.': '.ldap_error($ldapconn));
			return false;
		}

		$ldapsearch = ldap_search($ldapconn, $this->config['ldap']['users'], "(uid=$user)" );
		$ldapentries = ldap_get_entries($ldapconn, $ldapsearch);

		if( $ldapentries['count']=='1' ) {
			$arr = array(
				"cn" => $ldapentries[0]["cn"][0],
				"sn" => $ldapentries[0]["sn"][0],
				"uid" => $ldapentries[0]["uid"][0]
			);
			if($this->config['ldap']['groups']) {
				try {
					$ldapgroups = ldap_search($ldapconn, $this->config['ldap']['groups'], "(memberUid=$user)");
					$groupentries = ldap_get_entries($ldapconn, $ldapgroups);
					$agroups=array();
					foreach($groupentries as $k=>$v) {
						if($v['cn'][0]!="")
							array_push($agroups, $v['cn'][0]);
					}
				} catch(Exception $ldapex) {
					$this->stricter->log($ldapex);
				}
				$arr['groups']=$agroups;
			}	
			return $arr;
		} else {
			$this->stricter->log( LANG_USER_PASS_NOT_MATCHING );
			$this->user->user_password->setValue( "" );
			$this->user->user_password->setError( LANG_USER_PASS_NOT_MATCHING );
			return null;
		}
	}
}

?>
