<?php
function ldap_auth($user, $password) {
	global $default_timeout, $default_graceperiod, $default_token, $ldap_dn, $ldap_host, $ldap_binddn, $ldap_starttls, $ldap_debug;

	$out['success'] = false;
	if(empty($user) || empty($password)) return $out;
	
	if ($ldap_debug >= 3 ) { error_log("MPT.ldap_auth: ldap_conenct($ldap_host)"); }
	// connect to LDAP host
	$ldap = ldap_connect($ldap_host);
	$ldap_connect_msg = "";
	if ( $ldap ) { $ldap_connect_msg = "Success"; }  else { $ldap_connect_msg = "Failed"; }
	if ($ldap_debug >= 3 ) { error_log("MPT.ldap_auth: ldap_conenct($ldap_host) => $ldap_connect_msg"); }
	// configure ldap params
	ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION,3);
	ldap_set_option($ldap, LDAP_OPT_REFERRALS,0);
	ldap_set_option($ldap, LDAP_OPT_NETWORK_TIMEOUT, 1);
	ldap_set_option($ldap, LDAP_OPT_TIMELIMIT, 1);
	
	// Don't do things over plain text.
	if ( $ldap_starttls ) { 
		
		 if ($ldap_debug >= 2 ) { error_log("MPT.ldap_auth: ldap_start_tls() attempting."); }
		$r = ldap_start_tls($ldap); 
		if ( $r ) { $ldap_connect_msg = "Success"; }  else { $ldap_connect_msg = "Failed"; }		
		if ($ldap_debug >= 2 ) { error_log("MPT.ldap_auth: ldap_start_tls() => $ldap_connect_msg"); }
	}


	// Doing authentication based on the success of the BIND to LDAP.
	
	if ( $ldap ) {
		 if ($ldap_debug >= 1 ) { error_log("MPT.ldap_auth: ldap_bind() attempting to bind as uid=$user,$ldap_dn "); } 
		$bind = ldap_bind($ldap, "uid=$user,$ldap_dn", $password);
		if ($bind) {
			$out['success'] = TRUE;
			$out['tokens']  = array ( $default_token ) ;
			$out['timeout'] = $default_timeout;
			$out['graceperiod'] = $default_graceperiod;
		}
		if ( $bind ) { $ldap_connect_msg = "Success"; }  else { $ldap_connect_msg = "Failed"; }		
		if ($ldap_debug >= 1 ) { error_log("MPT.ldap_auth: ldap_bind() => $ldap_connect_msg "); }
	} else {
		if ($ldap_debug >= 1 ) { error_log("MPT.ldap_auth: ldap_bind(), skipping problem with ldap_connect."); 	 }
	}

	// error_log ( " ==> user_out_info:  " . json_encode($out) );

	return $out;
}
?>
