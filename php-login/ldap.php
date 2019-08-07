<?php
function ldap_auth($user, $password) {
	global $default_timeout, $default_graceperiod, $default_token, $ldap_host, $ldap_binddn, $ldap_starttls

	$out['success'] = false;
	if(empty($user) || empty($password)) return $out;
	
	// connect to LDAP host
	$ldap = ldap_connect($ldap_host);
	
	// Don't do things over plain text.
	if ( $ldap_starttls ) { ldap_start_tls($ldap); }

	// configure ldap params
	ldap_set_option($ldap,LDAP_OPT_PROTOCOL_VERSION,3);
	ldap_set_option($ldap,LDAP_OPT_REFERRALS,0);

	// Doing authentication based on the success of the BIND to LDAP.
	
	if ( $ldap ) {
		// UID is what Zimbra uses for the username.  You may need to change this.
		$bind = ldap_bind($ldap, "uid=$user,$ldap_dn", $password);
		if ($bind) {
			$out['success'] = TRUE;
			$out['tokens']  = array ( $default_token ) ;
			$out['timeout'] = $default_timeout;
			$out['graceperiod'] = $default_graceperiod;
		}
	}

	// error_log ( " ==> user_out_info:  " . json_encode($out) );

	return $out;
}
?>
